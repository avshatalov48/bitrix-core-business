<?php

namespace Bitrix\Calendar\Sync\Managers;

use Bitrix\Calendar\Sync\Connection\SectionConnection;
use Bitrix\Calendar\Sync\Entities\SyncEventMap;
use Bitrix\Calendar\Sync\Entities\SyncSection;
use Bitrix\Calendar\Sync\Exceptions\NotFoundException;
use Bitrix\Calendar\Sync\Util\Result;

interface IncomingEventManagerInterface
{
	/**
	 * 'data' => [
	 * 		'externalSyncEventMap' => Bitrix\Calendar\Sync\Entities\SyncEventMap,
	 * ]
	 * @throws NotFoundException
	 */
	public function getEvents(SyncSection $syncSection): Result;

	/**
	 * 'data' => [
	 * 		'sectionConnection' => Bitrix\Calendar\Sync\Connection\SectionConnection,
	 * ]
	 */
	public function getSectionConnection(): Result;

	public function getEtag(): ?string;

	public function getSyncToken(): ?string;

	public function getStatus(): ?string;

	public function getPageToken(): ?string;
}
