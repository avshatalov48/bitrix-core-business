<?php

namespace Bitrix\Calendar\Sharing;

use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\Web\Cookie;

final class SharingUser
{
	public const EXTERNAL_AUTH_ID = 'calendar_sharing';
	public const COOKIE_AUTH_HASH_NAME = 'CALENDAR_SHARING_HASH';
	private static ?SharingUser $instance = null;

	protected function __construct()
	{
	}

	/**
	 * @return SharingUser
	 */
	public static function getInstance(): SharingUser
	{
		if (!self::$instance)
		{
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * @param bool $needToCreateUser
	 * @param array $userParams
	 * @return null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function login(bool $needToCreateUser = false, array $userParams = [])
	{
		global $USER;

		if (
			$USER->isAuthorized()
			&& in_array($USER->GetParam('EXTERNAL_AUTH_ID'), ['calendar_sharing', null], true)
		)
		{
			if ($needToCreateUser)
			{
				$this->updateUserPersonalInfo($USER->GetID(), $userParams);
			}

			return $USER->GetID();
		}

		$user = $this->getByHash();

		if (!$user && $needToCreateUser)
		{
			$user = $this->createUser($userParams);
		}

		if ($user)
		{
			$this->saveAuthHashToCookie($user->getXmlId());

			if ($needToCreateUser)
			{
				$this->updateUserPersonalInfo($USER->GetID(), $userParams);
			}

			return $user->getId();
		}

		return null;
	}

	/**
	 * @param array $userParams
	 * @return int|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getAnonymousUserForOwner(array $userParams): ?int
	{
		$user = $this->getByHash();

		if (!$user)
		{
			$user = $this->createUser($userParams);
		}

		if ($user)
		{
			$this->saveAuthHashToCookie($user->getXmlId());

			$this->updateUserPersonalInfo($user->getId(), $userParams);

			return $user->getId();
		}

		return null;
	}

	/**
	 * @return \Bitrix\Main\EO_User|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function getByHash(): ?\Bitrix\Main\EO_User
	{
		$request = Context::getCurrent()->getRequest();
		$authHash = $request->getCookieRaw(self::COOKIE_AUTH_HASH_NAME);

		if (!($authHash && is_string($authHash)))
		{
			return null;
		}
		if (!preg_match('/^[0-9a-f]{32}$/', $authHash))
		{
			return null;
		}

		$xmlId = self::EXTERNAL_AUTH_ID . '|' . $authHash;

		return \Bitrix\Main\UserTable::query()
			->setSelect(['*'])
			->where('ACTIVE', 'Y')
			->where('EXTERNAL_AUTH_ID', self::EXTERNAL_AUTH_ID)
			->where('XML_ID', $xmlId)
			->exec()->fetchObject();
	}

	/**
	 * @param array $userParams
	 * @return \Bitrix\Main\EO_User|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 * @throws \Exception
	 */
	private function createUser(array $userParams = []): ?\Bitrix\Main\EO_User
	{
		$name = $userParams['NAME'] ?? 'Guest';
		$lastName = $userParams['LAST_NAME'] ?? '';
		$personalPhone = '';
		$personalMailbox = '';

		if (SharingEventManager::isEmailCorrect($userParams['CONTACT_DATA']))
		{
			$personalMailbox = $userParams['CONTACT_DATA'];
		}
		if (SharingEventManager::isPhoneNumberCorrect($userParams['CONTACT_DATA']))
		{
			$personalPhone = $userParams['CONTACT_DATA'];
		}

		$login = 'calendar_sharing_' . random_int(10000, 99999) . \Bitrix\Main\Security\Random::getString(8);
		$password = md5($login . '|' . random_int(10000, 99999). '|' . time());
		$xmlId = self::EXTERNAL_AUTH_ID . '|' . md5($login . $password . time() . \Bitrix\Main\Security\Random::getString(8));

		$userManager = new \CUser();
		$userId = $userManager->add([
			'NAME' => $name,
			'LAST_NAME' => $lastName,
			'LOGIN' => $login,
			'PASSWORD' => $password,
			'CONFIRM_PASSWORD' => $password,
			'EXTERNAL_AUTH_ID' => self::EXTERNAL_AUTH_ID,
			'XML_ID' => $xmlId,
			'ACTIVE' => 'Y',
			'PERSONAL_PHONE' => $personalPhone,
			'PERSONAL_MAILBOX' => $personalMailbox,
		]);

		if ($userId)
		{
			if (Loader::includeModule("socialnetwork"))
			{
				\CSocNetUserPerms::SetPerm($userId, 'message', SONET_RELATIONS_TYPE_NONE);
			}

			return \Bitrix\Main\UserTable::getById($userId)->fetchObject();
		}

		return null;
	}

	/**
	 * @param int $userId
	 * @param array $userParams
	 * @return void
	 */
	private function updateUserPersonalInfo(int $userId, array $userParams = []): void
	{
		$user = \CUser::GetByID($userId)->Fetch();

		if (($user['EXTERNAL_AUTH_ID'] ?? null) !== 'calendar_sharing')
		{
			return;
		}

		if (
			$user['NAME'] === $userParams['NAME']
			&& (
				$user['PERSONAL_PHONE'] === $userParams['CONTACT_DATA']
				|| $user['PERSONAL_MAILBOX'] === $userParams['CONTACT_DATA']
			)
		)
		{
			return;
		}

		$name = $userParams['NAME'] ?? 'Guest';
		$personalPhone = '';
		$personalMailbox = '';

		if (SharingEventManager::isEmailCorrect($userParams['CONTACT_DATA']))
		{
			$personalMailbox = $userParams['CONTACT_DATA'];
		}
		if (SharingEventManager::isPhoneNumberCorrect($userParams['CONTACT_DATA']))
		{
			$personalPhone = $userParams['CONTACT_DATA'];
		}

		$userManager = new \CUser();
		$userManager->update($userId, [
			'NAME' => $name,
			'PERSONAL_PHONE' => $personalPhone,
			'PERSONAL_MAILBOX' => $personalMailbox,
		]);
	}

	/**
	 * @param string $userXmlId
	 * @return void
	 */
	private function saveAuthHashToCookie(string $userXmlId): void
	{
		$authHash = str_replace(self::EXTERNAL_AUTH_ID . '|', '', $userXmlId);
		$cookie = new Cookie(self::COOKIE_AUTH_HASH_NAME, $authHash, null, false);
		Context::getCurrent()->getResponse()->addCookie($cookie);
	}
}
