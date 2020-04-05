<?php

namespace Bitrix\Seo\Retargeting;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;

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

		$audience->setAccountId($accountId);
		$parameters = array(
			'NAME' => $name ?: Loc::getMessage('')
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
								$audienceData['COUNT_VALID'] ?
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
		}

		return $providers;
	}

	public static function addToAudience(AdsAudienceConfig $config, $contacts)
	{
		static $audiences = array();
		if (!isset($audiences[$config->type]))
		{
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
				'type' => $config->contactType
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

			$authAdapter = static::getService()->getAuthAdapter($type);
			$account = static::getService()->getAccount($type);

			$providers[$type] = array(
				'TYPE' => $type,
				'HAS_AUTH' => $authAdapter->hasAuth(),
				'AUTH_URL' => $authAdapter->getAuthUrl(),
				'PROFILE' => $account->getProfileCached(),
			);

			// check if no profile, then may be auth was removed in service
			if ($providers[$type]['HAS_AUTH'] && empty($providers[$type]['PROFILE']))
			{
				static::removeAuth($type);
				$providers[$type]['HAS_AUTH'] = false;
			}
		}

		return $providers;
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