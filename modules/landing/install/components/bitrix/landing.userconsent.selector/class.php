<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Landing\Manager;
use \Bitrix\Main\Loader;

\CBitrixComponent::includeComponentClass('bitrix:landing.base');

class LandingUserConsentSelector extends LandingBaseComponent
{
	/**
	 * Preparing params array.
	 * @return void
	 */
	protected function initParams()
	{
		$this->checkParam('ID', null);
		$this->checkParam('INPUT_NAME', 'AGREEMENT_ID');
		$this->checkParam('ACTION_REQUEST_URL', $this->getPath() . '/ajax.php');

		if (!Loader::includeModule('landing'))
		{
			return;
		}

		if (Manager::isB24())
		{
			$baseUri = '/settings/configs/userconsent/';
			$this->checkParam('PATH_TO_ADD', $baseUri . 'edit/0/');
			$this->checkParam('PATH_TO_EDIT', $baseUri . 'edit/#id#/');
			$this->checkParam(
				'PATH_TO_CONSENT_LIST',
		 		$baseUri . 'consents/#id#/?AGREEMENT_ID=#id#&apply_filter=Y'
			);
		}
		else
		{
			$baseUri = '/bitrix/admin/';
			$this->checkParam('PATH_TO_ADD', $baseUri . 'agreement_edit.php?ID=0&lang=' . LANGUAGE_ID);
			$this->checkParam('PATH_TO_EDIT', $baseUri . 'agreement_edit.php?ID=#id#&lang=' . LANGUAGE_ID);
			$this->checkParam(
				'PATH_TO_CONSENT_LIST',
				$baseUri . 'agreement_consents.php?AGREEMENT_ID=#id#&apply_filter=Y&lang=' . LANGUAGE_ID
			);
		}
	}

	/**
	 * Preparing result array.
	 * @return void
	 */
	protected function prepareResult()
	{
		$this->arResult['CAN_EDIT'] = $GLOBALS['USER']->IsAdmin() ||
									(
										IsModuleInstalled('bitrix24') &&
										$GLOBALS['USER']->CanDoOperation('bitrix24_config')
									);
	}

	/**
	 * Main executable method.
	 * @return void
	 */
	public function executeComponent()
	{
		$this->initParams();
		$this->prepareResult();
		$this->includeComponentTemplate();
	}
}