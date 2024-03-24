<?php
namespace Bitrix\Landing;

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Event;
use \Bitrix\Main\EventResult;

Loc::loadMessages(__FILE__);

class Site extends \Bitrix\Landing\Internals\BaseTable
{
	/**
	 * Internal class.
	 * @var string
	 */
	public static $internalClass = 'SiteTable';

	/**
	 * Site's ids that was pinged.
	 * @var array
	 */
	protected static $pings = [];

	/**
	 * Returns true if site exists and available.
	 * @param int $id Site id.
	 * @param bool $deleted And from recycle bin.
	 * @return bool
	 */
	public static function ping(int $id, bool $deleted = false): bool
	{
		if (array_key_exists($id, self::$pings))
		{
			return self::$pings[$id];
		}

		$filter = [
			'ID' => $id
		];
		if ($deleted)
		{
			$filter['=DELETED'] = ['Y', 'N'];
		}

		$check = Site::getList([
			'select' => [
				'ID'
			],
			'filter' => $filter
		]);
		self::$pings[$id] = (boolean) $check->fetch();

		return self::$pings[$id];
	}

	/**
	 * Removes sites id that was pinged.
	 * @param int $id Site id.
	 * @return void
	 */
	protected static function clearPing(int $id): void
	{
		if (array_key_exists($id, self::$pings))
		{
			unset(self::$pings[$id]);
		}
	}

	/**
	 * Get public url for site.
	 * @param int[]|int $id Site id or array of ids.
	 * @param boolean $full Return full site url with relative path.
	 * @param boolean $hostInclude Include host name in full path.
	 * @param boolean $previewForNotActive If true and site is not active, url will be with preview hash.
	 * @return string|array
	 */
	public static function getPublicUrl($id, bool $full = true, bool $hostInclude = true, bool $previewForNotActive = false)
	{
		$paths = [];
		$isB24 = Manager::isB24();

		$siteKeyCode = Site\Type::getKeyCode();
		$defaultPubPath = rtrim(Manager::getPublicationPath(), '/');
		$hostUrl = Domain::getHostUrl();
		$disableCloud = Manager::isCloudDisable();
		$res = self::getList(array(
			'select' => array(
				'DOMAIN_PROTOCOL' => 'DOMAIN.PROTOCOL',
				'DOMAIN_NAME' => 'DOMAIN.DOMAIN',
				'DOMAIN_ID',
				'SMN_SITE_ID',
				'CODE',
				'TYPE',
				'ACTIVE',
				'DELETED',
				'ID'
			),
			'filter' => array(
				'ID' => $id,
				'=DELETED' => ['Y', 'N'],
				'CHECK_PERMISSIONS' => 'N'
			)
		));
		while ($row = $res->fetch())
		{
			$pubPath = '';
			$isB24localVar = $isB24;

			if ($row['TYPE'] == 'SMN')
			{
				$isB24localVar = false;
			}

			if (!$isB24localVar || $disableCloud)
			{
				$pubPath = Manager::getPublicationPath(
					null,
					$row['SMN_SITE_ID'] ? $row['SMN_SITE_ID'] : null
				);
				$pubPath = rtrim($pubPath, '/');
			}

			if ($siteKeyCode == 'ID')
			{
				$row['CODE'] = '/' . $row['ID'] . '/';
			}

			// force https
			if (Manager::isHttps())
			{
				$row['DOMAIN_PROTOCOL'] = \Bitrix\Landing\Internals\DomainTable::PROTOCOL_HTTPS;
			}

			if ($row['DOMAIN_ID'])
			{
				$paths[$row['ID']] = ($hostInclude ? ($disableCloud ? $hostUrl : $row['DOMAIN_PROTOCOL'] . '://' . $row['DOMAIN_NAME']) : '') . $pubPath;
				if ($full)
				{
					if ($disableCloud && $isB24localVar)
					{
						$paths[$row['ID']] .= $row['CODE'];
					}
					else if (!$isB24localVar)
					{
						$paths[$row['ID']] .= '/';
					}
				}
			}
			else
			{
				$paths[$row['ID']] = ($hostInclude ? $hostUrl : '') . $defaultPubPath . ($full ? $row['CODE'] : '');
			}
			if ($previewForNotActive && ($row['ACTIVE'] === 'N' || $row['DELETED'] === 'Y'))
			{
				$paths[$row['ID']] .= 'preview/' . self::getPublicHash($row['ID'], $row['DOMAIN_NAME']) . '/';
			}
		}

		if (is_array($id))
		{
			return $paths;
		}
		else
		{
			return isset($paths[$id]) ? $paths[$id] : '';
		}
	}

	/**
	 * Get preview picture of the site's main page.
	 * @param int $siteId Site id.
	 * @return string
	 */
	public static function getPreview(int $siteId): string
	{
		$res = self::getList([
			'select' => [
				'LANDING_ID_INDEX'
			],
			'filter' => [
				'ID' => $siteId
			],
		]);
		if ($row = $res->fetch())
		{
			if ($row['LANDING_ID_INDEX'])
			{
				return Landing::createInstance(0)->getPreview($row['LANDING_ID_INDEX']);
			}
		}

		return Manager::getUrlFromFile('/bitrix/images/landing/nopreview.jpg');
	}

	/**
	 * Get hooks of Site.
	 * @param int $id Site id.
	 * @return array Array of Hook.
	 */
	public static function getHooks($id)
	{
		if (!Rights::hasAccessForSite($id, Rights::ACCESS_TYPES['read']))
		{
			return [];
		}

		return Hook::getForSite($id);
	}

	/**
	 * Get version of site for updater
	 * @param $siteId
	 * @return int
	 */
	public static function getVersion($siteId): int
	{
		static $versions;

		if (isset($versions[$siteId]))
		{
			return $versions[$siteId];
		}

		$resSite = self::getList([
			'select' => [
				'VERSION'
			],
			'filter' => [
				'=ID' => $siteId
			]
		]);
		if ($site = $resSite->fetch())
		{
			$versions[$siteId] = (int)$site['VERSION'];
		}
		else
		{
			$versions[$siteId] = 0;
		}

		return $versions[$siteId];
	}


	/**
	 * Get additional fields of Landing.
	 * @param int $id Landing id.
	 * @return array Array of Field.
	 */
	public static function getAdditionalFields($id)
	{
		$fields = array();

		// now we can get additional fields only from hooks
		foreach (self::getHooks($id) as $hook)
		{
			$fields += $hook->getPageFields();
		}

		return $fields;
	}

	/**
	 * Save additional fields for Site.
	 * @param int $id Site id.
	 * @param array $data Data array.
	 * @return void
	 */
	public static function saveAdditionalFields($id, array $data)
	{
		// now we can get additional fields only from hooks
		Hook::saveForSite($id, $data);
	}

	/**
	 * Get existed site types.
	 * @return array
	 */
	public static function getTypes()
	{
		static $types = null;

		if ($types !== null)
		{
			return $types;
		}

		$types = array(
			'PAGE' => Loc::getMessage('LANDING_TYPE_PAGE'),
			'STORE' => Loc::getMessage('LANDING_TYPE_STORE'),
			'SMN' => Loc::getMessage('LANDING_TYPE_SMN'),
			'KNOWLEDGE' => Loc::getMessage('LANDING_TYPE_KNOWLEDGE'),
			'GROUP' => Loc::getMessage('LANDING_TYPE_GROUP')
		);

		return $types;
	}

	/**
	 * Get default site type.
	 * @return string
	 */
	public static function getDefaultType()
	{
		return 'PAGE';
	}

	/**
	 * Delete site by id.
	 * @param int $id Site id.
	 * @param bool $pagesDelete Delete all pages before.
	 * @return \Bitrix\Main\Result
	 */
	public static function delete($id, $pagesDelete = false)
	{
		// first delete all pages if you want
		if ($pagesDelete)
		{
			$res = Landing::getList([
				'select' => [
					'ID', 'FOLDER_ID'
				],
				'filter' => [
					'SITE_ID' => $id,
					'=DELETED' => ['Y', 'N']
				]
			]);
			while ($row = $res->fetch())
			{
				if ($row['FOLDER_ID'])
				{
					Landing::update($row['ID'], [
						'FOLDER_ID' => 0
					]);
				}
				$resDel = Landing::delete($row['ID'], true);
				if (!$resDel->isSuccess())
				{
					return $resDel;
				}
			}
		}

		// delete site
		$result = parent::delete($id);
		self::clearPing($id);

		return $result;
	}

	/**
	 * Mark site as deleted.
	 * @param int $id Site id.
	 * @return \Bitrix\Main\Result
	 */
	public static function markDelete($id)
	{
		$event = new Event('landing', 'onBeforeSiteRecycle', array(
			'id' => $id,
			'delete' => 'Y'
		));
		$event->send();

		foreach ($event->getResults() as $result)
		{
			if ($result->getType() == EventResult::ERROR)
			{
				$return = new \Bitrix\Main\Result;
				foreach ($result->getErrors() as $error)
				{
					$return->addError(
						$error
					);
				}
				return $return;
			}
		}

		if (($currentScope = Site\Type::getCurrentScopeId()))
		{
			Agent::addUniqueAgent('clearRecycleScope', [$currentScope]);
		}

		$res = parent::update($id, array(
			'DELETED' => 'Y'
		));
		self::clearPing($id);

		return $res;
	}

	/**
	 * Mark site as restored.
	 * @param int $id Site id.
	 * @return \Bitrix\Main\Result
	 */
	public static function markUnDelete($id)
	{
		$event = new Event('landing', 'onBeforeSiteRecycle', array(
			'id' => $id,
			'delete' => 'N'
		));
		$event->send();

		foreach ($event->getResults() as $result)
		{
			if ($result->getType() == EventResult::ERROR)
			{
				$return = new \Bitrix\Main\Result;
				foreach ($result->getErrors() as $error)
				{
					$return->addError(
						$error
					);
				}
				return $return;
			}
		}

		$res = parent::update($id, array(
			'DELETED' => 'N'
		));
		self::clearPing($id);

		return $res;
	}

	/**
	 * Copy site without site's pages.
	 * @param int $siteId Site id.
	 * @return \Bitrix\Main\Result
	 */
	public static function copy($siteId)
	{
		$siteId = intval($siteId);
		$result = new \Bitrix\Main\Result;
		$error = new Error;

		$siteRow = Site::getList([
			'filter' => [
				'ID' => $siteId
			]
		])->fetch();

		if (!$siteRow)
		{
			$error->addError(
				'SITE_NOT_FOUND',
				Loc::getMessage('LANDING_COPY_ERROR_SITE_NOT_FOUND')
			);
		}
		else
		{
			$result = Site::add([
				'CODE' => $siteRow['CODE'],
				'ACTIVE' => 'N',
				'TITLE' => $siteRow['TITLE'],
				'XML_ID' => $siteRow['XML_ID'],
				'DESCRIPTION' => $siteRow['DESCRIPTION'],
				'TYPE' => $siteRow['TYPE'],
				'SMN_SITE_ID' => $siteRow['SMN_SITE_ID'],
				'LANG' => $siteRow['LANG']
			]);

			if ($result->isSuccess())
			{
				// copy hook data
				Hook::copySite(
					$siteId,
					$result->getId()
				);
				// copy files
				File::copySiteFiles(
					$siteId,
					$result->getId()
				);
			}
		}

		if (!$error->isEmpty())
		{
			$result->addError($error->getFirstError());
		}

		return $result;
	}

	/**
	 * Get full data for site with pages.
	 * @param int $siteForExport Site id.
	 * @param array $params Some params.
	 * @return array
	 */
	public static function fullExport($siteForExport, $params = array())
	{
		$version = 3;//used in demo/class.php
		$siteForExport = intval($siteForExport);
		$tplsXml = array();
		$export = array();
		$editMode = isset($params['edit_mode']) && $params['edit_mode'] === 'Y';

		Landing::setEditMode($editMode);
		Hook::setEditMode($editMode);

		if (!is_array($params))
		{
			$params = array();
		}
		$params['hooks_files'] = Hook::HOOKS_CODES_FILES;

		if (isset($params['scope']))
		{
			Site\Type::setScope($params['scope']);
		}

		// check params
		if (
			!isset($params['hooks_disable']) ||
			!is_array($params['hooks_disable'])
		)
		{
			$params['hooks_disable'] = array();
		}
		if (
			isset($params['code']) &&
			preg_match('/[^a-z0-9]/i', $params['code'])
		)
		{
			throw new \Bitrix\Main\Config\ConfigurationException(
				Loc::getMessage('LANDING_EXPORT_ERROR')
			);
		}
		// additional hooks for disable
		$params['hooks_disable'][] = 'B24BUTTON_CODE';
		$params['hooks_disable'][] = 'FAVICON_PICTURE';
		// get all templates
		$res = Template::getList(array(
			'select' => array(
				'ID', 'XML_ID'
			)
		));
		while ($row = $res->fetch())
		{
			$tplsXml[$row['ID']] = $row['XML_ID'];
		}
		// gets pages count
		$res = Landing::getList(array(
			'select' => array(
				'CNT'
			),
			'filter' => array(
				'SITE_ID' => $siteForExport
			),
			'runtime' => array(
				new \Bitrix\Main\Entity\ExpressionField(
					'CNT', 'COUNT(*)'
				)
			)
		));
		if ($pagesCount = $res->fetch())
		{
			$pagesCount = $pagesCount['CNT'];
		}
		else
		{
			return array();
		}
		// get all pages from the site
		$res = Landing::getList(array(
			'select' => array(
				'ID',
				'CODE',
				'RULE',
				'TITLE',
				'DESCRIPTION',
				'TPL_ID',
				'FOLDER_ID',
				'SITE_ID',
				'SITE_CODE' => 'SITE.CODE',
				'SITE_TYPE' => 'SITE.TYPE',
				'SITE_TPL_ID' => 'SITE.TPL_ID',
				'SITE_TITLE' => 'SITE.TITLE',
				'SITE_DESCRIPTION' => 'SITE.DESCRIPTION',
				'LANDING_ID_INDEX' => 'SITE.LANDING_ID_INDEX',
				'LANDING_ID_404' => 'SITE.LANDING_ID_404'
			),
			'filter' => array(
				'SITE_ID' => $siteForExport,
				//'=ACTIVE' => 'Y',
				//'=SITE.ACTIVE' => 'Y'
			),
			'order' => array(
				'ID' => 'asc'
			)
		));
		if (!($row = $res->fetch()))
		{
			return array();
		}
		do
		{
			if (empty($export))
			{
				$export = array(
					'charset' => SITE_CHARSET,
					'code' => isset($params['code'])
								? $params['code']
								: trim($row['SITE_CODE'], '/'),
					'code_mainpage' => '',
					'site_code' => $row['SITE_CODE'],
					'name' => isset($params['name'])
								? $params['name']
								: $row['SITE_TITLE'],
					'description' => isset($params['description'])
								? $params['description']
								: $row['SITE_DESCRIPTION'],
					'preview' => isset($params['preview'])
								? $params['preview']
								: '',
					'preview2x' => isset($params['preview2x'])
								? $params['preview2x']
								: '',
					'preview3x' => isset($params['preview3x'])
								? $params['preview3x']
								: '',
					'preview_url' => isset($params['preview_url'])
								? $params['preview_url']
								: '',
					'show_in_list' => 'Y',
					'type' => mb_strtolower($row['SITE_TYPE']),
					'version' => $version,
					'fields' => array(
						'ADDITIONAL_FIELDS' => array(),
						'TITLE' => isset($params['name'])
								? $params['name']
								: $row['SITE_TITLE'],
						'LANDING_ID_INDEX' => $row['LANDING_ID_INDEX'],
						'LANDING_ID_404' => $row['LANDING_ID_404']
					),
					'layout' => array(),
					'folders' => array(),
					'syspages' => array(),
					'items' => array()
				);
				// site tpl
				if ($row['SITE_TPL_ID'])
				{
					$export['layout'] = array(
						'code' => $tplsXml[$row['SITE_TPL_ID']],
						'ref' => TemplateRef::getForSite($row['SITE_ID'])
					);
				}
				// sys pages
				foreach (Syspage::get($siteForExport) as $syspage)
				{
					$export['syspages'][$syspage['TYPE']] = $syspage['LANDING_ID'];
				}
				// site hooks
				$hookFields = &$export['fields']['ADDITIONAL_FIELDS'];
				foreach (Hook::getForSite($row['SITE_ID']) as $hookCode => $hook)
				{
					if ($hookCode == 'SETTINGS')
					{
						continue;
					}
					foreach ($hook->getFields() as $fCode => $field)
					{
						$hookCodeFull = $hookCode . '_' . $fCode;
						if (!in_array($hookCodeFull, $params['hooks_disable']))
						{
							$hookFields[$hookCodeFull] = $field->getValue();
							if (!$hookFields[$hookCodeFull])
							{
								unset($hookFields[$hookCodeFull]);
							}
							else if (
								in_array($hookCodeFull, $params['hooks_files']) &&
								intval($hookFields[$hookCodeFull]) > 0
							)
							{
								$hookFields['~' . $hookCodeFull] = $hookFields[$hookCodeFull];
								$hookFields[$hookCodeFull] = File::getFilePath(
									$hookFields[$hookCodeFull]
								);
								if ($hookFields[$hookCodeFull])
								{
									$hookFields[$hookCodeFull] = Manager::getUrlFromFile(
										$hookFields[$hookCodeFull]
									);
								}
							}
						}
					}
				}
				unset($hookFields);
			}
			// fill one page
			$export['items'][$row['ID']] = array(
				'old_id' => $row['ID'],
				'code' => $pagesCount > 1
							? $export['code'] . '/' . $row['CODE']
							: $export['code'],
				'name' => (isset($params['name']) && $pagesCount == 1)
							? $params['name']
							: $row['TITLE'],
				'description' => (isset($params['description']) && $pagesCount == 1)
							? $params['description']
							: $row['DESCRIPTION'],
				'preview' => (isset($params['preview']) && $pagesCount == 1)
							? $params['preview']
							: '',
				'preview2x' => (isset($params['preview2x']) && $pagesCount == 1)
							? $params['preview2x']
							: '',
				'preview3x' => (isset($params['preview3x']) && $pagesCount == 1)
							? $params['preview3x']
							: '',
				'preview_url' => (isset($params['preview_url']) && $pagesCount == 1)
							? $params['preview_url']
							: '',
				'show_in_list' => ($pagesCount == 1) ? 'Y' : 'N',
				'type' => mb_strtolower($row['SITE_TYPE']),
				'version' => $version,
				'fields' => array(
					'TITLE' => (isset($params['name']) && $pagesCount == 1)
							? $params['name']
							: $row['TITLE'],
					'RULE' => $row['RULE'],
					'ADDITIONAL_FIELDS' => array(),
				),
				'layout' => $row['TPL_ID']
					? array(
						'code' => $tplsXml[$row['TPL_ID']],
						'ref' => TemplateRef::getForLanding($row['ID'])
					)
					: array(),
				'items' => array()
			);
			// special code for index page
			if (
				$pagesCount > 1 &&
				$row['LANDING_ID_INDEX'] == $row['ID']
			)
			{
				$export['code_mainpage'] = $row['CODE'];
			}
			// special pages
			if ($row['LANDING_ID_INDEX'] == $row['ID'])
			{
				$export['fields']['LANDING_ID_INDEX'] = $export['items'][$row['ID']]['code'];
			}
			if ($row['LANDING_ID_404'] == $row['ID'])
			{
				$export['fields']['LANDING_ID_404'] = $export['items'][$row['ID']]['code'];
			}
			// page hooks
			$hookFields = &$export['items'][$row['ID']]['fields']['ADDITIONAL_FIELDS'];
			foreach (Hook::getForLanding($row['ID']) as $hookCode => $hook)
			{
				if ($hookCode == 'SETTINGS')
				{
					continue;
				}
				foreach ($hook->getFields() as $fCode => $field)
				{
					$hookCodeFull = $hookCode . '_' . $fCode;
					if (!in_array($hookCodeFull, $params['hooks_disable']))
					{
						$hookFields[$hookCodeFull] = $field->getValue();
						if (!$hookFields[$hookCodeFull])
						{
							unset($hookFields[$hookCodeFull]);
						}
						else if (
							in_array($hookCodeFull, $params['hooks_files']) &&
							intval($hookFields[$hookCodeFull]) > 0
						)
						{
							$hookFields['~' . $hookCodeFull] = $hookFields[$hookCodeFull];
							$hookFields[$hookCodeFull] = File::getFilePath(
								$hookFields[$hookCodeFull]
							);
							if ($hookFields[$hookCodeFull])
							{
								$hookFields[$hookCodeFull] = Manager::getUrlFromFile(
									$hookFields[$hookCodeFull]
								);
							}
						}
					}
				}
			}
			unset($hookFields);
			// folders
			if ($row['FOLDER_ID'])
			{
				if (!isset($export['folders'][$row['FOLDER_ID']]))
				{
					$export['folders'][$row['FOLDER_ID']] = array();
				}
				$export['folders'][$row['FOLDER_ID']][] = $row['ID'];
			}
			// fill page with blocks
			$landing = Landing::createInstance($row['ID']);
			if ($landing->exist())
			{
				foreach ($landing->getBlocks() as $block)
				{
					if (!$block->isActive())
					{
						continue;
					}
					// repo blocks
					$repoBlock = array();
					if ($block->getRepoId())
					{
						$repoBlock = Repo::getBlock(
							$block->getRepoId()
						);
						if ($repoBlock)
						{
							$repoBlock = array(
								'app_code' => $repoBlock['block']['app_code'],
								'xml_id' => $repoBlock['block']['xml_id']
							);
						}
					}
					$exportBlock = $block->export();
					$exportItem = array(
						'old_id' => $block->getId(),
						'code' => $block->getCode(),
						'access' => $block->getAccess(),
						'anchor' => $block->getLocalAnchor(),
						'repo_block' => $repoBlock,
						'cards' => $exportBlock['cards'],
						'nodes' => $exportBlock['nodes'],
						'menu' => $exportBlock['menu'],
						'style' => array_map(static function ($style){
							if (is_array($style) && isset($style['classList']))
							{
								$style = $style['classList'];
							}
							return $style;
						}, $exportBlock['style']),
						'attrs' => $exportBlock['attrs'],
						'dynamic' => $exportBlock['dynamic']
					);
					foreach ($exportItem as $key => $item)
					{
						if (!$item)
						{
							unset($exportItem[$key]);
						}
					}
					$export['items'][$row['ID']]['items']['#block' . $block->getId()] = $exportItem;
				}
			}
		}
		while ($row = $res->fetch());

		if ($export['code_mainpage'])
		{
			$export['code'] = $export['code'] . '/' . $export['code_mainpage'];
		}
		unset($export['code_mainpage']);

		$pages = $export['items'];
		$export['items'] = array();

		// prepare for export tpls
		if (isset($export['layout']['ref']))
		{
			foreach ($export['layout']['ref'] as &$lid)
			{
				if (isset($pages[$lid]))
				{
					$lid = $pages[$lid]['code'];
				}
			}
			unset($lid);
		}
		// ... folders
		$nCount = 0;
		foreach ($export['folders'] as $folderId => $folderPages)
		{
			$export['folders']['n' . $nCount] = [];
			foreach ($folderPages as $pageId)
			{
				if (isset($pages[$pageId]))
				{
					$export['folders']['n' . $nCount][] = $pages[$pageId]['code'];
				}
			}
			unset($export['folders'][$folderId]);
			$nCount++;
		}
		foreach ($export['folders'] as $folderId => $folderPages)
		{
			$export['folders'][$folderPages[0]] = $folderPages;
			unset($export['folders'][$folderId]);
		}
		// ... syspages
		foreach ($export['syspages'] as &$lid)
		{
			if (isset($pages[$lid]))
			{
				$lid = $pages[$lid]['code'];
			}
		}
		unset($lid);
		// ... pages
		foreach ($pages as $page)
		{
			if (isset($page['layout']['ref']))
			{
				foreach ($page['layout']['ref'] as &$lid)
				{
					if (isset($pages[$lid]))
					{
						$lid = $pages[$lid]['code'];
					}
				}
				unset($lid);
			}
			$export['items'][$page['code']] = $page;
		}

		return $export;
	}

	/**
	 * Get md5 hash for site, using http host.
	 * @param int $id Site id.
	 * @param string $domain Domain name for this site.
	 * @return string
	 */
	public static function getPublicHash($id, $domain = null)
	{
		static $hashes = [];
		static $domains = [];

		if (isset($hashes[$id]))
		{
			return $hashes[$id];
		}

		$hash = [];

		if (Manager::isB24())
		{
			$hash[] = Manager::getHttpHost();
		}
		else
		{
			// detect domain
			if ($domain === null)
			{
				if (!isset($domains[$id]))
				{
					$domains[$id] = '';
					$res = self::getList(array(
						'select' => array(
							'SITE_DOMAIN' => 'DOMAIN.DOMAIN'
						),
						'filter' => array(
							'ID' => $id
						)
					));
					if ($row = $res->fetch())
					{
						$domains[$id] = $row['SITE_DOMAIN'];
					}
				}
				$domain = $domains[$id];
			}
			$hash[] = $domain;
		}

		if (Manager::isB24())
		{
			$hash[] = rtrim(Manager::getPublicationPath($id), '/');
		}
		else
		{
			$hash[] = $id;
			$hash[] = LICENSE_KEY;
		}

		$hashes[$id] = md5(implode('', $hash));

		return $hashes[$id];
	}

	/**
	 * Switch domains between two sites. Returns true on success.
	 * @param int $siteId1 First site id.
	 * @param int $siteId2 Second site id.
	 * @return bool
	 */
	public static function switchDomain(int $siteId1, int $siteId2): bool
	{
		return \Bitrix\Landing\Internals\SiteTable::switchDomain($siteId1, $siteId2);
	}

	/**
	 * Sets new random domain to site. Actual for Bitrix24 only.
	 * @param int $siteId Site id.
	 * @return bool
	 */
	public static function randomizeDomain(int $siteId): bool
	{
		return \Bitrix\Landing\Internals\SiteTable::randomizeDomain($siteId);
	}

	/**
	 * Creates site by template code.
	 * @param string $code Template code.
	 * @param string $type Site type.
	 * @param mixed $additional Data for landing.demo select.
	 * @return \Bitrix\Main\Entity\AddResult
	 */
	public static function addByTemplate(string $code, string $type, $additional = null): \Bitrix\Main\Entity\AddResult
	{
		$result = new \Bitrix\Main\Entity\AddResult;

		$componentName = 'bitrix:landing.demo';
		$className = \CBitrixComponent::includeComponentClass($componentName);
		/** @var \LandingSiteDemoComponent $demoCmp */
		$demoCmp = new $className;
		$demoCmp->initComponent($componentName);
		$demoCmp->arParams = [
			'TYPE' => $type,
			'DISABLE_REDIRECT' => 'Y'
		];
		$res = $demoCmp->actionSelect($code, $additional);

		if ($res)
		{
			$resSite = self::getList([
				'select' => [
					'ID'
				],
				'filter' => [
					'=TYPE' => $type
				],
				'order' => [
					'ID' => 'desc'
				]
			]);
			if ($rowSite = $resSite->fetch())
			{
				$result->setId($rowSite['ID']);
			}
		}
		else
		{
			foreach ($demoCmp->getErrors() as $code => $title)
			{
				$result->addError(new \Bitrix\Main\Error($title, $code));
			}
		}

		return $result;
	}

	/**
	 * Copies folders from one site to another without pages.
	 * @param int $fromSite Source site id.
	 * @param int $toSite Destination site id.
	 * @param array $folderMap External references old<>new ids.
	 * @return \Bitrix\Main\Result
	 */
	public static function copyFolders(int $fromSite, int $toSite, array &$folderMap = []): \Bitrix\Main\Result
	{
		$result = new \Bitrix\Main\Result();
		$fromSiteAccess = Site::ping($fromSite) && Rights::hasAccessForSite($fromSite, Rights::ACCESS_TYPES['read']);
		$toSiteAccess = Site::ping($toSite) && Rights::hasAccessForSite($toSite, Rights::ACCESS_TYPES['edit']);

		if ($fromSiteAccess && $toSiteAccess)
		{
			Landing::disableCheckUniqueAddress();

			$childrenExist = false;
			$res = Folder::getList([
				'filter' => [
					'SITE_ID' => $fromSite
				]
			]);
			while ($row = $res->fetch())
			{
				$oldId = $row['ID'];
				unset($row['ID']);

				if ($row['PARENT_ID'])
				{
					$childrenExist = true;
				}
				else
				{
					unset($row['PARENT_ID']);
				}

				if ($row['INDEX_ID'])
				{
					unset($row['INDEX_ID']);
				}

				$row['SITE_ID'] = $toSite;
				$resAdd = Folder::add($row);
				$folderMap[$oldId] = $resAdd->isSuccess() ? $resAdd->getId() : null;
			}

			// update child-parent
			if ($childrenExist)
			{
				$res = Folder::getList([
					'select' => [
						'ID', 'PARENT_ID'
					],
					'filter' => [
						'SITE_ID' => $toSite,
						'!PARENT_ID' => false
					]
				]);
				while ($row = $res->fetch())
				{
					Folder::update($row['ID'], [
						'PARENT_ID' => $folderMap[$row['PARENT_ID']] ?: null
					]);
				}
			}

			Landing::enableCheckUniqueAddress();
		}
		else
		{
			$result->addError(new \Bitrix\Main\Error(
				Loc::getMessage('LANDING_COPY_ERROR_SITE_NOT_FOUND'),
				'ACCESS_DENIED'
			));
		}
		return $result;
	}

	/**
	 * Creates folder into the site.
	 * @param int $siteId Site id.
	 * @param array $fields Folder's fields.
	 * @return \Bitrix\Main\Entity\AddResult
	 */
	public static function addFolder(int $siteId, array $fields): \Bitrix\Main\Entity\AddResult
	{
		if (self::ping($siteId) && Rights::hasAccessForSite($siteId, Rights::ACCESS_TYPES['edit']))
		{
			$fields['SITE_ID'] = $siteId;
			$result = Folder::add($fields);
		}
		else
		{
			$result = new \Bitrix\Main\Entity\AddResult;
			$result->addError(new \Bitrix\Main\Error(
				Loc::getMessage('LANDING_COPY_ERROR_SITE_NOT_FOUND'),
				'ACCESS_DENIED'
			));
		}
		return $result;
	}

	/**
	 * Updates folder of the site.
	 * @param int $siteId Site id.
	 * @param int $folderId Folder id.
	 * @param array $fields Folder's fields.
	 * @return \Bitrix\Main\Entity\UpdateResult
	 */
	public static function updateFolder(int $siteId, int $folderId, array $fields): \Bitrix\Main\Entity\UpdateResult
	{
		if (self::ping($siteId) && Rights::hasAccessForSite($siteId, Rights::ACCESS_TYPES['edit']))
		{
			$fields['SITE_ID'] = $siteId;
			$result = Folder::update($folderId, $fields);
		}
		else
		{
			$result = new \Bitrix\Main\Entity\UpdateResult;
			$result->addError(new \Bitrix\Main\Error(
				Loc::getMessage('LANDING_COPY_ERROR_SITE_NOT_FOUND'),
				'ACCESS_DENIED'
			));
		}

		return $result;
	}

	/**
	 * Public all folder's breadcrumb.
	 * @param int $folderId Folder id.
	 * @param bool $mark Publication / depublication.
	 * @return \Bitrix\Main\Result
	 */
	public static function publicationFolder(int $folderId, bool $mark = true): \Bitrix\Main\Result
	{
		$wasPublic = false;
		$result = new \Bitrix\Main\Result;
		$siteId = self::getFolder($folderId)['SITE_ID'] ?? null;

		if ($siteId && self::ping($siteId) && Rights::hasAccessForSite($siteId, Rights::ACCESS_TYPES['public']))
		{
			$wasPublic = true;
			$breadCrumbs = Folder::getBreadCrumbs($folderId);
			if (!$breadCrumbs)
			{
				$wasPublic = false;
			}
			$char = $mark ? 'Y' : 'N';
			foreach ($breadCrumbs as $folder)
			{
				if ($folder['ACTIVE'] === $char)
				{
					continue;
				}
				if ($folder['DELETED'] === 'Y')
				{
					$result->addError(new \Bitrix\Main\Error(
						Loc::getMessage('LANDING_COPY_ERROR_FOLDER_NOT_FOUND'),
						'ACCESS_DENIED'
					));
					return $result;
				}
				$res = Folder::update($folder['ID'], [
					'ACTIVE' => $char
				]);
				if (!$res->isSuccess())
				{
					$wasPublic = false;
				}
			}
		}

		if (!$wasPublic)
		{
			$result->addError(new \Bitrix\Main\Error(
				Loc::getMessage('LANDING_COPY_ERROR_FOLDER_NOT_FOUND'),
				'ACCESS_DENIED'
			));
		}

		return $result;
	}

	/**
	 * Moves folder.
	 * @param int $folderId Current folder id.
	 * @param int|null $toFolderId Destination folder id (or null for root folder of current folder's site).
	 * @param int|null $toSiteId Destination site id (if different from current).
	 * @return \Bitrix\Main\Result
	 */
	public static function moveFolder(int $folderId, ?int $toFolderId, ?int $toSiteId = null): \Bitrix\Main\Result
	{
		$returnError = function()
		{
			$result = new \Bitrix\Main\Result;
			$result->addError(new \Bitrix\Main\Error(
				Loc::getMessage('LANDING_COPY_ERROR_FOLDER_NOT_FOUND'),
				'ACCESS_DENIED'
			));
			return $result;
		};

		$folder = Folder::getList([
			'filter' => [
				'ID' => $folderId
			]
		])->fetch();
		if ($folder)
		{
			// move to another site
			if ($toSiteId && (int)$folder['SITE_ID'] !== $toSiteId)
			{
				// check access to another site
				$hasRightFrom = Rights::hasAccessForSite($folder['SITE_ID'], Rights::ACCESS_TYPES['delete']);
				$hasRightTo = Rights::hasAccessForSite($toSiteId, Rights::ACCESS_TYPES['edit']);
				if (!$hasRightFrom || !$hasRightTo)
				{
					return $returnError();
				}

				// check another site folder if specified
				$toFolder = null;
				if ($toFolderId)
				{
					$toFolder = Folder::getList([
						'filter' => [
							'ID' => $toFolderId,
							'SITE_ID' => $toSiteId
						]
					])->fetch();
					if (!$toFolder)
					{
						return $returnError();
					}
				}

				// move folder
				$res = Folder::update($folderId, [
					'SITE_ID' => $toSiteId,
					'PARENT_ID' => $toFolder['ID'] ?? null
				]);
				if ($res->isSuccess())
				{
					Folder::changeSiteIdRecursive($folderId, $toSiteId);
				}

				return $res;
			}

			$willBeRoot = !$toFolderId;

			// check destination folder
			$toFolder = null;
			if ($toFolderId)
			{
				$toFolder = Folder::getList([
					'filter' => [
						'ID' => $toFolderId
					]
				])->fetch();
				if (!$toFolder)
				{
					return $returnError();
				}
			}
			if (!$toFolder)
			{
				$toFolder = $folder;
			}
			// check restriction to move to itself
			if (!$willBeRoot)
			{
				$breadCrumbs = Folder::getBreadCrumbs($toFolder['ID'], $toFolder['SITE_ID']);
				for ($i = 0, $c = count($breadCrumbs); $i < $c; $i++)
				{
					if ($breadCrumbs[$i]['ID'] === $folder['ID'])
					{
						$result = new \Bitrix\Main\Result;
						$result->addError(new \Bitrix\Main\Error(
							Loc::getMessage('LANDING_COPY_ERROR_MOVE_RESTRICTION'),
							'MOVE_RESTRICTION'
						));
						return $result;
					}
				}
			}
			// check access and update then
			$hasRightFrom = Rights::hasAccessForSite($folder['SITE_ID'], Rights::ACCESS_TYPES['delete']);
			$hasRightTo = Rights::hasAccessForSite($toFolder['SITE_ID'], Rights::ACCESS_TYPES['edit']);
			if ($hasRightFrom && $hasRightTo)
			{
				return Folder::update($folderId, [
					'SITE_ID' => $toFolder['SITE_ID'],
					'PARENT_ID' => !$willBeRoot ? $toFolder['ID'] : null
				]);
			}
		}

		return $returnError();
	}

	/**
	 * Returns folder's list of site.
	 * @param int $siteId Site id.
	 * @param array $filter Folder's filter.
	 * @return array
	 */
	public static function getFolders(int $siteId, array $filter = []): array
	{
		if (!Rights::hasAccessForSite($siteId, Rights::ACCESS_TYPES['read']))
		{
			return [];
		}

		if (!isset($filter['DELETED']) && !isset($filter['=DELETED']))
		{
			$filter['=DELETED'] = 'N';
		}

		$folders = [];
		$filter['SITE_ID'] = $siteId;
		$res = Folder::getList([
			'filter' => $filter,
			'order' => [
				'DATE_MODIFY' => 'desc'
			]
		]);
		while ($row = $res->fetch())
		{
			$folders[$row['ID']] = $row;
		}
		return $folders;
	}

	/**
	 * Returns folder's info.
	 * @param int $folderId Folder id.
	 * @param string $accessLevel Access level to folder.
	 * @return array|null
	 */
	public static function getFolder(int $folderId, string $accessLevel = Rights::ACCESS_TYPES['read']): ?array
	{
		$folder = Folder::getList([
			'filter' => [
				'ID' => $folderId
			]
		])->fetch();

		if ($folder)
		{
			if (!Rights::hasAccessForSite($folder['SITE_ID'], $accessLevel))
			{
				return null;
			}
		}

		return is_array($folder) ? $folder : null;
	}

	/**
	 * Mark folder as deleted.
	 * @param int $id Folder id.
	 * @return \Bitrix\Main\Result
	 */
	public static function markFolderDelete(int $id): \Bitrix\Main\Result
	{
		$folder = self::getFolder($id);

		if (!$folder || !Rights::hasAccessForSite($folder['SITE_ID'], Rights::ACCESS_TYPES['delete']))
		{
			$result = new \Bitrix\Main\Entity\AddResult;
			$result->addError(new \Bitrix\Main\Error(
				Loc::getMessage('LANDING_COPY_ERROR_FOLDER_NOT_FOUND'),
				'ACCESS_DENIED'
			));
			return $result;
		}

		// disable delete if folder (or aby sub folders) contains area
		$res = Landing::getList([
			'select' => ['ID'],
			'filter' => [
				'FOLDER_ID' => [$id, ...Folder::getSubFolderIds($id)],
				'!==AREAS.ID' => null,
			],
		]);
		if ($res->fetch())
		{
			$result = new \Bitrix\Main\Entity\AddResult;
			$result->addError(new \Bitrix\Main\Error(
				Loc::getMessage('LANDING_DELETE_FOLDER_ERROR_CONTAINS_AREAS'),
				'FOLDER_CONTAINS_AREAS'
			));
			return $result;
		}

		$event = new Event('landing', 'onBeforeFolderRecycle', [
			'id' => $id,
			'delete' => 'Y'
		]);
		$event->send();

		foreach ($event->getResults() as $result)
		{
			if ($result->getType() == EventResult::ERROR)
			{
				$return = new \Bitrix\Main\Result;
				foreach ($result->getErrors() as $error)
				{
					$return->addError(
						$error
					);
				}
				return $return;
			}
		}

		if (($currentScope = Site\Type::getCurrentScopeId()))
		{
			Agent::addUniqueAgent('clearRecycleScope', [$currentScope]);
		}

		return Folder::update($id, [
			'DELETED' => 'Y'
		]);
	}

	/**
	 * Mark folder as restored.
	 * @param int $id Folder id.
	 * @return \Bitrix\Main\Result
	 */
	public static function markFolderUnDelete(int $id): \Bitrix\Main\Result
	{
		$folder = self::getFolder($id);

		if (!$folder || !Rights::hasAccessForSite($folder['SITE_ID'], Rights::ACCESS_TYPES['delete']))
		{
			$result = new \Bitrix\Main\Entity\AddResult;
			$result->addError(new \Bitrix\Main\Error(
				Loc::getMessage('LANDING_COPY_ERROR_FOLDER_NOT_FOUND'),
				'ACCESS_DENIED'
			));
			return $result;
		}

		$event = new Event('landing', 'onBeforeFolderRecycle', array(
			'id' => $id,
			'delete' => 'N'
		));
		$event->send();

		foreach ($event->getResults() as $result)
		{
			if ($result->getType() == EventResult::ERROR)
			{
				$return = new \Bitrix\Main\Result;
				foreach ($result->getErrors() as $error)
				{
					$return->addError(
						$error
					);
				}
				return $return;
			}
		}

		return Folder::update($id, array(
			'DELETED' => 'N'
		));
	}

	/**
	 * Tries to add page to the all menu on the site.
	 * Detects blocks with menu-manifests only.
	 * @param int $siteId Site id.
	 * @param array $data Landing data ([ID, TITLE]).
	 * @return void
	 */
	public static function addLandingToMenu(int $siteId, array $data): void
	{
		Landing::setEditMode();
		$res = Landing::getList([
			'select' => [
				'ID'
			],
			'filter' => [
				'SITE_ID' => $siteId,
				'!==AREAS.ID' => null
			],
		]);
		while ($row = $res->fetch())
		{
			$landing = Landing::createInstance($row['ID']);
			if ($landing->exist())
			{
				foreach ($landing->getBlocks() as $block)
				{
					$manifest = $block->getManifest();
					if (isset($manifest['menu']))
					{
						foreach ($manifest['menu'] as $menuSelector => $foo)
						{
							$block->updateNodes([
								$menuSelector => [
									[
										'text' => $data['TITLE'],
										'href' => '#landing' . $data['ID']
									]
								]
							], ['appendMenu' => true]);
							$block->save();
							break 2;
						}
					}
				}
			}
		}
	}

	/**
	 * Change modified user and date for the site.
	 * @param int $id Site id.
	 * @return void
	 */
	public static function touch(int $id): void
	{
		static $touched = [];

		if (isset($touched[$id]))
		{
			return;
		}

		$touched[$id] = true;

		self::update($id, [
			'TOUCH' => 'Y'
		]);
	}

	/**
	 * Makes site public.
	 * @param int $id Site id.
	 * @param bool $mark Mark.
	 * @return \Bitrix\Main\Result
	 */
	public static function publication(int $id, bool $mark = true): \Bitrix\Main\Result
	{
		$return = new \Bitrix\Main\Result;

		if ($mark)
		{
			$verificationError = new Error();
			if (!Mutator::checkSiteVerification($id, $verificationError))
			{
				$return->addError($verificationError->getFirstError());
				return $return;
			}
		}

		// work with pages
		$res = Landing::getList([
			'select' => [
				'ID', 'ACTIVE', 'PUBLIC'
			],
			'filter' => [
				'SITE_ID' => $id,
				[
					'LOGIC' => 'OR',
					['FOLDER_ID' => null],
					['!FOLDER_ID' => Folder::getFolderIdsForSite($id, ['=DELETED' => 'Y']) ?: [-1]]
				]
			]
		]);
		while ($row = $res->fetch())
		{
			if ($row['ACTIVE'] != 'Y')
			{
				$row['PUBLIC'] = 'N';
			}
			if ($row['PUBLIC'] == 'Y')
			{
				continue;
			}
			$landing = Landing::createInstance($row['ID'], [
				'skip_blocks' => true
			]);

			if ($mark)
			{
				$resPublication = $landing->publication();
			}
			else
			{
				$resPublication = $landing->unpublic();
			}

			if (!$resPublication)
			{
				if (!$landing->getError()->isEmpty())
				{
					$error = $landing->getError()->getFirstError();
					$return->addError(new \Bitrix\Main\Error(
						$error->getMessage(),
						$error->getCode()
					));
					return $return;
				}
			}
		}

		$res = Folder::getList([
			'select' => [
				'ID'
			],
			'filter' => [
				'SITE_ID' => $id,
				'=ACTIVE' => $mark ? 'N' : 'Y',
				'=DELETED' => 'N'
			]
		]);
		while ($row = $res->fetch())
		{
			Folder::update($row['ID'], [
				'ACTIVE' => $mark ? 'Y' : 'N'
			]);
		}

		return parent::update($id, [
			'ACTIVE' => $mark ? 'Y' : 'N'
		]);
	}

	/**
	 * Marks site unpublic.
	 * @param int $id Site id.
	 * @return \Bitrix\Main\Result
	 */
	public static function unpublic(int $id): \Bitrix\Main\Result
	{
		return self::publication($id, false);
	}

	/**
	 * Returns site id by template code.
	 * @param string $tplCode Template code.
	 * @return int|null
	 */
	public static function getSiteIdByTemplate(string $tplCode): ?int
	{
		$site = \Bitrix\Landing\Site::getList([
			'select' => [
				'ID'
			],
			'filter' => [
				'=TPL_CODE' => $tplCode
			],
			'order' => [
				'ID' => 'desc'
			]
		])->fetch();

		return $site['ID'] ?? null;
	}

	/**
	 * Event handler for check existing pages of main module's site.
	 * @param string $siteId Main site id.
	 * @return bool
	 */
	public static function onBeforeMainSiteDelete($siteId)
	{
		$res = Landing::getList(array(
			'select' => array(
				'ID'
			),
			'filter' => array(
				'=SITE.SMN_SITE_ID' => $siteId,
				'CHECK_PERMISSIONS' => 'N'
			)
		));

		if ($res->fetch())
		{
			Manager::getApplication()->throwException(
				Loc::getMessage('LANDING_CLB_ERROR_DELETE_SMN'),
				'ERROR_DELETE_SMN'
			);
			return false;
		}

		return true;
	}

	/**
	 * Event handler for delete pages of main module's site.
	 * @param string $siteId Main site id.
	 * @return void
	 */
	public static function onMainSiteDelete($siteId)
	{
		Rights::setOff();

		$realSiteId = null;
		// delete pages
		$res = Landing::getList(array(
			'select' => array(
				'ID', 'SITE_ID'
			),
			'filter' => array(
				'=SITE.SMN_SITE_ID' => $siteId,
				'=SITE.DELETED' => ['Y', 'N'],
				'=DELETED' => ['Y', 'N']
			)
		));
		while ($row = $res->fetch())
		{
			$realSiteId = $row['SITE_ID'];
			Landing::delete($row['ID'], true);
		}
		// detect site
		if (!$realSiteId)
		{
			$res = self::getList(array(
				'select' => array(
					'ID'
				),
				'filter' => array(
					'=SMN_SITE_ID' => $siteId,
					'=DELETED' => ['Y', 'N']
				)
			));
			if ($row = $res->fetch())
			{
				$realSiteId = $row['ID'];
			}
		}
		// and delete site
		if ($realSiteId)
		{
			self::delete($realSiteId);
		}

		Rights::setOn();
	}

	/**
	 * Change type for the site.
	 * @param int $id Site id.
	 * @param string $type Type.
	 * @return void
	 */
	public static function changeType(int $id, string $type): void
	{
		if (self::getTypes()[$type] ?? null)
		{
			parent::update($id, array(
				'TYPE' => $type
			));
		}
	}

	/**
	 * Change code for the site.
	 * @param int $id Site id.
	 * @param string $code Code.
	 * @return void
	 */
	public static function changeCode(int $id, string $code): void
	{
		parent::update($id, array(
			'CODE' => $code
		));
	}
}
