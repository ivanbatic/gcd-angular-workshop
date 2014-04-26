<?php

/**
 * Description of JsonResponse
 *
 * @author Ivan Batić <ivan.batic@live.com>
 */
use Illuminate\Support\MessageBag;

class ApiResponse implements JsonSerializable
{

    /** @var int Http status code */
    protected $statusCode = 200;
    /** @var array Custom fields */
    protected $fields = array();
    /** @var MessageBag */
    protected $messageBag;

    public function __construct(MessageBag $messageBag)
    {
        $this->messageBag = $messageBag;
    }

    public function setMessageBag(MessageBag $messages = null)
    {
        $this->messageBag = $messages;

        return $this;
    }

    public function addMessage($key, $message)
    {
        $this->messageBag->add($key, $message);

        return $this;
    }

    public function setField($key, $value)
    {
        $this->fields[$key] = $value;

        return $this;
    }

    public function getField($key)
    {
        return $this->fields[$key];
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function toArray()
    {
        return array(
            'status_code' => $this->getStatusCode(),
            'messages'    => $this->getMessages()
        ) + $this->getFields();
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function setStatusCode($status)
    {
        $this->statusCode = (int)$status;

        return $this;
    }

    public function getMessages()
    {
        return $this->messageBag->getMessages();
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function setFields(array $customFields)
    {
        $this->fields = $customFields;

        return $this;
    }

    public function toJsonResponse()
    {
        return \Response::json($this->toArray(), $this->getStatusCode());
    }
}
