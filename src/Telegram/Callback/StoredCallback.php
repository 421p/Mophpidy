<?php

namespace Phpidy\Telegram\Callback;

use Ramsey\Uuid\Uuid;

class StoredCallback
{
    private $id;
    private $command;
    private $date;
    private $payload;

    public function __construct(string $command, $payload = [])
    {
        $this->command = $command;
        $this->id = Uuid::uuid4();
        $this->date = new \DateTime();
        $this->payload = $payload;
    }

    public function getCommand(): string
    {
        return $this->command;
    }

    public function getId(): \Ramsey\Uuid\UuidInterface
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
}