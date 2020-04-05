<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Landing\Hook\Page\Settings;

class StoreCatalogFilterBlock extends \Bitrix\Landing\LandingBlock
{
	public function init(array $params = [])
	{
		$this->params = Settings::getDataForSite(
			$params['site_id']
		);

		$this->params['SITE_ID'] = $params['site_id'];
		$this->params['LANDING_ID'] = $params['landing_id'];

		$editMode = \Bitrix\Landing\Landing::getEditMode();
		$isSearch = isset($_REQUEST['q']) && trim($_REQUEST['q']);
		$sectionId = 0;

		if (!$isSearch)
		{
			$variables = \Bitrix\Landing\Landing::getVariables();
			$sectionCode = isset($variables['sef'][0]) ? $variables['sef'][0] : '';
			if (\Bitrix\Main\Loader::includeModule('iblock'))
			{
				$sectionId = \CIBlockFindTools::GetSectionIDByCodePath(
					$this->params['IBLOCK_ID'],
					$sectionCode
				);
			}
			if (!$sectionId)
			{
				$sectionId = $this->params['SECTION_ID'];
			}
		}

		$this->params['SECTION_ID'] = $sectionId;
		$this->params['SHOW_FILTER'] = $sectionId || $editMode;
	}
}