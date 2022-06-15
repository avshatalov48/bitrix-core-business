<?php

namespace Bitrix\Catalog;

use Bitrix\Main;
use Bitrix\Main\ORM;
use Bitrix\Main\Localization\Loc;

/**
 * Class GroupTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> NAME string(100) mandatory
 * <li> BASE bool optional default 'N'
 * <li> SORT int optional default 100
 * <li> XML_ID string(255) optional
 * <li> TIMESTAMP_X datetime optional
 * <li> MODIFIED_BY int optional
 * <li> DATE_CREATE datetime optional
 * <li> CREATED_BY int optional
 * <li> LANG reference to {@link \Bitrix\Catalog\GroupLang}
 * <li> CURRENT_LANG reference to {@link \Bitrix\Catalog\GroupLang} with current lang (LANGUAGE_ID)
 * </ul>
 *
 * @package Bitrix\Catalog
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Group_Query query()
 * @method static EO_Group_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Group_Result getById($id)
 * @method static EO_Group_Result getList(array $parameters = array())
 * @method static EO_Group_Entity getEntity()
 * @method static \Bitrix\Catalog\EO_Group createObject($setDefaultValues = true)
 * @method static \Bitrix\Catalog\EO_Group_Collection createCollection()
 * @method static \Bitrix\Catalog\EO_Group wakeUpObject($row)
 * @method static \Bitrix\Catalog\EO_Group_Collection wakeUpCollection($rows)
 */

class GroupTable extends ORM\Data\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_catalog_group';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			'ID' => new ORM\Fields\IntegerField(
				'ID',
				[
					'primary' => true,
					'autocomplete' => true,
					'title' => Loc::getMessage('GROUP_ENTITY_ID_FIELD'),
				]
			),
			'NAME' => new ORM\Fields\StringField(
				'NAME',
				[
					'required' => true,
					'validation' => function()
					{
						return [
							new ORM\Fields\Validators\LengthValidator(null, 100),
						];
					},
					'title' => Loc::getMessage('GROUP_ENTITY_NAME_FIELD'),
				]
			),
			'BASE' => new ORM\Fields\BooleanField(
				'BASE',
				[
					'values' => [
						'N',
						'Y',
					],
					'title' => Loc::getMessage('GROUP_ENTITY_BASE_FIELD'),
				]
			),
			'SORT' => new ORM\Fields\IntegerField(
				'SORT',
				[
					'title' => Loc::getMessage('GROUP_ENTITY_SORT_FIELD'),
				]
			),
			'XML_ID' => new ORM\Fields\StringField(
				'XML_ID',
				[
					'validation' => function()
					{
						return [
							new ORM\Fields\Validators\LengthValidator(null, 255),
						];
					},
					'title' => Loc::getMessage('GROUP_ENTITY_XML_ID_FIELD'),
				]
			),
			'TIMESTAMP_X' => new ORM\Fields\DatetimeField(
				'TIMESTAMP_X',
				[
					'title' => Loc::getMessage('GROUP_ENTITY_TIMESTAMP_X_FIELD'),
					'default_value' => function()
					{
						return new Main\Type\DateTime();
					},
				]
			),
			'MODIFIED_BY' => new ORM\Fields\IntegerField(
				'MODIFIED_BY',
				[
					'title' => Loc::getMessage('GROUP_ENTITY_MODIFIED_BY_FIELD'),
				]
			),
			'DATE_CREATE' => new ORM\Fields\DatetimeField(
				'DATE_CREATE',
				[
					'title' => Loc::getMessage('GROUP_ENTITY_DATE_CREATE_FIELD'),
					'default_value' => function()
					{
						return new Main\Type\DateTime();
					},
				]
			),
			'CREATED_BY' => new ORM\Fields\IntegerField(
				'CREATED_BY',
				[
					'title' => Loc::getMessage('GROUP_ENTITY_CREATED_BY_FIELD'),
				]
			),
			'CREATED_BY_USER' => new ORM\Fields\Relations\Reference(
				'CREATED_BY_USER',
				'\Bitrix\Main\User',
				['=this.CREATED_BY' => 'ref.ID']
			),
			'MODIFIED_BY_USER' => new ORM\Fields\Relations\Reference(
				'MODIFIED_BY_USER',
				'\Bitrix\Main\User',
				['=this.MODIFIED_BY' => 'ref.ID']
			),
			'LANG' => new ORM\Fields\Relations\Reference(
				'LANG',
				'\Bitrix\Catalog\GroupLang',
				['=this.ID' => 'ref.CATALOG_GROUP_ID']
			),
			'CURRENT_LANG' => new ORM\Fields\Relations\Reference(
				'CURRENT_LANG',
				'\Bitrix\Catalog\GroupLang',
				[
					'=this.ID' => 'ref.CATALOG_GROUP_ID',
					'=ref.LANG' => new Main\DB\SqlExpression('?', LANGUAGE_ID)
				],
				['join_type' => 'LEFT']
			)
		];
	}

	/**
	 * Default onAfterAdd handler. Absolutely necessary.
	 *
	 * @param ORM\Event $event Current data for add.
	 * @return void
	 */
	public static function onAfterAdd(ORM\Event $event): void
	{
		Model\Price::clearSettings();
	}

	/**
	 * Default onAfterUpdate handler. Absolutely necessary.
	 *
	 * @param ORM\Event $event Current data for add.
	 * @return void
	 */
	public static function onAfterUpdate(ORM\Event $event): void
	{
		Model\Price::clearSettings();
	}

	/**
	 * Default onAfterDelete handler. Absolutely necessary.
	 *
	 * @param ORM\Event $event Current data for add.
	 * @return void
	 */
	public static function onAfterDelete(ORM\Event $event): void
	{
		Model\Price::clearSettings();
	}

	/**
	 * Returns cached base price type or null.
	 *
	 * @return array|null
	 */
	public static function getBasePriceType(): ?array
	{
		$row = self::getList([
			'select' => [
				'ID',
				'NAME',
				'BASE',
				'SORT',
				'XML_ID',
				'NAME_LANG' =>'CURRENT_LANG.NAME',
			],
			'filter' => [
				'=BASE' => 'Y',
			],
			'cache' => [
				'ttl' => 86400,
			],
		])->fetch();

		if (!empty($row))
		{
			$row['ID'] = (int)$row['ID'];
			$row['SORT'] = (int)$row['SORT'];

			return $row;
		}

		return null;
	}

	/**
	 * Returns cached base price type id or null.
	 *
	 * @return int|null
	 */
	public static function getBasePriceTypeId(): ?int
	{
		$row = self::getBasePriceType();

		return $row === null ? null : $row['ID'];
	}

	/**
	 * Returns cached price type list.
	 *
	 * @return array
	 */
	public static function getTypeList(): array
	{
		$result = [];

		$iterator = self::getList([
			'select' => [
				'ID',
				'NAME',
				'BASE',
				'SORT',
				'XML_ID',
				'NAME_LANG' =>'CURRENT_LANG.NAME',
			],
			'order' => [
				'SORT' => 'ASC',
				'ID' => 'ASC',
			],
			'cache' => [
				'ttl' => 86400,
			],
		]);
		while ($row = $iterator->fetch())
		{
			$row['ID'] = (int)$row['ID'];
			$row['SORT'] = (int)$row['SORT'];

			$result[$row['ID']] = $row;
		}
		unset($row, $groupIterator);

		return $result;
	}
}
