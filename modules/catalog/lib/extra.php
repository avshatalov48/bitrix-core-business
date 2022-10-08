<?php

namespace Bitrix\Catalog;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM;
use Bitrix\Catalog\Model;

/**
 * Class ExtraTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> NAME string(50) mandatory
 * <li> PERCENTAGE double mandatory
 * </ul>
 *
 * @package Bitrix\Catalog
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Extra_Query query()
 * @method static EO_Extra_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Extra_Result getById($id)
 * @method static EO_Extra_Result getList(array $parameters = [])
 * @method static EO_Extra_Entity getEntity()
 * @method static \Bitrix\Catalog\EO_Extra createObject($setDefaultValues = true)
 * @method static \Bitrix\Catalog\EO_Extra_Collection createCollection()
 * @method static \Bitrix\Catalog\EO_Extra wakeUpObject($row)
 * @method static \Bitrix\Catalog\EO_Extra_Collection wakeUpCollection($rows)
 */

class ExtraTable extends ORM\Data\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_catalog_extra';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			'ID' => new ORM\Fields\IntegerField(
				'ID',
				[
					'primary' => true,
					'autocomplete' => true,
					'title' => Loc::getMessage('EXTRA_ENTITY_ID_FIELD'),
				]
			),
			'NAME' => new ORM\Fields\StringField(
				'NAME',
				[
					'required' => true,
					'validation' =>  function()
					{
						return [
							new ORM\Fields\Validators\LengthValidator(null, 50),
						];
					},
					'title' => Loc::getMessage('EXTRA_ENTITY_NAME_FIELD'),
				]
			),
			'PERCENTAGE' => new ORM\Fields\FloatField(
				'PERCENTAGE',
				[
					'required' => true,
					'title' => Loc::getMessage('EXTRA_ENTITY_PERCENTAGE_FIELD'),
				]
			)
		];
	}

	/**
	 * Default onAfterAdd handler. Absolutely necessary.
	 *
	 * @param ORM\Event $event Current data for add.
	 * @return void
	 */
	public static function onAfterAdd(ORM\Event $event): void
	{
		Model\Price::clearSettings();
	}

	/**
	 * Default onAfterUpdate handler. Absolutely necessary.
	 *
	 * @param ORM\Event $event Current data for add.
	 * @return void
	 */
	public static function onAfterUpdate(ORM\Event $event): void
	{
		Model\Price::clearSettings();
	}

	/**
	 * Default onAfterDelete handler. Absolutely necessary.
	 *
	 * @param ORM\Event $event Current data for add.
	 * @return void
	 */
	public static function onAfterDelete(ORM\Event $event): void
	{
		Model\Price::clearSettings();
	}

	/**
	 * Returns cached extra list.
	 *
	 * @return array
	 */
	public static function getExtraList(): array
	{
		$result = [];

		$iterator = self::getList([
			'select' => [
				'ID',
				'NAME',
				'PERCENTAGE',
			],
			'order' => [
				'ID' => 'ASC',
			],
			'cache' => [
				'ttl' => 86400,
			],
		]);
		while ($row = $iterator->fetch())
		{
			$row['ID'] = (int)$row['ID'];
			$row['PERCENTAGE'] = (float)$row['PERCENTAGE'];

			$result[$row['ID']] = $row;
		}
		unset($row, $iterator);

		return $result;
	}
}
