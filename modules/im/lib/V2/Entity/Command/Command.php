<?php

namespace Bitrix\Im\V2\Entity\Command;

use Bitrix\Im\Model\CommandTable;
use Bitrix\Im\V2\Rest\RestConvertible;
use Bitrix\Main\Application;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class Command implements RestConvertible
{
	protected const CACHE_PATH = '/bx/im/command/';
	protected const CACHE_TTL = 31536000;
	protected const CACHE_KEY = 'cache_command_bot_';
	protected const REST_NAME = 'commandList';

	protected bool $loadRestLang;
	protected ?int $botId;
	protected ?string $lang = LANGUAGE_ID;

	public function __construct($botId)
	{
		$this->botId = $botId;
	}

	protected function getBotId(): ?int
	{
		return isset($this->botId) ? (int)$this->botId : null;
	}

	public function getCommandList($id): array
	{
		$cache = $this->getSavedCache($id);
		$cachedCommandList = $cache->getVars();

		if ($cachedCommandList !== false)
		{
			return $cachedCommandList;
		}

		$commandList = $this->getCommandListFromDb($id);
		if (empty($commandList))
		{
			return [];
		}

		$this->saveInCache($cache, $commandList);

		return $commandList;
	}

	protected function getSavedCache(int $id): Cache
	{
		$cache = Application::getInstance()->getCache();

		$cacheDir = $this->getCacheDir($id);
		$cache->initCache(self::CACHE_TTL, $this->getCacheKey($id), $cacheDir);

		return $cache;
	}

	protected function getCacheDir(int $id): string
	{
		$cacheSubDir = substr(md5($id),2,2);

		return self::CACHE_PATH . "{$cacheSubDir}/" . self::CACHE_KEY . $id . "/{$this->getCacheKey($id)}/";
	}

	protected static function getDeleteDir(int $id): string
	{
		$cacheSubDir = substr(md5($id),2,2);

		return self::CACHE_PATH . "{$cacheSubDir}/" . self::CACHE_KEY . $id . "/";
	}

	protected function getCacheKey($id): string
	{
		return $this->lang . '_' . $id;
	}

	protected function getCommandListFromDb(int $id): ?array
	{
		$result = [];

		$query =CommandTable::query()
			->setSelect(['*'])
			->where('BOT_ID', $id)
			->exec()
		;

		while ($row = $query->fetch())
		{
			$result[$row['ID']] = $row;
		}


		return $result;
	}

	protected function saveInCache(Cache $cache, array $commandList): void
	{
		$cache->startDataCache();
		$cache->endDataCache($commandList);
	}

	public static function cleanCache(int $id): void
	{
		Application::getInstance()->getCache()->cleanDir(self::getDeleteDir($id));
	}

	public static function getRestEntityName(): string
	{
		return self::REST_NAME;
	}

	public function toRestFormat(array $option = []): array
	{
		$this->lang = $option['langId'] ?? LANGUAGE_ID;
		$commandList = $this->getCommandList($this->getBotId());
		$commandList = $this->prepareData($commandList);
		$commandList = $this->mergeWithDefaultCommands($commandList);

		$result = [];
		foreach ($commandList as $command)
		{
			$result[] = [
				'id' => is_numeric($command['ID']) ? (int)$command['ID'] : $command['ID'],
				'botId' => (int)$command['BOT_ID'],
				'command' => '/'. $command['COMMAND'],
				'category' => $command['CATEGORY'],
				'common' => $command['COMMON'],
				'context' => $command['CONTEXT'],
				'title' => $command['TITLE'],
				'params' => $command['PARAMS'],
				'extranet' => $command['EXTRANET_SUPPORT'],
			];
		}

		return $result;
	}

	protected function prepareData(array $commandList): array
	{
		$this->loadRestLang = false;
		$result = [];

		foreach ($commandList as $command)
		{
			$command['COMMAND_ID'] = $command['ID'];
			$command['CONTEXT'] = '';

			if ($command['BOT_ID'] > 0)
			{
				$command['CATEGORY'] = \Bitrix\Im\User::getInstance($command['BOT_ID'])->getFullName();
			}
			else if ($command['MODULE_ID'] == 'im')
			{
				$command['CATEGORY'] = Loc::getMessage('COMMAND_IM_CATEGORY');
			}
			else
			{
				$module = (new \CModule())->createModuleObject($command['MODULE_ID']);
				$command['CATEGORY'] = $module->MODULE_NAME;
			}

			if (!empty($command['CLASS']) && !empty($command['METHOD_LANG_GET']))
			{
				$command = $this->setModuleParams($command, $this->lang);
			}
			else
			{
				$command = $this->setModuleRestParams($command);
			}

			$result[(int)$command['ID']] = $command;
		}

		if ($this->loadRestLang)
		{
			$result = $this->commandRestLang($result);
		}

		$this->sortCommandData($result);

		return $result;
	}

	protected function setModuleParams(array $command): array
	{
		if (\Bitrix\Main\Loader::includeModule($command['MODULE_ID'])
			&& class_exists($command["CLASS"])
			&& method_exists($command["CLASS"], $command["METHOD_LANG_GET"]))
		{
			$localize = call_user_func_array(
				[$command["CLASS"], $command["METHOD_LANG_GET"]],
				[$command['COMMAND'], $this->lang]
			);

			if ($localize)
			{
				$command['TITLE'] = $localize['TITLE'];
				$command['PARAMS'] = $localize['PARAMS'];
			}
			else
			{
				$command['HIDDEN'] = 'Y';
				$command['METHOD_LANG_GET'] = '';
			}
		}
		else
		{
			$command['HIDDEN'] = 'Y';
			$command['METHOD_LANG_GET'] = '';
		}

		return $command;
	}
	protected function setModuleRestParams(array $command): array
	{
		$command['TITLE'] = '';
		$command['PARAMS'] = '';

		if ($command['MODULE_ID'] === 'rest')
		{
			$this->loadRestLang = true;

			if ($command['BOT_ID'] <= 0 && $command['APP_ID'])
			{
				$res = \Bitrix\Rest\AppTable::getList([
					'filter' => array('=CLIENT_ID' => $command['APP_ID']),
				]);

				if ($app = $res->fetch())
				{
					$command['CATEGORY'] = !empty($app['APP_NAME'])
						? $app['APP_NAME']
						: (!empty($app['APP_NAME_DEFAULT'])
							? $app['APP_NAME_DEFAULT']
							: $app['CODE']
						)
					;
				}
			}
		}

		return $command;
	}

	protected function commandRestLang(array $result): array
	{
		$langSet = [];
		$orm = \Bitrix\Im\Model\CommandLangTable::getList();
		while ($commandLang = $orm->fetch())
		{
			if (!isset($result[$commandLang['ID']]))
			{
				continue;
			}

			$langSet[$commandLang['ID']][$commandLang['LANGUAGE_ID']]['TITLE'] = $commandLang['TITLE'];
			$langSet[$commandLang['ID']][$commandLang['LANGUAGE_ID']]['PARAMS'] = $commandLang['PARAMS'];
		}

		$langAlter = \Bitrix\Im\Bot::getDefaultLanguage();
		foreach ($result as $commandId => $commandData)
		{
			if (isset($langSet[$commandId][$this->lang]))
			{
				$result[$commandId]['TITLE'] = $langSet[$commandId][$this->lang]['TITLE'];
				$result[$commandId]['PARAMS'] = $langSet[$commandId][$this->lang]['PARAMS'];
			}
			else if (isset($langSet[$commandId][$langAlter]))
			{
				$result[$commandId]['TITLE'] = $langSet[$commandId][$langAlter]['TITLE'];
				$result[$commandId]['PARAMS'] = $langSet[$commandId][$langAlter]['PARAMS'];
			}
			else if (isset($langSet[$commandId]))
			{
				$langSetCommand = array_values($langSet[$commandId]);
				$result[$commandId]['TITLE'] = $langSetCommand[0]['TITLE'];
				$result[$commandId]['PARAMS'] = $langSetCommand[0]['PARAMS'];
			}
		}

		foreach ($result as $key => $value)
		{
			if (empty($value['TITLE']))
			{
				$result[$key]['HIDDEN'] = 'Y';
				$commandLang['METHOD_LANG_GET'] = '';
			}
		}

		return $result;
	}

	protected function sortCommandData(array $result): array
	{
		if (!empty($result))
		{
			\Bitrix\Main\Type\Collection::sortByColumn(
				$result,
				Array('MODULE_ID' => SORT_ASC),
				'',
				null,
				true
			);
		}

		return $result;
	}

	protected function mergeWithDefaultCommands($commandList): array
	{
		$defaultCommands = [
			[
				'COMMAND' => 'me',
				'TITLE' => Loc::getMessage("COMMAND_DEF_ME_TITLE"),
				'PARAMS' => Loc::getMessage("COMMAND_DEF_ME_PARAMS"),
				'HIDDEN' => 'N',
				'EXTRANET_SUPPORT' => 'Y',
			],
			[
				'COMMAND' => 'loud',
				'TITLE' => Loc::getMessage("COMMAND_DEF_LOUD_TITLE"),
				'PARAMS' => Loc::getMessage("COMMAND_DEF_LOUD_PARAMS"),
				'HIDDEN' => 'N',
				'EXTRANET_SUPPORT' => 'Y',
			],
			[
				'COMMAND' => '>>',
				'TITLE' => Loc::getMessage("COMMAND_DEF_QUOTE_TITLE"),
				'PARAMS' => Loc::getMessage("COMMAND_DEF_QUOTE_PARAMS"),
				'HIDDEN' => 'N',
				'EXTRANET_SUPPORT' => 'Y',
			],
			[
				'COMMAND' => 'rename',
				'TITLE' => Loc::getMessage("COMMAND_DEF_RENAME_TITLE"),
				'PARAMS' => Loc::getMessage("COMMAND_DEF_RENAME_PARAMS"),
				'HIDDEN' => 'N',
				'EXTRANET_SUPPORT' => 'Y',
				'CATEGORY' => Loc::getMessage("COMMAND_DEF_CATEGORY_CHAT"),
				'CONTEXT' => 'chat',
			],
			[
				'COMMAND' => 'getDialogId',
				'TITLE' => Loc::getMessage("COMMAND_DEF_DIALOGID_TITLE"),
				'HIDDEN' => 'N',
				'EXTRANET_SUPPORT' => 'N',
				'CATEGORY' => Loc::getMessage("COMMAND_DEF_CATEGORY_CHAT")
			],
			[
				'COMMAND' => 'webrtcDebug',
				'TITLE' => Loc::getMessage("COMMAND_DEF_WD_TITLE"),
				'HIDDEN' => 'N',
				'EXTRANET_SUPPORT' => 'Y',
				'CATEGORY' => Loc::getMessage("COMMAND_DEF_CATEGORY_DEBUG"),
				'CONTEXT' => 'call'
			],
		];

		$imCommands = Array();
		foreach ($defaultCommands as $i => $command)
		{
			$newCommand['ID'] = 'def'.$i;
			$newCommand['BOT_ID'] = 0;
			$newCommand['APP_ID'] = '';
			$newCommand['COMMAND'] = $command['COMMAND'];
			$newCommand['HIDDEN'] = $command['HIDDEN'] ?? 'N';
			$newCommand['COMMON'] = 'Y';
			$newCommand['EXTRANET_SUPPORT'] = $command['EXTRANET_SUPPORT'] ?? 'N';
			$newCommand['SONET_SUPPORT'] = $command['SONET_SUPPORT'] ?? 'N';
			$newCommand['CLASS'] = '';
			$newCommand['METHOD_COMMAND_ADD'] = '';
			$newCommand['METHOD_LANG_GET'] = '';
			if (!$command['TITLE'])
			{
				$newCommand['HIDDEN'] = 'Y';
			}
			$newCommand['MODULE_ID'] = 'im';
			$newCommand['COMMAND_ID'] = $newCommand['ID'];
			$newCommand['CATEGORY'] = $command['CATEGORY'] ?? Loc::getMessage('COMMAND_IM_CATEGORY');
			$newCommand['CONTEXT'] = $command['CONTEXT'] ?? '';
			$newCommand['TITLE'] = $command['TITLE'] ?? '';
			$newCommand['PARAMS'] = $command['PARAMS'] ?? '';

			$imCommands[$newCommand['COMMAND_ID']] = $newCommand;
		}

		$result = $imCommands;
		if (is_array($commandList))
		{
			foreach ($commandList as $key => $command)
			{
				$result[$key] = $command;
			}
		}

		return $result;
	}
}