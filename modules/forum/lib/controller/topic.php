<?php

namespace Bitrix\Forum\Controller;

use Bitrix\Main;
use Bitrix\Forum;

class Topic extends Main\Engine\Controller
{
	public function headAction($topicId)
	{
		if (($topic = Forum\Topic::getById($topicId)) !== null)
		{
			global $USER;
			$user = Forum\User::getById($USER->GetID());
			if ($user->canReadTopic($topic))
			{
				return $topic->getData();
			}
		}
		return null;
	}
}
