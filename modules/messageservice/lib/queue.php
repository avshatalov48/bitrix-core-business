<?php
namespace Bitrix\MessageService;

use Bitrix\Main\Application;
use Bitrix\Main\Config;
use Bitrix\Main\Error;
use Bitrix\Main\Event;
use Bitrix\Main\EventManager;
use Bitrix\Main\Type;
use Bitrix\MessageService\Sender\Result\SendMessage;
use Bitrix\MessageService\Sender\SmsManager;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Queue
{
	public const EVENT_SEND_RESULT = 'messageSendResult';

	private static function hasMessages()
	{
		$connection = Application::getConnection();
		$now = date('Y-m-d H:i:s');

		$queryString = 'SELECT 1 FROM b_messageservice_message WHERE SUCCESS_EXEC=\'N\' AND (NEXT_EXEC < \''
			.$now.'\' OR NEXT_EXEC IS NULL) LIMIT 1';
		return is_array($connection->query($queryString)->fetch());
	}

	/**
	 * @return string
	 */
	public static function run()
	{
		if (
			defined('DisableMessageServiceCheck') && DisableMessageServiceCheck === true
			|| (
				!defined('DisableMessageServiceCheck')
				&& defined("DisableEventsCheck")
				&& DisableEventsCheck === true
			)
		)
		{
			return null;
		}

		if (!static::hasMessages())
		{
			return "";
		}

		Application::getInstance()->addBackgroundJob([get_called_class(), "sendMessages"]);

		return "";
	}

	/**
	 * @return string
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentTypeException
	 */
	public static function sendMessages()
	{
		$connection = Application::getConnection();
		$lockTag = \CMain::getServerUniqID().'_b_messageservice_message';

		$lockDb = $connection->query("SELECT GET_LOCK('".$lockTag."', 0) as L");
		$lockRow = $lockDb->fetch();
		if($lockRow["L"]=="0")
			return "";

		$counts = Internal\Entity\MessageTable::getAllDailyCount();

		$limit = abs((int)Config\Option::get("messageservice", "queue_limit", 5));
		if (!$limit)
		{
			$limit = 5;
		}

		$now = date('Y-m-d H:i:s');
		$nextDay = static::getNextExecTime();

		$strSql = "SELECT ID ,TYPE, SENDER_ID, AUTHOR_ID,
			MESSAGE_FROM, MESSAGE_TO, MESSAGE_HEADERS, MESSAGE_BODY
			FROM b_messageservice_message
			WHERE SUCCESS_EXEC = 'N' AND (NEXT_EXEC < '{$now}' OR NEXT_EXEC IS NULL)
			ORDER BY ID
			LIMIT ".$limit;
		$messagesResult = $connection->query($strSql);
		while($messageFields = $messagesResult->fetch())
		{
			$messageFields['MESSAGE_HEADERS'] = unserialize($messageFields['MESSAGE_HEADERS'], ['allowed_classes' => false]);
			$serviceId = $messageFields['SENDER_ID'] . ':' . $messageFields['MESSAGE_FROM'];
			$message = Message::createFromFields($messageFields);

			if (!isset($counts[$serviceId]))
				$counts[$serviceId] = 0;

			$sender = $message->getSender();
			if ($sender)
			{
				$limit = Sender\Limitation::getDailyLimit($sender->getId(), $messageFields['MESSAGE_FROM']);
				$current = $counts[$serviceId];

				if ($limit > 0 && $current >= $limit)
				{
					$message->update([
						'STATUS_ID' => MessageStatus::DEFERRED,
						'NEXT_EXEC' => $nextDay,
					]);
					continue;
				}
				++$counts[$serviceId];
			}

			try
			{
				$result = static::sendMessage($messageFields);
				$message->updateWithSendResult($result, $nextDay);
			}
			catch (\Throwable $e)
			{
				Application::getInstance()->getExceptionHandler()->writeToLog($e);

				$message->update([
					'STATUS_ID' => MessageStatus::EXCEPTION,
					'SUCCESS_EXEC' => 'E',
					'DATE_EXEC' => new Type\DateTime(),
					'EXEC_ERROR' => $e->getMessage(),
				]);
				break;
			}
		}

		$connection->query("SELECT RELEASE_LOCK('".$lockTag."')");
		return null;
	}

	/**
	 * @param array $messageFields
	 * @return SendMessage
	 */
	private static function sendMessage(array $messageFields)
	{
		$type = $messageFields['TYPE'];
		if ($type === MessageType::SMS)
		{
			$sender = SmsManager::getSenderById($messageFields['SENDER_ID']);
			if (!$sender)
			{
				$sendResult = new SendMessage();
				$sendResult->addError(new Error(Loc::getMessage("MESSAGESERVICE_QUEUE_SENDER_NOT_FOUND")));
			}
			else
			{
				$sendResult = $sender->sendMessage($messageFields);
			}
		}
		else
		{
			$sendResult = new SendMessage();
			$sendResult->addError(new Error(Loc::getMessage("MESSAGESERVICE_QUEUE_MESSAGE_TYPE_ERROR")));
		}
		EventManager::getInstance()->send(new Event("messageservice", static::EVENT_SEND_RESULT, [
			'message' => $messageFields,
			'sendResult' => $sendResult,
		]));


		return $sendResult;
	}

	/**
	 * Returns next date to exec message, if it will be deferred due to the send limits.
	 *
	 * @return Type\DateTime
	 */
	private static function getNextExecTime(): Type\DateTime
	{
		$nextDay = Type\DateTime::createFromTimestamp(time() + 86400);
		$retryTime = Sender\Limitation::getRetryTime();
		if (!$retryTime['auto'])
		{
			if ($nextDay->getTimeZone()->getName() !== $retryTime['tz'])
			{
				try //if TZ is incorrect
				{
					$nextDay->setTimeZone(new \DateTimeZone($retryTime['tz']));
				}
				catch (\Exception $e) {}
			}
			$nextDay->setTime($retryTime['h'], $retryTime['i'], 0);
		}
		return $nextDay;
	}

	/**
	 * @return string
	 * @throws \Bitrix\Main\ArgumentNullException
	 */
	public static function cleanUpAgent()
	{
		$period = abs(intval(Config\Option::get("messageservice", "clean_up_period", 14)));
		$periodInSeconds = $period * 24 * 3600;

		if ($periodInSeconds > 0)
		{
			$connection = \Bitrix\Main\Application::getConnection();
			$datetime = $connection->getSqlHelper()->addSecondsToDateTime('-' . $periodInSeconds);

			$strSql = "DELETE FROM b_messageservice_message WHERE DATE_EXEC <= " . $datetime ;
			$connection->query($strSql);
		}
		return "\Bitrix\MessageService\Queue::cleanUpAgent();";
	}
}