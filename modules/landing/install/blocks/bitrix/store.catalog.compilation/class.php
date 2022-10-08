<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Landing\Hook\Page\Settings;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Loader;
use Bitrix\Landing;
use Bitrix\Catalog;

class StoreCatalogCompilation extends \Bitrix\Landing\LandingBlock
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
		$variables = Landing\Landing::getVariables();
		if (isset($variables['sef'][0]))
		{
			$sectionCode = $variables['sef'][0];
			if (Loader::includeModule('iblock'))
			{
				$sectionId = \CIBlockFindTools::GetSectionIDByCodePath(
					$this->params['IBLOCK_ID'],
					$sectionCode
				);
			}
		}

		// check section id restricted
		$this->params['LANDING_SECTION_ID'] = $this->params['SECTION_ID'];
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
		$editMode = Landing\Landing::getEditMode();
		$this->params['EDIT_MODE'] = $editMode;
		$setStatus404 = $editMode ? 'N' : 'Y';
		$setTitle = $editMode || ($sectionId == $this->params['SECTION_ID']) ? 'N' : 'Y';
		$siteId = null;
		if ($editMode && isset($landing))
		{
			$siteId = $landing->getSmnSiteId();
		}
		if (!$siteId)
		{
			$siteId = Landing\Manager::getMainSiteId();
		}

		// check for show cart, personal section, and compare
		$showCart = false;
		$this->params['SHOW_PERSONAL_LINK'] = 'N';
		$this->params['ADD_TO_BASKET_ACTION'] = 'BUY';
		$this->params['SECTION_URL'] = '#system_catalog#SECTION_CODE_PATH#/';
		$this->params['DETAIL_URL'] = '#system_catalogitem/#ELEMENT_CODE#/';
		if (!$editMode && ModuleManager::isModuleInstalled('sale'))
		{
			$syspages = Landing\Syspage::get(
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
			if (isset($syspages['order']))
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
	}
}
