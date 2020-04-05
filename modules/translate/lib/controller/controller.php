<?php
namespace Bitrix\Translate\Controller;

use Bitrix\Main;

const STATUS_COMPLETED = 'COMPLETED';
const STATUS_PROGRESS = 'PROGRESS';

/**
 * @const SETTING_ID
 */
abstract class Controller extends Main\Engine\Controller
{
	/**
	 * Common operations before process action.
	 * @param Main\Engine\Action $action Action.
	 * @return bool If method will return false, then action will not execute.
	 */
	protected function processBeforeAction(Main\Engine\Action $action)
	{
		if (parent::processBeforeAction($action))
		{
			if (!Main\Loader::includeModule('translate'))
			{
				$this->addError(new Main\Error('Translate module not installed'));
			}

			return count($this->getErrors()) === 0;
		}

		return true;
	}

	/**
	 * Checks if error occurred.
	 *
	 * @return boolean
	 */
	public function hasErrors()
	{
		if (!$this->errorCollection instanceof Main\ErrorCollection)
		{
			return false;
		}

		return !$this->errorCollection->isEmpty();
	}
}
