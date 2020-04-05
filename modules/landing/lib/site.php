<?php
namespace Bitrix\Landing;

use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Site extends \Bitrix\Landing\Internals\BaseTable
{
	/**
	 * Internal class.
	 * @var string
	 */
	public static $internalClass = 'SiteTable';

	/**
	 * Get public url for site.
	 * @param int $id Site id.
	 * @param boolean $full Return full site url with relative path.
	 * @return string
	 */
	public static function getPublicUrl($id, $full = true)
	{
		$res = self::getList(array(
			'select' => array(
				'DOMAIN_PROTOCOL' => 'DOMAIN.PROTOCOL',
				'DOMAIN_NAME' => 'DOMAIN.DOMAIN',
				'CODE',
				'SMN_SITE_ID'
			),
			'filter' => array(
				'ID' => $id,
				'=DELETED' => ['Y', 'N']
			)
		));
		if ($row = $res->fetch())
		{
			$bitrix24 = Manager::isB24();

			if (!$bitrix24)
			{
				$pubPath = Manager::getPublicationPath(
					null,
					$row['SMN_SITE_ID'] ? $row['SMN_SITE_ID'] : null
				);
				$pubPath = rtrim($pubPath, '/');
			}

			// force https
			if (Manager::isHttps())
			{
				$row['DOMAIN_PROTOCOL'] = \Bitrix\Landing\Internals\DomainTable::PROTOCOL_HTTPS;
			}

			return $row['DOMAIN_PROTOCOL'] . '://' .
					$row['DOMAIN_NAME'] .
					(!$bitrix24 ? $pubPath : '') .
					(!$bitrix24 && $full ? $row['CODE'] : '');
		}
		return '';
	}

	/**
	 * Get hooks of Site.
	 * @param int $id Site id.
	 * @return array Array of Hook.
	 */
	public static function getHooks($id)
	{
		return Hook::getForSite($id);
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
			'PREVIEW' => Loc::getMessage('LANDING_TYPE_PREVIEW')
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
	 * @return \Bitrix\Main\Result
	 */
	public static function delete($id)
	{
		$result = parent::delete($id);
		return $result;
	}

	/**
	 * Mark entity as deleted.
	 * @param int $id Entity id.
	 * @return \Bitrix\Main\Result
	 */
	public static function markDelete($id)
	{
		return parent::update($id, array(
			'DELETED' => 'Y'
		));
	}

	/**
	 * Mark entity as restored.
	 * @param int $id Entity id.
	 * @return \Bitrix\Main\Result
	 */
	public static function markUnDelete($id)
	{
		return parent::update($id, array(
			'DELETED' => 'N'
		));
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
		$tplsXml = array();
		$export = array();
		Landing::setEditMode(
			isset($params['edit_mode']) && $params['edit_mode'] === 'Y'
		);

		if (!is_array($params))
		{
			$params = array();
		}
		$params['hooks_files'] = array(
			'METAOG_IMAGE',
			'BACKGROUND_PICTURE'
		);

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
				'FOLDER',
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
					'code' => isset($params['code'])
								? $params['code']
								: trim($row['SITE_CODE'], '/'),
					'code_mainpage' => '',
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
					'type' => strtolower($row['SITE_TYPE']),
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
				foreach (Hook::getForSite($row['SITE_ID']) as $hCode => $hook)
				{
					if ($hCode == 'SETTINGS')
					{
						continue;
					}
					foreach ($hook->getFields() as $fCode => $field)
					{
						$hCodeFull = $hCode . '_' . $fCode;
						if (!in_array($hCodeFull, $params['hooks_disable']))
						{
							$hookFields[$hCodeFull] = $field->getValue();
							if (!$hookFields[$hCodeFull])
							{
								unset($hookFields[$hCodeFull]);
							}
							else if (
								in_array($hCodeFull, $params['hooks_files']) &&
								intval($hookFields[$hCodeFull]) > 0
							)
							{
								$hookFields[$hCodeFull] = File::getFilePath(
									$hookFields[$hCodeFull]
								);
								if ($hookFields[$hCodeFull])
								{
									$hookFields[$hCodeFull] = Manager::getUrlFromFile(
										$hookFields[$hCodeFull]
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
				'type' => strtolower($row['SITE_TYPE']),
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
			foreach (Hook::getForLanding($row['ID']) as $hCode => $hook)
			{
				if ($hCode == 'SETTINGS')
				{
					continue;
				}
				foreach ($hook->getFields() as $fCode => $field)
				{
					$hCodeFull = $hCode . '_' . $fCode;
					if (!in_array($hCodeFull, $params['hooks_disable']))
					{
						$hookFields[$hCodeFull] = $field->getValue();
						if (!$hookFields[$hCodeFull])
						{
							unset($hookFields[$hCodeFull]);
						}
						else if (
							in_array($hCodeFull, $params['hooks_files']) &&
							intval($hookFields[$hCodeFull]) > 0
						)
						{
							$hookFields[$hCodeFull] = File::getFilePath(
								$hookFields[$hCodeFull]
							);
							if ($hookFields[$hCodeFull])
							{
								$hookFields[$hCodeFull] = Manager::getUrlFromFile(
									$hookFields[$hCodeFull]
								);
							}
						}
					}
				}
			}
			unset($hookFields);
			// folders
			if ($row['FOLDER'] == 'Y')
			{
				if (!isset($export['folders'][$row['ID']]))
				{
					$export['folders'][$row['ID']] = array();
				}
			}
			elseif ($row['FOLDER_ID'])
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
						'style' => $exportBlock['style'],
						'attrs' => $exportBlock['attrs']
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
		$newFolders = array();
		foreach ($export['folders'] as $lid => $infolder)
		{
			if (isset($pages[$lid]))
			{
				$export['folders'][$pages[$lid]['code']] = array();
				foreach ($infolder as $flid)
				{
					if (isset($pages[$flid]))
					{
						$export['folders'][$pages[$lid]['code']][] = $pages[$flid]['code'];
					}
				}
			}
			unset($export['folders'][$lid]);
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
	 * @param string Domain name for this site.
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

		$hash[] = rtrim(Manager::getPublicationPath($id), '/');
		if (!Manager::isB24())
		{
			$hash[] = LICENSE_KEY;
		}
		$hashes[$id] = md5(implode('', $hash));

		return $hashes[$id];
	}

	/**
	 * Event handler for check existing pages of main module's site.
	 * @param string Main site id.
	 * @return bool
	 */
	public static function onBeforeMainSiteDelete($siteId)
	{
		$res = Landing::getList(array(
			'select' => array(
				'ID'
			),
			'filter' => array(
				'=SITE.SMN_SITE_ID' => $siteId
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
	 * @param string Main site id.
	 * @return void
	 */
	public static function onMainSiteDelete($siteId)
	{
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
	}
}