<?php

namespace Bitrix\Bizproc\Controller;

use Bitrix\Main;

class Analytics extends Base
{
	public function configureActions(): array
	{
		$configureActions = parent::configureActions();
		$configureActions['push'] = [
			'-prefilters' => [
				Main\Engine\ActionFilter\Csrf::class,
				Main\Engine\ActionFilter\Authentication::class,
			],
			'+prefilters' => [
				new Main\Engine\ActionFilter\CloseSession(),
			]
		];

		return $configureActions;
	}

	public function pushAction()
	{
		return true;
	}
}
