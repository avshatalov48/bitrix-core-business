<?php
namespace Bitrix\Forum\Internals;
trait EntityBaseMethods
{
	/** @var int */
//	protected $id = 0;
	/** @var array */
//	protected $data = [];

	public function getId()
	{
		return $this->id;
	}

	public function getData()
	{
		return $this->data;
	}

	public function offsetExists($offset): bool
	{
		return array_key_exists($offset, $this->data);
	}

	public function offsetGet($offset): mixed
	{
		return $this->data[$offset];
	}

	public function offsetSet($offset, $value): void
	{

	}

	public function offsetUnset($offset): void
	{

	}
}
