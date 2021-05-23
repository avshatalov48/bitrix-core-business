<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Landing\Manager;
use \Bitrix\Landing\Site;
use \Bitrix\Landing\Domain;
use \Bitrix\Landing\Domain\Register;
use \Bitrix\Landing\Rights;
use \Bitrix\Landing\Restriction;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

\CBitrixComponent::includeComponentClass('bitrix:landing.base');

class LandingSiteDomainComponent extends LandingBaseComponent
{
	/**
	 * Sets new private domain to the site.
	 * @param string $param Domain name.
	 * @return bool
	 */
	protected function actionSavePrivate(string $param): bool
	{
		if ($this->checkAccess())
		{
			$res = Site::update($this->arParams['SITE_ID'], [
				'DOMAIN_ID' => $param
			]);
			if ($res->isSuccess())
			{
				return true;
			}
			else
			{
				$this->addErrorFromResult($res);
			}
		}

		return false;
	}

	/**
	 * Sets new Bitrix24 sub domain to the site.
	 * @param string $param Sub domain name.
	 * @return bool
	 */
	protected function actionSaveBitrix24(string $param): bool
	{
		return $this->actionSavePrivate($param . $this->arResult['POSTFIX']);
	}

	/**
	 * Sets new domain to the site by internal provider.
	 * @param string $param Domain name.
	 * @return bool
	 */
	protected function actionSaveProvider(string $param): bool
	{
		if (!Restriction\Manager::isAllowed('limit_free_domen'))
		{
			return false;
		}
		$registrator = \Bitrix\Landing\Domain\Register::getInstance();
		if ($registrator->registrationDomain($param, ['CNAME' => $this->arResult['CNAME']]))
		{
			$result = $this->actionSavePrivate($param);
			if ($result)
			{
				$res = Site::getList([
					'select' => [
						'DOMAIN_ID'
					],
					'filter' => [
						'ID' => $this->arParams['SITE_ID']
					]
				]);
				if ($row = $res->fetch())
				{
					Domain::update($row['DOMAIN_ID'], [
						'PROVIDER' => $registrator->getCode()
					]);
				}
				return $result;
			}
		}
		$this->addError(
			'ERROR_REGISTRATION',
			Loc::getMessage('LANDING_CMP_ERROR_REGISTRATION')
		);
		return false;
	}

	/**
	 * Switches domains between current site and another one.
	 * @param string $siteId Site id.
	 * @return bool
	 */
	protected function actionSwitch(string $siteId): bool
	{
		$siteId = intval($siteId);
		if ($this->checkAccess())
		{
			$access = Rights::hasAccessForSite(
				$siteId,
				Rights::ACCESS_TYPES['sett']
			);
			if ($access)
			{
				$result = Site::switchDomain(
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
						$siteId
					);
				}
			}
		}

		return true;
	}

	/**
	 * Returns domains with any provider.
	 * @return array
	 */
	protected function getDomainsWithProvider(): array
	{
		$puny = new \CBXPunycode;
		$domains = [];
		$res = Site::getList([
			'select' => [
				'ID', 'TITLE',
				'DOMAIN_NAME' => 'DOMAIN.DOMAIN'
			],
			'filter' => [
				'!=DOMAIN.PROVIDER' => null
			],
			'order' => [
				'ID' => 'desc'
			]
		]);
		while ($row = $res->fetch())
		{
			$row['DOMAIN_NAME'] = $puny->decode($row['DOMAIN_NAME']);
			$domains[] = $row;
		}

		return $domains;
	}

	/**
	 * Returns postfix for domain.
	 * @return string
	 */
	protected function getPostFix(): string
	{
		$zone = Manager::getZone();
		$postfix = '.bitrix24.site';

		if ($this->arParams['TYPE'] == 'STORE')
		{
			$postfix = ($zone == 'by')
				? '.bitrix24shop.by'
				: '.bitrix24.shop';
		}
		else if ($zone == 'by')
		{
			$postfix = '.bitrix24site.by';
		}
		else if ($zone == 'ua')
		{
			$postfix = '.bitrix24site.ua';
		}

		return $postfix;
	}

	/**
	 * Check access to settings edit.
	 * @return bool
	 */
	protected function checkAccess(): bool
	{
		static $access = null;

		if ($access !== null)
		{
			return $access;
		}

		if ($this->arParams['SITE_ID'])
		{
			$access = Rights::hasAccessForSite(
				$this->arParams['SITE_ID'],
				Rights::ACCESS_TYPES['sett']
			);
		}
		else
		{
			$access = false;
		}

		return $access;
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
		$this->checkParam('PAGE_URL_SITE_DOMAIN', '');
		$currentSite = [];
		$puny = new \CBXPunycode;

		if ($init)
		{
			\Bitrix\Landing\Site\Type::setScope(
				$this->arParams['TYPE']
			);
		}

		if ($init && !$this->checkAccess())
		{
			$init = false;
			$this->addError('ACCESS_DENIED', '', true);
		}
		else if ($init)
		{
			$currentSite = $this->getSites([
				'select' => [
					'DOMAIN_PROVIDER' => 'DOMAIN.PROVIDER',
					'DOMAIN_NAME' => 'DOMAIN.DOMAIN'
				],
				'filter' => [
					'ID' => $this->arParams['SITE_ID']
				]
			]);
			if (!$currentSite)
			{
				$init = false;
				$this->addError('ACCESS_DENIED', '', true);
			}
			else
			{
				$currentSite = array_shift($currentSite);
			}
		}

		if ($init)
		{
			$this->arResult['REGISTER'] = Register::getInstance();
			$this->arResult['TLD'] = $this->arResult['REGISTER']->getTld();
			$this->arResult['AGREEMENTS_URL'] = $this->arResult['REGISTER']->getAgreementURL();
			$this->arResult['DOMAIN_PROVIDER'] = $currentSite['DOMAIN_PROVIDER'];
			$this->arResult['~DOMAIN_NAME'] = $currentSite['DOMAIN_NAME'];
			$this->arResult['DOMAIN_NAME'] = $puny->decode($currentSite['DOMAIN_NAME']);
			$this->arResult['B24_DOMAIN_NAME'] = Domain::getBitrix24Subdomain($currentSite['DOMAIN_NAME']);
			$this->arResult['DOMAIN_ID'] = $currentSite['DOMAIN_ID'];
			$this->arResult['IP_FOR_DNS'] = $this->getIpForDNS();
			$this->arResult['POSTFIX'] = $this->getPostFix();
			$this->arResult['CNAME'] = 'lb' . $this->arResult['POSTFIX'] . '.';

			$this->arResult['FEATURE_FREE_AVAILABLE'] = Restriction\Manager::isAllowed(
				'limit_free_domen'
			);
			if (!$this->arResult['FEATURE_FREE_AVAILABLE'])
			{
				$this->arResult['PROVIDER_SITES'] = $this->getDomainsWithProvider();
			}
		}

		parent::executeComponent();
	}
}
