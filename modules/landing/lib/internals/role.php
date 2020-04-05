<?php
namespace Bitrix\Landing\Internals;

use \Bitrix\Main\Entity;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class RoleTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_landing_role';
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
			'TITLE' => new Entity\StringField('TITLE', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_ROLE_TITLE')
			)),
			'XML_ID' => new Entity\StringField('XML_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_ROLE_XML_ID')
			)),
			'TYPE' => new Entity\StringField('TYPE', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_ROLE_TYPE')
			)),
			'ACCESS_CODES' => new Entity\StringField('ACCESS_CODES', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_ROLE_ACCESS_CODES'),
				'serialized' => true
			)),
			'ADDITIONAL_RIGHTS' => new Entity\StringField('ADDITIONAL_RIGHTS', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_ROLE_ADDITIONAL_RIGHTS'),
				'serialized' => true
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
			$res = RightsTable::getList(array(
				'select' => array(
					'ID'
				),
				'filter' => array(
					'ROLE_ID' => $primary['ID']
				)
			));
			while ($row = $res->fetch())
			{
				RightsTable::delete($row['ID']);
			}
		}

		\Bitrix\Landing\Rights::refreshAdditionalRights();

		return $result;
	}

	/**
	 * Before add handler.
	 * @param Entity\Event $event Event instance.
	 * @return Entity\EventResult
	 */
	public static function OnBeforeAdd(Entity\Event $event)
	{
		$result = new Entity\EventResult();
		$fields = $event->getParameter('fields');

		// check title
		if (
			(
				!isset($fields['TITLE']) ||
				!trim($fields['TITLE'])
			) &&
			(
				!isset($fields['XML_ID']) ||
				!trim($fields['XML_ID'])
			)
		)
		{
			$result->setErrors(array(
				new Entity\EntityError(
					Loc::getMessage('LANDING_TABLE_ERROR_TITLE_REQUIRED'),
					'TITLE_REQUIRED'
				)
			));
		}

		$result->modifyFields([
			'TYPE' => \Bitrix\Landing\Site\Type::getFilterType(true)
	  	]);

		return $result;
	}

	/**
	 * Before update handler.
	 * @param Entity\Event $event Event instance.
	 * @return Entity\EventResult
	 */
	public static function OnBeforeUpdate(Entity\Event $event)
	{
		$result = new Entity\EventResult();
		$primary = $event->getParameter('primary');
		$fields = $event->getParameter('fields');

		// check title
		if (
			$primary['ID'] &&
			(
				isset($fields['TITLE']) &&
				!trim($fields['TITLE'])
			)
		)
		{
			$row = self::getList([
				'filter' => [
					'ID' => $primary['ID']
				]
			])->fetch();
			if (
				$row &&
				!trim($row['XML_ID'])
			)
			{
				$result->setErrors(array(
					new Entity\EntityError(
						Loc::getMessage('LANDING_TABLE_ERROR_TITLE_REQUIRED'),
						'TITLE_REQUIRED'
					)
				));
			}
			unset($primary, $fields, $row);

			return $result;
		}

		unset($primary, $fields);

		return $result;
	}
}