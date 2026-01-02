<?php

namespace Bitrix\Im\V2\Controller\Notify;

use Bitrix\Im\V2\Controller\BaseController;
use Bitrix\Im\V2\Controller\Filter\CheckNotifyGroupAccess;
use Bitrix\Im\V2\Notification\Group\UserGroup\Dto\UpdateGroupDto;
use Bitrix\Im\V2\Notification\Group\UserGroup\Dto\UserGroupDto;
use Bitrix\Im\V2\Notification\Group\GroupItemFactory;
use Bitrix\Im\V2\Notification\Group\UserGroup\Dto\CreateGroupDto;
use Bitrix\Im\V2\Notification\Group\UserGroup\GroupRepository;
use Bitrix\Main\Engine\AutoWire\BinderArgumentException;
use Bitrix\Main\Engine\AutoWire\ExactParameter;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Request;

class Group extends BaseController
{
	private readonly GroupRepository $groupRepository;

	public function __construct(Request $request = null)
	{
		parent::__construct($request);
		$this->groupRepository = new GroupRepository();
	}

	/**
	 * @throws BinderArgumentException
	 */
	public function getAutoWiredParameters(): array
	{
		return [
			new ExactParameter(
				UserGroupDto::class,
				'userGroup',
				function(string $className, int $groupId): UserGroupDto
				{
					return new UserGroupDto($groupId, (int)CurrentUser::get()?->getId());
				}
			),
		];
	}

	protected function getDefaultPreFilters(): array
	{
		return array_merge(
			parent::getDefaultPreFilters(),
			[
				new CheckNotifyGroupAccess(),
			]
		);
	}

	public function addAction(string $title, string $module, string $event): ?array
	{
		$createDto = new CreateGroupDto(
			title: $title,
			userId: (int)CurrentUser::get()?->getId(),
			module: $module,
			event: $event,
		);

		$result = $this->groupRepository->create($createDto);
		if ($result->isSuccess())
		{
			return [
				'id' => $result->id,
			];
		}

		$this->addErrors($result->getErrors());

		return null;
	}

	public function listAction(): array
	{
		return [
			'items' => (new GroupItemFactory())->makeForCurrentContextUser()->toRestFormat(),
		];
	}

	public function deleteAction(UserGroupDto $userGroup): array
	{
		$this->groupRepository->deleteById($userGroup->groupId);

		return ['result' => true];
	}

	public function updateAction(UserGroupDto $userGroup, string $title): ?array
	{
		$updateDto = new UpdateGroupDto(
			title: $title,
			groupId: $userGroup->groupId,
		);
		$result = $this->groupRepository->update($updateDto);
		if ($result->isSuccess())
		{
			return ['result' => true];
		}

		$this->addErrors($result->getErrors());

		return null;
	}
}