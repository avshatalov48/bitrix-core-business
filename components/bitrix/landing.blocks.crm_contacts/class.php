<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

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

		$this->arResult['CONTACTS'] = \Bitrix\Landing\Connector\Crm::getContactsRaw();

		if (empty($this->arResult['CONTACTS']))
		{
			$this->arResult['ERRORS'][] = [
				'title' => Loc::getMessage('LNDNG_BLPHB_ERROR_NO_REQUISITE_TITLE'),
				'text' => Loc::getMessage(
					'LNDNG_BLPHB_ERROR_NO_REQUISITE_TEXT',
					[
						'#LINK1#' => '',
						'#LINK2#' => '',
					]
				),
			];
		}
		else
		{
			$sliderPath = '';
			if (Landing\Manager::isB24() && Loader::includeModule('salescenter'))
			{
				$sliderPath = \CComponentEngine::makeComponentPath('bitrix:salescenter.company.contacts');
				$sliderPath = getLocalPath('components' . $sliderPath . '/slider.php');
			}

			$this->arResult['ALERTS'][] = Loc::getMessage(
				'LNDNG_BLPHB_ALERT_REQUISITE_HELP',
				[
					'#LINK1#' => '<a class="landing-trusted-link" 
						onclick="BX.PreventDefault(); BX.SidePanel.Instance.open(\'' . $sliderPath . '\');" href="">',
					'#LINK2#' => '</a>',
				]
			);
		}

		$this->arParams['EDIT_MODE'] = Landing\Landing::getEditMode() ? 'Y' : 'N';

		$this->includeComponentTemplate();
	}
}