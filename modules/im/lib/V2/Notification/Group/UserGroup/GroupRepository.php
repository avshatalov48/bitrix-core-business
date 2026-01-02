<?php

namespace Bitrix\Im\V2\Notification\Group\UserGroup;

use Bitrix\Im\Model\EO_NotifyGroup;
use Bitrix\Im\Model\NotifyGroupTable;
use Bitrix\Im\V2\Notification\Group\UserGroup\Dto\ConditionDto;
use Bitrix\Im\V2\Notification\Group\UserGroup\Dto\CreateGroupDto;
use Bitrix\Im\V2\Notification\Group\UserGroup\Dto\UpdateGroupDto;
use Bitrix\Im\V2\Notification\Group\UserGroup\Result\GroupCreateResult;
use Bitrix\Im\V2\Notification\NotifyError;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\ORM\Data\AddResult;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Validation\ValidationService;
use Bitrix\Main\Result;

class GroupRepository
{
	private ValidationService $validation;
	private ConditionRepository $conditionRepository;

	public function __construct()
	{
		$this->validation = ServiceLocator::getInstance()->get('main.validation.service');
		$this->conditionRepository = new ConditionRepository();
	}

	public function create(CreateGroupDto $createGroupDto): GroupCreateResult
	{
		$result = $this->validation->validate($createGroupDto);
		if (!$result->isSuccess())
		{
			return (new GroupCreateResult())->addErrors($result->getErrors());
		}

		$saveResult = $this->save($createGroupDto);
		if (!$saveResult->isSuccess())
		{
			return (new GroupCreateResult())->addError(new NotifyError(NotifyError::GROUP_CREATE_ERROR));
		}
		$groupId = $saveResult->getId();

		$result = $this->conditionRepository->add(
			new ConditionDto(
				module: $createGroupDto->module,
				event: $createGroupDto->event,
				userId: $createGroupDto->userId,
				groupId: $groupId,
			)
		);

		if (!$result->isSuccess())
		{
			return (new GroupCreateResult())->addErrors($result->getErrors());
		}

		return new GroupCreateResult($groupId);
	}

	private function save(CreateGroupDto $createGroupDto): AddResult
	{
		return (new EO_NotifyGroup())
			->setTitle($createGroupDto->title)
			->setUserId($createGroupDto->userId)
			->save()
		;
	}

	public function update(UpdateGroupDto $updateGroupDto): Result
	{
		$result = $this->validation->validate($updateGroupDto);
		if (!$result->isSuccess())
		{
			return $result;
		}

		return NotifyGroupTable::update($updateGroupDto->groupId, [
			NotifyGroupTable::FIELD_TITLE => $updateGroupDto->title,
			NotifyGroupTable::FIELD_DATE_UPDATE => new DateTime(),
		]);
	}

	public function delete(EO_NotifyGroup $model): void
	{
		$this->conditionRepository->deleteByGroupId($model->getId());
		$model->delete();
	}

	public function deleteById(int $id, bool $withConditions = true): void
	{
		if ($withConditions)
		{
			$this->conditionRepository->deleteByGroupId($id);
		}

		NotifyGroupTable::deleteByFilter([
			NotifyGroupTable::FIELD_ID => $id,
		]);
	}

	public function isExistsWithUserId(int $id, int $userId): bool
	{
		$row = NotifyGroupTable::query()
			->where(NotifyGroupTable::FIELD_ID, $id)
			->where(NotifyGroupTable::FIELD_USER_ID, $userId)
			->setSelect([NotifyGroupTable::FIELD_ID])
			->setLimit(1)
			->exec()
			->fetch()
		;

		return !empty($row);
	}
}