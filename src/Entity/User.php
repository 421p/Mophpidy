<?php

namespace Mophpidy\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="user", indexes={@ORM\Index(name="search_idx", columns={"should_be_notified"})})
 * @ORM\Entity
 */
class User
{
    /**
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     */
    protected $id;

    /**
     * @var boolean
     * @ORM\Column(name="should_be_notified", type="boolean")
     */
    protected $notification;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id): void
    {
        $this->id = $id;
    }

    public function shouldBeNotified(): bool
    {
        return $this->notification;
    }

    public function setNotification(bool $notification): void
    {
        $this->notification = $notification;
    }
}