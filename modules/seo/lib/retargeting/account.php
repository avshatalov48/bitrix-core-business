<?

namespace Bitrix\Seo\Retargeting;

use Bitrix\Main\Application;

abstract class Account extends BaseApiObject
{
	const PROFILE_INFO_CACHE_TTL = 86400;

	protected static $listRowMap = array(
		'ID' => 'ID',
		'NAME' => 'NAME',
	);

	/**
	 * Get profile data (cached)
	 * @return array
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getProfileCached()
	{
		$cache = Application::getInstance()->getManagedCache();

		$cacheId = $this->getCacheId();
		if ($cache->read(static::PROFILE_INFO_CACHE_TTL, $cacheId))
		{
			$profile = $cache->get($cacheId);
		}
		else
		{
			$profile = $this->getProfile();
			$cache->set($cacheId, $profile);
		}

		if ($profile)
		{
			$authProvider = Service::getAuthAdapter(static::TYPE_CODE);
			$authProvider->setService($this->service);
			$profile['CLIENT_ID'] = $authProvider->getClientId();
		}
		return $profile;
	}

	/**
	 * Clear profile cache
	 * @return void
	 * @throws \Bitrix\Main\SystemException
	 */
	public function clearCache()
	{
		$cacheId = $this->getCacheId();
		$cache = Application::getInstance()->getManagedCache();
		$cache->Clean($cacheId);
	}

	/**
	 * Get cache id
	 * @return string
	 */
	protected function getCacheId()
	{
		$cacheId = 'seo|account_profile|';
		$service = $this->service;
		$cacheId .= $service ? $service::getEngineCode(static::TYPE_CODE) : static::TYPE_CODE;
		if ($service instanceof \Bitrix\Seo\Retargeting\IMultiClientService)
		{
			$cacheId .= '|' . $service->getClientId();
		}
		return $cacheId;
	}

	/**
	 * @return Response
	 */
	abstract public function getList();

	/**
	 * @return Response
	 */
	abstract public function getProfile();

	public function getRegionsList()
	{
		return [];
	}

	public function checkNewAuthInfo(): bool
	{
		return false;
	}
}
