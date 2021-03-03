<?php
namespace Bitrix\Seo\Controller\Business;

use Bitrix\Main;
use Bitrix\Seo\BusinessSuite\Configuration\Facebook;
use Bitrix\Seo\BusinessSuite\Configuration\IConfig;

final class Config extends Main\Engine\Controller
{
	public function defaultAction(): IConfig
	{
		try
		{
			$current = $this->loadAction();
		}
		finally
		{
			return $current ?? Facebook\Config::default();
		}
	}
	public function loadAction(): IConfig
	{
		return Facebook\Config::load();
	}
	public function saveAction(array $config)
	{
		return Facebook\Config::loadFromArray($config)->save();
	}
}