<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2016 Bitrix
 */
namespace Bitrix\Main\Mail;

use Bitrix\Main;

class EventManager
{
	/**
	 * @return string|null
	 */
	public static function checkEvents()
	{
		if(
			(defined("DisableEventsCheck") && DisableEventsCheck === true)
			||
			(
				defined("BX_CRONTAB_SUPPORT") && BX_CRONTAB_SUPPORT === true
				&&
				(!defined("BX_CRONTAB") || BX_CRONTAB !== true)
			)
		)
		{
			return null;
		}

		$manage_cache = Main\Application::getInstance()->getManagedCache();
		if(CACHED_b_event !== false && $manage_cache->read(CACHED_b_event, "events"))
			return "";

		return static::executeEvents();
	}

	/**
	 * @return string
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 */
	public static function executeEvents()
	{
		$manage_cache = Main\Application::getInstance()->getManagedCache();

		$bulk = intval(Main\Config\Option::get("main", "mail_event_bulk", 5));
		if($bulk <= 0)
			$bulk = 5;

		$rsMails = null;

		$connection = Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		$strSql= "SELECT 'x' FROM b_event WHERE SUCCESS_EXEC='N' LIMIT 1";
		$resultEventDb = $connection->query($strSql);
		if($resultEventDb->fetch())
		{
			if(!$connection->lock('event'))
				return "";
		}
		else
		{
			if(CACHED_b_event!==false)
				$manage_cache->set("events", true);

			return "";
		}

		$strSql = "
			SELECT ID, C_FIELDS, EVENT_NAME, MESSAGE_ID, LID,
				" . $helper->formatDate('DD.MM.YYYY HH24:MI:SS', 'DATE_INSERT') . " as DATE_INSERT,
				DUPLICATE, LANGUAGE_ID
			FROM b_event
			WHERE SUCCESS_EXEC='N'
			ORDER BY ID
			LIMIT ".$bulk;

		$rsMails = $connection->query($strSql);

		if ($rsMails)
		{
			$arCallableModificator = array();
			$cnt = 0;
			foreach(Internal\EventTable::getFetchModificatorsForFieldsField() as $callableModificator)
			{
				if(is_callable($callableModificator))
				{
					$arCallableModificator[] = $callableModificator;
				}
			}
			while($arMail = $rsMails->fetch())
			{
				foreach($arCallableModificator as $callableModificator)
					$arMail['C_FIELDS'] = call_user_func_array($callableModificator, array($arMail['C_FIELDS']));

				$arFiles = array();
				$fileListDb = Internal\EventAttachmentTable::getList(array(
					'select' => array('FILE_ID'),
					'filter' => array('=EVENT_ID' => $arMail["ID"])
				));
				while($file = $fileListDb->fetch())
				{
					$arFiles[] = $file['FILE_ID'];
				}
				$arMail['FILE'] = $arFiles;

				if(!is_array($arMail['C_FIELDS']))
				{
					$arMail['C_FIELDS'] = array();
				}
				try
				{
					$flag = Event::handleEvent($arMail);
					Internal\EventTable::update($arMail["ID"], array('SUCCESS_EXEC' => $flag, 'DATE_EXEC' => new Main\Type\DateTime));
				}
				catch (\Exception $e)
				{
					Internal\EventTable::update($arMail["ID"], array('SUCCESS_EXEC' => "E", 'DATE_EXEC' => new Main\Type\DateTime));

					$application = Main\Application::getInstance();
					$exceptionHandler = $application->getExceptionHandler();
					$exceptionHandler->writeToLog($e);

					break;
				}

				$cnt++;
				if($cnt >= $bulk)
					break;
			}
		}

		$connection->unlock('event');

		if($cnt === 0 && CACHED_b_event !== false)
			$manage_cache->set("events", true);

		return null;
	}

	/**
	 * @return string
	 * @throws Main\ArgumentNullException
	 */
	public static function cleanUpAgent()
	{
		$period = abs(intval(Main\Config\Option::get("main", "mail_event_period", 14)));
		$periodInSeconds = $period * 24 * 3600;

		$connection = Main\Application::getConnection();
		$datetime = $connection->getSqlHelper()->addSecondsToDateTime('-' . $periodInSeconds);

		$strSql = "DELETE FROM b_event WHERE DATE_EXEC <= " . $datetime;
		$connection->query($strSql);

		$strSql = "DELETE FROM b_event_attachment "
			. " WHERE IS_FILE_COPIED='N'"
			. " AND NOT EXISTS(SELECT e.ID FROM b_event e WHERE e.ID=EVENT_ID)";
		$connection->query($strSql);

		\CAgent::addAgent(
			self::class . '::cleanUpAttachmentAgent();',
			'main',
			"N",
			60,
			"",
			"Y"
		);

		return "CEvent::CleanUpAgent();";
	}

	/**
	 * Agent for clean up event attachments.
	 *
	 * @return string
	 */
	public static function cleanUpAttachmentAgent()
	{
		$connection = Main\Application::getConnection();
		$rows = Internal\EventAttachmentTable::getList([
			'select' => ['EVENT_ID', 'FILE_ID'],
			'filter' => [
				'=IS_FILE_COPIED' => 'Y',
				'=EVENT.ID' => null,
			],
			'limit' => 5
		])->fetchAll();
		foreach ($rows as $row)
		{
			\CFile::Delete($row['FILE_ID']);
			$strSql = "DELETE FROM b_event_attachment "
				. " WHERE EVENT_ID=" . intval($row['EVENT_ID'])
				. " AND FILE_ID=" . intval($row['FILE_ID']);
			$connection->query($strSql);
		}

		return !empty($rows) ? self::class . '::cleanUpAttachmentAgent();' : '';
	}

	/**
	 * Handler of event main/OnMailEventSubscriptionList
	 *
	 * @param array $data Data.
	 * @return array
	 */
	public static function onMailEventSubscriptionList(array $data)
	{
		$row = Internal\BlacklistTable::getRow([
			'select' => ['ID'],
			'filter' => ['=CODE' => $data['FIELDS']['CODE']]
		]);
		if ($row)
		{
			return [];
		}

		return [
			[
				'ID' => 'main/mail/event',
				'NAME' => 'Mail events',
				'DESC' => '',
				'SELECTED' => true,
			]
		];
	}

	/**
	 * Handler of event main/OnMailEventSubscriptionDisable
	 *
	 * @param array $data Data.
	 * @return bool
	 */
	public static function onMailEventSubscriptionDisable(array $data)
	{
		if (empty($data['FIELDS']) || empty($data['FIELDS']['CODE']))
		{
			return false;
		}

		$code = mb_strtolower(trim($data['FIELDS']['CODE']));
		if (!check_email($code))
		{
			return false;
		}

		return Internal\BlacklistTable::add([
			'CODE' => $code,
			'CATEGORY_ID' => Internal\BlacklistTable::CategoryManual,
			'DATE_INSERT' => new Main\Type\DateTime()
		])->isSuccess();
	}
}
