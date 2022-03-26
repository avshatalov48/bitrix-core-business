<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Landing\Site\Type;
use Bitrix\Landing\Connector\Ui\SelectorProvider;

\CBitrixComponent::includeComponentClass('bitrix:landing.base');

class LandingSelector extends LandingBaseComponent
{
	/**
	 * Base executable method.
	 * @return void
	 */
	public function executeComponent()
	{
		$this->checkParam('SITE_ID', 0);
		$this->checkParam('FOLDER_ID', 0);
		$this->checkParam('LANDING_ID', 0);
		$this->checkParam('TYPE', '');
		$this->checkParam('PAGE_URL_LANDING_VIEW', '');
		$this->checkParam('PAGE_URL_LANDING_ADD', '');
		$this->checkParam('PAGE_URL_FOLDER_ADD', '');

		if ($this->init())
		{
			Type::setScope(
				$this->arParams['TYPE']
			);

			$this->arResult['FOLDERS'] = SelectorProvider::getFolders(
				$this->arParams['SITE_ID']
			);
			$this->arResult['LANDINGS'] = SelectorProvider::getLandings(
				$this->arParams['SITE_ID']
			);

			parent::executeComponent();
		}
	}
}
