<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Landing\Domain;
use \Bitrix\Landing\Site;
use \Bitrix\Landing\Landing;
use \Bitrix\Landing\Rights;
use \Bitrix\Landing\Manager;
use \Bitrix\Main\Engine\UrlManager;
use \Bitrix\Main\ModuleManager;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Config\Option;

\CBitrixComponent::includeComponentClass('bitrix:landing.base');

class LandingSitesComponent extends LandingBaseComponent
{
	/**
	 * Count items per page.
	 */
	const COUNT_PER_PAGE = 11;

	/**
	 * Rights array of sites.
	 * @var array
	 */
	protected $rights = [];

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
		if (!Loader::includeModule('extranet'))
		{
			return $sites;
		}

		// prepare filter
		$disabledSiteIds = [
			\CExtranet::getExtranetSiteID(),
			SITE_ID
		];
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
		$res = \CSite::getList(
			$by = 'lid',
			$order = 'desc',
			$filter
		);
		while ($row = $res->fetch())
		{
			if (in_array($row['LID'], $disabledSiteIds))
			{
				continue;
			}

			$row['PUBLIC_URL'] = '//' . $defaultServerName;

			if ($row['SERVER_NAME'])
			{
				$row['PUBLIC_URL'] = '//' . $row['SERVER_NAME'];
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
					$row['PUBLIC_URL'] = '//' . $url;
					$row['PUBLIC_URL'] .= $row['DIR'];
				}
			}

			$sites[$row['LID']] = $row;
		}

		return $sites;
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
			$b24 = Manager::isB24();
			$puny = new \CBXPunycode;
			$deletedLTdays = Manager::getDeletedLT();
			$landingNull = Landing::createInstance(0);
			$pictureFromCloud = $this->previewFromCloud();
			$this->checkParam('TYPE', '');
			$this->checkParam('OVER_TITLE', '');
			$this->checkParam('TILE_MODE', 'list');
			$this->checkParam('PAGE_URL_SITE', '');
			$this->checkParam('PAGE_URL_SITE_EDIT', '');
			$this->checkParam('PAGE_URL_LANDING_EDIT', '');
			$this->checkParam('DRAFT_MODE', 'N');
			$this->checkParam('~AGREEMENT', []);

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
			$this->arResult['SMN_SITES'] = $this->getSmnSites();
			$this->arResult['IS_DELETED'] = LandingFilterComponent::isDeleted();
			$this->arResult['SITES'] = $this->getSites([
				'select' => [
					'*',
					'DOMAIN_NAME' => 'DOMAIN.DOMAIN'
				],
				'filter' => $filter,
				'order' => $this->arResult['IS_DELETED']
					? [
						'DATE_MODIFY' => 'desc'
					]
					: [
						'ID' => 'desc'
					],
				'navigation' => $this::COUNT_PER_PAGE
			]);
			$this->arResult['NAVIGATION'] = $this->getLastNavigation();

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
					$item['DELETE_FINISH'] = $item['DATE_DELETED_DAYS'] <= 0;//@tmp
				}
				$item['DOMAIN_NAME'] = $puny->decode($item['DOMAIN_NAME']);
				$item['DOMAIN_B24_NAME'] = Domain::getBitrix24Subdomain($item['DOMAIN_NAME']);
				$item['EXPORT_URI'] = '#export';
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
						$item['PUBLIC_URL'] = $this->getTimestampUrl($siteUrls[$item['ID']]);
					}
					if ($item['PUBLIC_URL'])
					{
						if ($item['DOMAIN_ID'] > 0 && $pictureFromCloud && $item['TYPE'] != 'SMN')
						{
							$item['PREVIEW'] = $item['PUBLIC_URL'] . '/preview.jpg';
						}
						else if ($item['LANDING_ID_INDEX'])
						{
							$item['PREVIEW'] = $landingNull->getPreview($item['LANDING_ID_INDEX'], true);
						}
					}
				}
				unset($siteUrls, $item, $ids);
			}
		}

		parent::executeComponent();

		return $this->arResult;
	}
}
