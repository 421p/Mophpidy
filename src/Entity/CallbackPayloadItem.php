<?php

namespace Mophpidy\Entity;

class CallbackPayloadItem implements \Serializable, \JsonSerializable
{
    protected $uri;
    protected $name;

    public static function fromArray(array $data): CallbackPayloadItem
    {
        $item = new CallbackPayloadItem();
        $item->setName($data['name']);
        $item->setUri($data['uri']);

        return $item;
    }

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

        $this->name = $data['name'];
        $this->uri = $data['uri'];
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
            'name' => $this->name,
            'uri'  => $this->uri,
        ];
    }
}
