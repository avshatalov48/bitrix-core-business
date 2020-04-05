<?
namespace Bitrix\MessageService;

use \Bitrix\Main\Loader;
use \Bitrix\Rest\AppTable;
use \Bitrix\Rest\RestException;
use \Bitrix\Rest\AccessException;

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
		return array(static::SCOPE => array(
			'messageservice.sender.add' => array(__CLASS__, 'addSender'),
			'messageservice.sender.delete' => array(__CLASS__, 'deleteSender'),
			'messageservice.sender.list' => array(__CLASS__, 'getSenderList'),

			'messageservice.message.status.update' => array(__CLASS__, 'updateMessageStatus'),
		));
	}

	/**
	 * @param array $fields Fields describes application.
	 * @return void
	 */
	public static function onRestAppDelete(array $fields)
	{
		$fields = array_change_key_case($fields, CASE_UPPER);
		if (empty($fields['APP_ID']))
			return;

		if (!Loader::includeModule('rest'))
			return;

		$dbRes = AppTable::getById($fields['APP_ID']);
		$app = $dbRes->fetch();

		if (!$app)
			return;

		$iterator = Internal\Entity\RestAppTable::getList(array(
			'select' => array('ID'),
			'filter' => array('=APP_ID' => $app['CLIENT_ID'])
		));

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
	 * @param \CRestServer $server Rest server instance.
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

		$iterator = Internal\Entity\RestAppTable::getList(array(
			'select' => array('ID'),
			'filter' => array(
				'=APP_ID' => $params['APP_ID'],
				'=CODE' => $params['CODE']
			)
		));
		$result = $iterator->fetch();
		if ($result)
		{
			throw new RestException('Sender already installed!', self::ERROR_SENDER_ALREADY_INSTALLED);
		}

		$senderLang = array(
			'NAME' => $params['NAME'],
			'DESCRIPTION' => isset($params['DESCRIPTION']) ? $params['DESCRIPTION'] : ''
		);
		unset($params['NAME'], $params['DESCRIPTION']);

		$params['AUTHOR_ID'] = $USER->getId();
		$result = Internal\Entity\RestAppTable::add($params);

		if ($result->getErrors())
			throw new RestException('Sender save error!', self::ERROR_SENDER_ADD_FAILURE);

		$senderLang['APP_ID'] = $result->getId();
		static::addSenderLang($senderLang, $server->getClientId());

		return true;
	}

	/**
	 * @param array $params Input params.
	 * @param int $n Offset.
	 * @param \CRestServer $server Rest server instance.
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

		$iterator = Internal\Entity\RestAppTable::getList(array(
			'select' => array('ID'),
			'filter' => array(
				'=APP_ID' => $params['APP_ID'],
				'=CODE' => $params['CODE']
			)
		));
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
	 * @param \CRestServer $server Rest server instance.
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
		$iterator = Internal\Entity\RestAppTable::getList(array(
			'select' => array('CODE'),
			'filter' => array(
				'=APP_ID' => $server->getClientId()
			)
		));

		$result = array();
		while ($row = $iterator->fetch())
		{
			$result[] = $row['CODE'];
		}
		return $result;
	}

	/**
	 * @param array $params Input params.
	 * @param int $n Offset.
	 * @param \CRestServer $server Rest server instance.
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

		if ($statusId === null)
		{
			throw new RestException('Message status incorrect!', self::ERROR_MESSAGE_STATUS_INCORRECT);
		}

		$message = Internal\Entity\MessageTable::getList(array(
			'select' => array('ID', 'AUTHOR_ID', 'STATUS_ID'),
			'filter' => array(
				'=SENDER_ID' => 'rest',
				'=MESSAGE_FROM' => $server->getClientId().'|'.$params['CODE'],
				'=EXTERNAL_ID' => $params['MESSAGE_ID']
			)
		))->fetch();

		if (!$message)
		{
			throw new RestException('Message not found!', self::ERROR_MESSAGE_NOT_FOUND);
		}

		$previousStatusId = (int)$message['STATUS_ID'];

		if ($previousStatusId === $statusId)
		{
			return true;
		}

		$authorId = (int)$message['AUTHOR_ID'];

		if ($authorId !== static::getUserId())
		{
			static::checkAdminPermissions();
		}

		Internal\Entity\MessageTable::update($message['ID'], array('STATUS_ID' => $statusId));
		Integration\Pull::onMessagesUpdate(array(
			array('ID' => $message['ID'], 'STATUS_ID' => $statusId)
		));

		return true;
	}

	private static function getUserId()
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
			throw new RestException('Empty data!', self::ERROR_SENDER_VALIDATION_FAILURE);

		static::validateSenderCode($data['CODE']);
		static::validateSenderHandler($data['HANDLER'], $server);
		if (empty($data['NAME']))
			throw new RestException('Empty sender NAME!', self::ERROR_SENDER_VALIDATION_FAILURE);

		if (empty($data['TYPE']))
			throw new RestException('Empty sender message TYPE!', self::ERROR_SENDER_VALIDATION_FAILURE);

		if (!in_array($data['TYPE'], array('SMS'), true))
			throw new RestException('Unknown sender message TYPE!', self::ERROR_SENDER_VALIDATION_FAILURE);
	}

	private static function validateSenderCode($code)
	{
		if (empty($code))
			throw new RestException('Empty sender code!', self::ERROR_SENDER_VALIDATION_FAILURE);
		if (!preg_match('#^[a-z0-9\.\-_]+$#i', $code))
			throw new RestException('Wrong sender code!', self::ERROR_SENDER_VALIDATION_FAILURE);
	}

	private static function validateSenderHandler($handler, $server)
	{
		$handlerData = parse_url($handler);

		if (is_array($handlerData)
			&& strlen($handlerData['host']) > 0
			&& strpos($handlerData['host'], '.') > 0
		)
		{
			if ($handlerData['scheme'] == 'http' || $handlerData['scheme'] == 'https')
			{
				$host = $handlerData['host'];
				$app = self::getApp($server);
				if (strlen($app['URL']) > 0)
				{
					$urls = array($app['URL']);

					if (strlen($app['URL_DEMO']) > 0)
					{
						$urls[] = $app['URL_DEMO'];
					}
					if (strlen($app['URL_INSTALL']) > 0)
					{
						$urls[] = $app['URL_INSTALL'];
					}

					$found = false;
					foreach($urls as $url)
					{
						$a = parse_url($url);
						if ($host == $a['host'] || $a['host'] == 'localhost')
						{
							$found = true;
							break;
						}
					}

					if(!$found)
					{
						throw new RestException('Handler URL host doesn\'t match application url', self::ERROR_HANDLER_URL_MATCH);
					}
				}
			}
			else
			{
				throw new RestException('Unsupported event handler protocol', self::ERROR_UNSUPPORTED_PROTOCOL);
			}
		}
		else
		{
			throw new RestException('Wrong handler URL', self::ERROR_WRONG_HANDLER_URL);
		}
	}

	/**
	 * @param \CRestServer $server
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
					array(
						'filter' => array(
							'=CLIENT_ID' => $server->getClientId()
						)
					)
				);
				self::$app = $result->fetch();
			}
		}

		return self::$app;
	}

	private static function addSenderLang($langFields, $clientId)
	{
		$langData = array();

		if (!is_array($langFields['NAME']))
		{
			$langData['**'] = array(
				'APP_ID' => $langFields['APP_ID'],
				'LANGUAGE_ID' => '**',
				'NAME' => $langFields['NAME'],
				'DESCRIPTION' => is_scalar($langFields['DESCRIPTION']) ? (string)$langFields['DESCRIPTION'] : null
			);
		}
		else
		{
			foreach ($langFields['NAME'] as $langId => $langName)
			{
				$langData[strtolower($langId)] = array(
					'APP_ID' => $langFields['APP_ID'],
					'LANGUAGE_ID' => strtolower($langId),
					'NAME' => $langFields['NAME'][$langId],
					'DESCRIPTION' => is_array($langFields['DESCRIPTION']) && isset($langFields['DESCRIPTION'][$langId])
						? (string)$langFields['DESCRIPTION'][$langId] : null
				);

				if (!isset($langData['**']))
				{
					$langData['**'] = array(
						'APP_ID' => $langFields['APP_ID'],
						'LANGUAGE_ID' => '**',
						'NAME' => $langFields['NAME'][$langId],
						'DESCRIPTION' => is_array($langFields['DESCRIPTION']) && isset($langFields['DESCRIPTION'][$langId])
							? (string)$langFields['DESCRIPTION'][$langId] : null
					);
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
			array(
				'filter' => array(
					'=CLIENT_ID' => $clientId
				),
				'select' => array('ID', 'APP_NAME', 'CODE'),
			)
		);
		$app = $iterator->fetch();
		$result = array(
			'**' => $app['APP_NAME'] ? $app['APP_NAME'] : $app['CODE']
		);

		$orm = \Bitrix\Rest\AppLangTable::getList(array(
			'filter' => array(
				'=APP_ID' => $app['ID']
			),
			'select' => array('LANGUAGE_ID', 'MENU_NAME')
		));

		while ($row = $orm->fetch())
		{
			$result[strtolower($row['LANGUAGE_ID'])] = $row['MENU_NAME'];
		}

		if (isset($result[LANGUAGE_ID]))
		{
			$result['**'] = $result[LANGUAGE_ID];
		}

		return $result;
	}
}