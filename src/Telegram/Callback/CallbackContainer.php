<?php

namespace Mophpidy\Telegram\Callback;

use Ramsey\Uuid\Uuid;
use function Functional\map;

class CallbackContainer
{
    const DIRECTORIES = 'dirs';
    const TRACKS = 'tracks';

    private $id;
    private $date;
    private $payload;
    private $selectIndex;
    private $type;

    public function __construct($payload = [])
    {
        $this->id = Uuid::uuid4()->toString();
        $this->date = new \DateTime();
        $this->payload = $payload;
    }

    public static function packTracks(array $data): CallbackContainer
    {
        $payload = map(
            $data,
            function (array $song) {
                return [
                    'name' => $song['name'],
                    'uri' => $song['uri'],
                ];
            }
        );

        $callback = new CallbackContainer($payload);
        $callback->setType(CallbackContainer::TRACKS);

        return $callback;
    }

    public static function packDirs(array $data): CallbackContainer
    {
        $payload = map(
            $data,
            function (array $dir) {
                return [
                    'name' => $dir['name'],
                    'uri' => $dir['uri'],
                ];
            }
        );

        $callback = new CallbackContainer($payload);
        $callback->setType(CallbackContainer::DIRECTORIES);

        return $callback;
    }

    public function mapInlineKeyboard(): array
    {
        return map(
            $this->payload,
            function (array $dir, int $i) {
                return [
                    [
                        'text' => $dir['name'],
                        'callback_data' => sprintf('%s:%d', $this->id, $i),
                    ],
                ];
            }
        );
    }

    public function getCommand(): string
    {
        return sprintf('/resolve %s', $this->id);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getDate(): \DateTime
    {
        return $this->date;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type): void
    {
        $this->type = $type;
    }

    public function getSelectIndex()
    {
        return $this->selectIndex;
    }

    public function setSelectIndex($selectIndex): void
    {
        $this->selectIndex = $selectIndex;
    }
}