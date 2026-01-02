<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Onboarding\Collab\Repository\Cache;

use Bitrix\Socialnetwork\Collab\Onboarding\Collab\Entity\FirstAddedMemberData;

interface CollabCacheInterface
{
	public function getFirstAddedMemberData(int $collabId): ?FirstAddedMemberData;
	public function save(FirstAddedMemberData $firstAddedData): void;
}
