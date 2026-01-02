<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Integration\Dav\Service;

use Bitrix\Calendar\Integration\Pull\PushCommand;
use Bitrix\Calendar\Internals\PushTable;
use Bitrix\Calendar\Internals\SectionConnectionTable;
use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Sync\Google\Helper;
use Bitrix\Calendar\Sync\Util\Result;
use Bitrix\Calendar\Util;
use Bitrix\Dav\Internals\DavConnectionTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;

class ConnectionManager
{
	public function deactivateConnection(Connection $connection): Result
	{
		$result = new Result();

		if (!Loader::includeModule('dav'))
		{
			$result->addError(new Error('Module dav required'));
		}

		$updateResult = DavConnectionTable::update(
			$connection->getId(),
			[
				'IS_DELETED' => 'Y',
				'SYNC_TOKEN' => null,
			]
		);

		if ($updateResult->isSuccess())
		{
			$this->unsubscribeConnection($connection);

			$accountType = $connection->getAccountType() === Helper::GOOGLE_ACCOUNT_TYPE_API
				? 'google'
				: $connection->getAccountType();

			Util::addPullEvent(
				PushCommand::DeleteSyncConnection,
				$connection->getOwner()->getId(),
				[
					'syncInfo' => [
						$accountType => [
							'type' => $accountType,
						],
					],
					'connectionId' => $connection->getId()
				]
			);
		}
		else
		{
			$result->addErrors($updateResult->getErrors());
		}

		return $result;
	}

	/**
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 *
	 * @TODO: move it into PushManager
	 */
	private function unsubscribeConnection(Connection $connection): void
	{
		$links = SectionConnectionTable::query()
			->addFilter('CONNECTION_ID', $connection->getId())
			->setSelect(['ID'])
			->exec()
		;

		while ($link = $links->fetchObject())
		{
			PushTable::delete([
				'ENTITY_TYPE' => 'SECTION_CONNECTION',
				'ENTITY_ID' => $link->getId(),
			]);
		}
	}
}
