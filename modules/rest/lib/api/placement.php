<?php
namespace Bitrix\Rest\Api;


use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Result;
use Bitrix\Rest\AccessException;
use Bitrix\Rest\AppTable;
use Bitrix\Rest\AuthTypeException;
use Bitrix\Rest\HandlerHelper;
use Bitrix\Rest\OAuth\Auth;
use Bitrix\Rest\PlacementLangTable;
use Bitrix\Rest\PlacementTable;
use Bitrix\Rest\RestException;
use Bitrix\Rest\Exceptions;
use Bitrix\Rest\Lang;
use Bitrix\Main\ArgumentTypeException;

class Placement extends \IRestService
{
	const SCOPE_PLACEMENT = 'placement';

	public static function onRestServiceBuildDescription()
	{
		return array(
			static::SCOPE_PLACEMENT => array(
				'placement.list' => array(
					'callback' => array(__CLASS__, 'getList'),
					'options' => array()
				),
				'placement.bind' => array(
					'callback' => array(__CLASS__, 'bind'),
					'options' => array()
				),
				'placement.unbind' => array(
					'callback' => array(__CLASS__, 'unbind'),
					'options' => array()
				),
				'placement.get' => array(
					'callback' => array(__CLASS__, 'get'),
					'options' => array()
				)
			),
		);
	}


	public static function getList($query, $n, \CRestServer $server)
	{
		if($server->getAuthType() !== Auth::AUTH_TYPE)
		{
			throw new AuthTypeException("Application context required");
		}

		$result = array();

		$serviceDescription = $server->getServiceDescription();

		$scopeList = array(\CRestUtil::GLOBAL_SCOPE);

		$query = array_change_key_case($query, CASE_UPPER);

		if(isset($query['SCOPE']))
		{
			if($query['SCOPE'] != '')
			{
				$scopeList = array($query['SCOPE']);
			}
		}
		elseif($query['FULL'] == true)
		{
			$scopeList = array_keys($serviceDescription);
		}
		else
		{
			$scopeList = static::getScope($server);
			$scopeList[] = \CRestUtil::GLOBAL_SCOPE;
		}

		$placementList = static::getPlacementList($server, $scopeList);

		foreach($placementList as $placement => $placementInfo)
		{
			if(!$placementInfo['private'])
			{
				$result[] = $placement;
			}
		}

		return $result;
	}

	public static function bind($params, $n, \CRestServer $server)
	{
		static::checkPermission($server);

		$params = array_change_key_case($params, CASE_UPPER);

		if(!is_string($params['PLACEMENT']))
		{
			throw new Exceptions\ArgumentTypeException('PLACEMENT', 'string');
		}

		$placement = mb_strtoupper($params['PLACEMENT']);
		$placementHandler = $params['HANDLER'];

		if($placement == '')
		{
			throw new Exceptions\ArgumentNullException("PLACEMENT");
		}

		if($placement == PlacementTable::PLACEMENT_DEFAULT)
		{
			throw new Exceptions\ArgumentNullException("Wrong value", "PLACEMENT");
		}

		if($placementHandler == '')
		{
			throw new Exceptions\ArgumentNullException("HANDLER");
		}

		$appInfo = static::getApplicationInfo($server);
		HandlerHelper::checkCallback($placementHandler, $appInfo);

		$scopeList = static::getScope($server);
		$scopeList[] = \CRestUtil::GLOBAL_SCOPE;

		$placementList = static::getPlacementList($server, $scopeList);
		$placementInfo = $placementList[$placement];

		if (is_array($placementInfo) && (!isset($placementInfo['private']) || !$placementInfo['private']))
		{
			$placementLangList = [];
			$paramsOptions = $params['OPTIONS'] ?? [];
			$placementInfoOptions = $placementInfo['options'] ?? [];

			$placementBind = array(
				'APP_ID' => $appInfo['ID'],
				'USER_ID' => (isset($params['USER_ID']) && (int)$params['USER_ID'] > 0) ? (int)$params['USER_ID'] : PlacementTable::DEFAULT_USER_ID_VALUE,
				'PLACEMENT' => $placement,
				'PLACEMENT_HANDLER' => $placementHandler,
				'OPTIONS' => static::prepareOptions($paramsOptions, $placementInfoOptions),
			);

			if (
				$placementBind['USER_ID'] !== PlacementTable::DEFAULT_USER_ID_VALUE
				&& $placementInfo['user_mode'] !== true
			)
			{
				throw new RestException(
					'User mode is not available.',
					PlacementTable::ERROR_PLACEMENT_USER_MODE
				);
			}

			$langList = Lang::listLanguage();
			$langDefault = reset($langList);

			if (empty($params['LANG_ALL']))
			{
				if (!empty($params['TITLE']))
				{
					$placementLangList[$langDefault]['TITLE'] = trim($params['TITLE']);
				}

				if (!empty($params['DESCRIPTION']))
				{
					$placementLangList[$langDefault]['DESCRIPTION'] = trim($params['DESCRIPTION']);
				}

				if (!empty($params['GROUP_NAME']))
				{
					$placementLangList[$langDefault]['GROUP_NAME'] = trim($params['GROUP_NAME']);
				}
			}
			else
			{
				$fieldList = [
					'TITLE',
					'DESCRIPTION',
					'GROUP_NAME',
				];
				foreach ($params['LANG_ALL'] as $langCode => $langItem)
				{
					foreach ($fieldList as $field)
					{
						$placementLangList[$langCode][$field] = trim($langItem[$field] ?? '');
					}
				}
			}

			$placementBind['LANG_ALL'] = $placementLangList;
			$placementBind = Lang::mergeFromLangAll($placementBind);
			unset($placementBind['LANG_ALL']);

			if ($placementInfo['max_count'] > 0)
			{
				$filter = [
					'=APP_ID' => $placementBind['APP_ID'],
					'=PLACEMENT' => $placementBind['PLACEMENT'],
				];
				if ($placementInfo['user_mode'] === true)
				{
					$filter['=USER_ID'] = [
						PlacementTable::DEFAULT_USER_ID_VALUE,
						(int)$placementBind['USER_ID'],
					];
				}

				$res = PlacementTable::getList(
					[
						'filter' => $filter,
						'select' => [
							'COUNT',
						],
						'runtime' => [
							new ExpressionField('COUNT', 'COUNT(*)'),
						]
					]
				);

				if ($result = $res->fetch())
				{
					if ($result['COUNT'] >= $placementInfo['max_count'])
					{
						throw new RestException(
							'Placement max count: ' . $placementInfo['max_count'],
							PlacementTable::ERROR_PLACEMENT_MAX_COUNT
						);
					}
				}
			}

			if (
				array_key_exists('ICON', $params)
				&& is_array($params['ICON'])
				&& $params['ICON']['fileData']
				&& ($file = \CRestUtil::saveFile($params['ICON']['fileData']))
			)
			{
				$placementBind['ICON'] = $file;
			}
			if (!empty($placementInfo['registerCallback']['callback']))
			{
				if (
					$placementInfo['registerCallback']['moduleId']
					&& Loader::includeModule($placementInfo['registerCallback']['moduleId'])
					&& is_callable($placementInfo['registerCallback']['callback'])
				)
				{
					$resultCallback = call_user_func(
						$placementInfo['registerCallback']['callback'],
						$placementBind,
						$placementInfo
					);
					if (!empty($resultCallback['error']) && !empty($resultCallback['error_description']))
					{
						return $resultCallback;
					}
				}
			}

			$lockKey = implode('|', [
				$placementBind['APP_ID'],
				$placementBind['PLACEMENT'],
				$placementBind['PLACEMENT_HANDLER']
			]);

			if (Application::getConnection()->lock($lockKey))
			{
				$result = PlacementTable::add($placementBind);
				Application::getConnection()->unlock($lockKey);
			}
			else
			{
				$result = (new Result())->addError(new Error('Process of binding the handler has already started'));
			}

			if ($result->isSuccess())
			{
				$placementId = $result->getId();
				if (empty($placementLangList))
				{
					$app = AppTable::getByClientId($placementBind['APP_ID']);
					if (!empty($app['APP_NAME']))
					{
						$placementLangList[$langDefault] = [
							'TITLE' => $app['APP_NAME']
						];
					}
				}
				foreach ($placementLangList as $langId => $data)
				{
					$data['PLACEMENT_ID'] = $placementId;
					$data['LANGUAGE_ID'] = $langId;
					$res = PlacementLangTable::add($data);
					if (!$res->isSuccess())
					{
						$errorMessage = $res->getErrorMessages();
						throw new RestException(
							'Unable to set placements language: ' . implode(', ', $errorMessage),
							RestException::ERROR_CORE
						);
					}
				}
			}
			else
			{
				$errorMessage = $result->getErrorMessages();
				throw new RestException(
					'Unable to set placement handler: ' . implode(', ', $errorMessage),
					RestException::ERROR_CORE
				);
			}

			return true;
		}

		throw new RestException(
			'Placement not found',
			PlacementTable::ERROR_PLACEMENT_NOT_FOUND
		);
	}

	private static function prepareOptions($paramsOptions = [], $placementInfoOptions = []): array
	{
		$result = [];
		if (empty($placementInfoOptions))
		{
			return $result;
		}
		$requiredOptions = self::getRequiredOptions($placementInfoOptions);
		$defaultOptions = self::getDefaultOptions($placementInfoOptions);

		if (!is_array($paramsOptions))
		{
			if (!empty($requiredOptions))
			{
				throw new ArgumentTypeException('options', 'array');
			}

			return $defaultOptions;
		}

		self::checkRequiredOptionsInParamsOptions($paramsOptions, $requiredOptions);

		foreach ($placementInfoOptions as $optionName => $optionSetting)
		{
			$optionValue = $paramsOptions[$optionName] ?? $defaultOptions[$optionName] ?? null;
			$optionType = null;

			if (!is_array($optionSetting))
			{
				$optionType = $optionSetting;
			}
			else
			{
				$optionType = $optionSetting['type'];
			}

			switch($optionType)
			{
				case 'int':
					$result[$optionName] = (int)$optionValue;

					break;
				case 'string':
					$result[$optionName] = (string)$optionValue;

					break;
				case 'array':
					if (!is_array($optionValue))
					{
						throw new ArgumentTypeException($optionName, 'array');
					}
					$result[$optionName] = self::prepareCompositeOptions($optionValue, $optionSetting);

					break;
			}
		}

		return $result;
	}

	/**
	 * handling arrays in options
	 * @param array $paramOptionData
	 * @param array $optionSetting
	 * @return array
	 * @throws ArgumentTypeException
	 */
	private static function prepareCompositeOptions(array $paramOptionData, array $optionSetting): array
	{
		$result = [];
		if (!is_array($optionSetting['typeValue']))
		{
			throw new ArgumentTypeException('typeValue', 'array');
		}

		$allowedTypes = ['integer', 'string', 'array'];
		$optionSetting['typeValue'] = str_replace('int', 'integer', $optionSetting['typeValue']);
		$optionSetting['typeValue'] = array_intersect($optionSetting['typeValue'], $allowedTypes);

		//1. check transmitted placement data
		foreach ($paramOptionData as $keyOption => $valueOption)
		{
			$typeValue = gettype($valueOption);
			//do not take arrays, they are processed as a separate entity
			if (in_array($typeValue, $optionSetting['typeValue']) && $typeValue !== 'array')
			{
				$result[$keyOption] = $valueOption;
			}
		}

		//2. check default placement setting
		foreach ($optionSetting as $keySetting => $valueSetting)
		{
			//type and typeValue - service data
			if ($keySetting === 'type' || $keySetting === 'typeValue')
			{
				continue;
			}

			$typeValueSetting = gettype($valueSetting);

			if (
				$typeValueSetting === 'array'
				&& in_array('array', $optionSetting['typeValue'])
				&& isset($valueSetting['type'])
				&& isset($valueSetting['typeValue'])
				&& isset($paramOptionData[$keySetting])
			)
			{
				$result[$keySetting] = self::prepareCompositeOptions($paramOptionData[$keySetting], $valueSetting);
			}
		}

		return $result;
	}


	/**
	 * if the option configuration has the "default" key
	 * [
	 * 	optionName => [
	 * 		"default" => defaultValue
	 * 	],
	 * ]
	 * then return array in format
	 * [
	 * 	optionName => defaultValue
	 * ]
	 *
	 * @param array $placementInfoOptions
	 * @return array
	 */
	private static function getDefaultOptions(array $placementInfoOptions): array
	{
		$result = [];

		foreach ($placementInfoOptions as $optionName => $optionSetting)
		{
			if (isset($optionSetting['default']))
			{
				$result[$optionName] = $optionSetting['default'];
			}
		}

		return $result;
	}

	/**
	 * @param array $paramsOptions
	 * @param array $requiredOptions
	 * @return void
	 * @throws ArgumentNullException
	 */
	private static function checkRequiredOptionsInParamsOptions(array $paramsOptions, array $requiredOptions): void
	{
		foreach ($requiredOptions as $requiredOption)
		{
			if (!array_key_exists($requiredOption, $paramsOptions))
			{
				throw new ArgumentNullException($requiredOption);
			}
		}
	}


	/**
	 * get a list of names of all required options
	 * @param array $placementInfoOptions
	 * @return array
	 */
	private static function getRequiredOptions(array $placementInfoOptions): array
	{
		$result = [];
		foreach ($placementInfoOptions as $optionName => $optionSettings)
		{
			if (self::isRequiredOption($optionSettings))
			{
				$result[] = $optionName;
			}
		}

		return $result;
	}

	/**
	 * @param array|string $optionSettings
	 * @return bool
	 */
	private static function isRequiredOption($optionSettings): bool
	{
		if (!isset($optionSettings['require']))
		{
			return false;
		}

		return (bool)$optionSettings['require'];
	}

	public static function unbind($params, $n, \CRestServer $server)
	{
		static::checkPermission($server);

		$params = array_change_key_case($params, CASE_UPPER);

		if (!is_string($params['PLACEMENT']))
		{
			throw new Exceptions\ArgumentTypeException('PLACEMENT', 'string');
		}

		$placement = mb_strtoupper($params['PLACEMENT']);
		$placementHandler = $params['HANDLER'];

		if ($placement == '')
		{
			throw new Exceptions\ArgumentNullException("PLACEMENT");
		}

		$cnt = 0;

		$placementList = static::getPlacementList($server);

		if (array_key_exists($placement, $placementList) && !$placementList[$placement]['private'])
		{
			$appInfo = static::getApplicationInfo($server);

			$filter = array(
				'=APP_ID' => $appInfo["ID"],
				'=PLACEMENT' => $placement,
			);

			if (array_key_exists('USER_ID', $params))
			{
				$filter['USER_ID'] = (int)$params['USER_ID'];
			}

			if($placementHandler <> '')
			{
				$filter['=PLACEMENT_HANDLER'] = $placementHandler;
			}

			$dbRes = PlacementTable::getList(array(
				'filter' => $filter
			));

			while($placementHandler = $dbRes->fetch())
			{
				$cnt++;
				$result = PlacementTable::delete($placementHandler["ID"]);
				if($result->isSuccess())
				{
					$cnt++;
				}
			}
		}

		return array('count' => $cnt);
	}


	public static function get($params, $n, \CRestServer $server)
	{
		static::checkPermission($server);

		$result = array();

		$appInfo = static::getApplicationInfo($server);

		$dbRes = PlacementTable::getList(array(
			"filter" => array(
				"=APP_ID" => $appInfo["ID"],
			),
			'order' => array(
				"ID" => "ASC",
			)
		));

		$placementList = static::getPlacementList($server);

		foreach ($dbRes->fetchCollection() as $placement)
		{
			if (
				array_key_exists($placement->getPlacement(), $placementList)
				&& !$placementList[$placement->getPlacement()]['private']
			)
			{
				$langList = [];
				$placement->fillLangAll();
				if (!is_null($placement->getLangAll()))
				{
					foreach ($placement->getLangAll() as $lang)
					{
						$langList[$lang->getLanguageId()] = [
							'TITLE' => $lang->getTitle(),
							'DESCRIPTION' => $lang->getDescription(),
							'GROUP_NAME' => $lang->getGroupName(),
						];
					}
				}
				$result[] = array(
					'placement' => $placement->getPlacement(),
					'userId' => $placement->getUserId(),
					'handler' => $placement->getPlacementHandler(),
					'options' => $placement->getOptions(),
					'title' => $placement->getTitle(),
					'description' => $placement->getComment(),
					'langAll' => $langList,
				);
			}
		}

		return $result;
	}

	protected static function checkPermission(\CRestServer $server)
	{
		if($server->getAuthType() !== Auth::AUTH_TYPE)
		{
			throw new AuthTypeException("Application context required");
		}

		if(!\CRestUtil::isAdmin())
		{
			throw new AccessException();
		}
	}

	protected static function getScope(\CRestServer $server)
	{
		$result = array();

		$authData = $server->getAuthData();

		$scopeList = explode(',', $authData['scope']);

		$serviceDescription = $server->getServiceDescription();
		foreach($scopeList as $scope)
		{
			if(array_key_exists($scope, $serviceDescription))
			{
				$result[] = $scope;
			}
		}

		return $result;
	}

	protected static function getApplicationInfo(\CRestServer $server)
	{
		if($server->getAuthType() !== Auth::AUTH_TYPE)
		{
			throw new AuthTypeException("Application context required");
		}

		return AppTable::getByClientId($server->getClientId());
	}

	protected static function getPlacementList(\CRestServer $server, $scopeList = null)
	{
		$serviceDescription = $server->getServiceDescription();

		if($scopeList === null)
		{
			$scopeList = array_keys($serviceDescription);
		}

		$result = array();

		foreach($scopeList as $scope)
		{
			if(
				isset($serviceDescription[$scope])
				&& is_array($serviceDescription[$scope][\CRestUtil::PLACEMENTS])
			)
			{
				$result = array_merge($result, $serviceDescription[$scope][\CRestUtil::PLACEMENTS]);
			}
		}

		return $result;
	}
}