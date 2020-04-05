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
				'ID' => $id
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
	 * Get full data for site with pages.
	 * @param int $siteForExport Site id.
	 * @param array $params Some params.
	 * @return array
	 */
	public static function fullExport($siteForExport, $params = array())
	{
		$tplsXml = array();
		$export = array();
		Landing::setEditMode(true);

		if (
			!isset($params['hooks']) ||
			!is_array($params['hooks'])
		)
		{
			$params['hooks'] = array();
		}

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
				'SITE_DESCRIPTION' => 'SITE.DESCRIPTION'
			),
			'filter' => array(
				'SITE_ID' => $siteForExport
			),
			'order' => array(
				'ID' => 'asc'
			)
		));
		while ($row = $res->fetch())
		{
			if (empty($export))
			{
				$export = array(
					'code' => trim($row['SITE_CODE'], '/'),
					'name' => $row['SITE_TITLE'],
					'description' => $row['SITE_DESCRIPTION'],
					'type' => strtolower($row['SITE_TYPE']),
					'fields' => array(
						'ADDITIONAL_FIELDS' => array(
							'B24BUTTON_CODE' => '#B24BUTTON_CODE#'
						),
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
				if (!empty($params['hooks']))
				{
					$hookFields = &$export['fields']['ADDITIONAL_FIELDS'];
					foreach (Hook::getForSite($row['SITE_ID']) as $hCode => $hook)
					{
						if (in_array($hCode, $params['hooks']))
						{
							foreach ($hook->getFields() as $fCode => $field)
							{
								$hookFields[$hCode . '_' . $fCode] = $field->getValue();
								if (!$hookFields[$hCode . '_' . $fCode])
								{
									unset($hookFields[$hCode . '_' . $fCode]);
								}
							}
						}
					}
					unset($hookFields);
				}
			}
			// fill one page
			$export['items'][$row['ID']] = array(
				'code' => $export['code'] . '/' . $row['CODE'],
				'name' => $row['TITLE'],
				'description' => $row['DESCRIPTION'],
				'type' => strtolower($row['SITE_TYPE']),
				'version' => 2,
				'fields' => array(
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
			// page hooks
			if (!empty($params['hooks']))
			{
				$hookFields = &$export['items'][$row['ID']]['fields']['ADDITIONAL_FIELDS'];
				foreach (Hook::getForLanding($row['ID']) as $hCode => $hook)
				{
					if (in_array($hCode, $params['hooks']))
					{
						foreach ($hook->getFields() as $fCode => $field)
						{
							$hookFields[$hCode . '_' . $fCode] = $field->getValue();
							if (!$hookFields[$hCode . '_' . $fCode])
							{
								unset($hookFields[$hCode . '_' . $fCode]);
							}
						}
					}
				}
				unset($hookFields);
			}
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
					$cards = array();
					$nodes = array();
					$styles = array();
					$attrs = array();
					$doc = $block->getDom();
					$manifest = $block->getManifest();
					// get actual cards count
					if (isset($manifest['cards']))
					{
						foreach ($manifest['cards'] as $selector => $node)
						{
							$cards[$selector] = count($doc->querySelectorAll($selector));
						}
					}
					// get actual data from nodes
					if (isset($manifest['nodes']))
					{
						foreach ($manifest['nodes'] as $selector => $node)
						{
							if (in_array($node['type'], array('text', 'link', 'icon', 'img')))
							{
								$class = '\\Bitrix\\Landing\\Node\\' . $node['type'];
								$nodes[$selector] = $class::getNode($block, $selector);
							}
						}
					}
					// get actual css from nodes
					if (isset($manifest['style']['nodes']))
					{
						foreach ($manifest['style']['nodes'] as $selector => $node)
						{
							$styles[$selector] = array();
							$resultList = $doc->querySelectorAll($selector);
							foreach ($resultList as $pos => $result)
							{
								if ($result->getNodeType() == $result::ELEMENT_NODE)
								{
									$styles[$selector][$pos] = $result->getClassName();
								}
							}
							$styles[$selector] = array_unique($styles[$selector]);
							if (empty($styles[$selector]))
							{
								unset($styles[$selector]);
							}
						}
					}
					// get actual css from block wrapper
					if (isset($manifest['style']['block']))
					{
						$resultList = array(
							array_pop($doc->getChildNodesArray())
						);
						foreach ($resultList as $pos => $result)
						{
							if ($result && $result->getNodeType() == $result::ELEMENT_NODE)
							{
								$styles['#wrapper'][$pos] = $result->getClassName();
							}
						}
					}
					// get actual attrs from code
					if (isset($manifest['attrs']))
					{
						foreach ($manifest['attrs'] as $selector => $item)
						{
							if (!isset($attrs[$selector]))
							{
								$attrs[$selector] = array();
							}
							$resultList = $doc->querySelectorAll($selector);
							foreach ($resultList as $pos => $result)
							{
								//$attrs[$selector][$pos] = array();@todo
								$attrs[$selector] = array();
								foreach ($item as $attr)
								{
									if (isset($attr['attribute']))
									{
										$attr['attribute'] = (string)$attr['attribute'];
										$attrs[$selector][$attr['attribute']] = $result->getAttribute($attr['attribute']);
									}
								}
							}
						}
					}
					$export['items'][$row['ID']]['items'][] = array(
						'code' => $block->getCode(),
						'cards' => $cards,
						'nodes' => $nodes,
						'style' => $styles,
						'attrs' => $attrs
					);
				}
			}
		}

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
}