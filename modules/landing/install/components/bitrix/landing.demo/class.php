<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Landing\Site;
use \Bitrix\Landing\Landing;
use \Bitrix\Landing\Manager;
use \Bitrix\Landing\Syspage;
use \Bitrix\Landing\Demos;
use \Bitrix\Landing\Template;
use \Bitrix\Landing\TemplateRef;
use \Bitrix\Landing\Rights;
use \Bitrix\Landing\Landing\Cache;
use \Bitrix\Landing\Hook\Page\Settings;
use \Bitrix\Landing\Site\Type;
use \Bitrix\Highloadblock;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\ModuleManager;
use \Bitrix\Main\Web\HttpClient;
use \Bitrix\Main\Web\Json;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Config\Option;
use \Bitrix\Iblock;
use \Bitrix\Main;
use \Bitrix\Main\Event;
use \Bitrix\Main\EventResult;

\CBitrixComponent::includeComponentClass('bitrix:landing.base');

class LandingSiteDemoComponent extends LandingBaseComponent
{
	/**
	 * Count items per page.
	 */
	const COUNT_PER_PAGE = 48;

	/**
	 * Days count during which templates marked as 'new'.
	 */
	const LABEL_NEW_PERIOD_DAY = 12;

	/**
	 * Tag for managed cache.
	 */
	const DEMO_TAG = 'landing_demo';

	/**
	 * Path with demo templates data for site.
	 */
	const DEMO_DIR_SITE = 'site';

	/**
	 * Path with demo templates data for page.
	 */
	const DEMO_DIR_PAGE = 'page';

	/**
	 * Remote repository url.
	 */
	const REMOTE_REPOSITORY_URL = 'https://preview.bitrix24.site/rest/1/gvsn3ngrn7vb4t1m/';

	/**
	 * Steps constant for catalog import.
	 */
	const STEP_STATUS_ERROR = 'ERROR';
	const STEP_STATUS_CONTINUE = 'CONTINUE';
	const STEP_STATUS_COMPLETE = 'COMPLETE';
	const STEP_ID_HIGHLOADBLOCK = 'CREATE_HIGHLOADBLOCK';
	const STEP_ID_PREPARE_CRM_CATALOG = 'CRM_CATALOG';
	const STEP_ID_XML_IMPORT = 'XML_IMPORT';
	const STEP_ID_ADDITIONAL_UPDATE = 'ADDITIONAL';
	const STEP_ID_REINDEX = 'REINDEX';
	const STEP_ID_CATALOG_REINDEX = 'CATALOG_REINDEX';
	const STEP_ID_FINAL = 'FINAL';
	private $catalogStepList = [
		self::STEP_ID_HIGHLOADBLOCK,
		self::STEP_ID_PREPARE_CRM_CATALOG,
		self::STEP_ID_XML_IMPORT,
		self::STEP_ID_ADDITIONAL_UPDATE,
		self::STEP_ID_CATALOG_REINDEX,
		self::STEP_ID_FINAL
	];

	protected const DEMO_TEMPLATE_FIRST = 'empty';
	protected const DEMO_NFR_APP = 'bitrix.partnerlanding';
	protected const DEMO_SORT_OFFSET = 100;
	protected const DEMO_SORT_SCOPE_FIRST = 0;
	protected const DEMO_SORT_SCOPE_NEW = 1;
	protected const DEMO_SORT_SCOPE_HOLIDAYS = 2;
	protected const DEMO_SORT_SCOPE_REST = 3;
	protected const DEMO_SORT_SCOPE_LOCAL = 5;
	protected const DEMO_SORT_SCOPE_LAST = 6;
	protected const DEMO_HOLIDAYS = [
		"holidays/holiday.blackfriday" => [11,01,11,30],
		"holidays/holiday.easter1" => [2,21,4,4],
		"holidays/holiday.easter2" => [3,21,5,2],
		"holidays/holiday.halloween" => [10,10,10,31],
		"holidays/holiday.new-year" => [12,8,12,31],
		"holidays/holiday.thanksgiving" => [11,01,11,30],
		"holidays/holiday.valentine1" => [1,24,2,14],
		"holidays/holiday.valentine2" => [1,24,2,14],
		"holidays/holiday.valentine3" => [1,24,2,14],
		"holidays/holiday.xmas" => [12,1,12,25],
		"holidays/holiday.8march1" => [2,15,3,8],
		"holidays/holiday.8march2" => [2,15,3,8],
		"holidays/holiday.8march3" => [2,15,3,8],
		"holidays/holiday.23february.1" => [2,2,2,23],
		"holidays/holiday.23february.2" => [2,2,2,23],
		"holidays/holiday.23february.3" => [2,2,2,23],
		"holidays/holiday.april-fool" => [3,11,4,1],
	];

	/**
	 * Relative url for new site.
	 * @var string
	 */
	protected $urlTpl = '/#rand#/';

	/**
	 * Landing old ids for version 3.
	 * @var array
	 */
	protected $oldIds = [];

	/**
	 * Predefined additional fields.
	 * @var array
	 */
	protected $presetAdditionalFields = [];

	/**
	 * Gets remote templates from repository.
	 * @var bool
	 */
	protected $getRemoteTemplates = false;

	/**
	 * Different layouts for pages.
	 * @var array
	 */
	protected $pagesLayouts = [];

	/**
	 * Redirect to the landing.
	 * @param int $landingId Landing id.
	 * @return boolean If error.
	 */
	protected function redirectToLanding($landingId)
	{
		if (
			isset($this->arParams['DISABLE_REDIRECT']) &&
			$this->arParams['DISABLE_REDIRECT'] == 'Y'
		)
		{
			return true;
		}
		$landing = Landing::createInstance($landingId, [
			'skip_blocks' => true
		]);
		if ($landing->exist())
		{
			$siteId = $landing->getSiteId();
			$redirect = str_replace(
				array('#site_show#', '#landing_edit#'),
				array($siteId, $landingId),
				$this->arParams['PAGE_URL_LANDING_VIEW']
			);
			$uriEdit = new \Bitrix\Main\Web\Uri($redirect);
			$uriEdit->addParams([
				'IFRAME' => ($this->arParams['DONT_LEAVE_FRAME'] != 'Y') ? 'N' : 'Y'
			]);
			\localRedirect($uriEdit->getUri(), true);
		}
		else
		{
			$this->setErrors($landing->getError()->getErrors());
			return false;
		}
		return true;
	}

	/**
	 * Preset additional fields before create.
	 * @param array $fields Additional fields.
	 * @return void
	 */
	public function setAdditionalFields(array $fields)
	{
		$this->presetAdditionalFields = $fields;
	}

	/**
	 * Prepare additional fields for page.
	 * @param array $data Data array.
	 * @param bool $request Get data from request.
	 * @return array
	 */
	protected function prepareAdditionalFieldsPage($data, $request = true)
	{
		$data = $this->prepareAdditionalFields($data, $request);

		// for new page in EXISTING SITES - match value
		if ($this->request('theme'))
		{
			if ($this->request('theme_use_site') && $this->request('theme_use_site') == 'Y')
			{
				unset($data['ADDITIONAL_FIELDS']['THEME_CODE']);
			}
			else
			{
				$data['ADDITIONAL_FIELDS']['THEME_CODE'] = $this->request('theme');
				// not overwrite typo - always use theme from template
			}
		}

		return $data;
	}

	/**
	 * Prepare additional fields for site.
	 * @param array $data Data array.
	 * @return array
	 */
	protected function prepareAdditionalFieldsSite($data)
	{
		// For SITE always add theme from request.
		// It is may be original theme, or user change
		$data = $this->prepareAdditionalFields($data, false);

		if ($this->request('theme'))
		{
			$data['ADDITIONAL_FIELDS']['THEME_CODE'] = $this->request('theme');
			// not overwrite typo - always use theme from template
		}

		return $data;
	}


	/**
	 * Prepare array of additional data.
	 * @param array $data Data item array.
	 * @param bool $request Get data from request.
	 * @return array
	 */
	protected function prepareAdditionalFields($data, $request = true)
	{
		if (
			!isset($data['ADDITIONAL_FIELDS']) ||
			!is_array($data['ADDITIONAL_FIELDS'])
		)
		{
			$data['ADDITIONAL_FIELDS'] = array();
		}

		// title / description was changed in preview
		if ($request)
		{
			$title = isset($this->arParams['META']['TITLE'])
					? $this->arParams['META']['TITLE']
					: $this->request('title');
			$description = isset($this->arParams['META']['DESCRIPTION'])
					? $this->arParams['META']['DESCRIPTION']
					: $this->request('description');
			if ($title)
			{
				$data['ADDITIONAL_FIELDS']['METAOG_TITLE'] = $title;
				$data['ADDITIONAL_FIELDS']['METAMAIN_TITLE'] = $title;
			}
			if ($description)
			{
				$data['ADDITIONAL_FIELDS']['METAOG_DESCRIPTION'] = $description;
				$data['ADDITIONAL_FIELDS']['METAMAIN_DESCRIPTION'] = $description;
			}
		}

		// from predefined fields
		if ($this->presetAdditionalFields)
		{
			foreach ($this->presetAdditionalFields as $key => $value)
			{
				$data['ADDITIONAL_FIELDS'][$key] = $value;
			}
		}

		return $data;
	}

	/**
	 * Prepare array of main data.
	 * @param array $data Data item array.
	 * @param bool $request Get data from request.
	 * @return array
	 */
	protected function prepareMainFields(array $data, $request = true)
	{
		if ($request)
		{
			$title = isset($this->arParams['META']['TITLE'])
					? $this->arParams['META']['TITLE']
					: $this->request('title');
			if ($title)
			{
				$data['TITLE'] = $title;
			}
		}

		return $data;
	}

	/**
	 * Get template manifest.
	 * @param int $id Template id.
	 * @return array
	 */
	protected function getTemplateManifest($id)
	{
		if ($id < 0)
		{
			$http = new HttpClient;
			$res = $http->get(
				$this::REMOTE_REPOSITORY_URL . 'landing_cloud.cloud.getAppItemManifest?'
				. 'user_lang=' . LANGUAGE_ID
				. '&id=' . (-1 * $id)
			);
			if ($res)
			{
				$res = Json::decode($res);
			}
			if (isset($res['result']['MANIFEST']))
			{
				return $res['result']['MANIFEST'];
			}
			return [];
		}

		$res = Demos::getList(array(
			'select' => array(
				'MANIFEST', 'APP_CODE'
			),
			'filter' => array(
				'ID' => $id
			)
		));
		if ($row = $res->fetch())
		{
			$manifest = unserialize($row['MANIFEST']);
			if ($manifest)
			{
				$manifest['app_code'] = $row['APP_CODE'];
			}
			return $manifest;
		}

		return array();
	}

	/**
	 * Create one page in site.
	 * @param int $siteId Site id.
	 * @param string $code Page code.
	 * @param \Bitrix\Landing\Landing $landing Landing instance after create.
	 * @return boolean|int Id of new landing.
	 */
	public function createPage($siteId, $code, &$landing = null)
	{
		static $firstPage = true;
		$demo = $this->getDemoPage($code);
		$siteId = intval($siteId);

		if (is_string($code) && isset($demo[$code]))
		{
			$initiatorAppCode = null;
			// get from rest
			if ($demo[$code]['REST'] > 0)
			{
				$demo[$code]['DATA'] = $this->getTemplateManifest(
					$demo[$code]['REST']
				);
				if ($demo[$code]['DATA'])
				{
					$initiatorAppCode = $demo[$code]['DATA']['app_code'];
				}
			}
			if (!($data = $demo[$code]['DATA']))
			{
				$this->addError('WRONG_DATA', 'Wrong data');
				return false;
			}
			$pageData = $this->prepareMainFields(
				$data['fields'],
				$firstPage
			);
			$pageData = $this->prepareAdditionalFieldsPage(
				$pageData,
				$firstPage
			);
			$pageData['SITE_ID'] = $siteId;
			$pageData['ACTIVE'] = 'N';
			$pageData['PUBLIC'] = 'N';
			$pageData['SYS'] = ($demo[$code]['ACTIVE'] == 'Y') ? 'N' : 'Y';
			$pageData['TPL_CODE'] = $code;
			$pageData['XML_ID'] = $data['name'] . '|' . $code;
			// localization
			$pageData = $this->translate(
				$pageData,
				$demo[$code]['LANG']
			);
			$pageData['ADDITIONAL_FIELDS'] = $this->translate(
				$pageData['ADDITIONAL_FIELDS'],
				$demo[$code]['LANG']
			);
			// folder
			if ($this->request($this->arParams['ACTION_FOLDER']))
			{
				$pageData['FOLDER_ID'] = $this->request(
					$this->arParams['ACTION_FOLDER']
				);
			}
			if ($initiatorAppCode)
			{
				$pageData['INITIATOR_APP_CODE'] = $initiatorAppCode;
			}
			// add
			Landing::setEditMode();
			$res = Landing::add($pageData);
			// and fill each page with blocks
			if ($res->isSuccess())
			{
				$landingId = $res->getId();
				$this->oldIds[$data['old_id']] = $landingId;
				$landing = Landing::createInstance($landingId);
				if ($landing->exist())
				{
					$sort = 0;
					$blocks = array();
					$blocksIds = array();
					$blocksCodes = array();
					$blocksAccess = array();
					if (isset($data['layout']['code']))
					{
						$this->pagesLayouts[$landingId] = $data['layout'];
					}
					if (!is_array($data['items']))
					{
						$data['items'] = [];
					}
					foreach ($data['items'] as $k => $block)
					{
						if (is_array($block))
						{
							if ($data['version'] >= 2)
							{
								// support rest blocks
								if (
									isset($block['repo_block']['app_code']) &&
									isset($block['repo_block']['xml_id'])
								)
								{
									$repoBlock = \Bitrix\Landing\Repo::getList(array(
										'select' => array(
											'ID'
										),
										'filter' => array(
											'=APP_CODE' => $block['repo_block']['app_code'],
											'=XML_ID' => $block['repo_block']['xml_id']
										)
									))->fetch();
									if ($repoBlock)
									{
										$block['code'] = 'repo_' . $repoBlock['ID'];
									}
								}
								if (!isset($block['code']))
								{
									continue;
								}
								$blocksCodes[$k] = $block['code'];
								$blockId = $landing->addBlock(
									$block['code'],
									array(
										'PUBLIC' => 'N',
										'SORT' => $sort,
										'XML_ID' => isset($block['old_id'])
													? $block['old_id']
													: strtolower(\randString(10)),
										'ANCHOR' => isset($block['anchor'])
													? $block['anchor']
													: ''
									)
								);
								if (isset($block['access']))
								{
									$blocksAccess[$blockId] = $block['access'];
								}
								$blocksIds[$block['old_id']] = $blockId;
								$sort += 500;
								$blocks[$blockId] = $k;
							}
							else
							{
								$block['PUBLIC'] = 'N';
								$block['XML_ID'] = strtolower(\randString(10));
								$blockId = $landing->addBlock(
									isset($block['CODE']) ? $block['CODE'] : $k,
									$block
								);
								$blocks[$k] = $blockId;
							}
						}
						else
						{
							$blockId = $landing->addBlock($block, array(
								'PUBLIC' => 'N',
							));
							$blocks[$block] = $blockId;
						}
					}
					$blockReplace = [];
					foreach ($blocksIds as $oldId => $newId)
					{
						$blockReplace['\'#block' . $oldId . '\''] = '\'#block' . $newId . '\'';
						$blockReplace['"#block' . $oldId . '"'] = '"#block' . $newId . '"';
					}
					// redefine content of blocks
					foreach ($landing->getBlocks() as $k => $block)
					{
						if (!$block->getManifest())
						{
							continue;
						}
						$updated = false;
						if ($data['version'] == 3)
						{
							if (isset($data['items'][$blocks[$k]]))
							{
								$newData = $data['items'][$blocks[$k]];
								// update cards
								if (isset($newData['cards']) && is_array($newData['cards']))
								{
									$updated = true;
									$block->updateCards(
										$newData['cards']
									);
								}
								// update style
								if (isset($newData['style']) && is_array($newData['style']))
								{
									$updatedStyles = [];
									foreach ($newData['style'] as $selector => $classes)
									{
										if ($selector == '#wrapper')
										{
											$selector = '#' . $block->getAnchor($block->getId());
										}
										$updated = true;
										foreach ((array)$classes as $clPos => $clVal)
										{
											$selectorUpd = $selector . '@' . $clPos;
											if (!in_array($selectorUpd, $updatedStyles))
											{
												$updatedStyles[] = $selectorUpd;
												$block->setClasses(array(
													$selectorUpd => array(
														'classList' => (array)$clVal
													)
												));
											}
										}
									}
								}
								// update nodes
								if (isset($newData['nodes']) && !empty($newData['nodes']))
								{
									$updated = true;
									$block->updateNodes($newData['nodes']);
								}
								// update menu
								if (isset($newData['menu']) && !empty($newData['menu']))
								{
									$updated = true;
									$block->updateNodes($newData['menu']);
								}
								// update attrs
								if (isset($newData['attrs']) && !empty($newData['attrs']))
								{
									$updated = true;
									if (isset($newData['attrs']['#wrapper']))
									{
										$newData['attrs']['#' . $block->getAnchor($block->getId())] = $newData['attrs']['#wrapper'];
										unset($newData['attrs']['#wrapper']);
									}
									$block->setAttributes($newData['attrs']);
								}
								// dynamic
								if (isset($newData['dynamic']) && is_array($newData['dynamic']))
								{
									$block->saveDynamicParams($newData['dynamic']);
								}
							}
						}
						else if ($data['version'] == 2)
						{
							if (isset($data['items'][$blocks[$k]]))
							{
								$newData = $data['items'][$blocks[$k]];
								// adjust cards
								if (isset($newData['cards']) && is_array($newData['cards']))
								{
									foreach ($newData['cards'] as $selector => $count)
									{
										$changed = false;
										$block->adjustCards($selector, $count, $changed);
										if ($changed)
										{
											$updated = true;
										}
									}
								}
								// update style
								if (isset($newData['style']) && is_array($newData['style']) && !empty($newData['style']))
								{
									foreach ($newData['style'] as $selector => $classes)
									{
										if ($selector == '#wrapper')
										{
											$selector = '#' . $block->getAnchor($block->getId());
										}
										$updated = true;
										$block->setClasses(array(
									   		$selector => array(
							   					'classList' => array_unique($classes)
									   		)
									 	));
									}
								}
								// update nodes
								if (isset($newData['nodes']) && !empty($newData['nodes']))
								{
									$updated = true;
									$block->updateNodes($newData['nodes']);
								}
								// update attrs
								if (isset($newData['attrs']) && !empty($newData['attrs']))
								{
									$updated = true;
									$block->setAttributes($newData['attrs']);
								}
							}
						}
						// replace links and some content
						$content = $block->getContent();
						foreach ($blocks as $blockCode => $blockId)
						{
							if ($data['version'] == 3)
							{
								$count = 0;
								$content = str_replace(
									array_keys($blockReplace),
									array_values($blockReplace),
									$content,
									$count
								);
								if ($count)
								{
									$updated = true;
								}
							}
							else if ($data['version'] == 2)
							{
								$count = 0;
								$content = str_replace(
									'@block[' . $blocksCodes[$blockId] . ']',
									$blockCode,
									$content,
									$count
								);
								if ($count)
								{
									$updated = true;
								}
							}
							else
							{
								$count = 0;
								$content = str_replace(
									'@block[' . $blockCode . ']',
									$blockId,
									$content,
									$count
								);
								if ($count)
								{
									$updated = true;
								}
							}
							if (isset($data['replace']) && is_array($data['replace']))
							{
								foreach ($data['replace'] as $find => $replace)
								{
									$count = 0;
									$content = str_replace(
										$find,
										$replace,
										$content,
										$count
									);
									if ($count)
									{
										$updated = true;
									}
								}
							}
						}
						if (mb_strpos($content, '#TITLE#') !== false)
						{
							$updated = true;
							$content = str_replace(
								'#TITLE#',
								\htmlspecialcharsbx($block->getMeta()['LANDING_TITLE']),
								$content
							);
						}
						if ($updated)
						{
							$block->saveContent($content);
							$block->save();
						}
						if (
							isset($blocksAccess[$block->getId()]) &&
							$blocksAccess[$block->getId()] != \Bitrix\Landing\Block::ACCESS_X
						)
						{
							\Bitrix\Landing\Internals\BlockTable::update(
								$block->getId(),
								[
									'ACCESS' => $blocksAccess[$block->getId()]
								]
							);
						}
					}
					$firstPage = false;
					return $landing->getId();
				}
				else
				{
					$this->setErrors($landing->getError()->getErrors());
					return false;
				}
			}
			else
			{
				$this->setErrors($res->getErrors());
				return false;
			}
		}

		return false;
	}

	/**
	 * Get domain id for new site.
	 * @deprecated since 20.0.0
	 * @return int|string
	 */
	protected function getDomainId()
	{
		return Type::getDomainId();
	}

	/**
	 * Create demo page for preview.
	 * @param string $code Code of page.
	 * @param array $template Template data.
	 * @return string
	 */
	public function getUrlPreview($code, $template = []): string
	{
		if (!is_string($code))
		{
			return '';
		}

		if ($this->isRepo())
		{
			return '';
		}

		// first detect from rest
		if (($codePos = mb_strrpos($code, '@')) !== false)
		{
			$appCode = mb_substr($code, 0, $codePos);
			$xmlId = mb_substr($code, $codePos + 1);
			if ($appCode == 'local')
			{
				$appCode = '';
			}
			$res = Demos::getList(array(
				'select' => array(
					'PREVIEW_URL'
				),
				'filter' => array(
					'=ACTIVE' => 'Y',
					'=SHOW_IN_LIST' => 'Y',
					'=TYPE' => $this->arParams['TYPE'],
					'=APP_CODE' => $appCode,
					'=XML_ID' => $xmlId
				)
			));
			if ($row = $res->fetch())
			{
				return $row['PREVIEW_URL'];
			}
		}

		if (isset($template['DATA']['parent']))
		{
			$code = $template['DATA']['parent'];
		}
		$code = str_replace('/', '_', $code);
		if (
			$this->arParams['TYPE'] == Type::SCOPE_CODE_KNOWLEDGE ||
			$this->arParams['TYPE'] == Type::SCOPE_CODE_GROUP
		)
		{
			$previewSubDir = '/pub/kb/';
		}
		else
		{
			$previewSubDir = '/pub/site/';
		}

		return 'https://preview.bitrix24.site' . $previewSubDir . $code . '/?user_lang=' . LANGUAGE_ID;
	}

	/**
	 * Binding site if, if binding params specified.
	 * @param int $siteId Site id.
	 * @return void
	 */
	protected function bindingSite($siteId)
	{
		$this->arParams['BINDING_TYPE'] = mb_strtoupper($this->arParams['BINDING_TYPE']);
		if ($this->arParams['BINDING_TYPE'] == 'GROUP')
		{
			$binding = new \Bitrix\Landing\Binding\Group(
				intval(trim($this->arParams['BINDING_ID']))
			);
			$binding->bindSite($siteId);
		}
		else if ($this->arParams['BINDING_TYPE'] == 'MENU')
		{
			$binding = new \Bitrix\Landing\Binding\Menu(
				trim($this->arParams['BINDING_ID'])
			);
			$binding->bindSite($siteId);
		}
	}

	/**
	 * Create site or page by template.
	 * @param string $code Demo site code.
	 * @param mixed $additional Data from form.
	 * @return boolean
	 */
	public function actionSelect($code, $additional = null)
	{
		// create page in the site
		if (
			$this->arParams['SITE_WORK_MODE'] != 'Y' &&
			$this->arParams['SITE_ID'] > 0
		)
		{
			$landingId = $this->createPage(
				$this->arParams['SITE_ID'],
				$code
			);
			if ($landingId)
			{
				return $this->redirectToLanding($landingId);
			}
			else
			{
				return false;
			}
		}
		// else create site and pages into
		$demo = $this->getDemoSite();
		if (isset($demo[$code]))
		{
			// get from rest
			if ($demo[$code]['REST'])
			{
				$demo[$code]['DATA'] = $this->getTemplateManifest(
					$demo[$code]['REST']
				);
			}
			if (!($data = $demo[$code]['DATA']))
			{
				$this->addError('WRONG_DATA', 'Wrong data');
				return false;
			}
			$version = $data['version'];
			$siteData = $this->prepareMainFields($data['fields'], true);
			$siteData = $this->prepareAdditionalFieldsSite($siteData);
			$siteData['ACTIVE'] = 'N';
			if (isset($siteData['TITLE']))
			{
				$siteData['CODE'] = \CUtil::translit(
					$siteData['TITLE'],
					LANGUAGE_ID
				);
			}
			else
			{
				$siteData['CODE'] = str_replace(
					'#rand#',
					mb_strtolower(\randString(5)),
					$this->urlTpl
				);
			}
			$siteData['TPL_CODE'] = $code;
			$siteData['XML_ID'] = $data['name'] . '|' . $code;
			$siteData['TYPE'] = $this->arParams['TYPE'];
			$pageIndex = $siteData['LANDING_ID_INDEX']
						? $siteData['LANDING_ID_INDEX']
						: '';
			$page404 = $siteData['LANDING_ID_404']
						? $siteData['LANDING_ID_404']
						: '';
			// localization
			$siteData = $this->translate(
				$siteData,
				$demo[$code]['LANG']
			);
			$siteData['ADDITIONAL_FIELDS'] = $this->translate(
				$siteData['ADDITIONAL_FIELDS'],
				$demo[$code]['LANG']
			);
			// first create site
			if ($this->arParams['SITE_WORK_MODE'] == 'Y')
			{
				$siteId = $this->arParams['SITE_ID'];
			}
			else
			{
				if (self::checkAllowDemoData($siteData))
				{
					$settings = Settings::getDataForSite();
					// if shop section exist, save for site
					$sectionXmlId = null;
					if (!empty($additional) && is_array($additional))
					{
						if (!empty($additional['section']))
						{
							$sectionXmlId = $additional['section'];
						}
					}
					$sectionId = $this->getParentCatalogSectionId($settings['IBLOCK_ID'], $sectionXmlId);
					if ($sectionId !== null)
					{
						$siteData['ADDITIONAL_FIELDS']['SETTINGS_SECTION_ID'] = $sectionId;
					}
					unset($sectionId);
				}

				// need rewrite CRM-button code from repo
				$buttons = \Bitrix\Landing\Hook\Page\B24button::getButtons();
				$buttons = array_keys($buttons);
				if (isset($buttons[0]))
				{
					$siteData['ADDITIONAL_FIELDS']['B24BUTTON_CODE'] = $buttons[0];
				}
				// if portal have no buttons - set none
				else
				{
					$siteData['ADDITIONAL_FIELDS']['B24BUTTON_CODE'] = 'N';
				}
				if ($data['singleton'])
				{
					Manager::enableFeatureTmp(
						Manager::FEATURE_CREATE_SITE
					);
				}
				if (in_array(Manager::getZone(), ['es', 'de', 'fr', 'it', 'pl', 'uk']))
				{
					$siteData['ADDITIONAL_FIELDS']['COOKIES_USE'] = 'Y';
				}
				$res = Site::add($siteData);
				Manager::disableFeatureTmp(
					Manager::FEATURE_CREATE_SITE
				);
				$siteId = $res->getId();
				$this->bindingSite($siteId);
			}
			if ($siteId)
			{
				$siteData['ID'] = $siteId;
				$forSiteUpdate = array();
				$firstLandingId = false;
				if (
					!isset($data['syspages']) ||
					!is_array($data['syspages'])
				)
				{
					$data['syspages'] = array();
				}
				// check system pages for unique
				foreach (Syspage::get($siteId) as $sysCode => $sysItem)
				{
					if (isset($data['syspages'] [$sysCode]))
					{
						unset($data['syspages'] [$sysCode]);
					}
				}
				// then create pages
				$landings = array();
				if (empty($data['items']))
				{
					$data['items'][] = $code;
				}
				/** @var Landing $landingInstance */
				$landingInstance = null;
				Landing::disableUpdate();
				foreach ($data['items'] as $page)
				{
					$landingId = $this->createPage(
						$siteData['ID'],
						$demo[$code]['APP_CODE']
						? $demo[$code]['APP_CODE'] . '@' . $page
						: $page,
						$landingInstance
					);
					if (!$landingId)
					{
						continue;
					}
					elseif (!$firstLandingId)
					{
						$firstLandingId = $landingId;
					}
					$landings[$page] = $landingId;
				}
				Landing::enableUpdate();
				// publication after create
				if ($landingInstance !== null && $demo[$code]['PUBLICATION'])
				{
					$landingInstance->publication();
				}
				$landingReplace = [];
				foreach ($this->oldIds as $oldId => $newId)
				{
					$landingReplace['\'#landing' . $oldId . '\''] = '\'#landing' . $newId . '\'';
					$landingReplace['"#landing' . $oldId . '"'] = '"#landing' . $newId . '"';
					$landingReplaceDynamic['#landing' . $oldId] = '#landing' . $newId;
				}
				// update site for some fields
				if (isset($landings[$pageIndex]))
				{
					$forSiteUpdate['LANDING_ID_INDEX'] = $landings[$pageIndex];
				}
				if (isset($landings[$page404]))
				{
					$forSiteUpdate['LANDING_ID_404'] = $landings[$page404];
				}
				// redefine content of pages
				foreach ($landings as $landCode => $landId)
				{
					$landing = Landing::createInstance($landId);
					if ($landing->exist())
					{
						\Bitrix\Landing\Hook::indexLanding($landId);
						foreach ($landing->getBlocks() as $block)
						{
							$updated = false;
							$content = $block->getContent();
							foreach ($landings as $landCode => $landId)
							{
								$count = 0;
								if ($version == 3)
								{
									$content = str_replace(
										array_keys($landingReplace),
										array_values($landingReplace),
										$content,
										$count
									);
									// dynamic part
									$dynamicParams = $block->getDynamicParams();
									if ($dynamicParams)
									{
										$block->saveDynamicParams(
											$dynamicParams,
											[
												'linkReplace' => $landingReplaceDynamic
											]
										);
									}
								}
								else
								{
									$content = str_replace(
										'@landing[' . $landCode . ']',
										$landId,
										$content,
										$count
									);
								}
								if ($count)
								{
									$updated = true;
								}
							}
							if ($updated)
							{
								$block->saveContent($content);
								$block->save();
							}
						}
					}
				}
				// set layout
				$tplsXml = array();
				$res = Template::getList(array(
					'select' => array(
						'ID', 'XML_ID'
					)
				));
				while ($row = $res->fetch())
				{
					$tplsXml[$row['XML_ID']] = $row['ID'];
				}
				// for site
				if (
					isset($data['layout']['code']) &&
					isset($tplsXml[$data['layout']['code']])
				)
				{
					$ref = array();
					if (isset($data['layout']['ref']))
					{
						foreach ((array)$data['layout']['ref'] as $ac => $aLidCode)
						{
							if (isset($landings[$aLidCode]))
							{
								$ref[$ac] = $landings[$aLidCode];
							}
						}
					}
					$forSiteUpdate['TPL_ID'] = $tplsXml[$data['layout']['code']];
					TemplateRef::setForSite(
						$siteData['ID'],
						$ref
					);
				}
				// and for pages
				if ($this->pagesLayouts)
				{
					foreach ($this->pagesLayouts as $landingId => $layout)
					{
						if (isset($tplsXml[$layout['code']]))
						{
							Landing::update($landingId, [
								'TPL_ID' => $tplsXml[$layout['code']]
							]);
							if (isset($layout['ref']))
							{
								$ref = [];
								foreach ((array)$layout['ref'] as $ac => $aLidCode)
								{
									if (isset($landings[$aLidCode]))
									{
										$ref[$ac] = $landings[$aLidCode];
									}
								}
								if ($ref)
								{
									TemplateRef::setForLanding(
										$landingId,
										$ref
									);
								}
							}
						}
					}
				}
				// set pages to folders
				$alreadyFolders = array();
				if (isset($data['folders']) && is_array($data['folders']))
				{
					foreach ($data['folders'] as $folder => $items)
					{
						if (isset($landings[$folder]) && is_array($items))
						{
							foreach ($items as $item)
							{
								if (isset($landings[$item]))
								{
									if (!in_array($landings[$folder], $alreadyFolders))
									{
										$alreadyFolders[] = $landings[$folder];
										Landing::update($landings[$folder], array(
											'FOLDER' => 'Y'
										));
									}
									Landing::update($landings[$item], array(
										'FOLDER_ID' => $landings[$folder]
									));
								}
							}
						}
					}
				}
				// create system refs
				foreach ($data['syspages'] as $sysCode => $pageCode)
				{
					if (isset($landings[$pageCode]))
					{
						Syspage::set(
							$siteData['ID'],
							$sysCode,
							$landings[$pageCode]
						);
					}
				}
				// update site if need
				if ($forSiteUpdate)
				{
					Site::update($siteData['ID'], $forSiteUpdate);
				}
				// send events
				$event = new \Bitrix\Main\Event('landing', 'onAfterDemoCreate', array(
					'id' => $siteData['ID'],
					'code' => $code
				));
				$event->send();
				$this->redirectToLanding($firstLandingId);
			}
			else
			{
				$this->setErrors($res->getErrors());
				return false;
			}
			return true;
		}

		return false;
	}

	/**
	 * Returns true, if allow create store data.
	 *
	 * @param array $demo
	 * @return bool
	 */
	private static function checkAllowDemoData(array $demo)
	{
		if (!Manager::isB24())
		{
			return false;
		}
		if (!isset($demo['TYPE']))
		{
			return false;
		}
		return (is_array($demo['TYPE']) ? in_array('STORE', $demo['TYPE']) : $demo['TYPE'] == 'STORE');
	}

	/**
	 * Create store step.
	 * @param string $code Step code.
	 * @param null $additional
	 * @return array
	 */
	private function stepperStore($code, $additional = null)
	{
		$result = [
			'STATUS' => self::STEP_STATUS_COMPLETE,
			'MESSAGE' => '',
			'FINAL' => true,
			'PROGRESS' => 0
		];

		$demo = $this->getDemoSite();
		if (!is_string($code) || !isset($demo[$code]))
		{
			return $result;
		}

		if (self::checkAllowDemoData($demo[$code]))
		{
			Loader::includeModule('iblock');

			$internalResult = $this->createCatalogStep($code);
			if ($internalResult['FINAL'])
			{
				if ($internalResult['STATUS'] == self::STEP_STATUS_ERROR)
				{
					$result = $internalResult;
				}
				else
				{
					$result['MESSAGE'] = $internalResult['MESSAGE'];
				}
				$result['PROGRESS'] = 100;
			}
			else
			{
				$result['STATUS'] = self::STEP_STATUS_CONTINUE;
				$result['MESSAGE'] = $internalResult['MESSAGE'];
				$result['FINAL'] = false;
				$result['PROGRESS'] = $internalResult['PROGRESS'];
			}
		}

		return $result;
	}

	/**
	 * Final create store step.
	 * @param string $code Step code.
	 * @param null $additional
	 * @return string
	 */
	private function stepperStoreFinalUrl($code, $additional = null)
	{
		$uriSelect = new \Bitrix\Main\Web\Uri($this->currentRequest->getRequestUri());

		$params = array(
			'action' => 'select',
			'param' => $code,
			'sessid' => bitrix_sessid()
		);
		$post = $this->currentRequest->getPostList();
		if (!$post->isEmpty())
		{
			$params += $post->toArray();
		}

		$uriSelect->deleteParams(array('sessid', 'stepper', 'action', 'param', 'additional', 'code', 'start'));
		$uriSelect->addParams($params);

		unset($post);
		unset($params);

		return $uriSelect->getUri();
	}

	/**
	 * Localize the array.
	 * @param array $item Item for localization
	 * @param array $lang Localization array.
	 * @return array
	 */
	protected function translate(array $item, $lang)
	{
		if (
			isset($lang['lang']) &&
			isset($lang['lang_original']) &&
			is_array($lang['lang'])
		)
		{
			// detect translated messages
			$translate = null;
			$langPortal = LANGUAGE_ID;
			if (in_array($langPortal, ['ru', 'kz', 'by']))
			{
				$langPortal = 'ru';
			}
			$langArray = $lang['lang'];
			$langOrig = $lang['lang_original'];
			if (isset($langArray[$langPortal]))
			{
				$translate = $langArray[$langPortal];
			}
			else if (
				$langOrig != $langPortal &&
				isset($langArray['en'])
			)
			{
				$translate = $langArray['en'];
			}
			if ($translate)
			{
				foreach ($item as &$val)
				{
					if (
						!is_array($val) &&
						isset($translate[$val])
					)
					{
						$val = $translate[$val];
					}
				}
			}
		}

		return $item;
	}

	/**
	 * Gets demo templates.
	 * @param string $subDir Subdir for data dir.
	 * @param string $code Item code.
	 * @return array
	 */
	protected function getDemo($subDir, $code = null)
	{
		static $data = array();
		static $applicationItems = [];

		$eventFunc = function($data) use($subDir, $code, &$applicationItems)
		{
			if (!empty($data))
			{
				if ($subDir == self::DEMO_DIR_SITE)
				{
					// make some items disable, if they are singleton
					$res = Site::getList([
						'select' => [
							'XML_ID'
						],
						'filter' => [
							'=TYPE' => $this->arParams['TYPE'],
							'!==XML_ID' => null,
							'CHECK_PERMISSIONS' => 'N'
						],
						'group' => [
							'XML_ID'
						]
					]);
					while ($row = $res->fetch())
					{
						if (mb_strpos($row['XML_ID'], '|') !== false)
						{
							[, $row['XML_ID']] = explode('|', $row['XML_ID']);
						}
						if (
							isset($data[$row['XML_ID']]) &&
							$data[$row['XML_ID']]['SINGLETON']
						)
						{
							$data[$row['XML_ID']]['AVAILABLE'] = false;
						}
						if (
							isset($data[$row['XML_ID']]['DATA']['site_group_item']) &&
							$data[$row['XML_ID']]['DATA']['site_group_item'] === 'Y' &&
							isset($data[$row['XML_ID']]['DATA']['site_group_parent']) &&
							($siteGroupParent = $data[$row['XML_ID']]['DATA']['site_group_parent']) &&
							isset($data[$siteGroupParent]) &&
							$data[$siteGroupParent]['SINGLETON']
						)
						{
							$data[$siteGroupParent]['AVAILABLE'] = false;
						}
					}
					unset($res, $row);
				}

				$siteTemplateId = Manager::getTemplateId(
					Manager::getMainSiteId()
				);

				if ($code)
				{
					$xmlId = mb_substr($code, mb_strrpos($code, '@') + 1);
					$appCode = mb_substr($code, 0, mb_strrpos($code, '@'));
				}
				else
				{
					$xmlId = $appCode = null;
				}

				// fill from rest
				$res = Demos::getList(array(
					'select' => array(
						'ID', 'APP_CODE', 'XML_ID', 'TYPE',
						'TITLE', 'ACTIVE', 'DESCRIPTION', 'LANG',
						'PREVIEW', 'PREVIEW2X', 'PREVIEW3X',
						'DATE_CREATE'
					),
					'filter' => array(
						'=ACTIVE' => 'Y',
						'=TYPE' => $this->arParams['TYPE'],
						'=TPL_TYPE' =>
							($subDir == $this::DEMO_DIR_SITE)
							? Demos::TPL_TYPE_SITE
							: Demos::TPL_TYPE_PAGE,
						$code
							? array(
								'=XML_ID' => $xmlId,
								'=APP_CODE' => $appCode
							)
							: array(
								'=SHOW_IN_LIST' => 'Y'
							),
						Manager::isTemplateIdSystem($siteTemplateId)
							? array(
								'LOGIC' => 'OR',
								['=SITE_TEMPLATE_ID' => $siteTemplateId],
								['=SITE_TEMPLATE_ID' => false]
							)
							: array(
								['=SITE_TEMPLATE_ID' => $siteTemplateId]
							)
					),
					'order' => array(
						'ID' => 'asc'
					)
				));
				while ($row = $res->fetch())
				{
					$lang = $row['LANG'] ? (array)unserialize($row['LANG']) : [];
					if (!$row['APP_CODE'])
					{
						$row['APP_CODE'] = 'local';
					}
					$key = $row['APP_CODE'] . '@' . $row['XML_ID'];
					$data = array(
						$key => $this->translate(array(
							'ID' => $key,
							'XML_ID' => $row['XML_ID'],
							'TYPE' => mb_strtoupper($row['TYPE']),
							'TITLE' => $row['TITLE'],
							'ACTIVE' => $row['ACTIVE'],
							'AVAILABLE' => true,
							'SECTION' => [],//@todo
							'DESCRIPTION' => $row['DESCRIPTION'],
							'SORT' => self::matchFullSort($key, self::DEMO_SORT_SCOPE_REST, $row['DATE_CREATE']->getTimeStamp()),
							'PREVIEW' => $row['PREVIEW'],
							'PREVIEW2X' => $row['PREVIEW2X'],
							'PREVIEW3X' => $row['PREVIEW3X'],
							'APP_CODE' => $row['APP_CODE'],
							'REST' => $row['ID'],
							'LANG' => $lang,
							'TIMESTAMP' => $row['DATE_CREATE']->getTimeStamp(),
							'DESIGNED_BY' => [],
							'DATA' => []
						), $lang)
					) + $data;
				}

				// fill from remote repository
				if ($this->getRemoteTemplates && !$this->isRepo())
				{
					$result = [];
					$http = new HttpClient;

					if ($code)
					{
						if (!array_key_exists($appCode, $applicationItems))
						{
							$applicationItems[$appCode] = [];
							$res = $http->get(
								$this::REMOTE_REPOSITORY_URL . 'landing_cloud.cloud.getAppItems?'
								. 'user_lang=' . LANGUAGE_ID
								. '&appCode=' . $appCode
							);
							if ($res)
							{
								$res = Json::decode($res);
							}
							if (isset($res['result']) && is_array($res['result']))
							{
								foreach ($res['result'] as $item)
								{
									$item['REST'] = $item['ID'];
									unset($item['ID']);
									$applicationItems[$appCode][$item['XML_ID']] = $item;
								}
							}
						}
						if (isset($applicationItems[$appCode][$xmlId]))
						{
							$result[$appCode . '@' . $xmlId] = $applicationItems[$appCode][$xmlId];
						}
					}
					else
					{
						$command = ($subDir == $this::DEMO_DIR_SITE)
									? 'getDemoSiteList'
									: 'getDemoPageList';
						$res = $http->get(
							$this::REMOTE_REPOSITORY_URL . 'landing_cloud.cloud.' . $command . '?'
							. 'user_lang=' . LANGUAGE_ID
							. '&type=' . $this->arParams['TYPE']
							. '&filter[used_in_public]=Y'
							. '&filter[only_rest]=Y'
						);
						if ($res)
						{
							$res = Json::decode($res);
						}
						if (isset($res['result']) && is_array($res['result']))
						{
							$result = $res['result'];
						}
					}
					foreach ($result as $key => $item)
					{
						if (
							!isset($item['REST']) ||
							!isset($item['TITLE'])
						)
						{
							continue;
						}
						$timestamp = isset($item['TIMESTAMP'])
							? $item['TIMESTAMP']
							: 0;
						$data[$key] = [
							'ID' => $key,
							'SORT' => self::matchFullSort($key, self::DEMO_SORT_SCOPE_LOCAL, $timestamp),
							'XML_ID' => isset($item['XML_ID'])
										? $item['XML_ID']
										: '',
							'TYPE' => $this->arParams['TYPE'],
							'TITLE' => $item['TITLE'],
							'ACTIVE' => true,
							'AVAILABLE' => true,
							'SECTION' => [],
							'DESCRIPTION' => isset($item['DESCRIPTION'])
										? $item['DESCRIPTION']
										: '',
							'PREVIEW' => isset($item['PREVIEW'])
										? $item['PREVIEW']
										: '',
							'PREVIEW2X' => isset($item['PREVIEW2X'])
										? $item['PREVIEW2X']
										: '',
							'PREVIEW3X' => isset($item['PREVIEW3X'])
										? $item['PREVIEW3X']
										: '',
							'APP_CODE' => isset($item['APP_CODE'])
										? $item['APP_CODE']
										: '',
							'DATA' => isset($item['MANIFEST'])
										? $item['MANIFEST']
										: [],
							'TIMESTAMP' => $timestamp,
							'DESIGNED_BY' => isset($item['DESIGNED_BY'])
										? $item['DESIGNED_BY']
										: [],
							'REST' => -1 * $item['REST'],
							'LANG' => LANGUAGE_ID
						];
					}
				}
			}

			uasort($data, function($a, $b)
			{
				if ($a['SORT'] != $b['SORT'])
				{
					return $a['SORT'] > $b['SORT'] ? 1 : -1;
				}

				if ($a['TITLE'] === $b['TITLE'])
				{
					return 0;
				}

				return $a['TITLE'] > $b['TITLE'] ? 1 : -1;
			});

			// send events
			$event = new \Bitrix\Main\Event('landing', 'onDemosGetRepository', array(
				'data' => $data
			));
			$event->send();
			foreach ($event->getResults() as $result)
			{
				if ($result->getType() != \Bitrix\Main\EventResult::ERROR)
				{
					if (($modified = $result->getModified()))
					{
						if (isset($modified['data']))
						{
							$data = $modified['data'];
						}
					}
				}
			}
			return $data;
		};

		/**
		 * Attention! method also used into
		 * \Bitrix\Landing\PublicAction\Demos::getFilesList
		 */

		if (!isset($data[$subDir]))
		{
			// system cache begin
			$cache = new \CPHPCache();
			$cacheTime = 86400;
			$cacheStarted = false;
			$siteId = Manager::getMainSiteId();
			$siteTemplateId = Manager::getTemplateId($siteId);
			$cacheId = 'demo_manifest';
			$cacheId .= $subDir . $cacheStarted . $this->arParams['TYPE'];
			$cacheId .= $siteTemplateId . LANGUAGE_ID;

			$extParams = self::getExtDemoPerms();
			foreach($extParams as $param)
			{
				$cacheId .= $param;
			}

			$cachePath = 'landing';
			if ($cache->initCache($cacheTime, $cacheId, $cachePath))
			{
				$data[$subDir] = $cache->getVars();
				return $eventFunc($data[$subDir]);
			}
			if ($cache->startDataCache($cacheTime, $cacheId, $cachePath))
			{
				$cacheStarted = true;
				if (Cache::isCaching())
				{
					Manager::getCacheManager()->startTagCache($cachePath);
					Manager::getCacheManager()->registerTag(self::DEMO_TAG);
				}
			}

			$items = array();
			$data[$subDir] = array();
			$pathLocal = '/bitrix/components/bitrix/landing.demo/data/' . $subDir;//@todo make better
			$path = Manager::getDocRoot() . $pathLocal;
			$siteTypeDef = Site::getDefaultType();
			$siteTypeCurr = $this->arParams['TYPE'];
			$dir = array();

			// read demo from dir
			if (($handle = opendir($path)))
			{
				while ((($entry = readdir($handle)) !== false))
				{
					if ($entry != '.' && $entry != '..')
					{
						$descPath = $path . '/' . $entry . '/.description.php';
						if (file_exists($descPath))
						{
							$dir[] = $entry;
						}
						else if (($handleSubdir = opendir($path . '/' . $entry)))
						{
							while ((($entrySubdir = readdir($handleSubdir)) !== false))
							{
								if ($entrySubdir != '.' && $entrySubdir != '..')
								{
									$descPath = $path . '/' . $entry . '/' . $entrySubdir . '/.description.php';
									if (file_exists($descPath))
									{
										$dir[] = $entry . '/' . $entrySubdir;
									}
								}
							}
						}
					}
				}
			}

			if ($this->isRepo())
			{
				\Bitrix\Main\Localization\Loc::setCurrentLang('bx');
			}

			// and work with this
			foreach ($dir as $entry)
			{

				$itemData = include $path . '/' . $entry . '/.description.php';
				if (!isset($itemData['type']))
				{
					$itemData['type'] = $siteTypeDef;
				}
				$itemData['type'] = array_map('strtoupper', (array)$itemData['type']);
				if (in_array($siteTypeCurr, $itemData['type']) && isset($itemData['name']))
				{
					if (!isset($itemData['fields']) || !is_array($itemData['fields']))
					{
						$itemData['fields'] = array();
					}
					if (!isset($itemData['items']) || !is_array($itemData['items']))
					{
						$itemData['items'] = array();
					}
					if (!isset($itemData['version']))
					{
						$itemData['version'] = 1;
					}
					// predefined
					$itemData['fields']['ACTIVE'] = 'Y';
					if (!isset($itemData['fields']['TITLE']))
					{
						$itemData['fields']['TITLE'] = $itemData['name'];
					}
					if (
						$entry != 'empty' &&
						$siteTemplateId != 'landing24'
					)
					{
						if (
							!isset($itemData['site_template_id']) ||
							$itemData['site_template_id'] != $siteTemplateId
						)
						{
							continue;
						}
					}
					$timestamp = isset($itemData['timestamp']) ? (int)$itemData['timestamp'] : 0;
					$sort = self::matchFullSort(
						$entry,
						($entry === self::DEMO_TEMPLATE_FIRST) ? self::DEMO_SORT_SCOPE_FIRST : self::DEMO_SORT_SCOPE_LOCAL,
						$timestamp,
						$itemData['sort'] ?? 0
					);
					$items[$entry] = array(
						'ID' => $entry,
						'XML_ID' => $entry,
						'TYPE' => $itemData['type'],
						'TITLE' => $itemData['name'],
						'ACTIVE' => isset($itemData['active']) ? $itemData['active'] : true,
						'PUBLICATION' => isset($itemData['publication']) ? $itemData['publication'] : false,
						'AVAILABLE' => isset($itemData['available']) ? $itemData['available'] : true,
						'SINGLETON' =>  isset($itemData['singleton']) ? $itemData['singleton'] : false,
						'SECTION' => isset($itemData['section']) ? (array)$itemData['section'] : [],
						'DESCRIPTION' => isset($itemData['description'])
										? $itemData['description']
										: '',
						'SORT' => $sort,
						'PREVIEW' => file_exists($path . '/' . $entry . '/preview.jpg')
										? Manager::getUrlFromFile($pathLocal . '/' . $entry . '/preview.jpg')
										: '',
						'PREVIEW2X' => file_exists($path . '/' . $entry . '/preview@2x.jpg')
										? Manager::getUrlFromFile($pathLocal . '/' . $entry . '/preview@2x.jpg')
										: '',
						'PREVIEW3X' => file_exists($path . '/' . $entry . '/preview@3x.jpg')
										? Manager::getUrlFromFile($pathLocal . '/' . $entry . '/preview@3x.jpg')
										: '',
						'APP_CODE' => '',
						'REST' => 0,
						'TIMESTAMP' => $timestamp,
						'DESIGNED_BY' => isset($itemData['designed_by']) ? (array)$itemData['designed_by'] : [],
						'DATA' => $itemData
					);
				}
			}

			if ($this->isRepo())
			{
				\Bitrix\Main\Localization\Loc::setCurrentLang(null);
			}

			// available - first
			foreach ($items as $key => $item)
			{
				if ($item['AVAILABLE'])
				{
					$data[$subDir][$key] = $item;
					unset($items[$key]);
				}
			}
			$data[$subDir] += $items;

			// to change template list by additional params
			$data[$subDir] = self::applyExtDemoPerms($data[$subDir]);

			// system cache end
			if ($cacheStarted)
			{
				$cache->endDataCache($data[$subDir]);
				if (Cache::isCaching())
				{
					Manager::getCacheManager()->EndTagCache();
				}
			}
		}

		return $eventFunc($data[$subDir]);
	}

	protected static function getExtDemoPerms(): array
	{
		$params = [];

		$params[] = 'b24partner' . Option::get('landing', 'b24partner', 'N');
		$params[] = 'instagram' . Option::get('crm', 'import_instagram_enabled', 'Y');
		// chats can be exist not always
		if ($chatsEnabled = (Manager::isB24() && ModuleManager::isModuleInstalled('salescenter')))
		{
			$params[] = 'storeChatsY';
		}

		return $params;
	}

	protected static function applyExtDemoPerms(array $data): array
	{
		// not for repo
		// todo: change to isRepo()
		if (defined('LANDING_IS_REPO') && LANDING_IS_REPO === true)
		{
			return $data;
		}

		// templates for PARTNERS
		if (
			Option::get('landing', 'b24partner', 'N') == 'Y' &&
			$partnerId = Option::get('bitrix24', 'partner_id', 0)
		)
		{
			if (isset($data['bitrix24']))
			{
				$data['bitrix24']['DATA']['replace']['#partner_id#'] = $partnerId;
			}
			if (isset($data['sydney']))
			{
				$data['sydney']['DATA']['replace']['#partner_id#'] = $partnerId;
			}
		}
		else
		{
			if (isset($data['bitrix24']))
			{
				unset($data['bitrix24']);
			}
			if (isset($data['sydney']))
			{
				unset($data['sydney']);
			}
		}

		// template for INSTAGRAM store
		if (
			Option::get('crm', 'import_instagram_enabled', 'Y') !== 'Y' &&
			isset($data['store-instagram']))
		{
			unset($data['store-instagram']);
		}

		// template for STORES IN CHAT
		if (!(Manager::isB24() && ModuleManager::isModuleInstalled('salescenter')))
		{
			foreach ($data as $code => $value)
			{
				if (mb_strpos($code, 'store-chats') !== false)
				{
					unset($data[$code]);
				}
			}
		}

		return $data;
	}


	public static function isDemoNew(int $timestamp): bool
	{
		return time() - $timestamp < 86400 * self::LABEL_NEW_PERIOD_DAY;
	}

	protected static function matchFullSort(string $code, int $scope, int $timestamp = null, int $sort = 0): int
	{
		if ($timestamp && self::isDemoNew($timestamp))
		{
			return self::DEMO_SORT_OFFSET * self::DEMO_SORT_SCOPE_NEW;
		}

		if($scope === self::DEMO_SORT_SCOPE_REST)
		{
			if(strpos($code, self::DEMO_NFR_APP) !== false)
			{
				$scope++;
			}

			return self::DEMO_SORT_OFFSET * $scope;
		}

		if($scope === self::DEMO_SORT_SCOPE_LOCAL)
		{
			if(array_key_exists($code, self::DEMO_HOLIDAYS))
			{
				if(self::checkActivePeriod(self::DEMO_HOLIDAYS[$code]))
				{
					$scope = self::DEMO_SORT_SCOPE_HOLIDAYS;
				}
				else
				{
					$scope = self::DEMO_SORT_SCOPE_LAST;
				}
			}

			return self::DEMO_SORT_OFFSET * $scope + $sort;
		}

		return self::DEMO_SORT_OFFSET * $scope;
	}

	/**
	 * Return true if NOW in period
	 *
	 * @param $dateFrom
	 * @param $dateTo
	 * @return bool
	 */
	public static function checkActivePeriod(array $params): bool
	{
		[$monthFrom, $dayFrom, $monthTo, $dayTo] = $params;
		$now = new \DateTime();
		$now->setTime(0,0,0);

		$year = $now->format('Y');
		$dateFrom = new \DateTime($year.'-'.$monthFrom.'-'.$dayFrom);
		$dateTo = new \DateTime($year.'-'.$monthTo.'-'.$dayTo);

		if($now >= $dateFrom && $now <= $dateTo)
		{
			return true;
		}

		return false;
	}

	/**
	 * Gets demo site templates.
	 * @return array
	 */
	public function getDemoSite()
	{
		return $this->getDemo($this::DEMO_DIR_SITE);
	}

	/**
	 * Gets demo page templates.
	 * @param string $code Item code.
	 * @return array
	 */
	public function getDemoPage($code = null)
	{
		return $this->getDemo($this::DEMO_DIR_PAGE, $code);
	}

	/**
	 * Checking site or page activity depending on portal zone
	 * Format:
	 * $zones['ONLY_IN'] - show site only in these zones
	 * $zones['EXCEPT'] - not show site, if zone in this list
	 * @param array $zones Zones array.
	 * @return bool
	 */
	public static function checkActive($zones = array()): bool
	{
		$result = true;
		if (!empty($zones))
		{
			$currentZone = Manager::getZone();

			if (
				isset($zones['ONLY_IN']) &&
				is_array($zones['ONLY_IN']) && !empty($zones['ONLY_IN']) &&
				!in_array($currentZone, $zones['ONLY_IN'])
			)
			{
				$result = false;
			}

			if (
				isset($zones['EXCEPT']) &&
				is_array($zones['EXCEPT']) && !empty($zones['EXCEPT']) &&
				in_array($currentZone, $zones['EXCEPT'])
			)
			{
				$result = false;
			}
		}
		return $result;
	}

	/**
	 * Create some highloadblocks.
	 * @return void
	 */
	public static function createHLblocks()
	{
		if (!Loader::includeModule('highloadblock'))
		{
			return;
		}

		$xmlPath = '/bitrix/components/bitrix/landing.demo/data/xml';

		// demo data
		$sort = 0;
		$colorValues = array();
		$colors = array(
			'PURPLE' => 'colors_files/iblock/0d3/0d3ef035d0cf3b821449b0174980a712.jpg',
			'BROWN' => 'colors_files/iblock/f5a/f5a37106cb59ba069cc511647988eb89.jpg',
			'SEE' => 'colors_files/iblock/f01/f01f801e9da96ae5a7f26aae01255f38.jpg',
			'BLUE' => 'colors_files/iblock/c1b/c1ba082577379bdc75246974a9f08c8b.jpg',
			'ORANGERED' => 'colors_files/iblock/0ba/0ba3b7ecdef03a44b145e43aed0cca57.jpg',
			'REDBLUE' => 'colors_files/iblock/1ac/1ac0a26c5f47bd865a73da765484a2fa.jpg',
			'RED' => 'colors_files/iblock/0a7/0a7513671518b0f2ce5f7cf44a239a83.jpg',
			'GREEN' => 'colors_files/iblock/b1c/b1ced825c9803084eb4ea0a742b2342c.jpg',
			'WHITE' => 'colors_files/iblock/b0e/b0eeeaa3e7519e272b7b382e700cbbc3.jpg',
			'BLACK' => 'colors_files/iblock/d7b/d7bdba8aca8422e808fb3ad571a74c09.jpg',
			'PINK' => 'colors_files/iblock/1b6/1b61761da0adce93518a3d613292043a.jpg',
			'AZURE' => 'colors_files/iblock/c2b/c2b274ad2820451d780ee7cf08d74bb3.jpg',
			'JEANS' => 'colors_files/iblock/24b/24b082dc5e647a3a945bc9a5c0a200f0.jpg',
			'FLOWERS' => 'colors_files/iblock/64f/64f32941a654a1cbe2105febe7e77f33.jpg'
		);
		foreach ($colors as $colorName => $colorFile)
		{
			$sort += 100;
			$colorValues[] = array(
				'UF_NAME' => Loc::getMessage('LANDING_CMP_COLOR_' . $colorName),
				'UF_FILE' =>
					array (
						'name' => mb_strtolower($colorName).'.jpg',
						'type' => 'image/jpeg',
						'tmp_name' => Manager::getDocRoot() . $xmlPath . '/hl/' . $colorFile
					),
				'UF_SORT' => $sort,
				'UF_DEF' => ($sort > 100) ? '0' : '1',
				'UF_XML_ID' => mb_strtolower($colorName)
			);
		}
		$sort = 0;
		$brandValues = array();
		$brands = array(
			'Company1' => 'brands_files/cm-01.png',
			'Company2' => 'brands_files/cm-02.png',
			'Company3' => 'brands_files/cm-03.png',
			'Company4' => 'brands_files/cm-04.png',
			'Brand1' => 'brands_files/bn-01.png',
			'Brand2' => 'brands_files/bn-02.png',
			'Brand3' => 'brands_files/bn-03.png',
		);
		foreach ($brands as $brandName => $brandFile)
		{
			$sort += 100;
			$brandValues[] = array(
				'UF_NAME' => $brandName,
				'UF_FILE' =>
					array (
						'name' => mb_strtolower($brandName).'.jpg',
						'type' => 'image/jpeg',
						'tmp_name' => Manager::getDocRoot() . $xmlPath . '/hl/' . $brandFile
					),
				'UF_SORT' => $sort,
				'UF_XML_ID' => mb_strtolower($brandName)
			);
		}

		// some tables
		$tables = array(
			'eshop_color_reference' => array(
				'name' => 'ColorReference',
				'fields' => array(
					array(
						'FIELD_NAME' => 'UF_NAME',
						'USER_TYPE_ID' => 'string',
						'XML_ID' => 'UF_COLOR_NAME'
					),
					array(
						'FIELD_NAME' => 'UF_FILE',
						'USER_TYPE_ID' => 'file',
						'XML_ID' => 'UF_COLOR_FILE'
					),
					array(
						'FIELD_NAME' => 'UF_SORT',
						'USER_TYPE_ID' => 'double',
						'XML_ID' => 'UF_COLOR_SORT'
					),
					array(
						'FIELD_NAME' => 'UF_DEF',
						'USER_TYPE_ID' => 'boolean',
						'XML_ID' => 'UF_COLOR_DEF'
					),
					array(
						'FIELD_NAME' => 'UF_XML_ID',
						'USER_TYPE_ID' => 'string',
						'XML_ID' => 'UF_XML_ID'
					)
				),
				'values' => $colorValues
			),
			'eshop_brand_reference' => array(
				'name' => 'BrandReference',
				'fields' => array(
					array(
						'FIELD_NAME' => 'UF_NAME',
						'USER_TYPE_ID' => 'string',
						'XML_ID' => 'UF_BRAND_NAME'
					),
					array(
						'FIELD_NAME' => 'UF_FILE',
						'USER_TYPE_ID' => 'file',
						'XML_ID' => 'UF_BRAND_FILE'
					),
					array(
						'FIELD_NAME' => 'UF_SORT',
						'USER_TYPE_ID' => 'double',
						'XML_ID' => 'UF_BRAND_SORT'
					),
					array(
						'FIELD_NAME' => 'UF_XML_ID',
						'USER_TYPE_ID' => 'string',
						'XML_ID' => 'UF_XML_ID'
					)
				),
				'values' => $brandValues
			)
		);

		// create tables and fill with demo-data
		foreach ($tables as $tableName => &$table)
		{
			// if this hl isn't exist
			$res = Highloadblock\HighloadBlockTable::getList(
				array(
					'filter' => array(
						'NAME' => $table['name'],
						'TABLE_NAME' => $tableName
					)
				)
			);
			if (!$res->fetch())
			{
				// add new hl block
				$result = Highloadblock\HighloadBlockTable::add(array(
					'NAME' => $table['name'],
					'TABLE_NAME' => $tableName
				));
				if ($result->isSuccess())
				{
					$sort = 100;
					$tableId = $result->getId();
					// add fields
					$userField  = new \CUserTypeEntity;
					foreach ($table['fields'] as $field)
					{
						$field['ENTITY_ID'] = 'HLBLOCK_' . $tableId;
						$res = \CUserTypeEntity::getList(
							array(),
							array(
								'ENTITY_ID' => $field['ENTITY_ID'],
								'FIELD_NAME' => $field['FIELD_NAME']
							)
						);
						if (!$res->Fetch())
						{
							$field['SORT'] = $sort;
							$userField->Add($field);
							$sort += 100;
						}
					}
					// add data
					$hldata = Highloadblock\HighloadBlockTable::getById($tableId)->fetch();
					$hlentity = Highloadblock\HighloadBlockTable::compileEntity($hldata);
					$entityClass = $hlentity->getDataClass();
					foreach ($table['values'] as $item)
					{
						$entityClass::add($item);
					}
				}
			}
		}
	}

	/**
	 * Create catalog step.
	 * @param string $xmlcode Step code.
	 * @return array
	 */
	private function createCatalogStep($xmlcode)
	{
		$result = [
			'STATUS' => self::STEP_STATUS_ERROR,
			'MESSAGE' => '',
			'FINAL' => true,
			'PROGRESS' => 0
		];

		if (
			!Loader::includeModule('iblock')
			|| !Loader::includeModule('catalog')
		)
		{
			$result['MESSAGE'] = Loc::getMessage('LANDING_CMP_ERROR_MASTER_NO_SERVICE');
			return $result;
		}

		$this->initStepStorage();

		$result['STATUS'] = self::STEP_STATUS_CONTINUE;
		$result['FINAL'] = false;
		switch ($this->getCurrentStep())
		{
			case self::STEP_ID_HIGHLOADBLOCK:
				$this->createHLblocks();
				$this->nextStep();
				$result['MESSAGE'] = Loc::getMessage('LANDING_CMP_LD_MESS_DATA_PREPARE');
				$result['PROGRESS'] = 50;
				break;
			case self::STEP_ID_PREPARE_CRM_CATALOG:
				$this->transferCrmCatalogXmlId();
				$this->nextStep();
				$result['MESSAGE'] = Loc::getMessage('LANDING_CMP_LD_MESS_DATA_PREPARE');
				$result['PROGRESS'] = 100;
				break;
			case self::STEP_ID_XML_IMPORT:
				$importResult = $this->importXmlFile();
				$result['MESSAGE'] = $importResult['MESSAGE'];
				$result['PROGRESS'] = $importResult['PROGRESS'];
				if ($result['PROGRESS'] > 100)
				{
					$result['PROGRESS'] = $result['PROGRESS'] % 100;
				}
				if ($importResult['FINAL'])
				{
					if ($importResult['STATUS'] == self::STEP_STATUS_ERROR)
					{
						$result['STATUS'] = self::STEP_STATUS_ERROR;
						$result['FINAL'] = true;
					}
					else
					{
						$this->nextStep();
					}
				}
				unset($importResult);
				break;
			case self::STEP_ID_ADDITIONAL_UPDATE:
				$this->updateImportedIblocks(
					$this->getXmlIblockId('catalog'),
					$this->getXmlIblockId('catalog_sku')
				);
				$this->nextStep();
				$result['MESSAGE'] = Loc::getMessage('LANDING_CMP_LD_MESS_CATALOG_UPDATE');
				$result['PROGRESS'] = 50;
				break;
			case self::STEP_ID_CATALOG_REINDEX:
				$this->reindexCatalog(
					$this->getXmlIblockId('catalog'),
					$this->getXmlIblockId('catalog_sku')
				);
				$this->nextStep();
				$result['MESSAGE'] = Loc::getMessage('LANDING_CMP_LD_MESS_CATALOG_UPDATE');
				$result['PROGRESS'] = 100;
				break;
			case self::STEP_ID_FINAL:
				$result = [
					'STATUS' => self::STEP_STATUS_COMPLETE,
					'MESSAGE' => Loc::getMessage('LANDING_CMP_LD_MESS_IMPORT_COMPLETE'),
					'FINAL' => true,
					'PROGRESS' => 100
				];
				break;
			default:
				$result['MESSAGE'] = Loc::getMessage('LANDING_CMP_LD_ERROR_UNKNOWN_STEP_ID');
				$result['STATUS'] = self::STEP_STATUS_ERROR;
				$result['FINAL'] = true;
				break;
		}

		if ($result['FINAL'])
		{
			$this->destroyStepStorage();
		}

		return $result;
	}

	/**
	 * Base executable method.
	 * @return void
	 */
	public function executeComponent()
	{
		$init = $this->init();

		if (!$init)
		{
			parent::executeComponent();
			return;
		}

		$this->getRemoteTemplates = true;
		$application = Manager::getApplication();
		set_time_limit(300);
		$this->checkParam('SITE_ID', 0);
		$this->checkParam('TYPE', '');

		\Bitrix\Landing\Hook::setEditMode(true);
		Type::setScope($this->arParams['TYPE']);

		// check access
		if ($this->arParams['SITE_ID'] > 0)
		{
			$hasAccess = Rights::hasAccessForSite(
				$this->arParams['SITE_ID'],
				Rights::ACCESS_TYPES['edit']
			);
		}
		else
		{
			$hasAccess = Rights::hasAdditionalRight(
				Rights::ADDITIONAL_RIGHTS['create']
			);
		}
		if (!$hasAccess)
		{
			$init = false;
			$this->addError('ACCESS_DENIED', '', true);
		}

		// if all ok
		if ($init)
		{
			$this->checkParam('ACTION_FOLDER', 'folderId');
			$this->checkParam('PAGE_URL_SITES', '');
			$this->checkParam('PAGE_URL_LANDING_VIEW', '');
			$this->checkParam('SITE_WORK_MODE', 'N');
			$this->checkParam('DONT_LEAVE_FRAME', 'N');
			$this->checkParam('BINDING_TYPE', '');
			$this->checkParam('BINDING_ID', '');

			if (
				$this->arParams['SITE_ID'] > 0 &&
				$this->arParams['SITE_WORK_MODE'] != 'Y'
			)
			{
				$this->arResult['DEMO'] = $this->getDemoPage();
				$this->arResult['LIMIT_REACHED'] = false;
			}
			else
			{
				$this->arResult['DEMO'] = $this->getDemoSite();
				$this->arResult['LIMIT_REACHED'] = !Manager::checkFeature(
					Manager::FEATURE_CREATE_SITE,
					[
						'type' => $this->arParams['TYPE']
					]
				);
			}

			// check external preview (additional step on preview click)
			$event = new Event('landing', 'onBuildTemplatePreviewUrl');
			$event->send();
			foreach ($event->getResults() as $result)
			{
				if ($result->getType() != EventResult::ERROR)
				{
					if ($externalPreview = $result->getModified())
					{
						foreach ((array)$externalPreview as $code => $url)
						{
							if (isset($this->arResult['DEMO'][$code]))
							{
								$this->arResult['DEMO'][$code]['EXTERNAL_URL'] = $url;
							}
						}
					}
				}
			}

			// may be in future we will use sections,
			// but now sections used only for system things
			foreach ($this->arResult['DEMO'] as $code => $item)
			{
				if ($item['SECTION'] || !$item['ACTIVE'])
				{
					unset($this->arResult['DEMO'][$code]);
				}
			}


			// init nav
			$this->lastNavigation = new \Bitrix\Main\UI\PageNavigation('nav');
			$this->lastNavigation->allowAllRecords(false)
								 ->setPageSize($this::COUNT_PER_PAGE)
								 ->initFromUri();
			// emulate from db
			$res = new \CDBResult;
			$res->initFromArray($this->arResult['DEMO']);
			$res->navStart(
				$this::COUNT_PER_PAGE,
				false,
				$this->lastNavigation->getCurrentPage()
			);
			$this->lastNavigation->setRecordCount(
				count($this->arResult['DEMO'])
			);
			$this->arResult['DEMO'] = [];
			while ($row = $res->fetch())
			{
				$this->arResult['DEMO'][] = $row;
			}
			$this->arResult['NAVIGATION'] = $this->getLastNavigation();
		}

		$action = $this->request('stepper');
		$param = $this->request('param');
		$additional = $this->request('additional');

		$stepper = 'stepper'.$action;
		$stepperFinalUrl = 'stepper'.$action.'FinalUrl';
		if ($action && is_callable([$this, $stepper]) && is_callable([$this, $stepperFinalUrl]))
		{
			if (!check_bitrix_sessid())
			{
				\localRedirect($this->getUri());
			}
			else
			{
				if ($this->request('start') === 'Y')
				{
					$settings = Settings::getDataForSite();
					// if shop section exist, save for site, else make import
					$sectionId = $this->getParentCatalogSectionId($settings['IBLOCK_ID']);
					if ($sectionId !== null)
					{
						$result = [
							'status' => 'final',
							'message' => '',
							'url' => $this->{$stepperFinalUrl}($param, $additional)
						];
						$application->restartBuffer();
						echo Main\Web\Json::encode($result);
						die();
					}
				}
				/** @var array $result */
				$stepperResult = $this->{$stepper}($param, $additional);
				switch ($stepperResult['STATUS'])
				{
					case self::STEP_STATUS_CONTINUE:
						$result = [
							'status' => 'continue',
							'message' => $stepperResult['MESSAGE'],
							'progress' => $stepperResult['PROGRESS']
						];
						$application->restartBuffer();
						echo Main\Web\Json::encode($result);
						die();
						break;
					case self::STEP_STATUS_ERROR:
						$this->addError('CALLBACK_ERROR', $stepperResult['MESSAGE']);
						break;
					case self::STEP_STATUS_COMPLETE:
						$result = [
							'status' => 'final',
							'message' => $stepperResult['MESSAGE'],
							'progress' => $stepperResult['PROGRESS'],
							'url' => $this->{$stepperFinalUrl}($param, $additional)
						];
						$application->restartBuffer();
						echo Main\Web\Json::encode($result);
						die();
						break;
					default:
						\localRedirect($this->getUri());
						break;
				}
			}
		}

		parent::executeComponent();
	}

	private static function changeProductPropertyXmlId(int $iblockId): void
	{
		$list = [
			['CODE' => 'NEWPRODUCT', 'XML_ID' => 'af49d0e309af4fac506a8a228000efc5'],
			['CODE' => 'SALELEADER', 'XML_ID' => '103'],
			['CODE' => 'SPECIALOFFER', 'XML_ID' => '5'],
			['CODE' => 'ARTNUMBER', 'XML_ID' => '10'],
			['CODE' => 'MANUFACTURER', 'XML_ID' => '12'],
			['CODE' => 'MATERIAL', 'XML_ID' => '11'],
			['CODE' => 'COLOR', 'XML_ID' => '291'],
			['CODE' => 'BLOG_POST_ID', 'XML_ID' => '43'],
			['CODE' => 'BLOG_COMMENTS_CNT', 'XML_ID' => '44'],
			['CODE' => 'BACKGROUND_IMAGE', 'XML_ID' => '45']
		];
		self::changePropertyXmlId($iblockId, $list);
		unset($list);
	}

	private static function changeOfferPropertyXmlId(int $iblockId): void
	{
		$list = [
			['CODE' => 'ARTNUMBER', 'XML_ID' => '38'],
			['CODE' => 'SIZES_SHOES', 'XML_ID' => '4510'],
			['CODE' => 'SIZES_CLOTHES', 'XML_ID' => '40'],
			['CODE' => 'MORE_PHOTO', 'XML_ID' => '39']
		];
		self::changePropertyXmlId($iblockId, $list);
		unset($list);
	}

	private static function changePropertyXmlId(int $iblockId, array $list): void
	{
		$xmlCodes = [];
		$iterator = Iblock\PropertyTable::getList([
			'select' => ['ID', 'XML_ID'],
			'filter' => ['=IBLOCK_ID' => $iblockId]
		]);
		while ($row = $iterator->fetch())
		{
			$row['XML_ID'] = (string)$row['XML_ID'];
			$row['ID'] = (int)$row['ID'];
			if ($row['XML_ID'] != '')
			{
				$xmlCodes[$row['XML_ID']] = $row;
			}
		}
		unset($row, $iterator);

		foreach ($list as $property)
		{
			$iterator = Iblock\PropertyTable::getList([
				'select' => ['ID', 'XML_ID', 'CODE'],
				'filter' => [
					'=IBLOCK_ID' => $iblockId,
					'=CODE' => $property['CODE'],
					'=XML_ID' => $property['XML_ID']
				]
			]);
			$row = $iterator->fetch();
			if (!empty($row))
			{
				if (isset($xmlCodes[$property['CODE']]))
				{
					$old = $xmlCodes[$property['XML_ID']];
					$internalResult = Iblock\PropertyTable::update(
						$old['ID'],
						['XML_ID' => $old['XML_ID'].'_OLD']
					);
				}
				$internalResult = Iblock\PropertyTable::update(
					$row['ID'],
					['XML_ID' => $property['CODE']]
				);
				unset($internalResult);
			}
		}
		unset($row, $iterator);
		unset($property);
		unset($xmlCodes);
	}

	/**
	 * Checking iblock by xml id.
	 * @param int $iblockId Iblock id.
	 * @param string $xmlId External code.
	 * @return void
	 */
	private static function checkIblockXmlId($iblockId, $xmlId)
	{
		$iterator = Iblock\IblockTable::getList([
			'select' => ['ID', 'XML_ID'],
			'filter' => ['=ID' => $iblockId]
		]);
		$row = $iterator->fetch();
		unset($iterator);
		if (!empty($row) && $row['XML_ID'] != $xmlId)
		{
			$iblock = new \CIBlock();
			$iblock->Update($iblockId, ['XML_ID' => $xmlId]);
			unset($iblock);
		}
		unset($row);
	}

	/**
	 * Prepare crm iblocks XML_ID for xml import.
	 * @return void
	 */
	private function transferCrmCatalogXmlId()
	{
		if (!Loader::includeModule('crm'))
		{
			return;
		}
		$bitrix24Included = Loader::includeModule('bitrix24');
		$iblockId = (int)\CCrmCatalog::EnsureDefaultExists();
		if ($iblockId > 0)
		{
			self::checkIblockXmlId($iblockId, 'FUTURE-1C-CATALOG');
			if ($bitrix24Included)
			{
				self::changeProductPropertyXmlId($iblockId);
			}
		}
		$catalog = \CCatalogSku::GetInfoByProductIBlock($iblockId);
		if (!empty($catalog))
		{
			self::checkIblockXmlId($catalog['IBLOCK_ID'], 'FUTURE-1C-CATALOG-OFFERS');
			if ($bitrix24Included)
			{
				self::changeOfferPropertyXmlId($catalog['IBLOCK_ID']);
			}
		}
		unset($catalog, $iblockId);
	}

	/**
	 * Set base currency for xml.
	 * @return void
	 */
	private function setXmlBaseCurrency()
	{
		$callback = function(\Bitrix\Main\Event $event)
		{
			static $baseCurrency = null;

			if ($baseCurrency === null)
			{
				$baseCurrency = false;
				if (\Bitrix\Main\Loader::includeModule('currency'))
				{
					$baseCurrency = \Bitrix\Currency\CurrencyManager::getBaseCurrency();
				}
			}

			$result = new \Bitrix\Main\Entity\EventResult;
			$result->modifyFields(array(
				'fields' => array(
					'CURRENCY' => $baseCurrency
				)
			));
			return $result;
		};
		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->addEventHandler(
			'catalog',
			'Bitrix\Catalog\Model\Price::OnBeforeAdd',
			$callback
		);
		$eventManager->addEventHandler(
			'catalog',
			'Bitrix\Catalog\Model\Price::OnBeforeUpdate',
			$callback
		);
	}

	/**
	 * Xml import with internal steps.
	 * @return array
	 */
	private function importXmlFile()
	{
		$result = [
			'STATUS' => self::STEP_STATUS_CONTINUE,
			'MESSAGE' => '',
			'FINAL' => true,
			'PROGRESS' => 0
		];

		$importer = new \CIBlockXmlImport();
		$xmlProductCatalog = 'CRM_PRODUCT_CATALOG';
		$xmlPath = '/bitrix/components/bitrix/landing.demo/data/xml';
		$xml = $this->getCurrentXml();
		$currentZone = Manager::getZone();
		$subDirLng = in_array($currentZone, ['ru', 'kz', 'by']) ? 'ru' : 'en';
		$this->setXmlBaseCurrency();

		$parameters = [
			'FILE' => Manager::getDocRoot() .$xmlPath .'/clothes_' . $subDirLng . '/' . $xml . '.xml',
			'IBLOCK_TYPE' => $xmlProductCatalog,
			'SITE_LIST' => [SITE_ID],
			'MISSING_SECTION_ACTION' => \CIBlockXmlImport::ACTION_NOTHING,
			'MISSING_ELEMENT_ACTION' => \CIBlockXmlImport::ACTION_NOTHING,
			'INTERVAL' => 15,
		];
		$config = [
			'USE_CRC' => false,
			'PREVIEW_PICTURE_SETTINGS' => false,
			'DETAIL_PICTURE_SETTINGS' => false
		];

		$importer->init($parameters, $config);
		if (!$importer->isSuccess())
		{
			$result['STATUS'] = self::STEP_STATUS_ERROR;
			$result['MESSAGE'] = implode("\n", $importer->getErrors());
			return $result;
		}

		$importer->run();
		$importResult = $importer->getStepResult();
		if ($importResult['TYPE'] == \CIBlockXmlImport::RESULT_TYPE_SUCCESS)
		{
			if ($importResult['IS_FINAL'] == 'Y')
			{
				$this->setXmlIblockId($xml, $importer->getIblockId());
				$nextXml = $this->getNextXml();
				if ($nextXml === null)
				{
					$result = [
						'STATUS' => self::STEP_STATUS_COMPLETE,
						'MESSAGE' => Loc::getMessage('LANDING_CMP_LD_MESS_XML_IMPORT_COMPLETE'),
						'FINAL' => true,
						'PROGRESS' => 100
					];
					$this->resetStepParameters();
				}
				else
				{
					$this->setCurrentXml($nextXml);
					$result['MESSAGE'] = $this->importXmlFileResultMessage($xml, true);
					$result['FINAL'] = false;
					$result['PROGRESS'] = 100;
				}
			}
			else
			{
				$result['MESSAGE'] = $this->importXmlFileResultMessage($xml, false);
				$result['PROGRESS'] = $this->getCurrentXmlProgress();
				$result['FINAL'] = false;
			}
		}
		else
		{
			$result['STATUS'] = self::STEP_STATUS_ERROR;
			$result['MESSAGE'] = $importResult['ERROR'];
		}
		unset($importResult);
		unset($importer);

		return $result;
	}

	/**
	 * Internal updates.
	 * @param int $parentIblock Parent iblock id.
	 * @param int $offerIblock Offers iblock id.
	 * @return void
	 */
	private function updateImportedIblocks($parentIblock, $offerIblock)
	{
		// link iblocks
		$propertyId = \CIBlockPropertyTools::createProperty(
			$offerIblock,
			\CIBlockPropertyTools::CODE_SKU_LINK,
			array('LINK_IBLOCK_ID' => $parentIblock)
		);

		$res = \CCatalog::getList([], ['IBLOCK_ID' => $parentIblock], false, false, ['IBLOCK_ID']);
		$row = $res->Fetch();
		if (empty($row))
		{
			\CCatalog::add(array(
				'IBLOCK_ID' => $parentIblock,
				'PRODUCT_IBLOCK_ID' => 0,
				'SKU_PROPERTY_ID' => 0
			));
		}

		$res = \CCatalog::getList([], ['IBLOCK_ID' => $offerIblock], false, false, ['IBLOCK_ID']);
		if ($res->fetch())
		{
			\CCatalog::update(
				$offerIblock,
				array(
					'PRODUCT_IBLOCK_ID' => $parentIblock,
					'SKU_PROPERTY_ID' => $propertyId
				)
			);
		}
		else
		{
			\CCatalog::add(array(
				'IBLOCK_ID' => $offerIblock,
				'PRODUCT_IBLOCK_ID' => $parentIblock,
				'SKU_PROPERTY_ID' => $propertyId
			));
		}
		unset($res);

		// additional updates -- common
		foreach (array($parentIblock, $offerIblock) as $iblockId)
		{
			// uniq code
			$defValueCode = array (
				'UNIQUE' => 'N',
				'TRANSLITERATION' => 'Y',
				'TRANS_LEN' => 100,
				'TRANS_CASE' => 'L',
				'TRANS_SPACE' => '_',
				'TRANS_OTHER' => '_',
				'TRANS_EAT' => 'Y',
				'USE_GOOGLE' => 'N'
			);
			$iblock = new \CIBlock;
			$iblock->update($iblockId, array(
				'FIELDS' => array(
					'CODE' => array (
						'IS_REQUIRED' => 'N',
						'DEFAULT_VALUE' => $defValueCode
					),
					'SECTION_CODE' => array (
						'IS_REQUIRED' => 'N',
						'DEFAULT_VALUE' => $defValueCode
					)
				)
			));
		}
	}

	/**
	 * Reindex catalog.
	 * @param int $parentIblock Parent iblock id.
	 * @param int $offerIblock Offers iblock id.
	 * @return void
	 */
	private function reindexCatalog($parentIblock, $offerIblock)
	{
		$parentSectionId = $this->getParentCatalogSectionId($parentIblock);
		if ($parentSectionId === null)
		{
			return;
		}
		$iterator = \CIBlockElement::GetList(
			['ID' => 'ASC'],
			[
				'IBLOCK_ID' => $parentIblock,
				'SECTION_ID' => $parentSectionId, 'INCLUDE_SUBSECTIONS' => 'Y',
				'CHECK_PERMISSIONS' => 'N'
			],
			false,
			['nTopCount' => 1],
			['ID', 'IBLOCK_ID']
		);
		$firstElement = $iterator->Fetch();
		unset($iterator);
		if (empty($firstElement))
		{
			return;
		}
		$borderId = (int)$firstElement['ID'] - 1;

		$iblockId = $offerIblock;
		$count = \Bitrix\Iblock\ElementTable::getCount(array(
			'=IBLOCK_ID' => $iblockId,
			'=WF_PARENT_ELEMENT_ID' => null
		));
		if ($count > 0)
		{
			$catalogReindex = new \CCatalogProductAvailable('', 0, 0);
			$catalogReindex->initStep($count, 0, 0);
			$catalogReindex->setParams(array(
				'IBLOCK_ID' => $iblockId
			));
			$catalogReindex->run();
		}

		// update only for catalog - some magic
		$iblockId = $parentIblock;
		$count = \Bitrix\Iblock\ElementTable::getCount(array(
			'=IBLOCK_ID' => $iblockId,
			'=WF_PARENT_ELEMENT_ID' => null,
			'>ID' => $borderId
		));
		if ($count > 0)
		{
			$catalogReindex = new \CCatalogProductAvailable('', 0, 0);
			$catalogReindex->initStep($count, 0, $borderId);
			$catalogReindex->setParams(array(
				'IBLOCK_ID' => $iblockId
			));
			$catalogReindex->run();
		}
	}

	/**
	 * Init steps in session.
	 * @return void
	 */
	private function initStepStorage()
	{
		if (
			!isset($_SESSION['LANDING_DEMO_STORAGE']) ||
			!is_array($_SESSION['LANDING_DEMO_STORAGE'])
		)
		{
			$_SESSION['LANDING_DEMO_STORAGE'] = [
				'STEP_ID' => self::STEP_ID_HIGHLOADBLOCK,
				'XML_LIST' => [
					'catalog',
					'catalog_prices',
					'catalog_sku',
					'catalog_prices_sku'
				],
				'IBLOCK_ID' => [
					'catalog' => 0,
					'catalog_prices' => 0,
					'catalog_sku' => 0,
					'catalog_prices_sku' => 0
				],
				'STEP_PARAMETERS' => []
			];
		}
	}

	/**
	 * Gets current step from session.
	 * @return mixed
	 */
	private function getCurrentStep()
	{
		return $_SESSION['LANDING_DEMO_STORAGE']['STEP_ID'];
	}

	/**
	 * Set current step to session.
	 * @param mixed $step Step.
	 * @return void
	 */
	private function setCurrentStep($step)
	{
		$_SESSION['LANDING_DEMO_STORAGE']['STEP_ID'] = $step;
	}

	/**
	 * Go to the next step.
	 * @return void
	 */
	private function nextStep()
	{
		$index = array_search($this->getCurrentStep(), $this->catalogStepList);
		if (isset($this->catalogStepList[$index+1]))
		{
			$this->setCurrentStep($this->catalogStepList[$index+1]);
		}
	}

	/**
	 * Reset step params.
	 * @return void
	 */
	private function resetStepParameters()
	{
		$_SESSION['LANDING_DEMO_STORAGE']['STEP_PARAMETERS'] = [];
	}

	/**
	 * Get current xml.
	 * @return mixed
	 */
	private function getCurrentXml()
	{
		if (!isset($_SESSION['LANDING_DEMO_STORAGE']['STEP_PARAMETERS']['CURRENT_XML']))
		{
			$_SESSION['LANDING_DEMO_STORAGE']['STEP_PARAMETERS']['CURRENT_XML'] = [
				'CODE' => 'catalog',
				'PROGRESS' => 0
			];
		}
		return $_SESSION['LANDING_DEMO_STORAGE']['STEP_PARAMETERS']['CURRENT_XML']['CODE'];
	}

	/**
	 * Gets next xml.
	 * @return mixed
	 */
	private function getNextXml()
	{
		$index = array_search($this->getCurrentXml(), $_SESSION['LANDING_DEMO_STORAGE']['XML_LIST']);
		if (isset($_SESSION['LANDING_DEMO_STORAGE']['XML_LIST'][$index+1]))
		{
			return $_SESSION['LANDING_DEMO_STORAGE']['XML_LIST'][$index+1];
		}
		else
		{
			return null;
		}
	}

	/**
	 * Set current xml.
	 * @param mixed $xmlId External code.
	 * @return void
	 */
	private function setCurrentXml($xmlId)
	{
		$_SESSION['LANDING_DEMO_STORAGE']['STEP_PARAMETERS']['CURRENT_XML']['CODE'] = $xmlId;
		$_SESSION['LANDING_DEMO_STORAGE']['STEP_PARAMETERS']['CURRENT_XML']['PROGRESS'] = 0;
	}

	/*
	 * Gets current xml in progress.
	 * @return mixed
	 */
	private function getCurrentXmlProgress()
	{
		$_SESSION['LANDING_DEMO_STORAGE']['STEP_PARAMETERS']['CURRENT_XML']['PROGRESS'] += 5;
		return $_SESSION['LANDING_DEMO_STORAGE']['STEP_PARAMETERS']['CURRENT_XML']['PROGRESS'];
	}

	/**
	 * Gets xml of iblock id from session.
	 * @param string $xmlId External code.
	 * @return mixed
	 */
	private function getXmlIblockId($xmlId)
	{
		return $_SESSION['LANDING_DEMO_STORAGE']['IBLOCK_ID'][$xmlId];
	}

	/**
	 * Set xml of iblock id to session.
	 * @param string $xmlId External code.
	 * @param int $iblockId Iblock id.
	 * @return void
	 */
	private function setXmlIblockId($xmlId, $iblockId)
	{
		$_SESSION['LANDING_DEMO_STORAGE']['IBLOCK_ID'][$xmlId] = $iblockId;
	}

	/**
	 * Gets result message for different steps.
	 * @param string $xmlId External code.
	 * @param bool $complete Complete step.
	 * @return string
	 */
	private function importXmlFileResultMessage($xmlId, $complete = true)
	{
		$result = '';
		if ($complete)
		{
			switch ($xmlId)
			{
				case 'catalog':
					$result = Loc::getMessage('LANDING_CMP_LD_MESS_XML_IMPORT_CATALOG_COMPLETE');
					break;
				case 'catalog_prices':
					$result = Loc::getMessage('LANDING_CMP_LD_MESS_XML_IMPORT_CATALOG_PRICES_COMPLETE');
					break;
				case 'catalog_sku':
					$result = Loc::getMessage('LANDING_CMP_LD_MESS_XML_IMPORT_CATALOG_OFFERS_COMPLETE');
					break;
				case 'catalog_prices_sku':
					$result = Loc::getMessage('LANDING_CMP_LD_MESS_XML_IMPORT_CATALOG_OFFER_PRICES_COMPLETE');
					break;
			}
		}
		else
		{
			switch ($xmlId)
			{
				case 'catalog':
					$result = Loc::getMessage('LANDING_CMP_LD_MESS_XML_IMPORT_CATALOG_PROGRESS');
					break;
				case 'catalog_prices':
					$result = Loc::getMessage('LANDING_CMP_LD_MESS_XML_IMPORT_CATALOG_PRICES_PROGRESS');
					break;
				case 'catalog_sku':
					$result = Loc::getMessage('LANDING_CMP_LD_MESS_XML_IMPORT_CATALOG_OFFERS_PROGRESS');
					break;
				case 'catalog_prices_sku':
					$result = Loc::getMessage('LANDING_CMP_LD_MESS_XML_IMPORT_CATALOG_OFFER_PRICES_PROGRESS');
					break;
			}
		}
		return $result;
	}

	/**
	 * Clear session vars.
	 * @return void.
	 */
	private function destroyStepStorage()
	{
		if (array_key_exists('LANDING_DEMO_STORAGE', $_SESSION))
		{
			unset($_SESSION['LANDING_DEMO_STORAGE']);
		}
	}

	/**
	 * Gets parent section id for iblock id, if exists.
	 * @param int $iblockId Iblock id.
	 * @param string|null $xmlId Parent section external code.
	 * @return int|null
	 */
	private function getParentCatalogSectionId($iblockId, $xmlId = null)
	{
		if (empty($xmlId))
		{
			$xmlId = '666';
		}
		$iterator = Iblock\SectionTable::getList([
			'select' => ['ID'],
			'filter' => [
				'=IBLOCK_ID' => $iblockId,
				'=XML_ID' => $xmlId,
			]
		]);
		$row = $iterator->fetch();
		unset($iterator);
		return (!empty($row) ? (int)$row['ID'] : null);
	}
}
