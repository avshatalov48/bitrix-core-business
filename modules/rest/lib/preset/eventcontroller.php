<?php

namespace Bitrix\Rest\Preset;

use Bitrix\Main\Event;
use Bitrix\Rest\AppTable;
use Bitrix\Rest\EventTable;
use Bitrix\Rest\Preset\Data\Element;

/**
 * Class EventController
 * @package Bitrix\Rest\Preset
 */
class EventController
{
	private static $skipMode = false;
	private static $tmpAppList = [];
	private static $tmpUsesLangAppList = [];
	private static $tmpApList = [];
	private static $tmpApPermissionList = [];

	/**
	 * Turn off all event in class.
	 * @return bool
	 */
	public static function disableEvents()
	{
		static::$skipMode = true;
		return true;
	}

	/**
	 * Turn on all event in class.
	 * @return bool
	 */
	public static function enableEvents()
	{
		static::$skipMode = false;
		return true;
	}

	/**
	 * Event on after add application. Create integration for external create local application.
	 *
	 * @param Event $event
	 */
	public static function onAddApp(Event $event)
	{
		if (!static::$skipMode)
		{
			$id = intVal($event->getParameter('id'));
			$fields = $event->getParameter('fields');
			if ($id > 0 && $fields['STATUS'] == AppTable::STATUS_LOCAL)
			{
				$scope = explode(',', $fields['SCOPE']);
				$result = IntegrationTable::add(
					[
						'ELEMENT_CODE' => Element::DEFAULT_APPLICATION,
						'TITLE' => $fields['APP_NAME'],
						'APP_ID' => $fields['APP_ID'],
						'SCOPE' => $scope,
						'APPLICATION_ONLY_API' => 'Y',
						'APPLICATION_NEEDED' => 'Y',
						'QUERY_NEEDED' => 'N',
						'OUTGOING_NEEDED' => 'N',
						'WIDGET_NEEDED' => 'N',
						'BOT_NEEDED' => 'N',
					]
				);

				if ($result->isSuccess())
				{
					$integrationID = $result->getId();
					static::$tmpAppList[$id] = $integrationID;
				}
			}
		}
	}

	/**
	 * Event on after add application lang. Create integration for external create local application lang.
	 *
	 * @param Event $event
	 */
	public static function onAddAppLang(Event $event)
	{
		if (!static::$skipMode)
		{
			$id = intVal($event->getParameter('id'));
			$fields = $event->getParameter('fields');
			if ($id > 0 && !empty(static::$tmpAppList[$fields['APP_ID']]) && !isset(static::$tmpUsesLangAppList[$fields['APP_ID']]))
			{
				static::$tmpUsesLangAppList[$fields['APP_ID']] = $fields['LANGUAGE_ID'];
				try
				{
					$integrationId = static::$tmpAppList[$fields['APP_ID']];
					IntegrationTable::update(
						$integrationId,
						[
							'APPLICATION_ONLY_API' => 'N',
						]
					);
				}
				catch (\Exception $e)
				{
				}
			}
		}
	}

	/**
	 * Event on after add out-webhook. Create integration for external create out-webhook.
	 *
	 * @param Event $event
	 */
	public static function onAfterAddEvent(Event $event)
	{
		if (!static::$skipMode)
		{
			$id = intVal($event->getParameter('id'));
			$fields = $event->getParameter('fields');
			if ($id > 0 && !$fields['APP_ID'] > 0 && !$fields['INTEGRATION_ID'] > 0)
			{
				$result = IntegrationTable::add(
					[
						'ELEMENT_CODE' => Element::DEFAULT_OUT_WEBHOOK,
						'TITLE' => $fields['TITLE'],
						'USER_ID' => $fields['USER_ID'],
						'APPLICATION_TOKEN' => $fields['APPLICATION_TOKEN'],
						'OUTGOING_EVENTS' => [
							$fields['EVENT_NAME']
						],
						'OUTGOING_HANDLER_URL' => $fields['EVENT_HANDLER'],
						'APPLICATION_NEEDED' => 'N',
						'QUERY_NEEDED' => 'N',
						'OUTGOING_NEEDED' => 'Y',
						'WIDGET_NEEDED' => 'N',
						'BOT_NEEDED' => 'N',
					]
				);

				if ($result->isSuccess())
				{
					EventTable::update(
						$result->getId(),
						[
							'INTEGRATION_ID' => $result->getId()
						]
					);
				}
			}
		}
	}

	/**
	 * Event on after add webhook. Create integration for external create webhook.
	 *
	 * @param Event $event
	 */
	public static function onAfterAddAp(Event $event)
	{
		if (!static::$skipMode)
		{
			$id = intVal($event->getParameter('id'));
			if ($id > 0)
			{
				/** @var array $fields */
				$fields = $event->getParameter('fields');
				$result = IntegrationTable::add(
					[
						'ELEMENT_CODE' => Element::DEFAULT_IN_WEBHOOK,
						'TITLE' => $fields['TITLE'],
						'PASSWORD_ID' => $id,
						'USER_ID' => $fields['USER_ID'],
						'SCOPE' => [],
						'APPLICATION_NEEDED' => 'N',
						'QUERY_NEEDED' => 'Y',
						'OUTGOING_NEEDED' => 'N',
						'WIDGET_NEEDED' => 'N',
						'BOT_NEEDED' => 'N',

					]
				);
				if ($result->isSuccess())
				{
					$integrationID = $result->getId();
					static::$tmpApList[$id] = $integrationID;
				}
			}
		}
	}

	/**
	 * Event on after add webhook permission. Create integration for external create webhook permission.
	 *
	 * @param Event $event
	 */
	public static function onAfterAddApPermission(Event $event)
	{
		if (!static::$skipMode)
		{
			$id = intVal($event->getParameter('id'));
			if ($id > 0)
			{
				/** @var array $fields */
				$fields = $event->getParameter('fields');
				if (!isset(static::$tmpApList[$fields['PASSWORD_ID']]) ||
					!isset(static::$tmpApPermissionList[$fields['PASSWORD_ID']]))
				{
					$res = IntegrationTable::getList(
						[
							'filter' => [
								'PASSWORD_ID' => $fields['PASSWORD_ID']
							],
							'select' => [
								'ID',
								'PASSWORD_ID',
								'SCOPE'
							]
						]
					);
					if ($integration = $res->fetch())
					{
						static::$tmpApList[$integration['PASSWORD_ID']] = $integration['ID'];
						static::$tmpApPermissionList[$integration['PASSWORD_ID']] = $integration['SCOPE'];
					}
				}

				if (
					array_key_exists($fields['PASSWORD_ID'], static::$tmpApPermissionList)
					&& !in_array($fields['PERM'], static::$tmpApPermissionList[$fields['PASSWORD_ID']])
				)
				{
					static::$tmpApPermissionList[$fields['PASSWORD_ID']][] = $fields['PERM'];

					try
					{
						$integrationId = static::$tmpApList[$fields['PASSWORD_ID']];
						$scopeList = static::$tmpApPermissionList[$fields['PASSWORD_ID']];
						IntegrationTable::update(
							$integrationId,
							[
								'SCOPE' => $scopeList
							]
						);
					}
					catch (\Exception $e)
					{
					}
				}
			}
		}
	}
}