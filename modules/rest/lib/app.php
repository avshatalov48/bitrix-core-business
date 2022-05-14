<?php
namespace Bitrix\Rest;

use Bitrix\Main;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Event;
use Bitrix\Main\EventManager;
use Bitrix\Main\EventResult;
use Bitrix\Main\Localization\Loc;
use Bitrix\Rest\Engine\Access;
use Bitrix\Rest\Marketplace\Client;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\Relations\OneToMany;
use Bitrix\Rest\Preset\EventController;

Loc::loadMessages(__FILE__);


/**
 * Class AppTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> CLIENT_ID string(128) mandatory
 * <li> CODE string(128) mandatory
 * <li> ACTIVE bool optional default 'Y'
 * <li> INSTALLED bool optional default 'N'
 * <li> URL string(1000) mandatory
 * <li> URL_DEMO string(1000) optional
 * <li> URL_INSTALL string(1000) optional
 * <li> VERSION string(4) mandatory
 * <li> SCOPE string(2000) mandatory
 * <li> STATUS string(1) mandatory default 'F'
 * <li> DATE_FINISH date optional
 * <li> IS_TRIALED bool optional default 'N'
 * <li> SHARED_KEY string(32) optional
 * <li> CLIENT_SECRET string(100) optional
 * <li> APP_NAME string(1000) optional
 * <li> ACCESS string(2000) optional
 * </ul>
 *
 * @package Bitrix\Rest
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_App_Query query()
 * @method static EO_App_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_App_Result getById($id)
 * @method static EO_App_Result getList(array $parameters = array())
 * @method static EO_App_Entity getEntity()
 * @method static \Bitrix\Rest\EO_App createObject($setDefaultValues = true)
 * @method static \Bitrix\Rest\EO_App_Collection createCollection()
 * @method static \Bitrix\Rest\EO_App wakeUpObject($row)
 * @method static \Bitrix\Rest\EO_App_Collection wakeUpCollection($rows)
 */
class AppTable extends Main\Entity\DataManager
{
	const ACTIVE = 'Y';
	const INACTIVE = 'N';
	const INSTALLED = 'Y';
	const NOT_INSTALLED = 'N';
	const TRIALED = 'Y';
	const NOT_TRIALED = 'N';

	const TYPE_STANDARD = 'N';
	const TYPE_ONLY_API = 'A';
	const TYPE_CONFIGURATION = 'C';
	const TYPE_SMART_ROBOTS = 'R';

	const MODE_SITE = 'S';

	const STATUS_LOCAL = 'L';
	const STATUS_FREE = 'F';
	const STATUS_PAID = 'P';
	const STATUS_DEMO = 'D';
	const STATUS_TRIAL = 'T';
	const STATUS_SUBSCRIPTION = 'S';

	const PAID_NOTIFY_DAYS = 5;
	const PAID_GRACE_PERIOD = -14;

	const CACHE_TTL = 86400;
	const CACHE_PATH = '/rest/app/';

	private static $skipRemoteUpdate = false;

	protected static $licenseLang = null;

	protected static $applicationCache = array();

	protected static $localAppDeniedScope = array(
		'landing_cloud', 'rating',
	);

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_rest_app';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'CLIENT_ID' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateClientId'),
			),
			'CODE' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateCode'),
			),
			'ACTIVE' => array(
				'data_type' => 'boolean',
				'values' => array(static::INACTIVE, static::ACTIVE),
			),
			'INSTALLED' => array(
				'data_type' => 'boolean',
				'values' => array(static::NOT_INSTALLED, static::INSTALLED),
			),
			'URL' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateUrl'),
			),
			'URL_DEMO' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateUrlDemo'),
			),
			'URL_INSTALL' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateUrlInstall'),
			),
			'VERSION' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateVersion'),
			),
			'SCOPE' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateScope'),
			),
			'STATUS' => array(
				'data_type' => 'enum',
				'required' => true,
				'values' => array(
					static::STATUS_LOCAL,
					static::STATUS_FREE,
					static::STATUS_PAID,
					static::STATUS_DEMO,
					static::STATUS_TRIAL,
					static::STATUS_SUBSCRIPTION,
				),
			),
			'DATE_FINISH' => array(
				'data_type' => 'date',
			),
			'IS_TRIALED' => array(
				'data_type' => 'boolean',
				'values' => array(static::NOT_TRIALED, static::TRIALED),
			),
			'SHARED_KEY' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateSharedKey'),
			),
			'CLIENT_SECRET' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateClientSecret'),
			),
			'APP_NAME' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateAppName'),
			),
			'ACCESS' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateAccess'),
			),
			'APPLICATION_TOKEN' => array(
				'data_type' => 'string',
			),
			'MOBILE' => array(
				'data_type' => 'boolean',
				'values' => array(static::INACTIVE, static::ACTIVE),
			),
			'USER_INSTALL' => array(
				'data_type' => 'boolean',
				'values' => array(static::INACTIVE, static::ACTIVE),
			),
			'LANG' => array(
				'data_type' => 'Bitrix\Rest\AppLangTable',
				'reference' => array(
					'=this.ID' => 'ref.APP_ID',
					'=ref.LANGUAGE_ID' => new Main\DB\SqlExpression('?s', LANGUAGE_ID),
				),
			),
			'LANG_DEFAULT' => array(
				'data_type' => 'Bitrix\Rest\AppLangTable',
				'reference' => array(
					'=this.ID' => 'ref.APP_ID',
					'=ref.LANGUAGE_ID' => new Main\DB\SqlExpression('?s', Loc::getDefaultLang(LANGUAGE_ID)),
				),
			),
			'LANG_LICENSE' => array(
				'data_type' => 'Bitrix\Rest\AppLangTable',
				'reference' => array(
					'=this.ID' => 'ref.APP_ID',
					'=ref.LANGUAGE_ID' => new Main\DB\SqlExpression('?s', static::getLicenseLanguage()),
				),
			),
			(new OneToMany('LANG_ALL', AppLangTable::class, 'APP'))
				->configureJoinType('left')
		);
	}

	/**
	 * Holds sending changed data to oauth.
	 *
	 * @param $v bool
	 */
	public static function setSkipRemoteUpdate($v)
	{
		static::$skipRemoteUpdate = $v;
	}

	/**
	 * Event on before add application.
	 *
	 * @param Main\Entity\Event $event
	 * @return Main\Entity\EventResult
	 */
	public static function onBeforeAdd(Main\Entity\Event $event)
	{
		$result = new Main\Entity\EventResult();
		$data = $event->getParameters();

		if($data['fields']['STATUS'] == static::STATUS_LOCAL && !$data['fields']['CLIENT_ID'])
		{
			$rnd = Main\Security\Random::getString(8);
			$dummyClientId = 'no_client_id_'.$rnd;
			$dummyClientSecret = 'no_client_secret_'.$rnd;

			$result->modifyFields(array(
				"CLIENT_ID" => $dummyClientId,
				"CLIENT_SECRET" => $dummyClientSecret,
				"CODE" => $dummyClientId,
			));
		}

		return $result;
	}

	/**
	 * Event on after add application.
	 *
	 * @param Main\Entity\Event $event
	 * @return bool
	 * @throws OAuthException
	 */
	public static function onAfterAdd(Main\Entity\Event $event)
	{
		EventController::onAddApp($event);
		$data = $event->getParameters();
		if(!static::$skipRemoteUpdate)
		{
			if(
				$data['fields']['STATUS'] === static::STATUS_LOCAL
				&& OAuthService::getEngine()->isRegistered()
			)
			{
				try
				{
					$appFields = array(
						'TITLE' => $data['fields']['APP_NAME'],
						'REDIRECT_URI' => $data['fields']['URL'],
						'SCOPE' => $data['fields']['SCOPE'],
					);

					$clientData = OAuthService::getEngine()
						->getClient()
						->addApplication($appFields);
				}
				catch(Main\SystemException $e)
				{
					$clientData = array(
						"error" => $e->getCode(),
						"error_description" => $e->getMessage(),
					);
				}

				if(is_array($clientData))
				{
					if($clientData['result'])
					{
						static::$skipRemoteUpdate = true;

						static::clearClientCache($clientData['result']['client_id']);

						$updateResult = static::update($data['id'], array(
							'CLIENT_ID' => $clientData['result']['client_id'],
							'CLIENT_SECRET' => $clientData['result']['client_secret'],
							'STATUS' => static::STATUS_LOCAL,
							'SHARED_KEY' => md5(\CRestUtil::getMemberId().$clientData['result']['client_secret']),
							'CODE' => $clientData['result']['client_id'],
						));
						static::$skipRemoteUpdate = false;

						if($updateResult->isSuccess())
						{
							return true;
						}
						else
						{
							$clientData = array('error' => $updateResult->getErrorMessages());
						}
					}
				}
				else
				{
					$clientData = array('error' => 'Unknown error');
				}

				static::$skipRemoteUpdate = true;
				static::delete($data['id']);
				static::$skipRemoteUpdate = false;

				throw new OAuthException($clientData);
			}
		}

		if($data['fields']['STATUS'] !== static::STATUS_LOCAL)
		{
			\Bitrix\Rest\Engine\Access::getActiveEntity(true);
		}

		return true;
	}

	/**
	 * Event on after update application.
	 *
	 * @param Main\Entity\Event $event
	 * @return bool
	 * @throws OAuthException
	 */
	public static function onAfterUpdate(Main\Entity\Event $event)
	{
		$data = $event->getParameters();
		static::clearClientCache($data['primary']['ID']);

		if(!static::$skipRemoteUpdate)
		{
			if(
				$data['fields']['STATUS'] === static::STATUS_LOCAL
				&& OAuthService::getEngine()->isRegistered()
			)
			{
				$app = static::getByClientId($data['primary']['ID']);

				try
				{
					$updateResult = OAuthService::getEngine()
						->getClient()
						->updateApplication(array(
							"CLIENT_ID" => $app['CLIENT_ID'],
							'TITLE' => $data['fields']['APP_NAME'],
							'REDIRECT_URI' => $data['fields']['URL'],
							'SCOPE' => $data['fields']['SCOPE'],
						));

					if($updateResult['result'])
					{
						return true;
					}
				}
				catch(Main\SystemException $e)
				{
					$updateResult = array(
						"error" => $e->getCode(),
						"error_description" => $e->getMessage(),
					);
				}

				throw new OAuthException($updateResult);
			}
		}

		if($data['fields']['STATUS'] !== static::STATUS_LOCAL)
		{
			\Bitrix\Rest\Engine\Access::getActiveEntity(true);
		}

		return true;
	}

	/**
	 * Event on before delete application.
	 *
	 * @param Main\Entity\Event $event
	 */
	public static function onDelete(Main\Entity\Event $event)
	{
		if(!static::$skipRemoteUpdate)
		{
			$data = $event->getParameters();
			$app = static::getByClientId($data['primary']['ID']);

			if($app['STATUS'] == AppTable::STATUS_LOCAL)
			{
				if(OAuthService::getEngine()->isRegistered())
				{
					\CRestUtil::cleanApp($app["ID"], true);

					try
					{
						OAuthService::getEngine()
							->getClient()
							->deleteApplication(array(
								'CLIENT_ID' => $app['CLIENT_ID'],
							));
					}
					catch(\Bitrix\Main\SystemException $e)
					{
					}
				}
			}
		}
	}

	/**
	 * Event on after delete application.
	 *
	 * @param Main\Entity\Event $event
	 */
	public static function onAfterDelete(Main\Entity\Event $event)
	{
		$data = $event->getParameters();

		static::clearClientCache($data['primary']['ID']);
		AppLangTable::deleteByApp($data['primary']['ID']);
	}

	public static function install($appId)
	{
		$appInfo = static::getByClientId($appId);
		if($appInfo)
		{
			$eventFields = array(
				'APP_ID' => $appId,
				'VERSION' => $appInfo['VERSION'],
				'ACTIVE' => $appInfo['ACTIVE'],
				'INSTALLED' => $appInfo['INSTALLED'],
			);

			if ($appInfo['ACTIVE'] === self::ACTIVE && $appInfo['INSTALLED'] === self::INSTALLED)
			{
				$res = PlacementTable::getList(
					[
						'filter' => [
							'=APP_ID' => $appInfo['ID'],
						],
						'select' => [
							'ID',
							'PLACEMENT',
						],
					]
				);
				while ($item = $res->fetch())
				{
					$event = new Event(
						'rest',
						PlacementTable::PREFIX_EVENT_ON_AFTER_ADD . $item['PLACEMENT'],
						[
							'ID' => $item['ID'],
							'PLACEMENT' => $item['PLACEMENT'],
						]
					);
					EventManager::getInstance()->send($event);
				}
			}

			foreach(GetModuleEvents("rest", "OnRestAppInstall", true) as $eventHandler)
			{
				ExecuteModuleEventEx($eventHandler, array($eventFields));
			}
		}
	}

	/**
	 * Uninstalls application.
	 *
	 * @param string|int $appId
	 * @param int $clean
	 */
	public static function uninstall($appId, $clean = 0)
	{
		$appInfo = static::getByClientId($appId);
		if($appInfo)
		{
			\CRestUtil::cleanApp($appId, $clean);

			if($appInfo['STATUS'] !== static::STATUS_LOCAL)
			{
				OAuthService::getEngine()->getClient()->unInstallApplication(array(
					'CLIENT_ID' => $appInfo['CLIENT_ID']
				));
			}
		}
	}

	/**
	 * Checks opportunity of deleting application.
	 *
	 * @param int $appId
	 * @param int $clean
	 * @return Main\ErrorCollection
	 */
	public static function checkUninstallAvailability($appId, $clean = 0)
	{
		$event = new Main\Event('rest', 'onBeforeApplicationUninstall', [
			'ID' => $appId,
			'CLEAN' => $clean
		]);
		$event->send();

		$result = new Main\ErrorCollection();
		if ($event->getResults())
		{
			/** @var \Bitrix\Main\EventResult $eventResult */
			foreach ($event->getResults() as $eventResult)
			{
				if($eventResult->getType() === EventResult::ERROR)
				{
					$eventResultData = $eventResult->getParameters();
					if ($eventResultData instanceof Main\Error)
					{
						$result->add([$eventResultData]);
					}
				}
			}
		}

		return $result;
	}

	/**
	 * Updates applications status from OAuth.
	 *
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function updateAppStatusInfo()
	{
		$appList = OAuthService::getEngine()->getClient()->getApplicationList();

		if(is_array($appList) && is_array($appList['result']))
		{
			$dbApps = static::getList(array(
				'filter' => array(
					'!=STATUS' => static::STATUS_LOCAL,
				),
				'select' => array(
					'ID', 'CLIENT_ID', 'STATUS', 'DATE_FINISH',
				)
			));

			$localApps = array();
			while($app = $dbApps->fetch())
			{
				$localApps[$app['CLIENT_ID']] = $app;
			}

			foreach($appList['result'] as $app)
			{
				if(array_key_exists($app['client_id'], $localApps))
				{
					$dateFinishLocal = $localApps[$app['client_id']]['DATE_FINISH']
						? $localApps[$app['client_id']]['DATE_FINISH']->getTimestamp()
						: '';
					$dateFinishRemote = $app['date_finish'] ? Main\Type\Date::createFromTimestamp($app['date_finish'])->getTimestamp() : '';

					if(
						$localApps[$app['client_id']]['STATUS'] !== $app['status']
						|| $dateFinishRemote != $dateFinishLocal
					)
					{
						$appFields = array(
							'STATUS' => $app['status'],
							'DATE_FINISH' => $app['date_finish']
								? Main\Type\Date::createFromTimestamp($app['date_finish'])
								: '',
						);

						static::setSkipRemoteUpdate(true);
						$result = static::update($localApps[$app['client_id']]['ID'], $appFields);
						static::setSkipRemoteUpdate(false);

						if(
							$result->isSuccess()
							&& $appFields['STATUS'] === static::STATUS_PAID
						)
						{
							static::callAppPaymentEvent($localApps[$app['client_id']]['ID']);
						}
					}
				}
				else
				{
					$appFields = array(
						'CLIENT_ID' => $app['client_id'],
						'CODE' => $app['code'],
						'ACTIVE' => $app['active'] ? static::ACTIVE : static::INACTIVE,
						'INSTALLED' => static::NOT_INSTALLED,
						'VERSION' => $app['version'],
						'STATUS' => $app['status'],
						'SCOPE' => $app['scope'],
					);

					if(!empty($app['date_finish']))
					{
						$appFields['DATE_FINISH'] = Main\Type\Date::createFromTimestamp($app['date_finish']);
					}

					$result = static::add($appFields);

					if($result->isSuccess() && $appFields['STATUS'] === static::STATUS_PAID)
					{
						static::callAppPaymentEvent($result->getId());
					}
				}
			}
		}
	}

	/**
	 * Sends event applications payment information.
	 *
	 * @param $appId
	 */
	public static function callAppPaymentEvent($appId)
	{
		// for compatibility purpose module_id is bitrix24 here
		foreach(GetModuleEvents('bitrix24', 'OnAfterAppPaid', true) as $event)
		{
			ExecuteModuleEventEx($event, array($appId));
		}
	}

	/**
	 * Returns applications information.
	 *
	 * @param mixed $app
	 * @param string $detailUrl
	 * @return array
	 */
	public static function getAppStatusInfo($app, $detailUrl)
	{
		$res = array();

		if (
			!empty($app)
			&& (
				is_string($app)
				|| is_integer($app)
			)
		)
		{
			$appInfo = $app = static::getByClientId($app);
		}
		elseif (isset($app['CODE']))
		{
			$appInfo = static::getByClientId($app['CODE']);
		}

		if(is_array($app))
		{
			$res['STATUS'] = $app['STATUS'];
			$res['PAYMENT_NOTIFY'] = 'N';
			$res['PAYMENT_EXPIRED'] = 'N';
			$res['PAYMENT_ALLOW'] = 'Y';

			if ($app['STATUS'] === self::STATUS_SUBSCRIPTION)
			{
				if (!\Bitrix\Rest\Marketplace\Client::isSubscriptionAvailable())
				{
					$res['MESSAGE_REPLACE'] = array(
						'#DETAIL_URL#' => $detailUrl,
						'#DAYS#' => 0,
						'#CODE#' => urlencode($app['CODE'])
					);
					$res['PAYMENT_NOTIFY'] = 'Y';
					$res['PAYMENT_EXPIRED'] = 'Y';
					$res['PAYMENT_ALLOW'] = 'N';
				}
				else
				{
					$dateFinish = \Bitrix\Rest\Marketplace\Client::getSubscriptionFinalDate();
					if ($dateFinish !== false)
					{
						$res['DAYS_LEFT'] = floor(($dateFinish->getTimestamp() - \CTimeZone::getOffset() - time()) / 86400);
						if($res['DAYS_LEFT'] < 0)
						{
							$res['MESSAGE_REPLACE'] = array(
								'#DETAIL_URL#' => $detailUrl,
								'#DAYS#' => $res['DAYS_LEFT'],
								'#CODE#' => urlencode($app['CODE'])
							);
							$res['PAYMENT_NOTIFY'] = 'Y';
							$res['PAYMENT_EXPIRED'] = 'Y';
							$res['PAYMENT_ALLOW'] = 'N';
						}
						elseif ($res['DAYS_LEFT'] < static::PAID_NOTIFY_DAYS)
						{
							$res['MESSAGE_REPLACE'] = array(
								'#DETAIL_URL#' => $detailUrl,
								'#DAYS#' => $res['DAYS_LEFT'],
								'#CODE#' => urlencode($app['CODE'])
							);
							$res['PAYMENT_NOTIFY'] = 'Y';
						}
					}
				}
			}
			elseif($app['DATE_FINISH'] <> '' && $app['STATUS'] != self::STATUS_FREE)
			{
				$res['DAYS_LEFT'] = floor(
					(MakeTimeStamp($app['DATE_FINISH']) - \CTimeZone::getOffset() - time()) / 86400
				);

				if(
					$res['DAYS_LEFT'] < static::PAID_NOTIFY_DAYS
					|| $app['STATUS'] == static::STATUS_TRIAL)
				{
					$res['PAYMENT_NOTIFY'] = 'Y';

					if($res['DAYS_LEFT'] < 0)
					{
						$res['PAYMENT_EXPIRED'] = 'Y';

						if($app['STATUS'] == static::STATUS_TRIAL)
						{
							$res['PAYMENT_ALLOW'] = 'N';
						}
						elseif(
							$app['STATUS'] == static::STATUS_PAID
							&& $res['DAYS_LEFT'] < static::PAID_GRACE_PERIOD
						)
						{
							if($app['IS_TRIALED'] == 'N' && $app['URL_DEMO'] <> '')
							{
								$res['STATUS'] = static::STATUS_DEMO;
							}
							else
							{
								$res['PAYMENT_ALLOW'] = 'N';
							}
						}
					}
				}

				$res['MESSAGE_REPLACE'] = array(
					"#DETAIL_URL#" => $detailUrl,
					"#DAYS#" => $res['DAYS_LEFT'],
					"#CODE#" => urlencode($app['CODE']),
				);
			}
			elseif($app['STATUS'] == self::STATUS_DEMO)
			{
				$res['PAYMENT_NOTIFY'] = 'Y';
				$res['MESSAGE_REPLACE'] = array(
					"#DETAIL_URL#" => $detailUrl,
					"#CODE#" => urlencode($app['CODE'])
				);
			}
			else
			{
				$res['MESSAGE_REPLACE'] = array(
					"#DETAIL_URL#" => $detailUrl,
					"#CODE#" => urlencode($app['CODE'])
				);
			}

			$res['MESSAGE_SUFFIX'] = '_'.$res['STATUS'].'_'.$res['PAYMENT_EXPIRED'].'_'.$res['PAYMENT_ALLOW'];

		}

		if (!empty($appInfo['CLIENT_ID']))
		{
			$isHold = \Bitrix\Rest\Engine\Access\HoldEntity::is(
				\Bitrix\Rest\Engine\Access\HoldEntity::TYPE_APP,
				$appInfo['CLIENT_ID']
			);
			if ($isHold)
			{
				$res['MESSAGE_SUFFIX'] = '_HOLD_OVERLOAD';
				$res['PAYMENT_NOTIFY'] = 'Y';
			}
		}

		return $res;
	}

	/**
	 * Returns message with applications status.
	 *
	 * @param string $suffix
	 * @param array|null $replace
	 * @param bool $checkAdmin
	 * @param string|null $language
	 *
	 * @return string
	 */
	public static function getStatusMessage($suffix, $replace = null, $checkAdmin = true, $language = null)
	{
		if ($checkAdmin && \CRestUtil::isAdmin())
		{
			$suffix .= '_A';
		}

		if (
			array_key_exists('#DAYS#', $replace)
			&& (
				is_int($replace['#DAYS#'])
				|| preg_match('/^(-|)\d+$/', $replace['#DAYS#'])
			)
		)
		{
			$replace['#DAYS#'] = FormatDate('ddiff', time(), time() + 24 * 60 * 60 * $replace['#DAYS#']);
		}

		return Loc::getMessage('PAYMENT_MESSAGE' . $suffix, $replace, $language);
	}

	/**
	 * @param string|int $appId
	 * @return array|false
	 */
	public static function getAccess($appId)
	{
		$appInfo = static::getByClientId($appId);
		if($appInfo)
		{
			if($appInfo['ACCESS'] <> '')
			{
				$rightsList = explode(",", $appInfo["ACCESS"]);

				$access = new \CAccess();
				$accessNames = $access->getNames($rightsList);

				$result = array();
				foreach($rightsList as $right)
				{
					$result[$right] = array(
						"id" => $right,
						"provider" => $accessNames[$right]["provider_id"],
						"name" => $accessNames[$right]["name"]
					);
				}
				return $result;
			}
		}

		return false;
	}

	/**
	 * @param string|int $appId
	 * @param array $newRights
	 * @throws \Exception
	 */
	public static function setAccess($appId, $newRights = array())
	{
		$appInfo = static::getByClientId($appId);
		if($appInfo)
		{
			$rights = '';
			if(is_array($newRights) && !empty($newRights))
			{
				$rightIdList = array();
				foreach($newRights as $key => $rightsList)
				{
					foreach($rightsList as $rightId => $ar)
					{
						$rightIdList[] = $rightId;
					}
				}
				$rights = implode(",", $rightIdList);
			}

			static::update($appId, array(
				'ACCESS' => $rights,
			));
		}

		if(defined("BX_COMP_MANAGED_CACHE"))
		{
			global $CACHE_MANAGER;
			$CACHE_MANAGER->ClearByTag('bitrix24_left_menu');
		}
	}

	/**
	 * @param string|int $clientId
	 * @return mixed
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function getByClientId($clientId)
	{
		if(!array_key_exists($clientId, static::$applicationCache))
		{
			if(strval(intval($clientId)) == $clientId)
			{
				$filter = array('=ID' => $clientId);
			}
			else
			{
				$filter = array(
					array(
						'LOGIC' => 'OR',
						'=CODE' => $clientId,
						'=CLIENT_ID' => $clientId,
					),
				);
			}

			$dbRes = static::getList(
				[
					'filter' => $filter,
					'select' => [
						'*',
						'MENU_NAME' => 'LANG.MENU_NAME',
						'MENU_NAME_DEFAULT' => 'LANG_DEFAULT.MENU_NAME',
						'MENU_NAME_LICENSE' => 'LANG_LICENSE.MENU_NAME',
					],
					'limit' => 1,
				]
			);

			foreach ($dbRes->fetchCollection() as $app)
			{
				$appInfo = [
					'ID' => $app->getId(),
					'MENU_NAME' => !is_null($app->getLang()) ? $app->getLang()->getMenuName() : '',
					'MENU_NAME_DEFAULT' => !is_null($app->getLangDefault()) ? $app->getLangDefault()->getMenuName() : '',
					'MENU_NAME_LICENSE' => !is_null($app->getLangLicense()) ? $app->getLangLicense()->getMenuName() : '',
				];
				foreach ($app->sysGetEntity()->getScalarFields() as $field)
				{
					$fieldName = $field->getName();
					if ($field instanceof BooleanField)
					{
						$appInfo[$fieldName] = $app->get($fieldName) ? 'Y' : 'N';
					}
					else
					{
						$appInfo[$fieldName] = $app->get($fieldName);
					}
				}
				$app->fillLangAll();
				if (!is_null($app->getLangAll()))
				{
					foreach ($app->getLangAll() as $lang)
					{
						$appInfo['LANG_ALL'][$lang->getLanguageId()] = [
							'MENU_NAME' => $lang->getMenuName(),
						];
					}
				}
				if ($appInfo['MENU_NAME'] === '')
				{
					$appInfo = Lang::mergeFromLangAll($appInfo);
				}
			}

			if (is_array($appInfo))
			{
				static::$applicationCache[$appInfo['ID']] = $appInfo;
				static::$applicationCache[$appInfo['CLIENT_ID']] = $appInfo;
				static::$applicationCache[$appInfo['CODE']] = $appInfo;
			}
		}

		return static::$applicationCache[$clientId];
	}

	protected static function clearClientCache($clientId)
	{
		if(array_key_exists($clientId, static::$applicationCache))
		{
			$app = static::$applicationCache[$clientId];
			if(is_array($app))
			{
				unset(static::$applicationCache[$app['ID']]);
				unset(static::$applicationCache[$app['CLIENT_ID']]);
			}
			else
			{
				unset(static::$applicationCache[$clientId]);
			}
		}
	}

	protected static function getLicenseLanguage()
	{
		if(static::$licenseLang === null)
		{
			if(Main\Loader::includeModule('bitrix24'))
			{
				static::$licenseLang = \CBitrix24::getLicensePrefix();
			}
			else
			{
				$dbSites = \CSite::getList('sort', 'asc', array('DEFAULT' => 'Y', 'ACTIVE' => 'Y'));
				$site = $dbSites->fetch();

				static::$licenseLang = is_array($site) && isset($site['LANGUAGE_ID']) ? $site['LANGUAGE_ID'] : LANGUAGE_ID;
			}

			if(static::$licenseLang === null)
			{
				static::$licenseLang = 'en';
			}
		}

		return static::$licenseLang;
	}


	/**
	 * Returns validators for CLIENT_ID field.
	 *
	 * @return array
	 */
	public static function validateClientId()
	{
		return array(
			new Main\Entity\Validator\Length(null, 128),
			new Main\Entity\Validator\Unique(),
		);
	}

	/**
	 * Returns validators for CODE field.
	 *
	 * @return array
	 */
	public static function validateCode()
	{
		return array(
			new Main\Entity\Validator\Length(null, 128),
			new Main\Entity\Validator\Unique(),
		);
	}

	/**
	 * Returns validators for URL field.
	 *
	 * @return array
	 */
	public static function validateUrl()
	{
		return array(
			new Main\Entity\Validator\Length(null, 1000),
		);
	}

	/**
	 * Returns validators for URL_DEMO field.
	 *
	 * @return array
	 */
	public static function validateUrlDemo()
	{
		return array(
			new Main\Entity\Validator\Length(null, 1000),
		);
	}

	/**
	 * Returns validators for URL_INSTALL field.
	 *
	 * @return array
	 */
	public static function validateUrlInstall()
	{
		return array(
			new Main\Entity\Validator\Length(null, 1000),
			function ($value, $primary, array $row, Main\Entity\Field $field)
			{
				$checkResult = true;

				if($value)
				{
					try
					{
						if(!HandlerHelper::checkCallback($value, $row, false))
						{
							$checkResult = false;
						}
					}
					catch(RestException $e)
					{
						$checkResult = false;
					}

					if(!$checkResult)
					{
						return Loc::getMessage("MP_ERROR_INCORRECT_URL_INSTALL");
					}
				}

				return true;
			}
		);
	}

	/**
	 * Returns validators for VERSION field.
	 *
	 * @return array
	 */
	public static function validateVersion()
	{
		return array(
			new Main\Entity\Validator\Length(null, 4),
		);
	}

	/**
	 * Returns validators for SCOPE field.
	 *
	 * @return array
	 */
	public static function validateScope()
	{
		return array(
			new Main\Entity\Validator\Length(null, 2000),
		);
	}

	/**
	 * Returns validators for SHARED_KEY field.
	 *
	 * @return array
	 */
	public static function validateSharedKey()
	{
		return array(
			new Main\Entity\Validator\Length(null, 32),
		);
	}

	/**
	 * Returns validators for APP_SECRET_ID field.
	 *
	 * @return array
	 */
	public static function validateClientSecret()
	{
		return array(
			new Main\Entity\Validator\Length(null, 100),
		);
	}

	/**
	 * Returns validators for APP_NAME field.
	 *
	 * @return array
	 */
	public static function validateAppName()
	{
		return array(
			new Main\Entity\Validator\Length(null, 1000),
		);
	}

	/**
	 * Returns validators for ACCESS field.
	 *
	 * @return array
	 */
	public static function validateAccess()
	{
		return array(
			new Main\Entity\Validator\Length(null, 2000),
		);
	}

	/**
	 * @param array $permissionList
	 * @return array
	 */
	public static function cleanLocalPermissionList(array $permissionList)
	{
		foreach($permissionList as $key => $perm)
		{
			if(in_array($perm, static::$localAppDeniedScope))
			{
				unset($permissionList[$key]);
			}
		}

		return array_values($permissionList);
	}

	/**
	 * @param string $code
	 * @param false $version
	 * @return bool
	 */
	public static function canUninstallByType($code, $version = false)
	{
		$result = true;

		$type = static::getAppType($code, $version);
		if($type == static::TYPE_CONFIGURATION)
		{
			$appList = \Bitrix\Rest\Configuration\Helper::getInstance()->getBasicAppList();
			if(in_array($code, $appList))
			{
				$result = false;
			}
		}

		return $result;
	}

	/**
	 * @param $code
	 * @param false $version
	 * @return false|mixed
	 */
	public static function getAppType($code, $version = false)
	{
		$result = false;
		$cache = Cache::createInstance();
		if ($cache->initCache(static::CACHE_TTL, 'appType'.md5($code.$version), static::CACHE_PATH))
		{
			$result = $cache->getVars();
		}
		elseif ($cache->startDataCache())
		{
			$appDetailInfo = Client::getInstall($code, $version);
			$result = ($appDetailInfo['ITEMS']['TYPE'])?:false;
			$cache->endDataCache($result);
		}

		return $result;
	}
}
