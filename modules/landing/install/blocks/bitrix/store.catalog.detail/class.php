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
	public function init(array $params = [])
	{
		$this->params = Settings::getDataForSite(
			$params['site_id']
		);

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
		if ($editMode && isset($landing))
		{
			$siteId = $landing->getSmnSiteId();
		}
		else
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
}