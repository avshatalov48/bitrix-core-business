<?php

namespace Bitrix\Socialnetwork\Controller;

use Bitrix\Intranet\ActionFilter;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Loader;

class Base extends Controller
{
	protected int $userId;

	protected function getDefaultPreFilters(): array
	{
		$preFilters = parent::getDefaultPreFilters();

		if (Loader::includeModule('intranet'))
		{
			$preFilters[] = new ActionFilter\UserType([
				'employee',
				'extranet',
				'email',
				'replica',
			]);
		}

		return $preFilters;
	}

	protected function init(): void
	{
		parent::init();
		$this->userId = CurrentUser::get()->getId();
	}
}