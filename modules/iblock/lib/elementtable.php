<?php
namespace Bitrix\Iblock;

use Bitrix\Main\ORM\Fields\Validators;
use Bitrix\Main;
use Bitrix\Main\ORM;
use Bitrix\Main\Localization\Loc;

/**
 * Class ElementTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> TIMESTAMP_X datetime optional
 * <li> MODIFIED_BY int optional
 * <li> DATE_CREATE datetime optional
 * <li> CREATED_BY int optional
 * <li> IBLOCK_ID int mandatory
 * <li> IBLOCK_SECTION_ID int optional
 * <li> ACTIVE bool optional default 'Y'
 * <li> ACTIVE_FROM datetime optional
 * <li> ACTIVE_TO datetime optional
 * <li> SORT int optional default 500
 * <li> NAME string(255) mandatory
 * <li> PREVIEW_PICTURE int optional
 * <li> PREVIEW_TEXT string optional
 * <li> PREVIEW_TEXT_TYPE enum ('text', 'html') optional default 'text'
 * <li> DETAIL_PICTURE int optional
 * <li> DETAIL_TEXT string optional
 * <li> DETAIL_TEXT_TYPE enum ('text', 'html') optional default 'text'
 * <li> SEARCHABLE_CONTENT string optional
 * <li> WF_STATUS_ID int optional default 1
 * <li> WF_PARENT_ELEMENT_ID int optional
 * <li> WF_NEW enum ('N', 'Y') optional
 * <li> WF_LOCKED_BY int optional
 * <li> WF_DATE_LOCK datetime optional
 * <li> WF_COMMENTS string optional
 * <li> IN_SECTIONS bool optional default 'N'
 * <li> XML_ID string(255) optional
 * <li> CODE string(255) optional
 * <li> TAGS string(255) optional
 * <li> TMP_ID string(40) optional
 * <li> WF_LAST_HISTORY_ID int optional
 * <li> SHOW_COUNTER int optional
 * <li> SHOW_COUNTER_START datetime optional
 * <li> PREVIEW_PICTURE_FILE reference to {@link \Bitrix\Main\FileTable}
 * <li> DETAIL_PICTURE_FILE reference to {@link \Bitrix\Main\FileTable}
 * <li> IBLOCK reference to {@link \Bitrix\Iblock\IblockTable}
 * <li> WF_PARENT_ELEMENT reference to {@link \Bitrix\Iblock\IblockElementTable}
 * <li> IBLOCK_SECTION reference to {@link \Bitrix\Iblock\IblockSectionTable}
 * <li> MODIFIED_BY_USER reference to {@link \Bitrix\Main\UserTable}
 * <li> CREATED_BY_USER reference to {@link \Bitrix\Main\UserTable}
 * <li> WF_LOCKED_BY_USER reference to {@link \Bitrix\Main\UserTable}
 * </ul>
 *
 * @package Bitrix\Iblock
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Element_Query query()
 * @method static EO_Element_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Element_Result getById($id)
 * @method static EO_Element_Result getList(array $parameters = [])
 * @method static EO_Element_Entity getEntity()
 * @method static \Bitrix\Iblock\EO_Element createObject($setDefaultValues = true)
 * @method static \Bitrix\Iblock\EO_Element_Collection createCollection()
 * @method static \Bitrix\Iblock\EO_Element wakeUpObject($row)
 * @method static \Bitrix\Iblock\EO_Element_Collection wakeUpCollection($rows)
 */

class ElementTable extends ORM\Data\DataManager
{
	public const TYPE_TEXT = 'text';
	public const TYPE_HTML = 'html';

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName(): string
	{
		return 'b_iblock_element';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap(): array
	{
		return [
			'ID' => new ORM\Fields\IntegerField(
				'ID',
				[
					'primary' => true,
					'autocomplete' => true,
					'title' => Loc::getMessage('ELEMENT_ENTITY_ID_FIELD'),
				]
			),
			'TIMESTAMP_X' => new ORM\Fields\DatetimeField(
				'TIMESTAMP_X',
				[
					'default_value' => function()
						{
							return new Main\Type\DateTime();
						},
					'title' => Loc::getMessage('ELEMENT_ENTITY_TIMESTAMP_X_FIELD'),
				]
			),
			'MODIFIED_BY' => new ORM\Fields\IntegerField(
				'MODIFIED_BY',
				[
					'title' => Loc::getMessage('ELEMENT_ENTITY_MODIFIED_BY_FIELD'),
				]
			),
			'DATE_CREATE' => new ORM\Fields\DatetimeField(
				'DATE_CREATE',
				[
					'default_value' => function()
						{
							return new Main\Type\DateTime();
						},
					'title' => Loc::getMessage('ELEMENT_ENTITY_DATE_CREATE_FIELD'),
				]
			),
			'CREATED_BY' => new ORM\Fields\IntegerField(
				'CREATED_BY',
				[
					'title' => Loc::getMessage('ELEMENT_ENTITY_CREATED_BY_FIELD'),
				]
			),
			'IBLOCK_ID' => new ORM\Fields\IntegerField(
				'IBLOCK_ID',
				[
					'required' => true,
					'title' => Loc::getMessage('ELEMENT_ENTITY_IBLOCK_ID_FIELD'),
				]
			),
			'IBLOCK_SECTION_ID' => new ORM\Fields\IntegerField(
				'IBLOCK_SECTION_ID',
				[
					'title' => Loc::getMessage('ELEMENT_ENTITY_IBLOCK_SECTION_ID_FIELD'),
				]
			),
			'ACTIVE' => new ORM\Fields\BooleanField(
				'ACTIVE',
				[
					'values' => ['N', 'Y'],
					'default_value' => 'Y',
					'title' => Loc::getMessage('ELEMENT_ENTITY_ACTIVE_FIELD'),
				]
			),
			'ACTIVE_FROM' => new ORM\Fields\DatetimeField(
				'ACTIVE_FROM',
				[
					'title' => Loc::getMessage('ELEMENT_ENTITY_ACTIVE_FROM_FIELD'),
				]
			),
			'ACTIVE_TO' => new ORM\Fields\DatetimeField(
				'ACTIVE_TO',
				[
					'title' => Loc::getMessage('ELEMENT_ENTITY_ACTIVE_TO_FIELD'),
				]
			),
			'SORT' => new ORM\Fields\IntegerField(
				'SORT',
				[
					'default_value' => 500,
					'title' => Loc::getMessage('ELEMENT_ENTITY_SORT_FIELD'),
				]
			),
			'NAME' => new ORM\Fields\StringField(
				'NAME',
				[
					'required' => true,
					'validation' => function()
					{
						return [
							new Validators\LengthValidator(null, 255),
						];
					},
					'title' => Loc::getMessage('ELEMENT_ENTITY_NAME_FIELD'),
				]
			),
			'PREVIEW_PICTURE' => new ORM\Fields\IntegerField(
				'PREVIEW_PICTURE',
				[
					'title' => Loc::getMessage('ELEMENT_ENTITY_PREVIEW_PICTURE_FIELD'),
				]
			),
			'PREVIEW_TEXT' => new ORM\Fields\TextField(
				'PREVIEW_TEXT',
				[
					'title' => Loc::getMessage('ELEMENT_ENTITY_PREVIEW_TEXT_FIELD'),
				]
			),
			'PREVIEW_TEXT_TYPE' => new ORM\Fields\EnumField(
				'PREVIEW_TEXT_TYPE',
				[
					'values' => [
						self::TYPE_TEXT,
						self::TYPE_HTML,
					],
					'default_value' => self::TYPE_TEXT,
					'title' => Loc::getMessage('ELEMENT_ENTITY_PREVIEW_TEXT_TYPE_FIELD'),
				]
			),
			'DETAIL_PICTURE' => new ORM\Fields\IntegerField(
				'DETAIL_PICTURE',
				[
					'title' => Loc::getMessage('ELEMENT_ENTITY_DETAIL_PICTURE_FIELD'),
				]
			),
			'DETAIL_TEXT' => new ORM\Fields\TextField(
				'DETAIL_TEXT',
				[
					'title' => Loc::getMessage('ELEMENT_ENTITY_DETAIL_TEXT_FIELD'),
				]
			),
			'DETAIL_TEXT_TYPE' => new ORM\Fields\EnumField(
				'DETAIL_TEXT_TYPE',
				[
					'values' => [
						self::TYPE_TEXT,
						self::TYPE_HTML,
					],
					'default_value' => self::TYPE_TEXT,
					'title' => Loc::getMessage('ELEMENT_ENTITY_DETAIL_TEXT_TYPE_FIELD'),
				]
			),
			'SEARCHABLE_CONTENT' => new ORM\Fields\TextField(
				'SEARCHABLE_CONTENT',
				[
					'title' => Loc::getMessage('ELEMENT_ENTITY_SEARCHABLE_CONTENT_FIELD'),
				]
			),
			'WF_STATUS_ID' => new ORM\Fields\IntegerField(
				'WF_STATUS_ID',
				[
					'title' => Loc::getMessage('ELEMENT_ENTITY_WF_STATUS_ID_FIELD'),
				]
			),
			'WF_PARENT_ELEMENT_ID' => new ORM\Fields\IntegerField(
				'WF_PARENT_ELEMENT_ID',
				[
					'title' => Loc::getMessage('ELEMENT_ENTITY_WF_PARENT_ELEMENT_ID_FIELD'),
				]
			),
			'WF_NEW' => new ORM\Fields\EnumField(
				'WF_NEW',
				[
					'values' => [
						'N',
						'Y',
					],
					'title' => Loc::getMessage('ELEMENT_ENTITY_WF_NEW_FIELD'),
				]
			),
			'WF_LOCKED_BY' => new ORM\Fields\IntegerField(
				'WF_LOCKED_BY',
				[
					'title' => Loc::getMessage('ELEMENT_ENTITY_WF_LOCKED_BY_FIELD'),
				]
			),
			'WF_DATE_LOCK' => new ORM\Fields\DatetimeField(
				'WF_DATE_LOCK',
				[
					'title' => Loc::getMessage('ELEMENT_ENTITY_WF_DATE_LOCK_FIELD'),
				]
			),
			'WF_COMMENTS' => new ORM\Fields\TextField(
				'WF_COMMENTS',
				[
					'title' => Loc::getMessage('ELEMENT_ENTITY_WF_COMMENTS_FIELD'),
				]
			),
			'IN_SECTIONS' => new ORM\Fields\BooleanField(
				'IN_SECTIONS',
				[
					'values' => ['N', 'Y'],
					'title' => Loc::getMessage('ELEMENT_ENTITY_IN_SECTIONS_FIELD'),
				]
			),
			'XML_ID' => new ORM\Fields\StringField(
				'XML_ID',
				[
					'validation' => function()
					{
						return [
							new Validators\LengthValidator(null, 255),
						];
					},
					'title' => Loc::getMessage('ELEMENT_ENTITY_XML_ID_FIELD'),
				]
			),
			'CODE' => new ORM\Fields\StringField(
				'CODE',
				[
					'validation' => function()
					{
						return [
							new Validators\LengthValidator(null, 255),
						];
					},
					'title' => Loc::getMessage('ELEMENT_ENTITY_CODE_FIELD'),
				]
			),
			'TAGS' => new ORM\Fields\StringField(
				'TAGS',
				[
					'validation' => function()
					{
						return [
							new Validators\LengthValidator(null, 255),
						];
					},
					'title' => Loc::getMessage('ELEMENT_ENTITY_TAGS_FIELD'),
				]
			),
			'TMP_ID' => new ORM\Fields\StringField(
				'TMP_ID',
				[
					'validation' => function()
					{
						return [
							new Validators\LengthValidator(null, 40),
						];
					},
					'title' => Loc::getMessage('ELEMENT_ENTITY_TMP_ID_FIELD'),
				]
			),
			'SHOW_COUNTER' => new ORM\Fields\IntegerField(
				'SHOW_COUNTER',
				[
					'default_value' => 0,
					'title' => Loc::getMessage('ELEMENT_ENTITY_SHOW_COUNTER_FIELD'),
				]
			),
			'SHOW_COUNTER_START' => new ORM\Fields\DatetimeField(
				'SHOW_COUNTER_START',
				[
					'title' => Loc::getMessage('ELEMENT_ENTITY_SHOW_COUNTER_START_FIELD'),
				]
			),
			'IBLOCK' => new ORM\Fields\Relations\Reference(
				'IBLOCK',
				'\Bitrix\Iblock\Iblock',
				['=this.IBLOCK_ID' => 'ref.ID'],
				['join_type' => 'LEFT'],
			),
			'WF_PARENT_ELEMENT' => new ORM\Fields\Relations\Reference(
				'WF_PARENT_ELEMENT',
				'\Bitrix\Iblock\Element',
				['=this.WF_PARENT_ELEMENT_ID' => 'ref.ID'],
				['join_type' => 'LEFT'],
			),
			'IBLOCK_SECTION' => new ORM\Fields\Relations\Reference(
				'IBLOCK_SECTION',
				'\Bitrix\Iblock\Section',
				['=this.IBLOCK_SECTION_ID' => 'ref.ID'],
				['join_type' => 'LEFT'],
			),
			'MODIFIED_BY_USER' => new ORM\Fields\Relations\Reference(
				'MODIFIED_BY_USER',
				'\Bitrix\Main\User',
				['=this.MODIFIED_BY' => 'ref.ID'],
				['join_type' => 'LEFT'],
			),
			'CREATED_BY_USER' => new ORM\Fields\Relations\Reference(
				'CREATED_BY_USER',
				'\Bitrix\Main\User',
				['=this.CREATED_BY' => 'ref.ID'],
				['join_type' => 'LEFT'],
			),
			'WF_LOCKED_BY_USER' => new ORM\Fields\Relations\Reference(
				'WF_LOCKED_BY_USER',
				'\Bitrix\Main\User',
				['=this.WF_LOCKED_BY' => 'ref.ID'],
				['join_type' => 'LEFT'],
			),
		];
	}

	/**
	 * Add iblock element.
	 *
	 * @param array $data			Element data.
	 * @return ORM\Data\AddResult
	 */
	public static function add(array $data): ORM\Data\AddResult
	{
		$result = new ORM\Data\AddResult();
		$result->addError(new ORM\EntityError(
			Loc::getMessage('ELEMENT_ENTITY_MESS_ADD_BLOCKED')
		));

		return $result;
	}

	/**
	 * Updates iblock element by primary key.
	 *
	 * @param mixed $primary		Element primary key.
	 * @param array $data			Element data.
	 * @return ORM\Data\UpdateResult
	 */
	public static function update($primary, array $data): ORM\Data\UpdateResult
	{
		$result = new ORM\Data\UpdateResult();
		$result->addError(new ORM\EntityError(
			Loc::getMessage('ELEMENT_ENTITY_MESS_UPDATE_BLOCKED')
		));

		return $result;
	}

	/**
	 * Deletes iblock element by primary key.
	 *
	 * @param mixed $primary		Element primary key.
	 * @return ORM\Data\DeleteResult
	 */
	public static function delete($primary): ORM\Data\DeleteResult
	{
		$result = new ORM\Data\DeleteResult();
		$result->addError(new ORM\EntityError(
			Loc::getMessage('ELEMENT_ENTITY_MESS_DELETE_BLOCKED')
		));

		return $result;
	}
}
