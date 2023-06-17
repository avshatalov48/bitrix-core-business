<?php

namespace Bitrix\Im\V2\Entity\User;

use Bitrix\Im\Color;
use Bitrix\Im\Model\StatusTable;
use Bitrix\Im\Model\UserTable;
use Bitrix\Main\Application;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Loader;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use CVoxImplantPhone;

class UserFabric
{
	private const COMMON_SELECT_FIELD = [
		'ID',
		'LAST_NAME',
		'NAME',
		'EMAIL',
		'LOGIN',
		'PERSONAL_PHOTO',
		'SECOND_NAME',
		'PERSONAL_BIRTHDAY',
		'WORK_POSITION',
		'PERSONAL_GENDER',
		'EXTERNAL_AUTH_ID',
		'TIME_ZONE_OFFSET',
		'PERSONAL_WWW',
		'ACTIVE',
		'WORK_PHONE',
		'PERSONAL_PHONE',
		'PERSONAL_MOBILE',
		'COLOR' => 'STATUS.COLOR',
	];

	protected static self $instance;

	private function __construct()
	{
	}

	public static function getInstance(): self
	{
		if (isset(self::$instance))
		{
			return self::$instance;
		}

		self::$instance = new static();

		return self::$instance;
	}

	public function getUserById(int $id): User
	{
		$cache = $this->getCache($id);
		$cachedUser = $cache->getVars();
		if ($cachedUser !== false)
		{
			$userData = $this->prepareUserData($cachedUser);

			return $this->initUser($userData);
		}

		$userData = $this->getUserFromDb($id);

		if ($userData === null)
		{
			return new NullUser();
		}

		$this->saveInCache($cache, $userData);
		$userData = $this->prepareUserData($userData);

		return $this->initUser($userData);
	}

	public function initUser(array $userData): User
	{
		if ($userData['IS_BOT'])
		{
			return UserBot::initByArray($userData);
		}
		if ($userData['IS_EXTRANET'])
		{
			return UserExtranet::initByArray($userData);
		}
		if ($this->isExternal($userData))
		{
			return UserExternal::initByArray($userData);
		}

		return User::initByArray($userData);
	}

	protected function prepareUserData(array $userData): array
	{
		$avatar = \CIMChat::GetAvatarImage($userData['PERSONAL_PHOTO']) ?: '';

		$userData['COLOR'] = $this->getColor($userData);
		$userData['NAME'] = \Bitrix\Im\User::formatFullNameFromDatabase($userData);
		$userData['FIRST_NAME'] = \Bitrix\Im\User::formatNameFromDatabase($userData);
		$userData['BIRTHDAY'] =
			$userData['PERSONAL_BIRTHDAY'] instanceof \Bitrix\Main\Type\Date
				? $userData['PERSONAL_BIRTHDAY']->format('d-m')
				: false
		;
		$userData['AVATAR'] = $avatar !== '/bitrix/js/im/images/blank.gif' ? $avatar : '';
		$userData['AVATAR_HR'] = $avatar;
		$userData['AVATAR_ID'] = (int)$userData['PERSONAL_PHOTO'];
		$userData['IS_EXTRANET'] = $this->isExtranet($userData);
		$userData['IS_NETWORK'] = $this->isNetwork($userData);
		$userData['IS_BOT'] = $this->isBot($userData);
		$userData['IS_CONNECTOR'] = $this->isConnector($userData);
		$userData['ABSENT'] = \CIMContactList::formatAbsentResult((int)$userData['ID']) ?: null;

		if (Loader::includeModule('voximplant'))
		{
			$userData['WORK_PHONE'] = CVoxImplantPhone::Normalize($userData['WORK_PHONE']) ?: null;
			$userData['PERSONAL_MOBILE'] = CVoxImplantPhone::Normalize($userData['PERSONAL_MOBILE']) ?: null;
			$userData['PERSONAL_PHONE'] = CVoxImplantPhone::Normalize($userData['PERSONAL_PHONE']) ?: null;
		}

		return $userData;
	}

	protected function getUserFromDb(int $id): ?array
	{
		$query = UserTable::query()
			->setSelect(self::COMMON_SELECT_FIELD)
			->setLimit(1)
			->where('ID', $id)
			->registerRuntimeField(
				'STATUS',
				new Reference(
					'STATUS',
					StatusTable::class,
					Join::on('this.ID', 'ref.USER_ID'),
					['join_type' => Join::TYPE_LEFT]
				)
			)
		;

		if (Loader::includeModule('intranet'))
		{
			$query->addSelect('UF_DEPARTMENT');
		}

		return $query->fetch() ?: null;
	}

	protected function isExtranet(array $params): bool
	{
		return \CIMContactList::IsExtranet($params);
	}

	protected function isNetwork(array $params): bool
	{
		$bots = \Bitrix\Im\Bot::getListCache();
		$isNetworkUser = $params['EXTERNAL_AUTH_ID'] === \CIMContactList::NETWORK_AUTH_ID;
		$isNetworkBot = (
			$this->isBot($params)
			&& $bots[$params["ID"]]['TYPE'] === \Bitrix\Im\Bot::TYPE_NETWORK
		);

		return $isNetworkUser || $isNetworkBot;
	}

	protected function isBot(array $params): bool
	{
		return $params['EXTERNAL_AUTH_ID'] === \Bitrix\Im\Bot::EXTERNAL_AUTH_ID;
	}

	protected function isConnector(array $params): bool
	{
		return $params['EXTERNAL_AUTH_ID'] === 'imconnector';
	}

	protected function getColor(array $userData): string
	{
		return $userData['COLOR']
			? Color::getColor($userData['COLOR'])
			: $this->getColorByUserIdAndGender((int)$userData['ID'], $userData['PERSONAL_GENDER'] === 'M'? 'M': 'F');
	}

	protected function getColorByUserIdAndGender(int $id, string $gender): string
	{
		$code = Color::getCodeByNumber($id);
		if ($gender === 'M')
		{
			$replaceColor = Color::getReplaceColors();
			if (isset($replaceColor[$code]))
			{
				$code = $replaceColor[$code];
			}
		}

		return Color::getColor($code);
	}

	protected function isExternal(array $params): bool
	{
		return in_array($params['EXTERNAL_AUTH_ID'], UserTable::filterExternalUserTypes(['bot']), true);
	}

	//region Cache

	protected function getCache(int $id): Cache
	{
		$cache = Application::getInstance()->getCache();

		$cacheTTL = defined("BX_COMP_MANAGED_CACHE") ? 18144000 : 1800;
		$cacheId = "user_data_{$id}";
		$cacheDir = $this->getCacheDir($id);

		$cache->initCache($cacheTTL, $cacheId, $cacheDir);

		return $cache;
	}

	protected function saveInCache(Cache $cache, array $userData): void
	{
		$taggedCache = Application::getInstance()->getTaggedCache();
		$id = (int)$userData['ID'];
		$cache->startDataCache();
		$taggedCache->startTagCache($this->getCacheDir($id));
		$taggedCache->registerTag("USER_NAME_{$id}");
		$taggedCache->endTagCache();
		$cache->endDataCache($userData);
	}

	private function getCacheDir(int $id): string
	{
		$cacheSubDir = $id % 100;
		$cacheSubSubDir = ($id % 10000) / 100;

		return "/bx/imc/userdata_v2/{$cacheSubDir}/{$cacheSubSubDir}/{$id}";
	}

	//endregion
}