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

		if (!$isSearch)
		{
			$variables = \Bitrix\Landing\Landing::getVariables();
			$sectionCode = isset($variables['sef'][0]) ? $variables['sef'][0] : '';
			if ($sectionCode != '' && \Bitrix\Main\Loader::includeModule('iblock'))
			{
				$this->params['SECTION_ID'] = \CIBlockFindTools::getSectionIDByCodePath(
					$this->params['IBLOCK_ID'],
					$sectionCode
				);
			}
		}

		$this->params['SHOW_FILTER'] = $this->params['SECTION_ID'] || $editMode;
	}
}