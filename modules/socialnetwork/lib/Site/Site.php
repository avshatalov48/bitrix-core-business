<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Site;

use Bitrix\Socialnetwork\Collab\Integration\Extranet\Extranet;
use Bitrix\Socialnetwork\Helper\SingletonTrait;
use CSite;

class Site
{
	use SingletonTrait;

	protected string $mainSiteId;
	protected string $extranetSiteId;

	private function __construct()
	{
		$this->init();
	}

	public function getMainSiteId(): string
	{
		return $this->mainSiteId;
	}

	public function getExtranetSiteId(): string
	{
		return $this->extranetSiteId;
	}

	public function getCollabSiteIds(): array
	{
		return [$this->mainSiteId, $this->extranetSiteId];
	}

	public function getDirectory(): string
	{
		return SITE_DIR;
	}

	private function init(): void
	{
		$this->mainSiteId = (string)CSite::GetDefSite();
		$this->extranetSiteId = Extranet::getSiteId();
	}
}