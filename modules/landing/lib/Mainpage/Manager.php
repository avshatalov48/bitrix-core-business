<?php

namespace Bitrix\Landing\Mainpage;

use Bitrix\AI\Integration;
use Bitrix\Bitrix24\Feature;
use Bitrix\Intranet\MainPage\Publisher;
use Bitrix\Landing;
use Bitrix\Landing\Site;
use Bitrix\Landing\Rights;
use Bitrix\Landing\Site\Type;
use Bitrix\Main\Config\Option;
use Bitrix\Main\EventManager;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Pull;

/**
 * Manage mainpage site and pages
 */
class Manager
{
	private const SITE_ID_OPTION_CODE = 'mainpage_site_id';
	private const CREATION_OPTION_CODE = 'mainpage_creating';
	private const CREATION_OPTION_PROCESS = 'process';
	private const CREATION_OPTION_FULL = 'fully';
	private const USE_DEMO_OPTION_CODE = 'use_demo_data_in_block_widgets';
	private const FREE_MODE_OPTION_CODE = 'enable_at_free_tariff';

	/**
	 * Connected landing
	 */
	private ?int $siteId = null;
	private ?int $landingId = null;
	private ?string $previewImg = null;
	private ?string $pageTitle = null;
	private ?string $scopeBefore = null;
	private bool $rightsBefore;

	/**
	 * Check feature available. Not check tariff limits
	 * @return bool
	 */
	public static function isAvailable(): bool
	{
		// not in SMN
		return Loader::includeModule('intranet');
	}

	public static function isFeatureEnable(): bool
	{
		if (Loader::includeModule('bitrix24'))
		{
			return Feature::isFeatureEnabled('main_page');
		}

		return true;
	}

	/**
	 * Manager constructor.
	 */
	public function __construct()
	{
		$this->onBeforeOperation();

		$this->detectConnectedSite();
		$this->detectConnectedPage();

		$this->onAfterOperation();
	}

	/**
	 * Find exist or create new Mainpage site. Return site ID.
	 * @return void
	 */
	private function detectConnectedSite(): void
	{
		if ($this->siteId)
		{
			return;
		}

		Rights::setGlobalOff();

		$optionSiteId = (int)Landing\Manager::getOption(self::SITE_ID_OPTION_CODE);

		// check that exists
		if ($optionSiteId > 0)
		{
			$connectedSite = (Landing\Site::getList([
				'select' => ['LANDING_ID_INDEX'],
				'filter' => [
					'=ID' => $optionSiteId,
					'=ACTIVE' => 'Y',
					'TYPE' => Type::SCOPE_CODE_MAINPAGE,
					'=SPECIAL' => 'Y',
					'CHECK_PERMISSIONS' => 'N',
				],
				'cache' => ['ttl' => 86400],
			]))->fetch();
			if ($connectedSite)
			{
				$this->siteId = $optionSiteId;
				if (!$this->landingId && $connectedSite['LANDING_ID_INDEX'] > 0)
				{
					$this->landingId = (int)$connectedSite['LANDING_ID_INDEX'];
				}
				Rights::setGlobalOn();

				return;
			}
		}

		// try find
		$exists = (Landing\Site::getList([
			'select' => ['ID', 'TYPE', 'ACTIVE', 'LANDING_ID_INDEX'],
			'filter' => [
				'=ACTIVE' => 'Y',
				'TYPE' => Type::SCOPE_CODE_MAINPAGE,
				'=SPECIAL' => 'Y',
				'CHECK_PERMISSIONS' => 'N',
			],
		]))->fetch();
		if ($exists && (int)$exists['ID'])
		{
			$this->siteId = (int)$exists['ID'];
			if (!$this->landingId && $exists['LANDING_ID_INDEX'] > 0)
			{
				$this->landingId = (int)$exists['LANDING_ID_INDEX'];
			}
		}
		// create
		else
		{
			$newId = $this->createDefaultSite();
			if ($newId)
			{
				$this->siteId = $newId;
				$this->landingId = null;
			}
		}

		if ($this->siteId && $this->siteId !== $optionSiteId)
		{
			Landing\Manager::setOption(self::SITE_ID_OPTION_CODE, $this->siteId);
		}

		Rights::setGlobalOn();
	}

	private function createDefaultSite(): ?int
	{
		$new = Landing\Site::add([
			'TITLE' => Loc::getMessage('LANDING_MAINPAGE_SITE_NAME'),
			'CODE' => strtolower(Type::SCOPE_CODE_MAINPAGE),
			'TYPE' => Type::SCOPE_CODE_MAINPAGE,
			'SPECIAL' => 'Y',
		]);

		$defaultSiteId = null;
		if ($new->isSuccess())
		{
			$defaultSiteId = (int)$new->getId();
		}

		return $defaultSiteId;
	}

	/**
	 * Try to find landing for mainpage
	 * @return void
	 */
	private function detectConnectedPage(): void
	{
		if (!$this->getConnectedSiteId())
		{
			$this->landingId = null;

			return;
		}

		// check that exists
		if ($this->landingId > 0)
		{
			$exists = (Landing\Landing::getList([
				'select' => ['ID'],
				'filter' => [
					'=SITE_ID' => $this->getConnectedSiteId(),
					'=ID' => $this->landingId,
				],
				'order' => [
					'ID' => 'asc',
				],
				'cache' => ['ttl' => 86400],
			]))->fetch();

			if ($exists && (int)$exists['ID'])
			{
				$this->landingId = (int)$exists['ID'];

				$this->detectPreviewImg();
				$this->detectPageTitle();
			}
			else
			{
				$this->landingId = null;
			}
		}
	}

	/**
	 * If page connected - get preview image url
	 * @return void
	 */
	private function detectPreviewImg(): void
	{
		$this->previewImg = $this->getConnectedPageId()
			? Landing\Manager::getUrlFromFile(Site::getPreview($this->getConnectedSiteId(), true))
			: null;
	}

	/**
	 * If page connected - get page title
	 * @return void
	 */
	private function detectPageTitle(): void
	{
		$this->pageTitle = $this->getConnectedPageId()
			? Landing\Landing::createInstance($this->getConnectedPageId())->getTitle()
			: null;
	}

	public function createSonetGroupForPublicationOnce(): bool
	{
		if (!Loader::includeModule('socialnetwork'))
		{
			return false;
		}

		$storedGroupId = (int)Landing\Manager::getOption('mainpage_id_publication_group', 0);
		if ($storedGroupId > 0)
		{
			return true;
		}

		$firstSubject = \CSocNetGroupSubject::GetList(
			["SORT" => "ASC", "NAME" => "ASC"],
			["SITE_ID" => SITE_ID],
			false,
			false,
			["ID", "NAME"]
		)->Fetch();

		$fields = array(
			"SITE_ID" => SITE_ID,
			"NAME" => Loc::getMessage('LANDING_MAINPAGE_SOCIAL_GROUP_FOR_PUBLICATION_NAME'),
			"VISIBLE" => 'Y',
			"OPENED" => 'Y',
			"CLOSED" => 'N',
			"LANDING" => 'Y',
			"SUBJECT_ID" => $firstSubject['ID'] ?? 0,
			"INITIATE_PERMS" => 'E',
			"SPAM_PERMS" => 'E',
		);
		$newGroupId = (int)\CSocNetGroup::createGroup(Landing\Manager::getUserId(), $fields);
		if ($newGroupId && $newGroupId > 0)
		{
			Option::set('landing', 'mainpage_id_publication_group', $newGroupId);

			return true;
		}

		return false;
	}

	public function createPageByTemplate(?Templates $code = null, bool $publication = false): bool
	{
		if (!self::isAvailable())
		{
			return false;
		}

		$siteId = $this->getConnectedSiteId();
		if (!$siteId)
		{
			return false;
		}

		$this->onBeforeOperation();
		$this->onStartPageCreation();
		if ($publication)
		{
			(new Publisher())->publish();
		}

		$installer = new Installer($siteId);

		if ($code === null)
		{
			$newPageId = $installer->createDemoPage();
		}
		else
		{
			$newPageId = $installer->createPageByTemplate($code);
			$this->onTemplateCreation();
		}

		if (!$newPageId)
		{
			return false;
		}

		$this->landingId = $newPageId;
		$this->onFinishPageCreation();
		$this->onAfterOperation();

		return true;
	}

	/**
	 * Create default page. Using just for demo, not for product purposes
	 * @return bool
	 */
	public function createDemoPage(): bool
	{
		return $this->createPageByTemplate();
	}

	// todo: private
	private function onTemplateCreation(): void
	{
		EventManager::getInstance()->registerEventHandler(
			'intranet',
			'onLicenseHasChanged',
			'bitrix24',
			EventHandler::class,
			'onLicenseHasChanged'
		);
		self::setFreeTariffMode();
	}

	/**
	 * If true - enable some functionality at free tariff (by default in free tariff vibe is fully disabled)
	 * @param bool $flag
	 * @return void
	 */
	public static function setFreeTariffMode(bool $flag = true): void
	{
		Landing\Manager::setOption(self::FREE_MODE_OPTION_CODE, $flag ? 'Y' : 'N');
	}

	/**
	 * If true - enable some functionality at free tariff (by default in free tariff vibe is fully disabled)
	 * @return bool
	 */
	public static function isFreeTariffMode(): bool
	{
		return Landing\Manager::getOption(self::FREE_MODE_OPTION_CODE, 'N') === 'Y';
	}

	/**
	 * Check that the creation is processing now.
	 * @return bool
	 */
	public function isProcessing(): bool
	{
		return Landing\Manager::getOption(self::CREATION_OPTION_CODE) === self::CREATION_OPTION_PROCESS;
	}

	/**
	 * Check is Mainpage site is fully created, add all pages etc
	 * @return bool
	 */
	public function isReady(): bool
	{
		return $this->getConnectedPageId() && $this->isFullyCreated();
	}

	protected function isFullyCreated(): bool
	{
		$creating = Landing\Manager::getOption(self::CREATION_OPTION_CODE, self::CREATION_OPTION_FULL);

		return $creating === self::CREATION_OPTION_FULL;
	}


	/**
	 * Mark is Mainpage site start creating.
	 * Not created or check site or pages, just mark start of creating process.
	 * @return void
	 */
	public function onStartPageCreation(): void
	{
		Landing\Manager::setOption(self::CREATION_OPTION_CODE, self::CREATION_OPTION_PROCESS);
	}

	/**
	 * Call this method before do some operations by DB
	 * Don't forget onAfterOperation!
	 * @return void
	 */
	private function onBeforeOperation(): void
	{
		// getList filter by TYPE don't work in wrong scope
		$this->scopeBefore = Type::getCurrentScopeId();
		Type::setScope(Type::SCOPE_CODE_MAINPAGE);

		$this->rightsBefore = Rights::isOn();
		Rights::setOff();
		Rights::setGlobalOff();
	}

	/**
	 * Mark is Mainpage site is fully created, add all pages etc.
	 * Not created or check site or pages, just mark end of creating process.
	 * @return void
	 */
	public function onFinishPageCreation(): void
	{
		$connectedSiteId = $this->getConnectedSiteId();
		$connectedPageId = $this->getConnectedPageId();
		if (isset($connectedSiteId, $connectedPageId))
		{
			Landing\Manager::setOption(self::CREATION_OPTION_CODE, self::CREATION_OPTION_FULL);

			$this->createSonetGroupForPublicationOnce();

			Landing\Site::update($connectedSiteId, [
				'LANDING_ID_INDEX' => $connectedPageId,
			]);

			if (Loader::includeModule('pull'))
			{
				Pull\Event::add(
					Landing\Manager::getUserId(),
					[
						'module_id' => 'landing',
						'command' => 'Vibe:onCreate',
						'params' => [
							// todo: add vibe entity type
						],
					]
				);
			}
		}
	}

	/**
	 * Call this method after do some operations by DB
	 * @return void
	 */
	private function onAfterOperation(): void
	{
		if ($this->scopeBefore === null)
		{
			Type::clearScope();
		}
		elseif (
			is_string($this->scopeBefore)
			&& $this->scopeBefore !== Type::SCOPE_CODE_MAINPAGE
		)
		{
			Type::setScope($this->scopeBefore);
		}

		if ($this->rightsBefore)
		{
			Rights::setOn();
			Rights::setGlobalOn();
		}
	}

	/**
	 * Return ID of special site for Mainpage
	 * @return int|null
	 */
	public function getConnectedSiteId(): ?int
	{
		return (int)$this->siteId > 0 ? (int)$this->siteId : null;
	}

	/**
	 * Return id of Mainpage landing
	 * @return int|null
	 */
	public function getConnectedPageId(): ?int
	{
		return
			$this->getConnectedSiteId() && (int)$this->landingId > 0
				? (int)$this->landingId
				: null;
	}

	/**
	 * If page connected - get src to preview image. Else - empty string.
	 * @return string|null
	 */
	public function getPreviewImg(): ?string
	{
		return $this->previewImg;
	}

	/**
	 * If page connected - get page title. Else - empty string.
	 * @return string|null
	 */
	public function getPageTitle(): ?string
	{
		return $this->pageTitle;
	}

	/**
	 * Check is widgets must use demo data instead real data
	 * @return bool
	 */
	public static function isUseDemoData(): bool
	{
		return Landing\Manager::getOption(self::USE_DEMO_OPTION_CODE, 'N') === 'Y';
	}
}