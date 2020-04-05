<?php
namespace Bitrix\Landing\Internals;

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Entity;
use \Bitrix\Landing\Manager;

Loc::loadMessages(__FILE__);

class UrlRewriteTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_landing_urlrewrite';
	}

	/**
	 * Returns entity map definition.
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => new Entity\IntegerField('ID', array(
				'primary' => true,
				'autocomplete' => true,
				'title' => 'ID'
			)),
			'SITE_ID' => new Entity\IntegerField('SITE_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_SITE_ID'),
				'required' => true
			)),
			'RULE' => new Entity\StringField('RULE', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_RULE'),
				'required' => true
			)),
			'LANDING_ID' => new Entity\IntegerField('LANDING_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_LANDING_ID'),
				'required' => true
			)),
			'CREATED_BY_ID' => new Entity\IntegerField('CREATED_BY_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_CREATED_BY_ID'),
				'required' => true
			)),
			'MODIFIED_BY_ID' => new Entity\IntegerField('MODIFIED_BY_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_MODIFIED_BY_ID'),
				'required' => true
			)),
			'DATE_CREATE' => new Entity\DatetimeField('DATE_CREATE', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_DATE_CREATE'),
				'required' => true
			)),
			'DATE_MODIFY' => new Entity\DatetimeField('DATE_MODIFY', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_DATE_MODIFY'),
				'required' => true
			))
		);
	}

	/**
	 * Create new record and return it new id.
	 * @param array $fields Fields array.
	 * @return \Bitrix\Main\ORM\Data\AddResult
	 */
	public static function add(array $fields)
	{
		$uid = Manager::getUserId();
		$uid = $uid ? $uid : 1;
		$date = new \Bitrix\Main\Type\DateTime;

		if (!isset($fields['CREATED_BY_ID']))
		{
			$fields['CREATED_BY_ID'] = $uid;
		}
		if (!isset($fields['MODIFIED_BY_ID']))
		{
			$fields['MODIFIED_BY_ID'] = $uid;
		}
		if (!isset($fields['DATE_CREATE']))
		{
			$fields['DATE_CREATE'] = $date;
		}
		if (!isset($fields['DATE_MODIFY']))
		{
			$fields['DATE_MODIFY'] = $date;
		}

		return parent::add($fields);
	}

	/**
	 * Update record.
	 * @param int $id Record key.
	 * @param array $fields Fields array.
	 * @return \Bitrix\Main\Result
	 */
	public static function update($id, array $fields = array())
	{
		$uid = Manager::getUserId();
		$date = new \Bitrix\Main\Type\DateTime;

		if (!isset($fields['MODIFIED_BY_ID']))
		{
			$fields['MODIFIED_BY_ID'] = $uid;
		}
		if (!isset($fields['DATE_MODIFY']))
		{
			$fields['DATE_MODIFY'] = $date;
		}
		if (!$fields['DATE_MODIFY'])
		{
			unset($fields['DATE_MODIFY']);
		}

		return parent::update($id, $fields);
	}
}