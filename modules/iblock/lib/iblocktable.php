<?php
namespace Bitrix\Iblock;

use Bitrix\Iblock\ORM\ElementEntity;
use Bitrix\Iblock\ORM\ElementV1Table;
use Bitrix\Iblock\ORM\ElementV2Table;
use Bitrix\Iblock\ORM\Fields\PropertyOneToMany;
use Bitrix\Iblock\ORM\Fields\PropertyReference;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Event;
use Bitrix\Main\ORM\EventResult;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\Relations\CascadePolicy;
use Bitrix\Main\ORM\Fields\Relations\ManyToMany;
use Bitrix\Main\ORM\Fields\Relations\OneToMany;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Validators;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Type\DateTime;
use CIBlockProperty;


/**
 * Class IblockTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> TIMESTAMP_X datetime
 * <li> IBLOCK_TYPE_ID string(50) mandatory
 * <li> CODE string(50) optional
 * <li> NAME string(255) mandatory
 * <li> ACTIVE bool optional default 'Y'
 * <li> SORT int optional default 500
 * <li> LIST_PAGE_URL string(255) optional
 * <li> DETAIL_PAGE_URL string(255) optional
 * <li> SECTION_PAGE_URL string(255) optional
 * <li> CANONICAL_PAGE_URL string(255) optional
 * <li> PICTURE int optional
 * <li> DESCRIPTION text optional
 * <li> DESCRIPTION_TYPE enum ('text', 'html') optional default 'text'
 * <li> XML_ID string(255) optional
 * <li> TMP_ID string(40) optional <b>internal use only</b>
 * <li> INDEX_ELEMENT bool optional default 'Y'
 * <li> INDEX_SECTION bool optional default 'N'
 * <li> WORKFLOW bool optional default 'Y'
 * <li> BIZPROC bool optional default 'N'
 * <li> SECTION_CHOOSER enum ('L', 'D' or 'P') optional default 'L'
 * <li> LIST_MODE enum ('S' or 'C') optional default ''
 * <li> RIGHTS_MODE enum ('S' or 'E') optional default 'S'
 * <li> SECTION_PROPERTY bool optional default 'N'
 * <li> PROPERTY_INDEX enum ('N', 'Y', 'I') optional default 'N'
 * <li> VERSION enum (1 or 2) optional default 1
 * <li> LAST_CONV_ELEMENT int optional default 0 <b>internal use only</b>
 * <li> SOCNET_GROUP_ID int optional <b>internal use only</b>
 * <li> EDIT_FILE_BEFORE string(255) optional
 * <li> EDIT_FILE_AFTER string(255) optional
 * <li> TYPE reference to {@link \Bitrix\Iblock\TypeTable}
 * </ul>
 *
 * @package Bitrix\Iblock
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Iblock_Query query()
 * @method static EO_Iblock_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Iblock_Result getById($id)
 * @method static EO_Iblock_Result getList(array $parameters = [])
 * @method static EO_Iblock_Entity getEntity()
 * @method static \Bitrix\Iblock\Iblock createObject($setDefaultValues = true)
 * @method static \Bitrix\Iblock\EO_Iblock_Collection createCollection()
 * @method static \Bitrix\Iblock\Iblock wakeUpObject($row)
 * @method static \Bitrix\Iblock\EO_Iblock_Collection wakeUpCollection($rows)
 */
class IblockTable extends DataManager
{
	public const TYPE_TEXT = 'text';
	public const TYPE_HTML = 'html';

	public const PROPERTY_STORAGE_COMMON = 1;
	public const PROPERTY_STORAGE_SEPARATE = 2;

	public const RIGHTS_SIMPLE = 'S';
	public const RIGHTS_EXTENDED = 'E';

	public const PROPERTY_INDEX_DISABLE = 'N';
	public const PROPERTY_INDEX_ENABLE = 'Y';
	public const PROPERTY_INDEX_INVALID = 'I';

	public const LIST_MODE_SEPARATE = 'S';
	public const LIST_MODE_COMBINED = 'C';

	public const SECTION_CHOOSER_SELECT = 'L';
	public const SECTION_CHOOSER_DROPDOWNS = 'D';
	public const SECTION_CHOOSER_PATH = 'P';

	/* deprecated constants */
	public const SELECT = self::SECTION_CHOOSER_SELECT;
	public const DROPDOWNS = self::SECTION_CHOOSER_DROPDOWNS;
	public const PATH = self::SECTION_CHOOSER_PATH;
	public const SIMPLE = self::RIGHTS_SIMPLE;
	public const EXTENDED = self::RIGHTS_EXTENDED;
	public const SEPARATE = self::LIST_MODE_SEPARATE;
	public const COMBINED = self::LIST_MODE_COMBINED;
	public const INVALID = self::PROPERTY_INDEX_INVALID;

	public const DATA_CLASS_NAMESPACE = 'Bitrix\Iblock\Elements';

	public const DATA_CLASS_PREFIX = 'Element';

	/**
	 * Returns DB table name for entity
	 *
	 * @return string
	 */
	public static function getTableName(): string
	{
		return 'b_iblock';
	}

	public static function getObjectClass(): string
	{
		return Iblock::class;
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap(): array
	{
		return [
			'ID' => [
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage('IBLOCK_ENTITY_ID_FIELD'),
			],
			'TIMESTAMP_X' => [
				'data_type' => 'datetime',
				'default_value' => function()
					{
						return new DateTime();
					}
				,
				'title' => Loc::getMessage('IBLOCK_ENTITY_TIMESTAMP_X_FIELD'),
			],
			'IBLOCK_TYPE_ID' => [
				'data_type' => 'string',
				'required' => true,
				'title' => Loc::getMessage('IBLOCK_ENTITY_IBLOCK_TYPE_ID_FIELD'),
				'validation' => function()
				{
					return [
						new Validators\LengthValidator(null, 50),
					];
				},
			],
			'LID' => [
				'data_type' => 'string',
				'validation' => function()
				{
					return [
						new Validators\LengthValidator(null, 2),
					];
				},
			],
			'CODE' => [
				'data_type' => 'string',
				'title' => Loc::getMessage('IBLOCK_ENTITY_CODE_FIELD'),
				'validation' => function()
				{
					return [
						new Validators\LengthValidator(null, 50),
					];
				},
			],
			(new StringField('API_CODE'))
				->configureSize(50),
			(new BooleanField('REST_ON'))
				->configureValues('N', 'Y')
				->configureDefaultValue('N'),
			'NAME' => [
				'data_type' => 'string',
				'required' => true,
				'title' => Loc::getMessage('IBLOCK_ENTITY_NAME_FIELD'),
				'validation' => function()
				{
					return [
						new Validators\LengthValidator(null, 255),
					];
				},
			],
			'ACTIVE' => [
				'data_type' => 'boolean',
				'values' => ['N','Y'],
				'title' => Loc::getMessage('IBLOCK_ENTITY_ACTIVE_FIELD'),
			],
			'SORT' => [
				'data_type' => 'integer',
				'title' => Loc::getMessage('IBLOCK_ENTITY_SORT_FIELD'),
			],
			'LIST_PAGE_URL' => [
				'data_type' => 'string',
				'title' => Loc::getMessage('IBLOCK_ENTITY_LIST_PAGE_URL_FIELD'),
				'validation' => function()
				{
					return [
						new Validators\LengthValidator(null, 255),
					];
				},
			],
			'DETAIL_PAGE_URL' => [
				'data_type' => 'string',
				'title' => Loc::getMessage('IBLOCK_ENTITY_DETAIL_PAGE_URL_FIELD'),
				'validation' => function()
				{
					return [
						new Validators\LengthValidator(null, 255),
					];
				},
			],
			'SECTION_PAGE_URL' => [
				'data_type' => 'string',
				'title' => Loc::getMessage('IBLOCK_ENTITY_SECTION_PAGE_URL_FIELD'),
				'validation' => function()
				{
					return [
						new Validators\LengthValidator(null, 255),
					];
				},
			],
			'CANONICAL_PAGE_URL' => [
				'data_type' => 'string',
				'title' => Loc::getMessage('IBLOCK_ENTITY_CANONICAL_PAGE_URL_FIELD'),
				'validation' => function()
				{
					return [
						new Validators\LengthValidator(null, 255),
					];
				},
			],
			'PICTURE' => [
				'data_type' => 'integer',
				'title' => Loc::getMessage('IBLOCK_ENTITY_PICTURE_FIELD'),
			],
			'DESCRIPTION' => [
				'data_type' => 'string',
				'title' => Loc::getMessage('IBLOCK_ENTITY_DESCRIPTION_FIELD'),
			],
			'DESCRIPTION_TYPE' => [
				'data_type' => 'enum',
				'values' => [
					self::TYPE_TEXT,
					self::TYPE_HTML,
				],
				'default_value' => self::TYPE_TEXT,
				'title' => Loc::getMessage('IBLOCK_ENTITY_DESCRIPTION_TYPE_FIELD'),
			],
			'XML_ID' => [
				'data_type' => 'string',
				'title' => Loc::getMessage('IBLOCK_ENTITY_XML_ID_FIELD'),
				'validation' => function()
				{
					return [
						new Validators\LengthValidator(null, 255),
					];
				},
			],
			'TMP_ID' => [
				'data_type' => 'string',
				'title' => Loc::getMessage('IBLOCK_ENTITY_TMP_ID_FIELD'),
				'validation' => function()
				{
					return [
						new Validators\LengthValidator(null, 40),
					];
				},
			],
			'INDEX_ELEMENT' => [
				'data_type' => 'boolean',
				'values' => ['N','Y'],
				'title' => Loc::getMessage('IBLOCK_ENTITY_INDEX_ELEMENT_FIELD'),
			],
			'INDEX_SECTION' => [
				'data_type' => 'boolean',
				'values' => ['N','Y'],
				'title' => Loc::getMessage('IBLOCK_ENTITY_INDEX_SECTION_FIELD'),
			],
			'WORKFLOW' => [
				'data_type' => 'boolean',
				'values' => ['N','Y'],
				'title' => Loc::getMessage('IBLOCK_ENTITY_WORKFLOW_FIELD'),
			],
			'BIZPROC' => [
				'data_type' => 'boolean',
				'values' => ['N','Y'],
				'title' => Loc::getMessage('IBLOCK_ENTITY_BIZPROC_FIELD'),
			],
			'SECTION_CHOOSER' => [
				'data_type' => 'enum',
				'values' => [
					self::SECTION_CHOOSER_SELECT,
					self::SECTION_CHOOSER_DROPDOWNS,
					self::SECTION_CHOOSER_PATH
				],
				'default_value' => self::SECTION_CHOOSER_SELECT,
				'title' => Loc::getMessage('IBLOCK_ENTITY_SECTION_CHOOSER_FIELD'),
			],
			'LIST_MODE' => [
				'data_type' => 'enum',
				'values' => [self::LIST_MODE_COMBINED, self::LIST_MODE_SEPARATE],
				'default_value' => self::LIST_MODE_COMBINED,
				'title' => Loc::getMessage('IBLOCK_ENTITY_LIST_MODE_FIELD'),
			],
			'RIGHTS_MODE' => [
				'data_type' => 'enum',
				'values' => [self::RIGHTS_SIMPLE, self::RIGHTS_EXTENDED],
				'default_value' => self::RIGHTS_SIMPLE,
				'title' => Loc::getMessage('IBLOCK_ENTITY_RIGHTS_MODE_FIELD'),
			],
			'SECTION_PROPERTY' => [
				'data_type' => 'boolean',
				'values' => ['N','Y'],
				'title' => Loc::getMessage('IBLOCK_ENTITY_SECTION_PROPERTY_FIELD'),
			],
			'PROPERTY_INDEX' => [
				'data_type' => 'enum',
				'values' => [self::PROPERTY_INDEX_DISABLE, self::PROPERTY_INDEX_ENABLE, self::PROPERTY_INDEX_INVALID],
				'default' => self::PROPERTY_INDEX_DISABLE,
				'title' => Loc::getMessage('IBLOCK_ENTITY_PROPERTY_INDEX_FIELD'),
			],
			'VERSION' => [
				'data_type' => 'enum',
				'values' => [self::PROPERTY_STORAGE_COMMON, self::PROPERTY_STORAGE_SEPARATE],
				'default_value' => self::PROPERTY_STORAGE_COMMON,
				'title' => Loc::getMessage('IBLOCK_ENTITY_VERSION_FIELD'),
			],
			'LAST_CONV_ELEMENT' => [
				'data_type' => 'integer',
				'title' => Loc::getMessage('IBLOCK_ENTITY_LAST_CONV_ELEMENT_FIELD'),
			],
			'SOCNET_GROUP_ID' => [
				'data_type' => 'integer',
				'title' => Loc::getMessage('IBLOCK_ENTITY_SOCNET_GROUP_ID_FIELD'),
			],
			'EDIT_FILE_BEFORE' => [
				'data_type' => 'string',
				'title' => Loc::getMessage('IBLOCK_ENTITY_EDIT_FILE_BEFORE_FIELD'),
				'validation' => function()
				{
					return [
						new Validators\LengthValidator(null, 255),
					];
				},
			],
			'EDIT_FILE_AFTER' => [
				'data_type' => 'string',
				'title' => Loc::getMessage('IBLOCK_ENTITY_EDIT_FILE_AFTER_FIELD'),
				'validation' => function()
				{
					return [
						new Validators\LengthValidator(null, 255),
					];
				},
			],
			'TYPE' => [
				'data_type' => 'Bitrix\Iblock\Type',
				'reference' => ['=this.IBLOCK_TYPE_ID' => 'ref.ID'],
			],

			new OneToMany('PROPERTIES', PropertyTable::class, 'IBLOCK')
		];
	}

	/**
	 * @param int|Iblock $iblockApiCode
	 *
	 * @return ElementEntity|false
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function compileEntity($iblockApiCode)
	{
		// get iblock
		if ($iblockApiCode instanceof Iblock)
		{
			$iblock = $iblockApiCode;
			$iblock->fillApiCode();
		}
		else
		{
			$iblock = IblockTable::getList([
				'select' => ['ID', 'API_CODE'],
				'filter' => Query::filter()->where('API_CODE', $iblockApiCode)
			])->fetchObject();
		}

		if (!$iblock || empty($iblock->getApiCode()))
		{
			return false;
		}

		// class info
		$iblockNamespace = static::DATA_CLASS_NAMESPACE;
		$iblockDataClassName = $iblock->getEntityDataClassName();

		if ($iblockDataClassName == '')
		{
			return false;
		}

		// check if already compiled
		$iblockDataClass = $iblock->getEntityDataClass();

		if (class_exists($iblockDataClass, false))
		{
			return $iblockDataClass::getEntity();
		}

		// fill some necessary info
		$iblock->fill(['VERSION', 'PROPERTIES']);

		// iblock personal entity
		$parentDataClass = $iblock->getVersion() == 1
			? ElementV1Table::class
			: ElementV2Table::class;

		/** @var ElementEntity $elementEntity */
		$elementEntity = \Bitrix\Main\ORM\Entity::compileEntity(
			$iblockDataClassName,
			[],
			[
				'namespace' => $iblockNamespace,
				'parent' => $parentDataClass,
			]
		);

		// set iblock to the entity
		$elementEntity->setIblock($iblock);

		// set relation with sections
		SectionElementTable::getEntity()->addField(
			(new Reference('REGULAR_ELEMENT_'.$iblock->getId(), $elementEntity,
			Join::on('this.IBLOCK_ELEMENT_ID', 'ref.ID')->whereNull('this.ADDITIONAL_PROPERTY_ID')))
		);

		$elementEntity->addField((new ManyToMany('SECTIONS', SectionTable::class))
			->configureMediatorEntity(SectionElementTable::class)
			->configureLocalReference('REGULAR_ELEMENT_'.$iblock->getId())
			->configureRemoteReference('IBLOCK_SECTION')
		);

		//$baseTypeList = \Bitrix\Iblock\Helpers\Admin\Property::getBaseTypeList(true);
		$userTypeList = CIBlockProperty::GetUserType();

		// get properties
		foreach ($iblock->getProperties() as $property)
		{
			if (empty($property->getCode()))
			{
				continue;
			}

			// build property entity with base fields
			$propertyValueEntity = $property->getValueEntity($elementEntity);

			// add custom fields
			if (!empty($property->getUserType()) && !empty($userTypeList[$property->getUserType()]['GetORMFields']))
			{
				call_user_func($userTypeList[$property->getUserType()]['GetORMFields'], $propertyValueEntity, $property);
			}

			// add relations with property entity
			$niceColumnName = $property->getCode();

			if ($property->getMultiple())
			{
				// classic OneToMany
				$elementRefName = 'SRC_ELEMENT';

				// ref from prop entity to src element
				$propertyValueEntity->addField(
					(new Reference(
						$elementRefName,
						$elementEntity,
						Join::on('this.IBLOCK_ELEMENT_ID', 'ref.ID')
							->where('this.IBLOCK_PROPERTY_ID', $property->getId())
					))
						->configureJoinType(Join::TYPE_INNER)

				);

				// OneToMany from src element to prop entity
				$elementEntity->addField(
					(new PropertyOneToMany($niceColumnName, $propertyValueEntity, $elementRefName))
						->configureJoinType(Join::TYPE_LEFT)
						->configureIblockElementProperty($property)
						->configureCascadeDeletePolicy(
							CascadePolicy::FOLLOW
						)
				);
			}
			else
			{
				// classic ref
				$joinType = Join::TYPE_INNER;
				$joinFilter = Join::on('this.ID', 'ref.IBLOCK_ELEMENT_ID');

				if ($iblock->getVersion() == 1)
				{
					// additional clause for shared value table in v1.0
					$joinType = Join::TYPE_LEFT;
					$joinFilter->where('ref.IBLOCK_PROPERTY_ID', $property->getId());
				}

				// ref from src element to prop entity
				$elementEntity->addField(
					(new PropertyReference($niceColumnName, $propertyValueEntity, $joinFilter))
						->configureJoinType($joinType)
						->configureIblockElementProperty($property)
						->configureCascadeDeletePolicy(
							CascadePolicy::NO_ACTION  // will be removed together in onAfterDelete
						)
				);
			}
		}

		return $elementEntity;
	}

	/**
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function compileAllEntities()
	{
		foreach (static::getList(['select' => ['ID', 'API_CODE']])->fetchCollection() as $iblock)
		{
			static::compileEntity($iblock);
		}
	}

	/**
	 * Default onAfterAdd handler. Absolutely necessary.
	 *
	 * @param Event $event		Current data for add.
	 * @return void
	 */
	public static function onAfterAdd(Event $event): void
	{
		$primary = $event->getParameter('primary');
		\CIBlock::CleanCache($primary['ID']);
	}

	/**
	 * Default onBeforeAdd handler. Absolutely necessary.
	 *
	 * @param Event $event Current data for add.
	 * @return EventResult
	 */
	public static function onBeforeAdd(Event $event): EventResult
	{
		$result = new EventResult;
		$fields = $event->getParameter('fields');
		if (!isset($fields['TIMESTAMP_X']))
		{
			$result->modifyFields([
				'TIMESTAMP_X' => new DateTime(),
			]);
		}

		return $result;
	}

	/**
	 * Default onBeforeUpdate handler. Absolutely necessary.
	 *
	 * @param Event $event Current data for update.
	 * @return EventResult
	 */
	public static function onBeforeUpdate(Event $event): EventResult
	{
		$result = new EventResult;
		$fields = $event->getParameter('fields');
		if (!isset($fields['TIMESTAMP_X']))
		{
			$result->modifyFields([
				'TIMESTAMP_X' => new DateTime(),
			]);
		}

		return $result;
	}

	/**
	 * Default onAfterUpdate handler. Absolutely necessary.
	 *
	 * @param Event $event		Current data for add.
	 * @return void
	 */
	public static function onAfterUpdate(Event $event): void
	{
		$primary = $event->getParameter('primary');
		\CIBlock::CleanCache($primary['ID']);
	}

	/**
	 * Default onAfterDelete handler. Absolutely necessary.
	 *
	 * @param Event $event		Current data for add.
	 * @return void
	 */
	public static function onAfterDelete(Event $event): void
	{
		$primary = $event->getParameter('primary');
		\CIBlock::CleanCache($primary['ID']);
	}

	/**
	 * Returns iblock identifier by symbolic code. Optionally, checked site relation.
	 *
	 * @param string $code Iblock symbolic code.
	 * @param string|null $siteId Site identifier.
	 * @return int|null
	 */
	public static function resolveIdByCode(string $code, ?string $siteId = null): ?int
	{
		if ($code === '')
		{
			return null;
		}

		$row = static::getRow([
			'select' => [
				'ID',
			],
			'filter' => [
				'=CODE' => $code,
			],
			'cache' => [
				'ttl' => 86400,
			],
		]);

		if ($row === null)
		{
			return null;
		}

		$iblockId = (int)$row['ID'];

		if ($siteId === '')
		{
			$siteId = null;
		}
		if ($siteId !== null)
		{
			$row = IblockSiteTable::getRow([
				'select' => [
					'IBLOCK_ID',
				],
				'filter' => [
					'=IBLOCK_ID' => $iblockId,
					'=SITE_ID' => $siteId,
				],
				'cache' => [
					'ttl' => 86400,
				],
			]);
			if ($row === null)
			{
				$iblockId = null;
			}
		}

		return $iblockId;
	}
}
