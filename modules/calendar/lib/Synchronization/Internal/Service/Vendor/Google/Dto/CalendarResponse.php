<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Google\Dto;

use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Common\Dto\CalendarResponseInterface;
use Bitrix\Calendar\Synchronization\Internal\Exception\DtoValidationException;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Web\Json;

/**
 * @link https://developers.google.com/calendar/api/v3/reference/calendars#resource
 */
class CalendarResponse implements CalendarResponseInterface
{
	public function __construct(private readonly string $id, private readonly string $etag)
	{
	}

	/**
	 * @param string $jsonResponse
	 *
	 * @return self
	 *
	 * @throws DtoValidationException
	 */
	public static function fromJson(string $jsonResponse): self
	{
		try
		{
			$data = Json::decode($jsonResponse);
		}
		catch (ArgumentException $e)
		{
			throw new DtoValidationException(
				sprintf('Wrong JSON from Google: "%s"', $e->getMessage()),
				previous: $e
			);
		}

		$required = ['id', 'etag', 'kind'];

		foreach ($required as $key)
		{
			if (empty($data[$key]))
			{
				throw new DtoValidationException(sprintf('Field "%s" is required in CalendarResponse DTO', $key));
			}
		}

		if ($data['kind'] !== 'calendar#calendar')
		{
			throw new DtoValidationException('Data type should be "calendar#calendar"');
		}

		return new self($data['id'], $data['etag']);
	}

	public function getId(): string
	{
		return $this->id;
	}

	public function getEtag(): string
	{
		return $this->etag;
	}
}
