<?php

namespace Bitrix\Bizproc\Debugger\Session;

use Bitrix\Bizproc\Automation\Engine\Robot;

class TemplateShards extends Entity\EO_DebuggerSessionTemplateShards
{
	const TEMPLATE_TYPE_ACTIVITIES = 1;
	const TEMPLATE_TYPE_ROBOTS = 2;

	public function getRobotData(): ?array
	{
		if ((int)$this->fillTemplateType() === static::TEMPLATE_TYPE_ROBOTS)
		{
			return $this->getShards();
		}

		return null;
	}

	public function getRobots(): ?array
	{
		$robotData = $this->getRobotData();
		$robots = null;

		if (is_array($robotData))
		{
			$robots = [];
			foreach ($robotData as $data)
			{
				$robots[] = new Robot($data);
			}
		}

		return $robots;
	}
}