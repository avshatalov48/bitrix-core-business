<?php

namespace Bitrix\Forum\Comments\Service;

use Bitrix\Main\Loader;
use Bitrix\Main\Web\Json;
use Bitrix\Socialnetwork\CommentAux;

final class TaskCreated extends EntityCreated
{
	const TYPE = 'CREATETASK';

	protected function getSocnetType(): string
	{
		return CommentAux\CreateTask::TYPE;
	}
}