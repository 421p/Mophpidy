<?php

namespace Mophpidy\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="callback_payload")
 * @ORM\Entity
 */
class CallbackPayloadItem
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Id
     */
    protected $id;

    /**
     * @var CallbackContainer
     * @ORM\ManyToOne(targetEntity="Mophpidy\Entity\CallbackContainer", inversedBy="payload")
     * @ORM\JoinColumn(name="callback_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $callback;

    /** @ORM\Column(name="uri", type="string") */
    protected $uri;
    /** @ORM\Column(name="name", type="string") */
    protected $name;

    public function getUri()
    {
        return $this->uri;
    }

    public function setUri($uri): void
    {
        $this->uri = $uri;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name): void
    {
        $this->name = $name;
    }

    public function getCallback(): CallbackContainer
    {
        return $this->callback;
    }

    public function setCallback(CallbackContainer $callback): void
    {
        $this->callback = $callback;
    }
}