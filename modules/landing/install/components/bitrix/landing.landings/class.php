<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Landing\Landing;
use \Bitrix\Landing\Site;
use \Bitrix\Landing\Manager;
use \Bitrix\Landing\Rights;
use \Bitrix\Landing\TemplateRef;
use \Bitrix\Landing\Internals\TemplateRefTable;
use \Bitrix\Main\Entity;

\CBitrixComponent::includeComponentClass('bitrix:landing.base');

class LandingLandingsComponent extends LandingBaseComponent
{
	/**
	 * Count items per page.
	 */
	const COUNT_PER_PAGE = 23;

	/**
	 * Copy some landing.
	 * @param int $id Landing id.
	 * @param array $additional Additional params.
	 * @return boolean
	 */
	protected function actionCopy($id, $additional = array())
	{
		$siteId = isset($additional['siteId'])
					? $additional['siteId'] : null;
		$folderId = isset($additional['folderId'])
					? $additional['folderId'] : null;
		if(mb_strpos($siteId, '_'))
		{
			[$siteId, $folderId] = explode('_', $siteId);
		}

		$landing = Landing::createInstance($id);
		if ($landing->exist())
		{
			$landing->copy($siteId, $folderId);
		}
		$this->setErrors(
			$landing->getError()->getErrors()
		);

		return $landing->getError()->isEmpty();
	}

	/**
	 * Move some landing.
	 * @param int $id Landing id.
	 * @param array $additional Additional params.
	 * @return boolean
	 */
	protected function actionMove($id, $additional = array())
	{
		$siteId = isset($additional['siteId'])
				? $additional['siteId'] : null;
		$folderId = isset($additional['folderId'])
				? $additional['folderId'] : null;
		if(mb_strpos($siteId, '_'))
		{
			[$siteId, $folderId] = explode('_', $siteId);
		}

		if (!$siteId)
		{
			$this->addError('ACCESS_DENIED');
			return false;
		}

		$landing = Landing::createInstance($id);
		if ($landing->exist())
		{
			$rightsSite = Rights::getOperationsForSite(
				$siteId
			);
			if (!in_array(Rights::ACCESS_TYPES['edit'], $rightsSite))
			{
				$this->addError('ACCESS_DENIED');
				return false;
			}
			if (!$landing->canDelete())
			{
				$this->addError('ACCESS_DENIED');
				return false;
			}
			Landing::update($id, [
				'ACTIVE' => 'N',
				'PUBLIC' => 'N',
				'CODE' => $landing->getCode(),
				'SITE_ID' => $siteId,
				'FOLDER_ID' => $folderId
			]);
		}
		$this->setErrors(
			$landing->getError()->getErrors()
		);

		return $landing->getError()->isEmpty();
	}

	/**
	 * Get previews from folder.
	 * @param int $folderId Folder id.
	 * @return array
	 */
	protected function getFolderPreviews($folderId)
	{
		$previews = array();
		$pages = $this->getLandings(array(
			'select' => array(
				'ID'
			),
			'filter' => array(
				'FOLDER_ID' => $folderId
			),
			'order' => array(
				'ID' => 'DESC'
			),
			'limit' => 4
		));
		if ($pages)
		{
			$landing = Landing::createInstance(0);
		}
		foreach ($pages as $page)
		{
			$previews[$page['ID']] = $landing->getPreview(
				$page['ID']
			);
		}
		return $previews;
	}

	/**
	 * Returns true, if this site without external domain.
	 * @return bool
	 */
	protected function isIntranet()
	{
		return
			isset($this->arResult['SITES'][$this->arParams['SITE_ID']]) &&
			isset($this->arResult['SITES'][$this->arParams['SITE_ID']]['DOMAIN_ID']) &&
			$this->arResult['SITES'][$this->arParams['SITE_ID']]['DOMAIN_ID'] == '0';
	}

	/**
	 * Detect areas and requests some additional info.
	 * @return void
	 */
	protected function prepareAreas()
	{
		if (!isset($this->arResult['SITES'][$this->arParams['SITE_ID']]['TPL_ID']))
		{
			return;
		}

		$tplIds = [];
		$areas = [];
		$templates = $this->getTemplates();

		// get areas in current set
		$res = TemplateRefTable::getList([
			'select' => [
				'*'
			],
			'filter' => [
				'LANDING_ID' => array_keys($this->arResult['LANDINGS'])
			]
		]);
		while ($row = $res->fetch())
		{
			if (!isset($tplIds[$row['ENTITY_TYPE']]))
			{
				$tplIds[$row['ENTITY_TYPE']] = [];
			}
			$tplIds[$row['ENTITY_TYPE']][$row['ENTITY_ID']] = 0;
			$areas[] = $row;
		}

		// entities
		$entityTypes = [
			TemplateRef::ENTITY_TYPE_SITE,
			TemplateRef::ENTITY_TYPE_LANDING
		];
		foreach ($entityTypes as $entityType)
		{
			if (isset($tplIds[$entityType]))
			{
				$class = TemplateRef::resolveClassByType($entityType);
				if (!$class)
				{
					continue;
				}
				$res = $class::getList([
					'select' => [
						'ID', 'TPL_ID'
					],
					'filter' => [
						'ID' => array_keys($tplIds[$entityType]),
						'=DELETED' => ['Y', 'N']
					]
				]);
				while ($row = $res->fetch())
				{
					if (isset($templates[$row['TPL_ID']]))
					{
						$tplIds[$entityType][$row['ID']] = $row['TPL_ID'];
					}
				}
			}
		}

		// combine areas with entities
		foreach ($areas as $row)
		{
			$tplId = $tplIds[$row['ENTITY_TYPE']][$row['ENTITY_ID']];
			if ($tplId > 0)
			{
				$landingRow =& $this->arResult['LANDINGS'][$row['LANDING_ID']];
				$landingRow['IS_AREA'] = true;
				$landingRow['AREA_CODE'] = $templates[$tplId]['XML_ID'] . '_' . $row['AREA'];
			}
		}
	}

	/**
	 * Returns sites and folders array.
	 * @param array $sites Sites array.
	 * @return array
	 */
	protected function getTreeForCopy(array $sites): array
	{
		$tree = [];
		$folders = [];

		// get folders of sites
		$res = Landing::getList([
			'select' => [
				'ID', 'SITE_ID', 'TITLE'
			],
			'filter' => [
				'SITE_ID' => array_keys($sites),
				'=FOLDER' => 'Y'
			]
		]);
		while ($row = $res->fetch())
		{
			if (!isset($folders[$row['SITE_ID']]))
			{
				$folders[$row['SITE_ID']] = [];
			}
			$folders[$row['SITE_ID']][$row['ID']] = $row['TITLE'];;
		}

		// fill tree with sites and folders
		foreach ($sites as $site)
		{
			$tree[] = [
				'TITLE' => $site['TITLE'],
				'SITE_ID' => $site['ID'],
				'FOLDER_ID' => 0,
				'DEPTH' => 0
			];
			if (isset($folders[$site['ID']]))
			{
				foreach ($folders[$site['ID']] as $folderId => $folderTitle)
				{
					$tree[] = [
						'TITLE' => $folderTitle,
						'SITE_ID' => $site['ID'],
						'FOLDER_ID' => $folderId,
						'DEPTH' => 1
					];
				}
			}
		}

		return $tree;
	}

	/**
	 * Base executable method.
	 * @return void
	 */
	public function executeComponent()
	{
		$init = $this->init();

		if ($init)
		{
			$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
			$deletedLTdays = Manager::getDeletedLT();
			$pictureFromCloud = $this->previewFromCloud();
			$landing = Landing::createInstance(0);

			$this->checkParam('SITE_ID', 0);
			$this->checkParam('TYPE', '');
			$this->checkParam('ACTION_FOLDER', 'folderId');
			$this->checkParam('TILE_MODE', 'edit');
			$this->checkParam('PAGE_URL_LANDING_EDIT', '');
			$this->checkParam('PAGE_URL_LANDING_VIEW', '');
			$this->checkParam('DRAFT_MODE', 'N');
			$this->checkParam('~AGREEMENT', []);

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

			// make filter
			$siteId = $this->arParams['SITE_ID'];
			$filter = LandingFilterComponent::getFilter(
				LandingFilterComponent::TYPE_LANDING,
				$this->arParams['TYPE']
			);
			$filter['SITE_ID'] = $this->arParams['SITE_ID'];
			if ($request->offsetExists($this->arParams['ACTION_FOLDER']))
			{
				$filter[] = array(
					'LOGIC' => 'OR',
					'FOLDER_ID' => $request->get($this->arParams['ACTION_FOLDER']),
					'ID' => $request->get($this->arParams['ACTION_FOLDER'])
				);
			}
			else
			{
				$filter['FOLDER_ID'] = false;
			}

			$this->arResult['IS_DELETED'] = LandingFilterComponent::isDeleted();
			$this->arResult['SITES'] = $sites = $this->getSites();
			$this->arResult['TREE'] = $this->getTreeForCopy(array_reverse($sites, true));
			$this->arResult['IS_INTRANET'] = $this->isIntranet();

			// types mismatch
			$availableType = [$this->arParams['TYPE']];
			if ($this->arParams['TYPE'] == 'STORE')
			{
				$availableType[] = 'SMN';
			}
			if (
				!isset($sites[$siteId]) ||
				$sites[$siteId]['SPECIAL'] == 'Y' ||
				!in_array($sites[$siteId]['TYPE'], $availableType)
			)
			{
				\localRedirect($this->getRealFile());
			}

			if ($this->arResult['IS_INTRANET'])
			{
				$pictureFromCloud = false;
			}
			else if (
				isset($sites[$this->arParams['SITE_ID']]) &&
				$sites[$this->arParams['SITE_ID']]['TYPE'] == 'SMN'
			)
			{
				$pictureFromCloud = false;
			}

			// access
			$rights = Rights::getOperationsForSite(
				$this->arParams['SITE_ID']
			);
			$this->arResult['ACCESS_SITE'] = $access = [
				'EDIT' => in_array(Rights::ACCESS_TYPES['edit'], $rights) ? 'Y' : 'N',
				'SETTINGS' => in_array(Rights::ACCESS_TYPES['sett'], $rights) ? 'Y' : 'N',
				'PUBLICATION' => in_array(Rights::ACCESS_TYPES['public'], $rights) ? 'Y' : 'N',
				'DELETE' => in_array(Rights::ACCESS_TYPES['delete'], $rights) ? 'Y' : 'N'
			];

			// disable for un active pages for interface
			$canViewUnActive = $access['EDIT'] == 'Y' || $access['PUBLICATION'] == 'Y';
			if (!$canViewUnActive)
			{
				$filter['=ACTIVE'] = 'Y';
			}

			// get list
			$this->arResult['LANDINGS'] = $this->getLandings(array(
				'select' => array(
					'*',
					'DATE_MODIFY_UNIX',
					'DATE_PUBLIC_UNIX'
				),
				'filter' => $filter,
				'runtime' => array(
					new Entity\ExpressionField(
						'DATE_MODIFY_UNIX', 'UNIX_TIMESTAMP(%s)', array('DATE_MODIFY')
					),
					new Entity\ExpressionField(
						'DATE_PUBLIC_UNIX', 'UNIX_TIMESTAMP(%s)', array('DATE_PUBLIC')
					),
					new Entity\ExpressionField(
						'CHANGED', 'CASE WHEN %s > %s THEN 1 ELSE 0 END', ['DATE_MODIFY', 'DATE_PUBLIC']
					)
				),
				'order' => $this->arResult['IS_DELETED']
					? array(
						'DATE_MODIFY' => 'desc'
					)
					: array(
						'ID' => 'desc'
					),
				'navigation' => $this::COUNT_PER_PAGE
			));
			$this->arResult['NAVIGATION'] = $this->getLastNavigation();

			// base data
			$unActive = [];
			foreach ($this->arResult['LANDINGS'] as &$item)
			{
				// collect un active pages
				if (
					$item['ACTIVE'] != 'Y' &&
					$item['DELETED'] != 'Y'
				)
				{
					$unActive[] = $item['ID'];
				}
				else if (
					isset($sites[$item['SITE_ID']]) &&
					$sites[$item['SITE_ID']]['ACTIVE'] != 'Y' &&
					$sites[$item['SITE_ID']]['DELETED'] != 'Y'
				)
				{
					$unActive[] = $item['ID'];
				}
				// detect index page
				if (isset($sites[$item['SITE_ID']]))
				{
					$item['IS_HOMEPAGE'] = $item['ID'] == $sites[$item['SITE_ID']]['LANDING_ID_INDEX'];
				}
				else
				{
					$item['IS_HOMEPAGE'] = false;
				}
				if ($item['IS_HOMEPAGE'])
				{
					$item['SORT'] = PHP_INT_MAX;
				}
				else
				{
					$item['SORT'] = $item['ID'];
				}
				// preview, etc
				$item['IS_AREA'] = false;
				$item['AREA_CODE'] = '';
				$item['PUBLIC_URL'] = '';
				$item['WAS_MODIFIED'] = $item['DATE_MODIFY_UNIX'] > $item['DATE_PUBLIC_UNIX'] ? 'Y' : 'N';
				$item['PREVIEW'] = $pictureFromCloud ? '' : $landing->getPreview($item['ID'], true);
				if ($item['FOLDER'] == 'Y')
				{
					$item['FOLDER_PREVIEW'] = $this->getFolderPreviews($item['ID']);
				}
				if ($item['DELETED'] == 'Y')
				{
					$item['DATE_DELETED_DAYS'] = $deletedLTdays - intval((time() - $item['DATE_MODIFY']->getTimeStamp()) / 86400);
					$item['DELETE_FINISH'] = $item['DATE_DELETED_DAYS'] <= 0;//@tmp
				}
			}

			$this->prepareAreas();

			// sort by homepage additional
			uasort($this->arResult['LANDINGS'], function($a, $b)
			{
				return ($a['SORT'] < $b['SORT']) ? 1 : -1;
			});

			// public url
			$publicUrls = $landing->getPublicUrl(array_keys($this->arResult['LANDINGS']));
			foreach ($publicUrls as $id => $url)
			{
				$this->arResult['LANDINGS'][$id]['PUBLIC_URL'] = $this->getTimestampUrl($url);
				if ($pictureFromCloud)
				{
					$this->arResult['LANDINGS'][$id]['PREVIEW'] = $url . 'preview.jpg';
				}
			}
		}

		parent::executeComponent();
	}
}
