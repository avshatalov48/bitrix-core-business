<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Landing\Block\Cache;
use \Bitrix\Landing\Hook;
use \Bitrix\Landing\Manager;
use \Bitrix\Landing\Landing;
use \Bitrix\Landing\Folder;
use \Bitrix\Landing\Domain;
use \Bitrix\Landing\Site;
use \Bitrix\Landing\Syspage;
use \Bitrix\Landing\TemplateRef;
use \Bitrix\Landing\Rights;
use Bitrix\Landing\Update\Block\DuplicateImages;
use \Bitrix\Main\Entity;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\EventManager;
use \Bitrix\Main\Config\Option;
use \Bitrix\Main\Application;
use \Bitrix\Main\Event;
use \Bitrix\Crm\UI\Webpack\CallTracker;
use \Bitrix\Crm\MessageSender\NotificationsPromoManager;

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
	 * Current http status.
	 * @var string
	 */
	protected $currentHttpStatus = self::ERROR_STATUS_OK;

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
			$domainParts = explode('.', $domain);
			$domainPart = array_pop($domainParts);
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
	 * @param bool $addWWW Add www-part.
	 * @return string
	 */
	public function getRefLink(string $type, bool $addAdvCode = true, bool $addWWW = false): string
	{
		static $partnerId = null;

		if ($partnerId === null)
		{
			$partnerId = (int)Option::get('bitrix24', 'partner_id', 0);
		}

		$link = 'https://'. ($addWWW ? 'www.' : '') . $this->getParentDomain();
		$link .= $this->getCopyLinkPath($type);

		if ($addAdvCode)
		{
			$link .= (mb_strpos($link, '?') === false) ? '?' : '&amp;';
			$link .= $this->getAdvCode($type);
		}
		else if ($partnerId)
		{
			$link .= (mb_strpos($link, '?') === false) ? '?' : '&amp;';
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
		if (($this->arParams['NOT_SEND_HTTP_STATUS'] ?? 'N') === 'Y')
		{
			return;
		}

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
			$this->currentHttpStatus = $code;
			\CHTTP::setStatus($code);
		}
	}

	/**
	 * Clear status that http status was send.
	 * @return void
	 */
	protected function clearHttpStatus()
	{
		$this->currentHttpStatus = $this::ERROR_STATUS_OK;
		$this->httpStatusSend = false;
	}

	/**
	 * Returns current http status.
	 * @return string
	 */
	public function getCurrentHttpStatus(): string
	{
		return $this->currentHttpStatus;
	}

	/**
	 * Returns filter part if page path is in folder.
	 * @param string $fullPath Full page path.
	 * @param int $siteId Site id.
	 * @return array
	 */
	protected function getFolderFilter(string $fullPath, int $siteId): array
	{
		$filter = [];
		$fullPath = mb_strtolower(trim($fullPath, '/'));
		if (!$fullPath)
		{
			return $filter;
		}
		$pathParts = explode('/', $fullPath);

		// get all folders from the site
		$folders = [];
		$res = Folder::getList([
			'select' => [
				'ID', 'CODE', 'PARENT_ID', 'INDEX_ID'
			],
			'filter' => [
				'=DELETED' => 'N',
				'=ACTIVE' => $this->isPreviewMode ? ['Y', 'N'] : 'Y',
				'SITE_ID' => $siteId
			]
		]);
		while ($row = $res->fetch())
		{
			$row['PARENT_ID'] = $row['PARENT_ID'] ?: null;
			$folders[] = $row;
		}

		// walk throw $fullPath and detect eventual folder
		$parentId = null;
		do
		{
			$found = false;
			$pathPart = $pathParts[0];
			$indexId = null;
			foreach ($folders as $item)
			{
				if (
					$item['PARENT_ID'] === $parentId &&
					$pathPart === mb_strtolower($item['CODE'])
				)
				{
					$found = true;
					$parentId = $item['ID'];
					$indexId = $item['INDEX_ID'];
					break;
				}
			}
			if (!$found && $pathParts)
			{
				break;
			}
			array_shift($pathParts);
		} while($pathParts);

		$filter['=CODE'] = null;
		if ($parentId)
		{
			$filter['FOLDER_ID'] = $parentId;
			if (count($pathParts) === 1)
			{
				$filter['=CODE'] = $pathParts[0];
 			}
			else if ($pathParts)
			{
				return [];
			}
		}

		if (!$filter['=CODE'])
		{
			unset($filter['=CODE']);
			if ($indexId)
			{
				$filter['ID'] = $indexId;
			}
		}

		return $filter;
	}

	/**
	 * Detect landing by path.
	 * @return int|false Detected landing id or false.
	 */
	public function detectPage()
	{
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
			mb_strpos($realFilePath, Manager::getPublicationPath()) === 0 &&
			mb_strpos($realFilePath, Manager::getPublicationPathConst()) !== 0
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
		if (preg_match('#^([-_\w\/]+)\_([\d]+)\_([\d]+)$#', $landingSubUrl ?: $landingUrl, $matches))
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

			if (mb_substr($path, 0, 1) == '/')
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
		if ($this->arParams['SITE_ID'])
		{
			$filter = array(
				'ID' => $this->arParams['SITE_ID'],
				'=DELETED' => ['Y', 'N']
			);
		}
		else if (preg_match('#^([\d]+)$#', $siteUrl, $matches))
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
			$this->arParams['NOT_CHECK_DOMAIN'] != 'Y' &&
			!Manager::isCloudDisable()
		)
		{
			if (mb_strpos($serverHost, ':') !== false)
			{
				[$serverHost, ] = explode(':', $serverHost);
			}
			// set www alias
			if (mb_substr($serverHost, 0, 4) == 'www.')
			{
				$filter['=DOMAIN.ACTIVE'] = 'Y';
				$filter['=DOMAIN.DOMAIN'] = [
					$serverHost,
					mb_substr($serverHost, 4)
				];
			}
			else
			{
				$filter['=DOMAIN.ACTIVE'] = 'Y';
				$filter['=DOMAIN.DOMAIN'] = [
					$serverHost,
					'www.' . $serverHost
				];
			}
		}
		$filter['CHECK_PERMISSIONS'] = $this->arParams['CHECK_PERMISSIONS'];
		$res = Site::getList(array(
			'select' => array(
				'ID', 'ACTIVE', 'DELETED', 'SPECIAL',
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

		if ($site['SPECIAL'] !== 'Y')
		{
			$this->forceUpdateNewFolders($site['ID']);
		}

		// unactive site
		if (
			(
				!$this->isPreviewMode &&
				$site['ACTIVE'] == 'N'
			)
			||
			(
				Landing::checkDeleted() &&
				$site['DELETED'] == 'Y'
			)
		)
		{
			$this->setHttpStatusOnce($this::ERROR_STATUS_NOT_FOUND);
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
				(
					Landing::checkDeleted() &&
					$row['DELETED'] == 'Y'
				)
			)
			{
				return false;
			}

			return $row['ID'];
		};

		$regexVars = [];
		$regexLandingId = false;

		// detect regex urls
		$sefIds = [];
		$res = Landing::getList([
			'select' => [
				'ID', 'ACTIVE', 'DELETED', 'RULE'
			],
			'filter' => [
				'!=RULE' => false,
				'SITE_ID' => $site['ID'],
				'CHECK_PERMISSIONS' => $this->arParams['CHECK_PERMISSIONS']
			],
			'order' => [
				'RULE' => 'desc'
			]
		]);
		while ($row = $res->fetch())
		{
			$sefIds[$row['ID']] = $row;
		}
		if ($sefIds)
		{
			$isB24 = Manager::isB24();
			$publicationPath = Manager::getPublicationPath($siteId);
			$curFulPath = rtrim(Application::getInstance()->getContext()->getRequest()->getRequestedPageDirectory(), '/') . '/';
			$rewriteUrls = Landing::createInstance(0)->getPublicUrl(array_keys($sefIds), false, false, $full);
			foreach ($sefIds as $landingId => $landingRow)
			{
				$url = $rewriteUrls[$landingId] . trim($landingRow['RULE'], '/') . '/';
				if (strpos($url, $publicationPath) !== 0 && $isB24)
				{
					$url = $publicationPath . ltrim($url, '/');
				}
				if (preg_match('@^'. $url . '$@i', $curFulPath, $matches))
				{
					array_shift($matches);
					$regexVars = $matches;
					$regexLandingId = $checkExecId($landingRow);
					break;
				}
			}
		}

		// try detect folder(s)
		$folderFilter = $this->getFolderFilter($landingUrl . '/' . $landingSubUrl, $site['ID']);
		if ($folderFilter)
		{
			$folderFilter['==AREAS.ID'] = null;
			$folderFilter['SITE_ID'] = $site['ID'];
			$folderFilter['=ACTIVE'] = $this->isPreviewMode ? ['Y', 'N'] : 'Y';
			$folderFilter['CHECK_PERMISSIONS'] = $this->arParams['CHECK_PERMISSIONS'];
			$res = Landing::getList(array(
				'select' => [
					'ID', 'ACTIVE', 'DELETED'
				],
				'filter' => $folderFilter,
				'order' => [
					'ID' => 'asc'
				],
				'limit' => 1
			));
			if ($row = $res->fetch())
			{
				$landingIdExec = $checkExecId($row);
				if ($landingIdExec)
				{
					return $landingIdExec;
				}
			}
		}

		// detect landing
		$codeFilter = ($landingCodeOriginal === null)
					? $landingUrl
					: [$landingUrl, $landingCodeOriginal];
		$res = Landing::getList(array(
			'select' => array(
				'ID', 'CODE', 'ACTIVE', 'DELETED'
			),
			'filter' => array(
				'SITE_ID' => $site['ID'],
				'=DELETED' => ['Y', 'N'],
				'=ACTIVE' => ['Y', 'N'],
				'=RULE' => null,
				'FOLDER_ID' => null,
				'CHECK_PERMISSIONS' => $this->arParams['CHECK_PERMISSIONS'],
				array(
					'LOGIC' => 'OR',
					'=CODE' => $codeFilter,
					$site['LANDING_ID_404'] ? ['ID' => $site['LANDING_ID_404']] : [],
					$site['LANDING_ID_INDEX'] ? ['ID' => $site['LANDING_ID_INDEX']] : []
				)
			),
			'order' => array(
				'DATE_MODIFY' => 'asc'
			)
		));
		$codeFilter = (array)$codeFilter;
		$codeFilter = array_map('mb_strtolower', $codeFilter);
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
			if (!$landingIdExec && !$landingSubUrl && in_array(mb_strtolower($landing['CODE']), $codeFilter))
			{
				$landingIdExec = $checkExecId($landing);
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

		// if we detected page by regex early
		if (!$landingIdExec && $regexLandingId)
		{
			$this->sefVariables = $regexVars;
			$landingIdExec = $regexLandingId;
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
					if ($site['LANDING_ID_INDEX'])
					{
						$landingIdExec = $site['LANDING_ID_INDEX'];
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
						else
						{
							$this->setHttpStatusOnce($this::ERROR_STATUS_NOT_FOUND);
						}
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

		$urls = Landing::createInstance(0)->getPublicUrl(array_keys($ids));
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
					if (mb_substr($url, 0, 1) == '/')
					{
						$url = Site::getPublicUrl(
								$landing->getSiteId()
							) . $url;
						$bExternal = true;
					}
				}
				if (mb_strpos($url, '#system') === false)
				{
					return;
				}
				foreach (Syspage::get($landing->getSiteId()) as $code => $page)
				{
					if (mb_strpos($url, '#system_'.$code) !== false)
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
					if (mb_substr($row['ITEM_ID'], 0, 1) == 'S')
					{
						$row['ITEM_ID'] = mb_substr($row['ITEM_ID'], 1);
						$urlType = 'section';
					}
					$row['URL'] = \Bitrix\Landing\PublicAction\Utils::getIblockURL(
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
	 * Event handlers for rewrite syspages.
	 * @return void
	 */
	protected function onGetSysPage(): void
	{
		/** @var Bitrix\Landing\Landing $landing */
		$landing = $this->arResult['LANDING'];

		if ($landing->getMeta()['SITE_TPL_CODE'] === 'store-chats-dark')
		{
			$eventManager = EventManager::getInstance();
			$eventManager->addEventHandler('landing', 'onLandingSyspageRetrieve',
				function($event) use($landing)
				{
					$siteId = $landing->getSiteId();
					$types = $event->getParameter('types');

					if ($types[$siteId]['order'] ?? null)
					{
						$res = Landing::getList([
							'select' => [
								'ID'
							],
							'filter' => [
								'=TPL_CODE' => 'store-chats-dark/catalog_order',
								'SITE_ID' => $landing->getSiteId()
							],
							'limit' => 1
						]);
						if ($row = $res->fetch())
						{
							$types[$siteId]['order']['LANDING_ID'] = $row['ID'];
						}
					}

					return $types;
				}
			);
		}
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
						'@(https|http)://([^/]+)/.*?/index\.php\?' .
						'change_password=yes&lang=([^&]+)&' .
						'USER_CHECKWORD=([a-z0-9]+)@',
						function ($matches) use($landing, $personalLandingId)
						{
							$url = $landing->getPublicUrl($personalLandingId);
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
	 * Handler on epilog finish.
	 * @return void
	 */
	protected function onEpilog(): void
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
	 * Handler on view block.
	 * @return void
	 */
	protected function onBlockPublicView(): void
	{
		$query = $this->request('q');
		if ($query)
		{
			Cache::disableCache();
		}
		$eventManager = EventManager::getInstance();
		$eventManager->addEventHandler('landing', 'onBlockPublicView',
			function(Event $event) use($query)
			{
				$block = $event->getParameter('block');
				$outputContent = $event->getParameter('outputContent');

				// UPDATE block
				$blockUpdater = new DuplicateImages(null, [
					'block' => $block,
					'content' => $outputContent,
				]);
				$outputContent = $blockUpdater->update(false);

				// SEARCH replaces
				$isSearch =
					$query
					&& $this->arParams['TYPE'] !== 'KNOWLEDGE'
					&& $this->arParams['TYPE'] !== 'GROUP';
				if ($isSearch)
				{
					$isUtf = defined('BX_UTF') && BX_UTF === true;
					if (strpos($outputContent, '<?') !== false)
					{
						return $outputContent;
					}
					if (!$isUtf)
					{
						[$outputContent, $query] = \Bitrix\Main\Text\Encoding::convertEncoding(
							[$outputContent, $query], SITE_CHARSET, 'UTF-8'
						);
					}
					$phrases = explode(' ', $query);
					\trimArr($phrases, true);
					// try find search phrases in real content (between tags)
					$found = preg_match_all(
						'#>[^<]*(' . implode('|', $phrases) . ')[^<]*<#isu',
						$outputContent,
						$matches
					);
					if ($found)
					{
						foreach ($matches[0] as $outer)
						{
							// highlight found phrases
							$outerNew = preg_replace(
								'#([^\s;>]*(' . implode('|', $phrases) . ')[^\s<&!.,]*)#isu',
								'<span class="landing-highlight">$1</span>',
								$outer
							);
							$outputContent = str_replace($outer, $outerNew, $outputContent);
						}
					}
					if (!$isUtf)
					{
						$outputContent = \Bitrix\Main\Text\Encoding::convertEncoding(
							$outputContent, 'UTF-8', SITE_CHARSET
						);
					}
				}

				return $outputContent;
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
		if (mb_substr($landingUrl, 0, 1) == '/')
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
			'<meta property="og:url" content="' . $canonical . '" />' . "\n" .
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
	 * Sends request for getting access to current site.
	 * @return array
	 */
	protected function actionAskAccess(): array
	{
		$this->clearHttpStatus();
		$this->setHttpStatusOnce($this::ERROR_STATUS_OK);
		if (
			Manager::isB24() &&
			isset($this->arResult['REAL_LANDING']) &&
			($userId = $this->request('userId')) &&
			\Bitrix\Main\Loader::includeModule('im')
		)
		{
			$admins = $this->getAdmins();
			if (isset($admins[$userId]))
			{
				$fromUserId = Manager::getUserId();
				$name = $this->arResult['REAL_LANDING']->getTitle();
				$url = $this->arParams['PAGE_URL_ROLES']
						? $this->arParams['PAGE_URL_ROLES']
						: $this->arParams['PAGE_URL_SITES'];
				\CIMNotify::add([
					'TO_USER_ID' => $userId,
					'FROM_USER_ID' => $fromUserId,
					'NOTIFY_TYPE' => IM_NOTIFY_FROM,
					'NOTIFY_MODULE' => 'landing',
					'NOTIFY_TAG' => 'LANDING|NOTIFY_ADMIN|' . $userId . '|' . $fromUserId . '|V3',
					'NOTIFY_MESSAGE' => $this->getMessageType('LANDING_CMP_ASK_ACCESS_KNOWLEDGE', [
						'#LINK1#' => '<a href="' . $url . '">',
						'#LINK2#' => '</a>',
						'#NAME#' => $name
					])
				]);
			}
		}
		return [
			'status' => 'success'
		];
	}

	/**
	 * Checks if this site is binding to socialnet opened group.
	 * @param int $siteId Site id.
	 * @return bool
	 */
	protected function isOpenedGroupSite(int $siteId): bool
	{
		return \Bitrix\Landing\Site\Scope\Group::getGroupIdBySiteId($siteId, true) > 0;
	}

	/**
	 * Sends push on landing first view.
	 * @param int $landingId Landing id.
	 * @return void
	 */
	protected function sendPageViewPush(int $landingId): void
	{
		if (\Bitrix\Main\Loader::includeModule('pull'))
		{
			\CPullWatch::addToStack(
				'LANDING_ENTITY_LANDING',
				[
					'module_id' => 'landing',
					'command' => 'onLandingFirstView',
					'params' => [
						'ladingId' => $landingId
					]
				]
			);
		}
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
					$realFilePath = $_SERVER['REAL_FILE_PATH'] ?? null;
				}
				if (!$realFilePath)
				{
					$realFilePath = $context->getServer()->get('SCRIPT_NAME');
				}
				$requestURL = str_replace('/index.php', '/', $requestURL);
				$realFilePath = str_replace('/' . basename($realFilePath), '/', $realFilePath);
				$this->arParams['PATH'] = mb_substr($requestURL, mb_strlen($realFilePath));
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
			$this->checkParam('PAGE_URL_ROLES', '');

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
				$landing = Landing::createInstance($lid, [
					'check_permissions' => $this->arParams['CHECK_PERMISSIONS'] == 'Y',
					'disable_link_preview' => $this->arParams['DRAFT_MODE'] == 'Y'
				]);
				self::$landingMain['LANDING_ID'] = $lid;
				self::$landingMain['LANDING_INSTANCE'] = $landing;
				$this->arResult['LANDING'] = $landing;
				$this->arResult['SPECIAL_TYPE'] = $this->getSpecialTypeSiteByLanding($landing);
				$this->arResult['DOMAIN'] = $this->getParentDomain();
				$this->arResult['COPY_LINK'] = $this->getCopyLinkPath();
				$this->arResult['ADV_CODE'] = $this->getAdvCode();
				$this->arResult['SEARCH_RESULT_QUERY'] = $this->request('q');
				$this->arResult['CAN_EDIT'] = 'N';
				// if landing found
				if ($landing->exist())
				{
					\Bitrix\Landing\Site\Version::update($landing->getSiteId(), $landing->getMeta()['SITE_VERSION']);

					if ($this->arResult['SPECIAL_TYPE'] === \Bitrix\Landing\Site\Type::PSEUDO_SCOPE_CODE_FORMS)
					{
						Landing::setEditMode(true);
						$this->checkFormInLanding($landing);
						Landing::setEditMode(false);
					}

					$this->arParams['TYPE'] = $landing::getSiteType();
					if ($this->arParams['TYPE'] == 'STORE')
					{
						header('X-Bitrix24-Page: dynamic');
					}
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
							if (mb_strpos(strtolower($robotsContent), 'user-agent:') !== false)
							{
								$robotsContent = preg_replace(
									'/user-agent:\s+\*/i',
									$this->getForceRobots(),
									$robotsContent
								);
							}
							else
							{
								$robotsContent .= $this->getForceRobots();
							}
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
					$this->onGetSysPage();
					$this->onBeforeLocalRedirect();
					$this->onSearchGetURL();
					$this->onSaleBasketItemBeforeSaved();
					$this->onBeforeEventSend();
					$this->onEpilog();
					$this->onBlockPublicView();
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
					if ($this->request('promo') == 'Y')// only for promo hit
					{
						$this->sendPageViewPush($landing->getId());
						if (\Bitrix\Main\Loader::includeModule('crm'))
						{
							NotificationsPromoManager::enablePromoSession($landing->getId());
						}

					}
					\Bitrix\Landing\Landing\View::inc($lid);
				}
			}
			else if ($this->getCurrentHttpStatus() === $this::ERROR_STATUS_FORBIDDEN)
			{
				$this->addError(
					'SITE_NOT_ALLOWED',
					$this->getMessageType('LANDING_CMP_SITE_NOT_ALLOWED')
				);
			}
			else
			{
				// check if site is actual exists, but not allowed for current user
				if ($this->arParams['CHECK_PERMISSIONS'] == 'Y')
				{
					$this->arParams['CHECK_PERMISSIONS'] = 'N';
					if ($realLandingId = $this->detectPage())
					{
						$this->arResult['ADMINS'] = $this->getAdmins();
						$this->arResult['REAL_LANDING'] = Landing::createInstance($realLandingId, [
							'check_permissions' => false,
							'blocks_limit' => 0
						]);
						if ($this->isOpenedGroupSite($this->arResult['REAL_LANDING']->getSiteId()))
						{
							$this->executeComponent();
							return;
						}
						if (!\Bitrix\Main\ModuleManager::isModuleInstalled('bitrix24'))
						{
							$this->setHttpStatusOnce($this::ERROR_STATUS_FORBIDDEN);
						}
						$this->addError(
							'SITE_NOT_ALLOWED',
							$this->getMessageType('LANDING_CMP_SITE_NOT_ALLOWED')
						);
						$this->arParams['CHECK_PERMISSIONS'] = 'Y';
						parent::executeComponent();
						return;
					}
					$this->arParams['CHECK_PERMISSIONS'] = 'Y';
				}
				// try force reload
				if ($this->request('forceLandingId'))
				{
					$landingForce = Landing::createInstance($this->request('forceLandingId'));
					\localRedirect($landingForce->getPublicUrl(false, false) . '?IFRAME=Y');
				}
				// site is actual not exists
				$this->setHttpStatusOnce($this::ERROR_STATUS_NOT_FOUND);
				$this->addError(
					'SITE_NOT_FOUND',
					$this->getMessageType('LANDING_CMP_SITE_NOT_FOUND2')
				);
			}
		}

		parent::executeComponent();
	}
}
