<?php
namespace Bitrix\MessageService;

use Bitrix\Main\Application;
use Bitrix\Main\Config;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Error;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Type\DateTime;
use Bitrix\MessageService\Internal\Entity\MessageTable;
use Bitrix\MessageService\Sender\Result\SendMessage;
use Bitrix\MessageService\Sender\SmsManager;
use Bitrix\Main\Localization\Loc;
use Bitrix\MessageService\Internal\Entity\Message\SuccessExec;
use Bitrix\MessageService\Queue\Event\AfterProcessQueueEvent;
use Bitrix\MessageService\Queue\Event\AfterSendMessageFromQueueEvent;
use Bitrix\MessageService\Queue\Event\BeforeProcessQueueEvent;
use Bitrix\MessageService\Queue\Event\BeforeSendMessageFromQueueEvent;

class Queue
{
	/**
	 * @deprecated
	 * @see \Bitrix\MessageService\Queue\Event\AfterSendMessageFromQueueEvent
	 */
	public const EVENT_SEND_RESULT = 'messageSendResult';

	private const OPTION_QUEUE_STOP_MODULE = 'messageservice';
	private const OPTION_QUEUE_STOP_NAME = 'queue_stopped';

	private const CACHE_HAS_MESSAGES_TIME = 2592000;
	public const CACHE_HAS_MESSAGES_ID = 'has_messages_cache';
	public const CACHE_HAS_MESSAGES_DIR = '/messageservice/';

	public static function hasMessages(): bool
	{
		$nextExec = new DateTime();

		$cache = Cache::createInstance();
		if ($cache->initCache(self::CACHE_HAS_MESSAGES_TIME, self::CACHE_HAS_MESSAGES_ID, self::CACHE_HAS_MESSAGES_DIR))
		{
			$nextExec = $cache->getVars();
		}
		elseif ($cache->startDataCache())
		{
			$result = MessageTable::getList([
				'select' => ['ID', 'NEXT_EXEC'],
				'filter' => [
					'=SUCCESS_EXEC' => SuccessExec::NO,
				],
				'limit' => 1,
				'order' => ['ID' => 'DESC'],
			])->fetch();

			if ($result)
			{
				if ($result['NEXT_EXEC'] instanceof DateTime)
				{
					$nextExec = $result['NEXT_EXEC'];
				}
				else
				{
					$nextExec = new DateTime();
				}
			}
			else
			{
				$nextExec = (new DateTime())->add('+' . self::CACHE_HAS_MESSAGES_TIME . ' seconds');
			}

			$cache->endDataCache($nextExec);
		}

		return $nextExec <= new DateTime();
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

		if (static::isStopped() || !static::hasMessages())
		{
			return "";
		}

		Application::getInstance()->addBackgroundJob([static::class, "sendMessages"]);

		return "";
	}

	/**
	 * @return string|null
	 */
	public static function sendMessages(): ?string
	{
		if (static::isStopped())
		{
			return '';
		}

		$lockTag = 'b_messageservice_message';
		if (!Application::getConnection()->lock($lockTag))
		{
			return '';
		}

		$event = new BeforeProcessQueueEvent();
		$event->send();
		if (!$event->canProcessQueue())
		{
			Application::getConnection()->unlock($lockTag);

			return '';
		}

		$limit = (int)Config\Option::get('messageservice', 'queue_limit');
		if ($limit < 1)
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
						->where('SUCCESS_EXEC', SuccessExec::NO)
						->where(Query::filter()
							->logic('or')
							->where('NEXT_EXEC', '<', new DateTime())
							->whereNull('NEXT_EXEC')
						)
					)
					->where(Query::filter()
						->logic('and')
						->where('SUCCESS_EXEC', SuccessExec::PROCESSED)
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

		if (empty($messageFieldsList))
		{
			Application::getConnection()->unlock($lockTag);

			return null;
		}

		$idList = array_column($messageFieldsList, 'ID');
		MessageTable::updateMulti(
			$idList,
			[
				'SUCCESS_EXEC' => SuccessExec::PROCESSED,
				'NEXT_EXEC' => (new DateTime())->add('+2 MINUTE'),
			],
			true
		);

		$hasDailyLimits = Sender\Limitation::hasDailyLimits();
		if ($hasDailyLimits)
		{
			$counts = Internal\Entity\MessageTable::getAllDailyCount();
		}
		else
		{
			$counts = [];
		}

		$nextDay = static::getNextExecTime();
		foreach ($messageFieldsList as $messageFields)
		{
			$message = Message::createFromFields($messageFields);

			if ($hasDailyLimits)
			{
				$sender = $message->getSender();
				if ($sender)
				{
					$limit = Sender\Limitation::getDailyLimit($sender->getId(), $messageFields['MESSAGE_FROM']);
					if ($limit > 0)
					{
						$serviceId = $sender->getId() . ':' . $messageFields['MESSAGE_FROM'];

						$counts[$serviceId] ??= 0;
						if ($counts[$serviceId] >= $limit)
						{
							$message->update([
								'STATUS_ID' => MessageStatus::DEFERRED,
								'NEXT_EXEC' => $nextDay,
							]);

							continue;
						}

						++$counts[$serviceId];
					}
				}
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
					'SUCCESS_EXEC' => SuccessExec::ERROR,
					'DATE_EXEC' => new DateTime(),
					'EXEC_ERROR' => $e->getMessage(),
				]);

				break;
			}
		}

		$event = new AfterProcessQueueEvent();
		$event->send();

		Application::getConnection()->unlock($lockTag);

		return null;
	}

	/**
	 * @param array $messageFields
	 * @return SendMessage
	 */
	private static function sendMessage(array $messageFields)
	{
		$event = new BeforeSendMessageFromQueueEvent($messageFields);
		$event->send();

		$sendResult = $event->processResults() ?? new SendMessage;
		if (!$sendResult->isSuccess())
		{
			return $sendResult;
		}

		$type = $messageFields['TYPE'];
		if ($type === MessageType::SMS)
		{
			$sender = SmsManager::getSenderById($messageFields['SENDER_ID']);
			if (!$sender)
			{
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
			$sendResult->addError(new Error(Loc::getMessage("MESSAGESERVICE_QUEUE_MESSAGE_TYPE_ERROR")));
		}

		$event = new AfterSendMessageFromQueueEvent($messageFields, $sendResult);
		$event->send();
		$event->sendAlias(static::EVENT_SEND_RESULT);

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

	public static function stop(): void
	{
		Config\Option::set(self::OPTION_QUEUE_STOP_MODULE, self::OPTION_QUEUE_STOP_NAME, 'Y');
	}

	public static function resume(): void
	{
		Config\Option::set(self::OPTION_QUEUE_STOP_MODULE, self::OPTION_QUEUE_STOP_NAME, 'N');
	}

	public static function isStopped(): bool
	{
		return Config\Option::get(self::OPTION_QUEUE_STOP_MODULE, self::OPTION_QUEUE_STOP_NAME) === 'Y';
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