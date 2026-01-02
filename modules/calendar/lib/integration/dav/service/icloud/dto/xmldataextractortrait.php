<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Integration\Dav\Service\ICloud\Dto;

use Bitrix\Calendar\Integration\Dav\Service\ICloud\ICloudCalendar;
use Bitrix\Calendar\Synchronization\Internal\Exception\DtoValidationException;
use Bitrix\Main\SystemException;
use CDavXmlNode;
use ReflectionClass;

trait XmlDataExtractorTrait
{
	abstract private static function getRequiredProperties(): array;

	abstract private static function getResourceType(): ?string;

	/**
	 * @throws DtoValidationException
	 */
	private static function getData(CDavXmlNode $response): array
	{
		self::validateResourceType($response);

		$data = [];

		self::fillData($data, $response);

		self::validateData($data);

		return $data;
	}

	/**
	 * @throws DtoValidationException
	 */
	private static function validateResourceType(CDavXmlNode $response): void
	{
		$tag = self::getResourceType();

		if ($tag === null)
		{
			return;
		}

		$type = $response->getPath("/response/propstat/prop/resourcetype/{$tag}");

		if (empty($type))
		{
			throw new DtoValidationException("Data type should be {$tag}");
		}
	}

	private static function fillData(array &$data, CDavXmlNode $response): void
	{
		self::fillHref($data, $response);

		$status = self::fillStatus($data, $response);

		if ($status !== 404)
		{
			self::fillProperties($data, $response);
		}
	}

	private static function fillHref(array &$data, CDavXmlNode $response): string
	{
		$node = $response->getPath('/response/href')[0] ?? null;

		if (empty($node))
		{
			return '';
		}

		$tag = $node->getTag();

		$content = $node->getContent();
		$href = urldecode($content);

		$data[$tag] = $href;

		return $href;
	}

	private static function fillStatus(array &$data, CDavXmlNode $response): int
	{
		$node = $response->getPath('/response/propstat/status');

		if ($node)
		{
			$status = 200;
		}
		else
		{
			$status = 404;
		}

		$data['status'] = $status;

		return $status;
	}

	private static function fillProperties(array &$data, CDavXmlNode $response): array
	{
		$properties = [];

		$nodes = $response->getPath('/response/propstat/prop/*');

		foreach ($nodes as $node)
		{
			$content = $node->getContent();

			if (empty($content))
			{
				continue;
			}

			$tag = $node->getTag();

			if ($tag === 'supported-calendar-component-set')
			{
				$type = $content[0]->getAttribute('name');

				if (!is_string($type))
				{
					continue;
				}

				$content = $type;
			}

			if ($tag === 'calendar-data')
			{
				$iCloudCalendar = new ICloudCalendar($content);

				$calendarData = $iCloudCalendar->getCalendarData();

				if (!isset($calendarData[$tag]))
				{
					continue;
				}

				$content = $calendarData[$tag];

				if (isset($calendarData['excluded-calendar-data']))
				{
					$properties['excluded-calendar-data'] = $calendarData['excluded-calendar-data'];
				}
			}

			$properties[$tag] = $content;
		}

		$data = [...$data, ...$properties];

		return $properties;
	}

	/**
	 * @throws DtoValidationException
	 */
	private static function validateData(array $data): void
	{
		foreach ($data as $tag => $property)
		{
			if ($tag === 'supported-calendar-component-set' && $property !== 'VEVENT')
			{
				throw new DtoValidationException(
					sprintf(
						'Field "%s" must be type "VEVENT" in %s DTO',
						$tag,
						self::getClassName(),
					),
				);
			}
		}

		$requiredProperties = self::getRequiredProperties();
		foreach ($requiredProperties as $requiredProperty)
		{
			if (!empty($data[$requiredProperty]))
			{
				continue;
			}

			throw new DtoValidationException(
				sprintf(
					'Field "%s" is required in %s DTO',
					$requiredProperty,
					self::getClassName(),
				),
			);
		}
	}

	private static function getClassName(): string
	{
		return (new ReflectionClass(static::class))->getShortName();
	}
}
