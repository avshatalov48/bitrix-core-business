<?php
namespace Bitrix\Report\VisualConstructor\Helper;

/**
 * Class DemoBase
 * @package Bitrix\Report\VisualConstructor\Helper
 */
class DemoBase
{
	/**
	 * @param array $params Parameters for build demo data for single type.
	 * @return array
	 */
	public static function getDemoDataForSingle($params = array())
	{
		return array(
			'value' => 32,
			'params' => $params
		);
	}

	/**
	 * @return array
	 */
	public static function getDemoDataForMultiple()
	{
		return array();
	}

	/**
	 * @return array
	 */
	public static function getDemoDataForMultipleGrouped()
	{
		return array();
	}

	/**
	 * @return array
	 */
	public static function getDemoDataForMultipleBiGrouped()
	{
		return array();
	}
}