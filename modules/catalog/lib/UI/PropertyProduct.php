<?php

namespace Bitrix\Catalog\UI;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Catalog;
use Bitrix\Crm;
use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Iblock\PropertyTable;

class PropertyProduct
{
	private const PRICE_PRECISION = 2;

	/**
	 * Get readable properties of a product variation. Fields are not properties - but included here.
	 *
	 * @param int $iblockId
	 * @param int $skuId
	 * @param array $filter
	 *
	 * @return array in format ['PROPERTY_CODE' => 'readable value']
	 */
	public static function getSkuProperties(int $iblockId, int $skuId, array $filter = []): array
	{
		$properties = self::getIblockProperties($iblockId, $skuId, $filter);
		$product = Catalog\ProductTable::getRow([
			'select' => [
				'SKU_NAME' => 'IBLOCK_ELEMENT.NAME',
				'SKU_DESCRIPTION' => 'IBLOCK_ELEMENT.DETAIL_TEXT',
				'PURCHASING_PRICE',
				'PURCHASING_CURRENCY',
				'LENGTH',
				'WIDTH',
				'HEIGHT',
				'WEIGHT',
			],
			'filter' => ['=ID' => $skuId],
		]);

		$properties['SKU_ID'] = $skuId;

		if ($product !== null)
		{
			$properties['PURCHASING_PRICE'] = round((float)$product['PURCHASING_PRICE'], self::PRICE_PRECISION);
			if (Loader::includeModule('crm'))
			{
				$properties['PURCHASING_PRICE_FORMATTED'] = \CCrmCurrency::MoneyToString(
					$product['PURCHASING_PRICE'],
					$product['PURCHASING_CURRENCY']
				);
			}
			elseif (Loader::includeModule('currency'))
			{
				$properties['PURCHASING_PRICE_FORMATTED'] = \CCurrencyLang::CurrencyFormat(
					$product['PURCHASING_PRICE'],
					$product['PURCHASING_CURRENCY']
				);
			}
			else
			{
				$properties['PURCHASING_PRICE_FORMATTED'] = htmlspecialcharsbx(
					$product['PURCHASING_PRICE'] . ' ' . $product['PURCHASING_CURRENCY']
				);
			}
			$properties['LENGTH'] = $product['LENGTH'];
			$properties['WEIGHT'] = $product['WEIGHT'];
			$properties['WIDTH'] = $product['WIDTH'];
			$properties['HEIGHT'] = $product['HEIGHT'];
			$properties['SKU_NAME'] = htmlspecialcharsbx($product['SKU_NAME']);
			$properties['SKU_DESCRIPTION'] = (new \CBXSanitizer())->SanitizeHtml($product['SKU_DESCRIPTION']);
		}
		else
		{
			$properties['PURCHASING_PRICE'] = 0;
			$properties['PURCHASING_PRICE_FORMATTED'] = '';
			$properties['LENGTH'] = null;
			$properties['WEIGHT'] = null;
			$properties['WIDTH'] = null;
			$properties['HEIGHT'] = null;
			$properties['SKU_NAME'] = '';
			$properties['SKU_DESCRIPTION'] = '';
		}

		return $properties;
	}

	/**
	 * Get readable properties of a product.
	 *
	 * @param int $iblockId
	 * @param int $productId
	 * @param array $filter
	 *
	 * @return array in format ['PROPERTY_CODE' => 'readable value']
	 */
	public static function getIblockProperties(int $iblockId, int $productId, array $filter = []): array
	{
		$result = [];

		$filter['ACTIVE'] = 'Y';

		$props = \CIBlockElement::GetProperty($iblockId, $productId, 'id', 'asc', $filter);
		while ($prop = $props->GetNext())
		{
			if (empty($prop['VALUE'])
				&& !($prop['PROPERTY_TYPE'] === 'L' && $prop['LIST_TYPE'] === 'C')
			)
			{
				continue;
			}

			$code = 'PROPERTY_' . $prop['ID'];

			switch ($prop['PROPERTY_TYPE'])
			{
				case PropertyTable::TYPE_STRING:
				case PropertyTable::TYPE_NUMBER:
					if ($prop['USER_TYPE'] === PropertyTable::USER_TYPE_DIRECTORY
						&& isset($prop['USER_TYPE_SETTINGS']['TABLE_NAME'])
						&& Loader::includeModule('highloadblock')
					)
					{
						$value = self::getDirectoryValue($prop);
					}
					else if ($prop['USER_TYPE'] === PropertyTable::USER_TYPE_HTML)
					{
						$value = (new \CBXSanitizer())->SanitizeHtml($prop['~VALUE']['TEXT']);
					}
					else
					{
						$value = $prop['VALUE'];
					}

					if (!isset($result[$code]))
					{
						$result[$code] = $value;
					}
					else
					{
						$result[$code] .= ', ' . $value;
					}

					break;
				case PropertyTable::TYPE_LIST:
					if ($prop['LIST_TYPE'] === PropertyTable::CHECKBOX)
					{
						switch ($prop['VALUE_ENUM'])
						{
							case 'Y':
								$value = Loc::getMessage('CRM_ENTITY_PRODUCT_LIST_COLUMN_CHECKBOX_YES');
								break;
							case 'N':
							case '':
								$value = Loc::getMessage('CRM_ENTITY_PRODUCT_LIST_COLUMN_CHECKBOX_NO');
								break;
							default:
								$value = htmlspecialcharsbx($prop['VALUE_ENUM']);
						}
						$result[$code] = $value;

						break;
					}

					if ($prop['MULTIPLE'] !== 'Y')
					{
						$result[$code] = $prop['VALUE_ENUM'];

						break;
					}

					if (!isset($result[$code]))
					{
						$result[$code] = $prop['VALUE_ENUM'];
					}
					else
					{
						$result[$code] .= ', ' . $prop['VALUE_ENUM'];
					}

					break;
				case PropertyTable::TYPE_FILE:
					Loader::includeModule('fileman'); // always exists
					$listImageSize = (int)Option::get('iblock', 'list_image_size');
					$minImageSize = [
						'W' => 1,
						'H' => 1,
					];
					$maxImageSize = [
						'W' => $listImageSize,
						'H' => $listImageSize,
					];
					$result[$code] ??= '';
					$result[$code] .= \CFileInput::Show(
						'NO_FIELDS[' . $productId . ']',
						$prop['VALUE'],
						[
							'IMAGE' => 'Y',
							'PATH' => 'Y',
							'FILE_SIZE' => 'Y',
							'DIMENSIONS' => 'Y',
							'IMAGE_POPUP' => 'N',
							'MAX_SIZE' => $maxImageSize,
							'MIN_SIZE' => $minImageSize,
						],
						[
							'upload' => false,
							'medialib' => false,
							'file_dialog' => false,
							'cloud' => false,
							'del' => false,
							'description' => false,
						]
					);

					break;
				default:
					$result[$code] = htmlspecialcharsbx($prop['VALUE']);
			}
		}

		return $result;
	}

	/**
	 * Get readable string value of directory property.
	 *
	 * @param array $prop
	 *
	 * @return string|null
	 */
	private static function getDirectoryValue(array $prop): ?string
	{
		$hlblock = HighloadBlockTable::getRow([
			'filter' => [
				'=TABLE_NAME' => $prop['USER_TYPE_SETTINGS']['TABLE_NAME'],
			],
		]);

		if ($hlblock)
		{
			$entity = HighloadBlockTable::compileEntity($hlblock);
			$entityClass = $entity->getDataClass();
			$row = $entityClass::getRow([
				'filter' => [
					'=UF_XML_ID' => $prop['VALUE'],
				],
			]);

			if (isset($row['UF_NAME']))
			{
				return htmlspecialcharsbx($row['UF_NAME']);
			}

			return null;
		}

		return null;
	}

	/**
	 * All column codes for crm.entity.product.list template
	 *
	 * @return array
	 */
	public static function getColumnNames(): array
	{
		$result = [];
		$iterator = PropertyTable::getList([
			'select' => [
				'ID',
				'IBLOCK_ID',
				'SORT',
				'NAME',
			],
			'filter' => [
				'=IBLOCK_ID' => self::getIblockIds(),
				'=ACTIVE' => 'Y',
				[
					'LOGIC' => 'OR',
					'==USER_TYPE' => null,
					'=USER_TYPE' => '',
					'@USER_TYPE' => self::getAllowedPropertyUserTypes(),
				],
				'!@PROPERTY_TYPE' => self::getRestrictedPropertyTypes(),
				'!@CODE' => self::getRestrictedProperties(),
			],
			'order' => [
				'IBLOCK_ID' => 'ASC',
				'SORT' => 'ASC',
				'NAME' => 'ASC',
			],
		]);

		while ($prop = $iterator->fetch())
		{
			$result[] = 'PROPERTY_' . $prop['ID'];
		}
		unset($iterator);

		$skuFields = [
			'SKU_ID',
			'SKU_NAME',
			'SKU_DESCRIPTION',
			'LENGTH',
			'WIDTH',
			'HEIGHT',
			'WEIGHT',
		];

		return array_merge($result, $skuFields);
	}

	/**
	 * Restricted property types for hiding in product grid of deal.
	 * For \CCrmEntityProductListComponent::getIblockColumnsDescription
	 *
	 * @return array
	 */
	public static function getRestrictedPropertyTypes(): array
	{
		return [
			PropertyTable::TYPE_ELEMENT,
			PropertyTable::TYPE_SECTION,
		];
	}

	/**
	 * Restricted properties for hiding in product grid of deal.
	 * For \CCrmEntityProductListComponent::getIblockColumnsDescription
	 *
	 * @return array
	 */
	public static function getRestrictedProperties(): array
	{
		return [
			'MORE_PHOTO',
			'BLOG_POST_ID',
			'BLOG_COMMENTS_CNT',
		];
	}

	/**
	 * Supported user types of properties in product grid of deal.
	 * For \CCrmEntityProductListComponent::getIblockColumnsDescription
	 *
	 * @return array
	 */
	public static function getAllowedPropertyUserTypes(): array
	{
		return [
			PropertyTable::USER_TYPE_DATE,
			PropertyTable::USER_TYPE_DATETIME,
			PropertyTable::USER_TYPE_DIRECTORY,
			PropertyTable::USER_TYPE_HTML,
		];
	}

	/**
	 * @return array
	 */
	private static function getIblockIds(): array
	{
		if (Loader::includeModule('crm'))
		{
			return [
				Crm\Product\Catalog::getDefaultId(),
				Crm\Product\Catalog::getDefaultOfferId(),
			];
		}

		return [];
	}
}
