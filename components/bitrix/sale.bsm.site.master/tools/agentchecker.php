<?php

namespace Bitrix\Sale\BsmSiteMaster\Tools;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main,
	Bitrix\Main\Config\Option,
	Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class AgentChecker
 * @package Bitrix\Sale\BsmSiteMaster\Tools
 */
class AgentChecker
{
	const ERROR_CODE_FAIL = "Fail";
	const ERROR_CODE_WARNING = "Warning";

	/**
	 * @return Main\Result
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public function checkAgents()
	{
		$result = new Main\Result();

		if (defined('BX_CRONTAB'))
		{
			$result->addError(new Main\Error(Loc::getMessage("SALE_BSM_WIZARD_AGENTCHECKER_BX_CRONTAB_DEFINED"), self::ERROR_CODE_FAIL));
			return $result;
		}

		$isCron = Option::get("main", "agents_use_crontab", "N") == 'Y'
			|| (defined('BX_CRONTAB_SUPPORT') && BX_CRONTAB_SUPPORT === true)
			|| Option::get("main", "check_agents", "Y") != 'Y';

		if ($isCron)
		{
			/** @noinspection PhpVariableNamingConventionInspection */
			global $DB;
			if (!$DB->Query('SELECT LAST_EXEC FROM b_agent WHERE LAST_EXEC > NOW() - INTERVAL 1 DAY AND IS_PERIOD = "N" LIMIT 1')->Fetch())
			{
				$result->addError(new Main\Error(Loc::getMessage("SALE_BSM_WIZARD_AGENTCHECKER_CRON_NO_START"), self::ERROR_CODE_FAIL));
				return $result;
			}

			return $result;
		}

		$result->addError(new Main\Error(Loc::getMessage("SALE_BSM_WIZARD_AGENTCHECKER_AGENTS_HITS"), self::ERROR_CODE_WARNING));
		return $result;
	}
}