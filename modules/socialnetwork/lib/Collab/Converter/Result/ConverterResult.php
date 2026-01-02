<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Converter\Result;

use Bitrix\Main\Result;
use Bitrix\Socialnetwork\Item\Workgroup;

class ConverterResult extends Result
{
	private Workgroup $entityAfter;

	public function setEntityAfter(Workgroup $entityAfter): self
	{
		$this->entityAfter = $entityAfter;

		return $this;
	}

	public function getEntityAfter(): Workgroup
	{
		return $this->entityAfter;
	}
}
