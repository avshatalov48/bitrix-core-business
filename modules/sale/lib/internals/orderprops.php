<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sale
 * @copyright 2001-2014 Bitrix
 */
namespace Bitrix\Sale\Internals;

use	Bitrix\Main\Entity\DataManager,
	Bitrix\Main\Entity\Validator,
	Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Fields\Validators\EnumValidator;
use Bitrix\Main\ORM\Event;
use \Bitrix\Main\ORM\EventResult;
use Bitrix\Sale\Registry;

Loc::loadMessages(__FILE__);

/**
 * Class OrderPropsTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_OrderProps_Query query()
 * @method static EO_OrderProps_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_OrderProps_Result getById($id)
 * @method static EO_OrderProps_Result getList(array $parameters = [])
 * @method static EO_OrderProps_Entity getEntity()
 * @method static \Bitrix\Sale\Internals\EO_OrderProps createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Internals\EO_OrderProps_Collection createCollection()
 * @method static \Bitrix\Sale\Internals\EO_OrderProps wakeUpObject($row)
 * @method static \Bitrix\Sale\Internals\EO_OrderProps_Collection wakeUpCollection($rows)
 */
class OrderPropsTable extends DataManager
{
	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_sale_order_props';
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
			'PERSON_TYPE_ID' => array(
				'required' => true,
				'data_type' => 'integer',
				'format' => '/^[0-9]{1,11}$/',
			),
			'NAME' => array(
				'required' => true,
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'getNameValidators'),
				'title' => Loc::getMessage('ORDER_PROPS_ENTITY_NAME_FIELD'),
			),
			'TYPE' => array(
				'required' => true,
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'getTypeValidators'),
			),
			'REQUIRED' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'save_data_modification'  => array(__CLASS__, 'getRequiredSaveModifiers'),
			),
			'DEFAULT_VALUE' => array(
				'data_type' => 'string',
				'validation'              => array(__CLASS__, 'getValueValidators'),
				'save_data_modification'  => array(__CLASS__, 'getValueSaveModifiers'),
				'fetch_data_modification' => array(__CLASS__, 'getValueFetchModifiers'),
				'title' => Loc::getMessage('ORDER_PROPS_ENTITY_DEFAULT_VALUE_FIELD'),
			),
			'SORT' => array(
				'data_type' => 'integer',
				'format' => '/^[0-9]{1,11}$/',
				'title' => Loc::getMessage('ORDER_PROPS_ENTITY_SORT_FIELD'),
			),
			'USER_PROPS' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
			),
			'IS_LOCATION' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
			),
			'PROPS_GROUP_ID' => array(
				'required' => true,
				'data_type' => 'integer',
				'format' => '/^[0-9]{1,11}$/',
			),
			'DESCRIPTION' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'getDescriptionValidators'),
				'title' => Loc::getMessage('ORDER_PROPS_ENTITY_DESCRIPTION_FIELD'),
			),
			'IS_EMAIL' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
			),
			'IS_PROFILE_NAME' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
			),
			'IS_PAYER' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
			),
			'IS_LOCATION4TAX' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
			),
			'IS_FILTERED' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
			),
			'CODE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'getCodeValidators'),
				'title' => Loc::getMessage('ORDER_PROPS_ENTITY_CODE_FIELD'),
			),
			'IS_ZIP' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
			),
			'IS_PHONE' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
			),
			'IS_ADDRESS' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
			),
			'IS_ADDRESS_FROM' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
			),
			'IS_ADDRESS_TO' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
			),
			'ACTIVE' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
			),
			'UTIL' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
			),
			'INPUT_FIELD_LOCATION' => array(
				'data_type' => 'integer',
				'format' => '/^[0-9]{1,11}$/',
			),
			'MULTIPLE' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
			),
			'SETTINGS' => array(
				'data_type' => 'string',
				'validation'              => array(__CLASS__, 'getSettingsValidators'),
				'save_data_modification'  => array(__CLASS__, 'getSettingsSaveModifiers'),
				'fetch_data_modification' => array(__CLASS__, 'getSettingsFetchModifiers'),
			),

			'GROUP' => array(
				'data_type' => 'Bitrix\Sale\Internals\OrderPropsGroupTable',
				'reference' => array('=this.PROPS_GROUP_ID' => 'ref.ID'),
				'join_type' => 'LEFT',
			),
			'PERSON_TYPE' => array(
				'data_type' => 'Bitrix\Sale\Internals\PersonTypeTable',
				'reference' => array('=this.PERSON_TYPE_ID' => 'ref.ID'),
				'join_type' => 'LEFT',
			),
			'ENTITY_REGISTRY_TYPE' => array(
				'data_type' => 'string',
			),
			'XML_ID' => array(
				'data_type' => 'string',
			),
			'ENTITY_TYPE' => array(
				'data_type' => 'enum',
				'default_value' => Registry::ENTITY_ORDER,
				'required' => true,
				'validation' => array(__CLASS__, 'validateEntityType'),
				'values' => static::getEntityTypes()
			),
		);
	}

	public static function onBeforeAdd(Event $event): EventResult
	{
		$result = new EventResult;
		$fields = $event->getParameter('fields');

		$modifyFieldList = [];
		if (isset($fields['IS_FILTERED']))
		{
			$multiple = $fields['MULTIPLE'] ?? 'N';
			if (
				$multiple === 'Y'
				&& $fields['IS_FILTERED'] !== 'N'
			)
			{
				$modifyFieldList['IS_FILTERED'] = 'N';
			}
		}

		if (!empty($modifyFieldList))
		{
			$result->modifyFields($modifyFieldList);
		}

		return $result;
	}

	public static function onBeforeUpdate(Event $event): EventResult
	{
		$result = new EventResult;
		$fields = $event->getParameter('fields');

		$modifyFieldList = [];
		if (isset($fields['IS_FILTERED']) || isset($fields['MULTIPLE']))
		{
			$multiple = null;
			$filtered = null;
			if (isset($fields['MULTIPLE']))
			{
				$multiple = $fields['MULTIPLE'];
			}
			if (isset($fields['IS_FILTERED']))
			{
				$filtered = $fields['IS_FILTERED'];
			}

			if ($multiple === null || $filtered === null)
			{
				$primary = $event->getParameter('primary');
				$row = static::getRow([
					'select' => [
						'ID',
						'MULTIPLE',
						'IS_FILTERED'
					],
					'filter' => $primary,
				]);
				if ($row)
				{
					$multiple ??= $row['MULTIPLE'];
					$filtered ??= $row['IS_FILTERED'];
				}
			}
			if (
				$multiple === 'Y'
				&& $filtered !== 'N'
			)
			{
				$modifyFieldList['IS_FILTERED'] = 'N';
			}
		}

		if (!empty($modifyFieldList))
		{
			$result->modifyFields($modifyFieldList);
		}

		return $result;
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

	// value

	public static function getValueValidators()
	{
		return array(array(__CLASS__, 'validateValue'));
	}
	public static function validateValue($value, $primary, array $row, $field)
	{
		$maxlength = 500;

		$valueForSave = self::modifyValueForSave($value, $row);
		$length = isset($valueForSave) ? mb_strlen($valueForSave) : 0;

		return $length > $maxlength
			? Loc::getMessage('SALE_ORDER_PROPS_DEFAULT_ERROR', array('#PROPERTY_NAME#'=> $row['NAME'],'#FIELD_LENGTH#' => $length, '#MAX_LENGTH#' => $maxlength))
			: true;
	}

	public static function getValueSaveModifiers()
	{
		return array(array(__CLASS__, 'modifyValueForSave'));
	}
	public static function modifyValueForSave($value)
	{
		return is_array($value) ? serialize($value) : $value;
	}

	public static function getValueFetchModifiers()
	{
		return array(array(__CLASS__, 'modifyValueForFetch'));
	}
	public static function modifyValueForFetch($value, $query, $property, $alias)
	{
		if (!is_string($value) || $value === '')
		{
			return $value;
		}

		if (self::isSerialized($value))
		{
			if (
				CheckSerializedData($value)
			)
			{
				$value = unserialize($value, ['allowed_classes' => false]);
			}
		}
		elseif (isset($property['MULTIPLE']) && $property['MULTIPLE'] == 'Y') // compatibility
		{
			switch($property['TYPE'])
			{
				case 'ENUM':
					$value = explode(',', $value);
					break;
				case 'FILE':
					$value = explode(', ', $value);
					break;
			}
		}

		return $value;
	}

	// filtered

	/**
	 * @deprecated
	 *
	 * @return array[]
	 */
	public static function getFilteredSaveModifiers()
	{
		return array(array(__CLASS__, 'modifyFilteredForSave'));
	}

	/**
	 * @deprecated
	 *
	 * @param $value
	 * @param array $data
	 * @return string
	 */
	public static function modifyFilteredForSave($value, array $data)
	{
		return $data['MULTIPLE'] == 'Y' ? 'N' : $value;
	}

	// settings

	public static function getSettingsValidators()
	{
		return array(array(__CLASS__, 'validateSettings'));
	}
	public static function validateSettings($value)
	{
		$maxlength = 500;
		$length = mb_strlen(self::modifySettingsForSave($value));
		return $length > $maxlength
			? Loc::getMessage('SALE_ORDER_PROPS_SETTINGS_ERROR', array('#LENGTH#' => $length, '#MAXLENGTH#' => $maxlength))
			: true;
	}

	public static function getSettingsSaveModifiers()
	{
		return array(array(__CLASS__, 'modifySettingsForSave'));
	}
	public static function modifySettingsForSave($value)
	{
		return serialize($value);
	}

	public static function getSettingsFetchModifiers()
	{
		return array(array(__CLASS__, 'modifySettingsForFetch'));
	}
	public static function modifySettingsForFetch($value)
	{
		if (empty($value))
		{
			return [];
		}

		$v = @unserialize($value, ['allowed_classes' => false]);
		return is_array($v) ? $v : array();
	}

	// required

	public static function getRequiredSaveModifiers()
	{
		return array(array(__CLASS__, 'modifyRequiredForSave'));
	}
	public static function modifyRequiredForSave ($value, array $property)
	{
		$isProfileName = isset($property['IS_PROFILE_NAME']) && $property['IS_PROFILE_NAME'] === 'Y';
		$isLocation = isset($property['IS_LOCATION']) && $property['IS_LOCATION'] === 'Y';
		$isLocation4Tax = isset($property['IS_LOCATION4TAX']) && $property['IS_LOCATION4TAX'] === 'Y';
		$isPayer = isset($property['IS_PAYER']) && $property['IS_PAYER'] === 'Y';
		$isZip = isset($property['IS_ZIP']) && $property['IS_ZIP'] === 'Y';

		if ($value == 'Y' || $isProfileName || $isLocation || $isLocation4Tax || $isPayer || $isZip)
		{
			return 'Y';
		}

		return 'N';
	}

	// validators

	public static function getNameValidators()
	{
		return array(new Validator\Length(1, 255));
	}

	public static function getTypeValidators()
	{
		return array(new Validator\Length(1, 20));
	}

	public static function getDescriptionValidators()
	{
		return array(new Validator\Length(null, 255));
	}

	public static function getCodeValidators()
	{
		return array(new Validator\Length(null, 50));
	}

	public static function generateXmlId()
	{
		return uniqid('bx_');
	}

	private static function isSerialized(string $data): bool
	{
		$data = trim( $data );
		if ($data === 'N;')
		{
			return true;
		}
		if (strlen($data) < 4)
		{
			return false;
		}
		if (substr($data, 1, 1) !== ':')
		{
			return false;
		}

		$last = substr( $data, -1 );
		if ($last !== ';' && $last !== '}')
		{
			return false;
		}

		$token = substr($data, 0,1);
		if (
			$token === 's'
			&& substr( $data, -2, 1) !== '"'
			)
		{
			return false;
		}
		switch ($token)
		{
			case 's':
			case 'a':
			case 'O':
			case 'E':
				return (bool) preg_match("/^{$token}:[0-9]+:/s", $data);
			case 'b':
			case 'i':
			case 'd':
				return (bool) preg_match("/^{$token}:[0-9.E+-]+;$/", $data);
		}

		return false;
	}
}
