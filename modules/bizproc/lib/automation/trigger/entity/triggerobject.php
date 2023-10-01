<?php

namespace Bitrix\Bizproc\Automation\Trigger\Entity;

class TriggerObject extends EO_Trigger
{
	public function getValues(): array
	{
		return $this->collectValues();
	}
}
