<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Control;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Socialnetwork\Item\Workgroup;

class GroupResult extends Result
{
	public function addApplicationError(array $ignoreCodes = []): static
	{
		global $APPLICATION;

		$applicationError = $APPLICATION->GetException();
		if ($applicationError && !in_array($applicationError->id, $ignoreCodes, true))
		{
			$this->addError(new Error($applicationError->msg, $applicationError->id));
		}

		return $this;
	}

	public function getGroup(): ?Workgroup
	{
		return $this->data['group'] ?? null;
	}

	public function setGroup(?Workgroup $group): static
	{
		$this->data['group'] = $group;

		return $this;
	}

	public function merge(Result $result): static
	{
		$this->data = array_merge($this->data, $result->getData());

		$this->addErrors($result->getErrors());

		return $this;
	}
}