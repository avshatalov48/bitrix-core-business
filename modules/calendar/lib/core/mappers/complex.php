<?php

namespace Bitrix\Calendar\Core\Mappers;

use Bitrix\Calendar\Core;
use Bitrix\Calendar\Internals\EO_Event;
use Bitrix\Calendar\Internals\EO_Section;
use Bitrix\Calendar\Sync;
use Bitrix\Dav\Internals\EO_DavConnection;

/**
 * Class will help to prepare common entities
 */
abstract class Complex extends Mapper
{
	/**
	 * @param EO_DavConnection $connectionEO
	 *
	 * @return Core\Base\EntityInterface|Sync\Connection\Connection
	 *
	 */
	protected function prepareConnection(EO_DavConnection $connectionEO): Sync\Connection\Connection
	{
		$connectionMapper = new Connection();
		return $connectionMapper->getByEntityObject($connectionEO);
	}

	/**
	 * @param EO_Section $sectionEO
	 *
	 * @return Core\Base\EntityInterface|Core\Section\Section
	 */
	protected function prepareSection(EO_Section $sectionEO): Core\Section\Section
	{
		$sectionMapper = new Section();
		return $sectionMapper->getByEntityObject($sectionEO);
	}

	/**
	 * @param EO_Event $eventEO
	 *
	 * @return Core\Base\EntityInterface|Core\Event\Event
	 */
	protected function prepareEvent(EO_Event $eventEO): Core\Event\Event
	{
		$eventMapper = new Event();
		return $eventMapper->getByEntityObject($eventEO);
	}
}
