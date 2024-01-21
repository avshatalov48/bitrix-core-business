<?php

namespace Bitrix\MessageService;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Error;
use Bitrix\Main\ORM\Data\AddResult;
use Bitrix\Main\Type\DateTime;
use Bitrix\Messageservice\Internal\Entity\IncomingMessageTable;

abstract class IncomingMessage
{
	abstract public static function sendMessageToChat(array $message);
	abstract protected static function prepareBodyForSave(array $body): array;

	public static function addMessage(string $senderId, array $requestBody): AddResult
	{
		$requestBody = static::prepareBodyForSave($requestBody);

		$addResult = new AddResult();
		$insertingData = [
			'SENDER_ID' => $senderId,
			'EXTERNAL_ID' => $requestBody['id'],
			'REQUEST_BODY' => serialize($requestBody)
		];

		try
		{
			$addResult = IncomingMessageTable::add($insertingData);
		}
		catch (\Throwable $exception)
		{
			if (mb_strpos($exception->getMessage(), '1062'))
			{
				$addResult->addError(new Error($exception->getMessage()));

				return $addResult;
			}

			throw $exception;
		}

		return $addResult;
	}

	public static function confirmSendingMessage(int $internalId): void
	{
		IncomingMessageTable::update($internalId, [
			'DATE_EXEC' => new DateTime()
		]);
	}

	public static function cleanUpAgent(): string
	{
		$period = abs((int)Option::get("messageservice", "clean_up_period"));
		$periodInSeconds = $period * 24 * 3600;

		if ($periodInSeconds > 0)
		{
			$connection = \Bitrix\Main\Application::getConnection();
			$datetime = $connection->getSqlHelper()->addSecondsToDateTime('-' . $periodInSeconds);
			$connection->queryExecute("DELETE FROM b_messageservice_incoming_message WHERE DATE_EXEC <= {$datetime}");
		}

		return __METHOD__.'();';
	}
}