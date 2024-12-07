<?php

namespace Bitrix\Bizproc\Task\Result;

use Bitrix\Main\Result;

class DelegateTasksResult extends Result
{
	public function getSuccessDelegateTaskMessage(): ?string
	{
		if (isset($this->data['successMessage']) && is_string($this->data['successMessage']))
		{
			return $this->data['successMessage'];
		}

		return null;
	}
}