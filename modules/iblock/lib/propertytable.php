<?php

namespace Bitrix\Iblock;

use Bitrix\Main\ORM;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Localization\Loc;

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
 * @method static EO_Property_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Property_Result getById($id)
 * @method static EO_Property_Result getList(array $parameters = [])
 * @method static EO_Property_Entity getEntity()
 * @method static \Bitrix\Iblock\Property createObject($setDefaultValues = true)
 * @method static \Bitrix\Iblock\EO_Property_Collection createCollection()
 * @method static \Bitrix\Iblock\Property wakeUpObject($row)
 * @method static \Bitrix\Iblock\EO_Property_Collection wakeUpCollection($rows)
 */

class PropertyTable extends ORM\Data\DataManager
{
	public const CHECKBOX = 'C';
	public const LISTBOX = 'L';

	public const TYPE_STRING = 'S';
	public const TYPE_NUMBER = 'N';
	public const TYPE_FILE = 'F';
	public const TYPE_ELEMENT = 'E';
	public const TYPE_SECTION = 'G';
	public const TYPE_LIST = 'L';

	public const DEFAULT_MULTIPLE_CNT = 5;

	// iblock module
	public const USER_TYPE_DATE = 'Date';
	public const USER_TYPE_DATETIME = 'DateTime';
	public const USER_TYPE_XML_ID = 'ElementXmlID';
	public const USER_TYPE_FILE_MAN = 'FileMan';
	public const USER_TYPE_HTML = 'HTML';
	public const USER_TYPE_ELEMENT_LIST = 'EList';
	public const USER_TYPE_SEQUENCE = 'Sequence';
	public const USER_TYPE_ELEMENT_AUTOCOMPLETE = 'EAutocomplete';
	public const USER_TYPE_SKU = 'SKU';
	public const USER_TYPE_SECTION_AUTOCOMPLETE = 'SectionAuto';

	// other modules
	public const USER_TYPE_CRM = 'ECrm'; // \Bitrix\Crm\Integration\IBlockElementProperty::USER_TYPE
	public const USER_TYPE_MONEY = 'Money'; // \Bitrix\Currency\Integration\IblockMoneyProperty::USER_TYPE
	public const USER_TYPE_DISK = 'DiskFile'; // \Bitrix\Disk\Integration\FileDiskProperty
	public const USER_TYPE_GOOGLE_MAP = 'map_google'; // \CIBlockPropertyMapGoogle
	public const USER_TYPE_YANDEX_MAP = 'map_yandex'; // \CIBlockPropertyMapYandex
	public const USER_TYPE_FORUM_TOPIC = 'TopicID'; // \CIBlockPropertyTopicID
	public const USER_TYPE_DIRECTORY = 'directory'; // \CIBlockPropertyDirectory::USER_TYPE
	public const USER_TYPE_EMPLOYEE = 'employee'; // \CIBlockPropertyEmployee
	public const USER_TYPE_USER = 'UserID'; // \CIBlockPropertyUserID::USER_TYPE

	/**
	 * Returns DB table name for entity
	 *
	 * @return string
	 */
	public static function getTableName(): string
	{
		return 'b_iblock_property';
	}

	public static function getObjectClass(): string
	{
		return Property::class;
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap(): array
	{
		return [
			'ID' => (new ORM\Fields\IntegerField('ID'))
				->configurePrimary(true)
				->configureAutocomplete(true)
				->configureTitle(Loc::getMessage('IBLOCK_PROPERTY_ENTITY_ID_FIELD'))
			,
			'TIMESTAMP_X' => (new ORM\Fields\DatetimeField('TIMESTAMP_X'))
				->configureDefaultValue(function()
					{
						return new DateTime();
					}
				)
				->configureTitle(Loc::getMessage('IBLOCK_PROPERTY_ENTITY_TIMESTAMP_X_FIELD'))
			,
			'IBLOCK_ID' => (new ORM\Fields\IntegerField('IBLOCK_ID'))
				->configureRequired(true)
				->configureTitle(Loc::getMessage('IBLOCK_PROPERTY_ENTITY_IBLOCK_ID_FIELD'))
			,
			'NAME' => (new ORM\Fields\StringField('NAME'))
				->configureRequired(true)
				->configureSize(255)
				->addValidator(new ORM\Fields\Validators\LengthValidator(null, 255))
				->configureTitle(Loc::getMessage('IBLOCK_PROPERTY_ENTITY_NAME_FIELD'))
			,
			'ACTIVE' => (new ORM\Fields\BooleanField('ACTIVE'))
				->configureValues('N', 'Y')
				->configureDefaultValue('Y')
				->configureTitle(Loc::getMessage('IBLOCK_PROPERTY_ENTITY_ACTIVE_FIELD'))
			,
			'SORT' => (new ORM\Fields\IntegerField('SORT'))
				->configureDefaultValue(100)
				->configureTitle(Loc::getMessage('IBLOCK_PROPERTY_ENTITY_SORT_FIELD'))
			,
			'CODE' => (new ORM\Fields\StringField('CODE'))
				->configureNullable(true)
				->configureSize(50)
				->addValidator(new ORM\Fields\Validators\LengthValidator(null, 50))
				->configureTitle(Loc::getMessage('IBLOCK_PROPERTY_ENTITY_CODE_FIELD'))
			,
			'DEFAULT_VALUE' => (new ORM\Fields\TextField('DEFAULT_VALUE'))
				->configureNullable(true)
				->configureTitle(Loc::getMessage('IBLOCK_PROPERTY_ENTITY_DEFAULT_VALUE_FIELD'))
			,
			'PROPERTY_TYPE' => (new ORM\Fields\EnumField('PROPERTY_TYPE'))
				->configureRequired(true)
				->configureValues([
					self::TYPE_STRING,
					self::TYPE_NUMBER,
					self::TYPE_FILE,
					self::TYPE_ELEMENT,
					self::TYPE_SECTION,
					self::TYPE_LIST,
				])
				->configureDefaultValue(self::TYPE_STRING)
				->configureTitle(Loc::getMessage('IBLOCK_PROPERTY_ENTITY_PROPERTY_TYPE_FIELD'))
			,
			'ROW_COUNT' => (new ORM\Fields\IntegerField('ROW_COUNT'))
				->configureDefaultValue(1)
				->configureTitle(Loc::getMessage('IBLOCK_PROPERTY_ENTITY_ROW_COUNT_FIELD'))
			,
			'COL_COUNT' => (new ORM\Fields\IntegerField('COL_COUNT'))
				->configureDefaultValue(30)
				->configureTitle(Loc::getMessage('IBLOCK_PROPERTY_ENTITY_COL_COUNT_FIELD'))
			,
			'LIST_TYPE' => (new ORM\Fields\EnumField('LIST_TYPE'))
				->configureValues([
					self::LISTBOX,
					self::CHECKBOX,
				])
				->configureDefaultValue(self::LISTBOX)
				->configureTitle(Loc::getMessage('IBLOCK_PROPERTY_ENTITY_LIST_TYPE_FIELD'))
			,
			'MULTIPLE' => (new ORM\Fields\BooleanField('MULTIPLE'))
				->configureValues('N', 'Y')
				->configureDefaultValue('N')
				->configureTitle(Loc::getMessage('IBLOCK_PROPERTY_ENTITY_MULTIPLE_FIELD'))
			,
			'XML_ID' => (new ORM\Fields\StringField('XML_ID'))
				->configureNullable(true)
				->configureSize(100)
				->addValidator(new ORM\Fields\Validators\LengthValidator(null, 100))
				->configureTitle(Loc::getMessage('IBLOCK_PROPERTY_ENTITY_XML_ID_FIELD'))
			,
			'FILE_TYPE' => (new ORM\Fields\StringField('FILE_TYPE'))
				->configureNullable(true)
				->configureSize(200)
				->addValidator(new ORM\Fields\Validators\LengthValidator(null, 200))
				->configureTitle(Loc::getMessage('IBLOCK_PROPERTY_ENTITY_FILE_TYPE_FIELD'))
			,
			'MULTIPLE_CNT' => (new ORM\Fields\IntegerField('MULTIPLE_CNT'))
				->configureNullable(true)
				->configureDefaultValue(self::DEFAULT_MULTIPLE_CNT)
				->configureTitle(Loc::getMessage('IBLOCK_PROPERTY_ENTITY_MULTIPLE_CNT_FIELD'))
			,
			'TMP_ID' => (new ORM\Fields\StringField('TMP_ID'))
				->configureNullable(true)
				->configureSize(40)
				->addValidator(new ORM\Fields\Validators\LengthValidator(null, 40))
				->configureTitle(Loc::getMessage('IBLOCK_PROPERTY_ENTITY_TMP_ID_FIELD'))
			,
			'LINK_IBLOCK_ID' => (new ORM\Fields\IntegerField('LINK_IBLOCK_ID'))
				->configureNullable(true)
				->configureDefaultValue(0)
				->configureTitle(Loc::getMessage('IBLOCK_PROPERTY_ENTITY_LINK_IBLOCK_ID_FIELD'))
			,
			'WITH_DESCRIPTION' => (new ORM\Fields\BooleanField('WITH_DESCRIPTION'))
				->configureValues('N', 'Y')
				->configureDefaultValue('N')
				->configureTitle(Loc::getMessage('IBLOCK_PROPERTY_ENTITY_WITH_DESCRIPTION_FIELD'))
			,
			'SEARCHABLE' => (new ORM\Fields\BooleanField('SEARCHABLE'))
				->configureValues('N', 'Y')
				->configureDefaultValue('N')
				->configureTitle(Loc::getMessage('IBLOCK_PROPERTY_ENTITY_SEARCHABLE_FIELD'))
			,
			'FILTRABLE' => (new ORM\Fields\BooleanField('FILTRABLE'))
				->configureValues('N', 'Y')
				->configureDefaultValue('N')
				->configureTitle(Loc::getMessage('IBLOCK_PROPERTY_ENTITY_FILTRABLE_FIELD'))
			,
			'IS_REQUIRED' => (new ORM\Fields\BooleanField('IS_REQUIRED'))
				->configureValues('N', 'Y')
				->configureDefaultValue('N')
				->configureTitle(Loc::getMessage('IBLOCK_PROPERTY_ENTITY_IS_REQUIRED_FIELD'))
			,
			'VERSION' => (new ORM\Fields\EnumField('VERSION'))
				->configureValues([1, 2])
				->configureDefaultValue(1)
				->configureTitle(Loc::getMessage('IBLOCK_PROPERTY_ENTITY_VERSION_FIELD'))
			,
			'USER_TYPE' => (new ORM\Fields\StringField('USER_TYPE'))
				->configureNullable(true)
				->configureSize(255)
				->addValidator(new ORM\Fields\Validators\LengthValidator(null, 255))
				->configureTitle(Loc::getMessage('IBLOCK_PROPERTY_ENTITY_USER_TYPE_FIELD'))
			,
			'USER_TYPE_SETTINGS_LIST' => (new ORM\Fields\ArrayField('USER_TYPE_SETTINGS_LIST'))
				->configureNullable(true)
				->configureSerializationPhp()
				->configureColumnName('USER_TYPE_SETTINGS')
				->configureTitle(Loc::getMessage('IBLOCK_PROPERTY_ENTITY_USER_TYPE_SETTINGS_FIELD'))
			,
			'USER_TYPE_SETTINGS' => (new ORM\Fields\TextField('USER_TYPE_SETTINGS'))
				->configureNullable(true)
			,
			'HINT' => (new ORM\Fields\StringField('HINT'))
				->configureNullable(true)
				->configureSize(255)
				->addValidator(new ORM\Fields\Validators\LengthValidator(null, 255))
				->configureTitle(Loc::getMessage('IBLOCK_PROPERTY_ENTITY_HINT_FIELD'))
			,
			'LINK_IBLOCK' => new ORM\Fields\Relations\Reference(
				'LINK_IBLOCK',
				IblockTable::class,
				ORM\Query\Join::on('this.LINK_IBLOCK_ID', 'ref.ID')
			),
			'IBLOCK' => new ORM\Fields\Relations\Reference(
				'IBLOCK',
				IblockTable::class,
				ORM\Query\Join::on('this.IBLOCK_ID', 'ref.ID')
			),
		];
	}

	/**
	 * Default onBeforeAdd handler. Absolutely necessary.
	 *
	 * @param ORM\Event $event Event object.
	 * @return ORM\EventResult
	 */
	public static function onBeforeAdd(ORM\Event $event): ORM\EventResult
	{
		$result = new ORM\EventResult;
		$fields = $event->getParameter('fields');

		$modifyFieldList = [];
		self::copyOldFields($modifyFieldList, $fields);
		$result->unsetField('USER_TYPE_SETTINGS');
		if (!isset($fields['TIMESTAMP_X']))
		{
			$modifyFieldList['TIMESTAMP_X'] = new DateTime();
		}

		if (!empty($modifyFieldList))
		{
			$result->modifyFields($modifyFieldList);
		}
		unset($modifyFieldList);
		unset($fields);

		return $result;
	}

	/**
	 * Default onBeforeUpdate handler. Absolutely necessary.
	 *
	 * @param ORM\Event $event Event object.
	 * @return ORM\EventResult
	 */
	public static function onBeforeUpdate(ORM\Event $event): ORM\EventResult
	{
		$result = new ORM\EventResult;
		$fields = $event->getParameter('fields');

		$modifyFieldList = [];
		self::copyOldFields($modifyFieldList, $fields);
		$result->unsetField('USER_TYPE_SETTINGS');
		if (!isset($fields['TIMESTAMP_X']))
		{
			$modifyFieldList['TIMESTAMP_X'] = new DateTime();
		}

		if (!empty($modifyFieldList))
		{
			$result->modifyFields($modifyFieldList);
		}
		unset($modifyFieldList);
		unset($fields);

		return $result;
	}

	/**
	 * Remove values from old fields (for compatibility with old api).
	 *
	 * @param array &$result Modified data for add/update property.
	 * @param array $data Current data for add/update property.
	 * @return void
	 */
	private static function copyOldFields(array &$result, array $data): void
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
