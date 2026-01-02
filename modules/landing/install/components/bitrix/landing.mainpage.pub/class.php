<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\EventManager;
use Bitrix\Main\Event;
use Bitrix\Crm\UI\Webpack\CallTracker;
use Bitrix\Crm\MessageSender\NotificationsPromoManager;
use Bitrix\Landing\Hook;
use Bitrix\Landing\Manager;
use Bitrix\Landing\Landing;
use Bitrix\Landing\Domain;
use Bitrix\Landing\Site;
use Bitrix\Landing\Site\Type;
use Bitrix\Landing\Syspage;
use Bitrix\Landing\Mainpage;

Loc::loadMessages(__FILE__);

\CBitrixComponent::includeComponentClass('bitrix:landing.base');

class LandingMainpagePubComponent extends LandingBaseComponent
{
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
	protected string $zone = '';

	/**
	 * Http status was send.
	 * @var bool
	 */
	protected bool $httpStatusSend = false;

	/**
	 * Current http status.
	 * @var string
	 */
	protected string $currentHttpStatus = self::ERROR_STATUS_OK;

	/**
	 * Main instance of current page.
	 * @var array
	 */
	protected static ?array $landingMain = null;

	/**
	 * Gets main instance of current page.
	 * @return array
	 */
	public static function getMainInstance(): ?array
	{
		return self::$landingMain;
	}

	/**
	 * Return true if just preview (not view) mode
	 * @return bool
	 */
	public function isPreviewMode(): bool
	{
		return $this->isPreviewMode;
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
			$this->checkParam('CHECK_PERMISSIONS', 'N');
			$this->checkParam('SHOW_EDIT_PANEL', 'N');
			$this->checkParam('SKIP_404', 'N');
			$this->checkParam('PAGE_URL_LANDING_VIEW', '');
			$this->checkParam('PAGE_URL_SITES', '');
			$this->checkParam('PAGE_URL_SITE_SHOW', '');
			$this->checkParam('PAGE_URL_ROLES', '');

			Type::setScope(Type::SCOPE_CODE_MAINPAGE);

			// always draft
			$this->isPreviewMode = true;

			if ($this->detectPage())
			{
				$lid = $this->arResult['LANDING_ID'];

				if ($this->isPreviewMode)
				{
					Hook::setEditMode();
					$this->onPreviewMode();
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
					Landing::setVariables([
						'sef' => $this->sefVariables,
					]);
				}
				// some other vars
				if ($this->isPreviewMode)
				{
					Landing::setPreviewMode(true);
				}
				$landing = Landing::createInstance($lid, [
					'check_permissions' => $this->arParams['CHECK_PERMISSIONS'] == 'Y',
					'disable_link_preview' => $this->isPreviewMode,
				]);
				self::$landingMain['LANDING_ID'] = $lid;
				self::$landingMain['LANDING_INSTANCE'] = $landing;
				$this->arResult['LANDING'] = $landing;
				$this->arResult['SITE_RELATIVE_URL'] = Site::getPublicUrl($landing->getSiteId(), true, false);
				$this->arResult['SEARCH_RESULT_QUERY'] = $this->request('q');
				$this->arResult['CAN_EDIT'] = 'N';

				// if landing found
				if ($landing->exist())
				{
					Site\Version::update($landing->getSiteId(), $landing->getMeta()['SITE_VERSION']);

					$this->replaceParamsUrls($landing);
				}

				$this->setErrors(
					$landing->getError()?->getErrors()
				);

				if ($landing->getError()?->isEmpty())
				{
					// events
					$this->onBeforeLocalRedirect();

					// todo: need?
					\Bitrix\Landing\Landing\View::inc($lid);
				}
			}
			else
			{
				/**
				 * @var Mainpage\Manager $manager
				 */
				$manager = $this->arResult['MANAGER'];
				if (isset($manager) && $manager->isProcessing())
				{
					$this->template = 'processing';
				}
				else
				{
					$this->addError(
						'SITE_NOT_FOUND',
						$this->getMessageType('LANDING_CMP_SITE_NOT_FOUND2')
					);
				}
			}
		}

		if (!empty($this->getErrors()))
		{
			$this->template = 'error';
		}

		parent::executeComponent();
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
	 * Detect landing by path.
	 * @return int|null Detected landing id or false.
	 */
	public function detectPage(): ?int
	{
		if (
			isset($this->arParams['SEF_MODE'])
			&& $this->arParams['SEF_MODE'] === 'Y'
			&& isset($this->arParams['SEF_FOLDER'])
			&& isset($this->arParams['SEF_URL_TEMPLATES'])
		)
		{
			// todo: need behaviors without sef mode?
			$urlTemplates = \CComponentEngine::makeComponentUrlTemplates(
				[],
				$this->arParams['SEF_URL_TEMPLATES']
			);
			\CComponentEngine::parseComponentPath(
				$this->arParams['SEF_FOLDER'],
				$urlTemplates,
				$variables
			);

			$code = $variables['mainpage_code'] ?? '';
			$resSite = Site::getList([
				'select' => ['ID', 'LANDING_ID_INDEX'],
				'filter' => [
					'=DELETED' => 'N',
					'CODE' => "/{$code}/",
					'=TYPE' => Type::SCOPE_CODE_MAINPAGE,
				],
				'limit' => 1,
			]);
			if ($site = $resSite->fetch())
			{
				$resLanding = Landing::getList([
					'select' => ['ID'],
					'filter' => [
						'=ID' => (int)$site['LANDING_ID_INDEX'],
						'=SITE_ID' => (int)$site['ID'],
						'=DELETED' => 'N',
					],
					'limit' => 1,
				]);
				if ($landing = $resLanding->fetch())
				{
					$this->arResult['LANDING_ID'] = (int)$landing['ID'];
				}
			}
		}
		else
		{
			$this->arResult['MANAGER'] = new Mainpage\Manager();
			$this->arResult['LANDING_ID'] = $this->arResult['MANAGER']->getConnectedPageId();
		}

		return (int)$this->arResult['LANDING_ID'] > 0;
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
	 * Handler on preview mode.
	 * @return void
	 */
	protected function onPreviewMode(): void
	{
		$eventManager = EventManager::getInstance();

		Manager::setPageView('BodyClass', 'landing-mode-preview no-page-header');

		// remove all target="_self" in links
		$eventManager->addEventHandler('main', 'OnEndBufferContent',
			function(&$content)
			{
				$content = str_replace(
					['target="_self"', 'href="#"'],
					['', 'href=""'],
					$content
				);
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
			'PAGE_URL_LANDING_VIEW', 'PAGE_URL_SITES', 'PAGE_URL_SITE_SHOW',
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
	 * Sends push on landing first view.
	 * @param int $landingId Landing id.
	 * @return void
	 */
	protected function sendPageViewPush(int $landingId): void
	{
		if (Loader::includeModule('pull'))
		{
			\CPullWatch::addToStack(
				'LANDING_ENTITY_LANDING',
				[
					'module_id' => 'landing',
					'command' => 'onLandingFirstView',
					'params' => [
						'ladingId' => $landingId,
					],
				]
			);
		}
	}
}
