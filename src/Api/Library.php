<?php

namespace Phpidy\Api;

use React\Promise\Deferred;
use React\Promise\PromiseInterface;
use function Functional\first;

class Library
{
    private $endpoint;

    public function __construct(Endpoint $endpoint)
    {
        $this->endpoint = $endpoint;
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

    public function getFavorites(): PromiseInterface
    {
        return $this->getPlaylistByName('Favorites');
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

    public function getGmusicPromoted(): PromiseInterface
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