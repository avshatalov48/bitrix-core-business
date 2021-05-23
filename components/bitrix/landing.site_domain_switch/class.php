<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Landing\Rights;

\CBitrixComponent::includeComponentClass('bitrix:landing.base');
\CBitrixComponent::includeComponentClass('bitrix:landing.filter');

class LandingSiteDomainSwitchComponent extends LandingBaseComponent
{
	/**
	 * Switches domains between current site and selected.
	 * @param string $siteId Site id.
	 * @return bool
	 */
	protected function actionSwitch(string $siteId): bool
	{
		$siteId = intval($siteId);
		if (
			$this->checkAccess($siteId) &&
			$this->checkAccess($this->arParams['SITE_ID'])
		)
		{
			$result = \Bitrix\Landing\Site::switchDomain(
				$siteId,
				$this->arParams['SITE_ID']
			);
			if (!$result)
			{
				$this->addError('ACCESS_DENIED');
				return false;
			}
			else
			{
				\Bitrix\Landing\Site::randomizeDomain(
					$this->arParams['SITE_ID']
				);
			}
		}
		else
		{
			$this->addError('ACCESS_DENIED');
			return false;
		}

		return true;
	}

	/**
	 * Check access to settings edit.
	 * @param int $siteId Site id.
	 * @return bool
	 */
	protected function checkAccess(int $siteId): bool
	{
		if ($siteId)
		{
			return Rights::hasAccessForSite(
				$siteId,
				Rights::ACCESS_TYPES['sett']
			);
		}

		return false;
	}

	/**
	 * Base executable method.
	 * @return void
	 */
	public function executeComponent(): void
	{
		$init = $this->init();
		$this->checkParam('SITE_ID', 0);
		$this->checkParam('TYPE', '');
		$this->checkParam('MODE', '');

		if ($init)
		{
			\Bitrix\Landing\Site\Type::setScope(
				$this->arParams['TYPE']
			);
			$this->arParams['MODE'] = strtoupper($this->arParams['MODE']);
		}

		if ($init && !$this->checkAccess($this->arParams['SITE_ID']))
		{
			$this->addError('ACCESS_DENIED', '', true);
		}

		parent::executeComponent();
	}
}