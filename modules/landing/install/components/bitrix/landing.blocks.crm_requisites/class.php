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
		$bankRequisiteId = $this->arParams['BANK_REQUISITE'] ?? null;
		$hideRequisitesData = $this->arParams['HIDE_CONTACTS_DATA'] ?? null;
		$hideContactsData = $this->arParams['HIDE_REQUISITES_DATA'] ?? null;
		$hideBankData = $this->arParams['HIDE_BANK_DATA'] ?? null;
		$isPrimaryIcon = $this->arParams['PRIMARY_ICON'] ?? 'N';
		if (is_string($requisiteId))
		{
			[$companyId, $requisiteId] = explode('_', $requisiteId);
		}
		if (is_string($bankRequisiteId))
		{
			[$companyId, $bankRequisiteId] = explode('_', $bankRequisiteId);
		}

		if ($requisiteId)
		{
			$requisiteData = $requisites[$companyId]['requisites'][$requisiteId] ?? null;
		}
		if ($bankRequisiteId)
		{
			$requisiteData = $requisites[$companyId]['bankRequisites'][$bankRequisiteId] ?? null;
		}
		if (empty($requisiteData['data']) && empty($requisiteData['bankData']))
		{
			$this->showError(Loc::getMessage('LNDNG_BLPHB_NOT_SELECT_REQUISITES'));
			return;
		}

		$this->arResult['COMMUNICATIONS'] = Crm::getCompanyCommunications($companyId);
		$this->arResult['REQUISITES'] = $requisiteData['data'];
		$this->arResult['BANK_REQUISITES'] = $requisiteData['bankData'];
		$this->arResult['HIDE_CONTACTS_DATA'] = $hideRequisitesData;
		$this->arResult['HIDE_REQUISITES_DATA'] = $hideContactsData;
		$this->arResult['HIDE_BANK_DATA'] = $hideBankData;
		$this->arResult['IS_PRIMARY_ICON'] = $isPrimaryIcon;

		$this->includeComponentTemplate();
	}
}
