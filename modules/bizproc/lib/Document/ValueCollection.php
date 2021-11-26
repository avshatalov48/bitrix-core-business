<?php

namespace Bitrix\Bizproc\Document;

abstract class ValueCollection implements \ArrayAccess
{
	protected $document = [];

	public function __construct(array $document)
	{
		$this->document = $document;
	}

	public function offsetGet($offset)
	{
		return $this->document[$offset] ?? null;
	}

	public function offsetExists($offset)
	{
		return array_key_exists($offset, $this->document);
	}

	public function offsetSet($offset, $value)
	{

	}

	public function offsetUnset($offset)
	{

	}
}
