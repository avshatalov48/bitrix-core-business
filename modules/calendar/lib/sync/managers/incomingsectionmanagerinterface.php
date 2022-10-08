<?php

namespace Bitrix\Calendar\Sync\Managers;

use Bitrix\Calendar\Core\Base\BaseException;
use Bitrix\Calendar\Sync\Exceptions\AuthException;
use Bitrix\Calendar\Sync\Exceptions\RemoteAccountException;
use Bitrix\Calendar\Sync\Util\Result;

/** this interface is designed for managers in services */
interface IncomingSectionManagerInterface
{
	/**
	 * 'data' => [
	 * 		'externalSyncSectionMap' => Bitrix\Calendar\Sync\Entities\SyncSectionMap,
	 * ]
	 *
	 * @return Result
	 *
	 * @throws BaseException
	 * @throws RemoteAccountException
	 * @throws AuthException
	 */
	public function getSections(): Result;

	/**
	 * 'data' => [
	 * 		'connection' => Bitrix\Calendar\Sync\Connection\Connection,
	 * ]
	 *
	 * @return Result
	 */
	public function getConnection(): Result;

	// public function getEtag(): ?string;
	//
	// public function getSyncToken(): ?string;
	//
	// public function getStatus(): ?string;
}
