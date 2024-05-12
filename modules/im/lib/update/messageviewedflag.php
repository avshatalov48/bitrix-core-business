<?php

namespace Bitrix\Im\Update;

use Bitrix\Im\Model\EO_Message_Collection;
use Bitrix\Im\Model\MessageTable;
use Bitrix\Im\Model\MessageViewedTable;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Entity\BooleanField;
use Bitrix\Main\Loader;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\Update\Stepper;

final class MessageViewedFlag extends Stepper
{
	protected static $moduleId = 'im';
	public const OPTION_NAME_LIMIT = 'message_viewed_flag_migration_limit';
	public const OPTION_NAME_ITERATION_COUNT = 'message_viewed_flag_migration_iteration';
	public const LIMIT_DEFAULT = 200;
	public const ITERATION_COUNT_DEFAULT = 2;

	function execute(array &$option)
	{
		if (!Loader::includeModule(self::$moduleId))
		{
			return self::CONTINUE_EXECUTION;
		}

		$numOfIterations = (int)Option::get(self::$moduleId, self::OPTION_NAME_ITERATION_COUNT, self::ITERATION_COUNT_DEFAULT);

		$result = self::CONTINUE_EXECUTION;
		for ($i = 0; $i < $numOfIterations; ++$i)
		{
			$result = $this->makeMigrationIteration($option);

			if ($result === self::FINISH_EXECUTION)
			{
				return $result;
			}
		}

		if (!$this->hasMoreViewedMessages($option['lastId'] ?? 0))
		{
			return self::FINISH_EXECUTION;
		}

		return $result;
	}

	private function makeMigrationIteration(array &$option): bool
	{
		$lastId = $option['lastId'] ?? 0;
		$messages = $this->getMessages($lastId);

		if ($messages->isEmpty())
		{
			return self::FINISH_EXECUTION;
		}

		$option['lastId'] = min($messages->getIdList());
		$viewedMessageIds = $this->getViewedMessageIds($messages);
		$this->setViewedFlag($viewedMessageIds);

		return self::CONTINUE_EXECUTION;
	}

	private function getViewedMessageIds(EO_Message_Collection $messages): array
	{
		$result = [];

		foreach ($messages as $message)
		{
			if ($message->get('IS_VIEWED'))
			{
				$result[] = $message->getId();
			}
		}

		return $result;
	}

	private function getMessages(int $lastId): EO_Message_Collection
	{
		$subQuery = MessageViewedTable::query()
			->setSelect(['ID'])
			->where('MESSAGE_ID', new \Bitrix\Main\DB\SqlExpression('%s'))
		;
		$expression = new ExpressionField('IS_VIEWED', "EXISTS({$subQuery->getQuery()})", ['ID']);
		$expression->configureValueType(BooleanField::class);
		$query = MessageTable::query()
			->setSelect(['ID', 'IS_VIEWED'])
			->registerRuntimeField('IS_VIEWED', $expression)
			->setOrder(['ID' => 'DESC'])
			->setLimit((int)Option::get(self::$moduleId, self::OPTION_NAME_LIMIT, self::LIMIT_DEFAULT))
		;

		if ($lastId !== 0)
		{
			$query->where('ID', '<', $lastId);
		}

		return $query->fetchCollection();
	}

	private function hasMoreViewedMessages(int $lastId): bool
	{
		if ($lastId === 0)
		{
			return true;
		}

		$result = MessageViewedTable::query()
			->setSelect(['MESSAGE_ID'])
			->setOrder(['MESSAGE_ID' => 'DESC'])
			->where('MESSAGE_ID', '<', $lastId)
			->setLimit(1)
			->fetch()
		;

		return $result !== false;
	}

	private function setViewedFlag(array $messageIds): void
	{
		if (empty($messageIds))
		{
			return;
		}

		MessageTable::updateMulti($messageIds, ['NOTIFY_READ' => 'Y'], true);
	}
}