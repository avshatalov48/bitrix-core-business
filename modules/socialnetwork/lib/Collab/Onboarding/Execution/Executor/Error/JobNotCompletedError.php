<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Onboarding\Execution\Executor\Error;

use Bitrix\Main\Error;

class JobNotCompletedError extends Error
{
	public function __construct()
	{
		parent::__construct('Job not completed');
	}
}
