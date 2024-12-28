<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\User;

use Bitrix\Extranet\Service\ServiceContainer;
use Bitrix\Main\Loader;
use Bitrix\Socialnetwork\Site\Site;

class User
{
	protected int $id;

	public function __construct(int $id)
	{
		$this->id = $id;
	}

	public function getId(): int
	{
		return $this->id;
	}

	public function isCollaber(): bool
	{
		if (!Loader::includeModule('extranet'))
		{
			return false;
		}

		return ServiceContainer::getInstance()->getCollaberService()->isCollaberById($this->id);
	}

	public function isExtranet(): bool
	{
		return !$this->isIntranet();
	}

	public function isIntranet(): bool
	{
		return \Bitrix\Socialnetwork\Integration\Intranet\User::isIntranet($this->id);
	}

	public function getSiteId(): string
	{
		$site = Site::getInstance();

		if ($this->isIntranet())
		{
			return $site->getMainSiteId();
		}

		return $site->getExtranetSiteId();
	}
}