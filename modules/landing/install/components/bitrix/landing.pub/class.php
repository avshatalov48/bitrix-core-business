<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Entity;
use \Bitrix\Landing\Hook;
use \Bitrix\Landing\Manager;
use \Bitrix\Landing\Landing;
use \Bitrix\Landing\Site;
use \Bitrix\Landing\Syspage;
use \Bitrix\Landing\TemplateRef;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\EventManager;
use \Bitrix\Main\Config\Option;
use \Bitrix\Main\Application;

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
	 * Current zone.
	 * @var string
	 */
	protected $zone = '';

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
		$domains = array(
			'ru' => 'bitrix24.ru',
			'ua' => 'bitrix24.ua',
			'by' => 'bitrix24.by',
			'kz' => 'bitrix24.kz',
			'pl' => 'bitrix24.pl',
			'en' => 'bitrix24.com',
			'de' => 'bitrix24.de',
			'es' => 'bitrix24.es',
			'la' => 'bitrix24.es',
			'br' => 'bitrix24.com.br',
			'fr' => 'bitrix24.fr',
			'cn' => 'bitrix24.cn',
			'in' => 'bitrix24.in',
			'eu' => 'bitrix24.eu',
			'tr' => 'bitrix24.com.tr'
		);

		if (isset($domains[$this->zone]))
		{
			return $domains[$this->zone];
		}
		else
		{
			return $domains['en'];
		}
	}

	/**
	 * Gets path for copyright link.
	 * @return string
	 */
	protected function getCopyLinkPath()
	{
		$paths = array(
			'ru' => '/features/sites.php',
			'ua' => '/features/sites.php',
			'by' => '/features/sites.php',
			'kz' => '/features/sites.php',
			'en' => '/features/sites.php',
			'de' => '/features/sites.php',
			'es' => '/features/sites.php',
			'la' => '/features/sites.php',
			'br' => '/features/sites.php',
			'in' => '/features/sites.php',
			'eu' => '/features/sites.php'
		);
		if (isset($paths[$this->zone]))
		{
			return $paths[$this->zone];
		}
		else
		{
			return '/';
		}
	}

	/**
	 * Get adv campaign code.
	 * @return string
	 */
	protected function getAdvCode()
	{
		$codes = array(
			'ru' => 'utm_source=client_b24_site&utm_medium=referral&utm_campaign=b24_site',
			'ua' => 'utm_source=client_b24_site&utm_medium=referral&utm_campaign=b24ua_site',
			'pl' => 'utm_source=client_b24_site&utm_medium=referral&utm_campaign=b24pl_site',
			'en' => 'utm_medium=referral&utm_source=bitrix24.com&utm_campaign=SitesCom',
			'de' => 'utm_medium=referral&utm_source=bitrix24.de&utm_campaign=SitesDe',
			'es' => 'utm_medium=referral&utm_source=bitrix24.es&utm_campaign=SitesEs',
			'br' => 'utm_medium=referral&utm_source=bitrix24.com.br&utm_campaign=SitesBR',
			'fr' => 'utm_medium=referral&utm_source=bitrix24.fr&utm_campaign=SitesFr'
		);

		if (isset($codes[$this->zone]))
		{
			$return = $codes[$this->zone];
		}
		else
		{
			$return = $codes['en'];
		}

		$partnerId = Option::get('bitrix24', 'partner_id', 0);
		if ($partnerId)
		{
			$return .= '&p=' . $partnerId;
		}

		return \htmlspecialcharsbx($return);
	}

	/**
	 * Detect landing by path.
	 * @return int|false Detected landing id or false.
	 */
	protected function detectPage()
	{
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
				'filter' => array(
					'=SMN_SITE_ID' => SITE_ID,
					'=TYPE' => 'SMN'
				),
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
		if (
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
					$path = \CFile::getPath(
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
		if (
			$serverHost &&
			!$previewTemplate &&
			(!defined('LANDING_DISABLE_CLOUD') || LANDING_DISABLE_CLOUD !== true)
		)
		{
			if (strpos($serverHost, ':') !== false)
			{
				list($serverHost, ) = explode(':', $serverHost);
			}
			$filter['=DOMAIN.DOMAIN'] = $serverHost;
		}
		$res = Site::getList(array(
			'select' => array(
				'ID', 'ACTIVE', 'DELETED',
				'LANDING_ID_404', 'LANDING_ID_503',
				'LANDING_ID_INDEX'
			),
			'filter' => $filter
		));
		if (!($site = $res->fetch()))
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
		$res = Landing::getList(array(
			'select' => array(
				'ID', 'CODE', 'RULE',
				'FOLDER', 'ACTIVE', 'DELETED'
			),
			'filter' => array(
				'SITE_ID' => $site['ID'],
				'FOLDER_ID' => false,
				'=DELETED' => ['Y', 'N'],
				array(
					'LOGIC' => 'OR',
					'=CODE' => $landingUrl,
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
			if (strtolower($landing['CODE']) == strtolower($landingUrl))
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
		if ($landingIdExec)
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
							'!ID' => $landingId404
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
				'=SITEMAP' => 'Y'
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
				/* @var \Bitrix\Landing\Landing $landing*/
				$landing = $this->arResult['LANDING'];
				if (
					Manager::isB24() &&
					(
						!defined('LANDING_DISABLE_CLOUD') ||
						LANDING_DISABLE_CLOUD !== true
					)
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
						$landing = Landing::createInstance($page['LANDING_ID']);
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
					$syspages['catalog']['LANDING_ID']
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
			function(\Bitrix\Main\Event $event)
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
	 * For replace some fields in sending letters.
	 * @return void
	 */
	protected function onBeforeEventSend()
	{
		$eventManager = EventManager::getInstance();

		$addEventHandler = function() use ($eventManager)
		{
			$eventManager->addEventHandler('main', 'OnBeforeMailSend',
				function(\Bitrix\Main\Event $event)
				{
					/* @var \Bitrix\Landing\Landing $landing*/
					$params = $event->getParameters();
					$params = array_shift($params);
					$landing = $this->arResult['LANDING'];
					$sysPages = Syspage::get($landing->getSiteId());

					// replace auth-link if personal section is exists
					if (isset($sysPages['personal']['LANDING_ID']))
					{
						$personalLandingId = $sysPages['personal']['LANDING_ID'];
						$params['BODY'] = preg_replace_callback(
							'@(https|http)://([^/]+)/auth/index\.php\?' .
							'change_password=yes&lang=([^&]+)&' .
							'USER_CHECKWORD=([^&]+)&USER_LOGIN=([^\s"]+)@',
							function ($matches) use($landing, $personalLandingId)
							{
								$url = $landing->getPublicUrl($personalLandingId);
								if (substr($url, 0, 1) == '/')
								{
									$url = $matches[1] . '://' . $matches[2] . $url;
								}
								$url .= '?' . http_build_query([
									'SECTION' => 'password_change',
									'USER_CHECKWORD' => $matches[4],
									'USER_LOGIN' => $matches[5]
								]);
								return $url;
							},
							$params['BODY']
						);
					}

					return $params;
				}
			);
		};

		$eventManager->addEventHandler('main', 'OnBeforeEventSend',
			function($fields, &$eventMessage) use($addEventHandler)
			{
				$need = ['USER_PASS_REQUEST'];
				if (in_array($eventMessage['EVENT_NAME'], $need))
				{
					$addEventHandler();
				}
			}
		);

		$eventManager->addEventHandler('main', 'OnSendUserInfo',
			function(&$params) use($addEventHandler)
			{
				$addEventHandler();
			}
		);
	}

	/**
	 * Base executable method.
	 * @return void
	 */
	public function executeComponent()
	{
		$init = $this->init();
		$this->zone = Manager::getZone();

		if ($init)
		{
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
			$this->checkParam('HTTP_HOST', '');

			if (
				($lid = $this->arParams['LID']) ||
				($lid = $this->detectPage())
			)
			{
				if (Manager::isB24())
				{
					$asset = \Bitrix\Main\Page\Asset::getInstance();
					if (
						method_exists($asset, 'disableOptimizeCss') &&
						method_exists($asset, 'disableOptimizeJs')
					)
					{
						$asset->disableOptimizeCss();
						$asset->disableOptimizeJs();
					}
				}

				if (isset($this->sefVariables))
				{
					Landing::setVariables(array(
						'sef' => $this->sefVariables
					));
				}

				if ($this->isPreviewMode)
				{
					Landing::setPreviewMode(true);
				}

				self::$landingMain['LANDING_ID'] = $lid;
				$landing = Landing::createInstance($lid);
				$this->arResult['LANDING'] = $landing;
				$this->arResult['DOMAIN'] = $this->getParentDomain();
				$this->arResult['COPY_LINK'] = $this->getCopyLinkPath();
				$this->arResult['ADV_CODE'] = $this->getAdvCode();

				if ($landing->exist())
				{
					// exec Hook Robots
					if ($this->isRobotsTxt)
					{
						$hooksSite = Hook::getForSite($landing->getSiteId());
						if (isset($hooksSite['ROBOTS']))
						{
							Manager::getApplication()->restartBuffer();
							$robotsContent = trim($hooksSite['ROBOTS']->exec());
							// check sitemaps url
							$sitemap = Landing::getList(array(
								'select' => array(
									'ID'
								),
								'filter' => array(
									'SITE_ID' => $landing->getSiteId(),
									'=SITEMAP' => 'Y'
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
							// out
							if ($robotsContent)
							{
								header('content-type: text/plain');
								echo $robotsContent;
							}
							else
							{
								$this->setHttpStatusOnce($this::ERROR_STATUS_NOT_FOUND);
							}
							die();
						}
					}
					// build sitemap
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

				$this->setErrors(
					$landing->getError()->getErrors()
				);

				// events
				$this->onBeforeLocalRedirect();
				$this->onSearchGetURL();
				$this->onSaleBasketItemBeforeSaved();
				$this->onBeforeEventSend();

				// change view
				Manager::setPageView(
					'MainClass',
					'landing-public-mode'
				);
				if (
					\Bitrix\Main\Loader::includeModule('crm') &&
					method_exists(
						'\Bitrix\Crm\UI\Webpack\CallTracker',
						'getEmbeddedScript'
					)
				)
				{
					Manager::setPageView(
						'FooterJS',
						\Bitrix\Crm\UI\Webpack\CallTracker::instance()->getEmbeddedScript()
					);
				}

				// set og url
				Manager::setPageView(
					'MetaOG',
					'<meta name="og:url" content="' . $landing->getPublicUrl() . '" />' . "\n" .
					'<link rel="canonical" href="' . $landing->getPublicUrl() . '"/>'
				);
			}
			else
			{
				$this->setHttpStatusOnce($this::ERROR_STATUS_NOT_FOUND);
				$this->addError(
					'LANDING_CMP_SITE_NOT_FOUND',
					Loc::getMessage('LANDING_CMP_SITE_NOT_FOUND')
				);
			}
		}

		parent::executeComponent();
	}
}