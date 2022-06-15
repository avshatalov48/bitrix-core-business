<?php

namespace Bitrix\Catalog\Product\SystemField\ProductMapping;

use Bitrix\Catalog\Product\SystemField\ProductMapping;
use Bitrix\Catalog\ProductTable;
use Bitrix\Main\Application;
use Bitrix\Main\DB\Result;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Update\Stepper;
use Bitrix\Main\UserField\Internal\UserFieldHelper;
use Throwable;

Loc::loadMessages(__FILE__);

/**
 * Filling default value for UF product mapping.
 * Only products are filled in, without a filled value.
 */
class SetDefaultValueStepper extends Stepper
{
	protected static $moduleId = "catalog";

	public static function getTitle()
	{
		return Loc::getMessage('CATALOG_PRODUCT_SYSTEMFIELD_PRODUCTMAPPING_SET_DEFAULT_VALUE_STEPPER_TITLE');
	}

	/**
	 * @inheritDoc
	 */
	public function execute(array &$option)
	{
		$userFieldManager = UserFieldHelper::getInstance()->getManager();

		if (!ProductMapping::isAllowed())
		{
			return self::FINISH_EXECUTION;
		}
		$fieldDescription = ProductMapping::getUserFieldBaseParam();

		// first run
		if (!isset($option['is_started']))
		{
			return $this->firstRun($option);
		}

		// processing
		$defaultValues = $this->getDefaultValues();
		if (!$defaultValues)
		{
			return self::FINISH_EXECUTION;
		}

		$lastId = (int)($option['last_id'] ?? 0);
		$products = $this->getProductsWithEmptyValue($lastId);
		if ($products->getSelectedRowsCount() === 0)
		{
			return self::FINISH_EXECUTION;
		}
		$option['steps'] += $products->getSelectedRowsCount();

		$db = Application::getConnection();
		try
		{
			$db->startTransaction();

			$entityId = ProductTable::getUfId();
			$fieldId = $fieldDescription['FIELD_NAME'];
			foreach ($products as $row)
			{
				$productId = (int)$row['id'];
				$userFieldManager->Update(
					$entityId,
					$productId,
					[
						$fieldId => $defaultValues,
					]
				);
				$lastId = $productId;
			}

			$db->commitTransaction();
		}
		catch (Throwable $e)
		{
			$db->rollbackTransaction();

			throw $e;
		}

		$option['last_id'] = $lastId;

		unset($userFieldManager);

		return self::CONTINUE_EXECUTION;
	}

	/**
	 * First run stepper executer.
	 *
	 * @param array $option
	 *
	 * @return bool
	 */
	private function firstRun(array & $option): bool
	{
		$existUfTable = Application::getConnection()->query("SHOW TABLES LIKE 'b_uts_product'")->getSelectedRowsCount() > 0;
		if (!$existUfTable)
		{
			return self::FINISH_EXECUTION;
		}

		$existUfColumn = Application::getConnection()->query("SHOW COLUMNS FROM `b_uts_product` LIKE 'UF_PRODUCT_MAPPING'")->getSelectedRowsCount() > 0;
		if (!$existUfColumn)
		{
			return self::FINISH_EXECUTION;
		}

		$option['count'] = $this->getProductsToBeProcessedTotalCount();
		$option['title'] = self::getTitle();
		$option['is_started'] = true;
		$option['last_id'] = null;

		return self::CONTINUE_EXECUTION;
	}

	/**
	 * Build SQL query.
	 *
	 * @param bool $isCountSelect
	 * @param int|null $lastId
	 *
	 * @return string
	 */
	private function getProductsQuery(bool $isCountSelect = false, int $lastId = 0): string
	{
		$typeIds = join(',', [
			ProductTable::TYPE_PRODUCT,
			ProductTable::TYPE_SET,
			ProductTable::TYPE_SKU,
		]);

		$select = 'b_catalog_product.id as `id`';
		if ($isCountSelect)
		{
			$select = 'COUNT(b_catalog_product.id)';
		}

		$where = '';
		if ($lastId)
		{
			$where = new SqlExpression("AND b_catalog_product.id > ?i", $lastId);
		}

		return trim("
		SELECT {$select}
		FROM b_catalog_product
		LEFT JOIN b_uts_product ON b_catalog_product.id = b_uts_product.value_id
		WHERE b_catalog_product.TYPE in ({$typeIds})
		AND b_uts_product.UF_PRODUCT_MAPPING IS NULL {$where}
		ORDER BY b_catalog_product.id ASC
		");
	}

	/**
	 * Products with empty value for this iteration.
	 *
	 * @param int $lastId
	 * @return Result
	 */
	private function getProductsWithEmptyValue(int $lastId): Result
	{
		$limit = 10;
		$sql = $this->getProductsQuery(false, $lastId);

		return Application::getConnection()->query($sql, $limit);
	}

	/**
	 * The total count of products that will be processed.
	 *
	 * @return int
	 */
	private function getProductsToBeProcessedTotalCount(): int
	{
		$sql = $this->getProductsQuery(true);

		return (int)Application::getConnection()->queryScalar($sql);
	}

	/**
	 * Default values for filling.
	 *
	 * @return array|null
	 */
	private function getDefaultValues(): ?array
	{
		$config = ProductMapping::getConfig();
		$tableName = $config['HIGHLOADBLOCK']['TABLE_NAME'] ?? null;
		if (!$tableName)
		{
			return null;
		}

		$values = [];
		$values[] = Application::getConnection()->queryScalar(
			new SqlExpression("SELECT ID FROM ?# WHERE UF_XML_ID = 'LANDING'", $tableName)
		);

		return $values;
	}
}
