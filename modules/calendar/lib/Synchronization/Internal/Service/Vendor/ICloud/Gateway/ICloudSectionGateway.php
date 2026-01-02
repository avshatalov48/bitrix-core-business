<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\ICloud\Gateway;

use Bitrix\Calendar\Core\Section\Section;
use Bitrix\Calendar\Integration\Dav\WebDavMethodProvider;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\AccessDeniedException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\BadRequestException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\NotAuthorizedException;
use Bitrix\Calendar\Synchronization\Internal\Exception\DtoValidationException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\NotFoundException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\UnexpectedException;
use Bitrix\Calendar\Synchronization\Internal\Service\Logger\RequestLogger;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\ICloud\Dto\CalendarListResponse;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\ICloud\Dto\CalendarResponse;
use Bitrix\Main\UuidGenerator;
use Bitrix\Main\Web\HttpClient;

class ICloudSectionGateway extends AbstractICloudGateway
{
	public function __construct(
		protected string $pathToCalendars,
		WebDavMethodProvider $davMethodProvider,
		RequestDataBuilder $requestDataBuilder,
		HttpClient $client,
		RequestLogger $logger,
		string $serverPath,
	)
	{
		parent::__construct($davMethodProvider, $requestDataBuilder, $client, $logger, $serverPath);
	}

	/**
	 * @throws BadRequestException
	 * @throws DtoValidationException
	 * @throws NotAuthorizedException
	 * @throws NotFoundException
	 * @throws UnexpectedException
	 */
	public function createSection(Section $section): CalendarResponse
	{
		$this->client->setHeader('Content-type', 'text/xml');

		$uri = $this->serverPath . $this->pathToCalendars . UuidGenerator::generateV4();
		$data = $this->requestDataBuilder->buildCreateSectionData($section);

		$this->request(
			$this->davMethodProvider->getMkcolMethod(),
			$uri,
			$data,
		);

		if (!$this->isRequestSuccess())
		{
			$this->processErrors('Section was not created');
		}

		return $this->getSection($uri);
	}

	/**
	 * @throws AccessDeniedException
	 * @throws BadRequestException
	 * @throws DtoValidationException
	 * @throws NotAuthorizedException
	 * @throws NotFoundException
	 * @throws UnexpectedException
	 */
	public function updateSection(Section $section, string $iCloudCalendarId): CalendarResponse
	{
		$this->client->setHeader('Content-type', 'text/xml');

		$uri = $this->serverPath . $iCloudCalendarId;
		$data = $this->requestDataBuilder->buildUpdateSectionData($section);

		$this->request(
			$this->davMethodProvider->getProppatchMethod(),
			$uri,
			$data,
		);

		if (!$this->isDavRequestSuccess())
		{
			$this->processErrors('Section was not updated');
		}

		return $this->getSection($uri);
	}

	/**
	 * @throws AccessDeniedException
	 * @throws NotAuthorizedException
	 * @throws NotFoundException
	 * @throws UnexpectedException
	 */
	public function deleteSection(string $iCloudCalendarId): void
	{
		$this->request(
			HttpClient::HTTP_DELETE,
			$this->serverPath . $iCloudCalendarId,
		);

		if (!$this->isDeleteRequestSuccess())
		{
			$this->processErrors('Section was not deleted');
		}
	}

	/**
	 * @throws AccessDeniedException
	 * @throws BadRequestException
	 * @throws DtoValidationException
	 * @throws NotAuthorizedException
	 * @throws NotFoundException
	 * @throws UnexpectedException
	 */
	public function getSections(): CalendarListResponse
	{
		$this->client->setHeader('Depth', 1);
		$this->client->setHeader('Content-type', 'text/xml');

		$uri = $this->serverPath . $this->pathToCalendars;
		$data = $this->requestDataBuilder->buildGetSectionsData();

		$this->request(
			$this->davMethodProvider->getPropfindMethod(),
			$uri,
			$data,
		);

		if (!$this->isDavRequestSuccess())
		{
			$this->processErrors('Sections list was not retrieved from iCloud');
		}

		return CalendarListResponse::fromXml($this->client->getResult());
	}

	/**
	 * @throws AccessDeniedException
	 * @throws BadRequestException
	 * @throws DtoValidationException
	 * @throws NotAuthorizedException
	 * @throws NotFoundException
	 * @throws UnexpectedException
	 */
	private function getSection(string $uri): CalendarResponse
	{
		$this->client->setHeader('Depth', 1);
		$this->client->setHeader('Content-type', 'text/xml');

		$data = $this->requestDataBuilder->buildGetSectionData();

		$this->request(
			$this->davMethodProvider->getPropfindMethod(),
			$uri,
			$data,
		);

		if (!$this->isDavRequestSuccess())
		{
			$this->processErrors('Section was not received');
		}

		return CalendarResponse::fromXml($this->client->getResult());
	}
}
