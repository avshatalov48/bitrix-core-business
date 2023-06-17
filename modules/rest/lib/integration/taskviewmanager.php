<?php

namespace Bitrix\Rest\Integration;

use Bitrix\Main\Engine;
use Bitrix\Rest\Integration\View\Base;
use Bitrix\Rest\RestException;
use Bitrix\Tasks\Rest\Controllers;
use Bitrix\Tasks\RestView;

final class TaskViewManager extends ViewManager
{
	/**
	 * @param Engine\Controller $controller
	 * @return Base
	 * @throws RestException
	 */
	public function getView(Engine\Controller $controller)
	{
		if ($controller instanceof Controllers\ViewedGroup\Project)
		{
			return new RestView\ViewedGroup();
		}

		if ($controller instanceof Controllers\ViewedGroup\Scrum)
		{
			return new RestView\ViewedGroup();
		}

		if ($controller instanceof Controllers\ViewedGroup\User)
		{
			return new RestView\ViewedGroup();
		}

		throw new RestException('Unknown object ' . get_class($controller));
	}
}