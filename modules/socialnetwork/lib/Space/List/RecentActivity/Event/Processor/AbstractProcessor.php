<?php

namespace Bitrix\Socialnetwork\Space\List\RecentActivity\Event\Processor;

use Bitrix\Socialnetwork\Integration\Pull\PushService;
use Bitrix\Socialnetwork\Internals\EventService\Event;
use Bitrix\Socialnetwork\Internals\EventService\Push\PushEventDictionary;
use Bitrix\Socialnetwork\Internals\EventService\Recepients\Recepient;
use Bitrix\Socialnetwork\Space\List\RecentActivity\Collector;
use Bitrix\Socialnetwork\Space\List\RecentActivity\Service;
use Bitrix\Socialnetwork\Space\List\RecentActivity\Item\RecentActivityData;

abstract class AbstractProcessor implements ProcessorInterface
{
	protected Collector $collector;
	protected Service $activityService;

	public function __construct(
		protected Event $event,
		protected Recepient $recipient,
	)
	{
		$this->collector = new Collector(Collector::getDefaultProviders());
		$this->activityService = new Service();
	}

	abstract protected function getTypeId(): string;
	abstract protected function isAvailable(): bool;
	abstract protected function process(): void;

	protected function saveRecentActivityData(int $spaceId, int $entityId, ?int $secondaryEntityId = null): void
	{
		$recentActivityData =
			(new RecentActivityData())
				->setSpaceId($spaceId)
				->setTypeId($this->getTypeId())
				->setEntityId($entityId)
				->setUserId($this->recipient->getId())
				->setDateTime($this->event->getDateTime())
				->setSecondaryEntityId($secondaryEntityId)
		;

		$isSuccess = $this->activityService->save($recentActivityData);

		if (!$isSuccess)
		{
			return;
		}

		$this->collector->addRecentActivityData($recentActivityData);
		$this->collector->fillData();

		$this->sendUpdatePush($recentActivityData);
	}

	protected function sendUpdatePush(RecentActivityData $recentActivityData): void
	{
		$this->sendPush(
			PushEventDictionary::EVENT_SPACE_RECENT_ACTIVITY_UPDATE,
			[$recentActivityData->toArray()]
		);
	}

	private function sendPush(string $command, array $params): void
	{
		if ($this->recipient->isWatchingSpaces())
		{
			PushService::addEvent($this->recipient->getId(), [
				'module_id' => PushService::MODULE_NAME,
				'command' => $command,
				'params' => $params,
			]);
		}
	}

	protected function deleteRecentActivityData(int $entityId): void
	{
		$this->activityService->deleteByUserId($this->recipient->getId(), $this->getTypeId(), $entityId);
		$this->sendDeletePush($entityId);
	}

	protected function sendDeletePush(int $entityId): void
	{
		$this->sendPush(PushEventDictionary::EVENT_SPACE_RECENT_ACTIVITY_DELETE, [
			'typeId' => $this->getTypeId(),
			'entityId' => $entityId,
		]);
	}

	final public function processEvent(): void
	{
		if (!$this->isAvailable())
		{
			return;
		}

		$this->process();
	}
}
