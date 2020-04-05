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

//		find active updaters
		foreach (self::getUpdaterClasses() as $classId)
		{
			if (Option::get('main.stepper.' . $moduleId, $classId, '') !== '')
			{
				$updatersToShow[] = $classId;
			}
		}

//		show active updaters
		if (!empty($updatersToShow))
		{
			echo '<div style="padding-bottom: 20px;">';
			echo \Bitrix\Main\Update\Stepper::getHtml(array($moduleId => $updatersToShow));
			echo '</div>';
		}
	}
}