<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Landing\Landing;
use \Bitrix\Landing\Manager;
use \Bitrix\Landing\Hook\Page\Settings;
use \Bitrix\Main\ModuleManager;

class StoreCatalogListBlock extends \Bitrix\Landing\LandingBlock
{
	public function init(array $params = [])
	{
		$this->params = Settings::getDataForSite(
			$params['site_id']
		);

		$this->params['SITE_ID'] = $params['site_id'];
		$this->params['LANDING_ID'] = $params['landing_id'];

		// calc variables
		$sectionId = 0;
		$sectionCode = '';
		$variables = \Bitrix\Landing\Landing::getVariables();
		if (isset($variables['sef'][0]))
		{
			$sectionCode = $variables['sef'][0];
			if (\Bitrix\Main\Loader::includeModule('iblock'))
			{
				$sectionId = \CIBlockFindTools::GetSectionIDByCodePath(
					$this->params['IBLOCK_ID'],
					$sectionCode
				);
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
		if ($editMode && isset($landing))
		{
			$siteId = $landing->getSmnSiteId();
		}
		else
		{
			$siteId = \Bitrix\Landing\Manager::getMainSiteId();
		}

		// some parts can be showed only once
		if (defined('LANDING_TMP_CATALOG_SHOWED'))
		{
			$first = false;
		}
		else
		{
			$first = true;
			define('LANDING_TMP_CATALOG_SHOWED', true);
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
			if (isset($syspages['personal']) && Manager::getUserId())
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

		// @tmp bugfix for #110588
		if (
			!$editMode &&
			(
				$this->params['SECTION_URL'] ||
				$this->params['DETAIL_URL']
			)
		)
		{
			$this->params['SECTION_URL'] = '#system_catalog#SECTION_CODE_PATH#/';
			$this->params['DETAIL_URL'] = '#system_catalogitem/#ELEMENT_CODE#/';
			$syspages = \Bitrix\Landing\Syspage::get($params['site_id']);
			if (isset($syspages['catalog']))
			{
				$landing = Landing::createInstance(0);
				$catalogUrl = $landing->getPublicUrl(
					$syspages['catalog']['LANDING_ID']
				);
				if ($catalogUrl)
				{
					$this->params['SECTION_URL'] = str_replace(
						'#system_catalog',
						$catalogUrl,
						$this->params['SECTION_URL']
					);
					$this->params['DETAIL_URL'] = str_replace(
						'#system_catalog',
						$catalogUrl,
						$this->params['DETAIL_URL']
					);
				}
			}
		}

		$this->params['HIDE_DETAIL_URL'] = ($this->params['DETAIL_URL'] == '') ? 'Y' : 'N';
		$this->params['SECTION_ID'] = $sectionId;
		$this->params['SECTION_CODE'] = $sectionCode;
		$this->params['SHOW_CART'] = $showCart;
		$this->params['FIRST_TIME'] = $first;
		$this->params['SET_404'] = $setStatus404;
		$this->params['SET_TITLE'] = $setTitle;
		$this->params['SITE_ID'] = $siteId;
	}
}