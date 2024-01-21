<?php

namespace Bitrix\Catalog\InventoryManagement\Helpers;

use Bitrix\Catalog\ProductTable;
use Bitrix\Catalog\StoreProductTable;
use Bitrix\Main\Application;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Text\HtmlFilter;

/**
 * Doctor for inventory management.
 *
 * @internal use it only for debug!
 *
 * This object does not contain a "Cure all" button, because inventory management is falling apart for various reasons,
 * and if a large number of products have discrepancies in the catalog,
 * then there are serious problems and you need to understand in more detail what is the matter.
 *
 * Example show summary info:
 * ```php
	\Bitrix\Main\Loader::includeModule('catalog');

	$doctor = new \Bitrix\Catalog\InventoryManagement\Helpers\Doctor();
	$doctor->printInfo();
	// or show only problems
	$doctor->printProblems();
 * ```
 *
 * For fixes see that methods:
 * - fixQuantitiesFromStores
 * - fixReservesFromStores
 * - fixReservesLessZero
 */
final class Doctor
{
	/**
	 * Get SQL to select information about reserves and stocks.
	 *
	 * It can be used for customization and direct SQL queries.
	 *
	 * @return string
	 */
	private function getSql(): string
	{
		$conn = Application::getConnection();
		$helper = $conn->getSqlHelper();
		return '
			SELECT
				cp.ID AS ' . $helper->quote('PRODUCT_ID') . ',
				cp.QUANTITY AS ' . $helper->quote('PRODUCT_QUANTITY_AVAILABLE') . ',
				csp.QUANTITY_AVAILABLE AS ' . $helper->quote('STORE_QUANTITY_AVAILABLE') . ',
				cp.QUANTITY_RESERVED AS ' . $helper->quote('PRODUCT_QUANTITY_RESERVED') . ',
				csp.QUANTITY_RESERVED AS ' . $helper->quote('STORE_QUANTITY_RESERVED') . ',
				sbr.QUANTITY_RESERVED AS ' . $helper->quote('SALE_QUANTITY_RESERVED') . '
			FROM
				b_catalog_product AS cp
				LEFT JOIN (
					SELECT
						PRODUCT_ID,
						SUM(AMOUNT) as QUANTITY,
						SUM(QUANTITY_RESERVED) as QUANTITY_RESERVED,
						SUM(AMOUNT - QUANTITY_RESERVED) as QUANTITY_AVAILABLE
					FROM
						b_catalog_store_product
						INNER JOIN b_catalog_store ON b_catalog_store.ID = b_catalog_store_product.STORE_ID AND b_catalog_store.ACTIVE = \'Y\'
					GROUP BY PRODUCT_ID
				) as csp ON cp.ID = csp.PRODUCT_ID
				LEFT JOIN (
					SELECT
						sb.PRODUCT_ID,
						SUM(sbr.QUANTITY) AS QUANTITY_RESERVED
					FROM
						b_sale_basket AS sb
						LEFT JOIN b_sale_basket_reservation AS sbr ON sbr.BASKET_ID = sb.ID AND sbr.QUANTITY != 0
					WHERE
						sb.PRODUCT_ID > 0
						AND sbr.QUANTITY != 0
					GROUP BY sb.PRODUCT_ID
				) as sbr ON cp.ID = sbr.PRODUCT_ID
			WHERE
				(
					cp.QUANTITY != 0
					OR cp.QUANTITY_RESERVED != 0
					OR csp.QUANTITY != 0
					OR csp.QUANTITY_RESERVED != 0
					OR csp.QUANTITY_AVAILABLE != 0
					OR sbr.QUANTITY_RESERVED != 0
				)
		';
	}

	/**
	 * Print catalog and sale problems.
	 *
	 * @return void
	 */
	public function printProblems(): void
	{
		$sql = $this->getSql() . ' AND (
			cp.QUANTITY != csp.QUANTITY_AVAILABLE OR csp.QUANTITY_AVAILABLE IS NULL
			OR cp.QUANTITY_RESERVED != csp.QUANTITY_RESERVED OR csp.QUANTITY_RESERVED IS NULL
			OR sbr.QUANTITY_RESERVED > cp.QUANTITY_RESERVED
			OR cp.QUANTITY_RESERVED < 0
			OR csp.QUANTITY_RESERVED < 0
		)';

		$result = [];

		$rows = Application::getConnection()->query($sql);
		foreach ($rows as $row)
		{
			$problems = [];

			$storeReserveQuantity = (float)$row['STORE_QUANTITY_RESERVED'];
			$productReserveQuantity = (float)$row['PRODUCT_QUANTITY_RESERVED'];

			if ((float)$row['PRODUCT_QUANTITY_AVAILABLE'] !== (float)$row['STORE_QUANTITY_AVAILABLE'])
			{
				$problems[] = 'Available quantity not match';
			}

			if ($productReserveQuantity !== $storeReserveQuantity)
			{
				$problems[] = 'Reserve quantity not match';
			}

			if ($productReserveQuantity < 0.0)
			{
				$problems[] = 'Product reserve quantity less than 0';
			}

			if ($storeReserveQuantity < 0.0)
			{
				$problems[] = 'Store reserve quantity less than 0';
			}

			if ((float)$row['SALE_QUANTITY_RESERVED'] > $productReserveQuantity)
			{
				$problems[] = 'More is reserved in \'sale\' than in \'catalog\'';
			}

			if (empty($problems))
			{
				$problems[] = 'Unknown, check SQL';
			}

			$result[] = ['PROBLEMS' => join('; ', $problems)] + $row;
		}

		$this->printTable($result);
	}

	/**
	 * Print info about catalog and sale actual quantities.
	 *
	 * @return void
	 */
	public function printInfo(): void
	{
		$this->printTable(
			Application::getConnection()->query($this->getSql())->fetchAll()
		);
	}

	/**
	 * @param array[] $rows
	 *
	 * @return void
	 */
	private function printTable(array $rows): void
	{
		if (empty($rows))
		{
			echo '-- empty --';
			return;
		}

		$headers = array_keys(
			current($rows)
		);

		echo '<table border="1" cellspacing="0" cellpadding="2"><tr>';
		foreach ($headers as $header)
		{
			echo '<th>' . HtmlFilter::encode($header) . '</th>';
		}

		foreach ($rows as $row)
		{
			echo '<tr>';

			foreach ($headers as $header)
			{
				$value = isset($row[$header]) && $row[$header] !== '' ? $row[$header] : '-';
				echo '<td>' . HtmlFilter::encode($value) . '</td>';
			}

			echo '</td>';
		}

		echo '</table>';
	}

	/**
	 * Sets value to `b_catalog_product.QUANTITY` from stores amount.
	 *
	 * @param int ...$productIds
	 *
	 * @return void
	 */
	public function fixQuantitiesFromStores(int ...$productIds): void
	{
		if (empty($productIds))
		{
			throw new ArgumentNullException('productIds');
		}

		$result = [];

		$productIdsSql = join(',', $productIds);
		$sql = "
			SELECT
				PRODUCT_ID,
				SUM(AMOUNT) as QUANTITY
			FROM
				b_catalog_store_product
				INNER JOIN b_catalog_store ON b_catalog_store.ID = b_catalog_store_product.STORE_ID AND b_catalog_store.ACTIVE = 'Y'
			WHERE
				PRODUCT_ID IN ({$productIdsSql})
			GROUP BY
				PRODUCT_ID
		";
		$rows = Application::getConnection()->query($sql);
		foreach ($rows as $row)
		{
			$productId = (int)$row['PRODUCT_ID'];

			$result[$productId] = [
				'PRODUCT_ID' => $productId,
				'NEW_QUANTITY' => (float)$row['QUANTITY'],
			];
		}

		// fill products without store quantities
		foreach ($productIds as $productId)
		{
			$result[$productId] ??= [
				'PRODUCT_ID' => $productId,
				'NEW_QUANTITY' => 0.0,
			];
		}

		// update products
		foreach ($result as $productId => &$item)
		{
			// or \Bitrix\Catalog\Model\Product::update ?
			$saveResult = ProductTable::update($productId, [
				'QUANTITY' => $item['NEW_QUANTITY'],
			]);
			$item['SAVE_RESULT'] =
				$saveResult->isSuccess()
					? 'ok'
					: join(', ', $saveResult->getErrorMessages())
			;
		}

		$this->printTable($result);
	}

	/**
	 * Sets value to `b_catalog_product.QUANTITY_RESERVE` from stores amount.
	 *
	 * @param int ...$productIds
	 *
	 * @return void
	 */
	public function fixReservesFromStores(int ...$productIds): void
	{
		if (empty($productIds))
		{
			throw new ArgumentNullException('productIds');
		}

		$result = [];

		$productIdsSql = join(',', $productIds);
		$sql = "
			SELECT
				PRODUCT_ID,
				SUM(QUANTITY_RESERVED) as QUANTITY_RESERVED
			FROM
				b_catalog_store_product
				INNER JOIN b_catalog_store ON b_catalog_store.ID = b_catalog_store_product.STORE_ID AND b_catalog_store.ACTIVE = 'Y'
			WHERE
				PRODUCT_ID IN ({$productIdsSql})
			GROUP BY
				PRODUCT_ID
		";
		$rows = Application::getConnection()->query($sql);
		foreach ($rows as $row)
		{
			$productId = (int)$row['PRODUCT_ID'];

			$result[$productId] = [
				'PRODUCT_ID' => $productId,
				'NEW_QUANTITY_RESERVED' => (float)$row['QUANTITY_RESERVED'],
			];
		}

		// fill products without store quantities
		foreach ($productIds as $productId)
		{
			$result[$productId] ??= [
				'PRODUCT_ID' => $productId,
				'NEW_QUANTITY_RESERVED' => 0.0,
			];
		}

		// update products
		foreach ($result as $productId => &$item)
		{
			// or \Bitrix\Catalog\Model\Product::update ?
			$saveResult = ProductTable::update($productId, [
				'QUANTITY_RESERVED' => $item['NEW_QUANTITY_RESERVED'],
			]);
			$item['SAVE_RESULT'] =
				$saveResult->isSuccess()
					? 'ok'
					: join(', ', $saveResult->getErrorMessages())
			;
		}

		$this->printTable($result);
	}

	/**
	 * Sets values to `b_catalog_product.QUANTITY_RESERVE` and `b_catalog_store_product.QUANTITY_RESERVE` as `0`
	 * if quantity less than `0`.
	 *
	 * @return void
	 */
	public function fixReservesLessZero(): void
	{
		$db = Application::getConnection();

		// products
		$db->queryExecute(
			'UPDATE b_catalog_product SET QUANTITY_RESERVED = 0 WHERE QUANTITY_RESERVED < 0'
		);
		if ($db->getAffectedRowsCount() > 0)
		{
			ProductTable::cleanCache();
		}

		// stores
		$db->queryExecute(
			'UPDATE b_catalog_store_product SET QUANTITY_RESERVED = 0 WHERE QUANTITY_RESERVED < 0'
		);
		if ($db->getAffectedRowsCount() > 0)
		{
			StoreProductTable::cleanCache();
		}
	}
}
