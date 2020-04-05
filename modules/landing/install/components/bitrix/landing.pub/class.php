<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Entity;
use \Bitrix\Landing\Hook;
use \Bitrix\Landing\Manager;
use \Bitrix\Landing\Landing;
use \Bitrix\Landing\Domain;
use \Bitrix\Landing\Site;
use \Bitrix\Landing\Syspage;
use \Bitrix\Landing\TemplateRef;
use \Bitrix\Landing\Rights;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\EventManager;
use \Bitrix\Main\Config\Option;
use \Bitrix\Main\Application;
use \Bitrix\Main\Event;
use \Bitrix\Crm\UI\Webpack\CallTracker;

Loc::loadMessages(__FILE__);

\CBitrixComponent::includeComponentClass('bitrix:landing.base');

class LandingPubComponent extends LandingBaseComponent
{
	/**
	 * Special page - robots.txt
	 * @var boolean
	 */
	protected $isRobotsTxt = false;

	/**
	 * Special page - sitemap.xml
	 * @var boolean
	 */
	protected $isSitemapXml = false;

	/**
	 * Is preview mode.
	 * @var boolean
	 */
	protected $isPreviewMode = false;

	/**
	 * SEF variables.
	 * @var array
	 */
	protected $sefVariables = array();

	/**
	 * Dynamic filter id.
	 * @var int
	 */
	protected $dynamicFilterId = 0;

	/**
	 * Dynamic element id.
	 * @var int
	 */
	protected $dynamicElementId = 0;

	/**
	 * Current zone.
	 * @var string
	 */
	protected $zone = '';

	/**
	 * Http status was send.
	 * @var bool
	 */
	protected $httpStatusSend = false;

	/**
	 * Main instance of current page.
	 * @var array
	 */
	protected static $landingMain = null;

	/**
	 * Gets main instance of current page.
	 * @return array
	 */
	public static function getMainInstance()
	{
		return self::$landingMain;
	}

	/**
	 * Get base domain of service by lang.
	 * @return string
	 */
	protected function getParentDomain()
	{
		static $domain = null;

		if ($domain !== null)
		{
			return $domain;
		}

		$domains = \Bitrix\Landing\Help::getDomains();

		if (isset($domains[$this->zone]))
		{
			$domain = $domains[$this->zone];
		}
		else
		{
			$domain = $domains['en'];
		}

		return $domain;
	}

	/**
	 * Gets path for copyright link.
	 * @param string $section Code of section.
	 * @return string
	 */
	protected function getCopyLinkPath($section = 'websites')
	{
		static $paths = [
			'bitrix24_logo' => [
				'en' => '/'
			],
			'create' => [
				'en' => '/create.php?b24_type=sites'
			],
			'websites' => [
				'ru' => '/features/sites.php',
				'ua' => '/features/sites.php',
				'by' => '/features/sites.php',
				'kz' => '/features/sites.php',
				'en' => '/tools/websites/'
			],
			'crm' => [
				'ru' => '/features/',
				'ua' => '/features/',
				'by' => '/features/',
				'kz' => '/features/',
				'en' => '/tools/crm/'
			]
		];
		if (isset($paths[$section][$this->zone]))
		{
			return $paths[$section][$this->zone];
		}
		else
		{
			return $paths[$section]['en'];
		}
	}

	/**
	 * Get adv campaign code.
	 * @param string $type Type of the link.
	 * @return string
	 */
	protected function getAdvCode($type = 'bitrix24_logo')
	{
		static $domain = null;
		static $domainPart = null;
		static $partnerId = null;

		if ($domain === null)
		{
			$domain = $this->getParentDomain();
			$domainPart = array_pop(explode('.', $domain));
		}
		if ($partnerId === null)
		{
			$partnerId = (int)Option::get('bitrix24', 'partner_id', 0);
		}

		// base part
		$codes = [
			'utm_medium' => 'referral',
			'utm_source' => $domain,
			'utm_campaign' => $domainPart . '_sites_' . $type
		];
		if ($partnerId)
		{
			$codes['p'] = $partnerId;
		}

		return http_build_query($codes, '', '&amp;');
	}

	/**
	 * Build and gets link for different links in the copyright.
	 * @param string $type Type of the link.
	 * @param bool $addAdvCode Add or not adv code.
	 * @return string
	 */
	public function getRefLink($type, $addAdvCode = true)
	{
		static $partnerId = null;

		if ($partnerId === null)
		{
			$partnerId = (int)Option::get('bitrix24', 'partner_id', 0);
		}

		$link = 'https://' . $this->getParentDomain();
		$link .= $this->getCopyLinkPath($type);

		if ($addAdvCode)
		{
			$link .= (strpos($link, '?') === false) ? '?' : '&amp;';
			$link .= $this->getAdvCode($type);
		}
		else if ($partnerId)
		{
			$link .= (strpos($link, '?') === false) ? '?' : '&amp;';
			$link .= 'p=' . $partnerId;
		}

		return $link;
	}

	/**
	 * Send only first http status.
	 * @param string $code Http status code.
	 * @return void
	 */
	protected function setHttpStatusOnce($code)
	{
		if (!$this->httpStatusSend)
		{
			$this->httpStatusSend = true;
			$event = new Event('landing', 'onPubHttpStatus', array(
				'code' => $code
			));
			$event->send();
			foreach ($event->getResults() as $result)
			{
				if ($modified = $result->getModified())
				{
					if (isset($modified['code']))
					{
						$code = $modified['code'];
					}
				}
			}
			\CHTTP::setStatus($code);
		}
	}

	/**
	 * Clear status that http status was send.
	 * @return void
	 */
	protected function clearHttpStatus()
	{
		$this->httpStatusSend = false;
	}

	/**
	 * Detect landing by path.
	 * @return int|false Detected landing id or false.
	 */
	protected function detectPage()
	{
		$server = Application::getInstance()->getContext()->getServer();

		// preview mode for templates only
		$previewTemplate = $this->request('preview') == 'Y';

		// parse url
		$serverHost = $this->arParams['HTTP_HOST'];
		$requestedPage = '/' . $this->arParams['PATH'];
		$urlParts = parse_url($requestedPage);
		if (isset($urlParts['path']))
		{
			$requestedPage = $urlParts['path'];
		}
		$requestedPage = trim($requestedPage, '/');
		$requestedPageParts = explode('/', $requestedPage);
		// compatibility mode, before detect page we need to know
		// is it SMN site (after transfer SMN>B24) or typical b24 site
		$realFilePath = $this->getRealFile();
		if (
			Manager::isExtendedSMN() &&
			$this->arParams['DRAFT_MODE'] != 'Y' &&
			strpos($realFilePath, Manager::getPublicationPath()) === 0 &&
			strpos($realFilePath, Manager::getPublicationPathConst()) !== 0
		)
		{
			Manager::forceB24disable(true);
		}
		if (Manager::isB24())
		{
			$siteUrl = array_shift($requestedPageParts);
			$siteId = $siteUrl;
		}
		// in smn detect site dir auto
		else
		{
			$siteUrl = '';
			$res = Site::getList(array(
				'select' => array(
					'ID', 'CODE'
				),
				'filter' => $this->arParams['SITE_ID']
						? [
							'ID' => $this->arParams['SITE_ID'],
							'CHECK_PERMISSIONS' => $this->arParams['CHECK_PERMISSIONS']
						]
						: [
							'=SMN_SITE_ID' => SITE_ID,
							'=TYPE' => 'SMN',
							'CHECK_PERMISSIONS' => $this->arParams['CHECK_PERMISSIONS']
						],
				'order' => array(
					'ID' => 'desc'
				)
			));
			if ($row = $res->fetch())
			{
				$siteUrl = trim($row['CODE'], '/');
				$siteId = $row['ID'];
			}
		}

		// detect preview mode
		if ($this->arParams['DRAFT_MODE'] == 'Y')
		{
			$this->isPreviewMode = true;
		}
		else if (
			// for base work
			(
				$requestedPageParts[0] == 'preview' &&
				$requestedPageParts[1] == Site::getPublicHash($siteId)
			)
			||
			// for cloud version
			(
				$this->request('landing_mode') == 'preview' &&
				$this->request('hash') == Site::getPublicHash($siteId)
			)
		)
		{
			$this->isPreviewMode = true;
			if ($requestedPageParts[0] == 'preview')
			{
				array_shift($requestedPageParts);
				array_shift($requestedPageParts);
			}
		}

		$landingUrl = array_shift($requestedPageParts);
		$landingSubUrl = $requestedPageParts ? implode('/', $requestedPageParts) : '';
		$landingCodeOriginal = null;

		// check dynamic content
		$parseDynamic = $landingSubUrl ? $landingSubUrl : $landingUrl;
		if (preg_match('#^([\w]+)\_([\d]+)\_([\d]+)$#', $parseDynamic, $matches))
		{
			$this->dynamicFilterId = $matches[2];
			$this->dynamicElementId = $matches[3];
			if ($landingSubUrl)
			{
				$landingCodeOriginal = $landingSubUrl;
				$landingSubUrl = $matches[1];
			}
			else
			{
				$landingCodeOriginal = $landingUrl;
				$landingUrl = $matches[1];
			}
		}
		unset($matches, $matches);

		// if preview mode
		if ($previewTemplate)
		{
			$site = $this->getSites(array(
				'select' => array(
					'ID', 'CODE'
				),
				'filter' => array(
					'=TYPE' => 'PREVIEW'
				),
				'limit' => 1
			));
			$site = array_shift($site);
			$siteUrl = trim($site['CODE'], '/');
		}

		$landingSubUrl = str_replace(
			array('/index.php', 'index.php'),
			'',
			$landingSubUrl
		);

		// system pages
		if ($landingUrl == 'robots.php')
		{
			$landingUrl = '';
			$this->isRobotsTxt = true;
		}
		elseif ($landingUrl == 'sitemap.php')
		{
			$landingUrl = '';
			$this->isSitemapXml = true;
		}
		elseif ($landingUrl == 'favicon' || $landingUrl == 'favicon.php')
		{
			$path = '/bitrix/components/bitrix/landing.pub/favicon.ico';
			$hooksSite = Hook::getForSite($siteId);
			if (isset($hooksSite['FAVICON']))
			{
				$fields = $hooksSite['FAVICON']->getFields();
				if (
					isset($fields['PICTURE']) &&
					$fields['PICTURE']->getValue()
				)
				{
					$path = \Bitrix\Landing\File::getFilePath(
						$fields['PICTURE']->getValue()
					);
				}
			}

			Manager::getApplication()->restartBuffer();
			header('Content-type: image/x-icon');

			if (substr($path, 0, 1) == '/')
			{
				echo \Bitrix\Main\IO\File::getFileContents(
					Manager::getDocRoot() . $path
				);
			}
			else
			{
				$response = Application::getInstance()->getContext()->getResponse();
				$client = new \Bitrix\Main\Web\HttpClient;
				$contents = $client->get($path);
				$response->addHeader('Content-Type', $client->getContentType());
				$response->flush($contents);
			}
			die();
		}

		$landingIdExec = false;
		$landingIdIndex = false;
		$landingId404 = false;

		// first detect site
		if (preg_match('#^([\d]+)$#', $siteUrl, $matches))
		{
			$filter = array(
				'ID' => $matches[1],
				'=DELETED' => ['Y', 'N']
			);
		}
		else
		{
			$filter = array(
				'=CODE' => '/' . $siteUrl . '/',//@todo fixme
				'=DELETED' => ['Y', 'N']
			);
		}
		if ($this->arParams['SITE_TYPE'])
		{
			$filter['=TYPE'] = $this->arParams['SITE_TYPE'];
		}
		if (
			$serverHost &&
			!$previewTemplate &&
			!Manager::isCloudDisable()
		)
		{
			if (strpos($serverHost, ':') !== false)
			{
				list($serverHost, ) = explode(':', $serverHost);
			}
			// set www alias
			if (substr($serverHost, 0, 4) == 'www.')
			{
				$filter['=DOMAIN.DOMAIN'] = [
					$serverHost,
					substr($serverHost, 4)
				];
			}
			else
			{
				$filter['=DOMAIN.DOMAIN'] = [
					$serverHost,
					'www.' . $serverHost
				];
			}
		}
		$filter['CHECK_PERMISSIONS'] = $this->arParams['CHECK_PERMISSIONS'];
		$res = Site::getList(array(
			'select' => array(
				'ID', 'ACTIVE', 'DELETED',
				'LANDING_ID_404', 'LANDING_ID_503',
				'LANDING_ID_INDEX', 'DOMAIN_ID'
			),
			'filter' => $filter
		));
		if (!($site = $res->fetch()))
		{
			return $landingIdExec;
		}
		if (
			!$site['DOMAIN_ID'] &&
			$this->arParams['NOT_CHECK_DOMAIN'] != 'Y'
		)
		{
			return $landingIdExec;
		}

		// unactive site
		if (
			(
				!$this->isPreviewMode &&
				$site['ACTIVE'] == 'N'
			)
			||
			$site['DELETED'] == 'Y'
		)
		{
			if (Manager::isB24())
			{
				$this->setHttpStatusOnce($this::ERROR_STATUS_FORBIDDEN);
			}
			return $landingIdExec;
		}

		self::$landingMain['SITE_ID'] = $site['ID'];

		// site is down
		if (
			$site['LANDING_ID_503'] &&
			!$this->isPreviewMode
		)
		{
			$this->setHttpStatusOnce($this::ERROR_STATUS_UNAVAILABLE);
			return $site['LANDING_ID_503'];
		}

		/**
		 * Local function for iteration below,
		 * if record is un active, send 403 status.
		 * @param array $row Row array.
		 * @return int|bool
		 */
		$checkExecId = function(array $row)
		{
			if (
				(
					!$this->isPreviewMode &&
					$row['ACTIVE'] == 'N'
				)
				||
				$row['DELETED'] == 'Y'
			)
			{
				if (Manager::isB24())
				{
					$this->setHttpStatusOnce(
						$this::ERROR_STATUS_FORBIDDEN
					);
				}
				return false;
			}

			return $row['ID'];
		};

		// detect landing by sef rule
		//'/' . $landingUrl . '/' . $landingSubUrl . '/'

		// detect landing
		$codeFilter = ($landingCodeOriginal === null)
					? $landingUrl
					: [$landingUrl, $landingCodeOriginal];
		$res = Landing::getList(array(
			'select' => array(
				'ID', 'CODE', 'RULE',
				'FOLDER', 'ACTIVE', 'DELETED'
			),
			'filter' => array(
				'SITE_ID' => $site['ID'],
				'FOLDER_ID' => false,
				'=DELETED' => ['Y', 'N'],
				'CHECK_PERMISSIONS' => $this->arParams['CHECK_PERMISSIONS'],
				array(
					'LOGIC' => 'OR',
					'=CODE' => $codeFilter,
					'!=RULE' => false,
					array(
						'ID' => $site['LANDING_ID_404']
					),
					array(
						'ID' => $site['LANDING_ID_INDEX']
					)
				),
				$this->isPreviewMode
				? array()
				: array(
					'LOGIC' => 'OR',
					'=ACTIVE' => ['Y', 'N'],
					'ID' => $site['LANDING_ID_INDEX']
				)
			),
			'order' => array(
				'ID' => 'asc'
			)
		));
		$codeFilter = (array)$codeFilter;
		$codeFilter = array_map('strtolower', $codeFilter);
		while (($landing = $res->fetch()))
		{
			// if it's index and not active
			if (
				!$this->isPreviewMode &&
				$landing['ACTIVE'] != 'Y' &&
				$site['LANDING_ID_INDEX'] == $landing['ID']
			)
			{
				$landingIdIndex = -1 * $landing['ID'];
				continue;
			}
			// another checking
			if (in_array(strtolower($landing['CODE']), $codeFilter))
			{
				if ($landingSubUrl)
				{
					if (
						$landing['FOLDER'] == 'Y' &&
						($landing['ACTIVE'] == 'Y' || $this->isPreviewMode)
					)
					{
						// check landing in subfolder
						$resSub = Landing::getList(array(
							'select' => array(
								'ID', 'CODE', 'RULE', 'ACTIVE', 'DELETED'
							),
							'filter' => array(
								'SITE_ID' => $site['ID'],
								'=DELETED' => ['Y', 'N'],
								'CHECK_PERMISSIONS' => $this->arParams['CHECK_PERMISSIONS'],
								array(
									'LOGIC' => 'OR',
									'ID' => $landing['ID'],
									'FOLDER_ID' => $landing['ID']
								),
								array(
									'LOGIC' => 'OR',
									'CODE' => $landingSubUrl,
									'!=RULE' => false
								)
							),
							'order' => array(
								'ID' => 'asc'
							)
						));
						while ($row = $resSub->fetch())
						{
							if ($row['CODE'] == $landingSubUrl)
							{
								$landingIdExec = $checkExecId($row);
							}
							else if (
								$row['RULE'] &&
								preg_match('@^'. trim($row['RULE']) . '$@i', $landingSubUrl, $matches)
							)
							{
								$landingIdExec = $checkExecId($row);
								array_shift($matches);
								$this->sefVariables = $matches;
							}
						}
					}
				}
				else
				{
					$landingIdExec = $checkExecId($landing);
				}
			}
			else if (
				$landing['RULE'] && $landing['FOLDER'] != 'Y' &&
				preg_match('@^'. trim($landing['RULE']) . '$@i', $landingUrl, $matches)
			)
			{
				$landingIdExec = $checkExecId($landing);
				array_shift($matches);
				$this->sefVariables = $matches;
			}
			if ($site['LANDING_ID_INDEX'] == $landing['ID'])
			{
				$landingIdIndex = $landing['ID'];
			}
			if ($site['LANDING_ID_404'] == $landing['ID'])
			{
				$landingId404 = $landing['ID'];
			}
		}

		// disable direct access to include areas
		if ($landingIdExec && $this->arParams['DRAFT_MODE'] != 'Y')
		{
			if (TemplateRef::landingIsArea($landingIdExec))
			{
				$landingIdExec = false;
			}
		}

		// try load special landings if landing not found
		if (!$landingIdExec)
		{
			if (in_array($landingUrl, array('index.php', '')))
			{
				if ($landingIdIndex)
				{
					if ($landingIdIndex > 0)
					{
						$landingIdExec = $landingIdIndex;
					}
					else
					{
						$landingIdExec = $landingId404;
						$this->setHttpStatusOnce($this::ERROR_STATUS_NOT_FOUND);
					}
				}
				else
				{
					// if index page not set, gets first by asc
					$res = Landing::getList(array(
						'select' => array(
							'ID'
						),
						'filter' => array(
							'SITE_ID' => $site['ID'],
							'=ACTIVE' => 'Y',
							'!ID' => $landingId404,
							'CHECK_PERMISSIONS' => $this->arParams['CHECK_PERMISSIONS']
						),
						'order' => array(
							'ID' => 'asc'
						)
					));
					if ($row = $res->fetch())
					{
						$landingIdExec = $row['ID'];
					}
				}
			}
			else
			{
				$landingIdExec = $landingId404;
				if ($landingId404)
				{
					$this->clearHttpStatus();
				}
				$this->setHttpStatusOnce($this::ERROR_STATUS_NOT_FOUND);
			}
		}

		return $landingIdExec;
	}

	/**
	 * Get sitemap.xml content.
	 * @param int $siteId Site Id.
	 * @return string
	 */
	protected function getSitemap($siteId)
	{
		$ids = array();

		$res = Landing::getList(array(
			'select' => array(
				'ID', 'DATE_PUBLIC_UNIX'
			),
			'filter' => array(
				'SITE_ID' => $siteId,
				'=SITEMAP' => 'Y',
				'CHECK_PERMISSIONS' => $this->arParams['CHECK_PERMISSIONS']
			),
			'order' => array(
				'DATE_PUBLIC' => 'DESC'
			),
			'runtime' => array(
				new Entity\ExpressionField('DATE_PUBLIC_UNIX', 'UNIX_TIMESTAMP(DATE_PUBLIC)')
			)
		));
		while ($row = $res->fetch())
		{
			if ($row['DATE_PUBLIC_UNIX'])
			{
				$ids[$row['ID']] = $row['DATE_PUBLIC_UNIX'];
			}
		}

		if (empty($ids))
		{
			return '';
		}

		$urls = Landing::getPublicUrl(array_keys($ids));
		$sitemap = '<?xml version="1.0" encoding="' . SITE_CHARSET . '"?>';
		$sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
		foreach ($ids as $id => $date)
		{
			$sitemap .= '<url>';
			$sitemap .= '<loc>' . $urls[$id] . '</loc>';
			$sitemap .= '<lastmod>' . date('c', $date) . '</lastmod>';
			$sitemap .= '</url>';
		}
		$sitemap .= '</urlset>';

		return $sitemap;
	}

	/**
	 * Handler for localRedirect.
	 * @return void
	 */
	protected function onBeforeLocalRedirect()
	{
		$eventManager = EventManager::getInstance();
		$eventManager->addEventHandler('main', 'OnBeforeLocalRedirect',
			function(&$url, $skipCheck, &$bExternal)
			{
				/* @var Landing $landing*/
				$landing = $this->arResult['LANDING'];
				if (
					Manager::isB24() &&
					!Manager::isCloudDisable()
				)
				{
					$pubPathMask = '@^' . Manager::getPublicationPath('[\d]+') . '@i';
					$url = preg_replace($pubPathMask, '/', $url);
					if (substr($url, 0, 1) == '/')
					{
						$url = Site::getPublicUrl(
								$landing->getSiteId()
							) . $url;
						$bExternal = true;
					}
				}
				if (strpos($url, '#system') === false)
				{
					return;
				}
				foreach (Syspage::get($landing->getSiteId()) as $code => $page)
				{
					if (strpos($url, '#system_' . $code) !== false)
					{
						$landing = Landing::createInstance(
							$page['LANDING_ID'],
							['skip_blocks' => true]
						);
						if ($landing->exist())
						{
							$url = $landing->getPublicUrl(false, false);
							break;
						}
					}
				}
			}
		);
	}

	/**
	 * On search title.
	 * @return void
	 */
	protected function onSearchGetURL()
	{
		static $pageCatalog = null;

		if ($pageCatalog === null)
		{
			$syspages = Syspage::get($this->arResult['LANDING']->getSiteId());
			if (isset($syspages['catalog']))
			{
				$landing = Landing::createInstance(
					$syspages['catalog']['LANDING_ID'],
					['skip_blocks' => true]
				);
				if ($landing->exist())
				{
					$pageCatalog = $landing->getPublicUrl();
				}
			}
		}

		$eventManager = EventManager::getInstance();
		$eventManager->addEventHandler('search', 'onSearchGetURL',
			function($row) use($pageCatalog)
			{
				if (isset($row['URL']))
				{
					$urlType = 'detail';
					if (substr($row['ITEM_ID'], 0, 1) == 'S')
					{
						$row['ITEM_ID'] = substr($row['ITEM_ID'], 1);
						$urlType = 'section';
					}
					$row['URL'] = \Bitrix\Landing\Node\Component::getIblockURL(
						$row['ITEM_ID'],
						$urlType
					);
					$row['URL'] = str_replace(
						'#system_catalog',
						$pageCatalog,
						$row['URL']
					);
					return $row['URL'];
				}
			}
		);
	}

	/**
	 * Redefined basket item before save.
	 * @see Also called from landing/install/blocks/bitrix/store.cart/ajax.php:59
	 * @return void
	 */
	public function onSaleBasketItemBeforeSaved()
	{
		$eventManager = EventManager::getInstance();
		$eventManager->addEventHandler('sale', 'onSaleBasketItemBeforeSaved',
			function(Event $event)
			{
				$item = $event->getParameter('ENTITY');
				$productId = $item->getField('PRODUCT_ID');
				// by default without detail link
				$item->setField(
					'DETAIL_PAGE_URL',
					''
				);
				if (!Manager::isB24())
				{
					return;
				}
				// gets iblock id
				$res = \Bitrix\Iblock\ElementTable::getList(array(
					'select' => array(
						'IBLOCK_ID'
					),
					'filter' => array(
						'ID' => $productId
					)
				));
				if ($itemIblock = $res->fetch())
				{
					// gets prop with link to parent
					$res = \CIBlockElement::getProperty(
						$itemIblock['IBLOCK_ID'],
						$productId,
						array(),
						array(
							'CODE' => 'CML2_LINK'
						)
					);
					if ($itemProp = $res->fetch())
					{
						// gets parent's code
						$res = \Bitrix\Iblock\ElementTable::getList(array(
							'select' => array(
								'CODE'
							),
							'filter' => array(
								'ID' => $itemProp['VALUE']
							)
						));
						if ($itemParent = $res->fetch())
						{
							$item->setField(
								'DETAIL_PAGE_URL',
								'#system_catalogitem/' . $itemParent['CODE'] . '/'
							);
						}
					}
				}
			}
		);
	}

	/**
	 * Register callback for replace url in all letter.
	 * @param int $siteId Site id for get url.
	 * @return void
	 */
	public static function replaceUrlInLetter($siteId)
	{
		$eventManager = EventManager::getInstance();
		$eventManager->addEventHandler('main', 'OnBeforeMailSend',
			function(\Bitrix\Main\Event $event) use($siteId)
			{
				/* @var Landing $landing*/
				$params = $event->getParameters();
				$params = array_shift($params);
				$landing = Landing::createInstance(0);
				$sysPages = Syspage::get($siteId);

				// replace auth-link if personal section is exists
				if (isset($sysPages['personal']['LANDING_ID']))
				{
					$personalLandingId = $sysPages['personal']['LANDING_ID'];
					$params['BODY'] = preg_replace_callback(
						'@(https|http)://([^/]+)/auth/index\.php\?' .
						'change_password=yes&lang=([^&]+)&' .
						'USER_CHECKWORD=([a-z0-9]+)@',
						function ($matches) use($landing, $personalLandingId)
						{
							$url = $landing->getPublicUrl($personalLandingId);
							if (substr($url, 0, 1) == '/')
							{
								$url = $matches[1] . '://' . $matches[2] . $url;
							}
							$url .= '?' . http_build_query([
								'SECTION' => 'password_change',
								'USER_CHECKWORD' => $matches[4]
							]);
							return $url;
						},
						$params['BODY']
					);
				}

				return $params;
			}
		);
	}

	/**
	 * For replace some fields in sending letters.
	 * @return void
	 */
	protected function onBeforeEventSend()
	{
		/* @var Landing $landing*/
		$eventManager = EventManager::getInstance();
		$landing = $this->arResult['LANDING'];

		// replace only in $need types
		$eventManager->addEventHandler('main', 'OnBeforeEventSend',
			function($fields, &$eventMessage) use($landing)
			{
				$need = ['USER_PASS_REQUEST'];
				if (in_array($eventMessage['EVENT_NAME'], $need))
				{
					self::replaceUrlInLetter(
						$landing->getSiteId()
					);
				}
			}
		);

		// replace urls in user info letter
		$eventManager->addEventHandler('main', 'OnSendUserInfo',
			function(&$params) use($landing)
			{
				self::replaceUrlInLetter(
					$landing->getSiteId()
				);
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
				Manager::initAssets($this->arResult['LANDING']->getId());
			}
		);
	}

	/**
	 * Fill params urls with landing data.
	 * @param Landing $landing Landing instance.
	 * @return void
	 */
	protected function replaceParamsUrls(Landing $landing)
	{
		if ($this->arParams['SHOW_EDIT_PANEL'] != 'Y')
		{
			return;
		}

		$codes = [
			'PAGE_URL_LANDING_VIEW', 'PAGE_URL_SITES', 'PAGE_URL_SITE_SHOW'
		];

		foreach ($codes as $code)
		{
			if ($this->arParams[$code])
			{
				$this->arParams[$code] = str_replace(
					['#site_edit#', '#landing_edit#'],
					[$landing->getSiteId(), $landing->getId()],
					$this->arParams[$code]
				);
			}
		}
	}

	/**
	 * Sets canonical url.
	 * @param Landing $landing Landing instance.
	 * @return void
	 */
	public function setCanonical(Landing $landing)
	{
		// we need to know real domain name
		$domainName = '';
		$landingUrl = $landing->getPublicUrl();
		if (substr($landingUrl, 0, 1) == '/')
		{
			$domainName = Domain::getHostUrl();
		}
		else
		{
			$landingUrlParts = parse_url($landingUrl);
			if (
				isset($landingUrlParts['scheme']) &&
				isset($landingUrlParts['host'])
			)
			{
				$domainName = $landingUrlParts['scheme'] . '://';
				$domainName .= $landingUrlParts['host'];
			}
		}
		$canonical = $domainName . Manager::getApplication()->getCurDir();
		Manager::setPageView(
			'MetaOG',
			'<meta name="og:url" content="' . $canonical . '" />' . "\n" .
			'<link rel="canonical" href="' . $canonical . '"/>'
		);
	}

	/**
	 * Returns force content for robots.txt
	 * @return string
	 */
	protected function getForceRobots()
	{
		return 'User-agent: *' . PHP_EOL .
			   'Disallow: /pub/site/*' . PHP_EOL .
			   'Disallow: /preview/*';
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
			$this->zone = Manager::getZone();
			if (
				!isset($this->arParams['PATH']) ||
				!$this->arParams['PATH']
			)
			{
				$context = \Bitrix\Main\Context::getCurrent();
				$requestURL = $context->getRequest()->getRequestedPage();
				$realFilePath = $context->getServer()->get('REAL_FILE_PATH');
				if (!$realFilePath)
				{
					$realFilePath = $context->getServer()->get('SCRIPT_NAME');
				}
				$requestURL = str_replace('/index.php', '/', $requestURL);
				$realFilePath = str_replace('/' . basename($realFilePath), '/', $realFilePath);
				$this->arParams['PATH'] = substr($requestURL, strlen($realFilePath));
			}

			$this->checkParam('LID', 0);
			$this->checkParam('SITE_ID', 0);
			$this->checkParam('SITE_TYPE', '');
			$this->checkParam('HTTP_HOST', '');
			$this->checkParam('CHECK_PERMISSIONS', 'N');
			$this->checkParam('NOT_CHECK_DOMAIN', 'N');
			$this->checkParam('SHOW_EDIT_PANEL', 'N');
			$this->checkParam('DRAFT_MODE', 'N');
			$this->checkParam('PAGE_URL_LANDING_VIEW', '');
			$this->checkParam('PAGE_URL_SITES', '');
			$this->checkParam('PAGE_URL_SITE_SHOW', '');

			$this->arParams['TYPE'] = $this->arParams['SITE_TYPE'];

			\Bitrix\Landing\Site\Type::setScope(
				$this->arParams['SITE_TYPE']
			);

			if (
				($lid = $this->arParams['LID']) ||
				($lid = $this->detectPage())
			)
			{
				if ($this->isPreviewMode)
				{
					Hook::setEditMode();
				}
				// for cloud some magic for optimization
				if (Manager::isB24())
				{
					$asset = \Bitrix\Main\Page\Asset::getInstance();
					$asset->disableOptimizeCss();
					$asset->disableOptimizeJs();
				}
				// set external variables
				if (isset($this->sefVariables))
				{
					Landing::setVariables(array(
						'sef' => $this->sefVariables
					));
				}
				Landing::setDynamicParams(
					$this->dynamicFilterId,
					$this->dynamicElementId
				);
				// some other vars
				if ($this->isPreviewMode)
				{
					Landing::setPreviewMode(true);
				}
				self::$landingMain['LANDING_ID'] = $lid;
				$landing = Landing::createInstance($lid, [
					'check_permissions' => $this->arParams['CHECK_PERMISSIONS'] == 'Y',
					'disable_link_preview' => $this->arParams['DRAFT_MODE'] == 'Y'
				]);
				$this->arResult['LANDING'] = $landing;
				$this->arResult['DOMAIN'] = $this->getParentDomain();
				$this->arResult['COPY_LINK'] = $this->getCopyLinkPath();
				$this->arResult['ADV_CODE'] = $this->getAdvCode();
				$this->arResult['CAN_EDIT'] = 'N';
				// if landing found
				if ($landing->exist())
				{
					$landing->updateVersion();
					$this->arParams['TYPE'] = $landing::getSiteType();
					// if intranet, check rights for showing menu
					if (!$landing->getDomainId())
					{
						$operations = Rights::getOperationsForSite(
							$landing->getSiteId()
						);
						if (in_array(Rights::ACCESS_TYPES['edit'], $operations))
						{
							$this->arResult['CAN_EDIT'] = 'Y';
						}
					}
					$this->replaceParamsUrls($landing);
					// exec Hook Robots
					if ($this->isRobotsTxt)
					{
						$hooksSite = Hook::getForSite($landing->getSiteId());
						if (isset($hooksSite['ROBOTS']))
						{
							Manager::getApplication()->restartBuffer();
							if ($hooksSite['ROBOTS']->enabled())
							{
								$robotsContent = trim($hooksSite['ROBOTS']->exec());
							}
							else
							{
								$robotsContent = '';
							}
							// check sitemaps url
							$sitemap = Landing::getList(array(
								'select' => array(
									'ID'
								),
								'filter' => array(
									'SITE_ID' => $landing->getSiteId(),
									'=SITEMAP' => 'Y',
									'CHECK_PERMISSIONS' => $this->arParams['CHECK_PERMISSIONS']
								),
								'limit' => 1
							));
							if ($sitemap->fetch())
							{
								$robotsContent .= ($robotsContent ? PHP_EOL : '');
								$robotsContent .= 'Sitemap: ' .
											  		Site::getPublicUrl($landing->getSiteId()) .
											  		'/sitemap.xml';
							}
							if ($robotsContent)
							{
								$robotsContent .= PHP_EOL . PHP_EOL;
							}
							$robotsContent .= $this->getForceRobots();
							// out
							header('content-type: text/plain');
							echo $robotsContent;
							die();
						}
						else
						{
							$this->setHttpStatusOnce($this::ERROR_STATUS_NOT_FOUND);
							die();
						}
					}
					// build site map
					elseif ($this->isSitemapXml)
					{
						Manager::getApplication()->restartBuffer();
						header('content-type: text/xml');
						$sitemap = $this->getSitemap($landing->getSiteId());
						if ($sitemap)
						{
							echo $sitemap;
						}
						else
						{
							$this->setHttpStatusOnce($this::ERROR_STATUS_NOT_FOUND);
						}
						die();
					}
				}
				// else errors
				$this->setErrors(
					$landing->getError()->getErrors()
				);

				if ($landing->getError()->isEmpty())
				{
					// events
					$this->onBeforeLocalRedirect();
					$this->onSearchGetURL();
					$this->onSaleBasketItemBeforeSaved();
					$this->onBeforeEventSend();
					$this->onEpilog();
					// change view for public mode
					Manager::setPageView(
						'MainClass',
						'landing-public-mode'
					);
					// call tracker
					if (
						$this->arParams['DRAFT_MODE'] != 'Y' &&
						\Bitrix\Main\Loader::includeModule('crm')
					)
					{
						Manager::setPageView(
							'FooterJS',
							CallTracker::instance()->getEmbeddedScript()
						);
					}
					// views
					\Bitrix\Landing\Landing\View::inc($lid);
				}
			}
			else
			{
				$this->setHttpStatusOnce($this::ERROR_STATUS_NOT_FOUND);
				$this->addError(
					'LANDING_CMP_SITE_NOT_FOUND',
					$this->getMessageType('LANDING_CMP_SITE_NOT_FOUND')
				);
			}
		}

		parent::executeComponent();
	}
}
