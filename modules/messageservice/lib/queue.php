<?php
namespace Bitrix\MessageService;

use Bitrix\Main\Application;
use Bitrix\Main\Config;
use Bitrix\Main\Error;
use Bitrix\Main\Event;
use Bitrix\Main\EventManager;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Type\DateTime;
use Bitrix\MessageService\Internal\Entity\MessageTable;
use Bitrix\MessageService\Sender\Result\SendMessage;
use Bitrix\MessageService\Sender\SmsManager;
use Bitrix\Main\Localization\Loc;


class Queue
{
	public const EVENT_SEND_RESULT = 'messageSendResult';

	public static function hasMessages(): bool
	{
		$result = MessageTable::getList([
			'select' => ['ID'],
			'filter' => [
				'=SUCCESS_EXEC' => 'N',
				[
					'LOGIC' => 'OR',
					'<NEXT_EXEC' => new DateTime(),
					'=NEXT_EXEC' => null,
				]
			],
			'limit' => 1,
		]);

		return !empty($result->fetch());
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

		Application::getInstance()->addBackgroundJob([static::class, "sendMessages"]);

		return "";
	}

	/**
	 * @return string
	 */
	public static function sendMessages()
	{
		$lockTag = 'b_messageservice_message';
		if (!Application::getConnection()->lock($lockTag))
		{
			return "";
		}

		$counts = Internal\Entity\MessageTable::getAllDailyCount();

		$limit = abs((int)Config\Option::get("messageservice", "queue_limit", 5));
		if (!$limit)
		{
			$limit = 5;
		}

		$query =
			MessageTable::query()
				->addSelect('ID')
				->addSelect('TYPE')
				->addSelect('SENDER_ID')
				->addSelect('AUTHOR_ID')
				->addSelect('MESSAGE_FROM')
				->addSelect('MESSAGE_TO')
				->addSelect('MESSAGE_HEADERS')
				->addSelect('MESSAGE_BODY')
				->addSelect('EXTERNAL_ID')
				->where(Query::filter()
					->logic('or')
					->where(Query::filter()
						->logic('and')
						->where('SUCCESS_EXEC', 'N')
						->where(Query::filter()
							->logic('or')
							->where('NEXT_EXEC', '<', new DateTime())
							->whereNull('NEXT_EXEC')
						)
					)
					->where(Query::filter()
						->logic('and')
						->where('SUCCESS_EXEC', 'P')
						->where('NEXT_EXEC', '<', (new DateTime())->add('-2 MINUTE'))
					)
				)
				->addOrder('ID')
				->setLimit($limit)
		;

		if (defined('BX_CLUSTER_GROUP'))
		{
			$query->where('CLUSTER_GROUP', \BX_CLUSTER_GROUP);
		}
		$messageFieldsList = $query->fetchAll();

		if (!empty($messageFieldsList))
		{
			$idList = array_column($messageFieldsList, 'ID');
			MessageTable::updateMulti(
				$idList,
				[
					'SUCCESS_EXEC' => 'P',
					'NEXT_EXEC' => (new DateTime())->add('+2 MINUTE'),
				],
				true
			);
		}

		$nextDay = static::getNextExecTime();
		foreach ($messageFieldsList as $messageFields)
		{
			$serviceId = $messageFields['SENDER_ID'] . ':' . $messageFields['MESSAGE_FROM'];
			$message = Message::createFromFields($messageFields);

			if (!isset($counts[$serviceId]))
			{
				$counts[$serviceId] = 0;
			}

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
					'DATE_EXEC' => new DateTime(),
					'EXEC_ERROR' => $e->getMessage(),
				]);
				break;
			}
		}

		Application::getConnection()->unlock($lockTag);

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
				$sender->setSocketTimeout(6);
				$sender->setStreamTimeout(18);
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
	 * @return DateTime
	 */
	private static function getNextExecTime(): DateTime
	{
		$nextDay = DateTime::createFromTimestamp(time() + 86400);
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
	 */
	public static function cleanUpAgent(): string
	{
		$period = abs(intval(Config\Option::get("messageservice", "clean_up_period", 14)));
		$periodInSeconds = $period * 24 * 3600;

		if ($periodInSeconds > 0)
		{
			$connection = \Bitrix\Main\Application::getConnection();
			$datetime = $connection->getSqlHelper()->addSecondsToDateTime('-' . $periodInSeconds);
			$connection->queryExecute("DELETE FROM b_messageservice_message WHERE DATE_EXEC <= {$datetime}");
		}

		return __METHOD__.'();';
	}
}