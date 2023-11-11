<?php

namespace Bitrix\Im\V2\Entity\User;

use Bitrix\Im\Model\BotTable;
use Bitrix\Im\V2\Entity\Command\Command;
use Bitrix\Main\Application;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\ORM\Event;
use Bitrix\Main\ORM\EventResult;

class UserBot extends User
{
	private const CACHE_PATH = '/bx/im/bot/';
	private const CACHE_TTL = 31536000;
	private const CACHE_KEY = 'cache_data_';
	private const BOT_TYPE = [
		'TYPE_HUMAN' => 'H',
		'TYPE_NETWORK' => 'N',
		'TYPE_OPENLINE' => 'O',
		'TYPE_SUPERVISOR' => 'S',
	];

	private array $commands = [];

	protected function fillOnlineData(): void
	{
		return;
	}

	public function isOnlineDataFilled(): bool
	{
		return true;
	}

	protected function checkAccessWithoutCaching(User $otherUser): bool
	{
		if (!static::$moduleManager::isModuleInstalled('intranet'))
		{
			return $this->hasAccessBySocialNetwork($otherUser->getId());
		}

		global $USER;
		if ($otherUser->isExtranet())
		{
			if ($otherUser->getId() === $USER->GetID())
			{
				if ($USER->IsAdmin())
				{
					return true;
				}

				if (static::$loader::includeModule('bitrix24'))
				{
					if (\CBitrix24::IsPortalAdmin($otherUser->getId()) || \Bitrix\Bitrix24\Integrator::isIntegrator($otherUser->getId()))
					{
						return true;
					}
				}
			}

			$inGroup = \Bitrix\Im\Integration\Socialnetwork\Extranet::isUserInGroup($this->getId(), $otherUser->getId());
			if ($inGroup)
			{
				return true;
			}


			return false;
		}

		if ($this->isNetwork())
		{
			return true;
		}

		return true;
	}

	/**
	 * @return array
	 */
	public function getCommands(): array
	{
		if (empty($this->commands))
		{
			$this->commands = (new Command($this->getId()))->toRestFormat();
		}

		return $this->commands;
	}



	public function toRestFormat(array $option = []): array
	{
		$userData = parent::toRestFormat($option);

		if (isset($userData['botData']))
		{
			return $userData;
		}

		$botId = $this->getId();
		$botData = $this->getBotData($botId);

		$userData['botData'] = $this->prepareDataForRest($botData);

		return $userData;
	}

	protected function prepareDataForRest(array $botData): array
	{
		$type = 'bot';
		$code = $botData['CODE'];

		if ($botData['TYPE'] === self::BOT_TYPE['TYPE_HUMAN'])
		{
			$type = 'human';
		}
		else if ($botData['TYPE'] === self::BOT_TYPE['TYPE_NETWORK'])
		{
			$type = 'network';

			if ($botData['CLASS'] === 'Bitrix\ImBot\Bot\Support24')
			{
				$type = 'support24';
				$code = 'network_cloud';
			}
			else if ($botData['CLASS'] === 'Bitrix\ImBot\Bot\Partner24')
			{
				$type = 'support24';
				$code = 'network_partner';
			}
			else if ($botData['CLASS'] === 'Bitrix\ImBot\Bot\SupportBox')
			{
				$type = 'support24';
				$code = 'network_box';
			}
		}
		else if ($botData['TYPE'] === self::BOT_TYPE['TYPE_OPENLINE'])
		{
			$type = 'openline';
		}
		else if ($botData['TYPE'] === self::BOT_TYPE['TYPE_SUPERVISOR'])
		{
			$type = 'supervisor';
		}

		return [
			'code' => $code,
			'type' => $type,
			'appId' => $botData['APP_ID'],
			'isSupportOpenline' => $botData['OPENLINE'] === 'Y',
		];
	}

	protected function getBotData($id): array
	{
		$cache = $this->getSavedCache($id);
		$cachedBot = $cache->getVars();

		if ($cachedBot !== false)
		{
			return $cachedBot;
		}

		$botData = $this->getBotDataFromDb($id);
		if ($botData === null)
		{
			return [];
		}

		$this->saveInCache($cache, $botData);

		return $botData;
	}

	protected function getSavedCache(int $id): Cache
	{
		$cache = Application::getInstance()->getCache();

		$cacheDir = self::getCacheDir($id);
		$cache->initCache(self::CACHE_TTL, $this->getCacheKey($id), $cacheDir);

		return $cache;
	}

	protected static function getCacheDir(int $id): string
	{
		$cacheSubDir = substr(md5(self::getCacheKey($id)),2,2);

		return self::CACHE_PATH . "{$cacheSubDir}/" . self::getCacheKey($id) . "/";
	}

	protected static function getCacheKey($id): string
	{
		return self::CACHE_KEY . $id;
	}

	protected function getBotDataFromDb(int $id): ?array
	{
		$query = BotTable::query()
			->setSelect(['*'])
			->setLimit(1)
			->where('BOT_ID', $id)
		;

		$result = $query->fetch();

		return $result ?: null;
	}

	protected function saveInCache(Cache $cache, array $userData): void
	{
		$cache->startDataCache();
		$cache->endDataCache($userData);
	}

	public static function cleanCache(int $id): void
	{
		Application::getInstance()->getCache()->cleanDir(self::getCacheDir($id));
	}
}