<?php

namespace Bitrix\Bizproc\Controller;

use Bitrix\Bizproc\Internal\Service\LatestRobots\LatestRobotService;

class Robot extends Base
{
	public function saveViewedRobotsAction(array $viewedRobotIds = []): void
	{
		if (empty($viewedRobotIds))
		{
			return;
		}

		(new LatestRobotService())->upsertViewedNewRobotIds($viewedRobotIds);
	}
}
