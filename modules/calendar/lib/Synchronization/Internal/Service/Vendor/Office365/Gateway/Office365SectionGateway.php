<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365\Gateway;

use Bitrix\Calendar\Core\Section\Section;
use Bitrix\Calendar\Sync\Office365\ApiService;
use Bitrix\Calendar\Sync\Office365\Converter\ColorConverter;
use Bitrix\Calendar\Synchronization\Internal\Entity\SectionConnection;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Common\Gateway\SectionGatewayInterface;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\ConflictException;
use Bitrix\Calendar\Synchronization\Internal\Exception\DtoValidationException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\NotAuthorizedException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\GoneException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\NotFoundException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\PreconditionFailedException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\UnexpectedException;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365\Dto\CalendarListResponse;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365\Dto\CalendarResponse;

class Office365SectionGateway extends AbstractOffice365Gateway implements SectionGatewayInterface
{
	/**
	 * @throws ConflictException
	 * @throws DtoValidationException
	 * @throws NotAuthorizedException
	 * @throws UnexpectedException
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public function createSection(Section $section): CalendarResponse
	{
		$selectParams = ApiService::getCalendarSelectParams();

		$selectQuery = http_build_query($selectParams);

		/** @noinspection PhpUnhandledExceptionInspection */
		$response = $this->post(
			'me/calendars?' . $selectQuery,
			[
				'name' => $section->getExternalName(),
				'color' => ColorConverter::toOffice($section->getColor()),
			]
		);

		return CalendarResponse::fromArray($response);
	}

	/**
	 * @throws DtoValidationException
	 * @throws GoneException
	 * @throws NotAuthorizedException
	 * @throws NotFoundException
	 * @throws PreconditionFailedException
	 * @throws UnexpectedException
	 */
	public function updateSection(SectionConnection $sectionConnection): CalendarResponse
	{
		$response = $this->patch(
			'me/calendars/' . $sectionConnection->getVendorSectionId(),
			[
				'name' => $sectionConnection->getSection()->getExternalName(),
			]
		);

		return CalendarResponse::fromArray($response);
	}

	/**
	 * @throws GoneException
	 * @throws NotAuthorizedException
	 * @throws NotFoundException
	 * @throws UnexpectedException
	 */
	public function deleteSection(string $vendorSectionId): void
	{
		$this->delete('me/calendars/' . $vendorSectionId);
	}

	/**
	 * @throws DtoValidationException
	 * @throws GoneException
	 * @throws NotAuthorizedException
	 * @throws NotFoundException
	 * @throws UnexpectedException
	 */
	public function getCalendarList(): CalendarListResponse
	{
		$selectParams = ApiService::getCalendarSelectParams();

		$response = $this->get('me/calendars', $selectParams);

		return CalendarListResponse::fromArray($response);
	}
}
