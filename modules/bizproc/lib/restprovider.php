<?php
namespace Bitrix\Bizproc;

use Bitrix\Main\Entity;

/**
 * Class RestProviderTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> APP_ID string(128) mandatory
 * <li> APP_NAME text mandatory
 * <li> CODE string(128) mandatory
 * <li> TYPE(30) string mandatory
 * <li> HANDLER string(1000) mandatory
 * <li> NAME text mandatory
 * <li> DESCRIPTION text optional
 * </ul>
 *
 * @package Bitrix\Bizproc
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_RestProvider_Query query()
 * @method static EO_RestProvider_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_RestProvider_Result getById($id)
 * @method static EO_RestProvider_Result getList(array $parameters = [])
 * @method static EO_RestProvider_Entity getEntity()
 * @method static \Bitrix\Bizproc\EO_RestProvider createObject($setDefaultValues = true)
 * @method static \Bitrix\Bizproc\EO_RestProvider_Collection createCollection()
 * @method static \Bitrix\Bizproc\EO_RestProvider wakeUpObject($row)
 * @method static \Bitrix\Bizproc\EO_RestProvider_Collection wakeUpCollection($rows)
 */
class RestProviderTable extends Entity\DataManager
{
	const TYPE_SMS = 'SMS';

	/**
	 * Returns DB table name for entity
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_bp_rest_provider';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'APP_ID' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateAppId'),
			),
			'APP_NAME' => array(
				'data_type' => 'text',
				'required' => true,
				'serialized' => true,
				'save_data_modification'  => array(__CLASS__, 'getLocalizationSaveModifiers'),
			),
			'CODE' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateCode'),
			),
			'TYPE' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateType'),
			),
			'HANDLER' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateHandler'),
			),
			'NAME' => array(
				'data_type' => 'text',
				'required' => true,
				'serialized' => true,
				'save_data_modification'  => array(__CLASS__, 'getLocalizationSaveModifiers'),
			),
			'DESCRIPTION' => array(
				'data_type' => 'text',
				'serialized' => true,
				'save_data_modification'  => array(__CLASS__, 'getLocalizationSaveModifiers'),
			),
		);
	}

	/**
	 * Returns validators for APP_ID field.
	 *
	 * @return array
	 */
	public static function validateAppId()
	{
		return array(
			new Entity\Validator\Length(null, 128),
		);
	}

	/**
	 * Returns validators for CODE field.
	 *
	 * @return array
	 */
	public static function validateCode()
	{
		return array(
			new Entity\Validator\Length(null, 128),
		);
	}

	/**
	 * Returns validators for TYPE field.
	 *
	 * @return array
	 */
	public static function validateType()
	{
		return array(
			new Entity\Validator\Length(null, 30),
		);
	}

	/**
	 * Returns validators for HANDLER field.
	 *
	 * @return array
	 */
	public static function validateHandler()
	{
		return array(
			new Entity\Validator\Length(null, 1000),
		);
	}

	/**
	 * @return array Array of callbacks.
	 */
	public static function getLocalizationSaveModifiers()
	{
		return array(array(__CLASS__, 'prepareLocalization'));
	}

	/**
	 * @param mixed $value Original value.
	 * @return array Array to serialize.
	 */
	public static function prepareLocalization($value)
	{
		if (!is_array($value))
			$value = array('*' => (string) $value);
		return $value;
	}

	/**
	 * @param mixed $field Activity field value.
	 * @param string $langId Language ID.
	 * @return string
	 */
	public static function getLocalization($field, $langId)
	{
		$result = '';
		$langId = mb_strtoupper($langId);
		if (is_string($field))
			$result = $field;
		elseif (!empty($field[$langId]))
			$result = $field[$langId];
		elseif ($langId == 'UA' && !empty($field['RU']))
			$result = $field['RU'];
		elseif (!empty($field['EN']))
			$result = $field['EN'];
		elseif (!empty($field['*']))
			$result = $field['*'];
		return $result;
	}

	/**
	 * Get supported provider types.
	 * @return array
	 */
	public static function getTypesList()
	{
		return array(static::TYPE_SMS);
	}
}
