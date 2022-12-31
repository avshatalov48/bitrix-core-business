<?
namespace Bitrix\MessageService;

use \Bitrix\Main\Loader;
use Bitrix\MessageService\Context\User;
use \Bitrix\Rest\AppTable;
use Bitrix\Rest\HandlerHelper;
use \Bitrix\Rest\RestException;
use \Bitrix\Rest\AccessException;
use CRestServer;

Loader::includeModule('rest');

class RestService extends \IRestService
{
	const SCOPE = 'messageservice';
	protected static $app;

	const ERROR_UNSUPPORTED_PROTOCOL = 'ERROR_UNSUPPORTED_PROTOCOL';
	const ERROR_WRONG_HANDLER_URL = 'ERROR_WRONG_HANDLER_URL';
	const ERROR_HANDLER_URL_MATCH = 'ERROR_HANDLER_URL_MATCH';

	const ERROR_SENDER_ALREADY_INSTALLED = 'ERROR_SENDER_ALREADY_INSTALLED';
	const ERROR_SENDER_ADD_FAILURE = 'ERROR_SENDER_ADD_FAILURE';
	const ERROR_SENDER_VALIDATION_FAILURE = 'ERROR_SENDER_VALIDATION_FAILURE';
	const ERROR_SENDER_NOT_FOUND = 'ERROR_SENDER_NOT_FOUND';

	const ERROR_MESSAGE_NOT_FOUND = 'ERROR_MESSAGE_NOT_FOUND';
	const ERROR_MESSAGE_STATUS_INCORRECT = 'ERROR_MESSAGE_STATUS_INCORRECT';

	public static function onRestServiceBuildDescription()
	{
		return [
			static::SCOPE => [
			'messageservice.sender.add' => [__CLASS__, 'addSender'],
			'messageservice.sender.delete' => [__CLASS__, 'deleteSender'],
			'messageservice.sender.list' => [__CLASS__, 'getSenderList'],

			'messageservice.message.status.update' => [__CLASS__, 'updateMessageStatus'],
			'messageservice.message.status.get' => [__CLASS__, 'getMessageStatus'],
			]
		];
	}

	/**
	 * @param array $fields Fields describes application.
	 * @return void
	 */
	public static function onRestAppDelete(array $fields)
	{
		$fields = array_change_key_case($fields, CASE_UPPER);
		if (empty($fields['APP_ID']))
		{
			return;
		}

		if (!Loader::includeModule('rest'))
		{
			return;
		}

		$dbRes = AppTable::getById($fields['APP_ID']);
		$app = $dbRes->fetch();

		if (!$app)
		{
			return;
		}

		$iterator = Internal\Entity\RestAppTable::getList([
			'select' => ['ID'],
			'filter' => ['=APP_ID' => $app['CLIENT_ID']]
		]);

		while ($row = $iterator->fetch())
		{
			Internal\Entity\RestAppTable::delete($row['ID']);
		}
	}

	/**
	 * @param array $fields Fields describes application.
	 * @return void
	 */
	public static function onRestAppUpdate(array $fields)
	{
		static::onRestAppDelete($fields);
	}

	/**
	 * @param array $params Input params.
	 * @param int $n Offset.
	 * @param CRestServer $server Rest server instance.
	 * @return bool
	 * @throws \Exception
	 */
	public static function addSender($params, $n, $server)
	{
		global $USER;

		if(!$server->getClientId())
		{
			throw new AccessException("Application context required");
		}

		self::checkAdminPermissions();
		$params = array_change_key_case($params, CASE_UPPER);

		self::validateSender($params, $server);

		$params['APP_ID'] = $server->getClientId();

		$iterator = Internal\Entity\RestAppTable::getList([
			'select' => ['ID'],
			'filter' => [
				'=APP_ID' => $params['APP_ID'],
				'=CODE' => $params['CODE']
			]
		]);
		$result = $iterator->fetch();
		if ($result)
		{
			throw new RestException('Sender already installed!', self::ERROR_SENDER_ALREADY_INSTALLED);
		}

		$senderLang = [
			'NAME' => $params['NAME'],
			'DESCRIPTION' => isset($params['DESCRIPTION']) ? $params['DESCRIPTION'] : ''
		];
		unset($params['NAME'], $params['DESCRIPTION']);

		$params['AUTHOR_ID'] = $USER->getId();
		$result = Internal\Entity\RestAppTable::add($params);

		if ($result->getErrors())
		{
			throw new RestException('Sender save error!', self::ERROR_SENDER_ADD_FAILURE);
		}

		$senderLang['APP_ID'] = $result->getId();
		static::addSenderLang($senderLang, $server->getClientId());

		$app = \Bitrix\Rest\AppTable::getByClientId($params['APP_ID']);
		if ($app['CODE'])
		{
			AddEventToStatFile(
				'messageservice',
				'addProvider' . $params['TYPE'],
				uniqid($app['CODE'], true),
				$app['CODE']
			);
		}

		return true;
	}

	/**
	 * @param array $params Input params.
	 * @param int $n Offset.
	 * @param CRestServer $server Rest server instance.
	 * @return bool
	 * @throws \Exception
	 */
	public static function deleteSender($params, $n, $server)
	{
		if(!$server->getClientId())
		{
			throw new AccessException("Application context required");
		}

		$params = array_change_key_case($params, CASE_UPPER);
		self::checkAdminPermissions();
		self::validateSenderCode($params['CODE']);
		$params['APP_ID'] = $server->getClientId();

		$iterator = Internal\Entity\RestAppTable::getList([
			'select' => ['ID'],
			'filter' => [
				'=APP_ID' => $params['APP_ID'],
				'=CODE' => $params['CODE']
			]
		]);
		$result = $iterator->fetch();
		if (!$result)
		{
			throw new RestException('Sender not found!', self::ERROR_SENDER_NOT_FOUND);
		}
		Internal\Entity\RestAppTable::delete($result['ID']);
		Internal\Entity\RestAppLangTable::deleteByApp($result['ID']);

		return true;
	}

	/**
	 * @param array $params Input params.
	 * @param int $n Offset.
	 * @param CRestServer $server Rest server instance.
	 * @return array
	 * @throws AccessException
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function getSenderList($params, $n, $server)
	{
		if(!$server->getClientId())
		{
			throw new AccessException("Application context required");
		}

		self::checkAdminPermissions();
		$iterator = Internal\Entity\RestAppTable::getList([
			'select' => ['CODE'],
			'filter' => [
				'=APP_ID' => $server->getClientId()
			]
		]);

		$result = [];
		while ($row = $iterator->fetch())
		{
			$result[] = $row['CODE'];
		}
		return $result;
	}

	/**
	 * @param array $params Input params.
	 * @param int $n Offset.
	 * @param CRestServer $server Rest server instance.
	 * @return bool
	 * @throws AccessException
	 * @throws RestException
	 */
	public static function updateMessageStatus($params, $n, $server)
	{
		if(!$server->getClientId())
		{
			throw new AccessException("Application context required");
		}

		$params = array_change_key_case($params, CASE_UPPER);
		static::validateSenderCode($params['CODE']);
		if (empty($params['MESSAGE_ID']))
		{
			throw new RestException('Message not found!', self::ERROR_MESSAGE_NOT_FOUND);
		}

		$statusId = isset($params['STATUS']) ? Sender\Sms\Rest::resolveStatus($params['STATUS']) : null;
		if ($statusId === null || $statusId === MessageStatus::UNKNOWN)
		{
			throw new RestException('Message status incorrect!', self::ERROR_MESSAGE_STATUS_INCORRECT);
		}

		$message = Message::loadByExternalId(
			'rest',
			$params['MESSAGE_ID'],
			$server->getClientId().'|'.$params['CODE']
		);
		if (!$message)
		{
			throw new RestException('Message not found!', self::ERROR_MESSAGE_NOT_FOUND);
		}

		if ($message->getAuthorId() !== static::getUserId())
		{
			static::checkAdminPermissions();
		}
		$message->updateStatus($statusId);

		return true;
	}

	/**
	 * @param array{ message_id: int } $params
	 * @param int $n
	 * @param CRestServer $server
	 * @return string
	 * @throws RestException
	 */
	public static function getMessageStatus(array $params, int $n, CRestServer $server)
	{
		if (Loader::includeModule('intranet') && \Bitrix\Intranet\Util::isExtranetUser(static::getUserId()))
		{
			throw new AccessException("Extranet user denied access");
		}

		$params = array_change_key_case($params, CASE_UPPER);

		if (empty($params['MESSAGE_ID']) || !is_numeric($params['MESSAGE_ID']))
		{
			throw new RestException('Message not found!', self::ERROR_MESSAGE_NOT_FOUND);
		}

		$message = Message::loadById($params['MESSAGE_ID']);

		if ($message === null)
		{
			throw new RestException('Message not found!', self::ERROR_MESSAGE_NOT_FOUND);
		}

		$statusList = MessageStatus::getDescriptions('en');

		return array_key_exists($message->getStatusId(), $statusList) ? $statusList[$message->getStatusId()] : '';
	}

	private static function getUserId(): int
	{
		global $USER;
		if (isset($USER) && $USER instanceof \CUser)
		{
			return (int)$USER->getID();
		}
		return 0;
	}

	private static function checkAdminPermissions()
	{
		global $USER;
		if (!isset($USER)
			|| !is_object($USER)
			|| (!$USER->isAdmin() && !(Loader::includeModule('bitrix24') && \CBitrix24::isPortalAdmin($USER->getID())))
		)
		{
			throw new AccessException();
		}
	}

	private static function validateSender($data, $server)
	{
		if (!is_array($data) || empty($data))
		{
			throw new RestException('Empty data!', self::ERROR_SENDER_VALIDATION_FAILURE);
		}

		static::validateSenderCode($data['CODE']);
		static::validateSenderHandler($data['HANDLER'], $server);
		if (empty($data['NAME']))
		{
			throw new RestException('Empty sender NAME!', self::ERROR_SENDER_VALIDATION_FAILURE);
		}

		if (empty($data['TYPE']))
		{
			throw new RestException('Empty sender message TYPE!', self::ERROR_SENDER_VALIDATION_FAILURE);
		}

		if (!in_array($data['TYPE'], ['SMS'], true))
		{
			throw new RestException('Unknown sender message TYPE!', self::ERROR_SENDER_VALIDATION_FAILURE);
		}
	}

	private static function validateSenderCode($code)
	{
		if (empty($code))
		{
			throw new RestException('Empty sender code!', self::ERROR_SENDER_VALIDATION_FAILURE);
		}
		if (!preg_match('#^[a-z0-9\.\-_]+$#i', $code))
		{
			throw new RestException('Wrong sender code!', self::ERROR_SENDER_VALIDATION_FAILURE);
		}
	}

	private static function validateSenderHandler($handler, $server)
	{
		HandlerHelper::checkCallback($handler);
	}

	/**
	 * @param CRestServer $server
	 * @return array|bool|false|mixed|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 */
	private static function getApp($server)
	{
		if(self::$app == null)
		{
			if (Loader::includeModule('rest'))
			{
				$result = AppTable::getList(
					[
						'filter' => [
							'=CLIENT_ID' => $server->getClientId()
						]
					]
				);
				self::$app = $result->fetch();
			}
		}

		return self::$app;
	}

	private static function addSenderLang($langFields, $clientId)
	{
		$langData = [];

		if (!is_array($langFields['NAME']))
		{
			$langData['**'] = [
				'APP_ID' => $langFields['APP_ID'],
				'LANGUAGE_ID' => '**',
				'NAME' => $langFields['NAME'],
				'DESCRIPTION' => is_scalar($langFields['DESCRIPTION']) ? (string)$langFields['DESCRIPTION'] : null
			];
		}
		else
		{
			foreach ($langFields['NAME'] as $langId => $langName)
			{
				$langData[mb_strtolower($langId)] = [
					'APP_ID' => $langFields['APP_ID'],
					'LANGUAGE_ID' => mb_strtolower($langId),
					'NAME' => $langFields['NAME'][$langId],
					'DESCRIPTION' => is_array($langFields['DESCRIPTION']) && isset($langFields['DESCRIPTION'][$langId])
						? (string)$langFields['DESCRIPTION'][$langId] : null
				];

				if (!isset($langData['**']))
				{
					$langData['**'] = [
						'APP_ID' => $langFields['APP_ID'],
						'LANGUAGE_ID' => '**',
						'NAME' => $langFields['NAME'][$langId],
						'DESCRIPTION' => is_array($langFields['DESCRIPTION']) && isset($langFields['DESCRIPTION'][$langId])
							? (string)$langFields['DESCRIPTION'][$langId] : null
					];
				}
			}
		}

		$appNames = static::getAppNames($clientId);
		foreach ($appNames as $langId => $appName)
		{
			if (isset($langData[$langId]))
			{
				$langData[$langId]['APP_NAME'] = $appName;
			}
		}

		foreach ($langData as $toAdd)
		{
			Internal\Entity\RestAppLangTable::add($toAdd);
		}
	}

	private static function getAppNames($clientId)
	{
		$iterator = \Bitrix\Rest\AppTable::getList(
			[
				'filter' => [
					'=CLIENT_ID' => $clientId
				],
				'select' => ['ID', 'APP_NAME', 'CODE'],
			]
		);
		$app = $iterator->fetch();
		$result = [
			'**' => $app['APP_NAME'] ? $app['APP_NAME'] : $app['CODE']
		];

		$orm = \Bitrix\Rest\AppLangTable::getList([
			'filter' => [
				'=APP_ID' => $app['ID']
			],
			'select' => ['LANGUAGE_ID', 'MENU_NAME']
		]);

		while ($row = $orm->fetch())
		{
			$result[mb_strtolower($row['LANGUAGE_ID'])] = $row['MENU_NAME'];
		}

		if (isset($result[LANGUAGE_ID]))
		{
			$result['**'] = $result[LANGUAGE_ID];
		}

		return $result;
	}
}