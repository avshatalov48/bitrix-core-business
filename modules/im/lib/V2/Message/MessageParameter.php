<?php

namespace Bitrix\Im\V2\Message;

interface MessageParameter
{
	public function setMessageId(int $messageId): self;

	public function getMessageId(): ?int;

	/**
	 * @see \Bitrix\Im\V2\Message\Params for common names.
	 * @param string $name
	 * @return $this
	 */
	public function setName(string $name): self;

	/**
	 * @return string|null
	 */
	public function getName(): ?string;

	/**
	 * @see \Bitrix\Im\V2\Message\Param for scalar papam types.
	 * @param string $type
	 * @return $this
	 */
	public function setType(string $type): self;

	/**
	 * @return string
	 */
	public function getType(): string;

	/**
	 * @param mixed $value
	 * @return static
	 */
	public function setValue($value): self;

	/**
	 * @return mixed|null
	 */
	public function getDefaultValue();

	/**
	 * @return bool
	 */
	public function hasValue(): bool;

	/**
	 * @return mixed|null
	 */
	public function getValue();

	/**
	 * @param mixed $value
	 * @return static
	 */
	public function addValue($value): self;

	/**
	 * @return static
	 */
	public function unsetValue(): self;

	/**
	 * @return string|array|null
	 */
	public function toRestFormat();

	/**
	 * @return string|array|null
	 */
	public function toPullFormat();
}