<?php

namespace Mophpidy\Entity;

use Ramsey\Uuid\Uuid;
use function Functional\map;

class CallbackContainer implements \Serializable, \JsonSerializable
{
    const DIRECTORIES = 1;
    const TRACKS      = 2;

    const DELETE   = -1;
    const BACKWARD = -2;

    protected $id;
    protected $date;
    protected $messageId;

    protected $payload = [];
    protected $type;

    protected $userId;

    protected $selectIndex;

    public static function pack(array $data, string $type, int $userId): CallbackContainer
    {
        $callback = new CallbackContainer();
        $callback->setId(Uuid::uuid4()->toString());
        $callback->setDate(new \DateTime());
        $callback->setUserId($userId);

        $callback->setType($type);

        foreach ($data as $value) {
            $item = new CallbackPayloadItem();

            $item->setName($value['name']);
            $item->setUri($value['uri']);

            $callback->addItem($item);
        }

        return $callback;
    }

    public function addItem(CallbackPayloadItem $item)
    {
        $this->payload[] = $item;
    }

    public function mapInlineKeyboard(): array
    {
        $i       = 0;
        $buttons = map(
            $this->payload,
            function (CallbackPayloadItem $item) use (&$i) {
                $mapping = [
                    [
                        'text'          => $item->getName(),
                        'callback_data' => sprintf('%s:%d', $this->id, $i),
                    ],
                ];

                $i++;

                return $mapping;
            }
        );

        $buttons[] = [
            [
                'text'          => '❌ Close',
                'callback_data' => sprintf('%s:%d', $this->id, self::DELETE),
            ],
        ];

        return $buttons;
    }

    public function getCommand(): string
    {
        return '/resolve';
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getDate(): \DateTime
    {
        return $this->date;
    }

    /**
     * @return CallbackPayloadItem[]
     */
    public function getPayload()
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

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function setDate(\DateTime $date): void
    {
        $this->date = $date;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getMessageId()
    {
        return $this->messageId;
    }

    public function setMessageId($messageId): void
    {
        $this->messageId = $messageId;
    }

    /**
     * @param int $userId
     */
    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    /**
     * String representation of object
     *
     * @link  http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     * @since 5.1.0
     */
    public function serialize()
    {
        return json_encode($this);
    }

    /**
     * Constructs the object
     *
     * @link  http://php.net/manual/en/serializable.unserialize.php
     *
     * @param string $serialized <p>
     *                           The string representation of the object.
     *                           </p>
     *
     * @return void
     * @since 5.1.0
     */
    public function unserialize($serialized)
    {
        $data = json_decode($serialized, true);

        $this->id        = $data['id'];
        $this->date      = new \DateTime($data['date']['date'], new \DateTimeZone($data['date']['timezone']));
        $this->messageId = $data['messageId'];
        $this->userId    = $data['userId'];
        $this->type      = $data['type'];
        $this->payload   = array_map([CallbackPayloadItem::class, 'fromArray'], $data['payload']);
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link  http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return [
            'id'        => $this->id,
            'date'      => $this->date,
            'messageId' => $this->messageId,
            'userId'    => $this->userId,
            'type'      => $this->type,
            'payload'   => $this->payload,
        ];
    }
}
