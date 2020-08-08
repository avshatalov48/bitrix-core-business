<?php
namespace Bitrix\Iblock;

use Bitrix\Iblock\ORM\ElementEntity;
use Bitrix\Iblock\ORM\ElementV1Table;
use Bitrix\Iblock\ORM\ElementV2Table;
use Bitrix\Iblock\ORM\Fields\PropertyOneToMany;
use Bitrix\Iblock\ORM\Fields\PropertyReference;
use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\Relations\CascadePolicy;
use Bitrix\Main\ORM\Fields\Relations\OneToMany;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\ORM\Query\Query;
use CIBlockProperty;

Loc::loadMessages(__FILE__);

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
 */
class IblockTable extends DataManager
{
	const PROPERTY_STORAGE_COMMON = 1;
	const PROPERTY_STORAGE_SEPARATE = 2;

	const RIGHTS_SIMPLE = 'S';
	const RIGHTS_EXTENDED = 'E';

	const PROPERTY_INDEX_DISABLE = 'N';
	const PROPERTY_INDEX_ENABLE = 'Y';
	const PROPERTY_INDEX_INVALID = 'I';

	const LIST_MODE_SEPARATE = 'S';
	const LIST_MODE_COMBINED = 'C';

	const SECTION_CHOOSER_SELECT = 'L';
	const SECTION_CHOOSER_DROPDOWNS = 'D';
	const SECTION_CHOOSER_PATH = 'P';

	/* deprecated constants */
	const SELECT = self::SECTION_CHOOSER_SELECT;
	const DROPDOWNS = self::SECTION_CHOOSER_DROPDOWNS;
	const PATH = self::SECTION_CHOOSER_PATH;
	const SIMPLE = self::RIGHTS_SIMPLE;
	const EXTENDED = self::RIGHTS_EXTENDED;
	const SEPARATE = self::LIST_MODE_SEPARATE;
	const COMBINED = self::LIST_MODE_COMBINED;
	const INVALID = self::PROPERTY_INDEX_INVALID;

	const DATA_CLASS_NAMESPACE = 'Bitrix\Iblock\Elements';

	const DATA_CLASS_PREFIX = 'Element';

	/**
	 * Returns DB table name for entity
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_iblock';
	}

	public static function getObjectClass()
	{
		return Iblock::class;
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage('IBLOCK_ENTITY_ID_FIELD'),
			),
			'TIMESTAMP_X' => array(
				'data_type' => 'datetime',
				'title' => Loc::getMessage('IBLOCK_ENTITY_TIMESTAMP_X_FIELD'),
			),
			'IBLOCK_TYPE_ID' => array(
				'data_type' => 'string',
				'required' => true,
				'title' => Loc::getMessage('IBLOCK_ENTITY_IBLOCK_TYPE_ID_FIELD'),
				'validation' => array(__CLASS__, 'validateIblockTypeId'),
			),
			'LID' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateLid'),
			),
			'CODE' => array(
				'data_type' => 'string',
				'title' => Loc::getMessage('IBLOCK_ENTITY_CODE_FIELD'),
				'validation' => array(__CLASS__, 'validateCode'),
			),
			(new StringField('API_CODE'))
				->configureSize(50),
			'NAME' => array(
				'data_type' => 'string',
				'required' => true,
				'title' => Loc::getMessage('IBLOCK_ENTITY_NAME_FIELD'),
				'validation' => array(__CLASS__, 'validateName'),
			),
			'ACTIVE' => array(
				'data_type' => 'boolean',
				'values' => array('N','Y'),
				'title' => Loc::getMessage('IBLOCK_ENTITY_ACTIVE_FIELD'),
			),
			'SORT' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('IBLOCK_ENTITY_SORT_FIELD'),
			),
			'LIST_PAGE_URL' => array(
				'data_type' => 'string',
				'title' => Loc::getMessage('IBLOCK_ENTITY_LIST_PAGE_URL_FIELD'),
				'validation' => array(__CLASS__, 'validateListPageUrl'),
			),
			'DETAIL_PAGE_URL' => array(
				'data_type' => 'string',
				'title' => Loc::getMessage('IBLOCK_ENTITY_DETAIL_PAGE_URL_FIELD'),
				'validation' => array(__CLASS__, 'validateDetailPageUrl'),
			),
			'SECTION_PAGE_URL' => array(
				'data_type' => 'string',
				'title' => Loc::getMessage('IBLOCK_ENTITY_SECTION_PAGE_URL_FIELD'),
				'validation' => array(__CLASS__, 'validateSectionPageUrl'),
			),
			'CANONICAL_PAGE_URL' => array(
				'data_type' => 'string',
				'title' => Loc::getMessage('IBLOCK_ENTITY_CANONICAL_PAGE_URL_FIELD'),
				'validation' => array(__CLASS__, 'validateCanonicalPageUrl'),
			),
			'PICTURE' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('IBLOCK_ENTITY_PICTURE_FIELD'),
			),
			'DESCRIPTION' => array(
				'data_type' => 'string',
				'title' => Loc::getMessage('IBLOCK_ENTITY_DESCRIPTION_FIELD'),
			),
			'DESCRIPTION_TYPE' => array(
				'data_type' => 'enum',
				'values' => array('text', 'html'),
				'title' => Loc::getMessage('IBLOCK_ENTITY_DESCRIPTION_TYPE_FIELD'),
			),
			'XML_ID' => array(
				'data_type' => 'string',
				'title' => Loc::getMessage('IBLOCK_ENTITY_XML_ID_FIELD'),
				'validation' => array(__CLASS__, 'validateXmlId'),
			),
			'TMP_ID' => array(
				'data_type' => 'string',
				'title' => Loc::getMessage('IBLOCK_ENTITY_TMP_ID_FIELD'),
				'validation' => array(__CLASS__, 'validateTmpId'),
			),
			'INDEX_ELEMENT' => array(
				'data_type' => 'boolean',
				'values' => array('N','Y'),
				'title' => Loc::getMessage('IBLOCK_ENTITY_INDEX_ELEMENT_FIELD'),
			),
			'INDEX_SECTION' => array(
				'data_type' => 'boolean',
				'values' => array('N','Y'),
				'title' => Loc::getMessage('IBLOCK_ENTITY_INDEX_SECTION_FIELD'),
			),
			'WORKFLOW' => array(
				'data_type' => 'boolean',
				'values' => array('N','Y'),
				'title' => Loc::getMessage('IBLOCK_ENTITY_WORKFLOW_FIELD'),
			),
			'BIZPROC' => array(
				'data_type' => 'boolean',
				'values' => array('N','Y'),
				'title' => Loc::getMessage('IBLOCK_ENTITY_BIZPROC_FIELD'),
			),
			'SECTION_CHOOSER' => array(
				'data_type' => 'enum',
				'values' => array(
					self::SECTION_CHOOSER_SELECT,
					self::SECTION_CHOOSER_DROPDOWNS,
					self::SECTION_CHOOSER_PATH
				),
				'default_value' => self::SECTION_CHOOSER_SELECT,
				'title' => Loc::getMessage('IBLOCK_ENTITY_SECTION_CHOOSER_FIELD'),
			),
			'LIST_MODE' => array(
				'data_type' => 'enum',
				'values' => array(self::LIST_MODE_COMBINED, self::LIST_MODE_SEPARATE),
				'default_value' => self::LIST_MODE_COMBINED,
				'title' => Loc::getMessage('IBLOCK_ENTITY_LIST_MODE_FIELD'),
			),
			'RIGHTS_MODE' => array(
				'data_type' => 'enum',
				'values' => array(self::RIGHTS_SIMPLE, self::RIGHTS_EXTENDED),
				'default_value' => self::RIGHTS_SIMPLE,
				'title' => Loc::getMessage('IBLOCK_ENTITY_RIGHTS_MODE_FIELD'),
			),
			'SECTION_PROPERTY' => array(
				'data_type' => 'boolean',
				'values' => array('N','Y'),
				'title' => Loc::getMessage('IBLOCK_ENTITY_SECTION_PROPERTY_FIELD'),
			),
			'PROPERTY_INDEX' => array(
				'data_type' => 'enum',
				'values' => array(self::PROPERTY_INDEX_DISABLE, self::PROPERTY_INDEX_ENABLE, self::PROPERTY_INDEX_INVALID),
				'default' => self::PROPERTY_INDEX_DISABLE,
				'title' => Loc::getMessage('IBLOCK_ENTITY_PROPERTY_INDEX_FIELD'),
			),
			'VERSION' => array(
				'data_type' => 'enum',
				'values' => array(self::PROPERTY_STORAGE_COMMON, self::PROPERTY_STORAGE_SEPARATE),
				'default_value' => self::PROPERTY_STORAGE_COMMON,
				'title' => Loc::getMessage('IBLOCK_ENTITY_VERSION_FIELD'),
			),
			'LAST_CONV_ELEMENT' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('IBLOCK_ENTITY_LAST_CONV_ELEMENT_FIELD'),
			),
			'SOCNET_GROUP_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('IBLOCK_ENTITY_SOCNET_GROUP_ID_FIELD'),
			),
			'EDIT_FILE_BEFORE' => array(
				'data_type' => 'string',
				'title' => Loc::getMessage('IBLOCK_ENTITY_EDIT_FILE_BEFORE_FIELD'),
				'validation' => array(__CLASS__, 'validateEditFileBefore'),
			),
			'EDIT_FILE_AFTER' => array(
				'data_type' => 'string',
				'title' => Loc::getMessage('IBLOCK_ENTITY_EDIT_FILE_AFTER_FIELD'),
				'validation' => array(__CLASS__, 'validateEditFileAfter'),
			),
			'TYPE' => array(
				'data_type' => 'Bitrix\Iblock\Type',
				'reference' => array('=this.IBLOCK_TYPE_ID' => 'ref.ID'),
			),

			new OneToMany('PROPERTIES', PropertyTable::class, 'IBLOCK')
		);
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
							CascadePolicy::NO_ACTION // will be removed together in onAfterDelete
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
	 * Returns validators for IBLOCK_TYPE_ID field.
	 *
	 * @return array
	 */
	public static function validateIblockTypeId()
	{
		return array(
			new Entity\Validator\Length(null, 50),
		);
	}

	/**
	 * Returns validators for LID field.
	 *
	 * @return array
	 */
	public static function validateLid()
	{
		return array(
			new Entity\Validator\Length(null, 2),
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
			new Entity\Validator\Length(null, 50),
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
			new Entity\Validator\Length(null, 255),
		);
	}

	/**
	 * Returns validators for LIST_PAGE_URL field.
	 *
	 * @return array
	 */
	public static function validateListPageUrl()
	{
		return array(
			new Entity\Validator\Length(null, 255),
		);
	}

	/**
	 * Returns validators for DETAIL_PAGE_URL field.
	 *
	 * @return array
	 */
	public static function validateDetailPageUrl()
	{
		return array(
			new Entity\Validator\Length(null, 255),
		);
	}

	/**
	 * Returns validators for SECTION_PAGE_URL field.
	 *
	 * @return array
	 */
	public static function validateSectionPageUrl()
	{
		return array(
			new Entity\Validator\Length(null, 255),
		);
	}

	/**
	 * Returns validators for CANONICAL_PAGE_URL field.
	 *
	 * @return array
	 */
	public static function validateCanonicalPageUrl()
	{
		return array(
			new Entity\Validator\Length(null, 255),
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
			new Entity\Validator\Length(null, 255),
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
			new Entity\Validator\Length(null, 40),
		);
	}

	/**
	 * Returns validators for EDIT_FILE_BEFORE field.
	 *
	 * @return array
	 */
	public static function validateEditFileBefore()
	{
		return array(
			new Entity\Validator\Length(null, 255),
		);
	}

	/**
	 * Returns validators for EDIT_FILE_AFTER field.
	 *
	 * @return array
	 */
	public static function validateEditFileAfter()
	{
		return array(
			new Entity\Validator\Length(null, 255),
		);
	}
}