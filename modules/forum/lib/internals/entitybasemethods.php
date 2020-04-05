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

	public function offsetExists($offset)
	{
		return array_key_exists($offset, $this->data);
	}

	public function offsetGet($offset)
	{
		return $this->data[$offset];
	}

	public function offsetSet($offset, $value)
	{

	}

	public function offsetUnset($offset)
	{

	}
}