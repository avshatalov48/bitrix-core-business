<?php
namespace Bitrix\Catalog;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Event;
use Bitrix\Main\ORM\EventResult;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\EnumField;
use Bitrix\Main\ORM\Fields\FloatField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\Relations\Reference;

/**
 * Class RoundingTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> CATALOG_GROUP_ID int mandatory
 * <li> PRICE double mandatory
 * <li> ROUND_TYPE int mandatory
 * <li> ROUND_PRECISION double mandatory
 * <li> CREATED_BY int optional
 * <li> DATE_CREATE datetime optional
 * <li> MODIFIED_BY int optional
 * <li> TIMESTAMP_X datetime optional
 * <li> CREATED_BY_USER reference to {@link \Bitrix\Main\UserTable}
 * <li> MODIFIED_BY_USER reference to {@link \Bitrix\Main\UserTable}
 * </ul>
 *
 * @package Bitrix\Catalog
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Rounding_Query query()
 * @method static EO_Rounding_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Rounding_Result getById($id)
 * @method static EO_Rounding_Result getList(array $parameters = [])
 * @method static EO_Rounding_Entity getEntity()
 * @method static \Bitrix\Catalog\EO_Rounding createObject($setDefaultValues = true)
 * @method static \Bitrix\Catalog\EO_Rounding_Collection createCollection()
 * @method static \Bitrix\Catalog\EO_Rounding wakeUpObject($row)
 * @method static \Bitrix\Catalog\EO_Rounding_Collection wakeUpCollection($rows)
 */

class RoundingTable extends DataManager
{
	public const ROUND_MATH = 0x0001;
	public const ROUND_UP = 0x0002;
	public const ROUND_DOWN = 0x0004;

	/** @var int clear rounding cache flag */
	protected static int $clearCache = 0;
	/** @var array price type list for clear */
	protected static array $priceTypeIds = [];

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName(): string
	{
		return 'b_catalog_rounding';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap(): array
	{
		return [
			'ID' => new IntegerField(
				'ID',
				[
					'primary' => true,
					'autocomplete' => true,
					'title' => Loc::getMessage('ROUNDING_ENTITY_ID_FIELD'),
				]
			),
			'CATALOG_GROUP_ID' => new IntegerField(
				'CATALOG_GROUP_ID',
				[
					'required' => true,
					'title' => Loc::getMessage('ROUNDING_ENTITY_CATALOG_GROUP_ID_FIELD'),
				]
			),
			'PRICE' => new FloatField(
				'PRICE',
				[
					'required' => true,
					'title' => Loc::getMessage('ROUNDING_ENTITY_PRICE_FIELD'),
				]
			),
			'ROUND_TYPE' => new EnumField(
				'ROUND_TYPE',
				[
					'required' => true,
					'values' => [
						self::ROUND_MATH,
						self::ROUND_UP,
						self::ROUND_DOWN,
					],
					'title' => Loc::getMessage('ROUNDING_ENTITY_ROUND_TYPE_FIELD'),
				]
			),
			'ROUND_PRECISION' => new FloatField(
				'ROUND_PRECISION',
				[
					'required' => true,
					'title' => Loc::getMessage('ROUNDING_ENTITY_ROUND_PRECISION_FIELD'),
				]
			),
			'CREATED_BY' => new IntegerField(
				'CREATED_BY',
				[
					'title' => Loc::getMessage('ROUNDING_ENTITY_CREATED_BY_FIELD'),
				]
			),
			'DATE_CREATE' => new DatetimeField(
				'DATE_CREATE',
				[
					'title' => Loc::getMessage('ROUNDING_ENTITY_DATE_CREATE_FIELD'),
				]
			),
			'MODIFIED_BY' => new IntegerField(
				'MODIFIED_BY',
				[
					'title' => Loc::getMessage('ROUNDING_ENTITY_MODIFIED_BY_FIELD'),
				]
			),
			'DATE_MODIFY' => new DatetimeField(
				'DATE_MODIFY',
				[
					'title' => Loc::getMessage('ROUNDING_ENTITY_TIMESTAMP_X_FIELD'),
				]
			),
			'CREATED_BY_USER' => new Reference(
				'CREATED_BY_USER',
				'\Bitrix\Main\User',
				['=this.CREATED_BY' => 'ref.ID'],
				['join_type' => 'LEFT']
			),
			'MODIFIED_BY_USER' => new Reference(
				'MODIFIED_BY_USER',
				'\Bitrix\Main\User',
				['=this.MODIFIED_BY' => 'ref.ID'],
				['join_type' => 'LEFT']
			),
		];
	}

	/**
	 * Default onBeforeAdd handler. Absolutely necessary.
	 *
	 * @param Event $event		Current data for add.
	 * @return EventResult
	 */
	public static function onBeforeAdd(Event $event): EventResult
	{
		$result = new EventResult();
		$data = $event->getParameter('fields');

		$modifyFieldList = [];
		static::setUserId(
			$modifyFieldList,
			$data,
			[
				'CREATED_BY',
				'MODIFIED_BY',
			]
		);
		static::setTimestamp(
			$modifyFieldList,
			$data,
			[
				'DATE_CREATE',
				'DATE_MODIFY',
			]
		);

		if (!empty($modifyFieldList))
		{
			$result->modifyFields($modifyFieldList);
		}
		unset($modifyFieldList);

		return $result;
	}

	/**
	 * Default onAfterAdd handler. Absolutely necessary.
	 *
	 * @param Event $event		Current data for add.
	 * @return void
	 */
	public static function onAfterAdd(Event $event): void
	{
		if (!static::isAllowedClearCache())
		{
			return;
		}
		$data = $event->getParameter('fields');
		self::$priceTypeIds[$data['CATALOG_GROUP_ID']] = $data['CATALOG_GROUP_ID'];
		unset($data);
		static::clearCache();
	}

	/**
	 * Default onBeforeUpdate handler. Absolutely necessary.
	 *
	 * @param Event $event		Current data for update.
	 * @return EventResult
	 */
	public static function onBeforeUpdate(Event $event): EventResult
	{
		$result = new EventResult();
		$data = $event->getParameter('fields');

		$modifyFieldList = [];
		static::setUserId(
			$modifyFieldList,
			$data,
			[
				'MODIFIED_BY',
			]
		);
		static::setTimestamp(
			$modifyFieldList,
			$data,
			[
				'DATE_MODIFY',
			]
		);

		if (!empty($modifyFieldList))
		{
			$result->modifyFields($modifyFieldList);
		}
		unset($modifyFieldList);

		return $result;
	}

	/**
	 * Default onUpdate handler. Absolutely necessary.
	 *
	 * @param Event $event		Current data for update.
	 * @return void
	 */
	public static function onUpdate(Event $event): void
	{
		if (!static::isAllowedClearCache())
		{
			return;
		}
		$data = $event->getParameter('fields');
		$rule = static::getRow([
			'select' => [
				'ID',
				'CATALOG_GROUP_ID',
			],
			'filter' => [
				'=ID' => $event->getParameter('id'),
			],
		]);
		if (!empty($rule))
		{
			self::$priceTypeIds[$rule['CATALOG_GROUP_ID']] = $rule['CATALOG_GROUP_ID'];
			if (isset($data['CATALOG_GROUP_ID']))
			{
				self::$priceTypeIds[$data['CATALOG_GROUP_ID']] = $data['CATALOG_GROUP_ID'];
			}
		}
		unset($rule, $data);
	}

	/**
	 * Default onAfterUpdate handler. Absolutely necessary.
	 *
	 * @param Event $event		Current data for update.
	 * @return void
	 */
	public static function onAfterUpdate(Event $event): void
	{
		static::clearCache();
	}

	/**
	 * Default onDelete handler. Absolutely necessary.
	 *
	 * @param Event $event		Current data for delete.
	 * @return void
	 */
	public static function onDelete(Event $event): void
	{
		if (!static::isAllowedClearCache())
		{
			return;
		}
		$rule = static::getRow([
			'select' => [
				'ID',
				'CATALOG_GROUP_ID',
			],
			'filter' => [
				'=ID' => $event->getParameter('id'),
			],
		]);
		if (!empty($rule))
		{
			self::$priceTypeIds[$rule['CATALOG_GROUP_ID']] = $rule['CATALOG_GROUP_ID'];
		}
		unset($rule);
	}

	/**
	 * Default onAfterDelete handler. Absolutely necessary.
	 *
	 * @param Event $event		Current data for delete.
	 * @return void
	 */
	public static function onAfterDelete(Event $event): void
	{
		static::clearCache();
	}

	/**
	 * Returns current allow mode for cache clearing.
	 *
	 * @return bool
	 */
	public static function isAllowedClearCache(): bool
	{
		return (self::$clearCache >= 0);
	}

	/**
	 * Allow clear cache after multiuse add/update/delete.
	 *
	 * @return void
	 */
	public static function allowClearCache(): void
	{
		self::$clearCache++;
	}

	/**
	 * Disallow clear cache before multiuse add/update/delete.
	 *
	 * @return void
	 */
	public static function disallowClearCache(): void
	{
		self::$clearCache--;
	}

	/**
	 * Clear price type ids.
	 *
	 * @return void
	 */
	public static function clearPriceTypeIds(): void
	{
		self::$priceTypeIds = array();
	}

	/**
	 * Set price type list for cache clearing.
	 *
	 * @param string|int|array $priceTypes		Price types for cache clearing.
	 * @return void
	 */
	public static function setPriceTypeIds(string|int|array $priceTypes): void
	{
		if (!is_array($priceTypes))
		{
			$priceTypes = [$priceTypes => $priceTypes];
		}

		if (!empty($priceTypes) && is_array($priceTypes))
		{
			self::$priceTypeIds = (
				empty(self::$priceTypeIds)
					? $priceTypes
					: array_merge(self::$priceTypeIds, $priceTypes)
			);
		}
	}

	/**
	 * Clear managed cache.
	 *
	 * @return void
	 */
	public static function clearCache(): void
	{
		if (!static::isAllowedClearCache() || empty(self::$priceTypeIds))
		{
			return;
		}
		foreach (self::$priceTypeIds as $priceType)
		{
			Product\Price::clearRoundRulesCache($priceType);
		}
		unset($priceType);
		static::clearPriceTypeIds();
	}

	/**
	 * Delete rules by price type.
	 *
	 * @param string|int $priceType		Price type id.
	 * @return void
	 */
	public static function deleteByPriceType(string|int $priceType): void
	{
		$priceType = (int)$priceType;
		if ($priceType <= 0)
		{
			return;
		}
		$conn = Main\Application::getConnection();
		$helper = $conn->getSqlHelper();
		$conn->queryExecute(
			'delete from '.$helper->quote(self::getTableName()).' where '.$helper->quote('CATALOG_GROUP_ID').' = '.$priceType
		);
		unset($helper, $conn);
		Product\Price::clearRoundRulesCache($priceType);
	}

	/**
	 * Return round types.
	 *
	 * @param bool $full		Get types with description.
	 * @return array
	 */
	public static function getRoundTypes(bool $full = false): array
	{
		if ($full)
		{
			return [
				self::ROUND_MATH => Loc::getMessage('ROUNDING_TYPE_ROUND_MATH'),
				self::ROUND_UP => Loc::getMessage('ROUNDING_TYPE_ROUND_UP'),
				self::ROUND_DOWN => Loc::getMessage('ROUNDING_TYPE_ROUND_DOWN'),
			];
		}
		return [
			self::ROUND_MATH,
			self::ROUND_UP,
			self::ROUND_DOWN
		];
	}

	/**
	 * Get preset rounding precision values for public interfaces and
	 * all kinds of validations
	 *
	 * @return array
	 */
	public static function getPresetRoundingValues(): array
	{
		return [
			0.0001,
			0.001,
			0.005,
			0.01,
			0.02,
			0.05,
			0.1,
			0.2,
			0.5,
			1,
			2,
			5,
			10,
			20,
			50,
			100,
			200,
			500,
			1000,
			5000
		];
	}

	/**
	 * Fill user id fields.
	 *
	 * @param array &$result			Modified data for add/update discount.
	 * @param array $data				Current data for add/update discount.
	 * @param array $keys				List with checked keys (userId info).
	 * @return void
	 */
	protected static function setUserId(array &$result, array $data, array $keys): void
	{
		static $currentUserID = false;
		if ($currentUserID === false)
		{
			global $USER;
			/** @noinspection PhpMethodOrClassCallIsNotCaseSensitiveInspection */
			$currentUserID = (isset($USER) && $USER instanceof \CUser ? (int)$USER->getID() : null);
		}
		foreach ($keys as $index)
		{
			$setField = true;
			if (array_key_exists($index, $data))
			{
				$setField = ($data[$index] !== null && (int)$data[$index] <= 0);
			}

			if ($setField)
			{
				$result[$index] = $currentUserID;
			}
		}
		unset($index);
	}

	/**
	 * Fill datetime fields.
	 *
	 * @param array &$result			Modified data for add/update discount.
	 * @param array $data				Current data for add/update discount.
	 * @param array $keys				List with checked keys (datetime info).
	 * @return void
	 */
	protected static function setTimestamp(array &$result, array $data, array $keys): void
	{
		foreach ($keys as $index)
		{
			$setField = true;
			if (array_key_exists($index, $data))
			{
				$setField = ($data[$index] !== null && !is_object($data[$index]));
			}

			if ($setField)
			{
				$result[$index] = new Main\Type\DateTime();
			}
		}
		unset($index);
	}
}
