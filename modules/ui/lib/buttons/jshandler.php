<?php

namespace Bitrix\UI\Buttons;

final class JsHandler implements \JsonSerializable
{
	/**
	 * @var string
	 */
	private $handler;
	/**
	 * @var string|null
	 */
	private $context;

	/**
	 * JsHandler constructor.
	 *
	 * @param string $handler
	 * @param string|null $context
	 */
	public function __construct($handler, $context = null)
	{
		$this
			->setHandler($handler)
			->setContext($context)
		;
	}

	/**
	 * @return string
	 */
	public function getHandler()
	{
		return $this->handler;
	}

	/**
	 * @param string $handler
	 *
	 * @return JsHandler
	 */
	public function setHandler($handler)
	{
		$this->handler = $handler;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getContext()
	{
		return $this->context;
	}

	/**
	 * @param string|null $context
	 *
	 * @return JsHandler
	 */
	public function setContext($context)
	{
		$this->context = $context;

		return $this;
	}

	/**
	 * Specify data which should be serialized to JSON
	 * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
	 * @return mixed data which can be serialized by <b>json_encode</b>,
	 * which is a value of any type other than a resource.
	 * @since 5.4.0
	 */
	public function jsonSerialize()
	{
		return [
			'handler' => $this->getHandler(),
			'context' => $this->getContext(),
		];
	}
}