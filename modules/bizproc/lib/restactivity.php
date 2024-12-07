<?php
namespace Bitrix\Bizproc;

use Bitrix\Main\Entity;
use Bitrix\Main\ORM\Fields\Field;

/**
 * Class RestActivityTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> APP_ID string(128) mandatory
 * <li> APP_NAME text mandatory
 * <li> CODE string(128) mandatory
 * <li> INTERNAL_CODE(32) string mandatory
 * <li> HANDLER string(1000) mandatory
 * <li> AUTH_USER_ID int optional default 0
 * <li> USE_SUBSCRIPTION bool optional default ''
 * <li> NAME text mandatory
 * <li> DESCRIPTION text optional
 * <li> PROPERTIES text optional
 * <li> RETURN_PROPERTIES text optional
 * <li> DOCUMENT_TYPE text optional
 * <li> FILTER text optional
 * <li> IS_ROBOT bool optional default 'N'
 * </ul>
 *
 * @package Bitrix\Bizproc
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_RestActivity_Query query()
 * @method static EO_RestActivity_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_RestActivity_Result getById($id)
 * @method static EO_RestActivity_Result getList(array $parameters = [])
 * @method static EO_RestActivity_Entity getEntity()
 * @method static \Bitrix\Bizproc\EO_RestActivity createObject($setDefaultValues = true)
 * @method static \Bitrix\Bizproc\EO_RestActivity_Collection createCollection()
 * @method static \Bitrix\Bizproc\EO_RestActivity wakeUpObject($row)
 * @method static \Bitrix\Bizproc\EO_RestActivity_Collection wakeUpCollection($rows)
 */
class RestActivityTable extends Entity\DataManager
{
	/**
	 * Returns path to the file which contains definition of the class.
	 *
	 * @return string
	 */
	public static function getFilePath()
	{
		return __FILE__;
	}

	/**
	 * Returns DB table name for entity
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_bp_rest_activity';
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
			'INTERNAL_CODE' => array(
				'data_type' => 'string',
				'required' => true,
			),
			'HANDLER' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateHandler'),
			),
			'AUTH_USER_ID' => array(
				'data_type' => 'integer',
				'default_value' => 0
			),
			'USE_SUBSCRIPTION' => array(
				'data_type' => 'string'
			),
			'USE_PLACEMENT' => array(
				'data_type' => 'boolean',
				'values' => ['N', 'Y']
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
			'PROPERTIES' => array(
				'data_type' => 'text',
				'serialized' => true,
				'validation' => array(__CLASS__, 'validateProperties'),
			),
			'RETURN_PROPERTIES' => array(
				'data_type' => 'text',
				'serialized' => true,
				'validation' => array(__CLASS__, 'validateProperties'),
			),
			'DOCUMENT_TYPE' => array(
				'data_type' => 'text',
				'serialized' => true,
			),
			'FILTER' => array(
				'data_type' => 'text',
				'serialized' => true,
			),
			'IS_ROBOT' => array(
				'data_type' => 'boolean',
				'values' => ['N', 'Y']
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
	 * Returns validators for PROPERTIES and RETURN_PROPERTIES fields
	 *
	 * @return array
	 */
	public static function validateProperties()
	{
		return array(
			function($value, $primary, $row, Field $field) {
				$errorMsg = GetMessage("BPRAT_PROPERTIES_LENGTH_ERROR", array("#FIELD_TITLE#" => $field->getTitle()));
				return strlen(serialize($value)) < 65535 ? true : $errorMsg;
			}
		);
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
}
