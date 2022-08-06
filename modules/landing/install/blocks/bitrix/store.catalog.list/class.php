<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Landing\Hook\Page\Settings;
use Bitrix\Landing\Landing;
use Bitrix\Landing\Site;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Loader;
use Bitrix\Catalog;

class StoreCatalogListBlock extends \Bitrix\Landing\LandingBlock
{
	protected $catalogIncluded;

	/**
	 * Set cart position (top, left, ...).
	 * @return void
	 */
	protected function setCartPosition()
	{
		if (!isset($this->params['CART_POSITION']))
		{
			$this->params['CART_POSITION_HORIZONTAL'] = 'left';
			$this->params['CART_POSITION_VERTICAL'] = 'bottom';
		}
		else
		{
			switch ($this->params['CART_POSITION'])
			{
				case 'TC':
					{
						$this->params['CART_POSITION_HORIZONTAL'] = 'hcenter';
						$this->params['CART_POSITION_VERTICAL'] = 'top';
						break;
					}
				case 'TR':
					{
						$this->params['CART_POSITION_HORIZONTAL'] = 'right';
						$this->params['CART_POSITION_VERTICAL'] = 'top';
						break;
					}
				case 'CR':
					{
						$this->params['CART_POSITION_HORIZONTAL'] = 'right';
						$this->params['CART_POSITION_VERTICAL'] = 'vcenter';
						break;
					}
				case 'BR':
					{
						$this->params['CART_POSITION_HORIZONTAL'] = 'right';
						$this->params['CART_POSITION_VERTICAL'] = 'bottom';
						break;
					}
				case 'BC':
					{
						$this->params['CART_POSITION_HORIZONTAL'] = 'hcenter';
						$this->params['CART_POSITION_VERTICAL'] = 'bottom';
						break;
					}
				case 'BL':
					{
						$this->params['CART_POSITION_HORIZONTAL'] = 'left';
						$this->params['CART_POSITION_VERTICAL'] = 'bottom';
						break;
					}
				case 'CL':
					{
						$this->params['CART_POSITION_HORIZONTAL'] = 'left';
						$this->params['CART_POSITION_VERTICAL'] = 'vcenter';
						break;
					}
				case 'TL':
					{
						$this->params['CART_POSITION_HORIZONTAL'] = 'left';
						$this->params['CART_POSITION_VERTICAL'] = 'top';
						break;
					}
				default:
					{
						$this->params['CART_POSITION_HORIZONTAL'] = 'left';
						$this->params['CART_POSITION_VERTICAL'] = 'bottom';
					}
			}
		}
	}

	/**
	 * Redirects to site 404 page, if that page exists.
	 * @param int $siteId Site id.
	 * @return void
	 */
	private function redirectTo404ifExists(int $siteId): void
	{
		$res = Site::getList([
			'select' => [
				'LANDING_ID_404'
			],
			'filter' => [
				'=ID' => $siteId,
				'>LANDING_ID_404' => 0
			],
			'limit' => 1
		]);
		if ($row = $res->fetch())
		{
			localRedirect(Landing::createInstance($row['LANDING_ID_404'])->getPublicUrl(), true, '301 Moved Permanently');
		}
	}

	/**
	 * Method, which will be called once time.
	 * @param array Params array.
	 * @return void
	 */
	public function init(array $params = [])
	{
		$this->catalogIncluded = Loader::includeModule('catalog');

		$this->params = Settings::getDataForSite(
			$params['site_id']
		);

		$this->setCartPosition();
		$this->params['SITE_ID'] = $params['site_id'];
		$this->params['LANDING_ID'] = $params['landing_id'];

		// calc variables
		$sectionId = 0;
		$sectionCode = '';
		$variables = \Bitrix\Landing\Landing::getVariables();
		if (isset($variables['sef'][0]) && $variables['sef'][0] !== 'item')
		{
			$sectionCode = $variables['sef'][0];
			if (Loader::includeModule('iblock'))
			{
				$sectionId = \CIBlockFindTools::GetSectionIDByCodePath(
					$this->params['IBLOCK_ID'],
					$sectionCode
				);

				if (!$sectionId)
				{
					$this->redirectTo404ifExists($this->params['SITE_ID']);
				}
			}
		}

		// check section id restricted
		if ($this->params['SECTION_ID'])
		{
			if ($sectionId)
			{
				$allowed = false;
				$res = \CIBlockSection::getNavChain(
					$this->params['IBLOCK_ID'],
					$sectionId,
					[
						'ID'
					]
				);
				while ($row = $res->fetch())
				{
					if ($row['ID'] == $this->params['SECTION_ID'])
					{
						$allowed = true;
						break;
					}
				}
				if (!$allowed)
				{
					$sectionId = -1;
					$sectionCode = -1;
				}
			}
			else
			{
				$sectionId = $this->params['SECTION_ID'];
			}
		}

		// actions for edit mode
		$editMode = \Bitrix\Landing\Landing::getEditMode();
		$setStatus404 = $editMode ? 'N' : 'Y';
		$setTitle = $editMode || ($sectionId == $this->params['SECTION_ID']) ? 'N' : 'Y';
		$siteId = null;
		if ($editMode && isset($landing))
		{
			$siteId = $landing->getSmnSiteId();
		}
		if (!$siteId)
		{
			$siteId = \Bitrix\Landing\Manager::getMainSiteId();
		}

		// check for show cart, personal section, and compare
		$showCart = false;
		$this->params['SHOW_PERSONAL_LINK'] = 'N';
		$this->params['ADD_TO_BASKET_ACTION'] = 'BUY';
		$this->params['SECTION_URL'] = '#system_catalog#SECTION_CODE_PATH#/';
		$this->params['DETAIL_URL'] = '#system_catalogitem/#ELEMENT_CODE#/';
		if (!$editMode && ModuleManager::isModuleInstalled('sale'))
		{
			$syspages = \Bitrix\Landing\Syspage::get(
				$params['site_id']
			);
			if (
				isset($syspages['compare']) &&
				$this->params['DISPLAY_COMPARE'] != 'N'
			)
			{
				$this->params['DISPLAY_COMPARE'] = 'Y';
			}
			else
			{
				$this->params['DISPLAY_COMPARE'] = 'N';
			}
			if (isset($syspages['cart']))
			{
				$showCart = true;
				$this->params['ADD_TO_BASKET_ACTION'] = 'ADD';
			}
			if (isset($syspages['personal']))
			{
				$this->params['SHOW_PERSONAL_LINK'] = 'Y';
			}
			if (!isset($syspages['catalog']))
			{
				$this->params['SECTION_URL'] = $this->params['DETAIL_URL'] = '';
			}
		}
		else
		{
			$this->params['DISPLAY_COMPARE'] = 'N';
		}

		$this->params['HIDE_DETAIL_URL'] = ($this->params['DETAIL_URL'] == '') ? 'Y' : 'N';

		$this->params['SECTION_ID'] = $sectionId;
		$this->params['SECTION_CODE'] = $sectionCode;
		$this->params['SHOW_CART'] = $showCart;
		$this->params['SET_404'] = $setStatus404;
		$this->params['SET_TITLE'] = $setTitle;
		$this->params['SITE_ID'] = $siteId;
	}

	/**
	 *  Method, which executes just before block.
	 * @param \Bitrix\Landing\Block $block Block instance.
	 * @return void
	 */
	public function beforeView(\Bitrix\Landing\Block $block)
	{
		if (!defined('LANDING_TMP_CATALOG_SHOWED'))
		{
			define('LANDING_TMP_CATALOG_SHOWED', true);
			$this->params['FIRST_TIME'] = true;
		}
		else
		{
			$this->params['FIRST_TIME'] = false;
		}
		$this->params['ACTION_VARIABLE'] = 'action_' . $block->getId();

		$this->params['FILTER_NAME'] = 'arrFilter';

		$this->setElementListFilter();
	}

	private function setFilter(string $name, array $filter): void
	{
		$currentFilter = $GLOBALS[$name] ?? [];
		if (!is_array($currentFilter))
		{
			$currentFilter = [];
		}

		$GLOBALS[$name] = array_merge(
			$currentFilter,
			$filter
		);
	}

	private function setElementListFilter(): void
	{
		$filterName = $this->get('FILTER_NAME');
		if ($filterName === null || $filterName === '')
		{
			return;
		}

		$listFilter = [];

		if ($this->catalogIncluded)
		{
			if (class_exists('\Bitrix\Catalog\Product\SystemField\ProductMapping'))
			{
				$listFilter = Catalog\Product\SystemField\ProductMapping::getExtendedFilterByArea(
					$listFilter,
					Catalog\Product\SystemField\ProductMapping::MAP_LANDING
				);
			}
		}

		if (!empty($listFilter))
		{
			$this->setFilter($filterName, $listFilter);
		}
	}
}
