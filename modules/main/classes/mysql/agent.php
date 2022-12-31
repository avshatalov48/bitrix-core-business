<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2013 Bitrix
 */

class CAgent extends CAllAgent
{
	public static function CheckAgents()
	{
		define("START_EXEC_AGENTS_1", microtime(true));

		define("BX_CHECK_AGENT_START", true);

		//For a while agents will execute only on primary cluster group
		if((defined("NO_AGENT_CHECK") && NO_AGENT_CHECK===true) || (defined("BX_CLUSTER_GROUP") && BX_CLUSTER_GROUP !== 1))
			return null;

		$res = CAgent::ExecuteAgents();

		define("START_EXEC_AGENTS_2", microtime(true));

		return $res;
	}

	public static function ExecuteAgents()
	{
		global $DB, $CACHE_MANAGER, $pPERIOD;

		$cron = static::OnCron();

		if ($cron !== null)
		{
			$str_crontab = ($cron ? " AND IS_PERIOD='N' " : " AND IS_PERIOD='Y' ");
		}
		else
		{
			$str_crontab = "";
		}

		$saved_time = 0;
		$cache_id = "agents".$str_crontab;
		if (CACHED_b_agent !== false && $CACHE_MANAGER->Read(CACHED_b_agent, $cache_id, "agents"))
		{
			$saved_time = $CACHE_MANAGER->Get($cache_id);
			if (time() < $saved_time)
			{
				return "";
			}
		}

		$strSql = "
			SELECT 'x'
			FROM b_agent
			WHERE
				ACTIVE = 'Y'
				AND NEXT_EXEC <= now()
				AND (DATE_CHECK IS NULL OR DATE_CHECK <= now())
				".$str_crontab."
			LIMIT 1
		";

		$db_result_agents = $DB->Query($strSql);
		if ($db_result_agents->Fetch())
		{
			if(!\Bitrix\Main\Application::getConnection()->lock('agent'))
			{
				return "";
			}
		}
		else
		{
			if (CACHED_b_agent !== false)
			{
				$rs = $DB->Query("SELECT UNIX_TIMESTAMP(NEXT_EXEC)-UNIX_TIMESTAMP(NOW()) DATE_DIFF FROM b_agent WHERE ACTIVE = 'Y' ".$str_crontab." ORDER BY NEXT_EXEC LIMIT 1");
				$ar = $rs->Fetch();

				if (!$ar || $ar["DATE_DIFF"] < 0)
					$date_diff = 0;
				elseif ($ar["DATE_DIFF"] > CACHED_b_agent)
					$date_diff = CACHED_b_agent;
				else
					$date_diff = $ar["DATE_DIFF"];

				if ($saved_time > 0)
				{
					$CACHE_MANAGER->Clean($cache_id, "agents");
					$CACHE_MANAGER->Read(CACHED_b_agent, $cache_id, "agents");
				}
				$CACHE_MANAGER->Set($cache_id, intval(time()+$date_diff));
			}

			return "";
		}

		$strSql =
			"SELECT ID, NAME, AGENT_INTERVAL, IS_PERIOD, MODULE_ID, RETRY_COUNT ".
			"FROM b_agent ".
			"WHERE ACTIVE = 'Y' ".
			"	AND NEXT_EXEC <= now() ".
			"	AND (DATE_CHECK IS NULL OR DATE_CHECK <= now()) ".
			$str_crontab.
			" ORDER BY RUNNING ASC, SORT desc ";

		if ($cron !== true)
		{
			$limit = (int)COption::GetOptionString("main", "agents_limit", 100);
			if ($limit > 0)
			{
				$strSql .= 'LIMIT ' . $limit;
			}
		}

		$db_result_agents = $DB->Query($strSql);
		$ids = '';
		$agents_array = array();
		while ($db_result_agents_array = $db_result_agents->Fetch())
		{
			$agents_array[] = $db_result_agents_array;
			$ids .= ($ids <> ''? ', ':'').$db_result_agents_array["ID"];
		}
		if ($ids <> '')
		{
			$strSql = "UPDATE b_agent SET DATE_CHECK = DATE_ADD(now(), INTERVAL " . self::LOCK_TIME. " SECOND) WHERE ID IN (".$ids.")";
			$DB->Query($strSql);
		}

		\Bitrix\Main\Application::getConnection()->unlock('agent');

		/** @var callable|false $logFunction */
		$logFunction = (defined("BX_AGENTS_LOG_FUNCTION") && function_exists(BX_AGENTS_LOG_FUNCTION)? BX_AGENTS_LOG_FUNCTION : false);

		ignore_user_abort(true);
		$startTime = time();

		foreach ($agents_array as $arAgent)
		{
			if (time() - $startTime > self::LOCK_TIME - 30)
			{
				// locking time control; 30 seconds delta is for the possibly last agent
				break;
			}

			if ($logFunction)
			{
				$logFunction($arAgent, "start");
			}

			if ($arAgent["MODULE_ID"] <> '' && $arAgent["MODULE_ID"]!="main")
			{
				if (!CModule::IncludeModule($arAgent["MODULE_ID"]))
					continue;
			}

			if (
				defined('BX_AGENTS_MAX_RETRY_COUNT')
				&& $arAgent["RETRY_COUNT"] >= BX_AGENTS_MAX_RETRY_COUNT
			)
			{
				$DB->Query("UPDATE b_agent SET ACTIVE='N' WHERE ID = ".$arAgent["ID"]);
				continue;
			}

			//update the agent to the running state - if it fails it'll go to the end of the list on the next try
			$DB->Query("UPDATE b_agent SET RUNNING = 'Y', RETRY_COUNT = RETRY_COUNT+1 WHERE ID = ".$arAgent["ID"]);

			//these vars can be assigned within agent code
			$pPERIOD = $arAgent["AGENT_INTERVAL"];

			CTimeZone::Disable();

			$USER = null;
			try
			{
				$eval_result = "";
				$e = eval("\$eval_result=".$arAgent["NAME"]);
			}
			catch (Throwable $e)
			{
				CTimeZone::Enable();

				$application = \Bitrix\Main\Application::getInstance();
				$exceptionHandler = $application->getExceptionHandler();
				$exceptionHandler->writeToLog($e);

				continue;
			}

			CTimeZone::Enable();

			if ($logFunction)
			{
				$logFunction($arAgent, "finish", $eval_result, $e);
			}

			if ($e === false)
			{
				continue;
			}
			elseif ($eval_result == '')
			{
				$strSql = "DELETE FROM b_agent WHERE ID = ".$arAgent["ID"];
			}
			else
			{
				if ($logFunction && function_exists('token_get_all'))
				{
					if (count(token_get_all("<?php ".$eval_result)) < 3)
					{
						//probably there is an error in the result
						$logFunction($arAgent, "not_callable", $eval_result, $e);
					}
				}

				$strSql = "
					UPDATE b_agent SET
						NAME = '".$DB->ForSQL($eval_result)."',
						LAST_EXEC = now(),
						NEXT_EXEC = DATE_ADD(".($arAgent["IS_PERIOD"]=="Y"? "NEXT_EXEC" : "now()").", INTERVAL ".$pPERIOD." SECOND),
						DATE_CHECK = NULL,
						RUNNING = 'N',
						RETRY_COUNT = 0
					WHERE ID = ".$arAgent["ID"];
			}
			$DB->Query($strSql);
		}

		return null;
	}
}
