<?php
namespace Bitrix\Catalog;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc,
	Bitrix\Main\ORM,
	Bitrix\Main\Type;
Loc::loadMessages(__FILE__);

/**
 * Class DiscountEntityTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> DISCOUNT_ID int mandatory
 * <li> MODULE_ID string(50) mandatory
 * <li> ENTITY string(255) mandatory
 * <li> ENTITY_ID int optional
 * <li> ENTITY_VALUE string(255) optional
 * <li> FIELD_ENTITY string(255) mandatory
 * <li> FIELD_TABLE string(255) mandatory
 * </ul>
 *
 * @package Bitrix\Catalog
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_DiscountEntity_Query query()
 * @method static EO_DiscountEntity_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_DiscountEntity_Result getById($id)
 * @method static EO_DiscountEntity_Result getList(array $parameters = [])
 * @method static EO_DiscountEntity_Entity getEntity()
 * @method static \Bitrix\Catalog\EO_DiscountEntity createObject($setDefaultValues = true)
 * @method static \Bitrix\Catalog\EO_DiscountEntity_Collection createCollection()
 * @method static \Bitrix\Catalog\EO_DiscountEntity wakeUpObject($row)
 * @method static \Bitrix\Catalog\EO_DiscountEntity_Collection wakeUpCollection($rows)
 */

class DiscountEntityTable extends ORM\Data\DataManager
{
	const ENTITY_ELEMENT = 'ELEMENT';
	const ENTITY_ELEMENT_PROPERTY = 'ELEMENT_PROPERTY';
	const ENTITY_PRODUCT = 'PRODUCT';

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_catalog_discount_entity';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			'ID' => new ORM\Fields\IntegerField('ID', [
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage('DISCOUNT_ENTITY_ENTITY_ID_FIELD')
			]),
			'DISCOUNT_ID' => new ORM\Fields\IntegerField('DISCOUNT_ID', [
				'required' => true,
				'title' => Loc::getMessage('DISCOUNT_ENTITY_ENTITY_DISCOUNT_ID_FIELD')
			]),
			'MODULE_ID' => new ORM\Fields\StringField('MODULE_ID', [
				'required' => true,
				'validation' => [__CLASS__, 'validateModuleId'],
				'title' => Loc::getMessage('DISCOUNT_ENTITY_ENTITY_MODULE_ID_FIELD')
			]),
			'ENTITY' => new ORM\Fields\StringField('ENTITY', [
				'required' => true,
				'validation' => [__CLASS__, 'validateEntity'],
				'title' => Loc::getMessage('DISCOUNT_ENTITY_ENTITY_ENTITY_FIELD')
			]),
			'ENTITY_ID' => new ORM\Fields\IntegerField('ENTITY_ID', [
				'title' => Loc::getMessage('DISCOUNT_ENTITY_ENTITY_ENTITY_ID_FIELD')
			]),
			'ENTITY_VALUE' => new ORM\Fields\StringField('ENTITY_VALUE', [
				'title' => Loc::getMessage('DISCOUNT_ENTITY_ENTITY_ENTITY_VALUE_FIELD')
			]),
			'FIELD_ENTITY' => new ORM\Fields\StringField('FIELD_ENTITY', [
				'required' => true,
				'validation' => [__CLASS__, 'validateFieldEntity'],
				'title' => Loc::getMessage('DISCOUNT_ENTITY_ENTITY_FIELD_ENTITY_FIELD')
			]),
			'FIELD_TABLE' => new ORM\Fields\StringField('FIELD_TABLE', [
				'required' => true,
				'validation' => [__CLASS__, 'validateFieldTable'],
				'title' => Loc::getMessage('DISCOUNT_ENTITY_ENTITY_FIELD_TABLE_FIELD')
			]),
			'DISCOUNT' => new ORM\Fields\Relations\Reference(
				'DISCOUNT',
				'\Bitrix\Catalog\Discount',
				['=this.DISCOUNT_ID' => 'ref.ID']
			)
		];
	}

	/**
	 * Returns validators for MODULE_ID field.
	 *
	 * @return array
	 */
	public static function validateModuleId()
	{
		return [
			new ORM\Fields\Validators\LengthValidator(null, 50)
		];
	}

	/**
	 * Returns validators for ENTITY field.
	 *
	 * @return array
	 */
	public static function validateEntity()
	{
		return [
			new ORM\Fields\Validators\LengthValidator(null, 255)
		];
	}

	/**
	 * Returns validators for FIELD_ENTITY field.
	 *
	 * @return array
	 */
	public static function validateFieldEntity()
	{
		return [
			new ORM\Fields\Validators\LengthValidator(null, 255)
		];
	}

	/**
	 * Returns validators for FIELD_TABLE field.
	 *
	 * @return array
	 */
	public static function validateFieldTable()
	{
		return [
			new ORM\Fields\Validators\LengthValidator(null, 255)
		];
	}

	/**
	 * Delete entity list by discount.
	 *
	 * @param int $discount			Discount id.
	 * @return void
	 */
	public static function deleteByDiscount($discount)
	{
		$discount = (int)$discount;
		if ($discount <= 0)
			return;
		$conn = Main\Application::getConnection();
		$helper = $conn->getSqlHelper();
		$conn->queryExecute(
			'delete from '.$helper->quote(self::getTableName()).' where '.$helper->quote('DISCOUNT_ID').' = '.$discount
		);
		unset($helper, $conn);
	}

	/**
	 * Return entity by discount list.
	 *
	 * @param array $discountList			Discount id list.
	 * @param array $filter				Additional filter.
	 * @param bool $groupModule			Group by modules.
	 * @return array
	 */
	public static function getByDiscount(array $discountList, $filter = [], $groupModule = true)
	{
		$groupModule = ($groupModule === true);
		$result = [];
		if (!empty($discountList))
		{
			Type\Collection::normalizeArrayValuesByInt($discountList);
			if (!empty($discountList))
			{
				if (!is_array($filter))
					$filter = [];

				$discountRows = array_chunk($discountList, 500);
				foreach ($discountRows as &$row)
				{
					$filter['@DISCOUNT_ID'] = $row;
					$entityIterator = self::getList([
						'select' => [
							'DISCOUNT_ID', 'MODULE_ID',
							'ENTITY', 'ENTITY_ID', 'ENTITY_VALUE', 'FIELD_ENTITY', 'FIELD_TABLE'
						],
						'filter' => $filter
					]);
					if ($groupModule)
					{
						while ($entity = $entityIterator->fetch())
						{
							unset($entity['DISCOUNT_ID']);
							$module = $entity['MODULE_ID'];
							$entityCode = $entity['ENTITY'];
							if (!isset($result[$module]))
								$result[$module] = [];
							if (!isset($result[$module][$entityCode]))
								$result[$module][$entityCode] = [];
							$result[$module][$entityCode][$entity['FIELD_ENTITY']] = $entity;
						}
						unset($entityCode, $module);
					}
					else
					{
						while ($entity = $entityIterator->fetch())
						{
							$entity['DISCOUNT_ID'] = (int)$entity['DISCOUNT_ID'];
							if (!isset($result[$entity['DISCOUNT_ID']]))
								$result[$entity['DISCOUNT_ID']] = [];
							$result[$entity['DISCOUNT_ID']][] = $entity;
						}
					}
					unset($entity, $entityIterator);
				}
				unset($row, $discountRows);
			}
		}
		return $result;
	}
}