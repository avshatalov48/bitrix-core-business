<?php

declare(strict_types=1);

namespace Bitrix\Rest\Service;

use Bitrix\Main\Web\Uri;
use Bitrix\Rest\Contract;
use Bitrix\Rest\Entity\Collection\AppCollection;
use Bitrix\Rest\Internals\FreeAppTable;
use Bitrix\Rest\Marketplace\Client;

class AppService implements Contract\Service\AppService
{
	public function __construct(
		private readonly Contract\Repository\AppRepository $appRepository,
	)
	{}

	public function getPaidApplications(): AppCollection
	{
		return $this->appRepository->getPaidApplications();
	}

	public function hasPaidApps(): bool
	{
		return $this->appRepository->hasPaidApps();
	}
}
