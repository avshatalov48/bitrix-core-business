<?php
namespace Bitrix\Catalog;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/**
 * Class VatTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> TIMESTAMP_X datetime mandatory default 'CURRENT_TIMESTAMP'
 * <li> ACTIVE bool optional default 'Y'
 * <li> SORT int optional default 100
 * <li> NAME string(50) mandatory
 * <li> RATE double mandatory default 0.00
 * </ul>
 *
 * @package Bitrix\Catalog
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Vat_Query query()
 * @method static EO_Vat_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Vat_Result getById($id)
 * @method static EO_Vat_Result getList(array $parameters = array())
 * @method static EO_Vat_Entity getEntity()
 * @method static \Bitrix\Catalog\EO_Vat createObject($setDefaultValues = true)
 * @method static \Bitrix\Catalog\EO_Vat_Collection createCollection()
 * @method static \Bitrix\Catalog\EO_Vat wakeUpObject($row)
 * @method static \Bitrix\Catalog\EO_Vat_Collection wakeUpCollection($rows)
 */

class VatTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_catalog_vat';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => new Main\Entity\IntegerField('ID', array(
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage('VAT_ENTITY_ID_FIELD'),
			)),
			'TIMESTAMP_X' => new Main\Entity\DatetimeField('TIMESTAMP_X', array(
				'required' => true,
				'default_value' => function(){ return new Main\Type\DateTime(); },
				'title' => Loc::getMessage('VAT_ENTITY_TIMESTAMP_X_FIELD'),
			)),
			'ACTIVE' => new Main\Entity\BooleanField('ACTIVE', array(
				'values' => array('N', 'Y'),
				'default_value' => 'Y',
				'title' => Loc::getMessage('VAT_ENTITY_ACTIVE_FIELD'),
			)),
			'SORT' => new Main\Entity\IntegerField('SORT', array(
				'column_name' => 'C_SORT',
				'default_value' => 100,
				'title' => Loc::getMessage('VAT_ENTITY_SORT_FIELD'),
			)),
			'NAME' => new Main\Entity\StringField('NAME',  array(
				'required' => true,
				'validation' => array(__CLASS__, 'validateName'),
				'title' => Loc::getMessage('VAT_ENTITY_NAME_FIELD'),
			)),
			'RATE' => new Main\Entity\FloatField('RATE', array(
				'required' => true,
				'title' => Loc::getMessage('VAT_ENTITY_RATE_FIELD'),
			)),
		);
	}
	/**
	 * Returns validators for NAME field.
	 *
	 * @return array
	 */
	public static function validateName()
	{
		return array(
			new Main\Entity\Validator\Length(null, 50),
		);
	}

	/**
	 * Returns the ID of the active VAT rate for the specified value. If necessary, it creates a new VAT rate.
	 *
	 * @param float $rate Vat rate value.
	 * @param bool $create Create new vat, if not exists.
	 * @return int|null
	 */
	public static function getActiveVatIdByRate(float $rate, bool $create = false): ?int
	{
		if ($rate < 0 || $rate > 100)
		{
			return null;
		}
		$row = static::getList([
			'select' => ['ID'],
			'filter' => [
				'=ACTIVE' => 'Y',
				'=RATE' => $rate,
			]
		])->fetch();
		if (!empty($row))
		{
			return (int)$row['ID'];
		}

		if ($create)
		{
			$result = static::add([
				'ACTIVE' => 'Y',
				'NAME' => $rate . '%',
				'RATE' => $rate,
			]);

			return $result->isSuccess() ? (int)$result->getId() : null;
		}

		return null;
	}
}