<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Integration\Socialservices\Auth;

use Bitrix\Calendar\Internal\Integration\Socialservices\Auth\AbstractAuthService;
use Bitrix\Calendar\Synchronization\Internal\Exception\LogicException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Repository\RepositoryReadException;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Loader;
use CGoogleOAuthInterface;

class GoogleAuthHelper extends AbstractAuthService
{
	/**
	 * @param int $userId
	 *
	 * @return CGoogleOAuthInterface
	 *
	 * @throws RepositoryReadException
	 */
	public static function getUserAuthEntity(int $userId): CGoogleOAuthInterface
	{
		/** @noinspection PhpUnhandledExceptionInspection */
		if (!Loader::includeModule('socialservices'))
		{
			throw new LogicException('The module "socialservices" did not loaded but user is authorized');
		}

		/** @noinspection PhpUnhandledExceptionInspection */
		if (\CSocServGoogleProxyOAuth::isProxyAuth())
		{
			$auth = new \CSocServGoogleProxyOAuth($userId);
		}
		else
		{
			$auth = new \CSocServGoogleOAuth($userId);
		}

		$authEntity = $auth->getEntityOAuth();
		$authEntity->addScope([
			'https://www.googleapis.com/auth/calendar',
			'https://www.googleapis.com/auth/calendar.readonly'
		]);
		$authEntity->removeScope('https://www.googleapis.com/auth/drive');

		$authEntity->setUser($userId);

		$tokens = self::getStoredTokens($userId);

		if ($tokens)
		{
			$authEntity->setToken($tokens['OATOKEN']);
			$authEntity->setAccessTokenExpires($tokens['OATOKEN_EXPIRES']);
			$authEntity->setRefreshToken($tokens['REFRESH_TOKEN']);
		}

		if (!$authEntity->checkAccessToken())
		{
			$authEntity->getNewAccessToken(
				$authEntity->getRefreshToken(),
				$userId,
				true,
			);
		}

		return $authEntity;
	}

	/**
	 * @throws RepositoryReadException
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public static function getStoredTokens(int $userId): ?array
	{
		/** @noinspection PhpUnhandledExceptionInspection */
		return ServiceLocator::getInstance()
			->get(GoogleAuthHelper::class)
			->getUserTokens($userId, 'GoogleOAuth')
		;
	}
}
