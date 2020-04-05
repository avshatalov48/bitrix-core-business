<?

namespace Bitrix\Main\Grid\Panel\Snippet;

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);


/**
 * Control change actions
 * @package Bitrix\Main\Grid\Panel\Snippet
 */
class Onchange
{
	protected $actions;


	/**
	 * Onchange constructor.
	 * @param array $actions
	 */
	public function __construct($actions = array())
	{
		if (!empty($actions) && is_array($actions) && count($actions) > 0)
		{
			foreach ($actions as $key => $action)
			{
				if (isset($action["CONFIRM"]) && $action["CONFIRM"] === true)
				{
					if (!isset($action["CONFIRM_MESSAGE"]) || empty($action["CONFIRM_MESSAGE"]))
					{
						$actions[$key]["CONFIRM_MESSAGE"] = Loc::getMessage("DEFAULT_CONFIRM_MESSAGE");
					}

					if (!isset($action["CONFIRM_APPLY_BUTTON"]) || empty($action["CONFIRM_APPLY_BUTTON"]))
					{
						$actions[$key]["CONFIRM_APPLY_BUTTON"] = Loc::getMessage("CONFIRM_APPLY_BUTTON");
					}

					if (!isset($action["CONFIRM_CANCEL_BUTTON"]) || empty($action["CONFIRM_CANCEL_BUTTON"]))
					{
						$actions[$key]["CONFIRM_CANCEL_BUTTON"] = Loc::getMessage("CONFIRM_CANCEL_BUTTON");
					}
				}
			}

			$this->actions = $actions;
		}
	}


	/**
	 * Adds action
	 * @param array $action
	 */
	public function addAction($action = array())
	{
		if (isset($action["CONFIRM"]) && $action["CONFIRM"] === true)
		{
			if (!isset($action["CONFIRM_MESSAGE"]) || empty($action["CONFIRM_MESSAGE"]))
			{
				$action["CONFIRM_MESSAGE"] = Loc::getMessage("DEFAULT_CONFIRM_MESSAGE");
			}

			if (!isset($action["CONFIRM_APPLY_BUTTON"]) || empty($action["CONFIRM_APPLY_BUTTON"]))
			{
				$action["CONFIRM_APPLY_BUTTON"] = Loc::getMessage("CONFIRM_APPLY_BUTTON");
			}

			if (!isset($action["CONFIRM_CANCEL_BUTTON"]) || empty($action["CONFIRM_CANCEL_BUTTON"]))
			{
				$action["CONFIRM_CANCEL_BUTTON"] = Loc::getMessage("CONFIRM_CANCEL_BUTTON");
			}
		}

		$this->actions[] = $action;
	}


	/**
	 * @return array
	 */
	public function toArray()
	{
		return $this->actions;
	}
}