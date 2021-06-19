<?php

namespace Bitrix\Seo\Marketing;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

/**
 * Class AdsAudience.
 * @package Bitrix\Seo\Retargeting
 */
class AdsAudience
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

	/**
	 * Add audience.
	 *
	 * @param string $type Type.
	 * @param integer|null $accountId Account ID.
	 * @param string|null $name Name.
	 * @return integer|null
	 */
	public static function addAudience($type, $accountId = null, $name = null)
	{
		$audience = Service::getAudience($type);
		if (!$audience)
		{
			return null;
		}

		if (!$name)
		{
			self::$errors[] = Loc::getMessage("SEO_RETARGETING_EMPTY_AUDIENCE_NAME");
			return null;
		}
		$audience->setAccountId($accountId);
		$parameters = array(
			'NAME' => $name
		);
		$addResult = $audience->add($parameters);
		if ($addResult->isSuccess() && $addResult->getId())
		{
			return $addResult->getId();
		}
		else
		{
			self::$errors = $addResult->getErrorMessages();
			return null;
		}
	}

	public static function addLookalikeAudience($type, $accountId = null, $sourceAudienceId = null, $options = [])
	{
		$audience = Service::getAudience($type);
		if (!$audience)
		{
			return null;
		}
		$audience->setAccountId($accountId);
		$addResult = $audience->createLookalike($sourceAudienceId, $options);
		if ($addResult->isSuccess() && $addResult->getId())
		{
			return $addResult->getId();
		}
		else
		{
			self::$errors = $addResult->getErrorMessages();
			return null;
		}
	}

	/**
	 * Get audiences.
	 *
	 * @param string $type Type.
	 * @param integer|null $accountId Account ID.
	 * @return array
	 */
	public static function getAudiences($type, $accountId = null)
	{
		$result = array();

		$audience = Service::getAudience($type);

		$audience->setAccountId($accountId);
		$audiencesResult = $audience->getList();
		if ($audiencesResult->isSuccess())
		{
			while ($audienceData = $audiencesResult->fetch())
			{
				$audienceData = $audience->normalizeListRow($audienceData);
				if ($audienceData['ID'])
				{
					$result[] = array(
						'id' => $audienceData['ID'],
						'isSupportMultiTypeContacts' => $audience->isSupportMultiTypeContacts(),
						//'isAddingRequireContacts' => $audience->isAddingRequireContacts(),
						'supportedContactTypes' => $audienceData['SUPPORTED_CONTACT_TYPES'],
						'name' =>
							$audienceData['NAME']
								?
								$audienceData['NAME'] . (
								$audienceData['COUNT_VALID'] > 0 ?
									' (' . $audienceData['COUNT_VALID'] . ')'
									:
									''
								)
								:
								$audienceData['ID']
					);
				}
			}
		}
		else
		{
			self::$errors = $audiencesResult->getErrorMessages();
		}

		return $result;
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
		$providers = static::getServiceProviders($types);
		foreach ($providers as $type => $provider)
		{
			$audience = Service::getAudience($type);
			$providers[$type]['URL_AUDIENCE_LIST'] =  $audience->getUrlAudienceList();
			$providers[$type]['IS_SUPPORT_ACCOUNT'] =  $audience->isSupportAccount();
			$providers[$type]['IS_SUPPORT_REMOVE_CONTACTS'] =  $audience->isSupportRemoveContacts();
			//$providers[$type]['IS_ADDING_REQUIRE_CONTACTS'] =  $audience->isAddingRequireContacts();
			$providers[$type]['IS_SUPPORT_MULTI_TYPE_CONTACTS'] =  $audience->isSupportMultiTypeContacts();
			$providers[$type]['IS_SUPPORT_ADD_AUDIENCE'] =  $audience->isSupportAddAudience();
			$lookalikeAudienceParams = $audience->getLookalikeAudiencesParams();
			$providers[$type]['IS_SUPPORT_LOOKALIKE_AUDIENCE'] =  !!$lookalikeAudienceParams;
			$providers[$type]['LOOKALIKE_AUDIENCE_PARAMS'] = $lookalikeAudienceParams;
		}

		return $providers;
	}

	/**
	 * Add to audience
	 * @param AdsAudienceConfig $config Config.
	 * @param array $contacts Contacts.
	 * @return bool
	 */
	public static function addToAudience(AdsAudienceConfig $config, $contacts)
	{
		static $audiences = array();
		if (!isset($audiences[$config->type]))
		{
			if ($config->clientId)
			{
				$service = static::getService();
				$service->setClientId($config->clientId);
			}
			$audience = Service::getAudience($config->type);
			$audiences[$config->type] = $audience;
		}
		else
		{
			$audience = $audiences[$config->type];
		}

		$audience->setAccountId($config->accountId);
		static::$isQueueUsed ? $audience->enableQueueMode() : $audience->disableQueueMode();
		if ($config->autoRemoveDayNumber)
		{
			$audience->enableQueueAutoRemove($config->autoRemoveDayNumber);
		}
		else
		{
			$audience->disableQueueAutoRemove();
		}

		$audienceImportResult = $audience->addContacts(
			$config->audienceId,
			$contacts,
			array(
				'type' => $config->contactType,
				'parentId' => $config->parentId
			)
		);

		self::$errors = $audienceImportResult->getErrorMessages();
		return $audienceImportResult->isSuccess();
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

			$service = static::getService();
			$authAdapter = $service->getAuthAdapter($type);
			$account = $service->getAccount($type);
			$canUserMultiClients = $authAdapter->canUseMultipleClients();

			$providers[$type] = array(
				'TYPE' => $type,
				'HAS_AUTH' => $authAdapter->hasAuth(),
				'AUTH_URL' => $authAdapter->getAuthUrl(),
				'PROFILE' => $authAdapter->getToken() ? $account->getProfileCached() : false,
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
				$accountData = $account->normalizeListRow($accountData);
				if ($accountData['ID'])
				{
					$result[] = array(
						'id' => $accountData['ID'],
						'name' => $accountData['NAME'] ? $accountData['NAME'] : $accountData['ID']
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
}