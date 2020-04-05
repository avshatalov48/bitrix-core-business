<?php
namespace Bitrix\Landing\Hook\Page;

use \Bitrix\Landing\Manager;
use \Bitrix\Landing\Hook;
use \Bitrix\Landing\Field;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\ModuleManager;
use \Bitrix\Main\Loader;

Loc::loadMessages(__FILE__);

class Settings extends \Bitrix\Landing\Hook\Page
{
	/**
	 * Block's code for get params.
	 */
	const SOURCE_BLOCK = 'store.catalog.list';

	/**
	 * Component's code for get params.
	 */
	const SOURCE_COMPONENT = 'bitrix:catalog.section';

	/**
	 * Default values for settings' code.
	 * @var array
	 */
	protected static $defValues = array(
		'SECTION_ID' => '',
		'HIDE_NOT_AVAILABLE' => 'L',
		'HIDE_NOT_AVAILABLE_OFFERS' => 'N',
		'PRODUCT_SUBSCRIPTION' => 'Y',
		'USE_PRODUCT_QUANTITY' => 'Y',
		'DISPLAY_COMPARE' => 'Y',
		'PRICE_CODE' => [
			'BASE'
		],
		'CURRENCY_ID' => '',
		'PRICE_VAT_INCLUDE' => 'Y',
		'SHOW_OLD_PRICE' => 'Y',
		'SHOW_DISCOUNT_PERCENT' => 'Y',
		'USE_PRICE_COUNT' => 'N',
		'SHOW_PRICE_COUNT' => 1,
		'USE_ENHANCED_ECOMMERCE' => 'Y',
		'DATA_LAYER_NAME' => 'dataLayer',
		'BRAND_PROPERTY' => 'BRAND_REF'
	);

	/**
	 * Build local allowed codes array.
	 * @return array
	 */
	protected static function getCodesVsalues()
	{
		static $codes = array();

		if (!empty($codes))
		{
			return $codes;
		}

		if (ModuleManager::isModuleInstalled('catalog'))
		{
			$codes = array(
				'' => array(
					'IBLOCK_ID', 'SECTION_ID'
				),
				'VIEW' => array(
					'HIDE_NOT_AVAILABLE', 'HIDE_NOT_AVAILABLE_OFFERS', 'PRODUCT_SUBSCRIPTION',
					'USE_PRODUCT_QUANTITY', 'DISPLAY_COMPARE'
				),
				'PRICE' => array(
					'PRICE_CODE', 'USE_PRICE_COUNT', 'SHOW_PRICE_COUNT', 'CURRENCY_ID',
					'PRICE_VAT_INCLUDE', 'SHOW_OLD_PRICE', 'SHOW_DISCOUNT_PERCENT'
				),
				'ANAL' => array(
					'USE_ENHANCED_ECOMMERCE', 'DATA_LAYER_NAME', 'BRAND_PROPERTY'
				)
			);
		}
		else
		{
			$codes = array(
				'' => array(
					'IBLOCK_ID', 'SECTION_ID'
				)
			);
		}

		return $codes;
	}

	/**
	 * Return Field by component's param's type.
	 * @param string $type Type.
	 * @param string $code Field code.
	 * @param array $params Additional params.
	 * @return Field
	 */
	protected static function getFieldByType($type, $code, $params = array())
	{
		$field = null;

		switch ($type)
		{
			case 'LIST':
				{
					$field = new Field\Select($code, array(
						'title' => isset($params['NAME'])
									? $params['NAME']
									: '',
						'options' => isset($params['VALUES'])
									? (array) $params['VALUES']
									: array(),
						'multiple' => isset($params['MULTIPLE'])
										&& $params['MULTIPLE'] == 'Y'
					));
					break;
				}
			case 'CHECKBOX':
				{
					$field = new Field\Checkbox($code, array(
						'title' => isset($params['NAME'])
							? $params['NAME']
							: ''
					));
					break;
				}
			default:
				{
					$field = new Field\Text($code, array(
						'title' => isset($params['NAME'])
							? $params['NAME']
							: ''
					));
					break;
				}
		}

		if ($field && isset(self::$defValues[$code]))
		{
			$field->setValue(self::$defValues[$code]);
		}

		return $field;
	}

	/**
	 * Get catalog's components params.
	 * @return array
	 */
	protected static function getComponentsParams()
	{
		static $params = array();

		if (empty($params))
		{
			// get real manifest
			$block = new \Bitrix\Landing\Block(0, array(
				'CODE' => self::SOURCE_BLOCK
			));
			$manifest = $block->getManifest(
				true,
				true,
				array(
					'miss_subtype' => true
				)
			);
			$codes = self::getCodesVsalues();
			foreach (array_keys($codes) as $k)
			{
				foreach ($codes[$k] as $code)
				{
					if (isset($manifest['nodes'][self::SOURCE_COMPONENT]['extra'][$code]))
					{
						$params[$code] = $manifest['nodes'][self::SOURCE_COMPONENT]['extra'][$code];
					}
				}
			}
		}

		return $params;
	}

	/**
	 * Get allowed param's code.
	 * @param bool $linear Linear
	 * @return array
	 */
	public static function getCodes($linear = false)
	{
		$codes = array();

		if ($linear)
		{
			foreach (self::getCodesVsalues() as $item)
			{
				$codes = array_merge($codes, $item);
			}
		}
		else
		{
			$codes = self::getCodesVsalues();
		}

		return $codes;
	}

	/**
	 * Map of the field.
	 * @return array
	 */
	protected function getMap()
	{
		$fields = array();

		// set iblock_id to the map
		if (!Manager::isB24())
		{
			$catalogs = array(
				'' => ''
			);
			$allowedCatalogs = array();
			$catalogIncluded = Loader::includeModule('catalog');

			if ($catalogIncluded)
			{
				$iterator = \Bitrix\Catalog\CatalogIblockTable::getList(array(
					'select' => array(
						'IBLOCK_ID', 'PRODUCT_IBLOCK_ID'
					)
				));
				while ($row = $iterator->fetch())
				{
					$row['IBLOCK_ID'] = (int)$row['IBLOCK_ID'];
					$row['PRODUCT_IBLOCK_ID'] = (int)$row['PRODUCT_IBLOCK_ID'];
					if ($row['PRODUCT_IBLOCK_ID'] > 0)
						$allowedCatalogs[$row['PRODUCT_IBLOCK_ID']] = true;
					else
						$allowedCatalogs[$row['IBLOCK_ID']] = true;
				}
				unset($row, $iterator);
			}
			if (Loader::includeModule('iblock'))
			{
				$res = \CIblock::getList(
					[],
					[
						'SITE_ID' => Manager::getMainSiteId()
					]
				);
			}
			if (isset($res))
			{
				while ($row = $res->fetch())
				{
					$row['ID'] = (int)$row['ID'];
					if ($catalogIncluded && !isset($allowedCatalogs[$row['ID']]))
					{
						continue;
					}
					$catalogs[$row['ID']] = '[' . $row['ID'] . '] ' . $row['NAME'];
				}
			}
			$fields['IBLOCK_ID'] = self::getFieldByType(
				'LIST',
				'IBLOCK_ID',
				array(
					'NAME' => Loc::getMessage('LANDING_HOOK_SETTINGS_IBLOCK_ID'),
					'VALUES' => $catalogs
				)
			);
			unset($allowedCatalogs);
		}

		foreach (self::getComponentsParams() as $code => $params)
		{
			if (!isset($fields[$code]))
			{
				$fields[$code] = self::getFieldByType(
					$params['TYPE'],
					$code,
					$params
				);
			}
		}

		$fields['AGREEMENT_ID'] = self::getFieldByType(
			null, 'AGREEMENT_ID'
		);

		return $fields;
	}

	/**
	 * Enable or not the hook - this is only for system settings.
	 * @return boolean
	 */
	public function enabled()
	{
		return false;
	}

	/**
	 * Exec hook.
	 * @return void
	 */
	public function exec()
	{
		// do nothing
	}

	/**
	 * Get actual settings data for site.
	 * @param int $id Site id.
	 * @return array
	 */
	public static function getDataForSite($id = null)
	{
		static $settings = array();

		if (isset($settings[$id]))
		{
			return $settings[$id];
		}

		$default = self::getComponentsParams();
		$settings[$id] = array();

		if ($id)
		{
			$hooks = Hook::getData(
				$id,
				Hook::ENTITY_TYPE_SITE
			);
		}

		foreach ($default as $key => $item)
		{
			if (isset($hooks['SETTINGS'][$key]))
			{
				$settings[$id][$key] = $hooks['SETTINGS'][$key];
			}
			elseif (isset(self::$defValues[$key]))
			{
				$settings[$id][$key] = self::$defValues[$key];
			}
			else
			{
				$settings[$id][$key] = $item['VALUE'];
			}
		}

		// additional
		if (!Manager::isB24())
		{
			$settings[$id]['IBLOCK_ID'] = isset($hooks['SETTINGS']['IBLOCK_ID'])
				? $hooks['SETTINGS']['IBLOCK_ID']
				: 0;
		}
		else
		{
			$settings[$id]['IBLOCK_ID'] = \Bitrix\Main\Config\Option::get(
				'crm', 'default_product_catalog_id'
			);
		}
		if (isset($hooks['SETTINGS']['AGREEMENT_ID']))
		{
			$settings[$id]['AGREEMENT_ID'] = $hooks['SETTINGS']['AGREEMENT_ID'];
		}

		return $settings[$id];
	}
}