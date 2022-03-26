<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Landing\Connector;
use Bitrix\Landing\Rights;

\CBitrixComponent::includeComponentClass('bitrix:landing.base.form');

class LandingSiteContactsComponent extends LandingBaseFormComponent
{
	/**
	 * Saves form's data.
	 * @return bool
	 */
	protected function actionSave(): bool
	{
		$access = Rights::hasAccessForSite(
			$this->arParams['SITE_ID'],
			Rights::ACCESS_TYPES['sett']
		);
		if (!$access)
		{
			return false;
		}

		Connector\Crm::setContacts($this->arParams['SITE_ID'], [
			'COMPANY' => $this->request('COMPANY'),
			'PHONE' => $this->request('PHONE')
		]);

		return true;
	}

	/**
	 * Base executable method.
	 * @return void
	 */
	public function executeComponent(): void
	{
		$init = $this->init();

		if ($init)
		{
			$this->checkParam('TYPE', '');
			$this->checkParam('SITE_ID', 0);

			\Bitrix\Landing\Site\Type::setScope(
				$this->arParams['TYPE']
			);

			$this->arResult['SITE'] = $this->getSites([
				'filter' => [
					'ID' => $this->arParams['SITE_ID']
				]
			]);

			$access = Rights::hasAccessForSite(
				$this->arParams['SITE_ID'],
				Rights::ACCESS_TYPES['sett']
			);

			if ($this->arResult['SITE'] && $access)
			{
				$this->arResult['CRM_CONTACTS'] = Connector\Crm::getContacts(
					$this->arParams['SITE_ID']
				);
				$this->arResult['CRM_CONTACTS_RAW'] = Connector\Crm::getContactsRaw();
			}
			else
			{
				$this->addError('ACCESS_DENIED', '', true);
			}
		}

		parent::executeComponent();
	}
}
