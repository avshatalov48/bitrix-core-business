<?php

namespace Bitrix\Seo\Controller\Business;

use Bitrix\Main;
use Bitrix\Seo\BusinessSuite\Configuration\Facebook;
use Bitrix\Seo\BusinessSuite\Configuration\IConfig;

final class Setup extends Main\Engine\Controller
{
	/**
	 * @return IConfig
	 */
	public function defaultAction(): IConfig
	{
		try
		{
			$current = $this->loadAction();
		}
		finally
		{
			return $current ?? Facebook\Setup::default();
		}

	}

	/**
	 * @return IConfig
	 */
	public function loadAction(): IConfig
	{
		return Facebook\Setup::load();
	}
}