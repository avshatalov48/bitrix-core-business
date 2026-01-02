<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Internal\Integration\Socialservices\Auth;

use Bitrix\Calendar\Sync\Office365\Helper;
use Bitrix\Calendar\Synchronization\Internal\Exception\Repository\RepositoryReadException;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use COffice365OAuthInterface;
use CSocServOffice365OAuth;

class Office365AuthService extends AbstractAuthService
{
	/**
	 * @param int $userId
	 *
	 * @return COffice365OAuthInterface
	 *
	 * @throws LoaderException
	 * @throws RepositoryReadException
	 */
	public function prepareAuthEntity(int $userId): COffice365OAuthInterface
	{
		Loader::requireModule('socialservices');

		$oauth = new CSocServOffice365OAuth($userId);

		$authEntity = $oauth->getEntityOAuth();
		$authEntity->addScope(Helper::NEED_SCOPE);
		$authEntity->setUser($userId);

		$token = $this->getStorageToken($userId);

		if ($token)
		{
			$authEntity->setToken($token['OATOKEN']);
			$authEntity->setAccessTokenExpires($token['OATOKEN_EXPIRES']);
			$authEntity->setRefreshToken($token['REFRESH_TOKEN']);
		}

		if (!$authEntity->checkAccessToken())
		{
			$authEntity->getNewAccessToken(
				$authEntity->getRefreshToken(),
				$userId,
				true
			);
		}

		return $authEntity;
	}

	/**
	 * @throws RepositoryReadException
	 */
	private function getStorageToken(int $userId): ?array
	{
		return $this->getUserTokens($userId, 'Office365');
	}
}
