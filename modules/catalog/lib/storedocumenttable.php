<?php
namespace Bitrix\Catalog;

use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\EnumField;
use Bitrix\Main\ORM\Fields\FloatField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\UserTable;

/**
 * Class StoreDocumentTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> TITLE string(255) optional
 * <li> DOC_TYPE string(1) mandatory
 * <li> DOC_NUMBER string(64) optional
 * <li> SITE_ID string(2) optional
 * <li> CONTRACTOR_ID int optional
 * <li> DATE_MODIFY datetime optional
 * <li> DATE_CREATE datetime optional
 * <li> CREATED_BY int optional
 * <li> MODIFIED_BY int optional
 * <li> RESPONSIBLE_ID int optional
 * <li> CURRENCY string(3) optional
 * <li> STATUS bool ('N', 'Y') optional default 'N'
 * <li> WAS_CANCELLED bool ('N', 'Y') optional default 'N'
 * <li> DATE_STATUS datetime optional
 * <li> DATE_DOCUMENT datetime optional
 * <li> STATUS_BY int optional
 * <li> TOTAL double optional
 * <li> COMMENTARY string(1000) optional
 * <li> ITEMS_ORDER_DATE datetime optional
 * <li> ITEMS_RECEIVED_DATE datetime optional
 * <li> CONTRACTOR reference to {@link \Bitrix\Catalog\ContractorTable}
 * </ul>
 *
 * @package Bitrix\Catalog
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_StoreDocument_Query query()
 * @method static EO_StoreDocument_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_StoreDocument_Result getById($id)
 * @method static EO_StoreDocument_Result getList(array $parameters = [])
 * @method static EO_StoreDocument_Entity getEntity()
 * @method static \Bitrix\Catalog\EO_StoreDocument createObject($setDefaultValues = true)
 * @method static \Bitrix\Catalog\EO_StoreDocument_Collection createCollection()
 * @method static \Bitrix\Catalog\EO_StoreDocument wakeUpObject($row)
 * @method static \Bitrix\Catalog\EO_StoreDocument_Collection wakeUpCollection($rows)
 */

class StoreDocumentTable extends DataManager
{
	public const TYPE_ARRIVAL = 'A';
	public const TYPE_STORE_ADJUSTMENT = 'S';
	public const TYPE_MOVING = 'M';
	public const TYPE_RETURN = 'R';
	public const TYPE_DEDUCT = 'D';
	public const TYPE_UNDO_RESERVE = 'U';
	public const TYPE_SALES_ORDERS = 'W';
	//public const TYPE_INVENTORY = 'I';

	public const STATUS_CONDUCTED = 'Y';
	public const STATUS_DRAFT = 'N';
	public const STATUS_CANCELLED = 'C';

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_catalog_store_docs';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			'ID' => new IntegerField(
				'ID',
				[
					'primary' => true,
					'autocomplete' => true,
					'title' => Loc::getMessage('INVENTORY_DOCUMENT_ENTITY_ID_FIELD'),
				]
			),
			'TITLE' => new StringField(
				'TITLE',
				[
					'validation' => [__CLASS__, 'validateTitle'],
					'title' => Loc::getMessage('INVENTORY_DOCUMENT_ENTITY_TITLE_FIELD')
				]
			),
			'DOC_TYPE' => new EnumField(
				'DOC_TYPE',
				[
					'required' => true,
					'values' => static::getTypeList(),
					'title' => Loc::getMessage('INVENTORY_DOCUMENT_ENTITY_DOC_TYPE_FIELD'),
				]
			),
			'DOC_NUMBER' => new StringField(
				'DOC_NUMBER',
				[
					'validation' => [__CLASS__, 'validateDocNumber'],
					'title' => Loc::getMessage('INVENTORY_DOCUMENT_ENTITY_DOC_NUMBER_FIELD'),
				]
			),
			'SITE_ID' => new StringField(
				'SITE_ID',
				[
					'validation' => [__CLASS__, 'validateSiteId'],
					'title' => Loc::getMessage('INVENTORY_DOCUMENT_ENTITY_SITE_ID_FIELD'),
				]
			),
			'CONTRACTOR' => new Main\ORM\Fields\Relations\Reference(
				'CONTRACTOR',
				ContractorTable::class,
				Main\ORM\Query\Join::on('this.CONTRACTOR_ID', 'ref.ID')
			),
			'CONTRACTOR_ID' => new IntegerField(
				'CONTRACTOR_ID',
				[
					'title' => Loc::getMessage('INVENTORY_DOCUMENT_ENTITY_CONTRACTOR_ID_FIELD'),
				]
			),
			'DATE_MODIFY' => new DatetimeField(
				'DATE_MODIFY',
				[
					'default_value' => function()
						{
							return new Main\Type\DateTime();
						},
					'title' => Loc::getMessage('INVENTORY_DOCUMENT_ENTITY_DATE_MODIFY_FIELD'),
				]
			),
			'DATE_CREATE' => new DatetimeField(
				'DATE_CREATE',
				[
					'default_value' => function()
					{
						return new Main\Type\DateTime();
					},
					'title' => Loc::getMessage('INVENTORY_DOCUMENT_ENTITY_DATE_CREATE_FIELD'),
				]
			),
			'CREATED_BY' => new IntegerField(
				'CREATED_BY',
				[
					'title' => Loc::getMessage('INVENTORY_DOCUMENT_ENTITY_CREATED_BY_FIELD'),
				]
			),
			'CREATED_BY_USER' => new Main\ORM\Fields\Relations\Reference(
				'CREATED_BY_USER',
				UserTable::class,
				Main\ORM\Query\Join::on('this.CREATED_BY', 'ref.ID')
			),
			'MODIFIED_BY' => new IntegerField(
				'MODIFIED_BY',
				[
					'title' => Loc::getMessage('INVENTORY_DOCUMENT_ENTITY_MODIFIED_BY_FIELD'),
				]
			),
			'MODIFIED_BY_USER' => new Main\ORM\Fields\Relations\Reference(
				'MODIFIED_BY_USER',
				UserTable::class,
				Main\ORM\Query\Join::on('this.MODIFIED_BY', 'ref.ID')
			),
			'RESPONSIBLE_ID' => new IntegerField(
				'RESPONSIBLE_ID',
				[
					'title' => Loc::getMessage('INVENTORY_DOCUMENT_ENTITY_RESPONSIBLE_ID_FIELD')
				]
			),
			'RESPONSIBLE' => new Main\ORM\Fields\Relations\Reference(
				'RESPONSIBLE',
				UserTable::class,
				Main\ORM\Query\Join::on('this.RESPONSIBLE_ID', 'ref.ID')
			),
			'CURRENCY' => new StringField(
				'CURRENCY',
				[
					'validation' => [__CLASS__, 'validateCurrency'],
					'title' => Loc::getMessage('INVENTORY_DOCUMENT_ENTITY_CURRENCY_FIELD'),
				]
			),
			'STATUS' => new BooleanField(
				'STATUS',
				[
					'values' => ['N', 'Y'],
					'default' => 'N',
					'title' => Loc::getMessage('INVENTORY_DOCUMENT_ENTITY_STATUS_FIELD'),
				]
			),
			'WAS_CANCELLED' => new BooleanField(
				'WAS_CANCELLED',
				[
					'values' => array('N', 'Y'),
					'default' => 'N',
					'title' => Loc::getMessage('INVENTORY_DOCUMENT_ENTITY_WAS_CANCELLED_FIELD'),
				]
			),
			'DATE_STATUS' => new DatetimeField(
				'DATE_STATUS',
				[
					'default_value' => null,
					'title' => Loc::getMessage('INVENTORY_DOCUMENT_ENTITY_DATE_STATUS_FIELD'),
				]
			),
			'DATE_DOCUMENT' => new DatetimeField(
				'DATE_DOCUMENT',
				[
					'default_value' => function()
					{
						return new Main\Type\DateTime();
					},
					'title' => Loc::getMessage('INVENTORY_DOCUMENT_ENTITY_DATE_DOCUMENT_FIELD'),
				]
			),
			'STATUS_BY' => new IntegerField(
				'STATUS_BY',
				[
					'title' => Loc::getMessage('INVENTORY_DOCUMENT_ENTITY_STATUS_BY_FIELD'),
				]
			),
			'STATUS_BY_USER' => new Main\ORM\Fields\Relations\Reference(
				'STATUS_BY_USER',
				UserTable::class,
				Main\ORM\Query\Join::on('this.STATUS_BY', 'ref.ID')
			),
			'TOTAL' => new FloatField(
				'TOTAL',
				[
					'title' => Loc::getMessage('INVENTORY_DOCUMENT_ENTITY_TOTAL_FIELD'),
				]
			),
			'COMMENTARY' => new StringField(
				'COMMENTARY',
				[
					'validation' => [__CLASS__, 'validateCommentary'],
					'title' => Loc::getMessage('INVENTORY_DOCUMENT_ENTITY_COMMENTARY_FIELD'),
				]
			),
			'ITEMS_ORDER_DATE' => new DatetimeField(
				'ITEMS_ORDER_DATE',
				[
					'title' => Loc::getMessage('INVENTORY_DOCUMENT_ENTITY_ITEMS_ORDER_DATE_FIELD')
				]
			),
			'ITEMS_RECEIVED_DATE' => new DatetimeField(
				'ITEMS_RECEIVED_DATE',
				[
					'title' => Loc::getMessage('INVENTORY_DOCUMENT_ENTITY_ITEMS_RECEIVED_DATE_FIELD')
				]
			),
			'ELEMENTS' => (new Main\ORM\Fields\Relations\OneToMany(
				'ELEMENTS',
				StoreDocumentElementTable::class,
				'DOCUMENT'
			))->configureJoinType('left'),
		];
	}

	/**
	 * Returns validators for DOC_NUMBER field.
	 *
	 * @return array
	 */
	public static function validateDocNumber(): array
	{
		return [
			new LengthValidator(null, 64),
		];
	}

	/**
	 * Returns validators for TITLE field.
	 *
	 * @return array
	 */
	public static function validateTitle()
	{
		return [
			new LengthValidator(null, 255),
		];
	}

	/**
	 * Returns validators for SITE_ID field.
	 *
	 * @return array
	 */
	public static function validateSiteId(): array
	{
		return [
			new LengthValidator(null, 2),
		];
	}

	/**
	 * Returns validators for CURRENCY field.
	 *
	 * @return array
	 */
	public static function validateCurrency(): array
	{
		return [
			new LengthValidator(3, 3),
		];
	}

	/**
	 * Returns validators for COMMENTARY field.
	 *
	 * @return array
	 */
	public static function validateCommentary(): array
	{
		return [
			new LengthValidator(null, 1000),
		];
	}

	public static function getTypeList(bool $description = false): array
	{
		if ($description)
		{
			return [
				self::TYPE_ARRIVAL => Loc::getMessage('INVENTORY_DOCUMENT_TYPE_ARRIVAL'),
				self::TYPE_STORE_ADJUSTMENT => Loc::getMessage('INVENTORY_DOCUMENT_TYPE_STORE_ADJUSTMENT'),
				self::TYPE_MOVING => Loc::getMessage('INVENTORY_DOCUMENT_TYPE_MOVING'),
				self::TYPE_RETURN => Loc::getMessage('INVENTORY_DOCUMENT_TYPE_RETURN'),
				self::TYPE_DEDUCT => Loc::getMessage('INVENTORY_DOCUMENT_TYPE_DEDUCT'),
				self::TYPE_UNDO_RESERVE => Loc::getMessage('INVENTORY_DOCUMENT_TYPE_UNDO_RESERVE'),
			];
		}
		else
		{
			return [
				self::TYPE_ARRIVAL,
				self::TYPE_STORE_ADJUSTMENT,
				self::TYPE_MOVING,
				self::TYPE_RETURN,
				self::TYPE_DEDUCT,
				self::TYPE_UNDO_RESERVE,
			];
		}
	}

	public static function getStatusList(): array
	{
		return [
			self::STATUS_CONDUCTED => Loc::getMessage('INVENTORY_DOCUMENT_STATUS_CONDUCTED'),
			self::STATUS_DRAFT => Loc::getMessage('INVENTORY_DOCUMENT_STATUS_DRAFT'),
			self::STATUS_CANCELLED => Loc::getMessage('INVENTORY_DOCUMENT_STATUS_CANCELLED'),
		];
	}

	public static function getStatusName(string $status): ?string
	{
		$statusList = static::getStatusList();

		return $statusList[$status] ?? null;
	}

	public static function getOrmFilterByStatus(string $status): ?array
	{
		switch ($status)
		{
			case self::STATUS_CONDUCTED:
				$result = [
					'=STATUS' => 'Y',
				];
				break;
			case self::STATUS_DRAFT:
				$result = [
					'=STATUS' => 'N',
					'=WAS_CANCELLED' => 'N',
				];
				break;
			case self::STATUS_CANCELLED:
				$result = [
					'=STATUS' => 'N',
					'=WAS_CANCELLED' => 'Y',
				];
				break;
			default:
				$result = null;
				break;
		}

		return $result;
	}

	/*
	 * The following methods are used to select the documents that include a particular product/set of products.
	 * They are to be used with the \Bitrix\Main\ORM\Query\Query object (i.e. not with getList).
	 *
	 * Example:
	 * $docs = Catalog\StoreDocumentTable::query()->setSelect(['ID'])->withProduct($productId)->fetchAll();
	 */

	public static function withProduct(Main\ORM\Query\Query $query, $productId)
	{
		$productId = (int)$productId;
		if ($productId <= 0)
		{
			return;
		}

		$tableName = StoreDocumentElementTable::getTableName();
		$query->whereExpr("
			(
				CASE WHEN EXISTS (
					SELECT ID
					FROM {$tableName}
					WHERE DOC_ID = %s
					AND ELEMENT_ID = {$productId}
				)
				THEN 1
				ELSE 0
				END
			) = 1
		", ['ID']);
	}

	public static function withProductList(Main\ORM\Query\Query $query, array $productIds)
	{
		Main\Type\Collection::normalizeArrayValuesByInt($productIds);
		if (empty($productIds))
		{
			return;
		}

		$tableName = StoreDocumentElementTable::getTableName();
		$whereExpression = '(ELEMENT_ID IN (' . implode(',', $productIds) . '))';
		$query->whereExpr("
			(
				CASE WHEN EXISTS (
					SELECT ID
					FROM {$tableName}
					WHERE DOC_ID = %s
					AND {$whereExpression}
				)
				THEN 1
				ELSE 0
				END
			) = 1
		", ['ID']);
	}

	public static function withStore(Main\ORM\Query\Query $query, $storeId)
	{
		$storeId = (int)$storeId;
		if ($storeId <= 0)
		{
			return;
		}

		$tableName = StoreDocumentElementTable::getTableName();
		$query->whereExpr("
			(
				CASE WHEN EXISTS (
					SELECT ID
					FROM {$tableName}
					WHERE DOC_ID = %s
					AND (
						STORE_FROM = {$storeId}
						OR STORE_TO = {$storeId}
					)
				)
				THEN 1
				ELSE 0
				END
			) = 1
		", ['ID']);
	}

	public static function withStoreList(Main\ORM\Query\Query $query, array $storeIds)
	{
		Main\Type\Collection::normalizeArrayValuesByInt($storeIds);
		if (empty($storeIds))
		{
			return;
		}

		$storeIdsForQuery = implode(',', $storeIds);
		$whereExpression = "(STORE_FROM IN ({$storeIdsForQuery}) OR STORE_TO IN ({$storeIdsForQuery}))";
		$tableName = StoreDocumentElementTable::getTableName();

		$query->whereExpr("
			(
				CASE WHEN EXISTS (
					SELECT ID
					FROM {$tableName}
					WHERE DOC_ID = %s
					AND {$whereExpression}
				)
				THEN 1
				ELSE 0
				END
			) = 1
		", ['ID']);
	}

	public static function withStoreFromList(Main\ORM\Query\Query $query, array $storeIds)
	{
		static::addSingleStoreFilterToQuery($query, 'STORE_FROM', $storeIds);
	}

	public static function withStoreToList(Main\ORM\Query\Query $query, array $storeIds)
	{
		static::addSingleStoreFilterToQuery($query, 'STORE_TO', $storeIds);
	}

	protected static function addSingleStoreFilterToQuery(Main\ORM\Query\Query $query, string $fieldName, array $storeIds): void
	{
		Main\Type\Collection::normalizeArrayValuesByInt($storeIds);
		if (empty($storeIds))
		{
			return;
		}

		$filter = new Main\ORM\Query\Filter\ConditionTree();
		$filter
			->whereIn('ref.' . $fieldName, $storeIds)
		;
		$query->registerRuntimeField(
			new ReferenceField(
				'FILTER_' . $fieldName . '_DOC_ID',
				StoreDocumentElementTable::getEntity(),
				Join::on('ref.DOC_ID', 'this.ID')->where($filter),
				['join_type' => 'INNER']
			)
		);
		$query->addGroup('ID');
	}
}
