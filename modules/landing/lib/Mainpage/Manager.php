<?php

namespace Bitrix\Landing\Mainpage;

use Bitrix\AI\Integration;
use Bitrix\Landing;
use Bitrix\Landing\Site;
use Bitrix\Landing\Rights;
use Bitrix\Landing\Site\Type;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

/**
 * Manage mainpage site and pages
 */
class Manager
{
	private const SITE_ID_OPTION_CODE = 'mainpage_site_id';
	private const FULLY_CREATED_OPTION_CODE = 'mainpage_created';

	/**
	 * Connected landing
	 */
	protected ?int $siteId = null;
	protected ?int $landingId = null;
	protected ?Landing\Landing $landing = null;
	protected ?string $previewImg = null;
	protected ?string $pageTitle = null;

	public static function isAvailable(): bool
	{
		return Landing\Manager::isB24Cloud();
	}

	/**
	 * Manager constructor.
	 */
	public function __construct()
	{
		// getList filter by TYPE don't work in wrong scope
		$scopeBefore = Type::getCurrentScopeId();
		Type::setScope(Type::SCOPE_CODE_MAINPAGE);

		$this->detectConnectedSite();
		$this->detectConnectedPage();

		if ($scopeBefore !== Type::SCOPE_CODE_MAINPAGE)
		{
			$scopeBefore
				? Type::setScope($scopeBefore)
				: Type::clearScope()
			;
		}
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

		$storedSiteId = Landing\Manager::getOption(self::SITE_ID_OPTION_CODE);
		$this->siteId = $storedSiteId;

		if (!$storedSiteId)
		{
			Rights::setGlobalOff();

			// try find
			$exists = (Landing\Site::getList([
				'select' => ['ID', 'TYPE', 'ACTIVE'],
				'filter' => [
					'=ACTIVE' => 'Y',
					'TYPE' => Type::SCOPE_CODE_MAINPAGE,
					'=SPECIAL' => 'Y',
					'CHECK_PERMISSIONS' => 'N',
				],
			]))->fetch();
			if ($exists && (int)$exists['ID'] && $exists['TYPE'] === Type::SCOPE_CODE_MAINPAGE)
			{
				$this->siteId = (int)$exists['ID'];
			}
			else
			{
				$newId = $this->createDefaultSite();
				if ($newId)
				{
					$this->siteId = $newId;
				}
			}

			if ($this->siteId)
			{
				Landing\Manager::setOption(self::SITE_ID_OPTION_CODE, $this->siteId);
				Rights::setGlobalOn();

				return;
			}
		}

		// check that exists
		if ($this->siteId)
		{
			$new = Landing\Site::getList([
				'select' => [
					'ID',
				],
				'filter' => [
					'=ID' => $this->siteId,
					'=TYPE' => Type::SCOPE_CODE_MAINPAGE,
					'=SPECIAL' => 'Y',
					'CHECK_PERMISSIONS' => 'N',
				],
			]);

			$site = $new->fetch();
			if (!$site)
			{
				$this->siteId = null;
				Landing\Manager::setOption(self::SITE_ID_OPTION_CODE, $this->siteId);

				$this->detectConnectedSite();
			}
		}
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

	public function createDefaultSocialGroupForPublication(): void
	{
		if (Loader::includeModule('socialnetwork'))
		{
			$dbSubjects = \CSocNetGroupSubject::GetList(
				["SORT"=>"ASC", "NAME" => "ASC"],
				["SITE_ID" => SITE_ID],
				false,
				false,
				["ID", "NAME"]
			);
			$firstSubject = $dbSubjects->GetNext();
			$groupFields = array(
				"SITE_ID" => SITE_ID,
				"NAME" => Loc::getMessage('LANDING_MAINPAGE_SOCIAL_GROUP_FOR_PUBLICATION_NAME'),
				"VISIBLE" => 'Y',
				"OPENED" => 'Y',
				"CLOSED" => 'N',
				"LANDING" => 'Y',
				"SUBJECT_ID" => $firstSubject['ID'],
				"INITIATE_PERMS" => 'E',
				"SPAM_PERMS" => 'E',
			);
			$idGroup = \CSocNetGroup::createGroup(Landing\Manager::getUserId(), $groupFields);
			if ($idGroup)
			{
				Option::set('landing', 'mainpage_id_publication_group', $idGroup);
			}
		}
	}

	public function isExistDefaultSocialGroupForPublication(): bool
	{
		$publicationGroupId = Option::get('landing', 'mainpage_id_publication_group');

		return $publicationGroupId > 0;
	}

	/**
	 * Try to find landing for mainpage
	 * @return void
	 */
	private function detectConnectedPage(): void
	{
		if (!$this->siteId)
		{
			$this->landingId = null;

			return;
		}

		if ($this->landingId)
		{
			return;
		}

		$siteRes = Site::getList([
			'select' => ['ID', 'LANDING_ID_INDEX'],
			'filter' => [
				'=ID' => $this->siteId,
			],
		]);
		if ($site = $siteRes->fetch())
		{
			$exists = (Landing\Landing::getList([
				'select' => ['ID'],
				'filter' => [
					'=SITE_ID' => $this->siteId,
					'=ID' => $site['LANDING_ID_INDEX'],
				],
				'order' => [
					'ID' => 'asc',
				],
			]))->fetch();

			if ($exists && (int)$exists['ID'])
			{
				$this->landingId = (int)$exists['ID'];

				$this->detectPreviewImg();
				$this->detectPageTitle();
			}
		}
	}

	/**
	 * @return int|null
	 */
	public function createDemoPage(): ?int
	{
		if (
			$this->getConnectedSiteId()
			&& !$this->getConnectedPageId()
		)
		{
			$result = Landing\Landing::addByTemplate(
				$this->siteId,
				'empty',
				[
					'SITE_TYPE' => Type::SCOPE_CODE_MAINPAGE,
				]
			);

			if ($result->isSuccess() && $result->getId())
			{
				$this->landingId = $result->getId();
				$this->markEndCreation();

				return $this->landingId;
			}
		}

		return null;
	}

	/**
	 * If page connected - get preview image url
	 * @return void
	 */
	private function detectPreviewImg(): void
	{
		$this->previewImg = $this->getConnectedPageId()
			? Landing\Manager::getUrlFromFile(Site::getPreview($this->getConnectedSiteId(), true))
			: null
		;
	}

	/**
	 * If page connected - get page title
	 * @return void
	 */
	private function detectPageTitle(): void
	{
		$this->pageTitle = $this->getConnectedPageId()
			? Landing\Landing::createInstance($this->getConnectedPageId())->getTitle()
			: null
		;
	}

	/**
	 * Check is Mainpage site is fully created, add all pages etc
	 * @return bool
	 */
	public function isReady(): bool
	{
		// todo: check option
		return $this->getConnectedPageId() && $this->isFullCreated();
	}

	protected function isFullCreated(): bool
	{
		return Landing\Manager::getOption(self::FULLY_CREATED_OPTION_CODE, 'Y') === 'Y';
	}

	/**
	 * Mark is Mainpage site is fully created, add all pages etc.
	 * Not created or check site or pages, just mark end of creating process.
	 * @return void
	 */
	public function markEndCreation(): void
	{
		Landing\Manager::setOption(self::FULLY_CREATED_OPTION_CODE, 'Y');
	}

	/**
	 * Mark is Mainpage site start creating.
	 * Not created or check site or pages, just mark start of creating process.
	 * @return void
	 */
	public function markStartCreation(): void
	{
		Landing\Manager::setOption(self::FULLY_CREATED_OPTION_CODE, 'N');
	}

	/**
	 * Return ID of special site for Mainpage
	 * @return int|null
	 */
	public function getConnectedSiteId(): ?int
	{
		return $this->siteId;
	}

	/**
	 * Return id of Mainpage landing
	 * @return int|null
	 */
	public function getConnectedPageId(): ?int
	{
		return $this->getConnectedSiteId() ? $this->landingId : null;
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
		return Landing\Manager::getOption('use_demo_data_in_block_widgets', 'N') === 'Y';
	}
}