<?php

declare(strict_types=1);

namespace Bitrix\Rest\Contract\Service;

use Bitrix\Rest\Entity\Collection\AppCollection;

interface AppService
{
	public function getPaidApplications(): AppCollection;
	public function hasPaidApps(): bool;
}
