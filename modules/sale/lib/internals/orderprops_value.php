<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sale
 * @copyright 2001-2014 Bitrix
 */
namespace Bitrix\Sale\Internals;

use	Bitrix\Main\Entity\DataManager,
	Bitrix\Main\Entity\Validator;
use Bitrix\Main\ORM\Fields\Validators\EnumValidator;
use Bitrix\Sale\Registry;

/**
 * Class OrderPropsValueTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_OrderPropsValue_Query query()
 * @method static EO_OrderPropsValue_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_OrderPropsValue_Result getById($id)
 * @method static EO_OrderPropsValue_Result getList(array $parameters = array())
 * @method static EO_OrderPropsValue_Entity getEntity()
 * @method static \Bitrix\Sale\Internals\EO_OrderPropsValue createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Internals\EO_OrderPropsValue_Collection createCollection()
 * @method static \Bitrix\Sale\Internals\EO_OrderPropsValue wakeUpObject($row)
 * @method static \Bitrix\Sale\Internals\EO_OrderPropsValue_Collection wakeUpCollection($rows)
 */
class OrderPropsValueTable extends DataManager
{
	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_sale_order_props_value';
	}

	public static function getMap()
	{
		return array(
			'ID' => array(
				'primary' => true,
				'autocomplete' => true,
				'data_type' => 'integer',
				'format' => '/^[0-9]{1,11}$/',
			),
			'ORDER_ID' => array(
				'data_type' => 'integer',
				'format' => '/^[0-9]{1,11}$/',
			),
			'ORDER_PROPS_ID' => array(
				'data_type' => 'integer',
				'format' => '/^[0-9]{1,11}$/',
			),
			'NAME' => array(
				'required' => true,
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'getNameValidators'),
			),
			'VALUE' => array(
				'data_type' => 'string',
				'validation'              => array('Bitrix\Sale\Internals\OrderPropsTable', 'getValueValidators'),
				'save_data_modification'  => array('Bitrix\Sale\Internals\OrderPropsTable', 'getValueSaveModifiers'),
				'fetch_data_modification' => array('Bitrix\Sale\Internals\OrderPropsTable', 'getValueFetchModifiers'),
			),
			'CODE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'getCodeValidators'),
			),

			'PROPERTY' => array(
				'data_type' => 'Bitrix\Sale\Internals\OrderPropsTable',
				'reference' => array('=this.ORDER_PROPS_ID' => 'ref.ID'),
				'join_type' => 'LEFT',
			),
			'XML_ID' => array(
				'data_type' => 'string',
			),
			'ENTITY_ID' => array(
				'data_type' => 'integer',
				'format' => '/^[0-9]{1,11}$/',
			),
			'ENTITY_TYPE' => array(
				'data_type' => 'enum',
				'required' => true,
				'validation' => array(__CLASS__, 'validateEntityType'),
				'values' => static::getEntityTypes()
			),
		);
	}

	public static function getEntityTypes()
	{
		return [
			Registry::ENTITY_ORDER,
			Registry::ENTITY_SHIPMENT,
		];
	}

	public static function validateEntityType()
	{
		return [
			new EnumValidator(),
		];
	}

	public static function getNameValidators()
	{
		return array(
			new Validator\Length(1, 255),
		);
	}

	public static function getCodeValidators()
	{
		return array(
			new Validator\Length(null, 50),
		);
	}
}
