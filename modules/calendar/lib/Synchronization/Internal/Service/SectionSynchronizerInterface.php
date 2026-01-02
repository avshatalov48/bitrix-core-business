<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service;

use Bitrix\Calendar\Core\Section\Section;

interface SectionSynchronizerInterface
{
	public function sendSection(Section $section): void;

	public function deleteSection(string $vendorSectionId, int $userId): void;

	public function importSections(int $userId, ?string $token = null): array;
}
