<?php

namespace Mophpidy\Api;

use React\Promise as When;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;
use function Functional\filter;
use function Functional\first;
use function Functional\map;

class Library
{
    private $endpoint;

    public function __construct(Endpoint $endpoint)
    {
        $this->endpoint = $endpoint;
    }

    public function browse($uri = null): PromiseInterface
    {
        return $this->endpoint->ask('core.library.browse', ['uri' => $uri]);
    }

    public function getFavorites(string $pattern = '/favou?rites/i'): PromiseInterface
    {
        $defer = new Deferred();
        $reject = \Closure::fromCallable([$defer, 'reject']);

        $this->endpoint->getUriSchemes()->then(
            function (array $schemes) use ($defer, $reject, $pattern) {
                $promises = [];

                if (in_array('soundcloud', $schemes)) {
                    $promises[] = $this->browse('soundcloud:directory:sets');
                }

                $promises[] = $this->getAllPlaylists();

                When\all($promises)->then(
                    function (array $data) use ($defer, $reject, $pattern) {
                        $promises = map(
                            filter(
                                array_merge(...$data),
                                function (array $item) use ($pattern) {
                                    return preg_match($pattern, $item['name']);
                                }
                            ),
                            function (array $item) use ($defer) {
                                switch ($item['type']) {
                                    case 'directory':
                                        return $this->browse($item['uri']);
                                    case 'playlist':
                                        return $this->getPlaylist($item['uri']);
                                    default:
                                        $exception = new \RuntimeException('Unknown type: '.$item['type']);

                                        $defer->reject($exception);
                                        throw $exception;
                                }
                            }
                        );

                        When\all($promises)->then(
                            function (array $data) use ($defer) {
                                $defer->resolve(array_merge(...$data));
                            },
                            $reject
                        );
                    },
                    $reject
                );
            },
            $reject
        );

        return $defer->promise();
    }

    public function getPlaylistByName(string $name): PromiseInterface
    {
        $defer = new Deferred();

        $this->getAllPlaylists()->then(
            function (array $data) use ($name, $defer) {
                foreach ($data as $item) {
                    if ($item['name'] === $name) {
                        $this->getPlaylist($item['uri'])->then(
                            \Closure::fromCallable([$defer, 'resolve']),
                            \Closure::fromCallable([$defer, 'reject'])
                        );

                        return;
                    }
                }

                $defer->reject(new \Exception('Not found.'));
            },
            \Closure::fromCallable([$defer, 'reject'])
        );

        return $defer->promise();
    }

    public function getAllPlaylists(): PromiseInterface
    {
        return $this->endpoint->ask('core.playlists.as_list');
    }

    public function getPlaylist(string $uri): PromiseInterface
    {
        return $this->endpoint->ask(
            'core.playlists.get_items',
            [
                'uri' => $uri,
            ]
        );
    }

    public function getPromoted(): PromiseInterface
    {
        return $this->getPlaylist('gmusic:playlist:promoted');
    }

    public function findTrack(string $album, string $name, array $uris = ['gmusic:']): PromiseInterface
    {
        $defer = new Deferred();

        $this->endpoint->ask(
            'core.library.search',
            [
                'exact' => false,
                'uris' => $uris,
                'query' => [
                    'album' => [$album],
                ],
            ]
        )->then(
            function (array $data) use ($defer, $name) {
                $track = first(
                    $data[0]['tracks'],
                    function (array $element) use ($name) {
                        return $element['name'] === $name;
                    }
                );

                $defer->resolve($track);
            },
            \Closure::fromCallable([$defer, 'reject'])
        );

        return $defer->promise();
    }

    public function search(string $query, array $uris = ['gmusic:']): PromiseInterface
    {
        return $this->endpoint->ask(
            'core.library.search',
            [
                'exact' => false,
                'uris' => $uris,
                'query' => [
                    'any' => [$query],
                ],
            ]
        );
    }
}
