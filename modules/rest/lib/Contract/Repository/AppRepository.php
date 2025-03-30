<?php

declare(strict_types=1);

namespace Bitrix\Rest\Contract\Repository;

use Bitrix\Rest\Entity\Collection\AppCollection;

interface AppRepository
{
	public function getPaidApplications(): AppCollection;
	public function hasPaidApps(): bool;
}
