<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Landing\Manager;
use \Bitrix\Landing\Hook\Page\Settings;
use \Bitrix\Main\ModuleManager;

class StoreCatalogDetailBlock extends \Bitrix\Landing\LandingBlock
{
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
		$this->params = Settings::getDataForSite(
			$params['site_id']
		);

		$this->setCartPosition();
		$this->params['SITE_ID'] = $params['site_id'];
		$this->params['LANDING_ID'] = $params['landing_id'];

		// calc variables
		$variables = \Bitrix\Landing\Landing::getVariables();
		$sectionCode = isset($variables['sef'][0]) ? $variables['sef'][0] : '';
		$elementCode = isset($variables['sef'][1]) ? $variables['sef'][1] : '';

		// set default view (for edit mode)
		if (!$sectionCode && !$elementCode)
		{
			$res = \Bitrix\Iblock\ElementTable::getList(array(
				'select' => array(
					'CODE'
				),
				'filter' => array(
					'IBLOCK_ID' => $this->params['IBLOCK_ID']
				)
			));
			if ($row = $res->fetch())
			{
				$elementCode = $row['CODE'];
			}
		}

		// actions for edit mode
		$editMode = \Bitrix\Landing\Landing::getEditMode();
		$setTitle = $editMode ? 'N' : 'Y';
		$setStatus404 = $editMode ? 'N' : 'Y';
		$siteId = null;
		if ($editMode && isset($landing))
		{
			$siteId = $landing->getSmnSiteId();
		}
		if (!$siteId)
		{
			$siteId = Manager::getMainSiteId();
		}

		// check for show cart and compare
		$showCart = false;
		$this->params['SHOW_PERSONAL_LINK'] = 'N';
		if (!$editMode && ModuleManager::isModuleInstalled('sale'))
		{
			$syspages = \Bitrix\Landing\Syspage::get(
				$params['site_id'],
				true
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
			}
			if (isset($syspages['personal']))
			{
				$this->params['SHOW_PERSONAL_LINK'] = 'Y';
			}
		}
		else
		{
			$this->params['DISPLAY_COMPARE'] = 'N';
		}


		$this->params['ELEMENT_CODE'] = $elementCode;
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
		$this->params['ACTION_VARIABLE'] = 'action_' . $block->getId();
	}
}