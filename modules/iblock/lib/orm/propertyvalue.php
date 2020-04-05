<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage iblock
 * @copyright  2001-2018 Bitrix
 */

namespace Bitrix\Iblock\ORM;

/**
 * @package    bitrix
 * @subpackage iblock
 */
class PropertyValue
{
	protected $value;

	protected $description;

	public function __construct($value, $description = null)
	{
		$this->value = $value;

		if ($description !== null)
		{
			$this->description = $description;
		}
	}

	/**
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * @return null
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * @return bool
	 */
	public function hasDescription()
	{
		return $this->description !== null;
	}
}
