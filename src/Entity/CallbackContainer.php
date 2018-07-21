<?php

namespace Mophpidy\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use function Functional\map;

/**
 * @ORM\Table(name="callback", indexes={@ORM\Index(name="callback_id_index", columns={"id"})})
 * @ORM\Entity
 */
class CallbackContainer
{
    const DIRECTORIES = 1;
    const TRACKS = 2;

    const DELETE = -1;
    const BACKWARD = -2;

    /**
     * @ORM\Column(name="id", type="string")
     * @ORM\Id
     */
    protected $id;
    /** @ORM\Column(name="date", type="date") */
    protected $date;
    /** @ORM\Column(name="message_id", type="integer") */
    protected $messageId;

    /**
     * @var CallbackContainer
     * @ORM\ManyToOne(targetEntity="CallbackContainer", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    protected $parent;
    /**
     * @ORM\OneToMany(targetEntity="CallbackContainer", mappedBy="children", cascade={"persist", "remove"})
     */
    protected $children;
    /**
     * @ORM\OneToMany(targetEntity="CallbackPayloadItem", mappedBy="callback", cascade={"persist", "remove"})
     */
    protected $payload;
    /** @ORM\Column(name="type", type="integer") */
    protected $type;
    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="Mophpidy\Entity\User", inversedBy="callbacks")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    protected $selectIndex;

    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->payload = new ArrayCollection();
    }

    public static function pack(array $data, string $type, User $user): CallbackContainer
    {
        $callback = new CallbackContainer();
        $callback->setId(Uuid::uuid4()->toString());
        $callback->setDate(new \DateTime());
        $callback->setUser($user);

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
        $this->payload->add($item);
        $item->setCallback($this);
    }

    public function mapInlineKeyboard(): array
    {
        $buttons = map(
            $this->payload->getIterator(),
            function (CallbackPayloadItem $item, int $i) {
                return [
                    [
                        'text' => $item->getName(),
                        'callback_data' => sprintf('%s:%d', $this->id, $i),
                    ],
                ];
            }
        );

        $buttons[] = [
            [
                'text' => '❌ Close',
                'callback_data' => sprintf('%s:%d', $this->id, self::DELETE),
            ],
        ];

        if ($this->hasParent()) {
            array_unshift(
                $buttons,
                [
                    [
                        'text' => '⬅️Back',
                        'callback_data' => sprintf('%s:%d', $this->id, self::BACKWARD),
                    ],
                ]
            );
        }

        return $buttons;
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

    /** @return ArrayCollection */
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

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getMessageId()
    {
        return $this->messageId;
    }

    public function setMessageId($messageId): void
    {
        $this->messageId = $messageId;
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function getRoot(): CallbackContainer
    {
        $root = $this;

        while (null !== ($parent = $root->getParent())) {
            $root = $parent;
        }

        return $root;
    }

    public function purgeChildren()
    {
        $this->children->clear();
    }

    public function hasChildren(): bool
    {
        return 0 !== $this->children->count();
    }

    public function hasParent(): bool
    {
        return null !== $this->getParent();
    }

    public function getParent(): ?CallbackContainer
    {
        return $this->parent;
    }

    public function setParent(CallbackContainer $parent)
    {
        $this->parent = $parent;
    }

    public function addChild(CallbackContainer $child)
    {
        $this->children->add($child);
        $child->setParent($this);
    }
}
