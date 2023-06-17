<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Landing\Assets\PreProcessing\CrmContacts;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Landing;

/** @noinspection PhpUnused */

class LandingBlocksPhoneButtonComponent extends \CBitrixComponent
{
	/**
	 * Base executable method.
	 * @return void
	 * @noinspection PhpMissingParentCallCommonInspection
	 */
	public function executeComponent(): void
	{
		$this->arParams['TITLE'] = $this->arParams['~TITLE'] ?? '';
		$this->arParams['BUTTON_TITLE'] = $this->arParams['~BUTTON_TITLE'] ?? 'Contact';
		$this->arParams['BUTTON_CLASSES'] = $this->arParams['~BUTTON_CLASSES'] ?? '';

		$this->arResult['CONTACTS'] = CrmContacts::getContacts();

		$status = CrmContacts::getStatus();

		$sliderPath = '';
		if(Landing\Manager::isB24() && Loader::includeModule('salescenter'))
		{
			$sliderPath = \CComponentEngine::makeComponentPath('bitrix:salescenter.company.contacts');
			$sliderPath = getLocalPath('components' . $sliderPath . '/slider.php');
		}

		switch ($status)
		{
			case CrmContacts::STATUS_CRM_OK:
				$this->arResult['ALERTS'][] = Loc::getMessage(
					'LNDNG_BLPHB_ALERT_REQUISITE_HELP',
					[
						'#LINK1#' => '<a class="landing-trusted-link" 
							onclick="BX.PreventDefault(); BX.SidePanel.Instance.open(\'' . $sliderPath . '\');" href="">',
						'#LINK2#' => '</a>',
					]
				);
				break;

			case CrmContacts::STATUS_CRM_DEFAULT:
				$this->arResult['ERRORS'][] = [
					'title' => Loc::getMessage('LNDNG_BLPHB_ERROR_NO_REQUISITE_TITLE'),
					'text' => Loc::getMessage(
						'LNDNG_BLPHB_ERROR_NO_REQUISITE_TEXT',
						[
							'#LINK1#' => '<a class="landing-trusted-link" 
								onclick="BX.PreventDefault(); BX.SidePanel.Instance.open(\'' . $sliderPath . '\');" href="">',
							'#LINK2#' => '</a>',
						]
					),
				];
				break;

			case CrmContacts::STATUS_CRM_NO_SALESCENTER:
				$this->arResult['ERRORS'][] = [
					'title' => Loc::getMessage('LNDNG_BLPHB_ERROR_BLOCK_NOT_ACTIVE'),
					'text' => Loc::getMessage(
						'LNDNG_BLPHB_ERROR_CRM_NO_SALESCENTER_TEXT',
						[
							'#LINK1#' => '<a class="landing-trusted-link" 
								href="/bitrix/admin/module_admin.php?lang=' . LANGUAGE_ID . '">',
							'#LINK2#' => '</a>',
						]
					),
				];
				break;

				// todo: now not work in BUS, try later
			// case CrmContacts::STATUS_CONNECTOR_OK:
			// 	$this->arResult['ALERTS'][] = Loc::getMessage(
			// 		'LNDNG_BLPHB_ALERT_REQUISITE_HELP',
			// 		[
			// 			'#LINK1#' => '<a class="landing-trusted-link"
			// 				href="/bitrix/admin/b24connector_b24connector.php?lang=' . LANGUAGE_ID . '">',
			// 			'#LINK2#' => '</a>',
			// 		]
			// 	);
			// 	break;
			//
			// case CrmContacts::STATUS_CONNECTOR_OLD_CRM:
			// 	$this->arResult['ERRORS'][] = [
			// 		'title' => Loc::getMessage('LNDNG_BLPHB_ERROR_BLOCK_NOT_ACTIVE'),
			// 		'text' => Loc::getMessage('LNDNG_BLPHB_ERROR_CONNECTOR_OLD_CRM_TEXT'),
			// 	];
			// 	break;
			//
			// case CrmContacts::STATUS_CONNECTOR_DEFAULT:
			// 	$this->arResult['ERRORS'][] = [
			// 		'title' => Loc::getMessage('LNDNG_BLPHB_ERROR_NO_REQUISITE_TITLE'),
			// 		'text' => Loc::getMessage('LNDNG_BLPHB_ERROR_NO_REQUISITE_TEXT'),
			// 	];
			// 	break;
			//
			// case CrmContacts::STATUS_SMN_DEFAULT:
			// 	$this->arResult['ERRORS'][] = [
			// 		'title' => Loc::getMessage('LNDNG_BLPHB_ERROR_BLOCK_NOT_ACTIVE'),
			// 		'text' => Loc::getMessage('LNDNG_BLPHB_ERROR_SMN_DEFAULT_TEXT'),
			// 	];
			// 	break;

			default:
				break;
		}

		// todo: crm rest for bus contacts
		// todo: how to open slider

		$this->arParams['EDIT_MODE'] = Landing\Landing::getEditMode() ? 'Y' : 'N';

		$this->includeComponentTemplate();
	}
}