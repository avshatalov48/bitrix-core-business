<?php

namespace Bitrix\Im\V2\Controller\Notify;

use Bitrix\Im\V2\Controller\BaseController;
use Bitrix\Im\V2\Controller\Filter\CheckNotifyGroupAccess;
use Bitrix\Im\V2\Notification\Group\UserGroup\ConditionRepository;
use Bitrix\Im\V2\Notification\Group\UserGroup\Dto\ConditionDto;
use Bitrix\Main\Engine\AutoWire\ExactParameter;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Request;

class Condition extends BaseController
{
	private readonly ConditionRepository $conditionRepository;

	public function __construct(Request $request = null)
	{
		parent::__construct($request);
		$this->conditionRepository = new ConditionRepository();
	}

	public function getAutoWiredParameters(): array
	{
		return [
			new ExactParameter(
				ConditionDto::class,
				'conditionDto',
				function(string $className, int $groupId, string $module, string $event): ConditionDto
				{
					return new ConditionDto(
						module: $module,
						event: $event,
						userId: (int)CurrentUser::get()?->getId(),
						groupId: $groupId,
					);
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

	public function addAction(ConditionDto $conditionDto): ?array
	{
		$result = $this->conditionRepository->add($conditionDto);
		if ($result->isSuccess())
		{
			return [
				'id' => $result->id,
			];
		}

		$this->addErrors($result->getErrors());

		return null;
	}

	public function deleteAction(ConditionDto $conditionDto): array
	{
		$this->conditionRepository->deleteByDto($conditionDto);

		return ['result' => true];
	}
}