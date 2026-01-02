<?php

namespace Bitrix\Bizproc\Internal\Config;

use CUserOptions;

class Storage
{
	protected const OPTION_CATEGORY = 'bizproc';
	protected const VIEWED_NEW_ROBOT_IDS_OPTION_NAME = 'viewedNewRobotIds';

	public function getViewedNewRobotIds(): array
	{
		return array_values(CUserOptions::getOption(static::OPTION_CATEGORY, static::VIEWED_NEW_ROBOT_IDS_OPTION_NAME, []));
	}

	public function setViewedNewRobotIds(array $ids): void
	{
		CUserOptions::setOption(static::OPTION_CATEGORY, static::VIEWED_NEW_ROBOT_IDS_OPTION_NAME, $ids);
	}
}