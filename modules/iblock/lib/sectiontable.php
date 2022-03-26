<?php
namespace Bitrix\Iblock;

use Bitrix\Main\ORM;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Event;

Loc::loadMessages(__FILE__);

/**
 * Class SectionTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> TIMESTAMP_X datetime mandatory default 'CURRENT_TIMESTAMP'
 * <li> MODIFIED_BY int optional
 * <li> DATE_CREATE datetime optional
 * <li> CREATED_BY int optional
 * <li> IBLOCK_ID int mandatory
 * <li> IBLOCK_SECTION_ID int optional
 * <li> ACTIVE bool optional default 'Y'
 * <li> GLOBAL_ACTIVE bool optional default 'Y'
 * <li> SORT int optional default 500
 * <li> NAME string(255) mandatory
 * <li> PICTURE int optional
 * <li> LEFT_MARGIN int optional
 * <li> RIGHT_MARGIN int optional
 * <li> DEPTH_LEVEL int optional
 * <li> DESCRIPTION string optional
 * <li> DESCRIPTION_TYPE enum ('text', 'html') optional default 'text'
 * <li> SEARCHABLE_CONTENT string optional
 * <li> CODE string(255) optional
 * <li> XML_ID string(255) optional
 * <li> TMP_ID string(40) optional
 * <li> DETAIL_PICTURE int optional
 * <li> SOCNET_GROUP_ID int optional
 * <li> IBLOCK reference to {@link \Bitrix\Iblock\IblockTable}
 * <li> PARENT_SECTION reference to {@link \Bitrix\Iblock\SectionTable}
 * <li> CREATED_BY_USER reference to {@link \Bitrix\Main\UserTable}
 * <li> MODIFIED_BY_USER reference to {@link \Bitrix\Main\UserTable}
 * </ul>
 *
 * @package Bitrix\Iblock
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Section_Query query()
 * @method static EO_Section_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Section_Result getById($id)
 * @method static EO_Section_Result getList(array $parameters = array())
 * @method static EO_Section_Entity getEntity()
 * @method static \Bitrix\Iblock\EO_Section createObject($setDefaultValues = true)
 * @method static \Bitrix\Iblock\EO_Section_Collection createCollection()
 * @method static \Bitrix\Iblock\EO_Section wakeUpObject($row)
 * @method static \Bitrix\Iblock\EO_Section_Collection wakeUpCollection($rows)
 */

class SectionTable extends ORM\Data\DataManager
{
	/**
	 * Returns DB table name for entity
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_iblock_section';
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
				'title' => Loc::getMessage('IBLOCK_SECTION_ENTITY_ID_FIELD'),
			),
			'TIMESTAMP_X' => array(
				'data_type' => 'datetime',
				'required' => true,
				'title' => Loc::getMessage('IBLOCK_SECTION_ENTITY_TIMESTAMP_X_FIELD'),
			),
			'MODIFIED_BY' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('IBLOCK_SECTION_ENTITY_MODIFIED_BY_FIELD'),
			),
			'DATE_CREATE' => array(
				'data_type' => 'datetime',
				'title' => Loc::getMessage('IBLOCK_SECTION_ENTITY_DATE_CREATE_FIELD'),
			),
			'CREATED_BY' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('IBLOCK_SECTION_ENTITY_CREATED_BY_FIELD'),
			),
			'IBLOCK_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('IBLOCK_SECTION_ENTITY_IBLOCK_ID_FIELD'),
			),
			'IBLOCK_SECTION_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('IBLOCK_SECTION_ENTITY_IBLOCK_SECTION_ID_FIELD'),
			),
			'ACTIVE' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'default_value' => 'Y',
				'title' => Loc::getMessage('IBLOCK_SECTION_ENTITY_ACTIVE_FIELD'),
			),
			'GLOBAL_ACTIVE' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('IBLOCK_SECTION_ENTITY_GLOBAL_ACTIVE_FIELD'),
			),
			'SORT' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('IBLOCK_SECTION_ENTITY_SORT_FIELD'),
			),
			'NAME' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateName'),
				'title' => Loc::getMessage('IBLOCK_SECTION_ENTITY_NAME_FIELD'),
			),
			'PICTURE' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('IBLOCK_SECTION_ENTITY_PICTURE_FIELD'),
			),
			'LEFT_MARGIN' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('IBLOCK_SECTION_ENTITY_LEFT_MARGIN_FIELD'),
			),
			'RIGHT_MARGIN' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('IBLOCK_SECTION_ENTITY_RIGHT_MARGIN_FIELD'),
			),
			'DEPTH_LEVEL' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('IBLOCK_SECTION_ENTITY_DEPTH_LEVEL_FIELD'),
			),
			'DESCRIPTION' => array(
				'data_type' => 'text',
				'title' => Loc::getMessage('IBLOCK_SECTION_ENTITY_DESCRIPTION_FIELD'),
			),
			'DESCRIPTION_TYPE' => array(
				'data_type' => 'enum',
				'required' => true,
				'values' => array('text', 'html'),
				'default_value' => 'text',
				'title' => Loc::getMessage('IBLOCK_SECTION_ENTITY_DESCRIPTION_TYPE_FIELD'),
			),
			'SEARCHABLE_CONTENT' => array(
				'data_type' => 'text',
				'title' => Loc::getMessage('IBLOCK_SECTION_ENTITY_SEARCHABLE_CONTENT_FIELD'),
			),
			'CODE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateCode'),
				'title' => Loc::getMessage('IBLOCK_SECTION_ENTITY_CODE_FIELD'),
			),
			'XML_ID' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateXmlId'),
				'title' => Loc::getMessage('IBLOCK_SECTION_ENTITY_XML_ID_FIELD'),
			),
			'TMP_ID' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateTmpId'),
				'title' => Loc::getMessage('IBLOCK_SECTION_ENTITY_TMP_ID_FIELD'),
			),
			'DETAIL_PICTURE' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('IBLOCK_SECTION_ENTITY_DETAIL_PICTURE_FIELD'),
			),
			'SOCNET_GROUP_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('IBLOCK_SECTION_ENTITY_SOCNET_GROUP_ID_FIELD'),
			),
			'IBLOCK' => array(
				'data_type' => 'Bitrix\Iblock\Iblock',
				'reference' => array('=this.IBLOCK_ID' => 'ref.ID'),
			),
			'PARENT_SECTION' => array(
				'data_type' => 'Bitrix\Iblock\Section',
				'reference' => array('=this.IBLOCK_SECTION_ID' => 'ref.ID'),
			),
			'CREATED_BY_USER' => array(
				'data_type' => 'Bitrix\Main\User',
				'reference' => array('=this.CREATED_BY' => 'ref.ID'),
			),
			'MODIFIED_BY_USER' => array(
				'data_type' => 'Bitrix\Main\User',
				'reference' => array('=this.MODIFIED_BY' => 'ref.ID'),
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
			new ORM\Fields\Validators\LengthValidator(null, 255),
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
			new ORM\Fields\Validators\LengthValidator(null, 255),
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

	public static function onAfterAdd(Event $event)
	{
		/** @var EO_Section $section */
		$section = $event->getParameter('object');
		$section->fill(['IBLOCK_ID', 'IBLOCK_SECTION_ID', 'NAME', 'SORT']);

		// clear tag cache
		\CIBlock::clearIblockTagCache($section->getIblockId());

		// recount tree
		\CIBlockSection::recountTreeAfterAdd($section->collectValues());
	}

	public static function onUpdate(Event $event)
	{
		/** @var EO_Section $section */
		$section = $event->getParameter('object');

		// save old fields
		$oldValues = \CIBlockSection::GetList([], ["ID" => $section->getId(), "CHECK_PERMISSIONS" => "N"]);
		$section->customData->set('RECOUNT_TREE_OLD_VALUES', $oldValues);
	}

	public static function onAfterUpdate(Event $event)
	{
		/** @var EO_Section $section */
		$section = $event->getParameter('object');
		$section->fill(['IBLOCK_ID', 'IBLOCK_SECTION_ID', 'NAME', 'SORT', 'ACTIVE']);

		// clear tag cache
		\CIBlock::clearIblockTagCache($section->getIblockId());

		// recount tree
		\CIBlockSection::recountTreeAfterUpdate($section->collectValues(), $section->customData->get('RECOUNT_TREE_OLD_VALUES'));
	}

	public static function onDelete(Event $event)
	{
		$section = static::wakeUpObject($event->getParameter('id'));
		$section->fill(['IBLOCK_ID']);

		// clear tag cache
		\CIBlock::clearIblockTagCache($section->getIblockId());
	}

	public static function onAfterDelete(Event $event)
	{
		// recount tree
		$primary = $event->getParameter('id');
		if (!is_array($primary))
		{
			$primary = ['ID' => $primary];
		}
		\CIBlockSection::recountTreeOnDelete($primary);
	}
}