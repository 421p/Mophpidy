<?php

namespace Mophpidy\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="user", indexes={@ORM\Index(name="search_idx", columns={"should_be_notified"})})
 * @ORM\Entity
 */
class User
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     */
    protected $id;

    /**
     * @var bool
     * @ORM\Column(name="should_be_notified", type="boolean")
     */
    protected $notification;

    /**
     * @var bool
     * @ORM\Column(name="is_admin", type="boolean")
     */
    protected $admin;

    /**
     * @ORM\OneToMany(targetEntity="CallbackContainer", mappedBy="user")
     */
    protected $callbacks;

    public function __construct()
    {
        $this->callbacks = new ArrayCollection();
    }

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

    public function isAdmin(): bool
    {
        return $this->admin;
    }

    public function setAdmin(bool $admin): void
    {
        $this->admin = $admin;
    }
}
