<?php

namespace Bitrix\Iblock;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Fields\StringField;
use bitrix\Main\ORM\Fields\Validators\LengthValidator;
use Bitrix\Main\ORM\Query\Join;

/**
 * Class IblockFieldTable
 *
 * Fields:
 * <ul>
 * <li> IBLOCK_ID int mandatory
 * <li> FIELD_ID string(50) mandatory
 * <li> IS_REQUIRED bool optional default 'N'
 * <li> DEFAULT_VALUE string optional
 * <li> IBLOCK reference to {@link \Bitrix\Iblock\IblockTable}
 * </ul>
 *
 * @package Bitrix\Iblock
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_IblockField_Query query()
 * @method static EO_IblockField_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_IblockField_Result getById($id)
 * @method static EO_IblockField_Result getList(array $parameters = [])
 * @method static EO_IblockField_Entity getEntity()
 * @method static \Bitrix\Iblock\EO_IblockField createObject($setDefaultValues = true)
 * @method static \Bitrix\Iblock\EO_IblockField_Collection createCollection()
 * @method static \Bitrix\Iblock\EO_IblockField wakeUpObject($row)
 * @method static \Bitrix\Iblock\EO_IblockField_Collection wakeUpCollection($rows)
 */
class IblockFieldTable extends DataManager
{
	/**
	 * Returns DB table name for entity
	 *
	 * @return string
	 */
	public static function getTableName(): string
	{
		return 'b_iblock_fields';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap(): array
	{
		return [
			'IBLOCK_ID' => (new IntegerField('IBLOCK_ID'))
				->configurePrimary(true)
				->configureTitle(Loc::getMessage('IBLOCK_FIELD_ENTITY_IBLOCK_ID_FIELD'))
			,
			'FIELD_ID' => (new StringField('FIELD_ID'))
				->configurePrimary(true)
				->addValidator(new LengthValidator(null, 50))
				->configureTitle(Loc::getMessage('IBLOCK_FIELD_ENTITY_FIELD_ID_FIELD'))
			,
			'IS_REQUIRED' => (new BooleanField('IS_REQUIRED'))
				->configureValues('N', 'Y')
				->configureTitle(Loc::getMessage('IBLOCK_FIELD_ENTITY_IS_REQUIRED_FIELD'))
			,
			'DEFAULT_VALUE' => (new StringField('DEFAULT_VALUE'))
				->configureTitle(Loc::getMessage('IBLOCK_FIELD_ENTITY_DEFAULT_VALUE_FIELD'))
			,
			'IBLOCK' => new Reference(
				'IBLOCK', IblockTable::class,
				Join::on('this.IBLOCK_ID', 'ref.ID')
			),
		];
	}
}
