<?php


namespace Bitrix\Calendar;


trait SerializeObject
{
	public function __serialize(): array
	{
		return (array)unserialize($this->serialize(), ['allowed_classes'=>false]);
	}

	/**
	 * @return string
	 */
	public function serialize(): string
	{
		return serialize(get_object_vars($this));
	}

	public function __unserialize($data): void
	{
		$this->unserialize($data);
	}

	/**
	 * @param $serializedData
	 */
	public function unserialize($serializedData): void
	{
		$data = $serializedData;
		if (is_string($data))
		{
			$data = unserialize($data, ['allowed_classes' => false]);
		}
		foreach ($data as $key => $value)
		{
			$this->$key = $value;
		}
	}
}