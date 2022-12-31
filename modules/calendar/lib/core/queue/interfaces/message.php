<?php
namespace Bitrix\Calendar\Core\Queue\Interfaces;

/**
 * The Message interface is the root interface of all transport messages.
 * Most message-oriented middleware (MOM) products
 * treat messages as lightweight entities that consist of a header and a payload.
 * The header contains fields used for message routing and identification;
 * the payload contains the application data being sent.
 *
 * Within this general form, the definition of a message varies significantly across products.
 *
 * @see https://docs.oracle.com/javaee/7/api/javax/jms/Message.html
 */
interface Message
{

	/**
	 * @param mixed $body json serializable value
	 *
	 * @return $this
	 */
	public function setBody($body): self;
	/**
	 * @return mixed
	 */
	public function getBody();


	public function setProperties(array $properties): self;

	/**
	 * Returns [name => value, ...]
	 */
	public function getProperties(): array;

	public function setProperty(string $name, $value): self;

	public function getProperty(string $name, $default = null);

	public function setHeaders(array $headers): self;

	/**
	 * Returns [name => value, ...]
	 */
	public function getHeaders(): array;

	public function setHeader(string $name, $value): self;

	public function getHeader(string $name, $default = null);

	public function setRoutingKey(string $routingKey = null): self;

	public function getRoutingKey(): ?string;
}