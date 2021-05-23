<?php

namespace Bitrix\Seo\BusinessSuite\Configuration\Facebook;

use Bitrix\Main;
use Bitrix\Main\Application;
use Bitrix\Seo\BusinessSuite\Service;
use Bitrix\Seo\BusinessSuite\ServiceAdapter;
use Bitrix\Seo\BusinessSuite\AuthAdapter\IAuthSettings;

final class Installs implements IAuthSettings
{
	private const FACEBOOK_INSTALLS_TTL = 840000;
	public const FACEBOOK_INSTALLS_CACHE_ID = 'facebook|business|installs';

	/** @var Installs $current*/
	private static $current;

	/**@var array $value*/
	private $value;

	private function __construct()
	{}

	public static function load() : ?self
	{
		if(!self::$current)
		{
			self::$current = new static();
			$cache = Application::getInstance()->getManagedCache();
			if ($cache->read(self::FACEBOOK_INSTALLS_TTL,self::FACEBOOK_INSTALLS_CACHE_ID))
			{
				self::$current->value = $cache->get(self::FACEBOOK_INSTALLS_CACHE_ID);
			}
			elseif
			(
				($adapter = ServiceAdapter::loadFacebookService())
				&& ($response = $adapter->getExtension()->getInstalls())
				&& $response->isSuccess()
			)
			{
				$cache->set(self::FACEBOOK_INSTALLS_CACHE_ID, self::$current->value = $response->fetch());
			}
		}

		return self::$current;
	}

	public function getPixel()
	{
		return $this->value['PIXEL_ID'];
	}
	public function getPages()
	{
		return $this->value['PAGES'];
	}
	public function getInstagramProfiles()
	{
		return $this->value['INSTAGRAM_PROFILES'];
	}
	public function getBusinessManager()
	{
		return $this->value['BUSINESS_MANAGER_ID'];
	}
	public function getAdAccount()
	{
		return $this->value['AD_ACCOUNT_ID'];
	}
	public function getCatalog()
	{
		return $this->value['CATALOG_ID'];
	}

	public static function clearCache()
	{
		Application::getInstance()->getManagedCache()->clean(self::FACEBOOK_INSTALLS_CACHE_ID);
		self::$current = null;
	}

	public function toArray(): array
	{
		return array_filter([
				'business_manager_id' => $this->getBusinessManager(),
				'ad_account_id' => $this->getAdAccount(),
				'pixel_id' => $this->getPixel(),
				'catalog_id' => $this->getCatalog(),
				'page_id' => current($this->getPages() ?? []),
				'ig_profile_id' => current($this->getInstagramProfiles() ?? [])
			]);
	}
}