<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Landing\Connector\Crm;
use Bitrix\Landing\Landing;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

class LandingBlocksCrmRequisites extends \CBitrixComponent
{
	/**
	 * Local helper for showing error template.
	 * @param string $message Error message.
	 * @return void
	 */
	private function showError(string $message): void
	{
		if (!Landing::getEditMode())
		{
			return;
		}

		$this->arResult['ERROR'] = $message;
		$this->includeComponentTemplate('error');
	}

	/**
	 * Component's endpoint.
	 * @return void
	 */
	public function executeComponent(): void
	{
		if (!Loader::includeModule('crm'))
		{
			$this->showError(Loc::getMessage('LNDNG_BLPHB_CRM_NOT_INSTALLED'));
			return;
		}

		$requisites = Crm::getMyRequisites();
		if (empty($requisites))
		{
			$this->showError(Loc::getMessage('LNDNG_BLPHB_EMPTY_REQUISITES'));
			return;
		}

		$companyId = null;
		$requisiteId = $this->arParams['REQUISITE'] ?? null;
		if (is_string($requisiteId))
		{
			[$companyId, $requisiteId] = explode('_', $requisiteId);
		}

		$requisiteData = $requisites[$companyId]['requisites'][$requisiteId] ?? null;
		if (empty($requisiteData['data']))
		{
			$this->showError(Loc::getMessage('LNDNG_BLPHB_NOT_SELECT_REQUISITES'));
			return;
		}

		$this->arResult['COMMUNICATIONS'] = Crm::getCompanyCommunications($companyId);
		$this->arResult['REQUISITES'] = $requisiteData['data'];

		$this->includeComponentTemplate();
	}
}
