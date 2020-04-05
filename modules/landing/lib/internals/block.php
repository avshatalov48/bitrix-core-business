<?php
namespace Bitrix\Landing\Internals;

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Entity;

Loc::loadMessages(__FILE__);

class BlockTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_landing_block';
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
			'PARENT_ID' => new Entity\IntegerField('PARENT_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_PARENT_ID')
			)),
			'LID' => new Entity\IntegerField('LID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_LID'),
				'required' => true
			)),
			'LANDING' => new Entity\ReferenceField(
				'LANDING',
				'\Bitrix\Landing\Internals\LandingTable',
				array('=this.LID' => 'ref.ID')
			),
			'CODE' => new Entity\StringField('CODE', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_CODE'),
				'required' => true
			)),
			'MANIFEST_DB' => new Entity\ReferenceField(
				'MANIFEST_DB',
				'\Bitrix\Landing\Internals\ManifestTable',
				array('=this.CODE' => 'ref.CODE')
			),
			'SORT' => new Entity\IntegerField('SORT', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_SORT')
			)),
			'ACTIVE' => new Entity\StringField('ACTIVE', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_ACTIVE'),
				'default_value' => 'Y'
			)),
			'PUBLIC' => new Entity\StringField('PUBLIC', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_PUBLIC'),
				'default_value' => 'Y'
			)),
			'DELETED' => new Entity\StringField('DELETED', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_DELETED'),
				'default_value' => 'N'
			)),
			'ACCESS' => new Entity\StringField('ACCESS', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_ACCESS'),
				'default_value' => 'X'
			)),
			'CONTENT' => new Entity\StringField('CONTENT', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_CONTENT'),
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
	 * After delete handler.
	 * @param Entity\Event $event Event instance.
	 * @return Entity\EventResult
	 */
	public static function onAfterDelete(Entity\Event $event)
	{
		$result = new Entity\EventResult();
		$primary = $event->getParameter('primary');

		// delete all inner landings
		if ($primary)
		{
			\Bitrix\Landing\File::deleteFromBlock($primary['ID']);
		}

		return $result;
	}
}