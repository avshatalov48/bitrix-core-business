<?php
namespace Bitrix\Landing\Update;

use \Bitrix\Main\Config\Option;

class Stepper
{
	/**
	 * list of updaters classes, then can be show in progress bar
	 * @return array
	 */
	private static function getUpdaterClasses()
	{
		return array(
			'Bitrix\Landing\Update\Block\NodeAttributes',
			'Bitrix\Landing\Update\Block\NodeImg',
			'Bitrix\Landing\Update\Block\FixSrcImg',
			'Bitrix\Landing\Update\Block\SearchContent',
			'Bitrix\Landing\Update\Block',
			'Bitrix\Landing\Update\Landing\InitApp',
			'Bitrix\Landing\Update\Landing\SearchContent',
			'Bitrix\Landing\Update\Domain\Check',
			'Bitrix\Landing\Update\Assets\WebpackClear',
		);
	}
	
	
	/**
	 * Show some stepper if need.
	 * @return void
	 */
	public static function show()
	{
		$moduleId = 'landing';
		$updatersToShow = array();

		// find active updaters
		foreach (self::getUpdaterClasses() as $className)
		{
			if (Option::get('main.stepper.' . $moduleId, $className, '') !== '')
			{
				if (self::checkAgentActivity($className))
				{
					$updatersToShow[] = $className;
				}
				// if not exist agent - something went wrong, need rollback
				else
				{
					Option::delete(
						'main.stepper.' . $moduleId,
						['name' => $className]
					);
					
					// journal
					$eventLog = new \CEventLog;
					$eventLog->Add(array(
						'SEVERITY' => $eventLog::SEVERITY_WARNING,
						'AUDIT_TYPE_ID' => 'LANDING_STEPPER',
						'MODULE_ID' => 'landing',
						'ITEM_ID' => $className,
						'DESCRIPTION' => 'Stepper is running, but agent not exist. Stepper was deleted.',
					));
				}
			}
		}

		// show active updaters
		if (!empty($updatersToShow))
		{
			echo '<div style="padding-bottom: 20px;">';
			echo \Bitrix\Main\Update\Stepper::getHtml(array(
				$moduleId => $updatersToShow
		  	));
			echo '</div>';
		}
	}

	/**
	 * Exist or not the agent?
	 * @param string $className Class name.
	 * @return bool
	 */
	public static function checkAgentActivity($className)
	{
		global $DB;
		
		$className = trim($className, '\\');
		$name = $DB->ForSql($className . '::execAgent();');
		
		$res = $DB->Query("
			SELECT ID
			FROM b_agent
			WHERE NAME = '" . $name . "' OR NAME = '\\" . $name . "'
			AND USER_ID IS NULL"
		);
		if (!($agent = $res->Fetch()))
		{
			return false;
		}
		
		return true;
	}
}