<?php

namespace Bitrix\Im\V2\Notification\Group\UserGroup;

use Bitrix\Im\Model\EO_NotifyGroupCondition;
use Bitrix\Im\Model\NotifyGroupConditionTable;
use Bitrix\Im\V2\Notification\Group\UserGroup\Dto\ConditionDto;
use Bitrix\Im\V2\Notification\Group\UserGroup\Result\ConditionCreateResult;
use Bitrix\Im\V2\Notification\NotifyError;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Validation\ValidationService;

class ConditionRepository
{
	private readonly ValidationService $validation;

	public function __construct()
	{
		$this->validation = ServiceLocator::getInstance()->get('main.validation.service');
	}

	public function add(ConditionDto $conditionDto): ConditionCreateResult
	{
		$result = $this->validation->validate($conditionDto);
		if (!$result->isSuccess())
		{
			return (new ConditionCreateResult())->addErrors($result->getErrors());
		}

		$result = (new EO_NotifyGroupCondition())
			->setGroupId($conditionDto->groupId)
			->setModule($conditionDto->module)
			->setEvent($conditionDto->event)
			->setUserId($conditionDto->userId)
			->save()
		;

		if ($result->isSuccess())
		{
			return new ConditionCreateResult($result->getId());
		}

		return (new ConditionCreateResult())->addError(new NotifyError(NotifyError::CONDITION_ALREADY_EXISTS));
	}

	public function deleteByGroupId(int $groupId): void
	{
		NotifyGroupConditionTable::deleteByFilter([
			NotifyGroupConditionTable::FIELD_GROUP_ID => $groupId,
		]);

		$this->deleteGroupIfEmpty($groupId);
	}

	public function deleteByDto(ConditionDto $conditionDto): void
	{
		NotifyGroupConditionTable::deleteByFilter([
			NotifyGroupConditionTable::FIELD_GROUP_ID => $conditionDto->groupId,
			NotifyGroupConditionTable::FIELD_MODULE => $conditionDto->module,
			NotifyGroupConditionTable::FIELD_EVENT => $conditionDto->event,
			NotifyGroupConditionTable::FIELD_USER_ID => $conditionDto->userId,
		]);

		$this->deleteGroupIfEmpty($conditionDto->groupId);
	}

	private function deleteGroupIfEmpty(int $groupId): void
	{
		if (!$this->isExistsByGroupId($groupId))
		{
			(new GroupRepository())->deleteById($groupId, false);
		}
	}

	private function isExistsByGroupId(int $groupId): bool
	{
		$row = NotifyGroupConditionTable::query()
			->where(NotifyGroupConditionTable::FIELD_GROUP_ID, $groupId)
			->setSelect([NotifyGroupConditionTable::FIELD_ID])
			->setLimit(1)
			->fetch()
		;

		return !empty($row);
	}
}