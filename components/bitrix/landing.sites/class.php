<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Landing\Domain;
use Bitrix\Landing\Error;
use Bitrix\Landing\Mutator;
use \Bitrix\Landing\Site;
use \Bitrix\Landing\Landing;
use \Bitrix\Landing\Rights;
use \Bitrix\Landing\Manager;
use \Bitrix\Landing\Transfer;
use \Bitrix\Landing\Restriction;
use \Bitrix\Main\Context;
use \Bitrix\Main\ModuleManager;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Config\Option;

\CBitrixComponent::includeComponentClass('bitrix:landing.base');

class LandingSitesComponent extends LandingBaseComponent
{
	/**
	 * Count items per page.
	 */
	const COUNT_PER_PAGE = 12;

	/**
	 * Rights array of sites.
	 * @var array
	 */
	protected $rights = [];

	/**
	 * Gets additional access filter for sites.
	 * @param string $accessCode Access code for filter.
	 * @return array
	 */
	protected function getAdditionalAccessFilter(string $accessCode)
	{
		$filter = ['ID' => [-1]];
		$accessTypes = Rights::ACCESS_TYPES;

		if (Rights::isAdmin())
		{
			return [];
		}
		if (!isset($accessTypes[$accessCode]))
		{
			return [];
		}

		// get all sites first
		$ids = [];
		$res = Site::getList([
			'select' => [
				'ID'
			],
			'filter' => [
				'=TYPE' => $this->arParams['TYPE']
			]
		]);
		while ($row = $res->fetch())
		{
			$ids[] = $row['ID'];
		}

		// get rights for all sites
		$this->rights = Rights::getOperationsForSite($ids);
		foreach ($this->rights as $siteId => $rights)
		{
			if (in_array($accessTypes[$accessCode], $rights))
			{
				$filter['ID'][] = $siteId;
			}
		}

		return $filter;
	}

	/**
	 * Returns sites of main module.
	 * @return array
	 */
	protected function getSmnSites()
	{
		$sites = [];
		$filter = [];

		if (ModuleManager::isModuleInstalled('bitrix24'))
		{
			return $sites;
		}

		// prepare filter
		$disabledSiteIds = [SITE_ID];
		if (Loader::includeModule('extranet'))
		{
			$disabledSiteIds[] = \CExtranet::getExtranetSiteID();
		}
		$search = LandingFilterComponent::getFilterRaw(
			LandingFilterComponent::TYPE_SITE,
			$this->arParams['TYPE']
		);
		if ($search['DELETED'] == 'Y')
		{
			return $sites;
		}
		if (isset($search['FIND']) && trim($search['FIND']))
		{
			$filter['NAME'] = '%' . trim($search['FIND']) . '%';
		}
		$defaultServerName = Option::get('main', 'server_name');

		// get data
		$by = 'lid';
		$order = 'desc';
		$request = Context::getCurrent()->getRequest();
		$protocol = ($request->isHttps() ? 'https://' : 'http://');
		$res = \CSite::getList($by, $order, $filter);
		while ($row = $res->fetch())
		{
			if (in_array($row['LID'], $disabledSiteIds))
			{
				continue;
			}

			$row['DOMAIN_NAME'] = $defaultServerName;
			$row['PUBLIC_URL'] = $protocol . $defaultServerName . $row['DIR'];

			if ($row['SERVER_NAME'])
			{
				$row['DOMAIN_NAME'] = $row['SERVER_NAME'];
				$row['PUBLIC_URL'] = $protocol . $row['SERVER_NAME'];
				$row['PUBLIC_URL'] .= $row['DIR'];
			}
			elseif ($row['DOMAINS'])
			{
				$url = explode("\n", trim($row['DOMAINS']));
				if ($url)
				{
					$url = trim(array_shift($url));
				}
				if ($url)
				{
					$row['DOMAIN_NAME'] = $url;
					$row['PUBLIC_URL'] = $protocol . $url;
					$row['PUBLIC_URL'] .= $row['DIR'];
				}
			}

			$sites[$row['LID']] = $row;
		}

		return $sites;
	}

	/**
	 * Returns array of site ids with 'delete' locked.
	 * @param array $ids Site ids.
	 * @return array
	 */
	protected function getDeleteLocked(array $ids): array
	{
		$statuses = [];

		if ($ids)
		{
			$res = \Bitrix\Landing\Lock::getList([
				'select' => [
					'SITE_ID' => 'ENTITY_ID'
				],
				'filter' => [
					'ENTITY_ID' => $ids,
					'=ENTITY_TYPE' => \Bitrix\Landing\Lock::ENTITY_TYPE_SITE,
					'=LOCK_TYPE' => \Bitrix\Landing\Lock::LOCK_TYPE_DELETE
				]
			]);
			while ($row = $res->fetch())
			{
				$statuses[] = $row['SITE_ID'];
			}
		}

		return $statuses;
	}

	/**
	 * Base executable method.
	 * @return mixed
	 */
	public function executeComponent()
	{
		$init = $this->init();

		if ($init)
		{
			// params
			$puny = new \CBXPunycode;
			$deletedLTdays = Manager::getDeletedLT();
			$landingNull = Landing::createInstance(0);
			$pictureFromCloud = $this->previewFromCloud();
			$this->checkParam('TYPE', '');
			$this->checkParam('OVER_TITLE', '');
			$this->checkParam('TILE_MODE', 'list');
			$this->checkParam('PAGE_URL_SITE', '');
			$this->checkParam('PAGE_URL_SETTINGS', '');
			$this->checkParam('PAGE_URL_SITE_EDIT', '');
			$this->checkParam('PAGE_URL_SITE_DESI   GN', '');
			$this->checkParam('PAGE_URL_SITE_CONTACTS', '');
			$this->checkParam('PAGE_URL_LANDING_EDIT', '');
			$this->checkParam('PAGE_URL_SITE_DOMAIN_EDIT', '');
			$this->checkParam('PAGE_URL_SITE_DOMAIN_SWITCH', '');
			$this->checkParam('DRAFT_MODE', 'N');
			$this->checkParam('ACCESS_CODE', '');
			$this->checkParam('~AGREEMENT', []);
			$this->checkParam(
				'PAGE_URL_SITE_EXPORT',
				str_replace(-1, '#site_edit#', Transfer\Export\Site::getUrl($this->arParams['TYPE'], -1))
			);

			\Bitrix\Landing\Hook::setEditMode(true);

			\Bitrix\Landing\Site\Type::setScope(
				$this->arParams['TYPE']
			);

			// check agreements for Bitrix24
			if (Manager::isB24())
			{
				$this->arResult['AGREEMENT'] = $this->arParams['~AGREEMENT'];
			}
			else
			{
				$this->arResult['AGREEMENT'] = [];
			}

			\CBitrixComponent::includeComponentClass(
				'bitrix:landing.filter'
			);

			// template data
			$filter = LandingFilterComponent::getFilter(
				LandingFilterComponent::TYPE_SITE,
				$this->arParams['TYPE']
			);
			$filter['=SPECIAL'] = 'N';
			if (
				Manager::isExtendedSMN() &&
				$this->arParams['TYPE'] == 'STORE')
			{
				$filter['=TYPE'] = [
					$this->arParams['TYPE'],
					'SMN'
				];
			}
			else
			{
				$filter['=TYPE'] = $this->arParams['TYPE'];
			}
			if ($this->arParams['ACCESS_CODE'])
			{
				$filter[] = $this->getAdditionalAccessFilter($this->arParams['ACCESS_CODE']);
			}
			$this->arResult['EXPORT_DISABLED'] = Restriction\Manager::isAllowed('limit_sites_transfer') ? 'N' : 'Y';
			$isAllowedExportByTariff = null;
			if ($this->arResult['EXPORT_DISABLED'] !== 'Y')
			{
				$isAllowedExportByTariff = true;
				Bitrix\Landing\Restriction\Manager::enableFeatureTmp('limit_sites_access_permissions');
				if (
					Rights::hasAdditionalRight(Rights::ADDITIONAL_RIGHTS['unexportable'], null, false, true)
					&& !Rights::hasAdditionalRight(Rights::ADDITIONAL_RIGHTS['admin'], null, false, true)
				)
				{
					$this->arResult['EXPORT_DISABLED'] = 'Y';
				}
				else
				{
					$this->arResult['EXPORT_DISABLED'] = 'N';
				}
				Bitrix\Landing\Restriction\Manager::disableFeatureTmp('limit_sites_access_permissions');
			}
			$this->arResult['SMN_SITES'] = $this->getSmnSites();
			$this->arResult['IS_DELETED'] = LandingFilterComponent::isDeleted();
			$this->arResult['SITES'] = $this->getSites([
				'select' => [
					'*',
					'DOMAIN_NAME' => 'DOMAIN.DOMAIN',
					'DOMAIN_PROVIDER' => 'DOMAIN.PROVIDER',
					'DOMAIN_PREV' => 'DOMAIN.PREV_DOMAIN'
				],
				'filter' => $filter,
				'order' => [
						'DATE_MODIFY' => 'desc'
					],
				'navigation' => $this::COUNT_PER_PAGE
			]);
			$this->arResult['NAVIGATION'] = $this->getLastNavigation();
			$this->arResult['DELETE_LOCKED'] = $this->getDeleteLocked(
				array_keys($this->arResult['SITES'])
			);

			// detect preview of sites and set rights
			$rights = Rights::getOperationsForSite(
				array_merge(
					array_keys($this->arResult['SITES']),
					[0]
				)
			);
			$this->arResult['ACCESS_SITE_NEW'] = (
				Rights::hasAdditionalRight(Rights::ADDITIONAL_RIGHTS['create'])
				&&
				in_array(Rights::ACCESS_TYPES['edit'], $rights[0])
			) ? 'Y' : 'N';
			$ids = [];
			$unActiveIndexes = [];
			foreach ($this->arResult['SITES'] as &$item)
			{
				// collect un active sites with index pages
				if (
					$item['LANDING_ID_INDEX'] &&
					$item['ACTIVE'] != 'Y' &&
					$item['DELETED'] != 'Y'
				)
				{
					$unActiveIndexes[$item['ID']] = $item['LANDING_ID_INDEX'];
				}

				$ids[] = $item['ID'];
				$item['ACCESS_EDIT'] = 'Y';
				$item['ACCESS_SETTINGS'] = 'Y';
				$item['ACCESS_PUBLICATION'] = 'Y';
				$item['ACCESS_DELETE'] = 'Y';
				$item['ACCESS_SITE_NEW'] = $this->arResult['ACCESS_SITE_NEW'];
				if (isset($rights[$item['ID']]))
				{
					$currRights = $rights[$item['ID']];
					if (!in_array(Rights::ACCESS_TYPES['edit'], $currRights))
					{
						$item['ACCESS_EDIT'] = 'N';
					}
					if (!in_array(Rights::ACCESS_TYPES['sett'], $currRights))
					{
						$item['ACCESS_SETTINGS'] = 'N';
					}
					if (!in_array(Rights::ACCESS_TYPES['public'], $currRights))
					{
						$item['ACCESS_PUBLICATION'] = 'N';
					}
					if (!in_array(Rights::ACCESS_TYPES['delete'], $currRights))
					{
						$item['ACCESS_DELETE'] = 'N';
					}
				}

				//can export
				$item['ACCESS_EXPORT'] = 'Y';
				if ($isAllowedExportByTariff && $this->arResult['EXPORT_DISABLED'] === 'Y')
				{
					$item['ACCESS_EXPORT'] = 'N';
				}

				if (!$item['LANDING_ID_INDEX'])
				{
					$landing = $this->getLandings(array(
						'filter' => array(
							'SITE_ID' => $item['ID']
						),
						'order' => array(
							'ID' => 'ASC'
						),
						'limit' => 1
					));
					if ($landing)
					{
						$landing = array_pop($landing);
						$item['LANDING_ID_INDEX'] = $landing['ID'];
					}
				}
				if ($item['DELETED'] == 'Y')
				{
					$item['DATE_DELETED_DAYS'] = $deletedLTdays - intval((time() - $item['DATE_MODIFY']->getTimeStamp()) / 86400);
					$item['DELETE_FINISH'] = $item['DATE_DELETED_DAYS'] <= 0;
				}
				$item['DOMAIN_NAME'] = $puny->decode($item['DOMAIN_NAME']);
				$item['DOMAIN_B24_NAME'] = Domain::getBitrix24Subdomain($item['DOMAIN_NAME']);
				$item['EXPORT_URI'] = Transfer\Export\Site::getUrl(
					$this->arParams['TYPE'], $item['ID']
				);
			}
			unset($item);
			if ($ids)
			{
				$siteUrls = Site::getPublicUrl($ids);
				foreach ($this->arResult['SITES'] as &$item)
				{
					$item['PUBLIC_URL'] = '';
					$item['PREVIEW'] = '';
					if (isset($siteUrls[$item['ID']]))
					{
						$item['PUBLIC_URL'] = $siteUrls[$item['ID']];
					}
					if ($item['PUBLIC_URL'])
					{
						if ($item['DOMAIN_ID'] > 0 && $pictureFromCloud && $item['TYPE'] !== 'SMN')
						{
							$item['PREVIEW'] = $landingNull->getPreview($item['LANDING_ID_INDEX'], true);
							$item['CLOUD_PREVIEW'] = $item['PUBLIC_URL'] . '/preview.jpg';
						}
						else if ($item['LANDING_ID_INDEX'])
						{
							$item['PREVIEW'] = $landingNull->getPreview($item['LANDING_ID_INDEX'], true);
						}
						else
						{
							$item['PREVIEW'] = Manager::getUrlFromFile('/bitrix/images/landing/nopreview.jpg');
						}
					}

					$item['INDEX_EDIT_URI'] = str_replace(
						['#site_show#', '#landing_edit#'],
						[$item['ID'], $item['LANDING_ID_INDEX']],
						$this->arParams['~PAGE_URL_LANDING_VIEW']
					);
				}
				unset($siteUrls, $item);
			}
		}

		// check is need force verify site
		$forceVerifySiteId = (int)$this->request('force_verify_site_id');
		$verificationError = new Error();
		if (
			$forceVerifySiteId
			&& in_array($forceVerifySiteId, $ids ?? [])
			&& !Mutator::checkSiteVerification($forceVerifySiteId, $verificationError)
		)
		{
			$this->arResult['FORCE_VERIFY_SITE_ID'] = $forceVerifySiteId;
		}

		if (\Bitrix\Main\Loader::includeModule('bitrix24'))
		{
			$this->arResult['LICENSE'] = \CBitrix24::getLicenseType();
		}

		parent::executeComponent();

		return $this->arResult;
	}
}
