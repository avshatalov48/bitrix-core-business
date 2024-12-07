<?php
namespace Bitrix\Iblock;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM;
use Bitrix\Main\ORM\Event;
use Bitrix\Main\ORM\EventResult;
use Bitrix\Main\ORM\Fields\Validators;
use Bitrix\Main\Type\DateTime;

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
 * @method static EO_Section_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Section_Result getById($id)
 * @method static EO_Section_Result getList(array $parameters = [])
 * @method static EO_Section_Entity getEntity()
 * @method static \Bitrix\Iblock\EO_Section createObject($setDefaultValues = true)
 * @method static \Bitrix\Iblock\EO_Section_Collection createCollection()
 * @method static \Bitrix\Iblock\EO_Section wakeUpObject($row)
 * @method static \Bitrix\Iblock\EO_Section_Collection wakeUpCollection($rows)
 */

class SectionTable extends ORM\Data\DataManager
{
	public const TYPE_TEXT = 'text';
	public const TYPE_HTML = 'html';

	private static array $oldValues = [];

	/**
	 * Returns DB table name for entity
	 *
	 * @return string
	 */
	public static function getTableName(): string
	{
		return 'b_iblock_section';
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
				'title' => Loc::getMessage('IBLOCK_SECTION_ENTITY_ID_FIELD'),
			],
			'TIMESTAMP_X' => [
				'data_type' => 'datetime',
				'required' => true,
				'default_value' => function()
					{
						return new DateTime();
					}
				,
				'title' => Loc::getMessage('IBLOCK_SECTION_ENTITY_TIMESTAMP_X_FIELD'),
			],
			'MODIFIED_BY' => [
				'data_type' => 'integer',
				'title' => Loc::getMessage('IBLOCK_SECTION_ENTITY_MODIFIED_BY_FIELD'),
			],
			'DATE_CREATE' => [
				'data_type' => 'datetime',
				'title' => Loc::getMessage('IBLOCK_SECTION_ENTITY_DATE_CREATE_FIELD'),
			],
			'CREATED_BY' => [
				'data_type' => 'integer',
				'title' => Loc::getMessage('IBLOCK_SECTION_ENTITY_CREATED_BY_FIELD'),
			],
			'IBLOCK_ID' => [
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('IBLOCK_SECTION_ENTITY_IBLOCK_ID_FIELD'),
			],
			'IBLOCK_SECTION_ID' => [
				'data_type' => 'integer',
				'title' => Loc::getMessage('IBLOCK_SECTION_ENTITY_IBLOCK_SECTION_ID_FIELD'),
			],
			'ACTIVE' => [
				'data_type' => 'boolean',
				'values' => ['N', 'Y'],
				'default_value' => 'Y',
				'title' => Loc::getMessage('IBLOCK_SECTION_ENTITY_ACTIVE_FIELD'),
			],
			'GLOBAL_ACTIVE' => [
				'data_type' => 'boolean',
				'values' => ['N', 'Y'],
				'title' => Loc::getMessage('IBLOCK_SECTION_ENTITY_GLOBAL_ACTIVE_FIELD'),
			],
			'SORT' => [
				'data_type' => 'integer',
				'title' => Loc::getMessage('IBLOCK_SECTION_ENTITY_SORT_FIELD'),
			],
			'NAME' => [
				'data_type' => 'string',
				'required' => true,
				'validation' => function()
				{
					return [
						new Validators\LengthValidator(null, 255),
					];
				},
				'title' => Loc::getMessage('IBLOCK_SECTION_ENTITY_NAME_FIELD'),
			],
			'PICTURE' => [
				'data_type' => 'integer',
				'title' => Loc::getMessage('IBLOCK_SECTION_ENTITY_PICTURE_FIELD'),
			],
			'LEFT_MARGIN' => [
				'data_type' => 'integer',
				'title' => Loc::getMessage('IBLOCK_SECTION_ENTITY_LEFT_MARGIN_FIELD'),
			],
			'RIGHT_MARGIN' => [
				'data_type' => 'integer',
				'title' => Loc::getMessage('IBLOCK_SECTION_ENTITY_RIGHT_MARGIN_FIELD'),
			],
			'DEPTH_LEVEL' => [
				'data_type' => 'integer',
				'title' => Loc::getMessage('IBLOCK_SECTION_ENTITY_DEPTH_LEVEL_FIELD'),
			],
			'DESCRIPTION' => [
				'data_type' => 'text',
				'title' => Loc::getMessage('IBLOCK_SECTION_ENTITY_DESCRIPTION_FIELD'),
			],
			'DESCRIPTION_TYPE' => [
				'data_type' => 'enum',
				'required' => true,
				'values' => [
					self::TYPE_TEXT,
					self::TYPE_HTML,
				],
				'default_value' => self::TYPE_TEXT,
				'title' => Loc::getMessage('IBLOCK_SECTION_ENTITY_DESCRIPTION_TYPE_FIELD'),
			],
			'SEARCHABLE_CONTENT' => [
				'data_type' => 'text',
				'title' => Loc::getMessage('IBLOCK_SECTION_ENTITY_SEARCHABLE_CONTENT_FIELD'),
			],
			'CODE' => [
				'data_type' => 'string',
				'validation' => function()
				{
					return [
						new Validators\LengthValidator(null, 255),
					];
				},
				'title' => Loc::getMessage('IBLOCK_SECTION_ENTITY_CODE_FIELD'),
			],
			'XML_ID' => [
				'data_type' => 'string',
				'validation' => function()
				{
					return [
						new Validators\LengthValidator(null, 255),
					];
				},
				'title' => Loc::getMessage('IBLOCK_SECTION_ENTITY_XML_ID_FIELD'),
			],
			'TMP_ID' => [
				'data_type' => 'string',
				'validation' => function()
				{
					return [
						new Validators\LengthValidator(null, 40),
					];
				},
				'title' => Loc::getMessage('IBLOCK_SECTION_ENTITY_TMP_ID_FIELD'),
			],
			'DETAIL_PICTURE' => [
				'data_type' => 'integer',
				'title' => Loc::getMessage('IBLOCK_SECTION_ENTITY_DETAIL_PICTURE_FIELD'),
			],
			'SOCNET_GROUP_ID' => [
				'data_type' => 'integer',
				'title' => Loc::getMessage('IBLOCK_SECTION_ENTITY_SOCNET_GROUP_ID_FIELD'),
			],
			'IBLOCK' => [
				'data_type' => 'Bitrix\Iblock\Iblock',
				'reference' => ['=this.IBLOCK_ID' => 'ref.ID'],
			],
			'PARENT_SECTION' => [
				'data_type' => 'Bitrix\Iblock\Section',
				'reference' => ['=this.IBLOCK_SECTION_ID' => 'ref.ID'],
			],
			'CREATED_BY_USER' => [
				'data_type' => 'Bitrix\Main\User',
				'reference' => ['=this.CREATED_BY' => 'ref.ID'],
			],
			'MODIFIED_BY_USER' => [
				'data_type' => 'Bitrix\Main\User',
				'reference' => ['=this.MODIFIED_BY' => 'ref.ID'],
			],
		];
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

	public static function onAfterAdd(Event $event): void
	{
		/** @var EO_Section $section */
		$section = $event->getParameter('object');
		$section->fill(['IBLOCK_ID', 'IBLOCK_SECTION_ID', 'NAME', 'SORT']);

		// clear tag cache
		\CIBlock::clearIblockTagCache($section->getIblockId());

		// recount tree
		\CIBlockSection::recountTreeAfterAdd($section->collectValues());
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

	public static function onUpdate(Event $event): void
	{
		/** @var EO_Section $section */
		$section = $event->getParameter('object');

		// save old fields
		$row = static::getRow([
			'select' => [
				'ID',
				'IBLOCK_ID',
				'SORT',
				'NAME',
				'IBLOCK_SECTION_ID',
				'LEFT_MARGIN',
				'RIGHT_MARGIN',
				'DEPTH_LEVEL',
				'ACTIVE',
			],
			'filter' => [
				'=ID' => $section->getId(),
			],
		]);
		self::$oldValues = $row !== null ? $row : [];
	}

	public static function onAfterUpdate(Event $event): void
	{
		/** @var EO_Section $section */
		$section = $event->getParameter('object');
		$section->fill(['IBLOCK_ID', 'IBLOCK_SECTION_ID', 'NAME', 'SORT', 'ACTIVE']);

		// clear tag cache
		\CIBlock::clearIblockTagCache($section->getIblockId());

		// recount tree
		\CIBlockSection::recountTreeAfterUpdate($section->collectValues(), self::$oldValues);
		self::$oldValues = [];
	}

	public static function onDelete(Event $event): void
	{
		$section = static::wakeUpObject($event->getParameter('id'));
		$section->fill(['IBLOCK_ID']);

		// clear tag cache
		\CIBlock::clearIblockTagCache($section->getIblockId());
	}

	public static function onAfterDelete(Event $event): void
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
