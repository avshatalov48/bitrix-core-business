<?php

namespace Bitrix\Calendar\Sync\Icloud;

use Bitrix\Calendar\Sync\Entities\SyncSection;
use Bitrix\Calendar\Sync\Managers\IncomingEventManagerInterface;
use Bitrix\Calendar\Sync\Managers\IncomingSectionManagerInterface;
use Bitrix\Calendar\Sync\Util\Result;

class IncomingManager implements IncomingSectionManagerInterface, IncomingEventManagerInterface
{
	/**
	 * @param SyncSection $syncSection
	 * @return Result
	 */
	public function getEvents(SyncSection $syncSection): Result
	{
		return new Result();
	}

	/**
	 * @return Result
	 */
	public function getSectionConnection(): Result
	{
		return new Result();
	}

	/**
	 * @return string|null
	 */
	public function getEtag(): ?string
	{
		return null;
	}

	/**
	 * @return string|null
	 */
	public function getSyncToken(): ?string
	{
		return null;
	}

	/**
	 * @return string|null
	 */
	public function getStatus(): ?string
	{
		return null;
	}

	/**
	 * @return string|null
	 */
	public function getPageToken(): ?string
	{
		return null;
	}

	/**
	 * @return Result
	 */
	public function getSections(): Result
	{
		return new Result();
	}

	/**
	 * @return Result
	 */
	public function getConnection(): Result
	{
		return new Result();
	}
}