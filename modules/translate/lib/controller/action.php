<?php
namespace Bitrix\Translate\Controller;

use Bitrix\Main;

/**
 * Extending of the Main\Engine\Action class.
 */
abstract class Action
	extends Main\Engine\Action
{
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
