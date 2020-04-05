<?php
namespace Bitrix\Landing\Internals;

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Entity;

Loc::loadMessages(__FILE__);

class BindingTable extends Entity\DataManager
{
	/**
	 * Entity type 'SITE'.
	 */
	const ENTITY_TYPE_SITE = 'S';

	/**
	 * Entity type 'LANDING'.
	 */
	const ENTITY_TYPE_LANDING = 'L';

	/**
	 * Returns DB table name for entity.
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_landing_binding';
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
			'ENTITY_ID' => new Entity\IntegerField('ENTITY_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_ENTITY_ID'),
				'required' => true
			)),
			'ENTITY_TYPE' => new Entity\StringField('ENTITY_TYPE', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_ENTITY_TYPE'),
				'required' => true
			)),
			'BINDING_ID' => new Entity\StringField('BINDING_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_BINDING_ID'),
				'required' => true
			)),
			'BINDING_TYPE' => new Entity\StringField('BINDING_TYPE', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_BINDING_TYPE'),
				'required' => true
			))
		);
	}

	/**
	 * Clear records by entity.
	 * @param int $entityId Entity id.
	 * @param string $entityType Entity type.
	 * @return void
	 */
	protected static function entityClear($entityId, $entityType)
	{
		$res = self::getList([
			'select' => [
				'ID'
			],
			'filter' => [
				'=ENTITY_TYPE' => $entityType,
				'ENTITY_ID' => $entityId
			]
		]);
		while ($row = $res->fetch())
		{
			self::delete($row['ID'])->isSuccess();
		}
	}

	/**
	 * Clear all records for site.
	 * @param int $siteId Site id.
	 * @return void
	 */
	public static function siteClear($siteId)
	{
		self::entityClear((int) $siteId, self::ENTITY_TYPE_SITE);
	}
}
