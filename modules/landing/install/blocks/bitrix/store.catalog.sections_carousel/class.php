<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Landing\Hook\Page\Settings;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Loader;
use Bitrix\Iblock;
use Bitrix\Catalog;

class StoreCatalogSectionsCarousel extends \Bitrix\Landing\LandingBlock
{
	protected $iblockIncluded;
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
	 * Method, which will be called once time.
	 * @param array Params array.
	 * @return void
	 */
	public function init(array $params = [])
	{
		$this->iblockIncluded = Loader::includeModule('iblock');
		$this->catalogIncluded = Loader::includeModule('catalog');
		$this->params = Settings::getDataForSite(
			$params['site_id']
		);

		$this->setCartPosition();
		$this->params['SITE_ID'] = $params['site_id'];
		$this->params['LANDING_ID'] = $params['landing_id'];

		$this->params['LANDING_SECTION_ID'] = ($this->params['SECTION_ID'] ?? 0);
		$this->params['ALLOW_SEO_DATA'] = 'Y';

		// calc variables
		$sectionId = 0;
		$sectionCode = '';
		$variables = \Bitrix\Landing\Landing::getVariables();
		if (isset($variables['sef'][0]))
		{
			$sectionCode = $variables['sef'][0];
			if ($this->iblockIncluded)
			{
				$sectionId = (int)\CIBlockFindTools::GetSectionIDByCodePath(
					$this->params['IBLOCK_ID'],
					$sectionCode
				);
			}
		}

		// check section id restricted
		if ($this->params['SECTION_ID'])
		{
			if ($this->iblockIncluded && $sectionId > 0)
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
		$this->params['EDIT_MODE'] = $editMode;
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

		$this->params['FILTER_NAME'] = 'carouselFilter';
		$this->params['CATALOG_FILTER_NAME'] = 'catalogCarouselFilter';
		$this->params['SECTIONS_FILTER_NAME'] = 'sectionCarouselFilter';

		$this->correctParams();
		$this->setElementCarouselFilter();
		$this->setSectionCarouselFilter();
	}

	/**
	 * Method for clear carousel filter. Page has contain some section blocks.
	 *
	 * @internal
	 *
	 * @param string $name
	 * @return void
	 */
	private function clearCarouselFilter(string $name): void
	{
		if (isset($GLOBALS[$name]))
		{
			$GLOBALS[$name] = [];
		}
	}

	private function setCarouselFilter(string $name, array $filter): void
	{
		if ($name === '')
		{
			return;
		}
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

	private function setElementCarouselFilter(): void
	{
		$filterName = $this->get('FILTER_NAME');
		$catalogFilterName = $this->get('CATALOG_FILTER_NAME');

		$listFilter = [];
		$catalogFilter = [];

		$currentElementId = $this->getCurrentElement();

		if (!empty($currentElementId))
		{
			$listFilter['!=ID'] = $currentElementId;
			$catalogFilter['!=ID'] = $currentElementId;
		}

		$currentSectionId = (int)$this->get('SECTION_ID');
		if (
			$currentSectionId > 0
			&& $currentSectionId !== (int)$this->get('LANDING_SECTION_ID')
		)
		{
			$catalogFilter['!=IBLOCK_SECTION_ID'] = $currentSectionId;
		}

		if ($this->catalogIncluded)
		{
			if (class_exists('\Bitrix\Catalog\Product\SystemField\ProductMapping'))
			{
				$listFilter = Catalog\Product\SystemField\ProductMapping::getExtendedFilterByArea(
					$listFilter,
					Catalog\Product\SystemField\ProductMapping::MAP_LANDING
				);
				$catalogFilter = Catalog\Product\SystemField\ProductMapping::getExtendedFilterByArea(
					$catalogFilter,
					Catalog\Product\SystemField\ProductMapping::MAP_LANDING
				);
			}
		}

		if (!empty($listFilter) && $filterName !== null)
		{
			$this->setCarouselFilter($filterName, $listFilter);
		}
		if (!empty($catalogFilter) && $catalogFilterName !== null)
		{
			$this->setCarouselFilter($catalogFilterName, $catalogFilter);
		}
	}

	private function setSectionCarouselFilter(): void
	{
		$filterName = $this->get('SECTIONS_FILTER_NAME');
		$this->clearCarouselFilter($filterName);
		$currentSectionId = (int)$this->get('SECTION_ID');
		if ($currentSectionId > 0)
		{
			$this->setCarouselFilter($filterName, ['!=ID' => $currentSectionId]);
		}
	}

	/**
	 * Returns element Id, if current page - is detail.
	 *
	 * @return int|null
	 */
	private function getCurrentElement(): ?int
	{
		$result = 0;
		if (isset($GLOBALS['CURRENT_PRODUCT_ID']))
		{
			$result = (int)$GLOBALS['CURRENT_PRODUCT_ID'];
		}

		return ($result > 0 ? $result : null);
	}

	private function correctParams(): void
	{
		if (!$this->iblockIncluded)
		{
			return;
		}
		$currentElementId = $this->getCurrentElement();
		if (!empty($currentElementId))
		{
			$this->params['ALLOW_SEO_DATA'] = 'N';
			$iterator = Iblock\ElementTable::getList([
				'select' => ['ID', 'IBLOCK_SECTION_ID'],
				'filter' => [
					'=ID' => $currentElementId,
					'=IBLOCK_ID' => (int)$this->get('IBLOCK_ID')
				]
			]);
			$row = $iterator->fetch();
			unset($iterator);
			if (!empty($row))
			{
				$sectionId = (int)$row['IBLOCK_SECTION_ID'];
				if ($sectionId > 0)
				{
					$this->params['SECTION_ID'] = $sectionId;
				}
				$this->params['SECTION_CODE'] = '';
			}
		}
	}
}
