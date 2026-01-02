<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365\Dto\Push;

use CCalendar;

class PushCreateRequest extends AbstractSubscriptionRequest
{
	protected string $id;

	public function __construct(
		public readonly int $entityId,
		public readonly string $vendorSectionId,
		public readonly int $userId
	)
	{
		$this->id = 'BX_OFFICE_SC_' . $this->userId . '_' . md5($this->entityId . time());
	}

	public function getId(): string
	{
		return $this->id;
	}

	/**
	 * @return array
	 */
	public function toArray(): array
	{
		return [
			'changeType' => 'created,updated,deleted',
			'notificationUrl' => $this->getNotificationUrl(),
			'resource' => sprintf('me/calendars/%s/events', $this->vendorSectionId),
			'expirationDateTime' => $this->getExpirationDateTime(),
			'clientState' => $this->id,
			'latestSupportedTlsVersion' => 'v1_2',
		];
	}

	/**
	 * @return string
	 */
	private function getNotificationUrl(): string
	{
		return str_replace('http:', 'https:', CCalendar::GetServerPath())
			. '/bitrix/tools/calendar/office365push.php';
	}
}
