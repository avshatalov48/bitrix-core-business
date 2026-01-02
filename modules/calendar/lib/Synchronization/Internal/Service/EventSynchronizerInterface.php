<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service;

use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Synchronization\Internal\Entity\SectionConnection;

interface EventSynchronizerInterface
{
	public function sendEvent(Event $event): void;

	public function importSectionEvents(SectionConnection $sectionConnection): void;

	public function importEvents(int $userId): void;

	public function sendInstance(Event $event): void;
}
