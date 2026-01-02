<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Internal\Integration\Socialservices\Auth;

use Bitrix\Calendar\Synchronization\Internal\Exception\Exception;
use Bitrix\Calendar\Synchronization\Internal\Exception\Repository\RepositoryReadException;
use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;
use Bitrix\Socialservices\UserTable;

class AbstractAuthService
{
	/**
	 * @throws RepositoryReadException
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	protected function getUserTokens(int $userId, string $authService): ?array
	{
		/** @noinspection PhpUnhandledExceptionInspection */
		if (!Loader::includeModule('socialservices'))
		{
			return null;
		}

		try
		{
			$filter = [
				'USER_ID' => $userId,
				'EXTERNAL_AUTH_ID' => $authService,
			];

			$this->checkConsistency($filter);

			$select =  ['USER_ID', 'EXTERNAL_AUTH_ID', 'OATOKEN', 'OATOKEN_EXPIRES', 'REFRESH_TOKEN'];

			$tokens = UserTable::getList(['filter' => $filter, 'select' => $select])->fetch();
		}
		catch (\Exception $e)
		{
			throw new RepositoryReadException(
				sprintf('Unable to get user %d tokes: "%s"', $userId, $e->getMessage()),
				$e->getCode(),
				$e
			);
		}

		return $tokens ?: null;
	}

	/**
	 * @throws Exception
	 * @throws SystemException
	 */
	private function checkConsistency(array $filter): void
	{
		$count = UserTable::getCount($filter);

		if ($count > 1)
		{
			$this->removeUserRecords($filter);

			throw new Exception(
				sprintf(
					'User "%s" has more than one tokens for "%s"',
					$filter['USER_ID'],
					$filter['EXTERNAL_AUTH_ID']
				)
			);
		}
	}

	/**
	 * @throws SystemException
	 * @throws \Exception
	 */
	private function removeUserRecords(array $filter): void
	{
		$dbRecords = UserTable::getList(
			[
				'filter' => $filter,
				'select' => ['ID']
			]
		);

		while ($record = $dbRecords->fetch())
		{
			UserTable::delete($record['ID']);
		}
	}
}
