<?php

namespace Bitrix\Rest\Engine;

use Bitrix\Main\Config\Configuration;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Application;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Data\Cache;
use CRestProvider;
use CRestUtil;

/**
 * Class Scope
 * @package Bitrix\Rest\Engine
 */
class ScopeManager
{
	public const CACHE_TIME = 604800;// 86400 * 7
	public const CACHE_DIR = '/rest/scope/';
	private const CACHE_KEY = 'list';
	private const METHOD_DELIMITER = '.';

	/** @var ScopeManager|null  */
	private static $instance;
	private $scopeList;
	private $methodInfoList = [];

	private function __construct()
	{
		$this->load();
	}

	/**
	 * @return ScopeManager
	 */
	public static function getInstance() : ScopeManager
	{
		if (self::$instance === null)
		{
			self::$instance = new ScopeManager();
		}

		return self::$instance;
	}

	private function load() : bool
	{
		$this->scopeList = [];
		$cache = Cache::createInstance();
		if ($cache->initCache(self::CACHE_TIME, self::CACHE_KEY, self::CACHE_DIR))
		{
			$this->scopeList = $cache->getVars();
		}
		elseif ($cache->startDataCache())
		{
			$provider = new CRestProvider();
			$scopeList = $provider->getDescription();
			foreach ($scopeList as $code => $value)
			{
				$this->scopeList[$code] = $code;
			}

			$installedModuleList = ModuleManager::getInstalledModules();
			foreach ($installedModuleList as $moduleId => $moduleDescription)
			{
				if (!isset($description[$moduleId]))
				{
					$controllersConfig = Configuration::getInstance($moduleId);

					if (!empty($controllersConfig['controllers']['restIntegration']['enabled']))
					{
						if (!$controllersConfig['controllers']['restIntegration']['hideModuleScope'])
						{
							$this->scopeList[$moduleId] = $moduleId;
						}

						if (is_array($controllersConfig['controllers']['restIntegration']['scopes']))
						{
							$this->scopeList = array_merge(
								$this->scopeList,
								array_fill_keys(
									$controllersConfig['controllers']['restIntegration']['scopes'],
									$moduleId
								)
							);
						}
					}
				}
			}

			unset($this->scopeList[CRestUtil::GLOBAL_SCOPE]);

			$cache->endDataCache($this->scopeList);
		}

		return true;
	}

	public function reset() : bool
	{
		$this->methodInfoList = [];
		$this->load();

		return true;
	}

	public static function cleanCache() : bool
	{
		return Cache::clearCache(true, self::CACHE_DIR);
	}

	public function getAlias($code) : ?string
	{
		return $this->scopeList[$code];
	}

	public function listScope() : array
	{
		return array_keys($this->scopeList);
	}

	public function getAliasList() : array
	{
		return array_unique(array_values($this->scopeList));
	}

	public function getList() : array
	{
		$langScope = Application::getDocumentRoot() . BX_ROOT . '/modules/rest/scope.php';
		Loc::loadMessages($langScope);
		$result = [];
		foreach ($this->listScope() as $code)
		{
			$key = mb_strtoupper($code);
			$name = Loc::getMessage('REST_SCOPE_' . $key);
			$result[$code] = [
				'code' => $code,
				'title' => ($name) ? $name . ' (' . $code . ')' : $code,
				'description' => Loc::getMessage('REST_SCOPE_' . $key . '_DESCRIPTION')
			];
		}

		return $result;
	}

	public function getMethodInfo(?string $method) : array
	{
		if (!$this->methodInfoList[$method])
		{
			$scope = '';
			$module = '';
			$scopeFind = '';
			$actionParts = explode(self::METHOD_DELIMITER, $method);

			foreach ($actionParts as $partScope)
			{
				$scopeFind .= ($scopeFind !== '' ? self::METHOD_DELIMITER : '') . $partScope;
				$moduleFind = $this->getAlias($scopeFind);
				if ($moduleFind)
				{
					$module = $moduleFind;
					$scope = $scopeFind;
				}
			}

			if (!$scope || !$module)
			{
				$scope = array_shift($actionParts);
				$module = $scope;
			}
			elseif ($module !== $scope)
			{
				$method = $module . self::METHOD_DELIMITER . $method;
			}

			$this->methodInfoList[$method] = [
				'moduleId' => $module,
				'scope' => $scope,
				'method' => $method,
			];
		}

		return $this->methodInfoList[$method];
	}

	public static function onChangeRegisterModule() : void
	{
		static::cleanCache();
	}
}
