<?php

namespace Bitrix\Im\V2\Chat\EntityLink;

class CallType extends CrmType
{
	protected function __construct(string $entityData1)
	{
		parent::__construct($entityData1);
	}

	protected function extractCrmData(string $rawCrmData): void
	{
		$separatedEntityId = explode('|', $rawCrmData);
		$this->crmType = $separatedEntityId[1] ?? '';
		$this->crmId = (int)($separatedEntityId[2] ?? 0);
	}
}