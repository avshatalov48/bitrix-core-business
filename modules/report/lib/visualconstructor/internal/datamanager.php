<?php
namespace Bitrix\Report\VisualConstructor\Internal;

/**
 * Class DataManager
 * @package Bitrix\Report\VisualConstructor\Internal
 */
class DataManager extends \Bitrix\Main\Entity\DataManager
{
	/**
	 * @return string
	 */
	public static function getClassName()
	{
		return get_called_class();
	}
}