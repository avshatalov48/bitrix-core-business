<?php

namespace Bitrix\Im\V2\Chat\EntityLink;

use Bitrix\Im\V2\Chat\EntityLink;
use Bitrix\Main\Loader;

class TasksType extends EntityLink
{
	protected const HAS_URL = true;

	protected function getUrl(): string
	{
		if (!Loader::includeModule('tasks'))
		{
			return '';
		}

		$url = \CTasksTools::GetOptionPathTaskUserEntry(SITE_ID, "/company/personal/user/#user_id#/tasks/task/view/#task_id#/");
		$url = str_replace(['#user_id#', '#task_id#'], [$this->getContext()->getUserId(), $this->entityId], mb_strtolower($url));

		return $url;
	}
}