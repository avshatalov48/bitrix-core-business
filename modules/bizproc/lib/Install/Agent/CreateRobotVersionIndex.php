<?php

namespace Bitrix\Bizproc\Install\Agent;

use Bitrix\Main\Loader;

use Bitrix\Bizproc\Internal\Service\LatestRobots\RobotVersionIndexer;

final class CreateRobotVersionIndex
{
	public static function run(): string
	{
		if (!Loader::includeModule('bizproc'))
		{
			return '';
		}

		$robotVersionIndexer = new RobotVersionIndexer();
		$robotVersionIndexer->ensureFreshIndex();

		return '';
	}
}