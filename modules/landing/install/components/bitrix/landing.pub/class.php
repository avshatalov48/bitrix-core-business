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
use \Bitrix\Main\Localization\Loc;

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
			return $codes[$this->zone];
		}
		else
		{
			return $codes['en'];
		}
	}

	/**
	 * Detect landing by path.
	 * @return int|false Detected landing id or false.
	 */
	protected function detectPage()
	{
		$preview = $this->request('preview') == 'Y';
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
		}
		// in smn detect site dir auto
		else
		{
			$siteUrl = '';
			$res = Site::getList(array(
				'select' => array(
					'CODE'
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
			}
		}
		$landingUrl = array_shift($requestedPageParts);
		$landingSubUrl = $requestedPageParts ? implode('/', $requestedPageParts) : '';

		if ($preview)
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
			Manager::getApplication()->restartBuffer();
			header('Content-type: image/x-icon');
			echo file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/bitrix/components/bitrix/landing.pub/favicon.ico');
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
				'=ACTIVE' => 'Y'
			);
		}
		else
		{
			$filter = array(
				'=CODE' => '/' . $siteUrl . '/',//@todo fixme
				'=ACTIVE' => 'Y'
			);
		}
		if ($serverHost && !$preview)
		{
			if (strpos($serverHost, ':') !== false)
			{
				list($serverHost, ) = explode(':', $serverHost);
			}
			$filter['=DOMAIN.DOMAIN'] = $serverHost;
		}
		$res = Site::getList(array(
			'select' => array(
				'ID',
				'LANDING_ID_404',
				'LANDING_ID_INDEX'
			),
			'filter' => $filter
		));
		if (!($site = $res->fetch()))
		{
			return $landingIdExec;
		}

		// detect landing
		$res = Landing::getList(array(
			'select' => array(
				'ID', 'CODE', 'RULE', 'FOLDER'
			),
			'filter' => array(
				'SITE_ID' => $site['ID'],
				'=ACTIVE' => 'Y',
				'FOLDER_ID' => false,
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
				)
			)
		));
		while (($landing = $res->fetch()))
		{
			if ($landing['CODE'] == $landingUrl)
			{
				if ($landingSubUrl)
				{
					if ($landing['FOLDER'] == 'Y')
					{
						// check landing in subfolder
						$resSub = Landing::getList(array(
							'select' => array(
								'ID', 'CODE', 'RULE'
							),
							'filter' => array(
								'SITE_ID' => $site['ID'],
								'=ACTIVE' => 'Y',
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
							)
						));
						while ($row = $resSub->fetch())
						{
							if ($row['CODE'] == $landingSubUrl)
							{
								$landingIdExec = $row['ID'];
							}
							else if (
								$row['RULE'] &&
								preg_match('@^'. trim($row['RULE']) . '$@i', $landingSubUrl, $matches)
							)
							{
								$landingIdExec = $row['ID'];
								array_shift($matches);
								$this->sefVariables = $matches;
							}
						}
					}
				}
				else
				{
					$landingIdExec = $landing['ID'];
				}
			}
			else if (
				$landing['RULE'] && $landing['FOLDER'] != 'Y' &&
				preg_match('@^'. trim($landing['RULE']) . '$@i', $landingUrl, $matches)
			)
			{
				$landingIdExec = $landing['ID'];
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

		// try load special landings if landing not found
		if (!$landingIdExec)
		{
			if (in_array($landingUrl, array('index.php', '')))
			{
				if ($landingIdIndex)
				{
					$landingIdExec = $landingIdIndex;
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
		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->addEventHandler('main', 'OnBeforeLocalRedirect',
			function(&$url, $skipCheck, $bExternal)
			{
				$landing = $this->arResult['LANDING'];
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

		$eventManager = \Bitrix\Main\EventManager::getInstance();
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

				$landing = Landing::createInstance($lid);
				$this->arResult['LANDING'] = $landing;
				$this->arResult['DOMAIN'] = $this->getParentDomain();
				$this->arResult['ADV_CODE'] = $this->getAdvCode();

				if ($landing->exist())
				{
					// exeec Hook Robots
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
								$robotsContent .= 'Sitemap: ' . Site::getPublicUrl($landing->getSiteId()) . '/sitemap.xml';
							}
							// out
							if ($robotsContent)
							{
								header('content-type: text/plain');
								echo $robotsContent;
							}
							else
							{
								\CHTTP::setStatus('404 Not Found');
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
							\CHTTP::setStatus('404 Not Found');
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

				// change view
				Manager::setPageView(
					'MainClass',
					'landing-public-mode'
				);
			}
			else
			{
				\CHTTP::SetStatus('404 Not Found');
				$this->addError(
					'LANDING_CMP_SITE_NOT_FOUND',
					Loc::getMessage('LANDING_CMP_SITE_NOT_FOUND')
				);
			}
		}

		parent::executeComponent();
	}
}