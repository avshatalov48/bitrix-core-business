<?php

declare(strict_types=1);

namespace Bitrix\Im\V2\Anchor;

use Bitrix\Im\Model\AnchorTable;
use Bitrix\Im\V2\Anchor\DI\AnchorContainer;
use Bitrix\Im\V2\Anchor\Push\PushService;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Result;
use Bitrix\Main\Application;
use Bitrix\Main\Diag\ExceptionHandler;
use Bitrix\Main\SystemException;

class ReadService
{
	use ContextCustomer;

	private PushService $pushService;
	private AnchorProvider $anchorProvider;
	private ExceptionHandler $exceptionHandler;

	public function __construct()
	{
		$this->init();
	}

	public function read(array $messageIds, ?int $userId = null): Result
	{
		$result = new Result();

		if (empty($messageIds))
		{
			return $result;
		}

		$userId ??= $this->getContext()->getUserId();

		$filter = [
			'USER_ID' => $userId,
			'MESSAGE_ID' => $messageIds,
		];

		try
		{
			$anchorCollection = AnchorCollection::find($filter);
			if ($anchorCollection->isEmpty())
			{
				return $result;
			}

			$deleteResult = $anchorCollection->delete();
			if (!$deleteResult->isSuccess())
			{
				return $result->addErrors($deleteResult->getErrors());
			}
		}
		catch (SystemException $exception)
		{
			$this->exceptionHandler->writeToLog($exception);

			return $result->addError(new AnchorError(AnchorError::UNEXPECTED));
		}

		$this->anchorProvider->cleanCache($userId);

		$this->pushService->deleteMulti($anchorCollection);

		return $result;
	}

	public function readByMessageIds(array $messageIds): Result
	{
		$result = new Result();

		if (empty($messageIds))
		{
			return $result;
		}

		$filter = [
			'MESSAGE_ID' => $messageIds,
		];

		try
		{
			$anchorCollection = AnchorCollection::find($filter);
			if ($anchorCollection->isEmpty())
			{
				return $result;
			}

			$deleteResult = $anchorCollection->delete();
			if (!$deleteResult->isSuccess())
			{
				return $result->addErrors($deleteResult->getErrors());
			}
		}
		catch (SystemException $exception)
		{
			$this->exceptionHandler->writeToLog($exception);

			return $result->addError(new AnchorError(AnchorError::UNEXPECTED));
		}

		$this->anchorProvider->cleanUsersCache((array)$anchorCollection->getUserIdList());

		$this->pushService->deleteMulti($anchorCollection);

		return $result;
	}

	public function readAll(?int $userId = null): Result
	{
		$result = new Result();

		$userId ??= $this->getContext()->getUserId();

		if ($userId <= 0)
		{
			return $result;
		}

		try
		{
			AnchorTable::deleteByFilter(['USER_ID' => $userId]);
		}
		catch (SystemException $exception)
		{
			$this->exceptionHandler->writeToLog($exception);

			return $result->addError(new AnchorError(AnchorError::UNEXPECTED));
		}

		$this->pushService->deleteAll($userId);

		$this->anchorProvider->cleanCache($userId);

		return $result;
	}

	public function readByChatId(int $chatId, ?int $userId = null): Result
	{
		$result = new Result();

		$userId ??= $this->getContext()->getUserId();

		if ($userId <= 0 || $chatId <= 0)
		{
			return $result;
		}

		try
		{
			AnchorTable::deleteByFilter(['CHAT_ID' => $chatId, 'USER_ID' => $userId]);
		}
		catch (SystemException $exception)
		{
			$this->exceptionHandler->writeToLog($exception);

			return $result->addError(new AnchorError(AnchorError::UNEXPECTED));
		}

		$this->pushService->deleteByChat($chatId, $userId);

		$this->anchorProvider->cleanCache($userId);

		return $result;
	}

	private function init(): void
	{
		$this->pushService = AnchorContainer::getInstance()->getPushService();
		$this->anchorProvider = AnchorContainer::getInstance()->getAnchorProvider();
		$this->exceptionHandler = Application::getInstance()->getExceptionHandler();
	}
}