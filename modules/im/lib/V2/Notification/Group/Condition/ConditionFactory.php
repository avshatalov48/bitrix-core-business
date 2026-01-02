<?php

namespace Bitrix\Im\V2\Notification\Group\Condition;

use Bitrix\Im\Model\EO_NotifyGroupCondition_Collection;
use Bitrix\Im\Model\NotifyGroupConditionTable;
use Bitrix\Im\V2\Notification\Group\SystemGroup\SystemGroup;

class ConditionFactory
{
	public function makeByTag(string $tag, int $userId): Conditions
	{
		return match ($tag)
		{
			SystemGroup::Confirm->value => $this->makeOnlyConfirms(),
			SystemGroup::Mention->value => $this->makeMention(),
			SystemGroup::Admin->value => $this->makeAdmin(),
			default => $this->makeUserTag($tag, $userId),
		};
	}

	private function makeOnlyConfirms(): Conditions
	{
		return (new Conditions())
			->setOnlyConfirms(true)
		;
	}

	private function makeMention(): Conditions
	{
		return (new Conditions())
			->appendModuleEvent(new ModuleEventCondition('', 'mention'))
			->appendModuleEvent(new ModuleEventCondition('', 'mention_comment'))
		;
	}

	private function makeAdmin(): Conditions
	{
		return (new Conditions())
			->appendModuleEvent(new ModuleEventCondition('blog', 'log_notify_all_request'))
			->appendModuleEvent(new ModuleEventCondition('imbot', 'refresh_error'))
			->appendModuleEvent(new ModuleEventCondition('intranet', 'refresh_error'))
			->appendModuleEvent(new ModuleEventCondition('voximplant', 'notifications'))
			->appendModuleEvent(new ModuleEventCondition('voximplant', 'status_notifications'))
			->appendModuleEvent(new ModuleEventCondition('', 'admin_notification'))
		;
	}

	private function makeUserTag(string $tag, int $userId): Conditions
	{
		if (!is_numeric($tag) || $userId <= 0)
		{
			return new Conditions();
		}

		$id = (int)$tag;

		$collection = $this->getModelCollection($id, $userId);

		return $this->convertCollectionToConditionsDto($collection);
	}

	private function getModelCollection(int $id, int $userId): EO_NotifyGroupCondition_Collection
	{
		return NotifyGroupConditionTable::query()
			->where(NotifyGroupConditionTable::FIELD_GROUP_ID, $id)
			->where(NotifyGroupConditionTable::FIELD_USER_ID, $userId)
			->setSelect([
				 NotifyGroupConditionTable::FIELD_ID,
				 NotifyGroupConditionTable::FIELD_MODULE,
				 NotifyGroupConditionTable::FIELD_EVENT,
			])
			->fetchCollection()
		;
	}

	public function convertCollectionToConditionsDto(EO_NotifyGroupCondition_Collection $collection): Conditions
	{
		$conditions = new Conditions();
		foreach ($collection as $model)
		{
			$item = new ModuleEventCondition($model->getModule(), $model->getEvent());
			$conditions->appendModuleEvent($item);
		}

		return $conditions;
	}
}