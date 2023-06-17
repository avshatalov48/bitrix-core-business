<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Landing\Site;
use Bitrix\Landing\Landing;
use Bitrix\Landing\Manager;
use Bitrix\Landing\Syspage;
use Bitrix\Landing\Demos;
use Bitrix\Landing\Template;
use Bitrix\Landing\TemplateRef;
use Bitrix\Landing\Rights;
use Bitrix\Landing\Landing\Cache;
use Bitrix\Landing\Hook\Page\Settings;
use Bitrix\Landing\Site\Type;
use Bitrix\Highloadblock;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Bitrix\Main\UI\Filter;
use Bitrix\Iblock;
use Bitrix\Main;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use Bitrix\Crm\Integration\UserConsent;
use Bitrix\Main\Web\Uri;
use Bitrix\Rest\Marketplace\Client;

\CBitrixComponent::includeComponentClass('bitrix:landing.base');

class LandingSiteDemoComponent extends LandingBaseComponent
{
	/**
	 * Count items per page.
	 */
	const COUNT_PER_PAGE = 29;

	// /**
	//  * Days count during which templates marked as 'new'.
	//  */
	// const LABEL_NEW_PERIOD_DAY = 120;

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

	const DEMO_TYPES_FILTER = [
		'PAGE' => 'page',
		'STORE' => 'shop',
		'KNOWLEDGE' => 'knowledgeBase',
	];

	protected const FILTER_ID = 'LANDING_FLT_DEMO';

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
	const STEP_ID_CREATE_CATALOG = 'CREATE_CATALOG';
	const STEP_ID_HIGHLOADBLOCK = 'CREATE_HIGHLOADBLOCK';
	const STEP_ID_PREPARE_CRM_CATALOG = 'CRM_CATALOG';
	const STEP_ID_XML_IMPORT = 'XML_IMPORT';
	const STEP_ID_ADDITIONAL_UPDATE = 'ADDITIONAL';
	const STEP_ID_REINDEX = 'REINDEX';
	const STEP_ID_CATALOG_REINDEX = 'CATALOG_REINDEX';
	const STEP_ID_FINAL = 'FINAL';
	private $catalogStepList = [
		//self::STEP_ID_CREATE_CATALOG,
		self::STEP_ID_HIGHLOADBLOCK,
		self::STEP_ID_PREPARE_CRM_CATALOG,
		self::STEP_ID_XML_IMPORT,
		self::STEP_ID_ADDITIONAL_UPDATE,
		self::STEP_ID_CATALOG_REINDEX,
		self::STEP_ID_FINAL
	];

	private const IMPORT_CATALOG_ERROR_ID = 'IMPORT_CATALOG_DATA';
	private const IBLOCK_AUTODETECT = 'AUTODETECT';

	public const SHOWCASE_CLOTHES = 'clothes';
	public const SHOWCASE_FASHION = 'fashion';
	public const SHOWCASE_DEFAULT = self::SHOWCASE_CLOTHES;

	private $showcaseList = [];
	/** @var string */
	private $showcaseId;
	/** @var array */
	private $showcase;

	/**
	 * On template creation we can add callbacks from white list
	 */
	protected const ALLOWED_CALLBACKS = [
		'\Bitrix\Landing\Subtype\Form::setSpecialFormToBlock',
	];

	/**
	 * First call createPage.
	 * @var bool
	 */
	protected $firstCreatePage = true;

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

	// Needed to import demo data.
	/** @var bool */
	protected $bitrix24Included;
	/** @var bool */
	protected $iblockIncluded;
	/** @var bool */
	protected $catalogIncluded;
	/** @var bool */
	protected $crmIncluded;


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
			'skip_blocks' => true,
			'check_permissions' => false,
		]);
		if ($landing->exist())
		{
			$siteId = $landing->getSiteId();
			$redirect = str_replace(
				array('#site_show#', '#landing_edit#'),
				array($siteId, $landingId),
				$this->arParams['PAGE_URL_LANDING_VIEW']
			);
			$uriEdit = new Uri($redirect);
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

		// todo: theme_custom_color now is not using, can remove?
		if ($this->request('theme') || $this->request('theme_custom_color'))
		{
			$color = $this->request('theme_custom_color') ?: $this->request('theme');
			$color = $this->prepareColor($color);
			unset($data['ADDITIONAL_FIELDS']['THEME_CODE']);
			$data['ADDITIONAL_FIELDS']['THEME_COLOR'] = $color;

			if ($this->arParams['SITE_ID'] > 0)
			{
				$data['ADDITIONAL_FIELDS']['THEME_USE'] = 'Y';
			}
			else
			{
				$data['ADDITIONAL_FIELDS']['THEME_USE'] = 'N';
			}
		}

		if ($this->request('theme_use_site'))
		{
			$color = $this->request('theme_use_site');
			$color = $this->prepareColor($color);
			unset($data['ADDITIONAL_FIELDS']['THEME_CODE']);
			$data['ADDITIONAL_FIELDS']['THEME_COLOR'] = $color;
			$data['ADDITIONAL_FIELDS']['THEME_USE'] = 'N';
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

		if ($this->request('theme') || $this->request('theme_custom_color'))
		{
			$color = $this->request('theme_custom_color') ?: $this->request('theme');
			$color = $this->prepareColor($color);
			unset($data['ADDITIONAL_FIELDS']['THEME_CODE']);
			$data['ADDITIONAL_FIELDS']['THEME_COLOR'] = $color;
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
			$manifest = unserialize($row['MANIFEST'], ['allowed_classes' => false]);
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
				$this->firstCreatePage
			);
			$pageData = $this->prepareAdditionalFieldsPage(
				$pageData,
				$this->firstCreatePage
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
			else if ($this->arParams['FOLDER_ID'])
			{
				$pageData['FOLDER_ID'] = $this->arParams['FOLDER_ID'];
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
					if ($this->arParams['DONT_LEAVE_FRAME'] === 'Y' && $pageData['FOLDER_ID'])
					{
						$indexEmpty = \Bitrix\Landing\Folder::getList([
							'select' => [
								'ID'
							],
							'filter' => [
								'ID' => $pageData['FOLDER_ID'],
								'INDEX_ID' => false
							]
						])->fetch();
						if ($indexEmpty)
						{
							Site::updateFolder(
								$landing->getSiteId(),
								$pageData['FOLDER_ID'],
								['INDEX_ID' => $landing->getId()]
							);
						}
					}
					if ($demo[$code]['LOCK_DELETE'] ?? false)
					{
						\Bitrix\Landing\Lock::lockDeleteLanding($landingId);
					}
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
						$manifest = $block->getManifest();
						if (!$manifest)
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
								if (
									isset($newData['style']) && is_array($newData['style']) &&
									(
										!empty($manifest['style']['block']) ||
										!empty($manifest['style']['nodes'])
									)
								)
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
								// callbacks
								if (isset($newData['callbacks']))
								{
									if (
										isset($newData['callbacks']['afterAdd']['method'])
										&& is_callable($newData['callbacks']['afterAdd']['method'])
										&& in_array(
											$newData['callbacks']['afterAdd']['method'],
											self::ALLOWED_CALLBACKS,
											true
										)
									)
									{
										array_unshift($newData['callbacks']['afterAdd']['params'], $block);
										call_user_func_array(
											$newData['callbacks']['afterAdd']['method'],
											$newData['callbacks']['afterAdd']['params']
										);
									}
								}
							}
						}
						else if ($data['version'] == 2)
						{
							if (isset($data['items'][$blocks[$k]]))
							{
								$newData = $data['items'][$blocks[$k]];
								// adjustParams cards
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
					$this->firstCreatePage = false;
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

		$url = '';
		//template data
		if (isset($template['PREVIEW_URL']))
		{
			$url = $template['PREVIEW_URL'];
		}

		// first detect from rest
		elseif (($codePos = mb_strrpos($code, '@')) !== false)
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
				$url = $row['PREVIEW_URL'];
			}
		}

		// default from preview.bitrix24.site
		else
		{
			if (isset($template['DATA']['parent']))
			{
				$code = $template['DATA']['parent'];
			}
			$code = str_replace('/', '_', $code);
			if (
				$this->arParams['TYPE'] == Type::SCOPE_CODE_KNOWLEDGE
				|| $this->arParams['TYPE'] == Type::SCOPE_CODE_GROUP
			)
			{
				$previewSubDir = '/pub/kb/';
			}
			else
			{
				$previewSubDir = '/pub/site/';
			}
			$url = 'https://preview.bitrix24.site' . $previewSubDir . $code;
		}

		$availableLangs = ['ru', 'de', 'en', 'br', 'fr', 'la', 'pl', 'ua'];
		$lang = in_array(LANGUAGE_ID, $availableLangs, true) ? LANGUAGE_ID : 'en';
		$uri = new Uri($url);
		$uri->addParams(['user_lang' => $lang]);

		return $uri->getUri();
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
			$siteData['LANG'] = Manager::getZone();
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
					$this->initShowcaseList();
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

					// agreement
					if(!$settings['AGREEMENT_ID'] && Loader::includeModule('crm'))
					{
						$siteData['ADDITIONAL_FIELDS']['SETTINGS_AGREEMENT_ID'] = UserConsent::getDefaultAgreementId();
					}
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
				$siteData['ADDITIONAL_FIELDS']['SETTINGS_USE_ENHANCED_ECOMMERCE'] = 'N';
				$res = Site::add($siteData);
				Manager::disableFeatureTmp(
					Manager::FEATURE_CREATE_SITE
				);
				$siteId = $res->getId();
				$this->bindingSite($siteId);
			}
			if ($siteId)
			{
				\Bitrix\Landing\Rights::setGlobalOff();
				$siteData['ID'] = $siteId;
				$forSiteUpdate = array();
				$firstLandingId = false;
				if ($demo[$code]['LOCK_DELETE'] ?? false)
				{
					\Bitrix\Landing\Lock::lockDeleteSite($siteId);
				}
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
				Landing::disableCheckUniqueAddress();
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
				Landing::enableCheckUniqueAddress();
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
				if (isset($landings[$pageIndex]) && count($landings) > 1)
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
				if (isset($data['folders']) && is_array($data['folders']))
				{
					foreach ($data['folders'] as $folderCode => $folderPages)
					{
						if (!is_array($folderPages))
						{
							continue;
						}
						if (!in_array($folderCode, $folderPages))
						{
							$folderPages[] = $folderCode;
						}
						// create folder
						$folderId = null;
						$folderIndexId = null;
						if (isset($landings[$folderCode]))
						{
							$res = Landing::getList([
								'select' => [
									'TITLE', 'SITE_ID', 'ID'
								],
								'filter' => [
									'ID' => $landings[$folderCode]
								]
							]);
							if ($page = $res->fetch())
							{
								$folderIndexId = $page['ID'];
								$folderId = Site::addFolder($page['SITE_ID'], [
									'TITLE' => $page['TITLE']
								])->getId();
							}
						}
						if (!$folderId)
						{
							continue;
						}
						// set pages to the folder
						foreach ($folderPages as $pageCode)
						{
							if (isset($landings[$pageCode]))
							{
								Landing::update($landings[$pageCode], array(
									'FOLDER_ID' => $folderId
								));
								if ($folderIndexId == $landings[$pageCode])
								{
									\Bitrix\Landing\Folder::update($folderId, [
										'INDEX_ID' => $folderIndexId
									]);
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
				\Bitrix\Landing\Rights::setGlobalOn();
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
	private static function checkAllowDemoData(array $demo): bool
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
	 * @param mixed $additional
	 * @return array
	 */
	private function stepperStore(string $code, $additional = null): array
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
			$config = [];
			if (!empty($additional) && is_array($additional))
			{
				$showcaseId = $additional['SHOWCASE_ID'] ?? null;
				if (is_string($showcaseId))
				{
					$this->setCurrentShowcaseId($showcaseId);
				}
				$iblockId = (int)$additional['IBLOCK_ID'] ?? 0;
				if ($iblockId > 0)
				{
					$config['IBLOCK_ID'] = $iblockId; //TODO: no working now
				}
			}
			$internalResult = $this->createCatalogStep($code, $config);
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
	private function stepperStoreFinalUrl($code, $additional = null): string
	{
		$uriSelect = new Uri($this->currentRequest->getRequestUri());

		$params = array(
			'action' => 'select',
			'param' => $code,
			'sessid' => bitrix_sessid(),
			'additional' => [
				'section' => $this->getCurrentShowcaseSectionXmlId(),
			]
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
			// region modify data
			if (!empty($data))
			{
				// todo: need check singleton?
				if ($subDir === self::DEMO_DIR_SITE)
				{
					// make some items disable, if they are singleton
					$res = Site::getList([
						'select' => [
							'XML_ID', 'TPL_CODE'
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
						if (!$row['TPL_CODE'])
						{
							if (mb_strpos($row['XML_ID'], '|') !== false)
							{
								[, $row['TPL_CODE']] = explode('|', $row['XML_ID']);
							}
						}
						if (
							isset($data[$row['TPL_CODE']]) &&
							$data[$row['TPL_CODE']]['SINGLETON']
						)
						{
							$data[$row['TPL_CODE']]['AVAILABLE'] = false;
						}
						if (
							isset($data[$row['TPL_CODE']]['DATA']['site_group_item']) &&
							$data[$row['TPL_CODE']]['DATA']['site_group_item'] === 'Y' &&
							isset($data[$row['TPL_CODE']]['DATA']['site_group_parent']) &&
							($siteGroupParent = $data[$row['TPL_CODE']]['DATA']['site_group_parent']) &&
							isset($data[$siteGroupParent]) &&
							$data[$siteGroupParent]['SINGLETON']
						)
						{
							$data[$siteGroupParent]['AVAILABLE'] = false;
						}
					}
					unset($res, $row);
				}

				if ($code)
				{
					$data = [$code => $data[$code] ?? null];
				}
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
			// endregion

			// region fill from APPs
			if (!($this->arResult['IS_SEARCH'] ?? false))
			{
				$res = Demos::getList([
					'select' => [
						'ID', 'APP_CODE', 'XML_ID', 'TYPE',
						'TITLE', 'ACTIVE', 'DESCRIPTION', 'LANG',
						'PREVIEW', 'PREVIEW2X', 'PREVIEW3X',
						'DATE_CREATE',
					],
					'filter' => [
						'=ACTIVE' => 'Y',
						'=TYPE' => $this->arParams['TYPE'],
						'=TPL_TYPE' =>
							($subDir == $this::DEMO_DIR_SITE)
								? Demos::TPL_TYPE_SITE
								: Demos::TPL_TYPE_PAGE,
						$code
							? [
							'=XML_ID' => $xmlId,
							'=APP_CODE' => $appCode,
						]
							: [
							'=SHOW_IN_LIST' => 'Y',
						],
						Manager::isTemplateIdSystem($siteTemplateId)
							? [
							'LOGIC' => 'OR',
							['=SITE_TEMPLATE_ID' => $siteTemplateId],
							['=SITE_TEMPLATE_ID' => false],
						]
							: [
							['=SITE_TEMPLATE_ID' => $siteTemplateId],
						],
					],
					'order' => [
						'ID' => 'asc',
					],
				]);
				while ($row = $res->fetch())
				{
					$lang = $row['LANG'] ? (array)unserialize($row['LANG'], ['allowed_classes' => false]) : [];
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
								// 'SORT' => self::matchFullSort($key, self::DEMO_SORT_SCOPE_REST, $row['DATE_CREATE']->getTimeStamp()),
								'PREVIEW' => $row['PREVIEW'],
								'PREVIEW2X' => $row['PREVIEW2X'],
								'PREVIEW3X' => $row['PREVIEW3X'],
								'APP_CODE' => $row['APP_CODE'],
								'REST' => $row['ID'],
								'LANG' => $lang,
								'TIMESTAMP' => $row['DATE_CREATE']->getTimeStamp(),
								'DESIGNED_BY' => [],
								'DATA' => [],
							), $lang),
						) + $data;
				}
			}
			//endregion

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
			$cacheTime = 3600;
			$cacheStarted = false;
			$siteId = Manager::getMainSiteId();
			$siteTemplateId = Manager::getTemplateId($siteId);
			$cacheId = 'demo_manifest';
			$cacheId .= $subDir . $cacheStarted . $this->arParams['TYPE'];
			$cacheId .= $siteTemplateId . LANGUAGE_ID;
			$cacheId .= $code ?? 'all';
			$cacheId .= ($this->arParams['SITE_ID'] ?? 0) > 0 ? 'onePage' : 'multiPage';
			$cacheId .= $this->getFilterToString();

			$navigation = $this->getLastNavigation();
			$cacheId .= $navigation ? $navigation->getCurrentPage() : 1;

			$extParams = self::getExtDemoPerms();
			foreach($extParams as $param)
			{
				$cacheId .= $param;
			}

			// market checks
			$marketPrefix = 'market';
			$marketIdDelimiter = '/';
			$isNeedMarket =
				($this->arParams['SKIP_REMOTE'] ?? 'N') !== 'Y'
				&& (!$code || mb_strpos($code, $marketPrefix . $marketIdDelimiter) === 0)
				&& $this->arParams['TYPE'] === 'PAGE'
			;
			if ($isNeedMarket)
			{
				$hasMarket =
					Loader::includeModule('rest')
					&& is_callable(['\Bitrix\Rest\Marketplace\Client', 'getSiteList'])
				;
				if (!$hasMarket)
				{
					$this->setErrors(new Bitrix\Main\Error(Loc::getMessage('LANDING_TPL_REPO_NOT_INSTALL')));
				}
			}
			$cacheId .= ($isNeedMarket && $hasMarket) ? 'Market_v2' : 'NoMarket';

			// nfr - without cache
			$cachePath = 'landing/demo';
			if (
				$cache->initCache($cacheTime, $cacheId, $cachePath)
				&& (int)Option::get('bitrix24', 'partner_id', 0) === 0
			)
			{
				$data = $cache->getVars();
				$navigation->setRecordCount($data['navigation']['recordCount'] ?? 0);

				if (!empty($data[$subDir]))
				{
					return $eventFunc($data[$subDir]);
				}
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

			$data[$subDir] = [];
			$data['navigation'] = ['recordCount' => 0];
			$siteTypeDef = Site::getDefaultType();
			$siteTypeCurr = $this->arParams['TYPE'];

			// region get LOCAL
			if (!($this->arResult['IS_SEARCH'] ?? false))
			{
				$pathLocal = '/bitrix/components/bitrix/landing.demo/data/' . $subDir;//@todo make better
				$path = Manager::getDocRoot() . $pathLocal;
				$localDirectories = [];
				$localTemplates = [
					'empty',
					'empty-multipage',
					'wiki-dark',
					'wiki-light',
					'store_v3',
					'store-chats-dark',
					'clothes',
					'store-instagram',
					'store-mini-catalog',
					'store-mini-one-element',
					'search-result',
					'search-result2',
					'search-result3-dark',
					'news-detail',
				];
				foreach ($localTemplates as $template)
				{
					$descPath = $path . '/' . $template . '/.description.php';
					if (file_exists($descPath))
					{
						$localDirectories[] = $template;
					}
					elseif (($handleSubdir = opendir($path . '/' . $template)))
					{
						while ((($entrySubdir = readdir($handleSubdir)) !== false))
						{
							if ($entrySubdir != '.' && $entrySubdir != '..')
							{
								$descPath = $path . '/' . $template . '/' . $entrySubdir . '/.description.php';
								if (file_exists($descPath))
								{
									$localDirectories[] = $template . '/' . $entrySubdir;
								}
							}
						}
					}
				}

				foreach ($localDirectories as $dir)
				{
					$itemData = include $path . '/' . $dir . '/.description.php';
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
						$data[$subDir][$dir] = array(
							'ID' => $dir,
							'XML_ID' => $dir,
							'TYPE' => $itemData['type'],
							'TITLE' => $itemData['name'],
							'ACTIVE' => $itemData['active'] ?? true,
							'PUBLICATION' => $itemData['publication'] ?? false,
							'LOCK_DELETE' => $itemData['lock_delete'] ?? false,
							'AVAILABLE' => $itemData['available'] ?? true,
							'SINGLETON' => $itemData['singleton'] ?? false,
							'SECTION' => isset($itemData['section']) ? (array)$itemData['section'] : [],
							'DESCRIPTION' => $itemData['description'] ?? '',
							'PREVIEW' => file_exists($path . '/' . $dir . '/preview.jpg')
								? Manager::getUrlFromFile($pathLocal . '/' . $dir . '/preview.jpg')
								: '',
							'PREVIEW2X' => file_exists($path . '/' . $dir . '/preview@2x.jpg')
								? Manager::getUrlFromFile($pathLocal . '/' . $dir . '/preview@2x.jpg')
								: '',
							'PREVIEW3X' => file_exists($path . '/' . $dir . '/preview@3x.jpg')
								? Manager::getUrlFromFile($pathLocal . '/' . $dir . '/preview@3x.jpg')
								: '',
							'APP_CODE' => '',
							'REST' => 0,
							'DATA' => $itemData,
						);
					}
				}
			}
			// endregion

			// region get from zip repository
			if ($isNeedMarket && $hasMarket)
			{
				$query = [
					'pageSize' => $navigation ? $navigation->getPageSize() : self::COUNT_PER_PAGE,
					'page' => $navigation ? $navigation->getCurrentPage() : 1,
					'siteType' => self::DEMO_TYPES_FILTER[$siteTypeCurr] ?? 'page',
				];
				if ($this->arParams['SITE_ID'] > 0)
				{
					$query['onePage'] = 'Y';
				}
				if ($code)
				{
					$query['code'] = preg_replace(['/^\w+\//i', '/\/\w+$/i'], '', $code);
					unset($query['onePage'], $query['pageSize'], $query['page']);
				}

				// filter
				$query = array_merge($query, $this->getQueryFromFilter());

				$siteList = Client::getSiteList($query);
				if (is_array($siteList) && is_array($siteList['ITEMS'] ?? null))
				{
					$fakeRecordCount = $siteList['PAGES'] * $navigation->getPageSize();
					$navigation->setRecordCount($fakeRecordCount);
					$data['navigation']['recordCount'] = $fakeRecordCount;

					foreach ($siteList['ITEMS'] as $site)
					{
						$key = implode(
							$marketIdDelimiter,
							[
								$marketPrefix,
								$site['APP_CODE']
							]
						);
						if ($code && ($code !== $key))
						{
							continue;
						}
						$data[$subDir][$key] = [
							'ID' => $key,
							'XML_ID' => '',
							'TYPE' => $this->arParams['TYPE'],
							'TITLE' => $site['NAME'],
							'ACTIVE' => true,
							'AVAILABLE' => true,
							'SECTION' => $site['SECTION'] ?? [],
							'DESCRIPTION' => $site['DESCRIPTION'] ?? $site['APP_SHORT_DESC'],
							'THEME_COLOR' => $site['THEME_COLOR'] ?? null,
							'PREVIEW' => $site['PREVIEW'] ?? '',
							'PREVIEW2X' => $site['PREVIEW_2X'] ?? '',
							'PREVIEW3X' => $site['PREVIEW_3X'] ?? '',
							'PREVIEW_URL' => $site['URL'] ?? '',
							'ZIP_ID' => $site['ID'] ?? '',
							'APP_CODE' => $site['APP_CODE'] ?? '',
							'DATA' => $site['DATA'] ?? [],
							'IS_NEW' => $site['IS_NEW'],
							'REST' => 0,
							'LANG' => $site['LANG'] ?? LANGUAGE_ID,
							'LABELS' => $site['LABELS'] ?? null,
						];
						if (is_array($site['TAGS']) && in_array('krayt', $site['TAGS'], true))
						{
							$data[$subDir][$key]['DESIGNED_BY'] = 'KRAYT';
						}
						if (is_array($site['TAGS']) && in_array('delobot', $site['TAGS'], true))
						{
							$data[$subDir][$key]['DESIGNED_BY'] = 'DELOBOT';
						}
					}
				}
			}
			// endregion

			// to change template list by additional params
			$data[$subDir] = $this->applyExtDemoPerms($data[$subDir]);

			// system cache end
			if ($cacheStarted)
			{
				$cache->endDataCache($data);
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

	protected function applyExtDemoPerms(array $data): array
	{
		// not for repo
		if ($this->isRepo())
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

	/**
	 * Gets demo site templates.
	 * @param string|null $code Item code.
	 * @return array
	 */
	public function getDemoSite(?string $code = null): array
	{
		return $this->getDemo($this::DEMO_DIR_SITE, $code);
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
	public static function checkActive(array $zones = array()): bool
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
				if (
					$currentZone === 'ru'
					&& !Loader::includeModule('bitrix24')
					&& (file_exists($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/lang/ua"))
				)
				{
					$result = true;
				}
			}
		}
		return $result;
	}

	private function createCatalog(?int $iblockId = null): ?array
	{
		if ($this->bitrix24Included)
		{
			if (!$this->crmIncluded)
			{
				$this->addError(
					self::IMPORT_CATALOG_ERROR_ID,
					Loc::getMessage('LANDING_CMP_LD_ERR_CRM_IS_ABSENT')
				);
				return null;
			}
			$iblockId = (int)\CCrmCatalog::EnsureDefaultExists();
		}
		else
		{
			if ($iblockId === null)
			{
				return [
					'PRODUCT_IBLOCK_ID' => self::IBLOCK_AUTODETECT,
					'OFFER_IBLOCK_ID' => self::IBLOCK_AUTODETECT,
				];
			}
		}
		return $this->getCatalogInfo($iblockId);
	}

	private function getCatalogInfo(int $iblockId): ?array
	{
		if ($iblockId <= 0)
		{
			$this->addError(
				self::IMPORT_CATALOG_ERROR_ID,
				Loc::getMessage('LANDING_CMP_LD_ERR_BAD_IBLOCK_ID')
			);
			return null;
		}
		$row = Iblock\IblockTable::getList([
			'select' => ['ID'],
			'filter' => ['=ID' => $iblockId],
		])->fetch();
		if (empty($row))
		{
			$this->addError(
				self::IMPORT_CATALOG_ERROR_ID,
				Loc::getMessage('LANDING_CMP_LD_ERR_IBLOCK_IS_ABSENT')
			);
			return null;
		}
		$catalog = \CCatalogSku::GetInfoByProductIBlock($iblockId);
		if (empty($catalog))
		{
			$this->addError(
				self::IMPORT_CATALOG_ERROR_ID,
				Loc::getMessage('LANDING_CMP_LD_ERR_IBLOCK_IS_NOT_CATALOG')
			);
			return null;
		}
		if (
			$catalog['CATALOG_TYPE'] !== \CCatalogSku::TYPE_FULL
			&& $catalog['CATALOG_TYPE'] !== \CCatalogSku::TYPE_PRODUCT
		)
		{
			$this->addError(
				self::IMPORT_CATALOG_ERROR_ID,
				Loc::getMessage('LANDING_CMP_LD_ERR_CATALOG_IS_NOT_SKU_PARENT')
			);
			return null;
		}
		return [
			'PRODUCT_IBLOCK_ID' => $catalog['PRODUCT_IBLOCK_ID'],
			'OFFER_IBLOCK_ID' => $catalog['IBLOCK_ID'],
		];
	}

	/**
	 * Create some highloadblocks.
	 * @return void
	 */
	public static function createHLblocks(): void
	{
		if (!Loader::includeModule('highloadblock'))
		{
			return;
		}

		$xmlPath = '/bitrix/components/bitrix/landing.demo/data/xml';

		// demo data
		$sort = 0;
		$colorValues = array();

		$colors = [];
		$colors['PURPLE'] = [
			'XML_ID' => 'purple',
			'PATH' => 'colors_files/iblock/0d3/0d3ef035d0cf3b821449b0174980a712.jpg',
			'FILE_NAME' => 'purple.jpg',
			'FILE_TYPE' => 'image/jpeg',
			'TITLE' => ''
		];
		$colors['BROWN'] = [
			'XML_ID' => 'brown',
			'PATH' => 'colors_files/iblock/f5a/f5a37106cb59ba069cc511647988eb89.jpg',
			'FILE_NAME' => 'brown.jpg',
			'FILE_TYPE' => 'image/jpeg',
			'TITLE' => ''
		];
		$colors['SEE'] = [
			'XML_ID' => 'see',
			'PATH' => 'colors_files/iblock/f01/f01f801e9da96ae5a7f26aae01255f38.jpg',
			'FILE_NAME' => 'see.jpg',
			'FILE_TYPE' => 'image/jpeg',
			'TITLE' => ''
		];
		$colors['BLUE'] = [
			'XML_ID' => 'blue',
			'PATH' => 'colors_files/iblock/c1b/c1ba082577379bdc75246974a9f08c8b.jpg',
			'FILE_NAME' => 'blue.jpg',
			'FILE_TYPE' => 'image/jpeg',
			'TITLE' => ''
		];
		$colors['ORANGERED'] = [
			'XML_ID' => 'orangered',
			'PATH' => 'colors_files/iblock/0ba/0ba3b7ecdef03a44b145e43aed0cca57.jpg',
			'FILE_NAME' => 'orangered.jpg',
			'FILE_TYPE' => 'image/jpeg',
			'TITLE' => ''
		];
		$colors['REDBLUE'] = [
			'XML_ID' => 'redblue',
			'PATH' => 'colors_files/iblock/1ac/1ac0a26c5f47bd865a73da765484a2fa.jpg',
			'FILE_NAME' => 'redblue.jpg',
			'FILE_TYPE' => 'image/jpeg',
			'TITLE' => ''
		];
		$colors['RED'] = [
			'XML_ID' => 'red',
			'PATH' => 'colors_files/iblock/0a7/0a7513671518b0f2ce5f7cf44a239a83.jpg',
			'FILE_NAME' => 'red.jpg',
			'FILE_TYPE' => 'image/jpeg',
			'TITLE' => ''
		];
		$colors['GREEN'] = [
			'XML_ID' => 'green',
			'PATH' => 'colors_files/iblock/b1c/b1ced825c9803084eb4ea0a742b2342c.jpg',
			'FILE_NAME' => 'green.jpg',
			'FILE_TYPE' => 'image/jpeg',
			'TITLE' => ''
		];
		$colors['WHITE'] = [
			'XML_ID' => 'white',
			'PATH' => 'colors_files/iblock/b0e/b0eeeaa3e7519e272b7b382e700cbbc3.jpg',
			'FILE_NAME' => 'white.jpg',
			'FILE_TYPE' => 'image/jpeg',
			'TITLE' => ''
		];
		$colors['BLACK'] = [
			'XML_ID' => 'black',
			'PATH' => 'colors_files/iblock/d7b/d7bdba8aca8422e808fb3ad571a74c09.jpg',
			'FILE_NAME' => 'black.jpg',
			'FILE_TYPE' => 'image/jpeg',
			'TITLE' => ''
		];
		$colors['PINK'] = [
			'XML_ID' => 'pink',
			'PATH' => 'colors_files/iblock/1b6/1b61761da0adce93518a3d613292043a.jpg',
			'FILE_NAME' => 'pink.jpg',
			'FILE_TYPE' => 'image/jpeg',
			'TITLE' => ''
		];
		$colors['AZURE'] = [
			'XML_ID' => 'azure',
			'PATH' => 'colors_files/iblock/c2b/c2b274ad2820451d780ee7cf08d74bb3.jpg',
			'FILE_NAME' => 'azure.jpg',
			'FILE_TYPE' => 'image/jpeg',
			'TITLE' => ''
		];
		$colors['JEANS'] = [
			'XML_ID' => 'jeans',
			'PATH' => 'colors_files/iblock/24b/24b082dc5e647a3a945bc9a5c0a200f0.jpg',
			'FILE_NAME' => 'jeans.jpg',
			'FILE_TYPE' => 'image/jpeg',
			'TITLE' => ''
		];
		$colors['FLOWERS'] = [
			'XML_ID' => 'flowers',
			'PATH' => 'colors_files/iblock/64f/64f32941a654a1cbe2105febe7e77f33.jpg',
			'FILE_NAME' => 'flowers.jpg',
			'FILE_TYPE' => 'image/jpeg',
			'TITLE' => 'colors_files/iblock/64f/64f32941a654a1cbe2105febe7e77f33.jpg'
		];

		$colors['DARKBLUE'] = [
			'XML_ID' => 'darkblue',
			'PATH' => 'colors_files/iblock/84a/84afl562rq429820451d780ee7cf08d7.png',
			'FILE_NAME' => 'darkblue.png',
			'FILE_TYPE' => 'image/png',
			'TITLE' => ''
		];
		$colors['DARKGREEN'] = [
			'XML_ID' => 'darkgreen',
			'PATH' => 'colors_files/iblock/87f/87f5d3ad34562rq429820451d780ee7c.png',
			'FILE_NAME' => 'darkgreen.png',
			'FILE_TYPE' => 'image/png',
			'TITLE' => ''
		];
		$colors['GREY'] = [
			'XML_ID' => 'grey',
			'PATH' => 'colors_files/iblock/90c/90c274ad2820451d780ee7cf08d74bb3.png',
			'FILE_NAME' => 'grey.png',
			'FILE_TYPE' => 'image/png',
			'TITLE' => ''
		];
		$colors['YELLOW'] = [
			'XML_ID' => 'yellow',
			'PATH' => 'colors_files/iblock/99a/99a082dc5e647a3a945bc9a5c0a200f0.png',
			'FILE_NAME' => 'yellow.png',
			'FILE_TYPE' => 'image/png',
			'TITLE' => ''
		];
		$colors['ORANGE'] = [
			'XML_ID' => 'orange',
			'PATH' => 'colors_files/iblock/a0d/a0ddba8aca8422e808fb3ad571a74c09.png',
			'FILE_NAME' => 'orange.png',
			'FILE_TYPE' => 'image/png',
			'TITLE' => ''
		];

		foreach (array_keys($colors) as $index)
		{
			$colors[$index]['TITLE'] = Loc::getMessage('LANDING_CMP_COLOR_'.$index);
		}

		Main\Type\Collection::sortByColumn($colors, ['TITLE' => SORT_ASC]);

		foreach($colors as $row)
		{
			$sort += 100;
			$colorValues[] = [
				'UF_NAME' => $row['TITLE'],
				'UF_FILE' => [
					'name' => $row['FILE_NAME'],
					'type' => $row['FILE_TYPE'],
					'tmp_name' => Manager::getDocRoot().$xmlPath.'/hl/'.$row['PATH']
				],
				'UF_SORT' => $sort,
				'UF_DEF' => '0',
				'UF_XML_ID' => $row['XML_ID']
			];
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
			$tableId = null;
			// if this hl isn't exist
			$res = Highloadblock\HighloadBlockTable::getList([
				'select' => [
					'ID',
					'NAME',
					'TABLE_NAME',
				],
				'filter' => [
					'=NAME' => $table['name'],
					'=TABLE_NAME' => $tableName
				]
			]);
			$row = $res->fetch();
			unset($res);
			if (empty($row))
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
				}
			}
			else
			{
				$tableId = (int)$row['ID'];
			}
			// add data
			if (!empty($tableId))
			{
				$hldata = Highloadblock\HighloadBlockTable::getById($tableId)->fetch();
				$hlentity = Highloadblock\HighloadBlockTable::compileEntity($hldata);
				$entityClass = $hlentity->getDataClass();
				foreach ($table['values'] as $item)
				{
					$rowColor = $entityClass::getList([
						'select' => ['ID'],
						'filter' => [
							'=UF_XML_ID' => $item['UF_XML_ID']
						],
					])->fetch();
					if (empty($rowColor))
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
	 * @param array $config Execute config.
	 * @return array
	 */
	private function createCatalogStep(string $xmlcode, array $config = []): array
	{
		$result = [
			'STATUS' => self::STEP_STATUS_ERROR,
			'MESSAGE' => '',
			'FINAL' => true,
			'PROGRESS' => 0
		];

		if (
			!$this->iblockIncluded
			|| !$this->catalogIncluded
		)
		{
			$result['MESSAGE'] = Loc::getMessage('LANDING_CMP_ERROR_MASTER_NO_SERVICE');
			return $result;
		}

		$showcaseId = $this->getCurrentShowcaseId();
		if (empty($showcaseId))
		{
			$result['STATUS'] = self::STEP_STATUS_COMPLETE;
			return $result;
		}
		$showcase = $this->getCurrentShowcase();
		$parentXmlId = $showcase['XML_LIST'][0];
		$offerXmlId = $showcase['XML_LIST'][1];

		if ($this->bitrix24Included)
		{
			\CBitrix24::createIblockDemodataFileBucket();
		}

		$stepConfig = []; // TODO: add IBLOCK_LIST support
		$this->initStepStorage($stepConfig);

		$result['STATUS'] = self::STEP_STATUS_CONTINUE;
		$result['FINAL'] = false;
		switch ($this->getCurrentStep())
		{
			case self::STEP_ID_CREATE_CATALOG:
				$this->createCatalog();
				$this->nextStep();
				$result['PROGRESS'] = 15;
				break;
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
					(int)$this->getXmlIblockId($parentXmlId),
					(int)$this->getXmlIblockId($offerXmlId)
				);
				$this->nextStep();
				$result['MESSAGE'] = Loc::getMessage('LANDING_CMP_LD_MESS_CATALOG_UPDATE');
				$result['PROGRESS'] = 50;
				break;
			case self::STEP_ID_CATALOG_REINDEX:
				$this->reindexCatalog(
					(int)$this->getXmlIblockId($parentXmlId),
					(int)$this->getXmlIblockId($offerXmlId)
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

		$this->initRequiredModules();

		$this->getRemoteTemplates = true;
		$application = Manager::getApplication();
		set_time_limit(300);
		$this->checkParam('SITE_ID', 0);
		$this->checkParam('FOLDER_ID', 0);
		$this->checkParam('TYPE', '');
		$this->checkParam('SKIP_REMOTE', 'N');
		$this->checkParam('FILTER', []);

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

			// save frame state in ajax requests
			$this->arResult['IS_AJAX'] = $this->isAjax();
			$this->arResult['IS_FRAME'] = $this->request('IFRAME') === 'Y' || $this->request('IS_FRAME') === 'Y';

			// Filter
			$this->arResult['FILTER_URI'] = $this->getUri(
				[
					'IS_AJAX' => 'Y',
					'IS_FRAME' => $this->arResult['IS_FRAME'] ? 'Y' : 'N'
				],
				['IFRAME', 'IFRAME_TYPE']
			);
			if (
				$this->arParams['TYPE'] === 'PAGE'
				&& !$this->request('action')
			)
			{
				$this->arResult['FILTER_FIELDS'] = self::getFilterFields();
				$this->arResult['FILTER_ID'] = self::FILTER_ID . '_' . $this->arParams['TYPE'];
				$this->arResult['FILTER_PRESETS'] = self::getFilterPresets();
				$this->arResult['FILTER_OPTIONS'] = new Filter\Options(
					$this->arResult['FILTER_ID'],
					$this->arResult['FILTER_PRESETS']
				);
				$this->arResult['FILTER'] = $this->arResult['FILTER_OPTIONS']->getFilter();
			}
			$this->arResult['IS_SEARCH'] = is_array($this->arResult['FILTER']) && !empty($this->arResult['FILTER']);

			// init nav
			$this->arResult['NAV_URI'] =
				$this->arResult['IS_FRAME']
					? $this->getUri(['IFRAME' => 'Y', 'IFRAME_TYPE' => 'SIDE_SLIDER'], ['IS_AJAX', 'IS_FRAME'])
					: $this->getUri([], ['IS_AJAX', 'IS_FRAME'])
				;
			$this->lastNavigation = new PageNavigation(self::NAVIGATION_ID);
			$this->lastNavigation->allowAllRecords(false)
				->setPageSize($this::COUNT_PER_PAGE)
				->initFromUri();



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
				$this->initShowcase();

				if ($this->request('start') === 'Y')
				{
					$settings = Settings::getDataForSite();
					// if shop section exist, save for site, else make import
					$sectionId = $this->getParentCatalogSectionId(
						$settings['IBLOCK_ID'],
						$this->getCurrentShowcaseSectionXmlId()
					);
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

	protected function initRequiredModules(): void
	{
		$this->bitrix24Included = Loader::includeModule('bitrix24');
		$this->iblockIncluded = Loader::includeModule('iblock');
		$this->catalogIncluded = Loader::includeModule('catalog');
		$this->crmIncluded = Loader::includeModule('crm');
	}

	private static function changeProductPropertyXmlId(int $iblockId): void
	{
		$list = [
			['CODE' => 'NEWPRODUCT', 'XML_ID' => 'af49d0e309af4fac506a8a228000efc5'],
			['CODE' => 'SALELEADER', 'XML_ID' => '103'],
			['CODE' => 'SPECIALOFFER', 'XML_ID' => '5'],
			['CODE' => 'ARTNUMBER', 'XML_ID' => '10', 'NEW_XML_ID' => 'CML2_ARTICLE'],
			['CODE' => 'MANUFACTURER', 'XML_ID' => '12'],
			['CODE' => 'MATERIAL', 'XML_ID' => '11'],
			['CODE' => 'COLOR', 'XML_ID' => '291'],
			['CODE' => 'BLOG_POST_ID', 'XML_ID' => '43'],
			['CODE' => 'BLOG_COMMENTS_CNT', 'XML_ID' => '44'],
			['CODE' => 'BACKGROUND_IMAGE', 'XML_ID' => '45'],
			['CODE' => 'MORE_PHOTO', 'XML_ID' => 'MORE_PHOTO', 'NEW_XML_ID' => 'CML2_PICTURES']
		];
		self::changePropertyXmlId($iblockId, $list);
		unset($list);
	}

	private static function changeOfferPropertyXmlId(int $iblockId): void
	{
		$list = [
			['CODE' => 'ARTNUMBER', 'XML_ID' => '38', 'NEW_XML_ID' => 'CML2_ARTICLE'],
			['CODE' => 'ARTNUMBER', 'XML_ID' => 'ARTNUMBER', 'NEW_XML_ID' => 'CML2_ARTICLE'],
			['CODE' => 'SIZES_SHOES', 'XML_ID' => '4510'],
			['CODE' => 'SIZES_CLOTHES', 'XML_ID' => '40'],
			['CODE' => 'MORE_PHOTO', 'XML_ID' => '39', 'NEW_XML_ID' => 'CML2_PICTURES'],
			['CODE' => 'MORE_PHOTO', 'XML_ID' => 'MORE_PHOTO', 'NEW_XML_ID' => 'CML2_PICTURES'],
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
				$newXmlId = $property['NEW_XML_ID'] ?? $property['CODE'];
				if (isset($xmlCodes[$newXmlId]))
				{
					$old = $xmlCodes[$property['XML_ID']];
					Iblock\PropertyTable::update(
						$old['ID'],
						['XML_ID' => $old['XML_ID'].'_OLD', 'CODE' => $old['CODE'].'_OLD']
					);
				}
				Iblock\PropertyTable::update(
					$row['ID'],
					['XML_ID' => $newXmlId]
				);
			}
		}
		unset($row, $iterator);
		unset($property);
		unset($xmlCodes);
	}

	private static function transferOfferListProperties(int $iblockId): void
	{
		$list = [
			[
				'CODE' => 'SIZES_SHOES',
				'PROPERTY_TYPE' => Iblock\PropertyTable::TYPE_LIST,
				'DEFAULT' => 'N',
				'VALUES' => [
					[
						'VALUE' => '35',
						'XML_ID' => 'd5c054b792e65b6f093fa81313570f0b',
						'NEW_XML_ID' => 'shoesize35',
					],
					[
						'VALUE' => '36',
						'XML_ID' => '07be2c1628d03a7cf0f194eeb3595853',
						'NEW_XML_ID' => 'shoesize36',
					],
					[
						'VALUE' => '37',
						'XML_ID' => 'bcf799ba1361be1e5c3c8a38aac331d3',
						'NEW_XML_ID' => 'shoesize37',
					],
					[
						'VALUE' => '38',
						'XML_ID' => '05954c7cb32f164bdf507c34230b547d',
						'NEW_XML_ID' => 'shoesize38',
					],
					[
						'VALUE' => '39',
						'XML_ID' => 'cbe3a6965734b6cea57ffdf6b0259254',
						'NEW_XML_ID' => 'shoesize39',
					],
					[
						'VALUE' => '40',
						'XML_ID' => '9f7530be7c4439529be99615cf2cdf1f',
						'NEW_XML_ID' => 'shoesize40',
					],
				],
			],
			[
				'CODE' => 'SIZES_CLOTHES',
				'PROPERTY_TYPE' => Iblock\PropertyTable::TYPE_LIST,
				'DEFAULT' => 'N',
				'VALUES' => [
					[
						'VALUE' => 'XS',
						'XML_ID' => 'a11f96c3b88d222460d9796067d28b0c',
						'NEW_XML_ID' => 'sizeXS',
					],
					[
						'VALUE' => 'S',
						'XML_ID' => 'be1079a982cc85bbdfec9b2cffe132c2',
						'NEW_XML_ID' => 'sizeS',
					],
					[
						'VALUE' => 'M',
						'XML_ID' => 'a2f1730e4dacc24cae7fd200e20ecb15',
						'NEW_XML_ID' => 'sizeM',
					],
					[
						'VALUE' => 'L',
						'XML_ID' => '309a4b56bb562106fcaffaa45595c200',
						'NEW_XML_ID' => 'sizeL',
					],
					[
						'VALUE' => 'XL',
						'XML_ID' => '98d8be59b4d50201222aa8fa85410395',
						'NEW_XML_ID' => 'sizeXL',
					],
					[
						'VALUE' => 'XXL',
						'XML_ID' => '58178408c32af4f9205a5f0d1f16b90c',
						'NEW_XML_ID' => 'sizeXXL',
					],
					[
						'VALUE' => 'XXXL',
						'XML_ID' => '07249452260df37df35152cef9352bec',
						'NEW_XML_ID' => 'sizeXXXL',
					],
				]
			]
		];
		self::transferListProperties($iblockId, $list);
	}

	private static function transferListProperties(int $iblockId, array $list): void
	{
		foreach ($list as $row)
		{
			$property = Iblock\PropertyTable::getList([
				'select' => ['ID'],
				'filter' => [
					'=IBLOCK_ID' => $iblockId,
					'=CODE' => $row['CODE'],
					'=PROPERTY_TYPE' => $row['PROPERTY_TYPE'],
				],
			])->fetch();
			if (!empty($property))
			{
				$property['ID'] = (int)$property['ID'];
				if (isset($row['DEFAULT']) && $row['DEFAULT'] === 'N')
				{
					$defaultValue = Iblock\PropertyEnumerationTable::getList([
						'select' => ['ID', 'PROPERTY_ID'],
						'filter' => [
							'=PROPERTY_ID' => $property['ID'],
							'=DEF' => 'Y'
						],
					])->fetch();
					if (!empty($defaultValue))
					{
						Iblock\PropertyEnumerationTable::update(
							$defaultValue,
							['DEF' => 'N']
						);
					}
				}
				if (!empty($row['VALUES']) && is_array($row['VALUES']))
				{
					foreach ($row['VALUES'] as $item)
					{
						$value = Iblock\PropertyEnumerationTable::getList([
							'select' => ['ID'],
							'filter' => [
								'=PROPERTY_ID' => $property['ID'],
								'=VALUE' => $item['VALUE'],
								'=XML_ID' => $item['XML_ID'],
							],
						])->fetch();
						if (!empty($value))
						{
							\CIBlockPropertyEnum::Update(
								$value['ID'],
								['XML_ID' => $item['NEW_XML_ID']]
							);
						}
					}
				}
			}
		}
	}

	/**
	 * Checking iblock by xml id.
	 * @param int $iblockId Iblock id.
	 * @param string $xmlId External code.
	 * @return void
	 */
	private static function checkIblockXmlId(int $iblockId, string $xmlId): void
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
		if (!$this->crmIncluded)
		{
			return;
		}
		$iblockId = (int)\CCrmCatalog::EnsureDefaultExists();
		if ($iblockId > 0)
		{
			self::checkIblockXmlId($iblockId, 'FUTURE-1C-CATALOG');
			if ($this->bitrix24Included)
			{
				self::changeProductPropertyXmlId($iblockId);
			}
		}
		$catalog = \CCatalogSku::GetInfoByProductIBlock($iblockId);
		if (!empty($catalog))
		{
			self::checkIblockXmlId($catalog['IBLOCK_ID'], 'FUTURE-1C-CATALOG-OFFERS');
			if ($this->bitrix24Included)
			{
				self::changeOfferPropertyXmlId($catalog['IBLOCK_ID']);
				self::transferOfferListProperties($catalog['IBLOCK_ID']);
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
	private function importXmlFile(): array
	{
		$result = [
			'STATUS' => self::STEP_STATUS_CONTINUE,
			'MESSAGE' => '',
			'FINAL' => true,
			'PROGRESS' => 0
		];

		$importer = new \CIBlockXmlImport();
		$xmlProductCatalog = 'CRM_PRODUCT_CATALOG';

		$this->setXmlBaseCurrency();

		$xml = $this->getCurrentXml();

		$parameters = [
			'FILE' => $this->getCurrentXmlFilePath(),
			'IBLOCK_TYPE' => $xmlProductCatalog,
			'SITE_LIST' => [SITE_ID],
			'MISSING_SECTION_ACTION' => \CIBlockXmlImport::ACTION_NOTHING,
			'MISSING_ELEMENT_ACTION' => \CIBlockXmlImport::ACTION_NOTHING,
			'INTERVAL' => 20,
		];
		$config = [
			'USE_CRC' => false,
			'PREVIEW_PICTURE_SETTINGS' => false,
			'DETAIL_PICTURE_SETTINGS' => false,
			'READ_BLOCKSIZE' => 16384,
			'IBLOCK_CACHE_MODE' => \CIBlockCMLImport::IBLOCK_CACHE_FREEZE,
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
	private function updateImportedIblocks(int $parentIblock, int $offerIblock): void
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
				'UNIQUE' => 'Y',
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
	private function reindexCatalog(int $parentIblock, int $offerIblock): void
	{
		$parentSectionId = $this->getParentCatalogSectionId(
			$parentIblock,
			$this->getCurrentShowcaseSectionXmlId()
		);
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
	 * @param array $config Step config
	 * @return void
	 */
	private function initStepStorage(array $config = []): void
	{
		if (
			!isset($_SESSION['LANDING_DEMO_STORAGE']) ||
			!is_array($_SESSION['LANDING_DEMO_STORAGE'])
		)
		{
			$showcase = $this->getCurrentShowcase();
			$initData = [
				'STEP_ID' => reset($this->catalogStepList),
				'XML_LIST' => $showcase['XML_LIST'],
				'IBLOCK_ID' => array_fill_keys($showcase['XML_LIST'], 0),
				'STEP_PARAMETERS' => []
			];

			$_SESSION['LANDING_DEMO_STORAGE'] = $initData;
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
				'CODE' => reset($_SESSION['LANDING_DEMO_STORAGE']['XML_LIST']),
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

		return $_SESSION['LANDING_DEMO_STORAGE']['XML_LIST'][$index + 1] ?? null;
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
	private function getXmlIblockId(string $xmlId)
	{
		return $_SESSION['LANDING_DEMO_STORAGE']['IBLOCK_ID'][$xmlId];
	}

	/**
	 * Set xml of iblock id to session.
	 * @param string $xmlId External code.
	 * @param int $iblockId Iblock id.
	 * @return void
	 */
	private function setXmlIblockId(string $xmlId, int $iblockId)
	{
		$_SESSION['LANDING_DEMO_STORAGE']['IBLOCK_ID'][$xmlId] = $iblockId;
	}

	/**
	 * Gets result message for different steps.
	 * @param string $xmlId External code.
	 * @param bool $complete Complete step.
	 * @return string
	 */
	private function importXmlFileResultMessage(string $xmlId, bool $complete = true): string
	{
		if ($complete)
		{
			$result = $this->showcase['MESSAGES'][$xmlId]['IMPORT_COMPLETE'] ?? '';
		}
		else
		{
			$result = $this->showcase['MESSAGES'][$xmlId]['IMPORT_PROGRESS'] ?? '';
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
	private function getParentCatalogSectionId(int $iblockId, ?string $xmlId = null): ?int
	{
		if (empty($xmlId))
		{
			$xmlId = $this->showcaseList[self::SHOWCASE_DEFAULT]['SECTION_XML_ID'] ?? null;
		}
		if ($xmlId === null)
		{
			return null;
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

	/**
	 * Checking first symbols # in hex color
	 * @param string $color color in hex format.
	 * @return string
	 */
	private function prepareColor(string $color): string
	{
		if ($color[0] !== '#')
		{
			$color = '#'.$color;
		}
		return $color;
	}

	private function initShowcase(): void
	{
		$this->initShowcaseList();
		$this->initCurrentShowcase();
	}

	private function initShowcaseList(): void
	{
		$this->showcaseList[self::SHOWCASE_CLOTHES] = [
			'CLOUD_STORAGE' => 'N',
			'XML_FOLDER' => 'clothes',
			'XML_LIST' => [
				'catalog',
				'catalog_sku',
			],
			'SECTION_XML_ID' => '666',
			'MESSAGES' => [
				'catalog' => [
					'IMPORT_PROGRESS' => Loc::getMessage('LANDING_CMP_LD_MESS_XML_IMPORT_CATALOG_PROGRESS'),
					'IMPORT_COMPLETE' => Loc::getMessage('LANDING_CMP_LD_MESS_XML_IMPORT_CATALOG_COMPLETE'),
				],
				'catalog_sku' => [
					'IMPORT_PROGRESS' => Loc::getMessage('LANDING_CMP_LD_MESS_XML_IMPORT_CATALOG_OFFERS_PROGRESS'),
					'IMPORT_COMPLETE' => Loc::getMessage('LANDING_CMP_LD_MESS_XML_IMPORT_CATALOG_OFFERS_COMPLETE'),
				],
			],
		];
		$this->showcaseList[self::SHOWCASE_FASHION] = [
			'CLOUD_STORAGE' => 'Y',
			'XML_FOLDER' => 'fashion',
			'XML_LIST' => [
				'products',
				'offers',
			],
			'SECTION_XML_ID' => 'fashion',
			'MESSAGES' => [
				'products' => [
					'IMPORT_PROGRESS' => Loc::getMessage('LANDING_CMP_LD_MESS_XML_IMPORT_CATALOG_PROGRESS'),
					'IMPORT_COMPLETE' => Loc::getMessage('LANDING_CMP_LD_MESS_XML_IMPORT_CATALOG_COMPLETE'),
				],
				'offers' => [
					'IMPORT_PROGRESS' => Loc::getMessage('LANDING_CMP_LD_MESS_XML_IMPORT_CATALOG_OFFERS_PROGRESS'),
					'IMPORT_COMPLETE' => Loc::getMessage('LANDING_CMP_LD_MESS_XML_IMPORT_CATALOG_OFFERS_COMPLETE'),
				],
			],
		];
	}

	private function getDefaultShowcaseId(): string
	{
		return self::SHOWCASE_CLOTHES;
	}

	private function initCurrentShowcase(): void
	{
		$showcaseId = $this->request('showcaseId');
		if ($showcaseId === null)
		{
			$showcaseId = $this->getDefaultShowcaseId();
		}
		$this->setCurrentShowcaseId($showcaseId);
	}

	private function getCurrentShowcaseId(): ?string
	{
		return $this->showcaseId;
	}

	private function setCurrentShowcaseId(string $showcaseId): void
	{
		if (isset($this->showcaseList[$showcaseId]))
		{
			$this->showcaseId = $showcaseId;
			$this->showcase = $this->showcaseList[$showcaseId];
		}
	}

	private function getCurrentShowcase(): ?array
	{
		return $this->showcase;
	}

	private function getCurrentShowcaseSectionXmlId(): ?string
	{
		return $this->showcase['SECTION_XML_ID'] ?? null;
	}

	private function getCurrentShowcaseXmlFolder(): ?string
	{
		return $this->showcase['XML_FOLDER'] ?? null;
	}

	private function getCurrentShowcaseCloudStorage(): ?string
	{
		return $this->showcase['CLOUD_STORAGE'] ?? null;
	}

	private function getCurrentXmlFilePath(): string
	{
		$xmlPath = '/bitrix/components/bitrix/landing.demo/data/xml/'
			.$this->getCurrentShowcaseXmlFolder();
		if ($this->getCurrentShowcaseCloudStorage() === 'Y')
		{
			$xmlPath .= '/'.($this->bitrix24Included
				? 'cloud'
				: 'box'
			);
		}

		$currentZone = Manager::getZone();
		$subDirLang = in_array($currentZone, ['ru', 'kz', 'by']) ? 'ru' : 'en';
		$xmlPath .= '/'.$subDirLang;

		$xmlPath .= '/'.$this->getCurrentXml().'.xml';

		return $xmlPath;
	}

	protected static function getFilterFields(): array
	{
		$fields = [
			'INSTALLS' => [
				'id' => 'INSTALLS',
				'type' => 'list',
				'default' => true,
				'name' => Loc::getMessage('LANDING_CMP_DEMO_FILTER_INSTALL_COUNT'),
				'items' => [
					'100' => '1 - 100',
					'500' => '100 - 500',
					'3000' => '500 - 3000',
					'3001' => Loc::getMessage('LANDING_CMP_DEMO_FILTER_MORE_3000'),
					// '10001' => Loc::getMessage('LANDING_CMP_DEMO_FILTER_MORE_10000'),
					// todo: now max count is 4800
				],
			],
			'DATE' => [
				'id' => 'DATE',
				'type' => 'list',
				'default' => true,
				'name' => Loc::getMessage('LANDING_CMP_DEMO_FILTER_DATE_PUBLIC'),
				'items' => [
					'today' => Loc::getMessage('LANDING_CMP_DEMO_FILTER_TODAY'),
					'week' => Loc::getMessage('LANDING_CMP_DEMO_FILTER_WEEK_AGO'),
					'2week' => Loc::getMessage('LANDING_CMP_DEMO_FILTER_2WEEK_AGO'),
					'earlier' => Loc::getMessage('LANDING_CMP_DEMO_FILTER_LONG_TIME_AGO'),
				],
			],
		];

		if (Manager::isB24())
		{
			$fields['PAYMENT'] = [
				'id' => 'PAYMENT',
				'type' => 'list',
				'default' => true,
				'name' => Loc::getMessage('LANDING_CMP_DEMO_FILTER_TMP_TYPE'),
				'items' => [
					'free' => Loc::getMessage('LANDING_CMP_DEMO_FILTER_FREE'),
					'subscribe' => Loc::getMessage('LANDING_CMP_DEMO_FILTER_PAY'),
				],
			];
		}

		return $fields;
	}

	/**
	 * Gets presets for filter.
	 * @return array
	 */
	protected static function getFilterPresets(): array
	{
		$presets = [
			'New' => [
				'name' => Loc::getMessage('LANDING_CMP_DEMO_FILTER_PRESET_NEWS'),
				'fields' => [
					'DATE' => '2week',
				]
			],
			'Populars' => [
				'name' => Loc::getMessage('LANDING_CMP_DEMO_FILTER_PRESET_POPULAR'),
				'fields' => [
					'INSTALLS' => '3000',
				]
			],
		];

		if (Manager::isB24())
		{
			$presets['Free'] = [
				'name' => Loc::getMessage('LANDING_CMP_DEMO_FILTER_PRESET_FREE'),
				'fields' => [
					'PAYMENT' => 'free',
				],
			];
			$presets['Subscribe'] = [
				'name' => Loc::getMessage('LANDING_CMP_DEMO_FILTER_PRESET_PAY'),
				'fields' => [
					'PAYMENT' => 'subscribe',
				],
			];
		}

		return $presets;
	}

	/**
	 * Convert filter to unique string, f.e. for cache unification
	 * @return string
	 */
	protected function getFilterToString(): string
	{
		$result = '';
		$filter = $this->arResult['FILTER'] ?? [];
		if (is_array($filter) && !empty($filter))
		{
			$keys = array_merge(['FIND'], array_keys(self::getFilterFields()));
			foreach ($keys as $key)
			{
				if ($filter[$key] ?? null)
				{
					$result .= "{$key}:{$filter[$key]}_";
				}
			}
		}

		return $result;
	}

	/**
	 * @return array - query params for getSiteList method
	 */
	protected function getQueryFromFilter(): array
	{
		$filter = $this->arResult['FILTER'] ?? [];
		$query = [];

		if (!empty($filter) && $filter['FILTER_APPLIED'])
		{
			// search
			if ($filter['FIND'])
			{
				$query['q'] = $filter['FIND'];
			}

			// type
			if ($filter['PAYMENT'] ?? null)
			{
				if ($filter['PAYMENT'] === 'free')
				{
					$query['free'] = 'Y';
					unset($query['by_subscription']);
				}
				elseif ($filter['PAYMENT'] === 'subscribe')
				{
					$query['by_subscription'] = 'Y';
					unset($query['free']);
				}
			}

			// install count
			if (($filter['INSTALLS'] ?? null) && $filter['INSTALLS'] !== 'all')
			{
				if ($filter['INSTALLS'] === '100')
				{
					$query['installs_from'] = 1;
					$query['installs_to'] = 100;
				}
				elseif ($filter['INSTALLS'] === '500')
				{
					$query['installs_from'] = 100;
					$query['installs_to'] = 500;
				}
				elseif ($filter['INSTALLS'] === '3000')
				{
					$query['installs_from'] = 500;
					$query['installs_to'] = 3000;
				}
				elseif ($filter['INSTALLS'] === '3001')
				{
					$query['installs_from'] = 3000;
				}
				elseif ($filter['INSTALLS'] === '5000')
				{
					$query['installs_from'] = 5000;
					$query['installs_to'] = 10000;
				}
				elseif ($filter['INSTALLS'] === '10001')
				{
					$query['installs_from'] = 10000;
				}
			}

			if ($filter['DATE'] ?? null)
			{
				$now = new Date();
				$toPhpFormat = Date::convertFormatToPhp('DD.MM.YYYY HH:MI:SS');

				if ($filter['DATE'] === 'today')
				{
					$query['date_public_from'] = $now->add('-1D')->format($toPhpFormat);
				}
				elseif ($filter['DATE'] === 'week')
				{
					$query['date_public_from'] = $now->add('-7D')->format($toPhpFormat);
				}
				elseif ($filter['DATE'] === '2week')
				{
					$query['date_public_from'] = $now->add('-14D')->format($toPhpFormat);
				}
				elseif ($filter['DATE'] === 'earlier')
				{
					$query['date_public_to'] = $now->add('-14D')->format($toPhpFormat);
				}
			}
		}

		return  $query;
	}
}
