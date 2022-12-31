<?php

namespace Bitrix\Calendar\Core\Queue\Impl;

/**
 * The MessageTrait is a common implementation of the Message interface
 */
trait MessageTrait
{
    /**
     * @var mixed
     */
    private $body = null;

    /**
     * @var array
     */
    private array $properties = [];

    /**
     * @var bool
     */
    private bool $redelivered = false;

    /**
     * @var array
     */
    private array $headers = [];

    public function getBody()
    {
        return $this->body;
    }

    public function setBody($body): self
    {
        $this->body = $body;

		return $this;
    }

    public function setProperties(array $properties): self
    {
        $this->properties = $properties;

		return $this;
    }

    public function setProperty(string $name, $value): self
    {
        if(null === $value)
		{
            unset($this->properties[$name]);
        }
		else
		{
            $this->properties[$name] = $value;
        }

		return $this;
    }

    public function getProperties(): array
    {
        return $this->properties;
    }

    public function getProperty(string $name, $default = null)
    {
        return array_key_exists($name, $this->properties) ? $this->properties[$name] : $default;
    }

    public function setHeader(string $name, $value): self
    {
        if(null === $value)
		{
            unset($this->headers[$name]);
        }
		else
		{
            $this->headers[$name] = $value;
        }

		return $this;
    }

    public function setHeaders(array $headers): self
    {
        $this->headers = $headers;

		return $this;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getHeader(string $name, $default = null)
    {
        return array_key_exists($name, $this->headers) ? $this->headers[$name] : $default;
    }

    public function setRedelivered(bool $redelivered): self
    {
        $this->redelivered = $redelivered;

		return $this;
    }

    public function isRedelivered(): bool
    {
        return $this->redelivered;
    }

    public function setCorrelationId(string $correlationId = null): self
    {
        $this->setHeader('correlation_id', $correlationId);

		return $this;
    }

    public function getCorrelationId(): ?string
    {
        return $this->getHeader('correlation_id');
    }

    public function setMessageId(string $messageId = null): self
    {
        $this->setHeader('message_id', $messageId);

		return $this;
    }

    public function getMessageId(): ?string
    {
        return $this->getHeader('message_id');
    }

    public function getTimestamp(): ?int
    {
        $value = $this->getHeader('timestamp');

        return null === $value ? null : (int) $value;
    }

    public function setTimestamp(int $timestamp = null): self
    {
        $this->setHeader('timestamp', $timestamp);

		return $this;
    }

    public function setReplyTo(string $replyTo = null): self
    {
        $this->setHeader('reply_to', $replyTo);

		return $this;
    }

    public function getReplyTo(): ?string
    {
        return $this->getHeader('reply_to');
    }
}
