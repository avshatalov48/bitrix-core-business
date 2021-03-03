<?php

namespace Bitrix\Seo\Marketing;

use Bitrix\Main\Loader;
use Bitrix\Seo\Retargeting\AuthAdapter;

/**
 * Class AdsAudience.
 * @package Bitrix\Seo\Retargeting
 */
class Configurator
{
	/** @var array $errors Errors. */
	protected static $errors = array();

	/** @var bool $isQueueUsed Is queue used. */
	protected static $isQueueUsed = false;

	/**
	 * Return true if it can use.
	 *
	 * @return bool
	 */
	public static function canUse()
	{
		return Loader::includeModule('seo') && Loader::includeModule('socialservices');
	}

	/**
	 * Use queue.
	 *
	 * @return void
	 */
	public static function useQueue()
	{
		self::$isQueueUsed = true;
	}

	/**
	 * Get service.
	 *
	 * @return Service
	 */
	public static function getService()
	{
		return Service::getInstance();
	}

	public static function getRegions($type)
	{
		$account = Service::getAccount($type);
		if (!$account)
		{
			return [];
		}

		return $account->getRegionsList();
	}

	/**
	 * Get providers.
	 *
	 * @param array|null $types Types.
	 * @return array
	 */
	public static function getProviders(array $types = null)
	{
		return static::getServiceProviders($types);
	}

	/**
	 * Get errors.
	 *
	 * @return array
	 */
	public static function getErrors()
	{
		return self::$errors;
	}

	/**
	 * Reset errors.
	 *
	 * @return void
	 */
	public static function resetErrors()
	{
		self::$errors = array();
	}

	/**
	 * Return true if it has errors.
	 *
	 * @return bool
	 */
	public static function hasErrors()
	{
		return count(self::$errors) > 0;
	}

	/**
	 * Remove auth.
	 *
	 * @param string $type Type.
	 * @return void
	 */
	public static function removeAuth($type)
	{
		static::getService()->getAuthAdapter($type)->removeAuth();
	}

	/**
	 * Get service types.
	 *
	 * @return array
	 */
	public static function getServiceTypes()
	{
		if (!static::canUse())
		{
			return array();
		}

		return static::getService()->getTypes();
	}

	/**
	 * Get providers list
	 * @param array|null $types Provider types.
	 * @return array
	 * @throws \Bitrix\Main\SystemException
	 */
	protected static function getServiceProviders(array $types = null)
	{
		$typeList = static::getServiceTypes();

		$providers = array();
		foreach ($typeList as $type)
		{
			if ($types && !in_array($type, $types))
			{
				continue;
			}

			if($type === Service::TYPE_INSTAGRAM)
			{
				$type = Service::TYPE_FACEBOOK;
			}
			$service = static::getService();
			$authAdapter = $service->getAuthAdapter($type);
			$account = $service->getAccount($type);
			$canUserMultiClients = $authAdapter->canUseMultipleClients();

			$providers[$type] = array(
				'TYPE' => $type,
				'HAS_AUTH' => $authAdapter->hasAuth(),
				'AUTH_URL' => $authAdapter->getAuthUrl(),
				'PROFILE' => $authAdapter->getToken() ? $account->getProfileCached() : false,
				'IS_SUPPORT_ACCOUNT' =>  true,
				'ENGINE_CODE' => $service::getEngineCode($type)
			);
			if ($canUserMultiClients)
			{
				$providers[$type]['CLIENTS'] = static::getClientsProfiles($authAdapter);
				if (empty($providers[$type]['CLIENTS']))
				{
					$providers[$type]['HAS_AUTH'] = false;
				}
			}

			// check if no profile, then may be auth was removed in service
			if ($providers[$type]['HAS_AUTH'] && empty($providers[$type]['PROFILE']))
			{
				static::removeAuth($type);
				if (!$canUserMultiClients)
				{
					$providers[$type]['HAS_AUTH'] = false;
				}
			}
		}

		return $providers;
	}

	/**
	 * Get client profiles.
	 * @param AuthAdapter $authAdapter Auth adapter.
	 * @return array
	 */
	protected static function getClientsProfiles(AuthAdapter $authAdapter)
	{
		$type = $authAdapter->getType();
		return array_values(array_filter(array_map(function ($item) use ($type) {
			$service = new Service();
			$service->setClientId($item['proxy_client_id']);

			$authAdapter = Service::getAuthAdapter($type);
			$authAdapter->setService($service);

			$account = Service::getAccount($type);
			$account->setService($service);
			$account->getRequest()->setAuthAdapter($authAdapter);

			$profile = $account->getProfileCached();
			if ($profile)
			{
				return $profile;
			}
			else
			{
				// if no profile, then may be auth was removed in service
				$authAdapter->removeAuth();
			}
		}, $authAdapter->getAuthorizedClientsList())));
	}

	/**
	 * Get accounts.
	 *
	 * @param string $type Type.
	 * @return array
	 */
	public static function getAccounts($type)
	{
		if (!static::canUse())
		{
			return array();
		}

		$result = array();

		$account = static::getService()->getAccount($type);
		$accountsResult = $account->getList();
		if ($accountsResult->isSuccess())
		{
			while ($accountData = $accountsResult->fetch())
			{
				if ($accountData['ID'])
				{

					$result[] = array(
						'id' => $accountData['ID'],
						'name' => $accountData['NAME'] ? $accountData['NAME'] : $accountData['ID'],
						'currency' => $accountData['CURRENCY'],
					);
				}
			}
		}
		else
		{
			self::$errors = $accountsResult->getErrorMessages();
		}

		return $result;
	}

	/**
	 * Get instagram accounts.
	 *
	 * @param string $type Type.
	 * @return array
	 */
	public static function getInstagramAccounts($type)
	{
		if (!static::canUse())
		{
			return array();
		}

		$result = array();

		$account = static::getService()->getAccount($type);
		$accountsResult = $account->getInstagramList();
		if ($accountsResult->isSuccess())
		{
			while ($accountData = $accountsResult->fetch())
			{
				if ($accountData['ID'])
				{
					$result[] = array(
						'id' => $accountData['ID'],
						'name' => $accountData['NAME'] ? $accountData['NAME'] : $accountData['ID'],
						'page_id' => $accountData['PAGE_ID'] ? $accountData['PAGE_ID'] : $accountData['ID'],
						'actor_id' => $accountData['IG_ID']
					);
				}
			}
		}
		else
		{
			self::$errors = $accountsResult->getErrorMessages();
		}

		return $result;
	}

	/**
	 * Get post media list.
	 *
	 * @param string $type Type.
	 * @param $accountId
	 *
	 * @return array
	 */
	public static function getPostList($type, $params)
	{
		if (!static::canUse())
		{
			return array();
		}

		return static::getService()->getPostList($type, $params);
	}

	/**
	 * Get audience list.
	 *
	 * @param string $type Type.
	 * @return array
	 */
	public static function getAudiences($type, $accountId)
	{
		if (!static::canUse())
		{
			return array();
		}

		return static::getService()->getAudienceList($type, $accountId);
	}

	/**
	 * Get campaign list.
	 *
	 * @param string $type Type.
	 * @return array
	 */
	public static function getCampaignList($type, $accountId)
	{
		if (!static::canUse())
		{
			return array();
		}

		return static::getService()->getCampaignList($type, $accountId);
	}

	/**
	 * Get ad set list.
	 *
	 * @param string $type Type.
	 * @return array
	 */
	public static function getAdSetList($type, $accountId)
	{
		if (!static::canUse())
		{
			return array();
		}

		return static::getService()->getAdSetList($type, $accountId);
	}

	/**
	 * create ads campaign with ads.
	 *
	 * @param string $type Type.
	 * @param $type
	 * @param $data
	 *
	 * @return array
	 */
	public static function createCampaign($type, $data)
	{
		if (!static::canUse())
		{
			return array();
		}
		return static::getService()->createCampaign($type, $data);
	}

	/**
	 * create audiences for campaign.
	 *
	 * @param string $type Type.
	 * @param $type
	 * @param $data
	 *
	 * @return array
	 */
	public static function createAudience($type, $data)
	{
		if (!static::canUse())
		{
			return array();
		}
		return static::getService()->createAudience($type, $data);
	}

	/**
	 * get Ads by id
	 * @param $type
	 * @param $adsId
	 *
	 * @return array|mixed
	 */
	public static function getAds($type, $adsId)
	{
		if (!static::canUse())
		{
			return array();
		}
		return static::getService()->getAds($type, $adsId);
	}

	/**
	 * Search targeting data by query
	 * @param $type
	 * @param $params
	 *
	 * @return array|mixed
	 */
	public static function searchTargetingData($type, $params)
	{
		if (!static::canUse())
		{
			return array();
		}
		return static::getService()->searchTargetingData($type, $params);
	}
}