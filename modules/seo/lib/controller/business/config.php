<?php
namespace Bitrix\Seo\Controller\Business;

use Bitrix\Main;
use Bitrix\Seo\BusinessSuite\Configuration\Facebook;
use Bitrix\Seo\BusinessSuite\Configuration\IConfig;
use Bitrix\Seo\BusinessSuite\Exception\ConfigLoadException;
use Bitrix\Seo\BusinessSuite\Exception\UnknownFieldException;

final class Config extends Main\Engine\Controller
{
	/**
	 * Load or get default facebook config
	 * @return IConfig
	 * @throws ConfigLoadException
	 * @throws Main\SystemException
	 * @throws UnknownFieldException
	 */
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

	/**
	 * Load facebook config action
	 * @return IConfig
	 * @throws Main\SystemException
	 * @throws ConfigLoadException
	 */
	public function loadAction(): IConfig
	{
		return Facebook\Config::load();
	}

	/**
	 * Save facebook config action
	 * @param array $config
	 *
	 * @return bool
	 */
	public function saveAction(array $config)
	{
		return Facebook\Config::loadFromArray($config)->save();
	}
}