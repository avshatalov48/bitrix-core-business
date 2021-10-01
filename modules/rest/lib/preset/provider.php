<?php

namespace Bitrix\Rest\Preset;

use Bitrix\Main\SystemException;
use Bitrix\Main\UserTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Security\Random;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Rest\APAuth\PermissionTable;
use Bitrix\Rest\Lang;
use Bitrix\Rest\PlacementLangTable;
use Bitrix\Rest\Preset\Data\Element;
use Bitrix\Rest\Preset\Data\Rest;
use Bitrix\Rest\EventTable;
use Bitrix\Rest\AppTable;
use Bitrix\Rest\APAuth\PasswordTable;
use Bitrix\Rest\PlacementTable;
use Bitrix\Rest\AppLangTable;
use Bitrix\Rest\Event\Sender;
use Bitrix\Rest\OAuthService;
use Bitrix\Rest\Analytic;
use Bitrix\Im\Model\BotTable;
use Bitrix\Im\Bot;

/**
 * Class Provider
 * @package Bitrix\Rest\Preset
 */
class Provider
{
	public const URI_METHOD_INFO = 'https://util.bitrixsoft.com/example_b24/redirect.php';
	public const URI_EXAMPLE_DOWNLOAD = 'https://util.bitrixsoft.com/example_b24/';
	public const APP_MODE_SERVER = 'SERVER';
	public const APP_MODE_ZIP = 'ZIP';

	/**
	 * @param $id
	 *
	 * @return array
	 * @throws SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 */
	public static function deleteIntegration($id)
	{
		$result = [
			'result' => 'success'
		];
		$errorList = [];

		$res = IntegrationTable::getList(
			[
				'filter' => [
					'=ID' => $id
				],
				'select' => [
					'ID',
					'APP_ID',
					'BOT_ID',
					'PASSWORD_ID',
					'USER_ID'
				],
				'limit' => 1
			]
		);
		if ($integration = $res->fetch())
		{
			global $USER;
			if ($integration['USER_ID'] === $USER->GetID() || \CRestUtil::isAdmin())
			{
				$filterEvent = [
					'=INTEGRATION_ID' => $integration['ID']
				];
				if ($integration['BOT_ID'] > 0 && Loader::includeModule('im'))
				{
					$res = BotTable::getList(
						[
							'filter' => [
								'=BOT_ID' => $integration['BOT_ID'],
							]
						]
					);
					if ($bot = $res->fetch())
					{
						$filterEvent = [
							'LOGIC' => 'OR',
							$filterEvent,
							[
								'=APP_ID' => '',
								'=APPLICATION_TOKEN' => $bot['APP_ID'],
							]
						];
						Bot::unRegister(
							[
								'BOT_ID' => $integration['BOT_ID'],
								'MODULE_ID' => 'rest'
							]
						);
					}
				}
				if (($integration['PASSWORD_ID'] > 0) && !static::deleteWebHook($integration['PASSWORD_ID']))
				{
					$errorList[] = Loc::getMessage('INTEGRATION_PRESET_PROVIDER_ERROR_DELETE_WEBHOOK');
				}
				$resEvent = EventTable::getList(
					[
						'filter' => $filterEvent,
						'select' => [
							'ID'
						]
					]
				);
				while ($event = $resEvent->fetch())
				{
					$res = EventTable::delete($event['ID']);
					if (!$res->isSuccess())
					{
						$errorList[] = $res->getErrorMessages();
					}
				}
				$res = AppTable::getList(
					[
						'filter' => [
							'=ID' => $integration['APP_ID']
						],
						'select' => [
							'ID'
						]
					]
				);
				if ($app = $res->fetch())
				{
					$resPlacement = PlacementTable::getList(
						[
							'filter' => [
								'=APP_ID' => $app['ID']
							],
							'select' => [
								'ID'
							]
						]
					);
					while ($placement = $resPlacement->fetch())
					{
						$res = PlacementTable::delete($placement['ID']);
						if (!$res->isSuccess())
						{
							$errorList[] = $res->getErrorMessages();
						}
					}
					$res = AppTable::delete($app['ID']);
					if (!$res->isSuccess())
					{
						$errorList[] = $res->getErrorMessages();
					}
				}

				if (empty($errorList))
				{
					$res = IntegrationTable::delete($integration['ID']);
					if (!$res->isSuccess())
					{
						$errorList[] = $res->getErrorMessages();
					}
				}
			}
			else
			{
				$errorList[] = Loc::getMessage('INTEGRATION_PRESET_PROVIDER_ERROR_ACCESS_DENIED');
			}
		}

		if (!empty($errorList))
		{
			$result['result'] = 'error';
			$result['errors'] = $errorList;
		}

		\Bitrix\Rest\Engine\Access::getActiveEntity(true);

		return $result;
	}

	/**
	 * @param $id
	 *
	 * @return array
	 * @throws SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 */
	public static function getIntegration($id)
	{
		$id = intVal($id);
		$return = [];
		$runtime = [
			new ReferenceField(
				'APPLICATION_DATA',
				AppTable::class,
				[
					'=ref.ID' => 'this.APP_ID'
				],
				[
					'join_type' => 'LEFT'
				]
			),
			new ReferenceField(
				'PASSWORD_DATA',
				PasswordTable::class,
				[
					'=ref.ID' => 'this.PASSWORD_ID'
				],
				[
					'join_type' => 'LEFT'
				]
			),
		];
		$select = [
			'*',
			'APPLICATION_DATA_' => 'APPLICATION_DATA',
			'PASSWORD_DATA_' => 'PASSWORD_DATA'
		];
		if (Loader::includeModule('im'))
		{
			$runtime[] = new ReferenceField(
				'BOT_DATA',
				BotTable::class,
				[
					'=ref.BOT_ID' => 'this.BOT_ID'
				],
				[
					'join_type' => 'LEFT'
				]
			);
			$select['BOT_DATA_'] = 'BOT_DATA';

			$runtime[] = new ReferenceField(
				'BOT_ACCOUNT',
				UserTable::class,
				[
					'=ref.ID' => 'this.BOT_ID'
				],
				[
					'join_type' => 'LEFT'
				]
			);
			$select['BOT_ACCOUNT_NAME'] = 'BOT_ACCOUNT.NAME';
		}

		$res = IntegrationTable::getList(
			[
				'filter' => [
					'=ID' => $id
				],
				'limit' => 1,
				'select' => $select,
				'runtime' => $runtime
			]
		);
		if ($item = $res->fetch())
		{
			if ($item['PASSWORD_DATA_ID'] > 0)
			{
				$item['PASSWORD_DATA_URL'] = \CRestUtil::getWebhookEndpoint(
					$item['PASSWORD_DATA_PASSWORD'],
					$item['PASSWORD_DATA_USER_ID']
				);
			}

			if (!empty($item['BOT_DATA_APP_ID']) && mb_strpos($item['BOT_DATA_APP_ID'], 'custom') === 0)
			{
				//clear 'custom' prefix only for webhook bot
				$item['BOT_DATA_APP_ID'] = mb_substr($item['BOT_DATA_APP_ID'], 6);
			}

			if ($item['APP_ID'] > 0 && $item['APPLICATION_ONLY_API'] == 'N')
			{
				$resLang = AppLangTable::getList(
					[
						'filter' => [
							'=APP_ID' => $item['APP_ID']
						]
					]
				);
				while ($lang = $resLang->fetch())
				{
					$item['APPLICATION_LANG_DATA'][$lang['LANGUAGE_ID']] = $lang['MENU_NAME'];
				}
			}

			if ($item['APP_ID'] > 0 && !empty($item['WIDGET_LIST']))
			{
				$resLang = PlacementTable::getList(
					[
						'filter' => [
							'=APP_ID' => $item['APP_ID'],
						],
						'select' => [
							'ID',
							'LANG_ALL',
						],
					]
				);
				foreach ($resLang->fetchCollection() as $placement)
				{
					if (!is_null($placement->getLangAll()))
					{
						foreach ($placement->getLangAll() as $lang)
						{
							$item['WIDGET_LANG_LIST'][$lang->getLanguageId()] = [
								'TITLE' => $lang->getTitle(),
								'DESCRIPTION' => $lang->getDescription(),
								'GROUP_NAME' => $lang->getGroupName(),
							];
						}
					}
				}
			}

			$return = $item;
		}

		return $return;
	}

	/**
	 * @param $requestData
	 * @param string $elementCode
	 * @param int $id
	 *
	 * @return array
	 * @throws SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 */
	public static function saveIntegration($requestData, $elementCode = '', $id = 0)
	{
		global $USER;
		$result = [
			'status' => true,
		];
		$itemsEvent = [];
		$errorList = [];
		$id = (intVal($requestData['ID']) > 0) ? intVal($requestData['ID']) : $id;
		$userId = $GLOBALS['USER']->getID();
		$isAdmin = \CRestUtil::isAdmin();

		$presetData = Element::get($elementCode);

		if (
			!$isAdmin
			&&
			(
				$presetData['ADMIN_ONLY'] === 'Y'
				|| $presetData['OPTIONS']['WIDGET_NEEDED'] !== 'D'
				|| $presetData['OPTIONS']['APPLICATION_NEEDED'] !== 'D'
			)
		)
		{
			$result['status'] = false;
			$result['errors'][] = Loc::getMessage('INTEGRATION_PRESET_PROVIDER_ERROR_ACCESS_DENIED');
			return $result;
		}

		if (!OAuthService::getEngine()->isRegistered())
		{
			try
			{
				OAuthService::register();
				OAuthService::getEngine()->getClient()->getApplicationList();
			}
			catch (SystemException $e)
			{
				$result['status'] = false;
				$result['errors'][] = $e->getCode() . ': ' . $e->getMessage();
				return $result;
			}
		}

		$presetData = $presetData['OPTIONS'];

		$saveData = [
			'ELEMENT_CODE' => $elementCode,
			'USER_ID' => $USER->GetID(),
			'TITLE' => $requestData['TITLE'],
			'SCOPE' => is_array($requestData['SCOPE']) ? $requestData['SCOPE'] : [],
			'QUERY' => $requestData['QUERY'],
			'OUTGOING_HANDLER_URL' => trim($requestData['OUTGOING_HANDLER_URL']),
			'OUTGOING_EVENTS' => is_array($requestData['OUTGOING_EVENTS']) ? $requestData['OUTGOING_EVENTS'] : [],
			'APPLICATION_ONLY_API' => ($requestData['APPLICATION_ONLY_API'] === 'Y') ? 'Y' : 'N',
			'APPLICATION_NEEDED' => ($requestData['APPLICATION_NEEDED'] === 'Y') ? 'Y' : 'N',
			'APPLICATION_EVENTS' => is_array($requestData['APPLICATION_EVENTS']) ? $requestData['APPLICATION_EVENTS'] : [],
			'OUTGOING_NEEDED' => ($requestData['OUTGOING_NEEDED'] === 'Y') ? 'Y' : 'N',
			'WIDGET_NEEDED' => ($requestData['WIDGET_NEEDED'] === 'Y') ? 'Y' : 'N',
			'WIDGET_HANDLER_URL' => trim($requestData['WIDGET_HANDLER_URL']),
			'WIDGET_LIST' => $requestData['WIDGET_LIST'],
			'WIDGET_LANG_LIST' => is_array($requestData['WIDGET_LANG_LIST']) ? $requestData['WIDGET_LANG_LIST'] : [],
			'BOT_HANDLER_URL' => trim($requestData['BOT_HANDLER_URL'])
		];

		if ($id > 0)
		{
			$itemsEvent = EventTable::getList(
				[
					'filter' => [
						'=INTEGRATION_ID' => $id
					]
				]
			)->fetchAll();
		}

		if (!empty($itemsEvent['0']['APPLICATION_TOKEN']))
		{
			$saveData['APPLICATION_TOKEN'] = $itemsEvent['0']['APPLICATION_TOKEN'];
		}
		else
		{
			$saveData['APPLICATION_TOKEN'] = Random::getString(32);
		}

		$isAdd = false;
		if ($id == 0)
		{
			$saveResult = IntegrationTable::add($saveData);
			$isAdd = true;
			$id = $saveResult->getId();
			if (!$result['status'] = $saveResult->isSuccess())
			{
				$errorList = $saveResult->getErrorMessages();
			}
		}

		if ($id > 0 || $result['status'])
		{
			EventController::disableEvents();
			if (!empty($saveData['TITLE']))
			{
				$title = $saveData['TITLE'];
			}
			else
			{
				$title = Loc::getMessage('INTEGRATION_PRESET_PROVIDER_TITLE_PREFIX', ['#ID#' => $id]);
			}

			if (!$isAdd)
			{
				$resIntegration = IntegrationTable::getList(
					[
						'filter' => [
							'ID' => $id
						]
					]
				);
				if ($integrationData = $resIntegration->fetch())
				{
					if (!$isAdmin && $integrationData['USER_ID'] != $userId)
					{
						$result['status'] = false;
						$result['errors'][] = Loc::getMessage('INTEGRATION_PRESET_PROVIDER_ERROR_ACCESS_DENIED');
						return $result;
					}
					if ($integrationData['PASSWORD_ID'] > 0 && ($requestData['MODE'] === 'GEN_SAVE' || $integrationData['USER_ID'] != $userId))
					{
						Analytic::logToFile(
							'integrationRegen',
							'integration' . $integrationData['ID'],
							$integrationData['ELEMENT_CODE'],
							'code'
						);
						if (static::deleteWebHook($integrationData['PASSWORD_ID']))
						{
							$integrationData['PASSWORD_ID'] = 0;
						}
						else
						{
							$result['status'] = false;
							$errorList[] = Loc::getMessage('INTEGRATION_PRESET_PROVIDER_ERROR_DELETE_WEBHOOK');
							return $result;
						}
					}

					$saveData = array_merge($integrationData, $saveData);
				}
				//bot on webhooks
				if ($presetData['BOT_NEEDED'] !== 'D' && Loader::includeModule('im'))
				{
					if (!empty($requestData['BOT_NAME']) && !empty($requestData['BOT_HANDLER_URL']))
					{
						$botId = 0;
						$allEvents = [
							'onImBotMessageAdd',
							'onImBotJoinChat',
							'onImBotDelete',
							'onImBotMessageUpdate',
							'onImBotMessageDelete'
						];
						$params = [
							'TYPE' => $requestData['BOT_TYPE'],
							'EVENT_MESSAGE_ADD' => $saveData['BOT_HANDLER_URL'],
							'EVENT_WELCOME_MESSAGE' => $saveData['BOT_HANDLER_URL'],
							'EVENT_BOT_DELETE' => $saveData['BOT_HANDLER_URL'],
							'MODULE_ID' => 'rest',
							'PROPERTIES' => [
								'NAME' => $requestData['BOT_NAME']
							]
						];

						if ($integrationData['BOT_ID'] > 0)
						{
							$res = BotTable::getList(
								[
									'filter' => [
										'=BOT_ID' => $integrationData['BOT_ID'],
									]
								]
							);
							if ($bot = $res->fetch())
							{
								$params = array_merge($bot, $params);
							}
						}
						$clientId = $params['APP_ID'];
						if (!$params['APP_ID'])
						{
							$clientId = Random::getString(32);
							$params['APP_ID'] = 'custom' . $clientId;
							$params['CODE'] = Random::getString(16);
						}
						elseif (mb_stripos($clientId, 'custom') === 0)
						{
							$clientId = mb_substr($clientId, 6);
						}

						$uriWithClientId = $saveData['BOT_HANDLER_URL']
							. (mb_strpos($saveData['BOT_HANDLER_URL'], '?') === false ? '?' : '&')
							. 'CLIENT_ID=' . $clientId;
						$events = [
							'onImBotMessageAdd' => $uriWithClientId,
							'onImBotJoinChat' => $uriWithClientId,
							'onImBotDelete' => $uriWithClientId,
						];
						if (in_array($requestData['BOT_TYPE'], ['S', 'O']))
						{
							$events['onImBotMessageUpdate'] = $uriWithClientId;
							$events['onImBotMessageDelete'] = $uriWithClientId;
							$params['EVENT_MESSAGE_UPDATE'] = $uriWithClientId;
							$params['EVENT_MESSAGE_DELETE'] = $uriWithClientId;
						}

						if ($integrationData['BOT_ID'] > 0)
						{
							if (Bot::update(['BOT_ID' => $integrationData['BOT_ID']], $params) ===	true)
							{
								$botId = $integrationData['BOT_ID'];
							}
						}
						else
						{
							$botId = Bot::register($params);
						}

						if ($botId > 0)
						{
							$allEvents = array_change_key_case(array_combine($allEvents, $allEvents), CASE_UPPER);
							$saveData['BOT_ID'] = $botId;
							if ($integrationData['BOT_ID'] > 0)
							{
								$res = EventTable::getList(
									[
										'filter' => [
											'=APP_ID' => '',
											'=EVENT_NAME' => array_keys($allEvents),
											'=APPLICATION_TOKEN' => [
												$clientId,
												$params['APP_ID'],
											],
										],
										'select' => [
											'ID',
											'EVENT_NAME',
											'EVENT_HANDLER'
										]
									]
								);
								while ($event = $res->fetch())
								{
									if (isset($events[$allEvents[$event['EVENT_NAME']]]) &&
										$event['EVENT_HANDLER'] === $events[$allEvents[$event['EVENT_NAME']]])
									{
										unset($events[$allEvents[$event['EVENT_NAME']]]);
									}
									else
									{
										EventTable::delete($event['ID']);
									}
								}
							}
							if ($params['APP_ID'])
							{
								foreach ($events as $event => $eventHandler)
								{
									$res = EventTable::add(
										[
											'APP_ID' => '',
											'EVENT_NAME' => toUpper($event),
											'EVENT_HANDLER' => $eventHandler,
											'APPLICATION_TOKEN' => $clientId,
											'USER_ID' => 0,
										]
									);
									if ($result['status'] = $res->isSuccess())
									{
										Sender::bind('im', $event);
									}
									else
									{
										$errors = $res->getErrorMessages();
										if (is_array($errors))
										{
											$errorList = array_merge($errorList, $errors);
										}
										break;
									}
								}
							}
						}
						else
						{
							$errorList[] = Loc::getMessage('INTEGRATION_PRESET_PROVIDER_ERROR_BOT_CREATE');
						}
					}
					else
					{
						$errorList[] = Loc::getMessage(
							'INTEGRATION_PRESET_PROVIDER_ERROR_BOT_REQUIRED_FIELD_EMPTY'
						);
					}
				}
			}

			if ($presetData['QUERY_NEEDED'] !== 'D')
			{
				$webhook = static::getWebHook($saveData['SCOPE'], $saveData['PASSWORD_ID'], $title);
				$saveData['PASSWORD_ID'] = $webhook['ID'];
			}

			if (
				$presetData['OUTGOING_NEEDED'] !== 'D'
				&& !empty($saveData['OUTGOING_HANDLER_URL'])
				&& !empty($saveData['OUTGOING_EVENTS'])
				&& $saveData['OUTGOING_NEEDED'] == 'Y'
			)
			{
				$eventAddList = [];
				$eventUpdateList = [];
				if (!empty($itemsEvent))
				{
					$itemsEvent = array_column($itemsEvent, null, 'EVENT_NAME');
				}

				foreach ($saveData['OUTGOING_EVENTS'] as $event)
				{
					if ($itemsEvent[$event])
					{
						$eventUpdateList[] = $itemsEvent[$event]['ID'];
						unset($itemsEvent[$event]);
					}
					else
					{
						$eventAddList[] = [
							'TITLE' => $title,
							'EVENT_NAME' => $event,
							'EVENT_HANDLER' => $saveData['OUTGOING_HANDLER_URL'],
							'USER_ID' => $userId,
							'DATE_CREATE' => new DateTime(),
							'APPLICATION_TOKEN' => $saveData['APPLICATION_TOKEN'],
							'INTEGRATION_ID' => $saveData['ID']
						];
					}
				}

				foreach ($itemsEvent as $event)
				{
					EventTable::delete($event['ID']);
				}

				foreach ($eventAddList as $item)
				{
					$res = EventTable::add($item);
					if (!$result['status'] = $res->isSuccess())
					{
						$errors = $res->getErrorMessages();
						if (is_array($errors))
						{
							$errorList = array_merge($errorList, $errors);
						}
						break;
					}
				}

				if ($result['status'] && !empty($eventUpdateList))
				{
					$res = EventTable::updateMulti(
						$eventUpdateList,
						[
							'TITLE' => $title,
							'EVENT_HANDLER' => $saveData['OUTGOING_HANDLER_URL'],
							'USER_ID' => $userId,
							'APPLICATION_TOKEN' => $saveData['APPLICATION_TOKEN'],
						]
					);
					if (!$result['status'] = $res->isSuccess())
					{
						$errors = $res->getErrorMessages();
						if (is_array($errors))
						{
							$errorList = array_merge($errorList, $errors);
						}
					}
				}
			}
			else
			{
				foreach ($itemsEvent as $event)
				{
					EventTable::delete($event['ID']);
				}
			}

			if (
				$presetData['WIDGET_NEEDED'] !== 'D'
				&& $saveData['WIDGET_NEEDED'] == 'Y'
				&& !empty($saveData['WIDGET_HANDLER_URL'])
				&& is_array($saveData['WIDGET_LIST'])
			)
			{
				$app = static::saveApp(
					[
						'ID' => $saveData['APP_ID'],
						'FIELDS' => [
							'URL' => $saveData['WIDGET_HANDLER_URL'],
							'URL_INSTALL' => $saveData['WIDGET_HANDLER_URL'],
							'SCOPE' => $saveData['SCOPE'],
							'ONLY_API' => 'Y',
							'MOBILE' => 'N',
							'APP_NAME' => $saveData['TITLE'],
						],
						'PLACEMENTS' => $saveData['WIDGET_LIST'],
						'PLACEMENTS_LANG_LIST' => $saveData['WIDGET_LANG_LIST'],
						'PLACEMENT_HANDLER_URL' => $saveData['WIDGET_HANDLER_URL'],
						'INTEGRATION_CODE' => $saveData['ELEMENT_CODE'],
						'INTEGRATION_ID' => $saveData['ID']
					]
				);
				if ($app['ID'] > 0)
				{
					$saveData['APP_ID'] = $app['ID'];
				}

				if (!empty($app['errors']))
				{
					$errorList = array_merge($errorList, $app['errors']);
				}
			}
			elseif (
				$presetData['APPLICATION_NEEDED'] !== 'D'
				&& $saveData['APPLICATION_NEEDED'] == 'Y'
			)
			{
				$app = static::saveApp(
					[
						'ID' => $saveData['APP_ID'],
						'FIELDS' => [
							'URL' => trim($requestData['APPLICATION_URL_HANDLER']),
							'URL_INSTALL' => trim($requestData['APPLICATION_URL_INSTALL']),
							'SCOPE' => $saveData['SCOPE'],
							'ONLY_API' => ($saveData['APPLICATION_ONLY_API'] == 'Y') ? 'Y' : 'N',
							'MOBILE' => ($saveData['APPLICATION_ONLY_API'] != 'Y'
								&& $requestData['APPLICATION_MOBILE'] === 'Y') ? 'Y' : 'N',
							'APP_NAME' => $saveData['TITLE'],
						],
						'LANG_NAME' => ($saveData['APPLICATION_ONLY_API'] != 'Y' && is_array($requestData['APPLICATION_LANG_NAME'])) ?
											$requestData['APPLICATION_LANG_NAME']
											:
											[],
						'INTEGRATION_CODE' => $saveData['ELEMENT_CODE'],
						'INTEGRATION_ID' => $saveData['ID']
					]
				);

				if ($app['ID'] > 0)
				{
					$saveData['APP_ID'] = $app['ID'];
				}

				if (!empty($app['errors']))
				{
					$errorList = array_merge($errorList, $app['errors']);
				}
			}
			elseif ($saveData['APP_ID'] > 0)
			{
				$res = AppTable::getList(
					[
						'filter' => [
							'=ID' => $saveData['APP_ID']
						]
					]
				);
				if ($app = $res->fetch())
				{
					AppTable::delete($app['ID']);
				}
				$placementList = PlacementTable::getList(
					[
						'filter' => [
							'=APP_ID' => $app['ID']
						]
					]
				)->fetchAll();
				if (is_array($placementList))
				{
					foreach ($placementList as $placement)
					{
						PlacementTable::delete($placement['ID']);
					}
				}
			}

			$resultSave = IntegrationTable::update($id, $saveData);
			if (!$resultSave->isSuccess())
			{
				$errors = $resultSave->getErrorMessages();
				if (is_array($errors))
				{
					$errorList = array_merge($errorList, $errors);
				}
			}

			EventController::enableEvents();
		}

		$result['ID'] = $id;
		if (!empty($errorList))
		{
			$result['status'] = false;
			$result['errors'] = $errorList;
		}
		\Bitrix\Rest\Engine\Access::getActiveEntity(true);

		return $result;
	}

	private static function saveApp($data)
	{
		$app = [];
		$return = [];
		$errorList = [];
		if ($data['ID'] > 0)
		{
			$dbApp = AppTable::getList(
				[
					'filter' => [
						'=ID' => $data['ID'],
					],
					'limit' => 1
				]
			);
			if ($app = $dbApp->fetch())
			{
				$data['FIELDS'] = array_merge($app, $data['FIELDS']);
			}
		}

		if (count($data['FIELDS']['SCOPE']) <= 0)
		{
			$errorList[] = Loc::getMessage('INTEGRATION_PRESET_PROVIDER_ERROR_EMPTY_SCOPE');
		}

		if (empty($errorList))
		{
			foreach (GetModuleEvents('rest', 'OnRestLocalAppSave', true) as $eventHandler)
			{
				$eventResult = ExecuteModuleEventEx($eventHandler, [$app, &$data['FIELDS']]);
				if ($eventResult !== null)
				{
					$errorList[] = strip_tags($eventResult);
				}
			}
		}

		if (empty($errorList) && empty($data['FIELDS']['URL']))
		{
			$errorList[] = Loc::getMessage('INTEGRATION_PRESET_PROVIDER_ERROR_INCORRECT_URL');
		}

		if (empty($errorList))
		{
			try
			{
				$appFields = [
					'URL' => $data['FIELDS']['URL'],
					'URL_INSTALL' => $data['FIELDS']['URL_INSTALL'],
					'CLIENT_ID' => $data['FIELDS']['CLIENT_ID'],
					'CODE' => $data['FIELDS']['CLIENT_ID'],
					'SCOPE' => implode(',', $data['FIELDS']['SCOPE']),
					'STATUS' => AppTable::STATUS_LOCAL,
					'APP_NAME' => $data['FIELDS']['APP_NAME'],
					'MOBILE' => $data['FIELDS']['MOBILE'],
				];
				if ($app['ID'] > 0)
				{
					$result = AppTable::update($app['ID'], $appFields);
				}
				else
				{
					$appFields['INSTALLED'] = (!empty($data['FIELDS']['URL_INSTALL'])
						&& $data['FIELDS']['ONLY_API'] !== 'Y') ? AppTable::NOT_INSTALLED : AppTable::INSTALLED;
					$result = AppTable::add($appFields);
					if ($result->isSuccess())
					{
						Analytic::logToFile(
							'integrationAppCreated',
							'integration' . $data['INTEGRATION_ID'],
							$data['INTEGRATION_CODE'],
							'code'
						);
					}
				}

				if ($result->isSuccess())
				{
					$return['ID'] = $result->getId();

					AppLangTable::deleteByApp($return['ID']);
					if ($data['FIELDS']['ONLY_API'] === 'N')
					{
						foreach ($data['LANG_NAME'] as $lang => $name)
						{
							AppLangTable::add(
								[
									'APP_ID' => $return['ID'],
									'LANGUAGE_ID' => $lang,
									'MENU_NAME' => $name
								]
							);
						}
					}
					else
					{
						if (
							$data['FIELDS']['ONLY_API'] === 'Y'
							&& !empty($app['URL_INSTALL'])
							&& $data['FIELDS']['URL_INSTALL'] != $app['URL_INSTALL']
						)
						{
							$event = EventTable::getList(
								[
									'filter' => [
										'=APP_ID' => $return['ID'],
										'=EVENT_NAME' => 'ONAPPINSTALL',
									],
									'limit' => 1
								]
							);
							if ($eventInstall = $event->fetch())
							{
								// checkCallback is already called inside checkFields
								$result = EventTable::update(
									$eventInstall['ID'],
									[
										'APP_ID' => $return['ID'],
										'EVENT_NAME' => 'ONAPPINSTALL',
										'EVENT_HANDLER' => $data['FIELDS']['URL_INSTALL'],
									]
								);
							}
						}

						if (!empty($data['FIELDS']['URL_INSTALL']) && empty($app['URL_INSTALL']))
						{
							// checkCallback is already called inside checkFields
							$result = EventTable::add(
								[
									'APP_ID' => $return['ID'],
									'EVENT_NAME' => 'ONAPPINSTALL',
									'EVENT_HANDLER' => $data['FIELDS']['URL_INSTALL'],
								]
							);
							if ($result->isSuccess())
							{
								Sender::bind('rest', 'OnRestAppInstall');
							}
						}

						if ($app['ID'] <= 0)
						{
							AppTable::install($return['ID']);
						}
					}

					if (defined('BX_COMP_MANAGED_CACHE'))
					{
						global $CACHE_MANAGER;
						$CACHE_MANAGER->ClearByTag('sonet_group');
					}
				}
				else
				{
					$errorList = $result->getErrorMessages();
				}
			}
			catch (\Bitrix\Rest\OAuthException $e)
			{
				$errorList[] = $e->getMessage();
			}

			if (empty($errorList) && $data['PLACEMENTS'])
			{
				$title = '';
				$placementListOld = [];
				$updateIDList = [];
				$addList = [];
				$data['PLACEMENTS'] = is_array($data['PLACEMENTS']) ? $data['PLACEMENTS'] : [];

				$placementLangList = [];
				foreach ($data['PLACEMENTS_LANG_LIST'] as $lang => $fields)
				{
					if (!empty($fields['TITLE']))
					{
						$placementLangList[$lang] = [
							'LANGUAGE_ID' => $lang,
							'TITLE' => $fields['TITLE'],
						];
					}
				}

				$langList = Lang::listLanguage();
				$defaultLang = $langList[0];
				if (!empty($placementLangList))
				{
					foreach ($langList as $lang)
					{
						if (isset($placementLangList[$lang]))
						{
							$title = $placementLangList[$lang]['TITLE'];
							break;
						}
					}
				}

				if ($title === '')
				{
					$title = $data['APP_NAME'];
					$placementLangList[$defaultLang] = [
						'LANGUAGE_ID' => $defaultLang,
						'TITLE' => $title,
					];
				}

				$accessPlacement = Rest::getAccessPlacement($data['FIELDS']['SCOPE']);
				$placementRes = PlacementTable::getList(
					[
						'filter' => [
							'=APP_ID' => $return['ID']
						]
					]
				);
				if ($placementList = $placementRes->fetchAll())
				{
					$placementListOld = array_column($placementList, 'PLACEMENT', 'ID');
				}

				foreach ($data['PLACEMENTS'] as $placement)
				{
					if (!in_array($placement, $accessPlacement))
					{
						$errorList[] = Loc::getMessage(
							"INTEGRATION_PRESET_PROVIDER_ERROR_ACCESS_PLACEMENT",
							[
								'#PLACEMENT_CODE#' => $placement
							]
						) ;
						continue;
					}

					$key = array_search($placement, $placementListOld);
					if ($key !== false)
					{
						$updateIDList[] = $key;
						unset($placementListOld[$key]);
					}
					else
					{
						$addList[] = [
							'APP_ID' => $return['ID'],
							'PLACEMENT' => $placement,
							'PLACEMENT_HANDLER' => $data['PLACEMENT_HANDLER_URL'],
							'TITLE' => $title,
						];
					}
				}
				if (!empty($placementListOld))
				{
					foreach (array_keys($placementListOld) as $place)
					{
						PlacementTable::delete($place);
					}
				}

				if (!empty($updateIDList))
				{
					$resultPlacementBind = PlacementTable::updateMulti(
						$updateIDList,
						[
							'PLACEMENT_HANDLER' => $data['PLACEMENT_HANDLER_URL'],
							'TITLE' => $title,
						]
					);
					if ($resultPlacementBind->isSuccess())
					{
						foreach ($updateIDList as $id)
						{
							PlacementLangTable::deleteByPlacement((int) $id);
							foreach ($placementLangList as $fields)
							{
								$fields['PLACEMENT_ID'] = $id;
								$resultPlacementLang = PlacementLangTable::add($fields);
								if (!$resultPlacementLang->isSuccess())
								{
									$errors = $resultPlacementLang->getErrorMessages();
									if (is_array($errors))
									{
										$errorList = array_merge($errorList, $errors);
									}
								}
							}
						}
					}
					else
					{
						$errors = $resultPlacementBind->getErrorMessages();
						if (is_array($errors))
						{
							$errorList = array_merge($errorList, $errors);
						}
					}
				}

				if (count($addList) > 0)
				{
					Analytic::logToFile(
						'integrationPlacementCreated',
						'integration' . $data['INTEGRATION_ID'],
						$data['INTEGRATION_CODE'],
						'integrationCode'
					);
					foreach ($addList as $item)
					{
						$resultPlacementBind = PlacementTable::add($item);
						if ($resultPlacementBind->isSuccess())
						{
							$id = (int) $resultPlacementBind->getId();
							foreach ($placementLangList as $fields)
							{
								$fields['PLACEMENT_ID'] = $id;
								$resultPlacementLang = PlacementLangTable::add($fields);
								if (!$resultPlacementLang->isSuccess())
								{
									$errors = $resultPlacementLang->getErrorMessages();
									if (is_array($errors))
									{
										$errorList = array_merge($errorList, $errors);
									}
								}
							}
							Analytic::logToFile(
								'integrationPlacementCreated',
								'integration' . $data['INTEGRATION_ID'],
								$item['PLACEMENT'],
								'placementCode'
							);
						}
						else
						{
							$errors = $resultPlacementBind->getErrorMessages();
							if (is_array($errors))
							{
								$errorList = array_merge($errorList, $errors);
							}
						}
					}
				}
			}
		}

		if (!empty($errorList))
		{
			$return['errors'] = $errorList;
		}

		return $return;
	}

	private static function getWebHook($scopeList = [], $id = 0, $title = '')
	{
		$password = [];
		$id = intVal($id);
		$scopeList = is_array($scopeList) ? $scopeList : [];
		if ($id !== 0)
		{
			$passData = PasswordTable::getList(
				[
					'filter' => [
						'=ID' => $id,
					],
					'select' => [
						'PASSWORD',
						'USER_ID',
						'TITLE',
						'ID'
					],
					'limit' => 1
				]
			);
			if ($passwordData = $passData->fetch())
			{
				$scopeListOld = [];
				$scopeIdList = [];
				$permData = PermissionTable::getList(
					[
						'filter' => [
							'=PASSWORD_ID' => $passwordData['ID']
						]
					]
				);
				while ($scopeItem = $permData->fetch())
				{
					$scopeIdList[$scopeItem['PERM']] = $scopeItem['ID'];
					$scopeListOld[] = $scopeItem['PERM'];
				}
				$resultList = array_diff($scopeList, $scopeListOld);
				foreach ($resultList as $scope)
				{
					PermissionTable::add(
						[
							'PASSWORD_ID' => $passwordData['ID'],
							'PERM' => $scope,
						]
					);
				}
				$resultList = array_diff($scopeListOld, $scopeList);
				foreach ($resultList as $scope)
				{
					if (isset($scopeIdList[$scope]))
					{
						PermissionTable::delete($scopeIdList[$scope]);
					}
				}

				if ($title !== '' && $passwordData['TITLE'] !== $title)
				{
					PasswordTable::update(
						$passwordData['ID'],
						[
							'TITLE' => $title
						]
					);
				}

				$password = $passwordData;
			}
		}

		if (empty($password))
		{
			$userId = $GLOBALS['USER']->GetID();
			$passwordCreat = PasswordTable::createPassword(
				$userId,
				$scopeList,
				$title,
				true
			);
			if ($passwordCreat !== false)
			{
				$password = $passwordCreat;
			}
		}
		if (!empty($password['PASSWORD']))
		{
			$password['URL'] = \CRestUtil::getWebhookEndpoint($password['PASSWORD'], $password['USER_ID']);
		}

		return $password;
	}

	private static function deleteWebHook($id)
	{
		$result = false;
		$passData = PasswordTable::getList(
			[
				'filter' => [
					'=ID' => $id,
				],
				'select' => [
					'PASSWORD',
					'USER_ID',
					'ID'
				],
				'limit' => 1
			]
		);
		if ($passwordData = $passData->fetch())
		{
			$permData = PermissionTable::getList(
				[
					'filter' => [
						'=PASSWORD_ID' => $passwordData['ID']
					],
					'select' => [
						'ID'
					]
				]
			);
			while ($scopeItem = $permData->fetch())
			{
				PermissionTable::delete($scopeItem['ID']);
			}
			$deleteResult = PasswordTable::delete($passwordData['ID']);
			if ($deleteResult->isSuccess())
			{
				$result = true;
			}
		}
		return $result;
	}
}