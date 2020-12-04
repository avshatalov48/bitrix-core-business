<?php

namespace Bitrix\Main\Engine\Contract;

use Bitrix\Main\Engine\FallbackAction;

interface FallbackActionInterface
{
	public const ACTION_NAME = FallbackAction::ACTION_NAME;

	/**
	 * @param string $actionName
	 * @return mixed
	 */
	public function fallbackAction($actionName);
}