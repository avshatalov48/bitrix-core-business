<?php
namespace Bitrix\Catalog\Model;

use Bitrix\Main;
use Bitrix\Main\ORM;
use Bitrix\Main\Localization\Loc;

class Vat extends Entity
{
	/**
	 * Returns vat tablet name.
	 *
	 * @return string
	 */
	public static function getTabletClassName(): string
	{
		return '\Bitrix\Catalog\VatTable';
	}

	/**
	 * Returns vat default fields list for caching.
	 *
	 * @return array
	 */
	protected static function getDefaultCachedFieldList(): array
	{
		return [
			'ID',
			'RATE',
			'EXCLUDE_VAT',
			'ACTIVE',
		];
	}

	/**
	 * Check and modify fields before add vat. Need for entity automation.
	 *
	 * @param ORM\Data\AddResult $result
	 * @param int|null $id
	 * @param array &$data
	 * @return void
	 */
	protected static function prepareForAdd(ORM\Data\AddResult $result, $id, array &$data): void
	{
		$fields = $data['fields'];
		parent::prepareForAdd($result, $id, $fields);
		if (!$result->isSuccess())
		{
			return;
		}

		static $defaultValues = null,
			$blackList = null;

		if ($defaultValues === null)
		{
			$defaultValues = [
				'ACTIVE' => 'Y',
				'SORT' => 100,
				'NAME' => null,
				'EXCLUDE_VAT' => 'N',
				'RATE' => null,
				'XML_ID' => null,
			];

			$blackList = [
				'ID' => true
			];
		}

		$fields = array_merge(
			$defaultValues,
			array_diff_key($fields, $blackList)
		);

		if ($fields['ACTIVE'] !== 'N')
		{
			$fields['ACTIVE'] = $defaultValues['ACTIVE'];
		}

		$fields['SORT'] = static::prepareIntValue($fields['SORT']);
		if ($fields['SORT'] === null || $fields['SORT'] <= 0)
		{
			$fields['SORT'] = $defaultValues['SORT'];
		}

		$fields['NAME'] = static::prepareStringValue($fields['NAME']);
		if ($fields['NAME'] === null)
		{
			$result->addError(new ORM\EntityError(
				Loc::getMessage('BX_CATALOG_MODEL_VAT_ERR_WRONG_NAME')
			));
		}

		if ($fields['EXCLUDE_VAT'] !== 'Y')
		{
			$fields['EXCLUDE_VAT'] = $defaultValues['EXCLUDE_VAT'];
		}
		if ($fields['EXCLUDE_VAT'] === 'Y')
		{
			$fields['RATE'] = null;
		}
		else
		{
			$fields['RATE'] = static::prepareFloatValue($fields['RATE']);
			if ($fields['RATE'] === null || $fields['RATE'] < 0)
			{
				$result->addError(new ORM\EntityError(
					Loc::getMessage('BX_CATALOG_MODEL_VAT_ERR_WRONG_RATE')
				));
			}
		}

		$fields['XML_ID'] = static::prepareStringValue($fields['XML_ID']);
		if ($fields['XML_ID'] !== null)
		{
			$fields['XML_ID'] = mb_substr($fields['XML_ID'], 0, 255);
		}

		if ($result->isSuccess())
		{
			$fields['TIMESTAMP_X'] = new Main\Type\DateTime();
			$data['fields'] = $fields;
		}
		unset($fields);
	}

	/**
	 * Check and modify fields before update product price. Need for entity automation.
	 *
	 * @param ORM\Data\UpdateResult $result
	 * @param int $id
	 * @param array &$data
	 * @return void
	 */
	protected static function prepareForUpdate(ORM\Data\UpdateResult $result, $id, array &$data): void
	{
		$id = (int)$id;
		if ($id <= 0)
		{
			$result->addError(new ORM\EntityError(
				Loc::getMessage('BX_CATALOG_MODEL_VAT_ERR_WRONG_VAT_ID')
			));
			return;
		}

		$fields = $data['fields'];
		parent::prepareForUpdate($result, $id, $fields);
		if (!$result->isSuccess())
		{
			return;
		}

		$blackList = [
			'ID' => true
		];

		$fields = array_diff_key($fields, $blackList);

		if (array_key_exists('ACTIVE', $fields))
		{
			if (
				$fields['ACTIVE'] !== 'Y'
				&& $fields['ACTIVE'] !== 'N'
			)
			{
				unset($fields['ACTIVE']);
			}
		}

		if (array_key_exists('NAME', $fields))
		{
			$value = static::prepareStringValue($fields['NAME']);
			if ($value === null)
			{
				$result->addError(new ORM\EntityError(
					Loc::getMessage('BX_CATALOG_MODEL_VAT_ERR_WRONG_NAME')
				));
			}
		}

		if (array_key_exists('SORT', $fields))
		{
			$fields['SORT'] = static::prepareIntValue($fields['SORT']);
			if ($fields['SORT'] === null || $fields['SORT'] <= 0)
			{
				unset($fields['SORT']);
			}
		}

		if (array_key_exists('EXCLUDE_VAT', $fields))
		{
			if (
				$fields['EXCLUDE_VAT'] !== 'Y'
				&& $fields['EXCLUDE_VAT'] !== 'N'
			)
			{
				unset($fields['EXCLUDE_VAT']);
			}
		}


		if (array_key_exists('RATE', $fields))
		{
			$excludeVat = 'N';
			if (isset($fields['EXCLUDE_VAT']))
			{
				$excludeVat = $fields['EXCLUDE_VAT'];
			}
			else
			{
				$cache = static::getCacheItem($id, true);
				if (!empty($cache))
				{
					$excludeVat = $cache['EXCLUDE_VAT'];
				}
				unset($cache);
			}

			if ($excludeVat === 'Y')
			{
				$fields['RATE'] = null;
			}
			else
			{
				$fields['RATE'] = static::prepareFloatValue($fields['RATE']);
				if ($fields['RATE'] === null || $fields['RATE'] < 0)
				{
					$result->addError(new ORM\EntityError(
						Loc::getMessage('BX_CATALOG_MODEL_VAT_ERR_WRONG_RATE')
					));
				}
			}
			unset($excludeVat);
		}
		else
		{
			if (
				isset($fields['EXCLUDE_VAT'])
				&& $fields['EXCLUDE_VAT'] === 'Y'
			)
			{
				$fields['RATE'] = null;
			}
		}

		if (array_key_exists('XML_ID', $fields))
		{
			$fields['XML_ID'] = static::prepareStringValue($fields['XML_ID']);
			if ($fields['XML_ID'] !== null)
			{
				$fields['XML_ID'] = mb_substr($fields['XML_ID'], 0, 255);
			}
		}

		if ($result->isSuccess())
		{
			$fields['TIMESTAMP_X'] = new Main\Type\DateTime();
			$data['fields'] = $fields;
		}
		unset($fields);
	}
}
