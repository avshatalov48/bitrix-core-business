<?php
namespace Bitrix\Iblock;

use Bitrix\Main\ORM,
	Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class PropertyTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> TIMESTAMP_X datetime mandatory default 'CURRENT_TIMESTAMP'
 * <li> IBLOCK_ID int mandatory
 * <li> NAME string(255) mandatory
 * <li> ACTIVE bool optional default 'Y'
 * <li> SORT int optional default 500
 * <li> CODE string(50) optional
 * <li> DEFAULT_VALUE text optional
 * <li> PROPERTY_TYPE enum ('S', 'N', 'L', 'F', 'E' or 'G') optional default 'S'
 * <li> ROW_COUNT int optional default 1
 * <li> COL_COUNT int optional default 30
 * <li> LIST_TYPE enum ('C' or 'L') optional default 'L'
 * <li> MULTIPLE bool optional default 'N'
 * <li> XML_ID string(100) optional
 * <li> FILE_TYPE string(200) optional
 * <li> MULTIPLE_CNT int optional
 * <li> TMP_ID string(40) optional
 * <li> LINK_IBLOCK_ID int optional
 * <li> WITH_DESCRIPTION bool optional default 'N'
 * <li> SEARCHABLE bool optional default 'N'
 * <li> FILTRABLE bool optional default 'N'
 * <li> IS_REQUIRED bool optional default 'N'
 * <li> VERSION enum (1 or 2) optional default 1
 * <li> USER_TYPE string(255) optional
 * <li> USER_TYPE_SETTINGS string optional
 * <li> HINT string(255) optional
 * <li> LINK_IBLOCK reference to {@link \Bitrix\Iblock\IblockTable}
 * <li> IBLOCK reference to {@link \Bitrix\Iblock\IblockTable}
 * </ul>
 *
 * @package Bitrix\Iblock
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Property_Query query()
 * @method static EO_Property_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Property_Result getById($id)
 * @method static EO_Property_Result getList(array $parameters = array())
 * @method static EO_Property_Entity getEntity()
 * @method static \Bitrix\Iblock\Property createObject($setDefaultValues = true)
 * @method static \Bitrix\Iblock\EO_Property_Collection createCollection()
 * @method static \Bitrix\Iblock\Property wakeUpObject($row)
 * @method static \Bitrix\Iblock\EO_Property_Collection wakeUpCollection($rows)
 */

class PropertyTable extends ORM\Data\DataManager
{
	const CHECKBOX = 'C';
	const LISTBOX = 'L';

	const TYPE_STRING = 'S';
	const TYPE_NUMBER = 'N';
	const TYPE_FILE = 'F';
	const TYPE_ELEMENT = 'E';
	const TYPE_SECTION = 'G';
	const TYPE_LIST = 'L';

	const DEFAULT_MULTIPLE_CNT = 5;

	/**
	 * Returns DB table name for entity
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_iblock_property';
	}

	public static function getObjectClass()
	{
		return Property::class;
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => new ORM\Fields\IntegerField('ID', array(
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage('IBLOCK_PROPERTY_ENTITY_ID_FIELD'),
			)),
			'TIMESTAMP_X' => new ORM\Fields\DatetimeField('TIMESTAMP_X', array(
				'title' => Loc::getMessage('IBLOCK_PROPERTY_ENTITY_TIMESTAMP_X_FIELD'),
			)),
			'IBLOCK_ID' => new ORM\Fields\IntegerField('IBLOCK_ID', array(
				'title' => Loc::getMessage('IBLOCK_PROPERTY_ENTITY_IBLOCK_ID_FIELD'),
			)),
			'NAME' => new ORM\Fields\StringField('NAME', array(
				'validation' => array(__CLASS__, 'validateName'),
				'title' => Loc::getMessage('IBLOCK_PROPERTY_ENTITY_NAME_FIELD'),
			)),
			'ACTIVE' => new ORM\Fields\BooleanField('ACTIVE', array(
				'values' => array('N','Y'),
				'default_value' => 'Y',
				'title' => Loc::getMessage('IBLOCK_PROPERTY_ENTITY_ACTIVE_FIELD'),
			)),
			'SORT' => new ORM\Fields\IntegerField('SORT', array(
				'default_value' => 500,
				'title' => Loc::getMessage('IBLOCK_PROPERTY_ENTITY_SORT_FIELD'),
			)),
			'CODE' => new ORM\Fields\StringField('CODE', array(
				'validation' => array(__CLASS__, 'validateCode'),
				'title' => Loc::getMessage('IBLOCK_PROPERTY_ENTITY_CODE_FIELD'),
			)),
			'DEFAULT_VALUE' => new ORM\Fields\TextField('DEFAULT_VALUE', array(
				'title' => Loc::getMessage('IBLOCK_PROPERTY_ENTITY_DEFAULT_VALUE_FIELD'),
			)),
			'PROPERTY_TYPE' => new ORM\Fields\EnumField('PROPERTY_TYPE', array(
				'values' => array(
					self::TYPE_STRING,
					self::TYPE_NUMBER,
					self::TYPE_FILE,
					self::TYPE_ELEMENT,
					self::TYPE_SECTION,
					self::TYPE_LIST
				),
				'default_value' => self::TYPE_STRING,
				'title' => Loc::getMessage('IBLOCK_PROPERTY_ENTITY_PROPERTY_TYPE_FIELD'),
			)),
			'ROW_COUNT' => new ORM\Fields\IntegerField('ROW_COUNT', array(
				'default_value' => 1,
				'title' => Loc::getMessage('IBLOCK_PROPERTY_ENTITY_ROW_COUNT_FIELD'),
			)),
			'COL_COUNT' => new ORM\Fields\IntegerField('COL_COUNT', array(
				'default_value' => 30,
				'title' => Loc::getMessage('IBLOCK_PROPERTY_ENTITY_COL_COUNT_FIELD'),
			)),
			'LIST_TYPE' => new ORM\Fields\EnumField('LIST_TYPE', array(
				'values' => array(self::LISTBOX, self::CHECKBOX),
				'default_value' => self::LISTBOX,
				'title' => Loc::getMessage('IBLOCK_PROPERTY_ENTITY_LIST_TYPE_FIELD'),
			)),
			'MULTIPLE' => new ORM\Fields\BooleanField('MULTIPLE', array(
				'values' => array('N','Y'),
				'default_value' => 'N',
				'title' => Loc::getMessage('IBLOCK_PROPERTY_ENTITY_MULTIPLE_FIELD'),
			)),
			'XML_ID' => new ORM\Fields\StringField('XML_ID', array(
				'validation' => array(__CLASS__, 'validateXmlId'),
				'title' => Loc::getMessage('IBLOCK_PROPERTY_ENTITY_XML_ID_FIELD'),
			)),
			'FILE_TYPE' => new ORM\Fields\StringField('FILE_TYPE', array(
				'validation' => array(__CLASS__, 'validateFileType'),
				'title' => Loc::getMessage('IBLOCK_PROPERTY_ENTITY_FILE_TYPE_FIELD'),
			)),
			'MULTIPLE_CNT' => new ORM\Fields\IntegerField('MULTIPLE_CNT', array(
				'default_value' => self::DEFAULT_MULTIPLE_CNT,
				'title' => Loc::getMessage('IBLOCK_PROPERTY_ENTITY_MULTIPLE_CNT_FIELD'),
			)),
			'TMP_ID' => new ORM\Fields\StringField('TMP_ID', array(
				'validation' => array(__CLASS__, 'validateTmpId'),
				'title' => Loc::getMessage('IBLOCK_PROPERTY_ENTITY_TMP_ID_FIELD'),
			)),
			'LINK_IBLOCK_ID' => new ORM\Fields\IntegerField('LINK_IBLOCK_ID', array(
				'default_value' => 0,
				'title' => Loc::getMessage('IBLOCK_PROPERTY_ENTITY_LINK_IBLOCK_ID_FIELD'),
			)),
			'WITH_DESCRIPTION' => new ORM\Fields\BooleanField('WITH_DESCRIPTION', array(
				'values' => array('N','Y'),
				'default_value' => 'N',
				'title' => Loc::getMessage('IBLOCK_PROPERTY_ENTITY_WITH_DESCRIPTION_FIELD'),
			)),
			'SEARCHABLE' => new ORM\Fields\BooleanField('SEARCHABLE', array(
				'values' => array('N','Y'),
				'default_value' => 'N',
				'title' => Loc::getMessage('IBLOCK_PROPERTY_ENTITY_SEARCHABLE_FIELD'),
			)),
			'FILTRABLE' => new ORM\Fields\BooleanField('FILTRABLE', array(
				'values' => array('N','Y'),
				'default_value' => 'N',
				'title' => Loc::getMessage('IBLOCK_PROPERTY_ENTITY_FILTRABLE_FIELD'),
			)),
			'IS_REQUIRED' => new ORM\Fields\BooleanField('IS_REQUIRED', array(
				'values' => array('N','Y'),
				'default_value' => 'N',
				'title' => Loc::getMessage('IBLOCK_PROPERTY_ENTITY_IS_REQUIRED_FIELD'),
			)),
			'VERSION' => new ORM\Fields\EnumField('VERSION', array(
				'values' => array(1, 2),
				'title' => Loc::getMessage('IBLOCK_PROPERTY_ENTITY_VERSION_FIELD'),
			)),
			'USER_TYPE' => new ORM\Fields\StringField('USER_TYPE', array(
				'validation' => array(__CLASS__, 'validateUserType'),
				'title' => Loc::getMessage('IBLOCK_PROPERTY_ENTITY_USER_TYPE_FIELD'),
			)),
			'USER_TYPE_SETTINGS_LIST' => new ORM\Fields\TextField('USER_TYPE_SETTINGS_LIST', array(
				'serialized' => true,
				'column_name' => 'USER_TYPE_SETTINGS',
				'title' => Loc::getMessage('IBLOCK_PROPERTY_ENTITY_USER_TYPE_SETTINGS_FIELD')
			)),
			'USER_TYPE_SETTINGS' => new ORM\Fields\TextField('USER_TYPE_SETTINGS', array()),
			'HINT' => new ORM\Fields\StringField('HINT', array(
				'validation' => array(__CLASS__, 'validateHint'),
				'title' => Loc::getMessage('IBLOCK_PROPERTY_ENTITY_HINT_FIELD'),
			)),
			'LINK_IBLOCK' => new ORM\Fields\Relations\Reference(
				'LINK_IBLOCK',
				'\Bitrix\Iblock\Iblock',
				array('=this.LINK_IBLOCK_ID' => 'ref.ID')
			),
			'IBLOCK' => new ORM\Fields\Relations\Reference(
				'IBLOCK',
				'\Bitrix\Iblock\Iblock',
				array('=this.IBLOCK_ID' => 'ref.ID')
			),
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
			new ORM\Fields\Validators\LengthValidator(null, 255),
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
			new ORM\Fields\Validators\LengthValidator(null, 50),
		);
	}

	/**
	 * Returns validators for XML_ID field.
	 *
	 * @return array
	 */
	public static function validateXmlId()
	{
		return array(
			new ORM\Fields\Validators\LengthValidator(null, 100),
		);
	}

	/**
	 * Returns validators for FILE_TYPE field.
	 *
	 * @return array
	 */
	public static function validateFileType()
	{
		return array(
			new ORM\Fields\Validators\LengthValidator(null, 200),
		);
	}

	/**
	 * Returns validators for TMP_ID field.
	 *
	 * @return array
	 */
	public static function validateTmpId()
	{
		return array(
			new ORM\Fields\Validators\LengthValidator(null, 40),
		);
	}

	/**
	 * Returns validators for USER_TYPE field.
	 *
	 * @return array
	 */
	public static function validateUserType()
	{
		return array(
			new ORM\Fields\Validators\LengthValidator(null, 255),
		);
	}

	/**
	 * Returns validators for HINT field.
	 *
	 * @return array
	 */
	public static function validateHint()
	{
		return array(
			new ORM\Fields\Validators\LengthValidator(null, 255),
		);
	}

	/**
	 * Default onBeforeAdd handler. Absolutely necessary.
	 *
	 * @param ORM\Event $event		Event object.
	 * @return ORM\EventResult
	 */
	public static function onBeforeAdd(ORM\Event $event)
	{
		$result = new ORM\EventResult;
		$fields = $event->getParameter('fields');

		$modifyFieldList = [];
		self::copyOldFields($modifyFieldList, $fields);
		$result->unsetField('USER_TYPE_SETTINGS');

		if (!empty($modifyFieldList))
			$result->modifyFields($modifyFieldList);
		unset($modifyFieldList);
		unset($fields);

		return $result;
	}

	/**
	 * Default onBeforeUpdate handler. Absolutely necessary.
	 *
	 * @param ORM\Event $event		Event object.
	 * @return ORM\EventResult
	 */
	public static function onBeforeUpdate(ORM\Event $event)
	{
		$result = new ORM\EventResult;
		$fields = $event->getParameter('fields');

		$modifyFieldList = [];
		self::copyOldFields($modifyFieldList, $fields);
		$result->unsetField('USER_TYPE_SETTINGS');

		if (!empty($modifyFieldList))
			$result->modifyFields($modifyFieldList);
		unset($modifyFieldList);
		unset($fields);

		return $result;
	}

	/**
	 * Remove values from old fields (for compatibility with old api).
	 *
	 * @param array &$result			Modified data for add/update property.
	 * @param array $data				Current data for add/update property.
	 * @return void
	 */
	private static function copyOldFields(&$result, $data)
	{
		if (!isset($data['USER_TYPE_SETTINGS_LIST']) && isset($data['USER_TYPE_SETTINGS']))
		{
			$settings = $data['USER_TYPE_SETTINGS'];
			if (
				is_string($settings)
				&& $settings !== ''
			)
			{
				$settings = unserialize($settings, ['allowed_classes' => false]);
			}
			if (is_array($settings))
			{
				$result['USER_TYPE_SETTINGS_LIST'] = $settings;
			}
			unset($settings);
		}
	}
}