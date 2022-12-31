<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Landing\Hook;
use Bitrix\Landing\Landing;
use Bitrix\Landing\Site;
use Bitrix\Landing\Folder;
use Bitrix\Landing\Manager;
use Bitrix\Landing\Rights;
use Bitrix\Landing\Site\Type;
use Bitrix\Landing\TemplateRef;
use Bitrix\Landing\Internals\TemplateRefTable;
use Bitrix\Main\Entity;

\CBitrixComponent::includeComponentClass('bitrix:landing.base');

class LandingLandingsComponent extends LandingBaseComponent
{
	/**
	 * Count items per page.
	 */
	const COUNT_PER_PAGE = 23;

	/**
	 * Adds new folder.
	 * @param string $folderName Folder name.
	 * @return bool
	 */
	protected function actionAddFolder(string $folderName): bool
	{
		$siteId = $this->arParams['SITE_ID'];
		$folderId = $this->request($this->arParams['ACTION_FOLDER']);

		$res = Site::addFolder($siteId, [
			'PARENT_ID' => $folderId,
			'TITLE' => $folderName
		]);
		if (!$res->isSuccess() && $res->getErrors()[0]->getCode() === 'FOLDER_IS_NOT_UNIQUE')
		{
			return Site::addFolder($siteId, [
				'PARENT_ID' => $folderId,
				'TITLE' => $folderName,
				'CODE' => \CUtil::translit(
					trim($folderName),
					LANGUAGE_ID,
					[
						'replace_space' => '',
						'replace_other' => ''
					]
				) . '_' . rand(100, 999)
			])->isSuccess();
		}

		return $res->isSuccess();
	}

	/**
	 * Copy some landing.
	 * @param int $id Landing id.
	 * @param array $additional Additional params.
	 * @return boolean
	 */
	protected function actionCopy(int $id, array $additional = []): bool
	{
		$siteId = $additional['siteId'] ?? null;
		$folderId = $additional['folderId'] ?? null;
		if (mb_strpos($siteId, '_'))
		{
			[$siteId, $folderId] = explode('_', $siteId);
		}

		$landing = Landing::createInstance($id);
		$landing->copy($siteId, $folderId);
		$this->setErrors($landing->getError()->getErrors());

		return $landing->getError()->isEmpty();
	}

	/**
	 * Move the page.
	 * @param int $id Landing id.
	 * @param array $additional Additional params.
	 * @return boolean
	 */
	protected function actionMove(int $id, array $additional = []): bool
	{
		$siteId = $additional['siteId'] ?? null;
		$folderId = $additional['folderId'] ?? null;
		if (mb_strpos($siteId, '_'))
		{
			[$siteId, $folderId] = explode('_', $siteId);
		}
		if (!$siteId)
		{
			$this->addError('ACCESS_DENIED');
			return false;
		}

		$landing = Landing::createInstance($id);
		$landing->move($siteId, $folderId);
		$this->setErrors($landing->getError()->getErrors());

		return $landing->getError()->isEmpty();
	}

	/**
	 * Get previews from folder.
	 * @param int $siteId Site id.
	 * @param int $folderId Folder id.
	 * @return array
	 */
	protected function getFolderPreviews(int $siteId, int $folderId): array
	{
		$previews = [];
		$urls = [];

		$pages = $this->getLandings(array(
			'select' => array(
				'ID',
				'DOMAIN_ID' => 'SITE.DOMAIN_ID'
			),
			'filter' => array(
				'SITE_ID' => $siteId,
				'FOLDER_ID' => $folderId,
				'==AREAS.ID' => null
			),
			'order' => array(
				'ID' => 'DESC'
			),
			'limit' => 4
		));
		if ($pages)
		{
			$landing = Landing::createInstance(0);
			$urls = $landing->getPublicUrl(array_keys($pages));
		}

		foreach ($pages as $page)
		{
			$previews[$page['ID']] = $landing->getPreview(
				$page['ID'],
				$page['DOMAIN_ID'] == 0,
				$urls[$page['ID']] ?? null
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
	 * @deprecated
	 * @param array $sites Sites array.
	 * @return array
	 */
	protected function getTreeForCopy(array $sites = []): array
	{
		$sites = $this->getSites(['filter' => ['=SPECIAL' => 'N']]);
		$tree = [];

		// only for backward compatibility
		foreach ($sites as $site)
		{
			$tree[] = [
				'TITLE' => $site['TITLE'],
				'SITE_ID' => $site['ID'],
				'FOLDER_ID' => 0,
				'DEPTH' => 0
			];
		}

		return $tree;
	}

	/**
	 * Returns array of landing ids with 'delete' locked.
	 * @param array $ids Landing ids.
	 * @return array
	 */
	protected function getDeleteLocked(array $ids): array
	{
		$statuses = [];

		if ($ids)
		{
			$res = \Bitrix\Landing\Lock::getList([
				'select' => [
					'LANDING_ID' => 'ENTITY_ID'
				],
				'filter' => [
					'ENTITY_ID' => $ids,
					'=ENTITY_TYPE' => \Bitrix\Landing\Lock::ENTITY_TYPE_LANDING,
					'=LOCK_TYPE' => \Bitrix\Landing\Lock::LOCK_TYPE_DELETE
				]
			]);
			while ($row = $res->fetch())
			{
				$statuses[] = $row['LANDING_ID'];
			}
		}

		return $statuses;
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
			$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
			$deletedLTdays = Manager::getDeletedLT();
			$pictureFromCloud = $this->previewFromCloud();
			$landing = Landing::createInstance(0);

			$this->checkParam('SITE_ID', 0);
			$this->checkParam('TYPE', '');
			$this->checkParam('ACTION_FOLDER', 'folderId');
			$this->checkParam('TILE_MODE', 'edit');
			$this->checkParam('PAGE_URL_LANDING_EDIT', '');
			$this->checkParam('PAGE_URL_LANDING_DESIGN', '');
			$this->checkParam('PAGE_URL_FOLDER_EDIT', '');
			$this->checkParam('PAGE_URL_LANDING_VIEW', '');
			$this->checkParam('DRAFT_MODE', 'N');
			$this->checkParam('~AGREEMENT', []);

			$this->forceUpdateNewFolders($this->arParams['SITE_ID']);

			Hook::setEditMode(true);

			Type::setScope(
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
			$filter = LandingFilterComponent::getFilter(
				LandingFilterComponent::TYPE_LANDING,
				$this->arParams['TYPE']
			);

			$this->arResult['TREE'] = [];
			$this->arResult['IS_DELETED'] = LandingFilterComponent::isDeleted();
			$this->arResult['SITES'] = $sites = $this->getSites();
			$this->arResult['IS_INTRANET'] = $this->isIntranet();
			$siteId = $this->arParams['SITE_ID'];

			// types mismatch
			$availableType = [$this->arParams['TYPE']];
			if ($this->arParams['TYPE'] === 'STORE')
			{
				$availableType[] = 'SMN';
			}
			if (
				!isset($sites[$siteId]) ||
				$sites[$siteId]['SPECIAL'] === 'Y' ||
				!in_array($sites[$siteId]['TYPE'], $availableType)
			)
			{
				\localRedirect($this->getRealFile());
			}

			\Bitrix\Landing\Site\Version::update($siteId, $sites[$siteId]['VERSION']);

			// make filter & get folder
			$folderId = $this->arResult['FOLDER_ID'] = $request->get($this->arParams['ACTION_FOLDER']);
			$this->arResult['FOLDER'] = $folderId ? Site::getFolder($folderId) : null;
			$this->arResult['FOLDER_PATH'] = [];
			if ($this->arResult['FOLDER'])
			{
				$this->arResult['FOLDER_PATH'] = Folder::getBreadCrumbs($folderId, $siteId);
			}
			else
			{
				$folderId = $this->arResult['FOLDER_ID'] = null;
			}
			$filter['SITE_ID'] = $this->arParams['SITE_ID'];
			if ($folderId)
			{
				$filter['FOLDER_ID'] = $folderId;
				$this->arResult['TITLE'] = $this->arResult['FOLDER']['TITLE'];
			}
			else
			{
				$filter['FOLDER_ID'] = false;
				$this->arResult['TITLE'] = $sites[$siteId]['TITLE'];
			}
			$this->arResult['FOLDERS'] = Site::getFolders($siteId, [
				'TITLE' => $filter[0]['TITLE'] ?? '%',
				'PARENT_ID' => $folderId,
				'=DELETED' => $this->arResult['IS_DELETED'] ? 'Y' : 'N'
			]);
			$this->arResult['FOLDERS'] = array_values($this->arResult['FOLDERS']);
			foreach ($this->arResult['FOLDERS'] as &$folder)
			{
				$folder['FOLDER_PREVIEW'] = $this->getFolderPreviews($this->arParams['SITE_ID'], $folder['ID']);
			}
			unset($folder);

			// show sites' previews from cloud
			if ($this->arResult['IS_INTRANET'])
			{
				$pictureFromCloud = false;
			}
			else if (
				isset($sites[$this->arParams['SITE_ID']]) &&
				$sites[$this->arParams['SITE_ID']]['TYPE'] === 'SMN'
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
			$canViewUnActive = $access['EDIT'] === 'Y' || $access['PUBLICATION'] === 'Y';
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
					)
			));
			$this->arResult['DELETE_LOCKED'] = $this->getDeleteLocked(
				array_keys($this->arResult['LANDINGS'])
			);

			// base data
			$unActive = [];
			foreach ($this->arResult['LANDINGS'] as &$item)
			{
				// collect un active pages
				if (
					$item['ACTIVE'] !== 'Y' &&
					$item['DELETED'] !== 'Y'
				)
				{
					$unActive[] = $item['ID'];
				}
				else if (
					isset($sites[$item['SITE_ID']]) &&
					$sites[$item['SITE_ID']]['ACTIVE'] !== 'Y' &&
					$sites[$item['SITE_ID']]['DELETED'] !== 'Y'
				)
				{
					$unActive[] = $item['ID'];
				}
				$item['PUBLISHED'] = $item['ACTIVE'] === 'Y' && $item['DELETED'] === 'N';
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
				$item['PREVIEW'] = $landing->getPreview($item['ID'], true);
				if ($item['DELETED'] === 'Y')
				{
					$item['DATE_DELETED_DAYS'] = $deletedLTdays - intval((time() - $item['DATE_MODIFY']->getTimeStamp()) / 86400);
					$item['DELETE_FINISH'] = $item['DATE_DELETED_DAYS'] <= 0;
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
				$this->arResult['LANDINGS'][$id]['PUBLIC_URL'] = $url;
				if ($pictureFromCloud)
				{
					$this->arResult['LANDINGS'][$id]['CLOUD_PREVIEW'] = $url . 'preview.jpg';
				}
			}

			// redefine navigation (emulate from db)
			$this->arResult['LANDINGS'] = array_merge(
				array_values($this->arResult['FOLDERS']),
				array_values($this->arResult['LANDINGS'])
			);
			$this->lastNavigation = new \Bitrix\Main\UI\PageNavigation('nav');
			$this->lastNavigation->allowAllRecords(false)
								->setPageSize($this::COUNT_PER_PAGE)
								->initFromUri();
			$res = new \CDBResult;
			$res->initFromArray($this->arResult['LANDINGS']);
			$res->navStart(
				$this::COUNT_PER_PAGE,
				false,
				$this->lastNavigation->getCurrentPage()
			);
			$this->lastNavigation->setRecordCount(
				count($this->arResult['LANDINGS'])
			);
			$this->arResult['LANDINGS'] = [];
			while ($row = $res->fetch())
			{
				$this->arResult['LANDINGS'][] = $row;
			}
			$this->arResult['NAVIGATION'] = $this->lastNavigation;
		}

		parent::executeComponent();
	}
}
