<?php
namespace Bitrix\Scale;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentOutOfRangeException;
use \Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/**
 * Class ActionsData
 * @package Bitrix\Scale
 */
class ActionsData
{
	protected static $logLevel = Logger::LOG_LEVEL_INFO;

	/**
	 * @param $actionId
	 * @return array Action's parameters
	 * @throws \Bitrix\Main\ArgumentNullException
	 */
	public static function getAction($actionId)
	{
		if($actionId == '')
			throw new \Bitrix\Main\ArgumentNullException("actionId");

		$actionsDefinitions = static::getList();

		$result = [];

		if(isset($actionsDefinitions[$actionId]))
			$result = $actionsDefinitions[$actionId];

		return $result;
	}

	/**
	 * @param string $actionId - action idetifyer
	 * @param string $serverHostname - server hostname
	 * @param array $userParams - params filled by user
	 * @param array $freeParams - params filled somewere in code
	 * @param array $actionParams - acrion parameters
	 * @return Action|ActionsChain|bool
	 * @throws \Bitrix\Main\ArgumentTypeException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Exception
	 */
	public static function getActionObject(
		$actionId,
		$serverHostname = "",
		array $userParams = [],
		array $freeParams = [],
		array $actionParams = []
	)
	{
		if($actionId == '')
			throw new \Bitrix\Main\ArgumentNullException("actionId");

		if(!is_array($userParams))
			throw new \Bitrix\Main\ArgumentTypeException("userParams", "array");

		if(!is_array($userParams))
			throw new \Bitrix\Main\ArgumentTypeException("freeParams", "array");

		if(!is_array($actionParams))
			throw new \Bitrix\Main\ArgumentTypeException("actionParams", "array");

		$action = false;

		if(!isset($actionParams["TYPE"]) || $actionParams["TYPE"] !== "MODIFYED")
			$actionParams = static::getAction($actionId);

		if(empty($actionParams))
			throw new \Exception("Can't find params of action ".$actionId);

		if(isset($actionParams["TYPE"]) && $actionParams["TYPE"] === "CHAIN")
			$action =  new ActionsChain($actionId, $actionParams, $serverHostname, $userParams, $freeParams);
		else if(!empty($actionParams))
			$action =  new Action($actionId, $actionParams, $serverHostname, $userParams, $freeParams);

		return $action;
	}

	/**
	 * Returns action state
	 * @param string $bid -     action bitrix idetifyer
	 * @return array
	 */
	public static function getActionState($bid)
	{
		$result = [];
		$shellAdapter = new ShellAdapter();
		$execRes = $shellAdapter->syncExec("sudo -u root /opt/webdir/bin/bx-process -a status -t ".$bid." -o json");
		$data = $shellAdapter->getLastOutput();

		if($execRes)
		{
			$arData = json_decode($data, true);

			if(isset($arData["params"][$bid]))
			{
				$result = $arData["params"][$bid];
			}

			if($result["status"] === "finished")
			{
				Logger::addRecord(
					Logger::LOG_LEVEL_INFO,
					"SCALE_ACTION_CHECK_STATE",
					$bid,
					Loc::getMessage("SCALE_ACTIONSDATA_ACTION_FINISHED")
				);
			}
			elseif($result["status"] === "error")
			{
				Logger::addRecord(
					Logger::LOG_LEVEL_ERROR,
					"SCALE_ACTION_CHECK_STATE",
					$bid,
					Loc::getMessage("SCALE_ACTIONSDATA_ACTION_ERROR")
				);
			}

			if(self::$logLevel >= Logger::LOG_LEVEL_DEBUG)
			{
				Logger::addRecord(Logger::LOG_LEVEL_DEBUG, "SCALE_ACTION_CHECK_STATE", $bid, $data);
			}
		}

		return $result;
	}

	/**
	 * Returns actions list
	 * @param bool $checkConditions - if we need to check conditions
	 * @return array of all actions defenitions
	 * @throws \Bitrix\Main\IO\FileNotFoundException
	 */
	public static function getList($checkConditions = false)
	{
		static $def = null;

		if($def == null)
		{
			$filename = \Bitrix\Main\Application::getDocumentRoot()."/bitrix/modules/scale/include/actionsdefinitions.php";
			$file = new \Bitrix\Main\IO\File($filename);
			$actionsDefinitions = [];

			if($file->isExists())
				require_once($filename);
			else
				throw new \Bitrix\Main\IO\FileNotFoundException($filename);

			if(isset($actionsDefinitions))
			{
				$def = $actionsDefinitions;

				if(is_array($def) && $checkConditions)
				{
					foreach($def as $actionId => $action)
					{
						if(isset($action["CONDITION"]) && !self::isConditionSatisfied($action["CONDITION"]))
						{
							unset($def[$actionId]);
						}
					}
				}

				if(getenv('BITRIX_ENV_TYPE') === 'crm')
				{
					unset(
						$def['MONITORING_ENABLE'],
						$def['SITE_CREATE'],
						$def['SITE_CREATE_LINK'],
						$def['SITE_CREATE_KERNEL'],
						$def['SITE_DEL'],
						$def['MEMCACHED_ADD_ROLE'],
						$def['MEMCACHED_DEL_ROLE'],
						$def['SPHINX_ADD_ROLE'],
						$def['PUSH_DEL_ROLE']
					);
				}
			}
			else
			{
				$def = [];
			}
		}

		return $def;
	}

	/**
	 * @param array $condition
	 * @return bool
	 */
	protected static function isConditionSatisfied($condition): bool
	{
		$result = true;

		if(!isset($condition["COMMAND"], $condition["PARAMS"]) || !is_array($condition["PARAMS"]))
		{
			return true;
		}

		if(!isset($condition["PARAMS"][0], $condition["PARAMS"][1], $condition["PARAMS"][2]))
		{
			return true;
		}

		$actRes = static::getConditionActionResult($condition["COMMAND"]);

		if(isset($actRes["condition"]["OUTPUT"]["DATA"]["params"]))
		{
			$conditionValue = static::extractConditionValue(
				$condition["PARAMS"][0],
				$actRes["condition"]["OUTPUT"]["DATA"]["params"]
			);

			if($conditionValue)
			{
				$result = static::checkCondition(
					$conditionValue,
					$condition["PARAMS"][1],
					$condition["PARAMS"][2]
				);
			}
		}

		return $result;
	}

	/**
	 * @param string $paramName
	 * @param array $paramsValues
	 * @return string|null
	 */
	protected static function extractConditionValue(string $paramName, array $paramsValues): ?string
	{
		$result = null;
		$params = explode(":", $paramName);

		if(!is_array($params) || count($params) !== 2)
		{
			throw new ArgumentException('paramName must be like paramSection:paramName');
		}

		if(isset($paramsValues[$params[0]][$params[1]]))
		{
			$result = (string)$paramsValues[$params[0]][$params[1]];
		}

		return $result;
	}

	/**
	 * @param string $command
	 * @return array
	 */
	protected static function getConditionActionResult(string $command): array
	{
		$result = [];

		try
		{
			$action =  new Action("condition", [
					"START_COMMAND_TEMPLATE" => $command,
					"LOG_LEVEL" => Logger::LOG_LEVEL_DISABLE
				], "", []
			);

			if($action->start())
			{
				$result = $action->getResult();
			}
		}
		catch(\Exception $excpt)
		{}

		return $result;
	}

	/**
	 * For data defined in  \actionsDefinitions
	 * @param string $operand1 [CONDITION][PARAMS][0] The real value is obtained from system
	 * @param string $operator [CONDITION][PARAMS][1] For now it's only "==="
	 * @param string $operand2 [CONDITION][PARAMS][2]
	 * @return bool
	 * @throws ArgumentOutOfRangeException
	 */
	protected static function checkCondition(string $operand1, string $operator, string $operand2): bool
	{
		$allowedOperators = ['==='];

		if(!in_array($operator, $allowedOperators))
		{
			throw new ArgumentOutOfRangeException('This "operator" is not allowed');
		};

		$allowedOperandRegex = '/^[0-9a-zA-Z_:\-\'\"]+$/i';

		if(!preg_match($allowedOperandRegex, $operand1))
		{
			return false;
		}

		if(!preg_match($allowedOperandRegex, $operand2))
		{
			throw new ArgumentOutOfRangeException('This "operand2" is wrong');
		}

		return eval("return ('{$operand1}' {$operator} '{$operand2}');");
	}

	/**
	 * @param int $logLevel
	 */
	public static function setLogLevel($logLevel)
	{
		self::$logLevel = $logLevel;
	}

	/**
	 * Checks if some action is running
	 * after page refresh, or then smb. else come to page
	 * during the action running.
	 * @return array - Action params
	 */
	public static function checkRunningAction()
	{
		$result = [];
		$shellAdapter = new ShellAdapter();
		$execRes = $shellAdapter->syncExec("sudo -u root /opt/webdir/bin/bx-process -a list -o json");
		$data = $shellAdapter->getLastOutput();

		if($execRes)
		{
			$arData = json_decode($data, true);
			$result = [];

			if(isset($arData["params"]) && is_array($arData["params"]))
			{
				foreach($arData["params"] as $bid => $actionParams)
				{
					if(mb_strpos($bid, 'common_') === 0) // || strpos($bid, 'monitor_') === 0)
						continue;

					if($actionParams["status"] === "running")
					{
						$result = [$bid => $actionParams];
						break;
					}
				}
			}
		}

		return $result;
	}
}