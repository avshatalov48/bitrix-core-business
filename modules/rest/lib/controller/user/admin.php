<?php

namespace Bitrix\Rest\Controller\User;

use Bitrix\Intranet\ActionFilter\UserType;
use Bitrix\Intranet\UserTable;
use Bitrix\Main\Engine\ActionFilter\Scope;
use Bitrix\Main\Engine\Controller;


class Admin extends Controller
{
	public function listAction()
	{
		return UserTable::query()
			->setSelect(['ID','NAME','LAST_NAME','SECOND_NAME'])
			->where('GROUPS.GROUP_ID', 1)
			->exec()
			->fetchAll();
	}

	public function configureActions(): array
	{
		$configureActions = [];
		$configureActions['list'] = [
			'+prefilters' => [
				new Scope(Scope::REST),
			],
		];

		return $configureActions;
	}
}
