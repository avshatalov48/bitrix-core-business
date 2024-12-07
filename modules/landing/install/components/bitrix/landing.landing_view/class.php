<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Landing\Folder;
use \Bitrix\Landing\Manager;
use \Bitrix\Landing\Site;
use \Bitrix\Landing\Landing;
use \Bitrix\Landing\Site\Type;
use \Bitrix\Landing\Syspage;
use \Bitrix\Landing\Hook;
use \Bitrix\Landing\Rights;
use \Bitrix\Landing\TemplateRef;
use \Bitrix\Main\Event;
use \Bitrix\Main\EventManager;
use Bitrix\Main\Loader;
use \Bitrix\Main\ModuleManager;
use \Bitrix\Landing\Source\Selector;
use \Bitrix\Landing\PublicAction\Demos;
use Bitrix\Intranet;

\CBitrixComponent::includeComponentClass('bitrix:landing.base');

class LandingViewComponent extends LandingBaseComponent
{
	private const PHONE_VERIFY_ENTITY_FORM = 'crm_webform';

	/**
	 * Total this type sites count.
	 * @deprecated since 19.0.0
	 * @var int
	 */
	protected $sitesCount;

	/**
	 * Total pages count in current site.
	 * @deprecated since 19.0.0
	 * @var int
	 */
	protected $pagesCount;

	/**
	 * Returns Landing preview url for device.
	 * @param int $id Landing id.
	 * @return string|null
	 */
	protected function getDevicePreview(int $id): ?string
	{
		\Bitrix\Landing\Landing::setPreviewMode(true);

		$url = null;
		$landing = Landing::createInstance($id, [
			'skip_blocks' => true
		]);

		if ($landing->exist())
		{
			if (
				$landing->getSmnSiteId() &&
				Manager::isExtendedSMN() &&
				$this->arParams['DRAFT_MODE'] != 'Y'
			)
			{
				Manager::forceB24disable(true);
			}

			$url = $landing->getPublicUrl(false, true, $this->arParams['DRAFT_MODE'] !== 'Y');
		}

		\Bitrix\Landing\Landing::setPreviewMode(false);

		return $url;
	}

	/**
	 * Just redirect to the landing preview page.
	 * @param int $id Landing id.
	 * @return boolean
	 */
	protected function actionPreview($id)
	{
		\Bitrix\Landing\Landing::setPreviewMode(true);

		$landing = Landing::createInstance($id, [
			'skip_blocks' => true
		]);
		if ($landing->exist())
		{
			if (
				$landing->getSmnSiteId() &&
				Manager::isExtendedSMN() &&
				$this->arParams['DRAFT_MODE'] != 'Y'
			)
			{
				Manager::forceB24disable(true);
			}
			$url = $landing->getPublicUrl(false, true, true);
			if ($this->arParams['DONT_LEAVE_AFTER_PUBLICATION'] == 'Y')
			{
				$uriPreview = new \Bitrix\Main\Web\Uri($url);
				$uriPreview->addParams([
					'IFRAME' => 'Y'
				]);
				$url = $uriPreview->getUri();
			}
			\localRedirect($this->getTimestampUrl($url), true);
		}

		\Bitrix\Landing\Landing::setPreviewMode(false);

		$this->setErrors(
			$landing->getError()->getErrors()
		);

		return false;
	}

	/**
	 * Set auto publication flag to user option.
	 * @param string $check Auto publication flag (Y / N).
	 * @return void
	 */
	protected function actionChangeAutoPublication(string $check): void
	{
		\CUserOptions::setOption('landing', 'auto_publication', ($check === 'Y') ? 'Y' : 'N');
	}

	/**
	 * User try change top panel and need new data.
	 * @param int $lid Landing id.
	 * @return array
	 */
	protected function actionChangeTop($lid)
	{
		$site = null;
		$landing = Landing::createInstance($lid, [
			'skip_blocks' => true
		]);
		if ($landing->exist())
		{
			$site = $this->getSites([
				'filter' => [
					'ID' => $landing->getSiteId()
				]
			]);
		}
		if (!$landing->exist() || !$site)
		{
			return [
				'type' => 'error',
				'error_description' => 'Page not found'
			];
		}
		$site = array_pop($site);
		$rights = Rights::getOperationsForSite(
			$landing->getSiteId()
		);
		return $this->getTopPanelConfig($landing, $site, $rights);
	}

	/**
	 * Gets config for top panel.
	 * @param Landing $landing Landing instance.
	 * @param array $site Site landing's array.
	 * @param array $rights Rights landing's array.
	 * @return array
	 */
	protected function getTopPanelConfig(Landing $landing, array $site, array $rights)
	{
		$uiInstalled = Loader::includeModule('ui');
		return [
			'type' => $this->arParams['TYPE'],
			'id' => $landing->getId(),
			'url' => str_replace(' ', '%20', $this->arResult['~LANDING_FULL_URL'] ?? $landing->getPublicUrl()),
			'siteId' => $landing->getSiteId(),
			'siteTitle' => $site['TITLE'],
			'active' => $landing->isActive(),
			'draftMode' => $this->arParams['DRAFT_MODE'] == 'Y',
			'title' => $landing->getTitle(),
			'specialType' => $this->arResult['SPECIAL_TYPE'],
			'autoPublicationEnabled' =>
				$this->arResult['SPECIAL_TYPE'] === Site\Type::PSEUDO_SCOPE_CODE_FORMS ||
				\CUserOptions::getOption('landing', 'auto_publication', 'Y') === 'Y',
			'pagesCount' => $this->getPagesCount(
				$landing->getSiteId()
			),
			'storeEnabled' => (
				$this->arParams['TYPE'] == 'STORE' ||
				!Manager::isB24() &&
				Manager::isStoreEnabled()
			),
			'fullPublication' => $this->arParams['FULL_PUBLICATION'] == 'Y',
			'urls' => $this->getUrls(
				$landing
			),
			'sliderConditions' => $this->getSliderConditions(),
			'sliderFullConditions' => $this->getSliderFullConditions(),
			'rights' => [
				'settings' => in_array(
					Rights::ACCESS_TYPES['sett'],
					$rights
				),
				'public' => in_array(
					Rights::ACCESS_TYPES['public'],
					$rights
				) && $this->arResult['FAKE_PUBLICATION']
			],
			'helperFrameOpenUrl' => !$uiInstalled ? null : \CHTTP::urlAddParams(\Bitrix\UI\Util::getHelpdeskUrl(true) . '/widget2/', [
				'url' => urlencode(
					(Manager::isHttps() ? 'https://' : 'http://') .
					Manager::getHttpHost() .
					Manager::getApplication()->getCurPageParam()
				),
				'user_id' => Manager::getUserId(),
				'is_cloud' => ModuleManager::isModuleInstalled('bitrix24') ? '1' : '0',
				'action' => 'open'
			]),
			'helpCodes' => [
				'form_general' => \Bitrix\Landing\Help::getHelpData('FORM_GENERAL', 'ru'),
				'widget_general' => \Bitrix\Landing\Help::getHelpData('WIDGET_GENERAL', 'ru')
			]
		];
	}

	/**
	 * In some times we need show popup about site is now creating.
	 * @param int $siteId Site id.
	 * @return boolean
	 */
	protected function isNeedFirstPreparePopup($siteId)
	{
		if (!Manager::isB24())
		{
			return false;
		}
		$date = new \Bitrix\Main\Type\DateTime;
		$res = Site::getList(array(
			'filter' => array(
				'ID' => $siteId,
				'>DOMAIN.DATE_MODIFY' => $date->add('-15 seconds')
			)
		));
		if ($row = $res->fetch())
		{
			return true;
		}
		return false;
	}

	/**
	 * Publication landing.
	 * @param int $id Landing id.
	 * @param bool $disabledRedirect Disable redirect after publication.
	 * @return boolean
	 */
	protected function actionPublication($id, $disabledRedirect = false)
	{
		static $publicIds = [];

		if (isset($publicIds[$id]))
		{
			return $publicIds[$id];
		}

		$landing = Landing::createInstance($id, [
			'skip_blocks' => true
		]);
		$context = \Bitrix\Main\Application::getInstance()->getContext();
		$request = $context->getRequest();
		$agreementExist = isset($this->arParams['AGREEMENT']) &&
						  !empty($this->arParams['AGREEMENT']);

		// agreement already display
		if (
			$agreementExist &&
			$request->get('agreement') == 'Y'
		)
		{
			$publicIds[$id] = false;
			return $publicIds[$id];
		}

		if ($landing->exist())
		{
			// display agreement
			$uriSave = new \Bitrix\Main\Web\Uri(
				$request->getRequestUri()
			);
			$uriSave->deleteParams(array(
				'agreement'
			));
			if (
				isset($this->arParams['AGREEMENT']) &&
				!empty($this->arParams['AGREEMENT'])
			)
			{
				$uriSave->addParams(array(
					'agreement' => 'Y'
				));
				\localRedirect($uriSave->getUri(), true);
			}
			if ($landing->publication())
			{
				$publicIds[$id] = true;
				// current landing is not area
				$areas = $landing->getAreas();
				if (!in_array($id, $areas))
				{
					foreach ($areas as $aId)
					{
						if (isset($publicIds[$aId]))
						{
							continue;
						}
						$landingArea = Landing::createInstance($aId, [
							'skip_blocks' => true
						]);
						$meta = $landingArea->getMeta();
						if ($meta['ACTIVE'] != 'Y')
						{
							$meta['PUBLIC'] = 'N';
						}
						if ($meta['PUBLIC'] == 'Y')
						{
							$publicIds[$aId] = true;
							continue;
						}
						if (
							$landingArea->exist() &&
							$landingArea->publication()
						)
						{
							$publicIds[$aId] = true;
						}
						else
						{
							$error = $landingArea->getError()->getFirstError();
							$this->addError(
								$error->getCode(),
								$error->getMessage()
							);
							return false;
						}
					}
				}
				if ($disabledRedirect)
				{
					return $publicIds[$id];
				}
				if ($this->isNeedFirstPreparePopup($landing->getSiteId()))
				{
					$this->addError(
						'SITE_IS_NOW_CREATING'
					);
					return false;
				}
				else if ($this->arParams['DONT_LEAVE_AFTER_PUBLICATION'] != 'Y')
				{
					$url = $landing->getPublicUrl(false, true, true);
					\localRedirect($this->getTimestampUrl($url), true);
				}
				else
				{
					$this->arResult['CUR_URI'] = $this->getUri(
						['success' => 'Y']
					);
					return true;
				}
			}
			else
			{
				$this->setErrors(
					$landing->getError()->getErrors()
				 );
				return false;
			}
		}

		$this->setErrors(
			$landing->getError()->getErrors()
		);

		$publicIds[$id] = false;
		return $publicIds[$id];
	}

	/**
	 * Publication all landing in site of current landing.
	 * @param int $id Landing id.
	 * @return bool
	 */
	protected function actionPublicationGlobal(int $id): bool
	{
		return $this->actionPublicationAll($id);
	}

	/**
	 * Publication all landing in site of current landing.
	 * @param int $id Landing id.
	 * @return bool
	 */
	protected function actionPublicationAll(int $id): bool
	{
		$landing = Landing::createInstance($id, [
			'skip_blocks' => true
		]);

		if ($landing->exist())
		{
			$pages = $this->getLandings(array(
				'filter' => array(
					'SITE_ID' => $landing->getSiteId(),
					[
						'LOGIC' => 'OR',
						['FOLDER_ID' => null],
						['!FOLDER_ID' => Folder::getFolderIdsForSite($landing->getSiteId(), ['=DELETED' => 'Y']) ?: [-1]]
					]
				)
			));
			foreach ($pages as $page)
			{
				if ($page['ACTIVE'] != 'Y')
				{
					$page['PUBLIC'] = 'N';
				}
				if ($page['PUBLIC'] == 'Y')
				{
					continue;
				}
				if (!$this->actionPublication($page['ID'], true))
				{
					return false;
				}
			}
			if ($this->isNeedFirstPreparePopup($landing->getSiteId()))
			{
				$this->addError(
					'SITE_IS_NOW_CREATING'
				);
				return false;
			}
			if ($this->arParams['DONT_LEAVE_AFTER_PUBLICATION'] != 'Y')
			{
				$url = $landing->getPublicUrl(false, true, true);
				\localRedirect($this->getTimestampUrl($url), true);
			}
			else
			{
				$this->arResult['CUR_URI'] = $this->getUri(
					['success' => 'Y']
				);
				return true;
			}
		}

		$this->setErrors(
			$landing->getError()->getErrors()
		);

		return false;
	}

	/**
	 * Cancel publication the landing.
	 * @param int $id Landing id.
	 * @return boolean
	 */
	protected function actionUnpublic($id)
	{
		$landing = Landing::createInstance($id, [
			'skip_blocks' => true
		]);

		if ($landing->exist())
		{
			if ($landing->unpublic())
			{
				return true;
			}
		}

		$this->setErrors(
			$landing->getError()->getErrors()
		);

		return false;
	}

	/**
	 * Gets sites count.
	 * @return int
	 */
	public function getSitesCount()
	{
		static $sitesCount = null;

		if (is_int($sitesCount))
		{
			return $sitesCount;
		}

		$filter = [
			'=TYPE' => $this->arParams['TYPE']
		];
		// in group mode exist only one site == current
		if ($this->arParams['TYPE'] == 'GROUP')
		{
			$filter['ID'] = $this->arParams['SITE_ID'];
		}

		$res = Site::getList(array(
			'select' => array(
				new \Bitrix\Main\Entity\ExpressionField(
					'CNT', 'COUNT(*)'
				)
			),
			'filter' => $filter
		));
		if ($row = $res->fetch())
		{
			$sitesCount = $row['CNT'];
		}
		else
		{
			$sitesCount = 0;
		}

		return $sitesCount;
	}

	/**
	 * Gets pages count of current site.
	 * @param int $siteId Site id.
	 * @return int
	 */
	public function getPagesCount($siteId = null)
	{
		static $sites = [];

		if ($siteId === null)
		{
			$siteId = $this->arParams['SITE_ID'];
		}

		if (isset($sites[$siteId]))
		{
			return $sites[$siteId];
		}

		$res = Landing::getList(array(
			'select' => array(
				new \Bitrix\Main\Entity\ExpressionField(
					'CNT', 'COUNT(*)'
				)
			),
			'filter' => array(
				'=SITE_ID' => $siteId
			)
		));
		if ($row = $res->fetch())
		{
			$sites[$siteId] = (int) $row['CNT'];
		}
		else
		{
			$sites[$siteId] = 0;
		}

		return $sites[$siteId];
	}

	/**
	 * Returns additional references for placeholders.
	 * @param int $siteId Site id.
	 * @return array
	 */
	protected function getReferences(int $siteId): array
	{
		$references = [];

		$crmContacts = \Bitrix\Landing\Connector\Crm::getContacts(
			$siteId
		);
		if (!empty($crmContacts['PHONE']))
		{
			$references[] = [
				'value' => 1,
				'text' => $crmContacts['PHONE']
			];
		}

		return $references;
	}

	/**
	 * Returns block section, opened by default.
	 * @param string $type Site type.
	 * @return string
	 */
	protected function getCurrentBlockSection(string $type): string
	{
		$storeKey = 'opened_types';
		$openedTypes = (array)$this->getUserOption($storeKey);
		if (!in_array($type, $openedTypes))
		{
			$openedTypes[] = $type;
			$this->setUserOption($storeKey, $openedTypes);
			switch ($type)
			{
				case 'PAGE':
				case 'STORE':
					return 'cover';
				case 'KNOWLEDGE':
					return 'recommended';
			}
		}

		return 'last';
	}

	protected function getCrmFormEditorData(int $formId): ?array
	{
		static $formEditorData = null;
		if (!is_array($formEditorData))
		{
			$formEditorData = \Bitrix\Landing\PublicAction\Form::getEditorData($formId)->getResult();
		}
		return $formEditorData;
	}

	/**
	 * Handler on view landing.
	 * @return void
	 */
	protected function onLandingView()
	{
		$type = mb_strtoupper($this->arParams['TYPE']);
		$landing = $this->arResult['LANDING'];
		$site = $this->arResult['SITE'];
		$params = $this->arParams;
		$arResult = $this->arResult;
		$eventManager = EventManager::getInstance();
		$eventManager->addEventHandler('landing', 'onLandingView',
			function(Event $event) use ($type, $params, $arResult, $landing, $site)
			{
				/** @var \Bitrix\Landing\Landing $landing */
				$result = new \Bitrix\Main\Entity\EventResult;
				$b24 = \Bitrix\Landing\Manager::isB24();
				$isStore = \Bitrix\Landing\Manager::isStoreEnabled();
				$options = $event->getParameter('options');
				$meta = $landing->getMeta();
				$options['url'] = $arResult['~LANDING_FULL_URL'] ?? $landing->getPublicUrl();
				$options['allow_svg'] = Manager::getOption('allow_svg_content') === 'Y';
				$options['ai_text_available'] = $arResult['AI_TEXT_AVAILABLE'];
				$options['copilot_available'] = $arResult['COPILOT_AVAILABLE'];
				$options['ai_text_active'] = $arResult['AI_TEXT_ACTIVE'];
				$options['ai_image_available'] = $arResult['AI_IMAGE_AVAILABLE'];
				$options['ai_image_active'] = $arResult['AI_IMAGE_ACTIVE'];
				$options['ai_unactive_info_code'] = $arResult['AI_UNACTIVE_INFO_CODE'];
				$options['allow_minisites'] = \Bitrix\Landing\Restriction\Form::isMinisitesAllowed();
				$options['folder_id'] = $landing->getFolderId();
				$options['version'] = Manager::getVersion();
				$options['default_section'] = $this->getCurrentBlockSection($type);
				$options['specialType'] = $this->arResult['SPECIAL_TYPE'];
				if (
					$options['specialType'] === Type::PSEUDO_SCOPE_CODE_FORMS
					&& Loader::includeModule('crm')
				)
				{
					$formId = $this->getFormIdByLandingId($landing->getId());
					$options['formEditorData'] = $formId ? $this->getCrmFormEditorData($formId) : [];
				}
				$options['tplCode'] = $meta['TPL_CODE'] ?: null;
				$options['params'] = (array)$params['PARAMS'];
				$options['params']['type'] = $params['TYPE'];
				$options['params']['draftMode'] = $params['DRAFT_MODE'] == 'Y';
				$options['params']['sef_url']['design_block'] = $arResult['TOP_PANEL_CONFIG']['urls']['designBlock'];
				if ($options['params']['draftMode'])
				{
					$options['params']['editor'] = [
						'externalUrlTarget' => '_blank'
					];
				}
				if (!$site['TPL_CODE'] && mb_strpos($site['XML_ID'], '|'))
				{
					[, $site['TPL_CODE']] = explode('|', $site['XML_ID']);
				}
				$options['sites_count'] = $this->getSitesCount();
				$options['pages_count'] = $this->getPagesCount($landing->getSiteId());
				$options['references'] = $this->getReferences($landing->getSiteId());
				$options['syspages'] = array();
				$options['helps'] = [
					'DYNAMIC_BLOCKS' => \Bitrix\Landing\Help::getHelpUrl('DYNAMIC_BLOCKS')
				];
				// features
				$options['features'] = [];
				if (($type === 'KNOWLEDGE' || $type === 'GROUP'))
				{
					$options['features'][] = 'diskFile';
				}
				// rights
				$options['rights'] = Rights::getOperationsForSite(
					$landing->getSiteId()
				);
				// placements
				$options['placements'] = array(
					'blocks' => array(),
					'image' => array()
				);
				$options['hooks'] = array(
					'YACOUNTER' => array(),
					'GACOUNTER' => array(),
					'B24BUTTON' => array()
				);
				if (!Manager::isB24())
				{
					unset($options['hooks']['B24BUTTON']);
				}
				$options['lastModified'] = isset($meta['DATE_MODIFY'])
					? $meta['DATE_MODIFY']->getTimestamp()
					: null;
				$options['sources'] = array_values(Selector::getSources([]));
				// gets default pages in this site
				// @todo: should refactor for several types (detail, ...)
				if ($options['sources'])
				{
					foreach ($options['sources'] as &$source)
					{
						$source['default'] = [
							'detail' => ''
						];
						$checkPages = [
							'detail' => []
						];
						// get available templates
						$demoPages = Demos::getPageList(
							'page',
							['section' => 'dynamic:' . $source['id']]
						)->getResult();
						foreach ($demoPages as $demoItem)
						{
							if (in_array('dynamic:detail', $demoItem['SECTION']))
							{
								$checkPages['detail'][] = $demoItem['ID'];
							}
						}
						if ($checkPages['detail'])
						{
							$res = Landing::getList([
								'select' => [
									'ID', 'TPL_CODE'
								],
								'filter' => [
									'SITE_ID' => $this->arParams['SITE_ID']
								],
								'order' => [
									'ID' => 'asc'
								]
							]);
							while ($row = $res->fetch())
							{
								if (in_array($row['TPL_CODE'], $checkPages['detail']))
								{
									$source['default']['detail'] = '#landing' . $row['ID'];
								}
							}
						}
					}
					unset($source);
				}
				// product type
				if (ModuleManager::isModuleInstalled('bitrix24'))
				{
					$options['productType'] = 'b24cloud';
				}
				else if (ModuleManager::isModuleInstalled('intranet'))
				{
					$options['productType'] = 'b24selfhosted';
				}
				else
				{
					$options['productType'] = 'smn';
				}
				// some hooks
				$hookSite = Hook::getForSite($params['SITE_ID']);
				$hookLanding = Hook::getForLanding($params['LANDING_ID']);
				foreach ($options['hooks'] as $hook => &$hookFields)
				{
					$fields = array();
					if (
						isset($hookLanding[$hook]) &&
						$hookLanding[$hook]->enabled()
					)
					{
						$fields = $hookLanding[$hook]->getFields();
					}
					elseif (
						isset($hookSite[$hook]) &&
						$hookSite[$hook]->enabled()
					)
					{
						$fields = $hookSite[$hook]->getFields();
					}
					foreach ($fields as $fieldCode => $field)
					{
						$hookFields[$fieldCode] = $field->getValue();
					}
				}
				unset($hookFields);
				// resolve button24's id
				if (isset($options['hooks']['B24BUTTON']['CODE']) && $options['hooks']['B24BUTTON']['CODE'] !== 'N')
				{
					foreach (Hook\Page\B24button::getButtonsData() as $button)
					{
						if (strpos($button['SCRIPT'], $options['hooks']['B24BUTTON']['CODE']) !== false)
						{
							$options['hooks']['B24BUTTON']['ID'] = $button['ID'];
							break;
						}
					}
				}
				// get system pages
				foreach (Syspage::get($landing->getSiteId()) as $code => $page)
				{
					$options['syspages'][$code] = array(
						'landing_id' => $page['LANDING_ID'],
						'name' => $page['TITLE']
					);
				}
				if ($indexPageId = $this->arResult['SITE']['LANDING_ID_INDEX'])
				{
					$res = Landing::getList([
						'select' => [
							'TITLE'
						],
						'filter' => [
							'ID' => $indexPageId,
							'CHECK_PERMISSIONS' => 'N'
						]
				 	]);
					if ($row = $res->fetch())
					{
						$options['syspages']['mainpage'] = array(
							'landing_id' => $indexPageId,
							'name' => $row['TITLE']
						);
					}
				}
				// special check for type = SMN
				if ($options['params']['type'] == 'SMN')
				{
					if (isset($options['syspages']['catalog']))
					{
						$options['params']['type'] = 'STORE';
					}
				}
				if (Loader::includeModule('bitrix24'))
				{
					$options['license'] = \CBitrix24::getLicenseType();
				}
				// redefine options
				if (Loader::includeModule('rest'))
				{
					// add placements
					$res = \Bitrix\Rest\PlacementTable::getList(array(
						'select' => array(
							'ID', 'APP_ID', 'PLACEMENT', 'TITLE',
							'APP_NAME' => 'REST_APP.APP_NAME'
						),
						'filter' => array(
							array(
								'LOGIC' => 'OR',
								['PLACEMENT' => 'LANDING_BLOCK_%'],
								['=PLACEMENT' => 'LANDING_IMAGE']
							)
						),
						'order' => array(
							'ID' => 'DESC'
						)
					));
					while ($row = $res->fetch())
					{
						$placementType = ($row['PLACEMENT'] == 'LANDING_IMAGE')
										? 'image'
										: 'blocks';
						$row['PLACEMENT'] = mb_strtolower(mb_substr($row['PLACEMENT'], 14));
						if (!isset($options['placements'][$placementType][$row['PLACEMENT']]))
						{
							$options['placements'][$placementType][$row['PLACEMENT']] = array();
						}
						$options['placements'][$placementType][$row['PLACEMENT']][$row['ID']] = array(
							'id' => $row['ID'],
							'placement' => $row['PLACEMENT'],
							'app_id' => $row['APP_ID'],
							'title' => trim($row['TITLE'])
										? $row['TITLE']
										: $row['APP_NAME']
						);
					}
				}
				$result->modifyFields(array(
					'options' => $options
				));
				return $result;
			}
		);
	}

	/**
	 * Handler on template epilog.
	 * @return void
	 */
	protected function onEpilog()
	{
		$eventManager = EventManager::getInstance();
		$eventManager->addEventHandler('main', 'OnEpilog',
			function()
			{
				Manager::initAssets($this->arParams['LANDING_ID']);
			}
		);
	}

	/**
	 * Gets get some system urls for template.
	 * @param Landing $landing Landing instance.
	 * @param array $site Site row.
	 * @return \Bitrix\Main\Web\Uri[]
	 */
	protected function getUrls(Landing $landing, $site = null)
	{
		if ($site === null)
		{
			$site = $this->getSites([
				'filter' => [
					'ID' => $landing->getSiteId()
				]
			]);
			$site = array_shift($site);
		}

		$replaceParamUrl = function($sefCode) use($landing)
		{
			static $sefUrls = null;
			if ($sefUrls === null)
			{
				$sefUrls = isset($this->arParams['SEF'])
					? $this->arParams['SEF']
					: (
					isset($this->arParams['PARAMS']['sef_url'])
						? $this->arParams['PARAMS']['sef_url']
						: ''
					);
			}
			if (!isset($sefUrls[$sefCode]))
			{
				return '';
			}
			$urlReplace = [
				'#site_show#' => $landing->getSiteId(),
				'#site_edit#' => $landing->getSiteId(),
				'#landing_edit#' => $landing->getId()
			];
			return str_replace(
				array_keys($urlReplace),
				array_values($urlReplace),
				$sefUrls[$sefCode]
			);
		};

		$urls = [];
		$curUrl = $replaceParamUrl('landing_view');
		$sessId = bitrix_sessid();
		$urlsConfig = [
			'publication' => [
				'param' => $landing->getId(),
				'code' => $landing->getXmlId(),
				'site_code' => $site['XML_ID'],
				'sessid' => $sessId
			],
			'publicationAll' => [
				'param' => $landing->getId(),
				'site_id' => $landing->getSiteId(),
				'code' => $landing->getXmlId(),
				'site_code' => $site['XML_ID'],
				'sessid' => $sessId
			],
			'publicationGlobal' => [
				'param' => $landing->getId(),
				'site_id' => $landing->getSiteId(),
				'code' => $landing->getXmlId(),
				'site_code' => $site['XML_ID']
			],
			'preview' => [
				'landing_mode' => 'preview',
				'param' => $landing->getId(),
				'code' => $landing->getXmlId(),
				'site_code' => $site['XML_ID'],
				'sessid' => $sessId
			]
		];
		foreach ($urlsConfig as $code => $config)
		{
			$config['action'] = $code;
			$uri = new \Bitrix\Main\Web\Uri($curUrl);
			$uri->addParams($config);
			$urls[$code] = $uri;
		}

		$urls['preview_device'] = new \Bitrix\Main\Web\Uri(
			$this->getDevicePreview($landing->getId())
		);
		$urls['landings'] = new \Bitrix\Main\Web\Uri(
			$replaceParamUrl('site_show')
		);
		$urls['landingView'] = new \Bitrix\Main\Web\Uri(
			$replaceParamUrl('landing_view')
		);
		$urls['designBlock'] = new \Bitrix\Main\Web\Uri(
			str_replace('#', '__', $this->arParams['PARAMS']['sef_url']['landing_view'] ?? '')
		);
		$urls['designBlock']->addParams([
			'design_block' => '__block_id__'
		]);
		$urls['landingEdit'] = new \Bitrix\Main\Web\Uri(
			$replaceParamUrl('landing_edit')
		);
		$urls['landingDesign'] = new \Bitrix\Main\Web\Uri(
			$replaceParamUrl('landing_design')
		);
		$urls['landingSiteEdit'] = new \Bitrix\Main\Web\Uri(
			$replaceParamUrl('site_edit')
		);
		$urls['landingSiteDesign'] = new \Bitrix\Main\Web\Uri(
			$replaceParamUrl('site_design')
		);
		$urls['landingCatalogEdit'] = new \Bitrix\Main\Web\Uri(
			$replaceParamUrl('site_edit')
		);
		$urls['landingCatalogEdit']->addParams([
			'tpl' => 'catalog'
		]);
		$urls['landingFrame'] = new \Bitrix\Main\Web\Uri(
			$replaceParamUrl('landing_view')
		);
		$urls['landingFrame']->addParams([
			'landing_mode' => 'edit'
		]);
		if ($this->arParams['DONT_LEAVE_AFTER_PUBLICATION'] == 'Y')
		{
			$urls['landingFrame']->addParams([
				'IFRAME' => 'Y'
			]);
		}

		return $urls;
	}

	/**
	 * Gets conditions for slider init.
	 * @return array
	 */
	protected function getSliderConditions(): array
	{
		$sliderConditions = [];

		$sliderUrlKeys = [
			'landing_settings',
			'site_settings',
		];
		$sefUrls = isset($this->arParams['SEF'])
					? $this->arParams['SEF']
					: (
						isset($this->arParams['PARAMS']['sef_url'])
						? $this->arParams['PARAMS']['sef_url']
						: []
					);
		foreach ($sliderUrlKeys as $key)
		{
			if (isset($sefUrls[$key]) && $sefUrls[$key])
			{
				$url = $sefUrls[$key];
				$url = str_replace(
					['#site_show#', '#site_edit#', '#landing_edit#', '?'],
					['[0-9]+', '[0-9]+', '[0-9]+', '\\?'],
					$url
				);
				$sliderConditions[$key] = $url;
			}
		}

		if (isset($sliderConditions['site_show']))
		{
			$sliderConditions['site_show'] .= '(?!view)';
		}

		return array_values($sliderConditions);
	}

	/**
	 * Gets conditions for slider init, open in fullscrean
	 * @return array
	 */
	public function getSliderFullConditions(): array
	{
		$conditions = [
			$this->getUrlAddSidepanelCondition(false),
		];

		return $conditions;
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
			$this->checkParam('SITE_ID', 0);
			$this->checkParam('LANDING_ID', 0);
			$this->checkParam('TYPE', '');
			$this->checkParam('PAGE_URL_URL_SITES', '');
			$this->checkParam('PAGE_URL_LANDINGS', '');
			$this->checkParam('PAGE_URL_LANDING_EDIT', '');
			$this->checkParam('PAGE_URL_LANDING_DESIGN', '');
			$this->checkParam('PAGE_URL_SITE_EDIT', '');
			$this->checkParam('FULL_PUBLICATION', 'N');
			$this->checkParam('PANEL_LIGHT_MODE', 'N');
			$this->checkParam('DONT_LEAVE_AFTER_PUBLICATION', 'N');
			$this->checkParam('DRAFT_MODE', 'N');
			$this->checkParam('LANDING_TPL_PREVIEW_EXIT', '');
			$this->checkParam('PUBLICATION_ERROR_LINK', '');
			$this->checkParam('PARAMS', array());

			$this->forceUpdateNewFolders($this->arParams['SITE_ID']);

			Type::setScope(
				$this->arParams['TYPE']
			);

			Hook::setEditMode();
			Landing::setEditMode();
			$landing = Landing::createInstance($this->arParams['LANDING_ID']);

			// ai
			$this->arResult['AI_TEXT_AVAILABLE'] = \Bitrix\Landing\Connector\Ai::isTextAvailable();
			$this->arResult['COPILOT_AVAILABLE'] = \Bitrix\Landing\Connector\Ai::isCopilotAvailable();
			$this->arResult['AI_TEXT_ACTIVE'] = \Bitrix\Landing\Connector\Ai::isTextActive();
			$this->arResult['AI_IMAGE_AVAILABLE'] = \Bitrix\Landing\Connector\Ai::isImageAvailable();
			$this->arResult['AI_IMAGE_ACTIVE'] = \Bitrix\Landing\Connector\Ai::isImageActive();
			$this->arResult['AI_UNACTIVE_INFO_CODE'] = self::getAiUnactiveInfoCode();

			$this->arResult['AUTO_PUBLICATION_ENABLED'] = \CUserOptions::getOption('landing', 'auto_publication', 'Y') === 'Y';
			$this->arResult['SUCCESS_SAVE'] = $this->request('success') === 'Y';
			$this->arResult['LANDING'] = $landing;
			$this->arResult['FAKE_PUBLICATION'] = !$this->arResult['AUTO_PUBLICATION_ENABLED']
			                                      || ($this->arParams['DRAFT_MODE'] === 'Y')
			                                      || $landing->fakePublication();

			if (
				Loader::includeModule('intranet')
				&& $this->arParams['TYPE'] === Site\Type::SCOPE_CODE_MAINPAGE
			)
			{
				$publisher = new Intranet\MainPage\Publisher();
				$this->arResult['MAINPAGE_IS_PUBLIC'] =	$publisher->isPublished();
				$this->arResult['AI_TEXT_AVAILABLE'] = false;
				$this->arResult['COPILOT_AVAILABLE'] = false;
				$this->arResult['AI_IMAGE_AVAILABLE'] = false;
			}

			if (
				$this->arParams['TYPE'] === 'STORE'
				&& \Bitrix\Main\Loader::includeModule('catalog')
				&& \Bitrix\Catalog\Config\State::isExternalCatalog()
			)
			{
				$landingMeta = $landing->getMeta();
				if (!str_starts_with($landingMeta['TPL_CODE'], 'store-chats'))
				{
					$this->arResult['FAKE_PUBLICATION'] = false;
					$this->arResult['PUBLICATION_ERROR_CODE'] = 'shop1c';
					$this->arResult['PUBLICATION_ERROR_LINK'] = \Bitrix\Landing\Help::getHelpUrl('SHOP1C');
				}
			}
			$this->arResult['~LANDING_FULL_URL'] = $landing->getPublicUrl(
				false,
				true,
				$this->arParams['DRAFT_MODE'] !== 'Y'
			);
			$this->arResult['LANDING_FULL_URL'] = $this->getTimestampUrl(
				$this->arResult['~LANDING_FULL_URL']
			);

			if ($landing->exist())
			{
				\Bitrix\Landing\Site\Version::update($landing->getSiteId(), $landing->getMeta()['SITE_VERSION']);

				$this->arResult['SPECIAL_TYPE'] = $this->getSpecialTypeSiteByLanding($landing);

				// tmp fix for checking crm rights
				if ($this->arResult['SPECIAL_TYPE'] === \Bitrix\Landing\Site\Type::PSEUDO_SCOPE_CODE_FORMS)
				{
					if (Loader::includeModule('crm'))
					{
						if (!\Bitrix\Crm\WebForm\Manager::checkWritePermission())
						{
							$this->addError('LANDING_ERROR_PAGE_NOT_FOUND', '', true);
							parent::executeComponent();
							return;
						}
					}
					$this->checkFormInLanding($landing);
				}

				$this->arResult['SITES_COUNT'] = $this->getSitesCount();
				$this->arResult['PAGES_COUNT'] = $this->getPagesCount($landing->getSiteId());
				$this->arResult['SITE'] = $this->getSites(array(
					'select' => array(
						'DOMAIN_NAME' => 'DOMAIN.DOMAIN'
					),
					'filter' => array(
						'ID' => $landing->getSiteId()
					)
				), true);
				if ($this->arResult['SITE'])
				{
					$this->arResult['SITE'] = array_pop($this->arResult['SITE']);
				}
				else
				{
					\localRedirect($this->getRealFile());
				}
				// disable optimisation
				if (\Bitrix\Landing\Manager::isB24())
				{
					$asset = \Bitrix\Main\Page\Asset::getInstance();
					$asset->disableOptimizeCss();
					$asset->disableOptimizeJs();
				}
				// can publication / edit settings for page?
				if ($this->arResult['SPECIAL_TYPE'])
				{
					$this->arResult['CAN_PUBLICATION_PAGE'] = true;
					$this->arResult['CAN_PUBLICATION_SITE'] = true;
				}
				else
				{
					$canPublication = Manager::checkFeature(
						Manager::FEATURE_PUBLICATION_PAGE,
						array(
							'filter' => array(
								'!ID' => $landing->getId()
							)
						)
					);
					$this->arResult['CAN_PUBLICATION_PAGE'] = $canPublication;
					if ($canPublication)
					{
						$canPublication = Manager::checkFeature(
							Manager::FEATURE_PUBLICATION_SITE,
							array(
								'filter' => array(
									'!ID' => $landing->getSiteId()
								),
								'type' => $this->arParams['TYPE']
							)
						);
						$this->arResult['CAN_PUBLICATION_SITE'] = $canPublication;
					}
				}
				$rights = Rights::getOperationsForSite(
					$landing->getSiteId()
				);
				$this->arResult['CAN_SETTINGS_SITE'] = in_array(
					Rights::ACCESS_TYPES['sett'],
					$rights
				);
				$this->arResult['CAN_PUBLIC_SITE'] = in_array(
					Rights::ACCESS_TYPES['public'],
					$rights
				);
				$this->arResult['CAN_EDIT_SITE'] = in_array(
					Rights::ACCESS_TYPES['edit'],
					$rights
				);
				$this->arResult['TOP_PANEL_CONFIG'] = $this->getTopPanelConfig(
					$landing,
					$this->arResult['SITE'],
					$rights
				);

				// params for analytics
				$urlAddParams = [];
				if ($this->arResult['SPECIAL_TYPE'])
				{
					$urlAddParams['specType'] = $this->arResult['SPECIAL_TYPE'];
				}
				$urlAddParams['context_section'] = 'page_view';
				$urlAddParams['context_element'] = 'create_page_link';
				$this->arParams['PAGE_URL_LANDING_ADD'] = $this->getUrlAdd(false, $urlAddParams);

				$urlAddParams['replaceLid'] = $this->arParams['LANDING_ID'];
				$urlAddParams['context_section'] = 'block_style';
				$urlAddParams['context_element'] = 'create_template_button';
				$this->arParams['PAGE_URL_LANDING_REPLACE_FROM_STYLE'] = $this->getUrlAdd(
					false,
					$urlAddParams,
					Manager::getMarketCollectionId('form_minisite')
				);


				if (Loader::includeModule('bitrix24'))
				{
					$this->arResult['LICENSE'] = \CBitrix24::getLicenseType();
				}

				$formId = $this->getFormIdByLandingId($landing->getId());
				$this->arResult['FORM_VERIFICATION_REQUIRED'] = $formId && !self::isFormVerified($formId);
				$this->arResult['VERIFY_FORM_ID'] = $formId;

				if ($this->arResult['SPECIAL_TYPE'] == Site\Type::PSEUDO_SCOPE_CODE_FORMS)
				{
					$crmFormEditorData = $formId ? $this->getCrmFormEditorData($formId) : [];
					if (
						isset($crmFormEditorData['formOptions'])
						&& is_array($crmFormEditorData['formOptions'])
						&& isset($crmFormEditorData['formOptions']['name'])
					)
					{
						$this->arResult['FORM_NAME'] = $crmFormEditorData['formOptions']['name'];
					}
				}
				$this->arResult['IS_AREA'] = TemplateRef::landingIsArea($landing->getId());

				$this->onLandingView();
				$this->onEpilog();
			}
			else
			{
				$this->addError('LANDING_ERROR_PAGE_NOT_FOUND', '', true);
			}


			// some errors?
			$this->setErrors(
				$landing->getError()->getErrors()
			);
		}

		parent::executeComponent();
	}

	private static function isFormVerified(int $formId): bool
	{
		if (Loader::includeModule('bitrix24'))
		{
			$validatedLicenseType = [
				'project',
				'demo'
			];

			if (in_array(\CBitrix24::getLicenseType(), $validatedLicenseType, true))
			{
				return (new \Bitrix\Bitrix24\PhoneVerify(
					self::PHONE_VERIFY_ENTITY_FORM,
					$formId
				))->isVerified();
			}
		}

		return true;
	}

	private function getFormIdByLandingId(int $landingId): ?int
	{
		static $formId = null;

		if ($formId === null && Loader::includeModule('crm'))
		{
			$res = \Bitrix\Crm\WebForm\Internals\LandingTable::getList([
				'select' => [
					'FORM_ID'
				],
				'filter' => [
					'LANDING_ID' => $landingId,
				]
			]);

			if ($row = $res->fetch())
			{
				$formId = (int)$row['FORM_ID'];
			}
		}

		return $formId;
	}
}
