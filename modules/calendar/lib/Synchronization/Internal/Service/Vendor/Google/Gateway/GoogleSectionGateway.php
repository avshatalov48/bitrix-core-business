<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Google\Gateway;

use Bitrix\Calendar\Core\Section\Section;
use Bitrix\Calendar\Sync\Google\SectionConverter;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\AccessDeniedException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\Google\RateLimitExceededException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\NotFoundException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\UnexpectedException;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Common\Gateway\SectionGatewayInterface;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\ConflictException;
use Bitrix\Calendar\Synchronization\Internal\Exception\DtoValidationException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\Google\SyncTokenNotValidException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\NotAuthorizedException;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Google\Dto\CalendarListResponse;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Google\Dto\CalendarResponse;
use Bitrix\Main\Web\HttpClient;

class GoogleSectionGateway extends AbstractGoogleGateway implements SectionGatewayInterface
{
	/**
	 * @throws ConflictException
	 * @throws DtoValidationException
	 * @throws NotAuthorizedException
	 * @throws NotFoundException
	 * @throws RateLimitExceededException
	 * @throws UnexpectedException
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public function createSection(Section $section): CalendarResponse
	{
		$this->request(
			HttpClient::HTTP_POST,
			self::BASE_PATH . '/calendars',
			$this->encode((new SectionConverter($section))->convertForEdit())
		);

		if ($this->isRequestSuccess())
		{
			$response = CalendarResponse::fromJson($this->client->getResult());
			$this->updateSectionColor($response->getId(), $section->getColor());

			return $response;
		}

		/** @noinspection PhpUnhandledExceptionInspection */
		$this->processErrors('Section was not created');
	}

	/**
	 * @throws AccessDeniedException
	 * @throws DtoValidationException
	 * @throws NotAuthorizedException
	 * @throws NotFoundException
	 * @throws RateLimitExceededException
	 * @throws UnexpectedException
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public function updateSection(Section $section, string $googleCalendarId): CalendarResponse
	{
		$this->request(
			HttpClient::HTTP_PUT,
			self::BASE_PATH . '/calendars/' . urlencode($googleCalendarId),
			$this->encode((new SectionConverter($section))->convertForEdit())
		);

		if ($this->isRequestSuccess())
		{
			$response = CalendarResponse::fromJson($this->client->getResult());
			$this->updateSectionColor($response->getId(), $section->getColor());

			return $response;
		}

		/** @noinspection PhpUnhandledExceptionInspection */
		$this->processErrors('Section was not updated');
	}

	/**
	 * @throws AccessDeniedException
	 * @throws NotAuthorizedException
	 * @throws RateLimitExceededException
	 * @throws UnexpectedException
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public function deleteSection(string $vendorSectionId): void
	{
		$this->request(
			HttpClient::HTTP_DELETE,
			self::BASE_PATH . '/calendars/' . urlencode($vendorSectionId)
		);

		if ($this->isDeleteRequestSuccess())
		{
			return;
		}

		/** @noinspection PhpUnhandledExceptionInspection */
		$this->processErrors('Section was not deleted');
	}

	/**
	 * @throws DtoValidationException
	 * @throws NotAuthorizedException
	 * @throws NotFoundException
	 * @throws RateLimitExceededException
	 * @throws SyncTokenNotValidException
	 * @throws UnexpectedException
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public function getSections(?string $token = null): CalendarListResponse
	{
		$requestParams = '';

		if ($token)
		{
			$requestParams = '?' . http_build_query([
					'showDeleted' => 'true',
					'showHidden' => 'true',
					'syncToken' => $token,
				]);

			$requestParams = preg_replace('/(%3D)/', '=', $requestParams);
		}

		$this->request(
			HttpClient::HTTP_GET,
			self::BASE_PATH . '/users/me/calendarList' . $requestParams
		);

		if ($this->isRequestSuccess())
		{
			return CalendarListResponse::fromJson($this->client->getResult());
		}

		/** @noinspection PhpUnhandledExceptionInspection */
		$this->processErrors('Sections list was not retrieved from Google');
	}

	/**
	 * @throws UnexpectedException
	 */
	private function updateSectionColor(string $googleCalendarId, ?string $sectionColor): void
	{
		$this->request(
			HttpClient::HTTP_PUT,
			$this->buildListUpdateUrl($googleCalendarId),
			$this->buildUpdateColorData($sectionColor)
		);
	}

	private function buildListUpdateUrl(string $googleCalendarId): string
	{
		return self::BASE_PATH . '/users/me/calendarList/' . urlencode($googleCalendarId) . '?'
			. preg_replace('/(%3D)/', '=', http_build_query(['colorRgbFormat' => 'true']));
	}

	/**
	 * @throws UnexpectedException
	 */
	private function buildUpdateColorData(?string $colorHex): ?string
	{
		$parameters = [
			'selected' => 'true'
		];

		if (is_string($colorHex))
		{
			$parameters['backgroundColor'] = $colorHex;
			$parameters['foregroundColor'] = '#ffffff';
		}

		return $this->encode($parameters);
	}
}
