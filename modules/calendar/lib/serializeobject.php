<?php


namespace Bitrix\Calendar;


trait SerializeObject
{
	/**
	 * @return string
	 */
	public function serialize(): string
	{
		return serialize(get_object_vars($this));
	}

	/**
	 * @param $serializedData
	 */
	public function unserialize($serializedData): void
	{
		$data = unserialize($serializedData);
		foreach ($data as $key => $value)
		{
			$this->$key = $value;
		}
	}
}