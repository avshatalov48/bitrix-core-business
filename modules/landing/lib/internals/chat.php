<?php
namespace Bitrix\Landing\Internals;

use \Bitrix\Main\Entity;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class ChatTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_landing_chat';
	}

	/**
	 * Returns entity map definition.
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => new Entity\IntegerField('ID', array(
				'title' => 'ID',
				'primary' => true
			)),
			'CHAT_ID' => new Entity\IntegerField('CHAT_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_CHAT_ID'),
				'required' => true
			)),
			'TITLE' => new Entity\StringField('TITLE', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_CHAT_TITLE'),
				'required' => true
			)),
			'AVATAR' => new Entity\IntegerField('AVATAR', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_CHAT_AVATAR')
			)),
			'CREATED_BY_ID' => new Entity\IntegerField('CREATED_BY_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_CREATED_BY_ID'),
				'required' => true
			)),
			'CREATED_BY' => new Entity\ReferenceField(
				'CREATED_BY',
				'Bitrix\Main\UserTable',
				array('=this.CREATED_BY_ID' => 'ref.ID')
			),
			'MODIFIED_BY_ID' => new Entity\IntegerField('MODIFIED_BY_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_MODIFIED_BY_ID'),
				'required' => true
			)),
			'MODIFIED_BY' => new Entity\ReferenceField(
				'MODIFIED_BY',
				'Bitrix\Main\UserTable',
				array('=this.MODIFIED_BY_ID' => 'ref.ID')
			),
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
}