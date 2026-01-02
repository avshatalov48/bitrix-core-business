<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365\Processor\Event;

use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Sync\Office365\Converter\Converter;
use Bitrix\Calendar\Sync\Office365\Dto\EventDto;
use Bitrix\Calendar\Sync\Office365\Office365Context;
use Bitrix\Calendar\Synchronization\Internal\Entity\SectionConnection;
use Bitrix\Calendar\Synchronization\Internal\Exception\DtoConvertException;
use Bitrix\Main\SystemException;
use Psr\Container\NotFoundExceptionInterface;

class DtoConverter
{
	/**
	 * @throws DtoConvertException
	 */
	public function convertEvent(EventDto $eventDto, SectionConnection $sectionConnection): Event
	{
		try
		{
			$converter = new Converter(Office365Context::getConnectionContext($sectionConnection->getConnection()));

			return $converter->convertEvent($eventDto, $sectionConnection->getSection());
		}
		catch (SystemException|NotFoundExceptionInterface $e)
		{
			throw new DtoConvertException('Unable to convert an event DTO: ' . $e->getMessage(), $e->getCode(), $e);
		}
	}
}
