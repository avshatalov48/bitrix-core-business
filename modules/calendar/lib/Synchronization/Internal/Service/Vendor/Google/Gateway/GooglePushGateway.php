<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Google\Gateway;

use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Synchronization\Internal\Entity\Push\Push;
use Bitrix\Calendar\Synchronization\Internal\Entity\SectionConnection;
use Bitrix\Calendar\Synchronization\Internal\Exception\ApiException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Exception;
use Bitrix\Calendar\Synchronization\Internal\Exception\DtoValidationException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\Google\RateLimitExceededException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\NotAuthorizedException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\UnexpectedException;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Google\Dto\Push\PushResponse;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Google\Push\ChannelType;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Google\Dto\Push\PushCreateRequest;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;

class GooglePushGateway extends AbstractGoogleGateway
{
	/**
	 * @param SectionConnection $sectionConnection
	 *
	 * @return PushResponse|null
	 *
	 * @throws ApiException
	 * @throws Exception
	 * @throws DtoValidationException
	 * @throws NotAuthorizedException
	 * @throws ArgumentException
	 * @throws RateLimitExceededException
	 */
	public function addSectionPush(SectionConnection $sectionConnection): ?PushResponse
	{
		if ($this->isVirtualCalendar($sectionConnection))
		{
			return null;
		}

		$requestData = new PushCreateRequest(
			$sectionConnection->getId(),
			ChannelType::SectionConnection,
			$sectionConnection->getSection()?->getOwner()->getId()
		);

		$this->request(
			HttpClient::HTTP_POST,
			self::BASE_PATH . sprintf('/calendars/%s/events/watch', urlencode($sectionConnection->getVendorSectionId())),
			Json::encode($requestData, JSON_UNESCAPED_SLASHES) // @todo Change to $this->encode
		);

		if ($this->isRequestSuccess())
		{
			return PushResponse::fromJson($this->client->getResult());
		}

		$this->processErrors('Section was not subscribed to Google updates');
	}

	/**
	 * @param Connection $connection
	 *
	 * @return PushResponse
	 *
	 * @throws ApiException
	 * @throws ArgumentException
	 * @throws DtoValidationException
	 * @throws Exception
	 * @throws NotAuthorizedException
	 * @throws RateLimitExceededException
	 */
	public function addConnectionPush(Connection $connection): PushResponse
	{
		$requestData = new PushCreateRequest(
			$connection->getName(),
			ChannelType::Connection,
			$connection->getOwner()->getId()
		);

		$this->request(
			HttpClient::HTTP_POST,
			self::BASE_PATH . '/users/me/calendarList/watch',
			Json::encode($requestData, JSON_UNESCAPED_SLASHES) // @todo Change to $this->encode
		);

		if ($this->isRequestSuccess())
		{
			return PushResponse::fromJson($this->client->getResult());
		}

		$this->processErrors('Connection was not subscribed to Google updates');
	}

	/**
	 * @throws NotAuthorizedException
	 * @throws RateLimitExceededException
	 * @throws UnexpectedException
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public function deletePush(Push $push): void
	{
		$this->request(
			HttpClient::HTTP_POST,
			self::BASE_PATH . '/channels/stop',
			$this->encode(
				[
					'id' => $push->getChannelId(),
					'resourceId' => $push->getResourceId(),
				]
			)
		);

		if (!$this->isDeleteRequestSuccess())
		{
			/** @noinspection PhpUnhandledExceptionInspection */
			$this->processErrors('Error of stopping push channel');
		}
	}

	private function isVirtualCalendar(SectionConnection $sectionConnection): bool
	{
		$vendorId = $sectionConnection->getVendorSectionId();
		$type = $sectionConnection->getSection()?->getExternalType();

		return (strpos($vendorId, 'holiday.calendar.google.com'))
			|| (strpos($vendorId, 'group.v.calendar.google.com'))
			|| (strpos($vendorId, '@virtual'))
			|| (strpos($type, '_readonly'))
			|| (strpos($type, '_freebusy'));
	}
}
