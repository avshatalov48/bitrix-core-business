<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Common;

use Bitrix\Calendar\Core\Section\Section;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Common\Dto\CalendarResponseInterface;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Common\Gateway\SectionGatewayInterface;
use Bitrix\Calendar\Synchronization\Internal\Exception\DtoValidationException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\ConflictException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\NotAuthorizedException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\UnexpectedException;

/**
 * Some vendors deny to create calendars with the same name. In case of duplicate calendar names,
 * the vendor returns a 409 (Conflict) error code. To bypass this restriction,
 * we have to add a numeric suffix to the calendar name.
 */
trait CreateConflictedSectionTrait
{
	/**
	 * @throws DtoValidationException
	 * @throws NotAuthorizedException
	 * @throws UnexpectedException
	 */
	private function createSection(Section $section, SectionGatewayInterface $gateway): CalendarResponseInterface
	{
		$counter = 0;
		$originalName = $section->getName();

		do
		{
			try
			{
				$response = $gateway->createSection($section);

				$section->setName($originalName);

				return $response;
			}
			catch (ConflictException)
			{
				$counter++;

				$section->setName($originalName . " ($counter)");
			}
		}
		while (true);
	}
}
