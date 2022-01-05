<?php

namespace Bitrix\UI\Mobile\SeparateList;

use Exception;

class BaseController extends \Bitrix\Main\Engine\Controller
{
	protected const PREFIX = '';

	/**
	 * @return array
	 * @throws Exception
	 */
	public function configureActions(): array
	{
		throw new Exception('Need implement this method in children');
	}

	/**
	 * @return array
	 */
	public static function getActionsList(): array
	{
		$actions = [];

		foreach ((new static())->listNameActions() as $action)
		{
			$actions[$action] = static::PREFIX . '.' . $action;
		}

		return $actions;
	}
}
