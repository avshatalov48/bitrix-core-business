<?php

namespace Bitrix\Iblock\Component;

use Bitrix\Iblock;
use Bitrix\Catalog;
use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Text;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Type\Collection;
use Bitrix\Currency;

/**
 * @global \CUser $USER
 * @global \CMain $APPLICATION
 */

abstract class ElementList extends Base
{
	private $multiIblockMode = false;
	private $paginationMode = false;
	protected $navigation = false;
	protected $pagerParameters = array();

	/**
	 * Multi iblock mode setter.
	 * Enable it if you use data from different iblocks.
	 *
	 * @param $state
	 * @return $this
	 */
	protected function setMultiIblockMode($state)
	{
		$this->multiIblockMode = (bool)$state;

		return $this;
	}

	/**
	 * Return if multi iblock mode enabled.
	 *
	 * @return bool
	 */
	public function isMultiIblockMode()
	{
		return (bool)$this->multiIblockMode;
	}

	/**
	 * Pagination mode setter.
	 * Enable it if you use page navigation.
	 *
	 * @param $state
	 * @return $this
	 */
	protected function setPaginationMode($state)
	{
		$this->paginationMode = (bool)$state;

		return $this;
	}

	/**
	 * Return if pagination mode enabled.
	 *
	 * @return bool
	 */
	public function isPaginationMode()
	{
		return (bool)$this->paginationMode;
	}

	public function onPrepareComponentParams($params)
	{
		if (!is_array($params))
		{
			$params = [];
		}
		$params = parent::onPrepareComponentParams($params);
		$this->makeMagicWithPageNavigation();

		// compatibility with 'ELEMENT_COUNT' components
		if (isset($params['ELEMENT_COUNT']))
		{
			$params['PAGE_ELEMENT_COUNT'] = $params['ELEMENT_COUNT'];
		}

		// PREDICT_ELEMENT_COUNT - hidden parameter to get elements count from "PRODUCT_ROW_VARIANTS" instead of "PAGE_ELEMENT_COUNT"
		if (isset($params['PREDICT_ELEMENT_COUNT']) && $params['PREDICT_ELEMENT_COUNT'] === 'Y' && !empty($params['PRODUCT_ROW_VARIANTS']))
		{
			$isBigData = $this->request->get('bigData') === 'Y';
			$params['PRODUCT_ROW_VARIANTS'] = static::parseJsonParameter($params['PRODUCT_ROW_VARIANTS']);
			$params['PAGE_ELEMENT_COUNT'] = static::predictElementCountByVariants($params['PRODUCT_ROW_VARIANTS'], $isBigData);
		}

		$params['PAGE_ELEMENT_COUNT'] = (int)($params['PAGE_ELEMENT_COUNT'] ?? 0);
		$params['ELEMENT_COUNT'] = (int)($params['ELEMENT_COUNT'] ?? 0);
		$params['LINE_ELEMENT_COUNT'] = (int)($params['LINE_ELEMENT_COUNT'] ?? 3);

		$params['INCLUDE_SUBSECTIONS'] ??= '';
		if (!in_array(
			$params['INCLUDE_SUBSECTIONS'],
			[
				'Y',
				'A',
				'N',
			]
		))
		{
			$params['INCLUDE_SUBSECTIONS'] = 'Y';
		}

		$params['HIDE_NOT_AVAILABLE'] ??= '';
		if ($params['HIDE_NOT_AVAILABLE'] !== 'Y' && $params['HIDE_NOT_AVAILABLE'] !== 'L')
		{
			$params['HIDE_NOT_AVAILABLE'] = 'N';
		}

		$params['HIDE_NOT_AVAILABLE_OFFERS'] ??= '';
		if ($params['HIDE_NOT_AVAILABLE_OFFERS'] !== 'Y' && $params['HIDE_NOT_AVAILABLE_OFFERS'] !== 'L')
		{
			$params['HIDE_NOT_AVAILABLE_OFFERS'] = 'N';
		}

		$params['FILTER_NAME'] = trim((string)($params['FILTER_NAME'] ?? ''));
		// ajax request doesn't have access to page $GLOBALS
		if (isset($params['GLOBAL_FILTER']))
		{
			$this->globalFilter = $params['GLOBAL_FILTER'];
		}
		else
		{
			if (
				$params['FILTER_NAME'] !== ''
				&& preg_match(self::PARAM_TITLE_MASK, $params['FILTER_NAME'])
				&& isset($GLOBALS[$params['FILTER_NAME']])
				&& is_array($GLOBALS[$params['FILTER_NAME']])
			)
			{
				$this->globalFilter = $GLOBALS[$params['FILTER_NAME']];
			}

			if (isset($this->globalFilter['FACET_OPTIONS']) && count($this->globalFilter) == 1)
			{
				unset($this->globalFilter['FACET_OPTIONS']);
			}

			// save global filter for ajax request params
			$this->arResult['ORIGINAL_PARAMETERS']['GLOBAL_FILTER'] = $this->globalFilter;
		}

		$productMappingFilter = [];
		if (
			Loader::includeModule('catalog')
			&& Catalog\Product\SystemField\ProductMapping::isAllowed()
		)
		{
			$productMappingFilter = Catalog\Product\SystemField\ProductMapping::getExtendedFilterByArea(
				[],
				Catalog\Product\SystemField\ProductMapping::MAP_LANDING
			);
		}
		$params['CACHE_FILTER'] = isset($params['CACHE_FILTER']) && $params['CACHE_FILTER'] === 'Y';
		if (
			!$params['CACHE_FILTER']
			&& !empty($this->globalFilter)
			&& array_diff_assoc($this->globalFilter, $productMappingFilter)
		)
		{
			$params['CACHE_TIME'] = 0;
		}

		$params = $this->prepareElementSortRow(
			$params,
			['ORDER' => 'ELEMENT_SORT_FIELD', 'DIRECTION' => 'ELEMENT_SORT_ORDER'],
			['ORDER' => 'SORT', 'DIRECTION' => 'asc']
		);

		$params = $this->prepareElementSortRow(
			$params,
			['ORDER' => 'ELEMENT_SORT_FIELD2', 'DIRECTION' => 'ELEMENT_SORT_ORDER2'],
			['ORDER' => 'ID', 'DIRECTION' => 'desc']
		);

		$params['PAGER_BASE_LINK_ENABLE'] = (string)($params['PAGER_BASE_LINK_ENABLE'] ?? '');
		$params['PAGER_TITLE'] = (string)($params['PAGER_TITLE'] ?? '');
		$params['PAGER_TEMPLATE'] = (string)($params['PAGER_TEMPLATE'] ?? '');
		$params['PAGER_PARAMS_NAME'] = trim((string)($params['PAGER_PARAMS_NAME'] ?? ''));
		if (
			$params['PAGER_PARAMS_NAME'] !== ''
			&& preg_match(self::PARAM_TITLE_MASK, $params['PAGER_PARAMS_NAME'])
		)
		{
			$this->pagerParameters = $GLOBALS[$params['PAGER_PARAMS_NAME']] ?? [];

			if (!is_array($this->pagerParameters))
			{
				$this->pagerParameters = [];
			}
		}

		if (Loader::includeModule('catalog') && isset($params['CUSTOM_FILTER']) && is_string($params['CUSTOM_FILTER']))
		{
			try
			{
				$params['CUSTOM_FILTER'] = $this->parseCondition(Json::decode($params['CUSTOM_FILTER']), $params);
			}
			catch (\Exception $e)
			{
				$params['CUSTOM_FILTER'] = array();
			}
		}
		else
		{
			$params['CUSTOM_FILTER'] = array();
		}

		if ($this->isPaginationMode())
		{
			$this->getPaginationParams($params);
		}

		$this->getSpecificIblockParams($params);

		$params['CALCULATE_SKU_MIN_PRICE'] = (isset($params['CALCULATE_SKU_MIN_PRICE']) && $params['CALCULATE_SKU_MIN_PRICE'] === 'Y');

		return $params;
	}

	/**
	 * @param array $params
	 * @param array $orderRow
	 * @param array $default
	 * @return array
	 */
	protected function prepareElementSortRow(array $params, array $orderRow, array $default): array
	{
		$order = (isset($orderRow['ORDER']) ? trim($orderRow['ORDER']) : '');
		$direction = (isset($orderRow['DIRECTION']) ? trim($orderRow['DIRECTION']) : '');
		if (empty($params) || $order === '' || $direction === '')
		{
			return $params;
		}
		if (empty($params[$order]))
		{
			$params[$order] = $default['ORDER'] ?? 'SORT';
		}
		$params[$order] = strtoupper($params[$order]);

		if ($params[$order] === 'ID' && !empty($params[$direction]) && is_array($params[$direction]))
		{
			Collection::normalizeArrayValuesByInt($params[$direction], false);
			if (empty($params[$direction]))
			{
				$params[$direction] = $default['DIRECTION'] ?? 'desc';
			}
		}
		else
		{
			if (empty($params[$direction]) || !preg_match(self::SORT_ORDER_MASK, $params[$direction]))
			{
				$params[$direction] = $default['DIRECTION'] ?? 'desc';
			}
		}

		return $params;
	}

	protected function checkProductIblock(array $product): bool
	{
		$result = true;
		if (!$this->isMultiIblockMode())
		{
			$result = ($product['PRODUCT_IBLOCK_ID'] == $this->arParams['IBLOCK_ID']);
		}
		return $result;
	}

	protected static function predictElementCountByVariants($variants, $isBigData = false)
	{
		$count = 0;
		$templateVariantsMap = static::getTemplateVariantsMap();

		if (!empty($variants))
		{
			foreach ($variants as $variant)
			{
				foreach ($templateVariantsMap as $variantInfo)
				{
					if ((int)$variantInfo['VARIANT'] === (int)$variant['VARIANT'])
					{
						if (
							($isBigData && $variant['BIG_DATA'])
							|| (!$isBigData && !$variant['BIG_DATA'])
						)
						{
							$count += (int)$variantInfo['COUNT'];
						}

						break;
					}
				}
			}
		}

		return $count;
	}

	private function makeMagicWithPageNavigation()
	{
		if ($this->request->isAjaxRequest())
		{
			foreach ($this->request->getPostList() as $name => $value)
			{
				if (preg_match('%^PAGEN_(\d+)$%', $name, $m))
				{
					global $NavNum;
					$NavNum = (int)$m[1] - 1;
					return;
				}
			}
		}
	}

	protected function getPaginationParams(&$params)
	{
		$params['DISPLAY_TOP_PAGER'] = isset($params['DISPLAY_TOP_PAGER']) && $params['DISPLAY_TOP_PAGER'] === 'Y';
		$params['DISPLAY_BOTTOM_PAGER'] = !isset($params['DISPLAY_BOTTOM_PAGER']) || $params['DISPLAY_BOTTOM_PAGER'] !== 'N';
		$params['LAZY_LOAD'] = isset($params['LAZY_LOAD']) && $params['LAZY_LOAD'] === 'Y' ? 'Y' : 'N';

		if ($params['DISPLAY_TOP_PAGER'] || $params['DISPLAY_BOTTOM_PAGER'] || $params['LAZY_LOAD'] === 'Y')
		{
			\CPageOption::SetOptionString('main', 'nav_page_in_session', 'N');
			$params['PAGER_TITLE'] = isset($params['PAGER_TITLE']) ? trim($params['PAGER_TITLE']) : '';
			$params['PAGER_SHOW_ALWAYS'] = isset($params['PAGER_SHOW_ALWAYS']) && $params['PAGER_SHOW_ALWAYS'] === 'Y';
			$params['PAGER_TEMPLATE'] = isset($params['PAGER_TEMPLATE']) ? trim($params['PAGER_TEMPLATE']) : '';
			$params['PAGER_DESC_NUMBERING'] = isset($params['PAGER_DESC_NUMBERING']) && $params['PAGER_DESC_NUMBERING'] === 'Y';
			$params['PAGER_DESC_NUMBERING_CACHE_TIME'] = (int)$params['PAGER_DESC_NUMBERING_CACHE_TIME'];
			$params['PAGER_SHOW_ALL'] = isset($params['PAGER_SHOW_ALL']) && $params['PAGER_SHOW_ALL'] === 'Y';
			$params['LOAD_ON_SCROLL'] = isset($params['LOAD_ON_SCROLL']) && $params['LOAD_ON_SCROLL'] === 'Y' ? 'Y' : 'N';
			$params['MESS_BTN_LAZY_LOAD'] = isset($params['MESS_BTN_LAZY_LOAD']) ? trim($params['MESS_BTN_LAZY_LOAD']) : '';
		}
		else
		{
			$this->setPaginationMode(false);
			$params['PAGER_SHOW_ALWAYS'] = false;
			$params['PAGER_SHOW_ALL'] = false;
			$params['LOAD_ON_SCROLL'] = 'N';
		}
		if ($params['LAZY_LOAD'] === 'Y' && $params['LOAD_ON_SCROLL'] === 'Y')
		{
			$params['DEFERRED_LOAD'] = isset($params['DEFERRED_LOAD']) && $params['DEFERRED_LOAD'] === 'Y' ? 'Y' : 'N';
		}
		else
		{
			$params['DEFERRED_LOAD'] = 'N';
		}
	}

	protected function getSpecificIblockParams(&$params)
	{
		if ($this->isMultiIblockMode())
		{
			$parameters = $this->getMultiIblockParams($params);
		}
		else
		{
			$parameters = $this->getSingleIblockParams($params);
		}

		$this->storage['IBLOCK_PARAMS'] = $parameters;
	}

	/**
	 * Process iblock component parameters for single iblock with disabled multi-iblock-mode.
	 *
	 * @param $params
	 * @return array
	 */
	protected function getMultiIblockParams(&$params)
	{
		$usePropertyFeatures = Iblock\Model\PropertyFeature::isEnabledFeatures();

		$params['PROPERTY_CODE'] = array();
		$params['CART_PROPERTIES'] = array();
		$params['SHOW_PRODUCTS'] = $params['SHOW_PRODUCTS'] ?? array();

		foreach ($params as $name => $prop)
		{
			$match = array();
			if (preg_match('/^PROPERTY_CODE_(\d+)$/', $name, $match))
			{
				$iblockId = (int)$match[1];
				if ($iblockId <= 0)
					continue;

				if (!empty($params[$name]) && is_array($params[$name]))
				{
					foreach ($params[$name] as $k => $v)
					{
						if ($v == '')
						{
							unset($params[$name][$k]);
						}
					}

					$params['PROPERTY_CODE'][$iblockId] = $params[$name];
				}
				unset($params[$match[0]]);
			}
			elseif (preg_match('/^CART_PROPERTIES_(\d+)$/', $name, $match))
			{
				$iblockId = (int)$match[1];
				if ($iblockId <= 0)
					continue;

				if (!empty($params[$name]) && is_array($params[$name]))
				{
					foreach ($params[$name] as $k => $v)
					{
						if ($v == '' || $v === '-')
						{
							unset($params[$name][$k]);
						}
					}
					$params['CART_PROPERTIES'][$iblockId] = $params[$name];
				}
				unset($params[$match[0]]);
			}
			elseif (preg_match('/^OFFER_TREE_PROPS_(\d+)$/', $name, $match))
			{
				$iblockId = (int)$match[1];
				if ($iblockId <= 0)
					continue;

				if (!empty($params[$name]) && is_array($params[$name]))
				{
					foreach ($params[$name] as $k => $v)
					{
						if ($v == '' || $v === '-')
						{
							unset($params[$name][$k]);
						}
					}

					$params['OFFER_TREE_PROPS'][$iblockId] = $params[$name];
				}
				unset($params[$match[0]]);
			}
			elseif (preg_match('/^SHOW_PRODUCTS_(\d+)$/', $name, $match))
			{
				$iblockId = (int)$match[1];
				if ($iblockId <= 0)
					continue;

				if ($params[$name] === 'Y')
				{
					$params['SHOW_PRODUCTS'][$iblockId] = true;
				}

				unset($params[$match[0]]);
			}
			unset($match);
		}

		$parameters = array();

		if (!empty($params['SHOW_PRODUCTS']))
		{
			foreach (array_keys($params['SHOW_PRODUCTS']) as $iblockId)
			{
				$catalog = \CCatalogSku::GetInfoByProductIBlock($iblockId);

				// product iblock parameters
				$parameters[$iblockId] = array(
					'PROPERTY_CODE' => $params['PROPERTY_CODE'][$iblockId] ?? array(),
					'CART_PROPERTIES' => (!$usePropertyFeatures && isset($params['CART_PROPERTIES'][$iblockId])
						? $params['CART_PROPERTIES'][$iblockId]
						: array()
					)
				);

				// offers iblock parameters
				if (!empty($catalog))
				{
					$parameters[$iblockId]['OFFERS_FIELD_CODE'] = array('ID', 'CODE', 'NAME', 'SORT', 'PREVIEW_PICTURE', 'DETAIL_PICTURE');
					$parameters[$iblockId]['OFFERS_PROPERTY_CODE'] = $params['PROPERTY_CODE'][$catalog['IBLOCK_ID']] ?? array();
					$parameters[$iblockId]['OFFERS_CART_PROPERTIES'] = (!$usePropertyFeatures && isset($params['CART_PROPERTIES'][$catalog['IBLOCK_ID']])
						? $params['CART_PROPERTIES'][$catalog['IBLOCK_ID']]
						: array()
					);
					$parameters[$iblockId]['OFFERS_TREE_PROPS'] = (!$usePropertyFeatures && isset($params['OFFER_TREE_PROPS'][$catalog['IBLOCK_ID']])
						? $params['OFFER_TREE_PROPS'][$catalog['IBLOCK_ID']]
						: []
					);
				}
			}
		}

		return $parameters;
	}

	/**
	 * Process iblock component parameters for single iblock with disabled multi-iblock-mode.
	 *
	 * @param $params
	 * @return array
	 */
	protected function getSingleIblockParams(&$params)
	{
		$usePropertyFeatures = Iblock\Model\PropertyFeature::isEnabledFeatures();

		if (!isset($params['PROPERTY_CODE']) || !is_array($params['PROPERTY_CODE']))
		{
			$params['PROPERTY_CODE'] = array();
		}

		foreach ($params['PROPERTY_CODE'] as $k => $v)
		{
			if ($v == '')
			{
				unset($params['PROPERTY_CODE'][$k]);
			}
		}

		if (!isset($params['OFFERS_FIELD_CODE']))
		{
			$params['OFFERS_FIELD_CODE'] = array();
		}
		elseif (!is_array($params['OFFERS_FIELD_CODE']))
		{
			$params['OFFERS_FIELD_CODE'] = array($params['OFFERS_FIELD_CODE']);
		}

		foreach ($params['OFFERS_FIELD_CODE'] as $key => $value)
		{
			if ($value == '')
			{
				unset($params['OFFERS_FIELD_CODE'][$key]);
			}
		}

		if (!isset($params['OFFERS_PROPERTY_CODE']))
		{
			$params['OFFERS_PROPERTY_CODE'] = array();
		}
		elseif (!is_array($params['OFFERS_PROPERTY_CODE']))
		{
			$params['OFFERS_PROPERTY_CODE'] = array($params['OFFERS_PROPERTY_CODE']);
		}

		foreach ($params['OFFERS_PROPERTY_CODE'] as $key => $value)
		{
			if ($value == '')
			{
				unset($params['OFFERS_PROPERTY_CODE'][$key]);
			}
		}

		$cartProperties = [];
		$offersCartProperties = [];
		$offersTreeProperties = [];
		if (!$usePropertyFeatures)
		{
			if (!isset($params['PRODUCT_PROPERTIES']) || !is_array($params['PRODUCT_PROPERTIES']))
			{
				$params['PRODUCT_PROPERTIES'] = array();
			}

			foreach ($params['PRODUCT_PROPERTIES'] as $k => $v)
			{
				if ($v == '')
				{
					unset($params['PRODUCT_PROPERTIES'][$k]);
				}
			}
			$cartProperties = $params['PRODUCT_PROPERTIES'];

			if (!isset($params['OFFERS_CART_PROPERTIES']) || !is_array($params['OFFERS_CART_PROPERTIES']))
			{
				$params['OFFERS_CART_PROPERTIES'] = array();
			}

			foreach ($params['OFFERS_CART_PROPERTIES'] as $i => $pid)
			{
				if ($pid == '')
				{
					unset($params['OFFERS_CART_PROPERTIES'][$i]);
				}
			}
			$offersCartProperties = $params['OFFERS_CART_PROPERTIES'];

			if (!isset($params['OFFER_TREE_PROPS']))
			{
				$params['OFFER_TREE_PROPS'] = array();
			}
			elseif (!is_array($params['OFFER_TREE_PROPS']))
			{
				$params['OFFER_TREE_PROPS'] = array($params['OFFER_TREE_PROPS']);
			}

			foreach ($params['OFFER_TREE_PROPS'] as $key => $value)
			{
				$value = (string)$value;
				if ($value == '' || $value === '-')
				{
					unset($params['OFFER_TREE_PROPS'][$key]);
				}
			}

			if (empty($params['OFFER_TREE_PROPS']) && !empty($params['OFFERS_CART_PROPERTIES']))
			{
				$params['OFFER_TREE_PROPS'] = $params['OFFERS_CART_PROPERTIES'];
				foreach ($params['OFFER_TREE_PROPS'] as $key => $value)
				{
					if ($value === '-')
					{
						unset($params['OFFER_TREE_PROPS'][$key]);
					}
				}
			}
			$offersTreeProperties = $params['OFFER_TREE_PROPS'];
		}

		return array(
			$params['IBLOCK_ID'] => array(
				'PROPERTY_CODE' => $params['PROPERTY_CODE'],
				'CART_PROPERTIES' => $cartProperties,
				'OFFERS_FIELD_CODE' => $params['OFFERS_FIELD_CODE'],
				'OFFERS_PROPERTY_CODE' => $params['OFFERS_PROPERTY_CODE'],
				'OFFERS_CART_PROPERTIES' => $offersCartProperties,
				'OFFERS_TREE_PROPS' => $offersTreeProperties
			)
		);
	}

	/**
	 * Returns list of product ids which will be showed on first hit.
	 * @return array
	 */
	protected function getProductIds()
	{
		if ($this->isEmptyStartLoad())
		{
			return [];
		}
		return parent::getProductIds();
	}

	/**
	 * @return bool
	 */
	protected function isEmptyStartLoad(): bool
	{
		return (
			isset($this->arParams['LAZY_LOAD'])
			&& $this->arParams['LAZY_LOAD'] === 'Y'
			&& isset($this->arParams['LOAD_ON_SCROLL'])
			&& $this->arParams['LOAD_ON_SCROLL'] === 'Y'
			&& isset($this->arParams['DEFERRED_LOAD'])
			&& $this->arParams['DEFERRED_LOAD'] === 'Y'
		);
	}

	// some logic of \CComponentAjax to execute in component_epilog
	public function prepareLinks(&$data)
	{
		$addParam = \CAjax::GetSessionParam($this->arParams['AJAX_ID']);

		$regexpLinks = '/(<a\s[^>]*?>.*?<\/a>)/is'.BX_UTF_PCRE_MODIFIER;
		$regexpParams = '/([\w\-]+)\s*=\s*([\"\'])(.*?)\2/is'.BX_UTF_PCRE_MODIFIER;

		$this->checkPcreLimit($data);
		$arData = preg_split($regexpLinks, $data, -1, PREG_SPLIT_DELIM_CAPTURE);

		$dataCount = count($arData);
		if ($dataCount < 2)
			return;

		$ignoreAttributes = array(
			'onclick' => true,
			'target' => true
		);
		$search = array(
			$addParam.'&',
			$addParam,
			'AJAX_CALL=Y&',
			'AJAX_CALL=Y'
		);
		$dataChanged = false;

		for ($i = 1; $i < $dataCount; $i += 2)
		{
			if (!preg_match('/^<a\s([^>]*?)>(.*?)<\/a>$/is'.BX_UTF_PCRE_MODIFIER, $arData[$i], $match))
				continue;

			$params = $match[1];

			if (!preg_match_all($regexpParams, $params, $linkParams))
				continue;

			$strAdditional = ' ';
			$urlKey = -1;
			$ignoreLink = false;

			foreach ($linkParams[0] as $key => $value)
			{
				if ($value == '')
					continue;

				$paramName = mb_strtolower($linkParams[1][$key]);

				if ($paramName === 'href')
				{
					$urlKey = $key;
				}
				elseif (isset($ignoreAttributes[$paramName]))
				{
					$ignoreLink = true;
					break;
				}
				else
				{
					$strAdditional .= $value.' ';
				}
			}

			if ($urlKey >= 0 && !$ignoreLink)
			{
				$url = Text\Converter::getHtmlConverter()->decode($linkParams[3][$urlKey]);
				$url = str_replace($search, '', $url);

				if ($this->isAjaxURL($url))
				{
					$realUrl = $url;

					$pos = mb_strpos($url, '#');
					if ($pos !== false)
					{
						$realUrl = mb_substr($realUrl, 0, $pos);
					}

					$realUrl .= mb_strpos($url, '?') === false ? '?' : '&';
					$realUrl .= $addParam;

					$arData[$i] = \CAjax::GetLinkEx($realUrl, $url, $match[2], 'comp_'.$this->arParams['AJAX_ID'], $strAdditional);

					$dataChanged = true;
				}
			}
		}

		if ($dataChanged)
		{
			$data = implode('', $arData);
		}
	}

	private function checkPcreLimit($data)
	{
		$pcreBacktrackLimit = (int)ini_get('pcre.backtrack_limit');
		$textLen = function_exists('mb_strlen')? mb_strlen($data, 'latin1') : mb_strlen($data);
		$textLen++;

		if ($pcreBacktrackLimit > 0 && $pcreBacktrackLimit < $textLen)
		{
			@ini_set('pcre.backtrack_limit', $textLen);
			$pcreBacktrackLimit = intval(ini_get('pcre.backtrack_limit'));
		}

		return $pcreBacktrackLimit >= $textLen;
	}

	private function isAjaxURL($url)
	{
		if (preg_match('/^(#|mailto:|javascript:|callto:)/', $url))
			return false;

		if (mb_strpos($url, '://') !== false)
			return false;

		$url = preg_replace('/#.*/', '', $url);

		if (mb_strpos($url, '?') !== false)
		{
			$url = mb_substr($url, 0, mb_strpos($url, '?'));
		}

		if (mb_substr($url, -4) != '.php')
		{
			if (mb_substr($url, -1) != '/')
			{
				$url .= '/';
			}

			$url .= 'index.php';
		}

		$currentUrl = $this->arParams['CURRENT_BASE_PAGE'];

		if (mb_strpos($currentUrl, '?') !== false)
		{
			$currentUrl = mb_substr($currentUrl, 0, mb_strpos($currentUrl, '?'));
		}

		if (mb_substr($currentUrl, -4) != '.php')
		{
			if (mb_substr($currentUrl, -1) != '/')
			{
				$currentUrl .= '/';
			}

			$currentUrl .= 'index.php';
		}

		$currentUrlDirName = dirname($currentUrl);
		$currentUrlBaseName = basename($currentUrl);
		$dirName = dirname($url);

		if (
			($dirName == $currentUrlDirName || $dirName == '' || $dirName == '.')
			&& basename($url) == $currentUrlBaseName
		)
		{
			return true;
		}

		return false;
	}

	protected function initQueryFields()
	{
		parent::initQueryFields();
		$this->initSubQuery();
	}

	protected function initSubQuery()
	{
		$this->storage['SUB_FILTER'] = array();

		if (
			$this->useCatalog
			&& !$this->isMultiIblockMode()
			&& $this->offerIblockExist($this->arParams['IBLOCK_ID'])
		)
		{
			$catalogFilter = array();
			foreach ($this->globalFilter as $key => $value)
			{
				if (\CProductQueryBuilder::isCatalogFilterField($key))
				{
					//TODO: remove this hack after new catalog.section filter
					if ($key === '=PRODUCT_UF_PRODUCT_MAPPING')
					{
						continue;
					}
					$catalogFilter[$key] = $value;
					unset($this->globalFilter[$key]);
				}
			}

			$iblock = $this->storage['CATALOGS'][$this->arParams['IBLOCK_ID']];
			$offersFilterExists = !empty($this->globalFilter['OFFERS']) && is_array($this->globalFilter['OFFERS']);

			if ($offersFilterExists)
			{
				$this->storage['SUB_FILTER'] = array_merge($this->globalFilter['OFFERS'], $catalogFilter);
				$this->storage['SUB_FILTER']['IBLOCK_ID'] = $iblock['IBLOCK_ID'];
				$this->storage['SUB_FILTER']['ACTIVE_DATE'] = 'Y';
				$this->storage['SUB_FILTER']['ACTIVE'] = 'Y';

				if ($this->arParams['HIDE_NOT_AVAILABLE'] === 'Y')
				{
					$this->storage['SUB_FILTER']['AVAILABLE'] = 'Y';
				}

				$this->filterFields['=ID'] = \CIBlockElement::SubQuery(
					'PROPERTY_'.$iblock['SKU_PROPERTY_ID'],
					$this->storage['SUB_FILTER']
				);
			}
			elseif (!empty($catalogFilter))
			{
				$this->storage['SUB_FILTER'] = $catalogFilter;
				$this->storage['SUB_FILTER']['IBLOCK_ID'] = $iblock['IBLOCK_ID'];
				$this->storage['SUB_FILTER']['ACTIVE_DATE'] = 'Y';
				$this->storage['SUB_FILTER']['ACTIVE'] = 'Y';

				$this->filterFields[] = array(
					'LOGIC' => 'OR',
					array($catalogFilter),
					'=ID' => \CIBlockElement::SubQuery(
						'PROPERTY_'.$iblock['SKU_PROPERTY_ID'],
						$this->storage['SUB_FILTER']
					),
				);
			}
		}
	}

	protected function getIblockElements($elementIterator)
	{
		$iblockElements = array();

		if (!empty($elementIterator))
		{
			while ($element = $elementIterator->GetNext())
			{
				$this->processElement($element);
				$iblockElements[$element['ID']] = $element;
			}
		}

		return $iblockElements;
	}

	protected function modifyDisplayProperties($iblock, &$iblockElements)
	{
		if (!empty($iblockElements))
		{
			$iblockParams = $this->storage['IBLOCK_PARAMS'][$iblock];
			$propertyCodes = $iblockParams['PROPERTY_CODE'];
			$productProperties = $iblockParams['CART_PROPERTIES'];
			$getPropertyCodes = !empty($propertyCodes);
			$getProductProperties = $this->arParams['ADD_PROPERTIES_TO_BASKET'] === 'Y' && !empty($productProperties);
			$getIblockProperties = $getPropertyCodes || $getProductProperties;

			if ($getIblockProperties || ($this->useCatalog && $this->useDiscountCache))
			{
				$propFilter = array(
					'ID' => array_keys($iblockElements),
					'IBLOCK_ID' => $iblock
				);
				\CIBlockElement::GetPropertyValuesArray($iblockElements, $iblock, $propFilter);

				if ($getPropertyCodes)
				{
					$propertyList = $this->getPropertyList($iblock, $propertyCodes);
				}

				foreach ($iblockElements as &$element)
				{
					if ($this->useCatalog && $this->useDiscountCache)
					{
						if ($this->storage['USE_SALE_DISCOUNTS'])
							Catalog\Discount\DiscountManager::setProductPropertiesCache($element['ID'], $element["PROPERTIES"]);
						else
							\CCatalogDiscount::SetProductPropertiesCache($element['ID'], $element['PROPERTIES']);
					}

					if ($getIblockProperties)
					{
						if (!empty($propertyList))
						{
							foreach ($propertyList as $pid)
							{
								if (!isset($element['PROPERTIES'][$pid]))
									continue;

								$prop =& $element['PROPERTIES'][$pid];
								$isArr = is_array($prop['VALUE']);
								if (
									($isArr && !empty($prop['VALUE']))
									|| (!$isArr && (string)$prop['VALUE'] !== '')
									|| Tools::isCheckboxProperty($prop)
								)
								{
									$element['DISPLAY_PROPERTIES'][$pid] = \CIBlockFormatProperties::GetDisplayValue($element, $prop);
								}
								unset($prop);
							}
							unset($pid);
						}

						if ($getProductProperties)
						{
							$element['PRODUCT_PROPERTIES'] = \CIBlockPriceTools::GetProductProperties(
								$iblock,
								$element['ID'],
								$productProperties,
								$element['PROPERTIES']
							);

							if (!empty($element['PRODUCT_PROPERTIES']))
							{
								$element['PRODUCT_PROPERTIES_FILL'] = \CIBlockPriceTools::getFillProductProperties($element['PRODUCT_PROPERTIES']);
							}
						}
					}
				}
				unset($element);

				\CIBlockFormatProperties::clearCache();
				Tools::clearCache();
			}
		}
	}

	protected function getFilter()
	{
		$filterFields = parent::getFilter();
		$filterFields['ACTIVE'] = 'Y';

		if ($this->arParams['HIDE_NOT_AVAILABLE'] === 'Y')
		{
			$filterFields['AVAILABLE'] = 'Y';
		}

		if (!empty($this->arParams['CUSTOM_FILTER']))
		{
			$filterFields[] = $this->arParams['CUSTOM_FILTER'];
		}

		if (!empty($this->arParams['FILTER_IDS']))
		{
			$filterFields['!ID'] = $this->arParams['FILTER_IDS'];
		}

		return $filterFields;
	}

	protected function getSort()
	{
		$sortFields = $this->getCustomSort();
		if (empty($sortFields))
		{
			if (
				(
					$this->isIblockCatalog
					|| (
						$this->isMultiIblockMode()
						|| (!$this->isMultiIblockMode() && $this->offerIblockExist($this->arParams['IBLOCK_ID']))
					)
				)
				&& $this->arParams['HIDE_NOT_AVAILABLE'] === 'L'
			)
			{
				$sortFields['AVAILABLE'] = 'desc,nulls';
			}

			$field = strtoupper($this->arParams['ELEMENT_SORT_FIELD']);
			if (!isset($sortFields[$field]))
			{
				$sortFields[$field] = $this->arParams['ELEMENT_SORT_ORDER'];
			}

			$field = strtoupper($this->arParams['ELEMENT_SORT_FIELD2']);
			if (!isset($sortFields[$field]))
			{
				$sortFields[$field] = $this->arParams['ELEMENT_SORT_ORDER2'];
			}
			unset($field);
			if (!isset($sortFields['ID']))
			{
				$sortFields['ID'] = 'DESC';
			}
		}

		return $sortFields;
	}

	protected function getCustomSort(): array
	{
		$result = [];

		if (!empty($this->arParams['CUSTOM_ELEMENT_SORT']) && is_array($this->arParams['CUSTOM_ELEMENT_SORT']))
		{
			foreach ($this->arParams['CUSTOM_ELEMENT_SORT'] as $field => $value)
			{
				$field = strtoupper($field);
				if (isset($result[$field]))
				{
					continue;
				}
				if ($field === 'ID' && !empty($value) && is_array($value))
				{
					Collection::normalizeArrayValuesByInt($value, false);
					if (empty($value))
					{
						continue;
					}
				}
				else
				{
					if (!is_string($value))
					{
						continue;
					}
					if (!preg_match(self::SORT_ORDER_MASK, $value))
					{
						continue;
					}
				}

				$result[$field] = $value;
			}
			unset($field, $value);
		}

		return $result;
	}

	protected function getElementList($iblockId, $products)
	{
		// initialLoad case with only deferred bigData items
		if (is_array($this->navParams) && isset($this->navParams['nTopCount']) && $this->navParams['nTopCount'] == 0)
		{
			return false;
		}

		$elementIterator = parent::getElementList($iblockId, $products);

		if (!empty($elementIterator) && $this->isPaginationMode())
		{
			$this->initNavString($elementIterator);
		}

		return $elementIterator;
	}

	protected function initNavString(\CIBlockResult $elementIterator)
	{
		$navComponentParameters = array();

		if ($this->arParams['PAGER_BASE_LINK_ENABLE'] === 'Y')
		{
			$pagerBaseLink = trim($this->arParams['PAGER_BASE_LINK']) ?: $this->arResult['SECTION_PAGE_URL'];

			if ($this->pagerParameters && isset($this->pagerParameters['BASE_LINK']))
			{
				$pagerBaseLink = $this->pagerParameters['BASE_LINK'];
				unset($this->pagerParameters['BASE_LINK']);
			}

			$navComponentParameters['BASE_LINK'] = \CHTTP::urlAddParams($pagerBaseLink, $this->pagerParameters, array('encode' => true));
		}
		else
		{
			$uri = new Main\Web\Uri($this->arParams['CURRENT_BASE_PAGE']);
			$uri->deleteParams(array(
				'PAGEN_'.$elementIterator->NavNum,
				'SIZEN_'.$elementIterator->NavNum,
				'SHOWALL_'.$elementIterator->NavNum,
				'PHPSESSID',
				'clear_cache',
				'bitrix_include_areas'
			));
			$navComponentParameters['BASE_LINK'] = $uri->getUri();
		}

		$this->arResult['NAV_STRING'] = $elementIterator->GetPageNavStringEx(
			$navComponentObject,
			$this->arParams['PAGER_TITLE'],
			$this->arParams['PAGER_TEMPLATE'],
			$this->arParams['PAGER_SHOW_ALWAYS'],
			$this,
			$navComponentParameters
		);
		$this->arResult['NAV_CACHED_DATA'] = null;
		$this->arResult['NAV_RESULT'] = $elementIterator;
		$this->arResult['NAV_PARAM'] = $navComponentParameters;
	}

	protected function chooseOffer($offers, $iblockId)
	{
		if (empty($offers) || empty($this->storage['CATALOGS'][$iblockId]))
			return;
		$uniqueSortHash = array();
		$filteredOffers = array();
		$filteredElements = array();
		$filteredByProperty = $this->getFilteredOffersByProperty($iblockId);

		if (!$this->isMultiIblockMode() && !empty($this->storage['SUB_FILTER']))
		{
			$catalog = $this->storage['CATALOGS'][$iblockId];
			$this->storage['SUB_FILTER']['=PROPERTY_'.$catalog['SKU_PROPERTY_ID']] = array_keys($this->elementLinks);
			$filteredOffers = Iblock\Component\Filters::getFilteredOffersByProduct(
				$catalog['IBLOCK_ID'],
				$catalog['SKU_PROPERTY_ID'],
				$this->storage['SUB_FILTER']
			);
			unset($catalog);
		}

		foreach ($offers as &$offer)
		{
			$elementId = $offer['LINK_ELEMENT_ID'];

			if (!isset($this->elementLinks[$elementId]))
				continue;

			if (!isset($uniqueSortHash[$elementId]))
			{
				$uniqueSortHash[$elementId] = array();
			}

			$uniqueSortHash[$elementId][$offer['SORT_HASH']] = true;

			if ($this->elementLinks[$elementId]['OFFER_ID_SELECTED'] == 0 && $offer['CAN_BUY'])
			{
				if (isset($filteredOffers[$elementId]))
				{
					if (isset($filteredOffers[$elementId][$offer['ID']]))
					{
						$this->elementLinks[$elementId]['OFFER_ID_SELECTED'] = $offer['ID'];
						$filteredElements[$elementId] = true;
					}
				}
				elseif (isset($filteredByProperty[$elementId]))
				{
					if (isset($filteredByProperty[$elementId][$offer['ID']]))
					{
						$this->elementLinks[$elementId]['OFFER_ID_SELECTED'] = $offer['ID'];
						$filteredElements[$elementId] = true;
					}
				}
				else
				{
					$this->elementLinks[$elementId]['OFFER_ID_SELECTED'] = $offer['ID'];
				}
			}
			unset($elementId);
		}

		if (!empty($filteredOffers))
		{
			$this->arResult['FILTERED_OFFERS_ID'] = array();
		}

		foreach ($this->elementLinks as &$element)
		{
			if (isset($filteredOffers[$element['ID']]))
			{
				$this->arResult['FILTERED_OFFERS_ID'][$element['ID']] = $filteredOffers[$element['ID']];
			}

			if ($element['OFFER_ID_SELECTED'] == 0 || isset($filteredElements[$element['ID']]))
				continue;

			if (count($uniqueSortHash[$element['ID']]) < 2)
			{
				$element['OFFER_ID_SELECTED'] = 0;
			}
		}
	}

	protected function getFilteredOffersByProperty($iblockId)
	{
		$offers = array();
		if (empty($this->storage['CATALOGS'][$iblockId]))
			return $offers;

		if (!$this->isMultiIblockMode())
		{
			$filter = $this->getOffersPropFilter($this->arParams['CUSTOM_FILTER']);
			if (!empty($filter))
			{
				$catalog = $this->storage['CATALOGS'][$iblockId];
				$offers = Iblock\Component\Filters::getFilteredOffersByProduct(
					$catalog['IBLOCK_ID'],
					$catalog['SKU_PROPERTY_ID'],
					array(
						'=PROPERTY_'.$catalog['SKU_PROPERTY_ID'] => array_keys($this->elementLinks),
						$filter
					)
				);
			}
		}

		return $offers;
	}

	protected function getOffersPropFilter(array $level)
	{
		$filter = array();
		$checkLogic = true;

		if (!empty($level))
		{
			foreach ($level as $prop)
			{
				if (is_array($prop))
				{
					$filter[] = $this->getOffersPropFilter($prop);
				}
				elseif ($prop instanceOf \CIBlockElement)
				{
					$checkLogic = false;
					$filter = $prop->arFilter;
				}
			}

			if ($checkLogic && is_array($filter) && count($filter) > 1)
			{
				$filter['LOGIC'] = $level['LOGIC'];
			}
		}

		return $filter;
	}

	protected function getAdditionalCacheId()
	{
		return array(
			$this->globalFilter,
			$this->productIdMap,
			$this->arParams['CACHE_GROUPS'] === 'N' ? false : $this->getUserGroupsCacheId(),
			$this->navigation,
			$this->pagerParameters
		);
	}

	protected function getComponentCachePath()
	{
		return '/'.$this->getSiteId().$this->getRelativePath();
	}

	protected function makeOutputResult()
	{
		parent::makeOutputResult();
		$this->arResult['PRICES'] = $this->storage['PRICES'];
		$this->arResult['ITEMS'] = $this->elements;
		$this->arResult['ELEMENTS'] = array_keys($this->elementLinks);
	}

	public function loadData()
	{
		$this->initNavParams();
		parent::loadData();
	}

	protected function deferredLoadAction()
	{
		$this->prepareDeferredParams();
		parent::deferredLoadAction();
	}

	protected function prepareDeferredParams()
	{
		$this->arParams['~PRODUCT_ROW_VARIANTS'] = $this->arParams['~DEFERRED_PRODUCT_ROW_VARIANTS'];
		$this->arParams['PRODUCT_ROW_VARIANTS'] = static::parseJsonParameter($this->arParams['~PRODUCT_ROW_VARIANTS']);

		if (isset($this->arParams['PREDICT_ELEMENT_COUNT']) && $this->arParams['PREDICT_ELEMENT_COUNT'] === 'Y')
		{
			$this->arParams['PAGE_ELEMENT_COUNT'] = static::predictElementCountByVariants($this->arParams['PRODUCT_ROW_VARIANTS']);
		}
		else
		{
			$this->arParams['PAGE_ELEMENT_COUNT'] = $this->arParams['DEFERRED_PAGE_ELEMENT_COUNT'];
		}

		$this->arParams['PAGE_ELEMENT_COUNT'] = (int)$this->arParams['PAGE_ELEMENT_COUNT'];
	}

	/**
	 * Initialization of page navigation parameters.
	 */
	protected function initNavParams()
	{
		if ($this->isPaginationMode())
		{
			if (
				$this->arParams['PAGE_ELEMENT_COUNT'] > 0
				&& (
					$this->arParams['DISPLAY_TOP_PAGER']
					|| $this->arParams['DISPLAY_BOTTOM_PAGER']
					|| $this->arParams['LAZY_LOAD'] === 'Y'
				)
			)
			{
				$this->navParams = array(
					'nPageSize' => $this->arParams['PAGE_ELEMENT_COUNT'],
					'bDescPageNumbering' => $this->arParams['PAGER_DESC_NUMBERING'],
					'bShowAll' => $this->arParams['PAGER_SHOW_ALL']
				);
				$this->navigation = \CDBResult::GetNavParams($this->navParams);

				if ($this->navigation['PAGEN'] == 0 && $this->arParams['PAGER_DESC_NUMBERING_CACHE_TIME'] > 0)
				{
					$this->arParams['CACHE_TIME'] = $this->arParams['PAGER_DESC_NUMBERING_CACHE_TIME'];
				}
			}
			else
			{
				$this->navParams = array(
					'nTopCount' => $this->arParams['PAGE_ELEMENT_COUNT'],
					'bDescPageNumbering' => $this->arParams['PAGER_DESC_NUMBERING'],
				);
				$this->navigation = false;
			}
		}
		else
		{
			$this->navParams = array('nTopCount' => $this->arParams['PAGE_ELEMENT_COUNT']);
			$this->navigation = false;
		}
	}

	protected function prepareTemplateParams()
	{
		parent::prepareTemplateParams();
		$params =& $this->arParams;

		if ($params['LINE_ELEMENT_COUNT'] < 2)
		{
			$params['LINE_ELEMENT_COUNT'] = 2;
		}

		if ($params['LINE_ELEMENT_COUNT'] > 5)
		{
			$params['LINE_ELEMENT_COUNT'] = 5;
		}

		if ($params['ADD_TO_BASKET_ACTION'] != 'BUY')
		{
			$params['ADD_TO_BASKET_ACTION'] = 'ADD';
		}

		if (
			(empty($params['PRODUCT_ROW_VARIANTS']) || !is_array($params['PRODUCT_ROW_VARIANTS']))
			&& isset($params['~PRODUCT_ROW_VARIANTS'])
		)
		{
			$params['PRODUCT_ROW_VARIANTS'] = static::parseJsonParameter($params['~PRODUCT_ROW_VARIANTS']);
		}

		if (empty($params['PRODUCT_ROW_VARIANTS']))
		{
			$params['PRODUCT_ROW_VARIANTS'] = static::predictRowVariants($params['LINE_ELEMENT_COUNT'], $params['PAGE_ELEMENT_COUNT']);
		}

		if (empty($params['PRODUCT_BLOCKS_ORDER']))
		{
			$params['PRODUCT_BLOCKS_ORDER'] = 'price,props,sku,quantityLimit,quantity,buttons';
		}

		if (is_string($params['PRODUCT_BLOCKS_ORDER']))
		{
			$params['PRODUCT_BLOCKS_ORDER'] = explode(',', $params['PRODUCT_BLOCKS_ORDER']);
		}

		$params['PRODUCT_DISPLAY_MODE'] = isset($params['PRODUCT_DISPLAY_MODE']) && $params['PRODUCT_DISPLAY_MODE'] === 'Y' ? 'Y' : 'N';

		if ($this->isMultiIblockMode())
		{
			$this->getTemplateMultiIblockParams($params);
		}
		else
		{
			$this->getTemplateSingleIblockParams($params);
		}
	}

	protected static function parseJsonParameter($jsonString)
	{
		$parameter = [];

		if (!empty($jsonString) && is_string($jsonString))
		{
			try
			{
				$parameter = Json::decode(str_replace("'", '"', $jsonString));
			}
			catch (\Exception $e) {}
		}

		return $parameter;
	}

	/**
	 * Process iblock template parameters for multi iblock mode.
	 *
	 * @param $params
	 */
	protected function getTemplateMultiIblockParams(&$params)
	{
		$params['ADDITIONAL_PICT_PROP'] = array();
		$params['LABEL_PROP'] = array();
		$params['LABEL_PROP_MOBILE'] = array();
		$params['PROPERTY_CODE_MOBILE'] = array();
		$params['ENLARGE_PROP'] = array();
		$params['OFFER_TREE_PROPS'] = array();

		foreach ($params as $name => $prop)
		{
			if (preg_match('/^ADDITIONAL_PICT_PROP_(\d+)$/', $name, $match))
			{
				$iblockId = (int)$match[1];
				if ($iblockId <= 0)
					continue;

				if ($params[$name] != '' && $params[$name] != '-')
				{
					$params['ADDITIONAL_PICT_PROP'][$iblockId] = $params[$name];
				}
				unset($params[$match[0]]);
			}
			elseif (preg_match('/^LABEL_PROP_(\d+)$/', $name, $match))
			{
				$iblockId = (int)$match[1];
				if ($iblockId <= 0)
					continue;

				if (!empty($params[$name]))
				{
					if (!is_array($params[$name]))
					{
						$params[$name] = array($params[$name]);
					}

					foreach ($params[$name] as $k => $v)
					{
						if ($v == '')
						{
							unset($params[$name][$k]);
						}
					}

					$params['LABEL_PROP'][$iblockId] = $params[$name];
				}
			}
			elseif (preg_match('/^LABEL_PROP_MOBILE_(\d+)$/', $name, $match))
			{
				$iblockId = (int)$match[1];
				if ($iblockId <= 0)
					continue;

				if (!empty($params[$name]) && is_array($params[$name]))
				{
					foreach ($params[$name] as $k => $v)
					{
						if ($v == '')
						{
							unset($params[$name][$k]);
						}
					}

					if (!empty($params[$name]))
					{
						$params[$name] = array_flip($params[$name]);
					}

					$params['LABEL_PROP_MOBILE'][$iblockId] = $params[$name];
				}
				unset($params[$match[0]]);
			}
			elseif (preg_match('/^PROPERTY_CODE_MOBILE_(\d+)$/', $name, $match))
			{
				$iblockId = (int)$match[1];
				if ($iblockId <= 0)
					continue;

				if (!empty($params[$name]) && is_array($params[$name]))
				{
					foreach ($params[$name] as $k => $v)
					{
						if ($v == '')
						{
							unset($params[$name][$k]);
						}
					}

					if (!empty($params[$name]))
					{
						$params[$name] = array_flip($params[$name]);
					}

					$params['PROPERTY_CODE_MOBILE'][$iblockId] = $params[$name];
				}
				unset($params[$match[0]]);
			}
			elseif (preg_match('/^ENLARGE_PROP_(\d+)$/', $name, $match))
			{
				$iblockId = (int)$match[1];
				if ($iblockId <= 0)
					continue;

				if ($params[$name] != '' && $params[$name] != '-')
				{
					$params['ENLARGE_PROP'][$iblockId] = $params[$name];
				}
				unset($params[$match[0]]);
			}
		}

		if (!empty($params['SHOW_PRODUCTS']))
		{
			$usePropertyFeatures = Iblock\Model\PropertyFeature::isEnabledFeatures();

			foreach (array_keys($params['SHOW_PRODUCTS']) as $iblockId)
			{
				if (!isset($this->storage['IBLOCK_PARAMS'][$iblockId]) || !is_array($this->storage['IBLOCK_PARAMS'][$iblockId]))
				{
					$this->storage['IBLOCK_PARAMS'][$iblockId] = array();
				}

				// product iblock parameters
				$this->storage['IBLOCK_PARAMS'][$iblockId]['ADD_PICT_PROP'] = $params['ADDITIONAL_PICT_PROP'][$iblockId] ?? '';
				$this->storage['IBLOCK_PARAMS'][$iblockId]['LABEL_PROP'] = $params['LABEL_PROP'][$iblockId] ?? array();
				$this->storage['IBLOCK_PARAMS'][$iblockId]['LABEL_PROP_MOBILE'] = $params['LABEL_PROP_MOBILE'][$iblockId] ?? array();
				$this->storage['IBLOCK_PARAMS'][$iblockId]['PROPERTY_CODE_MOBILE'] = $params['PROPERTY_CODE_MOBILE'][$iblockId] ?? array();
				$this->storage['IBLOCK_PARAMS'][$iblockId]['ENLARGE_PROP'] = $params['ENLARGE_PROP'][$iblockId] ?? '';

				// offers iblock parameters
				$catalog = \CCatalogSku::GetInfoByProductIBlock($iblockId);
				if (!empty($catalog))
				{
					$this->storage['IBLOCK_PARAMS'][$iblockId]['OFFERS_ADD_PICT_PROP'] = $params['ADDITIONAL_PICT_PROP'][$catalog['IBLOCK_ID']] ?? '';
				}
			}

			unset($usePropertyFeatures);
		}
	}

	/**
	 * Process iblock template parameters for single iblock with disabled multi-iblock-mode.
	 *
	 * @param $params
	 */
	protected function getTemplateSingleIblockParams(&$params)
	{
		$params['ADD_PICT_PROP'] = isset($params['ADD_PICT_PROP']) ? trim($params['ADD_PICT_PROP']) : '';
		if ($params['ADD_PICT_PROP'] === '-')
		{
			$params['ADD_PICT_PROP'] = '';
		}

		if (!isset($params['LABEL_PROP']) || !is_array($params['LABEL_PROP']))
		{
			$params['LABEL_PROP'] = array();
		}

		if (!isset($params['LABEL_PROP_MOBILE']) || !is_array($params['LABEL_PROP_MOBILE']))
		{
			$params['LABEL_PROP_MOBILE'] = array();
		}

		if (!empty($params['LABEL_PROP_MOBILE']))
		{
			$params['LABEL_PROP_MOBILE'] = array_flip($params['LABEL_PROP_MOBILE']);
		}

		if (!isset($params['PROPERTY_CODE_MOBILE']) || !is_array($params['PROPERTY_CODE_MOBILE']))
		{
			$params['PROPERTY_CODE_MOBILE'] = array();
		}

		if (!empty($params['PROPERTY_CODE_MOBILE']))
		{
			$params['PROPERTY_CODE_MOBILE'] = array_flip($params['PROPERTY_CODE_MOBILE']);
		}

		$params['ENLARGE_PROP'] = isset($params['ENLARGE_PROP']) ? trim($params['ENLARGE_PROP']) : '';
		if ($params['ENLARGE_PROP'] === '-')
		{
			$params['ENLARGE_PROP'] = '';
		}

		$params['OFFER_ADD_PICT_PROP'] = isset($params['OFFER_ADD_PICT_PROP']) ? trim($params['OFFER_ADD_PICT_PROP']) : '';
		if ($params['OFFER_ADD_PICT_PROP'] === '-')
		{
			$params['OFFER_ADD_PICT_PROP'] = '';
		}

		if (!isset($this->storage['IBLOCK_PARAMS'][$params['IBLOCK_ID']]) || !is_array($this->storage['IBLOCK_PARAMS'][$params['IBLOCK_ID']]))
		{
			$this->storage['IBLOCK_PARAMS'][$params['IBLOCK_ID']] = array();
		}

		$this->storage['IBLOCK_PARAMS'][$params['IBLOCK_ID']]['ADD_PICT_PROP'] = $params['ADD_PICT_PROP'];
		$this->storage['IBLOCK_PARAMS'][$params['IBLOCK_ID']]['LABEL_PROP'] = $params['LABEL_PROP'];
		$this->storage['IBLOCK_PARAMS'][$params['IBLOCK_ID']]['LABEL_PROP_MOBILE'] = $params['LABEL_PROP_MOBILE'];
		$this->storage['IBLOCK_PARAMS'][$params['IBLOCK_ID']]['PROPERTY_CODE_MOBILE'] = $params['PROPERTY_CODE_MOBILE'];
		$this->storage['IBLOCK_PARAMS'][$params['IBLOCK_ID']]['ENLARGE_PROP'] = $params['ENLARGE_PROP'];
		$this->storage['IBLOCK_PARAMS'][$params['IBLOCK_ID']]['OFFERS_ADD_PICT_PROP'] = $params['OFFER_ADD_PICT_PROP'];
		unset($skuTreeProperties);
	}

	public static function getDefaultVariantId()
	{
		$variantId = 0;
		$templateVariantsMap = static::getTemplateVariantsMap();

		if (!empty($templateVariantsMap))
		{
			foreach ($templateVariantsMap as $key => $variant)
			{
				if (isset($variant['DEFAULT']) && $variant['DEFAULT'] === 'Y')
				{
					$variantId = $variant['VARIANT'];
					break;
				}
			}
		}

		return $variantId;
	}

	public static function predictRowVariants($lineElementCount, $pageElementCount)
	{
		if ($pageElementCount <= 0)
		{
			return array();
		}

		$templateVariantsMap = static::getTemplateVariantsMap();

		if (empty($templateVariantsMap))
		{
			return array();
		}

		$variantId = self::getDefaultVariantId();

		foreach ($templateVariantsMap as $key => $variant)
		{
			if ($variant['COUNT'] == $lineElementCount && $variant['ENLARGED_POS'] === false)
			{
				$variantId = $key;
				break;
			}
		}

		return array_fill(
			0,
			ceil($pageElementCount / $templateVariantsMap[$variantId]['COUNT']),
			array('VARIANT' => $variantId, 'BIG_DATA' => false)
		);
	}

	protected function checkTemplateTheme()
	{
		parent::checkTemplateTheme();

		if ($this->isPaginationMode())
		{
			if (isset($this->arResult['NAV_PARAM']) && is_array($this->arResult['NAV_PARAM']))
			{
				$this->arResult['NAV_PARAM']['TEMPLATE_THEME'] = $this->arParams['TEMPLATE_THEME'];
			}

			if (!empty($this->arResult['NAV_RESULT']))
			{
				/** @var \CBitrixComponent $navComponentObject */
				$this->arResult['NAV_STRING'] = $this->arResult['NAV_RESULT']->GetPageNavStringEx(
					$navComponentObject,
					$this->arParams['PAGER_TITLE'],
					$this->arParams['PAGER_TEMPLATE'],
					$this->arParams['PAGER_SHOW_ALWAYS'],
					$this,
					$this->arResult['NAV_PARAM']
				);
			}
		}
	}

	protected function getTemplateDefaultParams()
	{
		$defaultParams = parent::getTemplateDefaultParams();
		$defaultParams['PRODUCT_BLOCKS_ORDER'] = 'price,props,sku,quantity,buttons';
		$defaultParams['PRODUCT_ROW_VARIANTS'] = array();
		$defaultParams['PROPERTY_CODE_MOBILE'] = array();
		$defaultParams['SHOW_SLIDER'] = 'Y';
		$defaultParams['SLIDER_INTERVAL'] = 3000;
		$defaultParams['ENLARGE_PRODUCT'] = 'STRICT';
		$defaultParams['ENLARGE_PROP'] = '';
		$defaultParams['ADD_TO_BASKET_ACTION'] = 'ADD';
		$defaultParams['MESS_BTN_LAZY_LOAD'] = '';

		return $defaultParams;
	}

	protected function editTemplateData()
	{
		$this->arResult['CURRENCIES'] = $this->getTemplateCurrencies();

		if (!empty($this->arResult['ITEMS']))
		{
			$this->arResult['DEFAULT_PICTURE'] = $this->getTemplateEmptyPreview();
			$this->arResult['SKU_PROPS'] = $this->getTemplateSkuPropList();
			$this->editTemplateItems($this->arResult['ITEMS']);
			$this->sortItemsByTemplateVariants();
		}

		$this->arResult['BIG_DATA'] = [];
		if ($this->request->getRequestMethod() === 'GET')
		{
			$this->arResult['BIG_DATA'] = $this->getBigDataInfo();
		}
	}

	/**
	 * Creating sequence of variants to show
	 */
	protected function sortItemsByTemplateVariants()
	{
		$rows = array();
		$variantsMap = static::getTemplateVariantsMap();
		$isBigData = $this->getAction() === 'bigDataLoad';

		if ($this->arParams['ENLARGE_PRODUCT'] === 'PROP')
		{
			$enlargedIndexMap = $this->getEnlargedIndexMap();
		}

		if (!empty($this->arParams['PRODUCT_ROW_VARIANTS']))
		{
			$showItems = false;

			foreach ($this->arParams['PRODUCT_ROW_VARIANTS'] as $variant)
			{
				if (
					(!$isBigData && !$variant['BIG_DATA'])
					|| ($isBigData && $variant['BIG_DATA'])
				)
				{
					$showItems = true;
					break;
				}
			}
		}
		else
		{
			$showItems = true;
		}

		if ($showItems)
		{
			$variantParam = false;
			$itemsCounter = 0;
			$itemsLength = count($this->arResult['ITEMS']);

			while (($itemsRemaining = $itemsLength - $itemsCounter) > 0)
			{
				if ($variantParam === false)
				{
					$variantParam = reset($this->arParams['PRODUCT_ROW_VARIANTS']);
				}

				//	skip big_data rows on initial load and not_big_data rows on deferred load
				if (!empty($variantParam))
				{
					if (
						$isBigData && !$variantParam['BIG_DATA']
						|| !$isBigData && $variantParam['BIG_DATA']
					)
					{
						$variantParam = next($this->arParams['PRODUCT_ROW_VARIANTS']);
						// if last variant is not suitable - should reset again
						if ($variantParam === false)
						{
							$variantParam = reset($this->arParams['PRODUCT_ROW_VARIANTS']);
						}

						if ($variantParam === false)
							break;
						else
							continue;
					}
				}

				if (
					$variantParam === false
					|| !isset($variantsMap[$variantParam['VARIANT']])
					|| ($variantsMap[$variantParam['VARIANT']]['SHOW_ONLY_FULL'] && $variantsMap[$variantParam['VARIANT']]['COUNT'] > $itemsRemaining)
				)
				{
					// default variant
					$variant = $variantsMap[self::getDefaultVariantId()];
				}
				else
				{
					$variant = $variantsMap[$variantParam['VARIANT']];
				}

				// sorting by property $arResult['ITEMS'] for proper elements enlarge
				if ($this->arParams['ENLARGE_PRODUCT'] === 'PROP' && $variant['ENLARGED_POS'] !== false)
				{
					if (!empty($enlargedIndexMap))
					{
						$overallPos = $itemsCounter + $variant['ENLARGED_POS'];
						$overallPosKey = array_search($overallPos, $enlargedIndexMap);
						if ($overallPosKey === false)
						{
							$closestPos = false;
							$closestPosKey = false;
							$enlargedPosInRange = array_intersect($enlargedIndexMap , range($itemsCounter, $itemsCounter + $variant['COUNT']));

							if (!empty($enlargedPosInRange))
							{
								foreach ($enlargedPosInRange as $key => $posInRange)
								{
									if ($closestPos === false || abs($overallPos - $closestPos) > abs($posInRange - $overallPos))
									{
										$closestPos = $posInRange;
										$closestPosKey = $key;
									}
								}

								$temporary = array($this->arResult['ITEMS'][$closestPos]);
								unset($this->arResult['ITEMS'][$closestPos], $enlargedIndexMap[$closestPosKey]);
								array_splice($this->arResult['ITEMS'], $overallPos, 0, $temporary);
							}
						}
						else
						{
							unset($enlargedIndexMap[$overallPosKey]);
						}
					}
				}

				$rows[] = $variant;
				$itemsCounter += $variant['COUNT'];
				$variantParam = next($this->arParams['PRODUCT_ROW_VARIANTS']);
			}
		}

		$this->arResult['ITEM_ROWS'] = $rows;
	}

	/**
	 * Return array of big data settings.
	 *
	 * @return array
	 */
	protected function getBigDataInfo()
	{
		$rows = array();
		$count = 0;
		$rowsRange = array();
		$variantsMap = static::getTemplateVariantsMap();

		if (!empty($this->arParams['PRODUCT_ROW_VARIANTS']))
		{
			foreach ($this->arParams['PRODUCT_ROW_VARIANTS'] as $key => $row)
			{
				if ($row['BIG_DATA'])
				{
					$rows[$key] = $row;

					if (isset($variantsMap[$row['VARIANT']]))
					{
						$count += (int)$variantsMap[$row['VARIANT']]['COUNT'];
					}

					$rowsRange[] = $count;
				}
			}
		}

		$shownIds = array();
		if (!empty($this->elements))
		{
			foreach ($this->elements as $element)
			{
				$shownIds[] = $element['ID'];
			}
		}

		return array(
			'enabled' => $count > 0,
			'rows' => $rows,
			'count' => $count,
			'rowsRange' => $rowsRange,
			'shownIds' => $shownIds,
			'js' => array(
				'cookiePrefix' => \COption::GetOptionString('main', 'cookie_name', 'BITRIX_SM'),
				'cookieDomain' => Main\Web\Cookie::getCookieDomain(),
				'serverTime' => $count > 0 ? time() : 0,
			),
			'params' => $this->getBigDataServiceRequestParams(($this->arParams['RCM_TYPE'] ?? ''))
		);
	}

	// getting positions of enlarged elements
	protected function getEnlargedIndexMap()
	{
		$enlargedIndexMap = array();

		foreach ($this->arResult['ITEMS'] as $key => $item)
		{
			if ($item['ENLARGED'] === 'Y')
			{
				$enlargedIndexMap[] = $key;
			}
		}

		return $enlargedIndexMap;
	}

	public static function getTemplateVariantsMap()
	{
		return array(
			array(
				'VARIANT' => 0,
				'TYPE' => 'CARD',
				'COLS' => 1,
				'CLASS' => 'product-item-list-col-1',
				'CODE' => '1',
				'ENLARGED_POS' => false,
				'SHOW_ONLY_FULL' => false,
				'COUNT' => 1,
				'DEFAULT' => 'N'
			),
			array(
				'VARIANT' => 1,
				'TYPE' => 'CARD',
				'COLS' => 2,
				'CLASS' => 'product-item-list-col-2',
				'CODE' => '2',
				'ENLARGED_POS' => false,
				'SHOW_ONLY_FULL' => false,
				'COUNT' => 2,
				'DEFAULT' => 'N'
			),
			array(
				'VARIANT' => 2,
				'TYPE' => 'CARD',
				'COLS' => 3,
				'CLASS' => 'product-item-list-col-3',
				'CODE' => '3',
				'ENLARGED_POS' => false,
				'SHOW_ONLY_FULL' => false,
				'COUNT' => 3,
				'DEFAULT' => 'Y'
			),
			array(
				'VARIANT' => 3,
				'TYPE' => 'CARD',
				'COLS' => 4,
				'CLASS' => 'product-item-list-col-4',
				'CODE' => '4',
				'ENLARGED_POS' => false,
				'SHOW_ONLY_FULL' => false,
				'COUNT' => 4,
				'DEFAULT' => 'N'
			),
			array(
				'VARIANT' => 4,
				'TYPE' => 'CARD',
				'COLS' => 4,
				'CLASS' => 'product-item-list-col-1-4',
				'CODE' => '1-4',
				'ENLARGED_POS' => 0,
				'SHOW_ONLY_FULL' => false,
				'COUNT' => 5,
				'DEFAULT' => 'N'
			),
			array(
				'VARIANT' => 5,
				'TYPE' => 'CARD',
				'COLS' => 4,
				'CLASS' => 'product-item-list-col-4-1',
				'CODE' => '4-1',
				'ENLARGED_POS' => 4,
				'SHOW_ONLY_FULL' => true,
				'COUNT' => 5,
				'DEFAULT' => 'N'
			),
			array(
				'VARIANT' => 6,
				'TYPE' => 'CARD',
				'COLS' => 6,
				'CLASS' => 'product-item-list-col-6',
				'CODE' => '6',
				'ENLARGED_POS' => false,
				'SHOW_ONLY_FULL' => false,
				'COUNT' => 6,
				'DEFAULT' => 'N'
			),
			array(
				'VARIANT' => 7,
				'TYPE' => 'CARD',
				'COLS' => 6,
				'CLASS' => 'product-item-list-col-1-6',
				'CODE' => '1-6',
				'ENLARGED_POS' => 0,
				'SHOW_ONLY_FULL' => false,
				'COUNT' => 7,
				'DEFAULT' => 'N'
			),
			array(
				'VARIANT' => 8,
				'TYPE' => 'CARD',
				'COLS' => 6,
				'CLASS' => 'product-item-list-col-6-1',
				'CODE' => '6-1',
				'ENLARGED_POS' => 6,
				'SHOW_ONLY_FULL' => true,
				'COUNT' => 7,
				'DEFAULT' => 'N'
			),
			array(
				'VARIANT' => 9,
				'TYPE' => 'LINE',
				'COLS' => 1,
				'CLASS' => 'product-item-line-list',
				'CODE' => 'line',
				'ENLARGED_POS' => false,
				'SHOW_ONLY_FULL' => false,
				'COUNT' => 1,
				'DEFAULT' => 'N'
			)
		);
	}

	public function getTemplateSkuPropList()
	{
		$skuPropList = array();

		if ($this->arResult['MODULES']['catalog'] && !empty($this->storage['IBLOCK_PARAMS']))
		{
			$elementIndex = array_keys($this->elements);

			foreach ($this->storage['IBLOCK_PARAMS'] as $iblockId => $iblockParams)
			{
				$skuPropList[$iblockId] = array();
				$sku = \CCatalogSku::GetInfoByProductIBlock($iblockId);
				$boolSku = !empty($sku) && is_array($sku);

				if ($boolSku && !empty($iblockParams['OFFERS_TREE_PROPS']) && $this->arParams['PRODUCT_DISPLAY_MODE'] === 'Y')
				{
					$skuPropList[$iblockId] = \CIBlockPriceTools::getTreeProperties(
						$sku,
						$iblockParams['OFFERS_TREE_PROPS'],
						array(
							'PICT' => $this->arResult['DEFAULT_PICTURE'],
							'NAME' => '-'
						)
					);

					if (!empty($skuPropList[$iblockId]))
					{
						if (!empty($this->productWithOffers[$iblockId]))
						{
							$skuPropIds = array();
							foreach ($skuPropList[$iblockId] as $property)
							{
								$skuPropIds[$property['CODE']] = array(
									'ID' => $property['ID'],
									'CODE' => $property['CODE'],
									'PROPERTY_TYPE' => $property['PROPERTY_TYPE'],
									'USER_TYPE' => $property['USER_TYPE']
								);
							}
							unset($property);

							$needValues = array();
							foreach ($elementIndex as $index)
							{
								if ($this->elements[$index]['IBLOCK_ID'] != $iblockId)
									continue;
								if ($this->elements[$index]['PRODUCT']['TYPE'] != Catalog\ProductTable::TYPE_SKU)
									continue;
								if (empty($this->elements[$index]['OFFERS']))
									continue;
								foreach ($this->elements[$index]['OFFERS'] as $offer)
								{
									foreach ($skuPropIds as $property)
									{
										if (isset($offer['DISPLAY_PROPERTIES'][$property['CODE']]))
										{
											if (!isset($needValues[$property['ID']]))
												$needValues[$property['ID']] = array();
											$valueId = ($property['PROPERTY_TYPE'] == Iblock\PropertyTable::TYPE_LIST
												? $offer['DISPLAY_PROPERTIES'][$property['CODE']]['VALUE_ENUM_ID']
												: $offer['DISPLAY_PROPERTIES'][$property['CODE']]['VALUE']
											);
											$needValues[$property['ID']][$valueId] = $valueId;
											unset($valueId);
										}
									}
									unset($property);
								}
								unset($offer);
							}
							unset($index);

							if (!empty($needValues))
								\CIBlockPriceTools::getTreePropertyValues($skuPropList[$iblockId], $needValues);
							unset($needValues);

							unset($skuPropIds);
						}
					}
					else
					{
						$this->arParams['PRODUCT_DISPLAY_MODE'] = 'N';
					}
				}
			}
		}

		return $skuPropList;
	}

	protected function editTemplateItems(&$items)
	{
		$enableCompatible = $this->isEnableCompatible();
		foreach ($items as $key => &$item)
		{
			$iblockParams = $this->storage['IBLOCK_PARAMS'][$item['IBLOCK_ID']];

			if (!isset($item['CATALOG_QUANTITY']))
			{
				$item['CATALOG_QUANTITY'] = 0;
			}

			$item['CATALOG_QUANTITY'] = $item['CATALOG_QUANTITY'] > 0 && is_float($item['ITEM_MEASURE_RATIOS'][$item['ITEM_MEASURE_RATIO_SELECTED']]['RATIO'])
				? (float)$item['CATALOG_QUANTITY']
				: (int)$item['CATALOG_QUANTITY'];

			$item['CATALOG'] = false;
			$item['CATALOG_SUBSCRIPTION'] = ($item['CATALOG_SUBSCRIPTION'] ?? '') === 'Y' ? 'Y' : 'N';

			$item['BIG_DATA'] = $this->getAction() === 'bigDataLoad';

			\CIBlockPriceTools::getLabel($item, $iblockParams['LABEL_PROP']);
			$item['LABEL_PROP_MOBILE'] = $iblockParams['LABEL_PROP_MOBILE'];
			$item['PROPERTY_CODE_MOBILE'] = $iblockParams['PROPERTY_CODE_MOBILE'];
			static::checkEnlargedData($item, $iblockParams['ENLARGE_PROP']);

			if ($this->arParams['SHOW_SLIDER'] === 'Y')
			{
				$this->editTemplateProductSlider($item, $item['IBLOCK_ID'], 5, true, array($this->arResult['DEFAULT_PICTURE']));
			}

			$this->editTemplateProductPictures($item);
			$this->editTemplateCatalogInfo($item);

			if ($item['CATALOG'] && !empty($item['OFFERS']))
			{
				if ($this->arParams['PRODUCT_DISPLAY_MODE'] === 'Y')
				{
					$this->editTemplateOfferProps($item);
					$this->editTemplateJsOffers($item);
				}

				if ($this->arParams['CALCULATE_SKU_MIN_PRICE'] || $this->arParams['PRODUCT_DISPLAY_MODE'] !== 'Y')
				{
					$baseCurrency = '';
					if ($this->arResult['MODULES']['catalog'] && !isset($this->arResult['CONVERT_CURRENCY']['CURRENCY_ID']))
					{
						$baseCurrency = Currency\CurrencyManager::getBaseCurrency();
					}

					$currency = $this->arResult['CONVERT_CURRENCY']['CURRENCY_ID'] ?? $baseCurrency;

					$item['ITEM_START_PRICE'] = null;
					$item['ITEM_START_PRICE_SELECTED'] = null;
					if ($enableCompatible)
						$item['MIN_PRICE'] = false;

					$minPrice = null;
					$minPriceIndex = null;
					foreach (array_keys($item['OFFERS']) as $index)
					{
						if (!$item['OFFERS'][$index]['CAN_BUY'] || $item['OFFERS'][$index]['ITEM_PRICE_SELECTED'] === null)
							continue;

						$currentPrice = $item['OFFERS'][$index]['ITEM_PRICES'][$item['OFFERS'][$index]['ITEM_PRICE_SELECTED']];
						if ($currentPrice['CURRENCY'] != $currency)
						{
							$priceScale = \CCurrencyRates::ConvertCurrency(
								$currentPrice['RATIO_PRICE'],
								$currentPrice['CURRENCY'],
								$currency
							);
						}
						else
						{
							$priceScale = $currentPrice['RATIO_PRICE'];
						}
						if ($minPrice === null || $minPrice > $priceScale)
						{
							$minPrice = $priceScale;
							$minPriceIndex = $index;
						}
						unset($priceScale, $currentPrice);
					}
					unset($index);

					if ($minPriceIndex !== null)
					{
						$minOffer = $item['OFFERS'][$minPriceIndex];
						$item['ITEM_START_PRICE_SELECTED'] = $minPriceIndex;
						$item['ITEM_START_PRICE'] = $minOffer['ITEM_PRICES'][$minOffer['ITEM_PRICE_SELECTED']];
						if ($enableCompatible)
						{
							$item['MIN_PRICE'] = array(
								'CATALOG_MEASURE_RATIO' => $minOffer['ITEM_MEASURE_RATIOS'][$minOffer['ITEM_MEASURE_RATIO_SELECTED']]['RATIO'],
								'CATALOG_MEASURE' => $minOffer['ITEM_MEASURE']['ID'],
								'CATALOG_MEASURE_NAME' => $minOffer['ITEM_MEASURE']['TITLE'],
								'~CATALOG_MEASURE_NAME' => $minOffer['ITEM_MEASURE']['~TITLE'],
								'VALUE' => $item['ITEM_START_PRICE']['RATIO_BASE_PRICE'],
								'DISCOUNT_VALUE' => $item['ITEM_START_PRICE']['RATIO_PRICE'],
								'PRINT_VALUE' => $item['ITEM_START_PRICE']['PRINT_RATIO_BASE_PRICE'],
								'PRINT_DISCOUNT_VALUE' => $item['ITEM_START_PRICE']['PRINT_RATIO_PRICE'],
								'DISCOUNT_DIFF' => $item['ITEM_START_PRICE']['RATIO_DISCOUNT'],
								'PRINT_DISCOUNT_DIFF' => $item['ITEM_START_PRICE']['PRINT_RATIO_DISCOUNT'],
								'DISCOUNT_DIFF_PERCENT' => $item['ITEM_START_PRICE']['PERCENT'],
								'CURRENCY' => $item['ITEM_START_PRICE']['CURRENCY']
							);
						}
						unset($minOffer);
					}
					unset($minPriceIndex, $minPrice);

					unset($baseCurrency, $currency);
				}
			}

			if (
				$this->arResult['MODULES']['catalog']
				&& $item['CATALOG']
				&& (
					$item['CATALOG_TYPE'] == Catalog\ProductTable::TYPE_PRODUCT
					|| $item['CATALOG_TYPE'] == Catalog\ProductTable::TYPE_SET
				)
			)
			{
				if ($enableCompatible)
				{
					if ($item['ITEM_PRICE_SELECTED'] === null)
					{
						$item['RATIO_PRICE'] = null;
						$item['MIN_BASIS_PRICE'] = null;
					}
					else
					{
						$itemPrice = $item['ITEM_PRICES'][$item['ITEM_PRICE_SELECTED']];
						$item['RATIO_PRICE'] = array(
							'VALUE' => $itemPrice['RATIO_BASE_PRICE'],
							'DISCOUNT_VALUE' => $itemPrice['RATIO_PRICE'],
							'PRINT_VALUE' => $itemPrice['PRINT_RATIO_BASE_PRICE'],
							'PRINT_DISCOUNT_VALUE' => $itemPrice['PRINT_RATIO_PRICE'],
							'DISCOUNT_DIFF' => $itemPrice['RATIO_DISCOUNT'],
							'PRINT_DISCOUNT_DIFF' => $itemPrice['PRINT_RATIO_DISCOUNT'],
							'DISCOUNT_DIFF_PERCENT' => $itemPrice['PERCENT'],
							'CURRENCY' => $itemPrice['CURRENCY']
						);
						$item['MIN_BASIS_PRICE'] = array(
							'VALUE' => $itemPrice['BASE_PRICE'],
							'DISCOUNT_VALUE' => $itemPrice['PRICE'],
							'PRINT_VALUE' => $itemPrice['PRINT_BASE_PRICE'],
							'PRINT_DISCOUNT_VALUE' => $itemPrice['PRINT_PRICE'],
							'DISCOUNT_DIFF' => $itemPrice['DISCOUNT'],
							'PRINT_DISCOUNT_DIFF' => $itemPrice['PRINT_DISCOUNT'],
							'DISCOUNT_DIFF_PERCENT' => $itemPrice['PERCENT'],
							'CURRENCY' => $itemPrice['CURRENCY']
						);
						unset($itemPrice);
					}
				}
			}

			if (!empty($item['DISPLAY_PROPERTIES']))
			{
				foreach ($item['DISPLAY_PROPERTIES'] as $propKey => $displayProp)
				{
					{
						if ($displayProp['PROPERTY_TYPE'] === 'F')
						{
							unset($item['DISPLAY_PROPERTIES'][$propKey]);
						}
					}
				}
			}

			$item['LAST_ELEMENT'] = 'N';
		}

		end($items);
		$items[key($items)]['LAST_ELEMENT'] = 'Y';
	}

	protected function editTemplateProductPictures(&$item)
	{
		$iblockParams = $this->storage['IBLOCK_PARAMS'][$item['IBLOCK_ID']];
		$productPictures = \CIBlockPriceTools::getDoublePicturesForItem($item, $iblockParams['ADD_PICT_PROP']);

		if (empty($productPictures['PICT']))
		{
			$productPictures['PICT'] = $this->arResult['DEFAULT_PICTURE'];
		}

		if (empty($productPictures['SECOND_PICT']))
		{
			$productPictures['SECOND_PICT'] = $productPictures['PICT'];
		}

		$item['PREVIEW_PICTURE'] = $productPictures['PICT'];
		$item['PREVIEW_PICTURE_SECOND'] = $productPictures['SECOND_PICT'];
		$item['SECOND_PICT'] = true;
		$item['PRODUCT_PREVIEW'] = $productPictures['PICT'];
		$item['PRODUCT_PREVIEW_SECOND'] = $productPictures['SECOND_PICT'];
	}

	protected function editTemplateJsOffers(&$item)
	{
		$matrix = array();
		$boolSkuDisplayProperties = false;
		$intSelected = -1;

		foreach ($item['OFFERS'] as $offerKey => $offer)
		{
			if ($item['OFFER_ID_SELECTED'] > 0)
			{
				$foundOffer = ($item['OFFER_ID_SELECTED'] == $offer['ID']);
			}
			else
			{
				$foundOffer = $offer['CAN_BUY'];
			}

			if ($foundOffer && $intSelected == -1)
			{
				$intSelected = $offerKey;
			}

			unset($foundOffer);

			$skuProps = false;
			if (!empty($offer['DISPLAY_PROPERTIES']))
			{
				$boolSkuDisplayProperties = true;
				$skuProps = array();
				foreach ($offer['DISPLAY_PROPERTIES'] as $oneProp)
				{
					if ($oneProp['PROPERTY_TYPE'] === 'F')
						continue;

					$skuProps[] = array(
						'CODE' => $oneProp['CODE'],
						'NAME' => $oneProp['NAME'],
						'VALUE' => $oneProp['DISPLAY_VALUE']
					);
				}
				unset($oneProp);
			}

			$ratioSelectedIndex = $offer['ITEM_MEASURE_RATIO_SELECTED'];
			$oneRow = array(
				'ID' => $offer['ID'],
				'NAME' => ($offer['~NAME'] ?? ''),
				'TREE' => $offer['TREE'],
				'DISPLAY_PROPERTIES' => $skuProps,

				// compatible prices
				'PRICE' => ($offer['RATIO_PRICE'] ?? $offer['MIN_PRICE'] ?? 0),
				'BASIS_PRICE' => ($offer['MIN_PRICE'] ?? 0),

				// new prices
				'ITEM_PRICE_MODE' => $offer['ITEM_PRICE_MODE'],
				'ITEM_PRICES' => $offer['ITEM_PRICES'],
				'ITEM_PRICE_SELECTED' => $offer['ITEM_PRICE_SELECTED'],
				'ITEM_QUANTITY_RANGES' => $offer['ITEM_QUANTITY_RANGES'],
				'ITEM_QUANTITY_RANGE_SELECTED' => $offer['ITEM_QUANTITY_RANGE_SELECTED'],
				'ITEM_MEASURE_RATIOS' => $offer['ITEM_MEASURE_RATIOS'],
				'ITEM_MEASURE_RATIO_SELECTED' => $ratioSelectedIndex,
				'SECOND_PICT' => $offer['SECOND_PICT'],
				'OWNER_PICT' => $offer['OWNER_PICT'],
				'PREVIEW_PICTURE' => $offer['PREVIEW_PICTURE'],
				'PREVIEW_PICTURE_SECOND' => $offer['PREVIEW_PICTURE_SECOND'],
				'CHECK_QUANTITY' => $offer['CHECK_QUANTITY'],
				'MAX_QUANTITY' => $offer['PRODUCT']['QUANTITY'],
				'STEP_QUANTITY' => $offer['ITEM_MEASURE_RATIOS'][$ratioSelectedIndex]['RATIO'], // deprecated
				'QUANTITY_FLOAT' => is_float($offer['ITEM_MEASURE_RATIOS'][$ratioSelectedIndex]['RATIO']), //deprecated
				'MEASURE' => $offer['ITEM_MEASURE']['TITLE'],
				'CAN_BUY' => $offer['CAN_BUY'],
				'CATALOG_SUBSCRIBE' => $offer['PRODUCT']['SUBSCRIBE']
			);
			unset($ratioSelectedIndex);

			if (isset($offer['MORE_PHOTO_COUNT']) && $offer['MORE_PHOTO_COUNT'] > 0)
			{
				$oneRow['MORE_PHOTO'] = $offer['MORE_PHOTO'];
				$oneRow['MORE_PHOTO_COUNT'] = $offer['MORE_PHOTO_COUNT'];
			}

			$matrix[$offerKey] = $oneRow;
		}

		if ($intSelected == -1)
		{
			$intSelected = 0;
		}

		if (!$matrix[$intSelected]['OWNER_PICT'])
		{
			$item['PREVIEW_PICTURE'] = $matrix[$intSelected]['PREVIEW_PICTURE'];
			$item['PREVIEW_PICTURE_SECOND'] = $matrix[$intSelected]['PREVIEW_PICTURE_SECOND'];
		}

		$item['JS_OFFERS'] = $matrix;
		$item['OFFERS_SELECTED'] = $intSelected;
		$item['OFFERS_PROPS_DISPLAY'] = $boolSkuDisplayProperties;
	}

	protected function editTemplateOfferProps(&$item)
	{
		$matrix = array();
		$newOffers = array();
		$double = array();
		$item['OFFERS_PROP'] = false;
		$item['SKU_TREE_VALUES'] = array();

		$iblockParams = $this->storage['IBLOCK_PARAMS'][$item['IBLOCK_ID']];
		$skuPropList = [];
		if (isset($this->arResult['SKU_PROPS'][$item['IBLOCK_ID']]))
		{
			$skuPropList = $this->arResult['SKU_PROPS'][$item['IBLOCK_ID']];
		}
		$skuPropIds = array_keys($skuPropList);
		$matrixFields = array_fill_keys($skuPropIds, false);

		foreach ($item['OFFERS'] as $offerKey => $offer)
		{
			$offer['ID'] = (int)$offer['ID'];

			if (isset($double[$offer['ID']]))
				continue;

			$row = array();
			foreach ($skuPropIds as $code)
			{
				$row[$code] = $this->getTemplatePropCell($code, $offer, $matrixFields, $skuPropList);
			}

			$matrix[$offerKey] = $row;

			\CIBlockPriceTools::clearProperties($offer['DISPLAY_PROPERTIES'], $iblockParams['OFFERS_TREE_PROPS']);
			\CIBlockPriceTools::setRatioMinPrice($offer, false);

			if ($this->arParams['SHOW_SLIDER'] === 'Y')
			{
				$this->editTemplateOfferSlider($offer, $item['IBLOCK_ID'], 5, true, $item['MORE_PHOTO']);
			}

			$offerPictures = \CIBlockPriceTools::getDoublePicturesForItem($offer, $iblockParams['OFFERS_ADD_PICT_PROP']);
			$offer['OWNER_PICT'] = empty($offerPictures['PICT']);
			$offer['PREVIEW_PICTURE'] = false;
			$offer['PREVIEW_PICTURE_SECOND'] = false;
			$offer['SECOND_PICT'] = true;

			if (!$offer['OWNER_PICT'])
			{
				if (empty($offerPictures['SECOND_PICT']))
				{
					$offerPictures['SECOND_PICT'] = $offerPictures['PICT'];
				}

				$offer['PREVIEW_PICTURE'] = $offerPictures['PICT'];
				$offer['PREVIEW_PICTURE_SECOND'] = $offerPictures['SECOND_PICT'];
			}

			if ($iblockParams['OFFERS_ADD_PICT_PROP'] != '' && isset($offer['DISPLAY_PROPERTIES'][$iblockParams['OFFERS_ADD_PICT_PROP']]))
			{
				unset($offer['DISPLAY_PROPERTIES'][$iblockParams['OFFERS_ADD_PICT_PROP']]);
			}
			$offer['TREE'] = [];

			$double[$offer['ID']] = true;
			$newOffers[$offerKey] = $offer;
		}

		$item['OFFERS'] = $newOffers;

		$usedFields = array();
		$sortFields = array();

		$matrixKeys = array_keys($matrix);
		foreach ($skuPropIds as $propCode)
		{
			$boolExist = $matrixFields[$propCode];
			foreach ($matrixKeys as $offerKey)
			{
				if ($boolExist)
				{
					$propId = $this->arResult['SKU_PROPS'][$item['IBLOCK_ID']][$propCode]['ID'];
					$value = $matrix[$offerKey][$propCode]['VALUE'];

					if (!isset($item['SKU_TREE_VALUES'][$propId]))
					{
						$item['SKU_TREE_VALUES'][$propId] = array();
					}

					$item['SKU_TREE_VALUES'][$propId][$value] = true;
					$item['OFFERS'][$offerKey]['TREE']['PROP_'.$propId] = $value;
					$item['OFFERS'][$offerKey]['SKU_SORT_'.$propCode] = $matrix[$offerKey][$propCode]['SORT'];
					$usedFields[$propCode] = true;
					$sortFields['SKU_SORT_'.$propCode] = SORT_NUMERIC;
					unset($value, $propId);
				}
				else
				{
					unset($matrix[$offerKey][$propCode]);
				}
			}
			unset($offerKey);
		}
		unset($propCode, $matrixKeys);

		$item['OFFERS_PROP'] = $usedFields;
		$item['OFFERS_PROP_CODES'] = !empty($usedFields) ? base64_encode(serialize(array_keys($usedFields))) : '';

		Collection::sortByColumn($item['OFFERS'], $sortFields);
	}

	/**
	 * @return void
	 */
	protected function initIblockPropertyFeatures()
	{
		if (!Iblock\Model\PropertyFeature::isEnabledFeatures())
			return;

		foreach (array_keys($this->storage['IBLOCK_PARAMS']) as $iblockId)
		{
			$this->loadDisplayPropertyCodes($iblockId);
			$this->loadBasketPropertyCodes($iblockId);
			$this->loadOfferTreePropertyCodes($iblockId);
		}
		unset($iblockId);
	}

	/**
	 * @param int $iblockId
	 * @return void
	 */
	protected function loadDisplayPropertyCodes($iblockId)
	{
		$list = Iblock\Model\PropertyFeature::getListPageShowPropertyCodes(
			$iblockId,
			['CODE' => 'Y']
		);
		if ($list === null)
			$list = [];
		$this->storage['IBLOCK_PARAMS'][$iblockId]['PROPERTY_CODE'] = $list;
		if ($this->useCatalog)
		{
			$list = Iblock\Model\PropertyFeature::getListPageShowPropertyCodes(
				$this->getOffersIblockId($iblockId),
				['CODE' => 'Y']
			);
			if ($list === null)
				$list = [];
			$this->storage['IBLOCK_PARAMS'][$iblockId]['OFFERS_PROPERTY_CODE'] = $list;
		}
		unset($list);
	}
}
