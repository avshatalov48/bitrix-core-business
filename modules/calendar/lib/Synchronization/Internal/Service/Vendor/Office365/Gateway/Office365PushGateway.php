<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365\Gateway;

use Bitrix\Calendar\Synchronization\Internal\Entity\SectionConnection;
use Bitrix\Calendar\Synchronization\Internal\Exception\DtoValidationException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\GoneException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\NotAuthorizedException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\NotFoundException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\PreconditionFailedException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\UnexpectedException;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365\Dto\Push\PushCreateRequest;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365\Dto\Push\PushResponse;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365\Dto\Push\PushUpdateRequest;

class Office365PushGateway extends AbstractOffice365Gateway
{
	/**
	 * @throws DtoValidationException
	 * @throws GoneException
	 * @throws NotAuthorizedException
	 * @throws NotFoundException
	 * @throws UnexpectedException
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public function addSectionPush(SectionConnection $sectionConnection): PushResponse
	{
		$requestData = new PushCreateRequest(
			$sectionConnection->getId(),
			$sectionConnection->getVendorSectionId(),
			$sectionConnection->getSection()->getOwner()->getId()
		);

		/** @noinspection PhpUnhandledExceptionInspection */
		$response = $this->post('subscriptions', $requestData->toArray());

		return PushResponse::fromArray($response);
	}

	/**
	 * @throws GoneException
	 * @throws DtoValidationException
	 * @throws NotAuthorizedException
	 * @throws NotFoundException
	 * @throws PreconditionFailedException
	 * @throws UnexpectedException
	 */
	public function renewPush(string $resourceId): PushResponse
	{
		$requestData = new PushUpdateRequest();

		$response =  $this->patch('subscriptions/' . $resourceId, $requestData->toArray());

		return PushResponse::fromArray($response);
	}

	/**
	 * @throws UnexpectedException
	 */
	public function deletePush(string $resourceId): void
	{
		try
		{
			$this->delete('subscriptions/' . $resourceId);
		}
		catch (NotFoundException|GoneException|NotAuthorizedException)
		{
			return;
		}
	}
}
