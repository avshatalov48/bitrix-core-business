<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Google\Dto\Push;


use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Google\Push\CallbackUrlBuilder;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Google\Push\ChannelType;
use JsonSerializable;

/**
 * DTO for request of push creation in Google.
 *
 * https://developers.google.com/calendar/api/v3/reference/events/watch#request-body
 * https://developers.google.com/calendar/api/v3/reference/calendarList/watch#request-body
 */
class PushCreateRequest implements JsonSerializable
{
	// The time-to-live in seconds for the notification channel. Default in Google is 604800 seconds.
	private const DEFAULT_TTL = 604800; // 60*60*24*7;

	private string $id;

	/**
	 * @param int|string $entityId SectionConnection id or Connection name
	 * @param ChannelType $type
	 * @param int $userId
	 */
	public function __construct(
		public readonly int|string $entityId,
		public readonly ChannelType $type,
		public readonly int $userId
	)
	{
		$this->id = $this->type->value . '_' . $this->userId . '_' . md5($this->entityId . time());
	}

	public function getId(): string
	{
		return $this->id;
	}

	public function jsonSerialize(): array
	{
		return [
			'id' => $this->id,
			'type' => 'web_hook',
			'address' => CallbackUrlBuilder::buildUrl(),
			'params' => [
				'ttl' => self::DEFAULT_TTL,
			],
		];
	}
}
