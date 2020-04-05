<?php
namespace Bitrix\MessageService;

use Bitrix\Main\Application;
use Bitrix\Main\Config;
use Bitrix\Main\Error;
use Bitrix\Main\Type;
use Bitrix\MessageService\Sender\Result\SendMessage;
use Bitrix\MessageService\Sender\SmsManager;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Queue
{
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

		return static::sendMessages();
	}

	/**
	 * @return string
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentTypeException
	 */
	public static function sendMessages()
	{
		if(defined("BX_FORK_AGENTS_AND_EVENTS_FUNCTION"))
		{
			if(\CMain::forkActions(array(get_called_class(), "sendMessages")))
				return "";
		}

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

		$strSql = "SELECT ID ,TYPE, SENDER_ID, AUTHOR_ID,
			MESSAGE_FROM, MESSAGE_TO, MESSAGE_HEADERS, MESSAGE_BODY
			FROM b_messageservice_message
			WHERE SUCCESS_EXEC = 'N' AND (NEXT_EXEC < '{$now}' OR NEXT_EXEC IS NULL)
			ORDER BY ID
			LIMIT ".$limit;
		$messagesResult = $connection->query($strSql);
		$notifyUpdateMessages = array();

		if($messagesResult)
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

			while($message = $messagesResult->fetch())
			{
				$serviceId = $message['SENDER_ID'] . ':' . $message['MESSAGE_FROM'];

				if (!isset($counts[$serviceId]))
					$counts[$serviceId] = 0;

				$sender = SmsManager::getSenderById($message['SENDER_ID']);
				if ($sender)
				{
					$limit = Sender\Limitation::getDailyLimit($sender->getId(), $message['MESSAGE_FROM']);
					$current = $counts[$serviceId];

					if ($limit > 0 && $current >= $limit)
					{
						Internal\Entity\MessageTable::update($message['ID'], array(
							'NEXT_EXEC' => $nextDay,
							'STATUS_ID' => MessageStatus::DEFERRED
						));
						$notifyUpdateMessages[] = array(
							'ID' => $message['ID'],
							'STATUS_ID' => MessageStatus::DEFERRED,
							'NEXT_EXEC' => (string)$nextDay
						);
						continue;
					}
					++$counts[$serviceId];
				}

				$message['MESSAGE_HEADERS'] = unserialize($message['MESSAGE_HEADERS']);
				$toUpdate = array('SUCCESS_EXEC' => "E", 'DATE_EXEC' => new Type\DateTime);

				try
				{
					$result = static::sendMessage($message);
					if ($result->isSuccess())
					{
						$toUpdate['SUCCESS_EXEC'] = 'Y';
						if ($result->getExternalId() !== null)
						{
							$toUpdate['EXTERNAL_ID'] = $result->getExternalId();
						}
						if ($result->getStatus() !== null)
						{
							$toUpdate['STATUS_ID'] = $result->getStatus();
						}
					}
					elseif ($result->getStatus() === MessageStatus::DEFERRED)
					{
						$toUpdate = array(
							'NEXT_EXEC' => $nextDay,
							'STATUS_ID' => MessageStatus::DEFERRED
						);
					}
					else
					{
						$toUpdate['STATUS_ID'] = MessageStatus::ERROR;
					}

					$errors = $result->getErrorMessages();
					if ($errors)
					{
						$toUpdate['EXEC_ERROR'] = implode(PHP_EOL, $errors);
					}

					Internal\Entity\MessageTable::update($message["ID"], $toUpdate);
					$toUpdate['ID'] = $message['ID'];
					if (isset($toUpdate['DATE_EXEC']))
					{
						$toUpdate['DATE_EXEC'] = (string)$toUpdate['DATE_EXEC'];
					}
					if (isset($toUpdate['NEXT_EXEC']))
					{
						$toUpdate['NEXT_EXEC'] = (string)$toUpdate['NEXT_EXEC'];
					}
					$notifyUpdateMessages[] = $toUpdate;
				}
				catch (\Exception $e)
				{
					$application = \Bitrix\Main\Application::getInstance();
					$exceptionHandler = $application->getExceptionHandler();
					$exceptionHandler->writeToLog($e);
					$toUpdate['EXEC_ERROR'] = $e->getMessage();
					$toUpdate['STATUS_ID'] = MessageStatus::EXCEPTION;
					Internal\Entity\MessageTable::update($message["ID"], $toUpdate);
					$toUpdate['ID'] = $message["ID"];
					$toUpdate['DATE_EXEC'] = (string)$toUpdate['DATE_EXEC'];
					unset($toUpdate['EXEC_ERROR']);
					$notifyUpdateMessages[] = $toUpdate;
					break;
				}
			}
		}

		$connection->query("SELECT RELEASE_LOCK('".$lockTag."')");

		if ($notifyUpdateMessages)
		{
			Integration\Pull::onMessagesUpdate($notifyUpdateMessages);
		}

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
				$result = new SendMessage();
				$result->addError(new Error(Loc::getMessage("MESSAGESERVICE_QUEUE_SENDER_NOT_FOUND")));
				return $result;
			}

			return $sender->sendMessage($messageFields);
		}

		$result = new SendMessage();
		$result->addError(new Error(Loc::getMessage("MESSAGESERVICE_QUEUE_MESSAGE_TYPE_ERROR")));
		return $result;
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