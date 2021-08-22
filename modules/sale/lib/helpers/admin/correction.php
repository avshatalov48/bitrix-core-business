<?php

namespace Bitrix\Sale\Helpers\Admin;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(Main\Application::getDocumentRoot() . BX_ROOT . '/modules/sale/admin/cashbox_correction.php');

class Correction
{
	public const TABLE_ID = 'tbl_sale_cashbox_correction';

	public static function getTableHeaders(): array
	{
		return [
			[
				'id' => 'ID',
				'content' => Loc::getMessage('SALE_CHECK_CORRECTION_PAYMENT_ID'),
				'sort' => 'ID',
				'default' => true
			],
			[
				'id' => 'ORDER_ID',
				'content' => Loc::getMessage('SALE_CHECK_CORRECTION_ORDER_ID'),
				'sort' => 'ORDER_ID',
				'default' => true
			],
			[
				'id' => 'PAID',
				'content' => Loc::getMessage('SALE_CHECK_CORRECTION_ORDER_PAID'),
				'sort' => 'PAID',
				'default' => true
			],
			[
				'id' => 'PAY_SYSTEM_NAME',
				'content' => Loc::getMessage('SALE_CHECK_CORRECTION_PAY_SYSTEM_NAME'),
				'sort' => 'PAY_SYSTEM_NAME',
				'default' => true
			],
			[
				'id' => 'SUM',
				'content' => Loc::getMessage('SALE_CHECK_CORRECTION_ORDER_SUM'),
				'sort' => 'SUM',
				'default' => true
			],
			[
				'id' => 'DATE_BILL',
				'content' => Loc::getMessage('SALE_CHECK_CORRECTION_ORDER_DATE_BILL'),
				'sort' => 'DATE_BILL',
				'default' => false
			],
		];
	}

	public static function getFilterFields(): array
	{
		return [
			[
				'id' => 'PAID',
				'name' => Loc::getMessage('SALE_F_CORRECTION_PAID'),
				'type' => 'checkbox',
				'default' => true
			],
			[
				'id' => 'DATE_BILL',
				'name' => Loc::getMessage('SALE_F_CORRECTION_DATE_BILL'),
				'type' => 'date',
			],
			[
				'id' => 'ORDER_ID',
				'name' => Loc::getMessage('SALE_F_CORRECTION_ORDER_ID'),
				'type' => 'number',
				'filterable' => '',
				'quickSearch' => ''
			],
			[
				'id' => 'CHECK_PRINTED',
				'name' => Loc::getMessage('SALE_F_CORRECTION_CHECK_PRINTED'),
				'type' => 'checkbox',
				'filterable' => '',
				'quickSearch' => '',
				'default' => true
			],
		];
	}

	public static function prepareFilter($filter)
	{
		$newFilter = $filter;
		if (isset($newFilter['CHECK_PRINTED']))
		{
			if ($newFilter['CHECK_PRINTED'] === 'Y')
			{
				$newFilter['=PAYMENT_CHECK_PRINTED.STATUS'] = 'Y';
			}
			else
			{
				$newFilter[] = [
					'LOGIC' => 'OR',
					'=PAYMENT_CHECK_PRINTED.STATUS' => null,
					'@PAYMENT_CHECK_PRINTED.STATUS' => ['N', 'P', 'E']
				];
			}

			unset($newFilter['CHECK_PRINTED']);
		}

		return $newFilter;
	}

	public static function getPaymentSelectParams($filter): array
	{
		return [
			'select' => [
				'ID', 'ORDER_ID', 'SUM', 'CURRENCY', 'PAY_SYSTEM_NAME',
				'PAID', 'DATE_BILL', 'CHECK_PRINTED' => 'PAYMENT_CHECK_PRINTED.STATUS'
			],
			'filter' => $filter,
			'runtime' => [
				new Main\ORM\Fields\Relations\Reference(
					'PAYMENT_CHECK_PRINTED',
					\Bitrix\Sale\Cashbox\Internals\CashboxCheckTable::getEntity(),
					['=ref.PAYMENT_ID' => 'this.ID',],
					['join_type' => 'LEFT',]
				)
			]
		];
	}

	public static function getFilterValues(): array
	{
		$newFilter = [];

		$filterFields = self::getFilterFields();
		$filterOption = new \Bitrix\Main\UI\Filter\Options(self::TABLE_ID);
		$filterData = $filterOption->getFilter($filterFields);
		$filterable = array();
		$quickSearchKey = '';
		foreach ($filterFields as $filterField)
		{
			if (isset($filterField['quickSearch']))
			{
				$quickSearchKey = $filterField['quickSearch'].$filterField['id'];
			}
			$filterable[$filterField['id']] = $filterField['filterable'];
		}

		foreach ($filterData as $fieldId => $fieldValue)
		{
			if ((is_array($fieldValue) && empty($fieldValue)) || (is_string($fieldValue) && $fieldValue == ''))
			{
				continue;
			}

			if (mb_substr($fieldId, -5) === '_from')
			{
				$realFieldId = mb_substr($fieldId, 0, -5);
				if (!array_key_exists($realFieldId, $filterable))
				{
					continue;
				}
				if (mb_substr($realFieldId, -2) === '_1')
				{
					$newFilter[$realFieldId] = $fieldValue;
				}
				else
				{
					if (!empty($filterData[$realFieldId.'_numsel']) && $filterData[$realFieldId.'_numsel'] === 'more')
						$filterPrefix = '>';
					else
						$filterPrefix = '>=';
					$newFilter[$filterPrefix.$realFieldId] = trim($fieldValue);
				}
			}
			elseif (mb_substr($fieldId, -3) === '_to')
			{
				$realFieldId = mb_substr($fieldId, 0, -3);
				if (!array_key_exists($realFieldId, $filterable))
				{
					continue;
				}
				if (mb_substr($realFieldId, -2) === '_1')
				{
					$realFieldId = mb_substr($realFieldId, 0, -2);
					$newFilter[$realFieldId.'_2'] = $fieldValue;
				}
				else
				{
					if (!empty($filterData[$realFieldId.'_numsel']) && $filterData[$realFieldId.'_numsel'] === 'less')
					{
						$filterPrefix = '<';
					}
					else
					{
						$filterPrefix = '<=';
					}
					$newFilter[$filterPrefix.$realFieldId] = trim($fieldValue);
				}
			}
			else
			{
				if (array_key_exists($fieldId, $filterable))
				{
					$filterPrefix = $filterable[$fieldId];
					$newFilter[$filterPrefix.$fieldId] = $fieldValue;
				}
				if ($quickSearchKey && $fieldId === 'FIND' && trim($fieldValue))
				{
					$newFilter[$quickSearchKey] = $fieldValue;
				}
			}
		}

		return $newFilter;
	}
}