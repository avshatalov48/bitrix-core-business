<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Landing\Site\Cookies;

\CBitrixComponent::includeComponentClass('bitrix:landing.base');

class LandingCookiesComponent extends LandingBaseComponent
{
	/**
	 * Returns landing site id.
	 * @return int
	 */
	protected function getLandingSiteId(): int
	{
		if (is_callable(['LandingPubComponent', 'getMainInstance']))
		{
			$instance = LandingPubComponent::getMainInstance();
			return $instance['SITE_ID'];
		}
		return 0;
	}

	/**
	 * Base executable method.
	 * @return void
	 */
	public function executeComponent(): void
	{
		$init = $this->init();
		$this->checkParam('SITE_ID', $this->getLandingSiteId());
		$this->checkParam('AGREEMENT_ID', 0);
		$this->checkParam('USE', '');
		$this->checkParam('COLOR_BG', '');
		$this->checkParam('COLOR_TEXT', '');
		$this->checkParam('POSITION', '');

		$this->arResult['AGREEMENTS_CUSTOM_CODES'] = [];
		$this->arResult['AGREEMENT'] = [];

		if ($init && $this->arParams['SITE_ID'])
		{
			$this->arResult['AGREEMENT'] = Cookies::getMainAgreement(
				$this->arParams['AGREEMENT_ID']
			);
			if (!$this->arResult['AGREEMENT'])
			{
				$init = false;
			}
			else
			{
				$availableAgreements = Cookies::getAgreements($this->arParams['SITE_ID']);
				$availableAgreements = array_filter($availableAgreements, function($item)
				{
					return $item['ACTIVE'] == 'Y';
				});
				$this->arResult['AVAILABLE_AGREEMENTS'] = array_keys($availableAgreements);
				if (!$this->arResult['AVAILABLE_AGREEMENTS'])
				{
					$this->arParams['USE'] = 'N';
				}
			}
		}

		if (!$init)
		{
			$this->arParams['USE'] = 'N';
		}

		parent::executeComponent();
	}
}