<?php
namespace Bitrix\Rest;

use Bitrix\Main;

/**
 * Class EventTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> APP_ID int mandatory
 * <li> EVENT_NAME string(255) mandatory
 * <li> EVENT_HANDLER string(255) mandatory
 * <li> USER_ID int optional
 * </ul>
 *
 * @package Bitrix\Rest
 **/
class EventTable extends Main\Entity\DataManager
{
	const ERROR_EVENT_NOT_FOUND = 'ERROR_EVENT_NOT_FOUND';

	const TYPE_ONLINE = 'online';
	const TYPE_OFFLINE = 'offline';

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_rest_event';
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
			'APP_ID' => array(
				'data_type' => 'integer',
			),
			'EVENT_NAME' => array(
				'data_type' => 'string',
				'required' => true,
			),
			'EVENT_HANDLER' => array(
				'data_type' => 'string',
			),
			'USER_ID' => array(
				'data_type' => 'integer',
			),
			'TITLE' => array(
				'data_type' => 'string'
			),
			'COMMENT' => array(
				'data_type' => 'string'
			),
			'DATE_CREATE' => array(
				'data_type' => 'datetime'
			),
			'APPLICATION_TOKEN' => array(
				'data_type' => 'string'
			),
			'CONNECTOR_ID' => array(
				'data_type' => 'string'
			),
			'REST_APP' => array(
				'data_type' => 'Bitrix\Rest\AppTable',
				'reference' => array('=this.APP_ID' => 'ref.ID'),
			),

			/**
			 * @deprecated
			 * Use REST_APP
			 */
			'APP' => array(
				'data_type' => 'Bitrix\Bitrix24\AppsTable',
				'reference' => array('=this.APP_ID' => 'ref.ID'),
			),
		);
	}

	/**
	 * Removes all application event handlers.
	 *
	 * @param int $appId Application ID.
	 *
	 * @return Main\DB\Result
	 */
	public static function deleteByApp($appId)
	{
		$connection = Main\Application::getConnection();
		return $connection->query("DELETE FROM ".static::getTableName()." WHERE APP_ID='".intval($appId)."'");
	}

	/**
	 * Removes application install event handler.
	 *
	 * @param int $appId Application ID.
	 *
	 * @return Main\DB\Result
	 */
	public static function deleteAppInstaller($appId)
	{
		$connection = Main\Application::getConnection();
		return $connection->query("DELETE FROM ".static::getTableName()." WHERE APP_ID='".intval($appId)."' AND EVENT_NAME='ONAPPINSTALL'");
	}

	/**
	 * @deprecated
	 *
	 * Use \Bitrix\Rest\HandlerHelper::checkCallback
	 */
	public static function checkCallback($eventCallback, $appInfo, $checkInstallUrl = true)
	{
		return \Bitrix\Rest\HandlerHelper::checkCallback($eventCallback, $appInfo, $checkInstallUrl);
	}

	public static function onBeforeUpdate(Main\Entity\Event $event)
	{
		return static::checkUniq($event);
	}

	public static function onBeforeAdd(Main\Entity\Event $event)
	{
		return static::checkUniq($event);
	}

	public static function bind($eventName)
	{
		$provider = new \CRestProvider();
		$restDescription = $provider->getDescription();
		foreach($restDescription as $scope => $scopeDescription)
		{
			if(
				is_array($scopeDescription[\CRestUtil::EVENTS])
				&& array_key_exists($eventName, $scopeDescription[\CRestUtil::EVENTS])
			)
			{
				\Bitrix\Rest\Event\Sender::bind(
					$scopeDescription[\CRestUtil::EVENTS][$eventName][0],
					$scopeDescription[\CRestUtil::EVENTS][$eventName][1]
				);

				break;
			}
		}
	}

	public static function onAfterAdd(Main\Entity\Event $event)
	{
		$result = new Main\Entity\EventResult();

		$fields = $event->getParameter('fields');
		static::bind($fields['EVENT_NAME']);

		return $result;
	}

	public static function onAfterUpdate(Main\Entity\Event $event)
	{
		$result = new Main\Entity\EventResult();

		$fields = $event->getParameter('fields');
		static::bind($fields['EVENT_NAME']);

		return $result;
	}

	protected static function checkUniq(Main\Entity\Event $event)
	{
		$result = new Main\Entity\EventResult();
		$data = $event->getParameter("fields");

		$dbRes = static::getList(array(
			'filter' => array(
				'=APP_ID' => $data['APP_ID'],
				'=EVENT_NAME' => $data['EVENT_NAME'],
				'=EVENT_HANDLER' => $data['EVENT_HANDLER'],
				'=USER_ID' => $data['USER_ID'],
				'=CONNECTOR_ID' => $data['CONNECTOR_ID'],
			),
			'select' => array('ID')
		));

		if($dbRes->fetch())
		{
			$result->addError(new Main\Entity\EntityError(
				"Handler already binded"
			));
		}

		return $result;
	}
}
