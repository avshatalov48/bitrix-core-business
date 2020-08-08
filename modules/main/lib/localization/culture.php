<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Main\Localization;

use Bitrix\Main\ORM;
use Bitrix\Main\ORM\Data;

class CultureTable extends Data\DataManager
{
	const LEFT_TO_RIGHT = 'Y';
	const RIGHT_TO_LEFT = 'N';

	public static function getTableName()
	{
		return 'b_culture';
	}

	public static function getObjectClass()
	{
		return \Bitrix\Main\Context\Culture::class;
	}

	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'CODE' => array(
				'data_type' => 'string',
			),
			'NAME' => array(
				'data_type' => 'string',
				'required' => true,
				'title' => Loc::getMessage("culture_entity_name"),
			),
			'FORMAT_DATE' => array(
				'data_type' => 'string',
				'required' => true,
				'default_value' => "MM/DD/YYYY",
				'title' => Loc::getMessage("culture_entity_date_format"),
			),
			'FORMAT_DATETIME' => array(
				'data_type' => 'string',
				'required' => true,
				'default_value' => "MM/DD/YYYY HH:MI:SS",
				'title' => Loc::getMessage("culture_entity_datetime_format"),
			),
			'FORMAT_NAME' => array(
				'data_type' => 'string',
				'required' => true,
				'default_value' => "#NAME# #LAST_NAME#",
				'title' => Loc::getMessage("culture_entity_name_format"),
			),
			'WEEK_START' => array(
				'data_type' => 'integer',
				'default_value' => 0,
			),
			'CHARSET' => array(
				'data_type' => 'string',
				'required' => true,
				'default_value' => "UTF-8",
				'title' => Loc::getMessage("culture_entity_charset"),
			),
			'DIRECTION' => array(
				'data_type' => 'boolean',
				'values' => array(self::RIGHT_TO_LEFT, self::LEFT_TO_RIGHT),
				'default_value' => self::LEFT_TO_RIGHT,
			),
			'SHORT_DATE_FORMAT' => array(
				'data_type' => 'string',
				'default_value' => "n/j/Y",
			),
			'MEDIUM_DATE_FORMAT' => array(
				'data_type' => 'string',
				'default_value' => "M j, Y",
			),
			'LONG_DATE_FORMAT' => array(
				'data_type' => 'string',
				'default_value' => "F j, Y",
			),
			'FULL_DATE_FORMAT' => array(
				'data_type' => 'string',
				'default_value' => "l, F j, Y",
			),
			'DAY_MONTH_FORMAT' => array(
				'data_type' => 'string',
				'default_value' => "F j",
			),
			'DAY_SHORT_MONTH_FORMAT' => array(
				'data_type' => 'string',
				'default_value' => "M j",
			),
			'DAY_OF_WEEK_MONTH_FORMAT' => array(
				'data_type' => 'string',
				'default_value' => "l, F j",
			),
			'SHORT_DAY_OF_WEEK_MONTH_FORMAT' => array(
				'data_type' => 'string',
				'default_value' => "D, F j",
			),
			'SHORT_DAY_OF_WEEK_SHORT_MONTH_FORMAT' => array(
				'data_type' => 'string',
				'default_value' => "D, M j",
			),
			'SHORT_TIME_FORMAT' => array(
				'data_type' => 'string',
				'default_value' => "g:i a",
			),
			'LONG_TIME_FORMAT' => array(
				'data_type' => 'string',
				'default_value' => "g:i:s a",
			),
			'AM_VALUE' => array(
				'data_type' => 'string',
				'default_value' => "am",
			),
			'PM_VALUE' => array(
				'data_type' => 'string',
				'default_value' => "pm",
			),
			'NUMBER_THOUSANDS_SEPARATOR' => array(
				'data_type' => 'string',
				'default_value' => ",",
			),
			'NUMBER_DECIMAL_SEPARATOR' => array(
				'data_type' => 'string',
				'default_value' => ".",
			),
			'NUMBER_DECIMALS' => array(
				'data_type' => 'integer',
				'default_value' => 2,
			),
		);
	}

	public static function update($primary, array $data)
	{
		$result = parent::update($primary, $data);
		if(CACHED_b_lang !== false && $result->isSuccess())
		{
			$cache = \Bitrix\Main\Application::getInstance()->getManagedCache();
			$cache->cleanDir("b_lang");
		}
		return $result;
	}

	public static function delete($id)
	{
		//We know for sure that languages and sites can refer to the culture.
		//Other entities should place CultureOnBeforeDelete event handler.

		$result = new Data\DeleteResult();

		$res = LanguageTable::getList(array('filter' => array('=CULTURE_ID' => $id)));
		while(($language = $res->fetch()))
		{
			$result->addError(new ORM\EntityError(Loc::getMessage("culture_err_del_lang", array("#LID#" => $language["LID"]))));
		}

		$res = \Bitrix\Main\SiteTable::getList(array('filter' => array('=CULTURE_ID' => $id)));
		while(($site = $res->fetch()))
		{
			$result->addError(new ORM\EntityError(Loc::getMessage("culture_err_del_site", array("#LID#" => $site["LID"]))));
		}

		if(!$result->isSuccess())
		{
			return $result;
		}

		return parent::delete($id);
	}
}
