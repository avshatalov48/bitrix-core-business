<?php

declare(strict_types=1);

namespace Bitrix\Im\V2\Promotion\Event\Update;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Promotion\Promotion;
use Bitrix\Im\V2\Pull\BaseEvent;
use Bitrix\Im\V2\Pull\Dto\Diff;
use Bitrix\Im\V2\Pull\EventType;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Validation\ValidationService;

class PromotionUpdateEvent extends BaseEvent
{
	/** @param array<int, array> $sortedByUserData */
	private array $sortedByUserData;
	private ValidationService $validation;

	/** @param array<int, PromotionUpdateData> $sortedByUserData */
	public function __construct(array $sortedByUserData)
	{
		parent::__construct();

		$this->validation = ServiceLocator::getInstance()->get('main.validation.service');
		$this->sortedByUserData = $this->prepareData($sortedByUserData);
	}

	/**
	 * @param array<int, PromotionUpdateData> $sortedByUserData
	 * @return array<int, array>
	 */
	private function prepareData(array $sortedByUserData): array
	{
		if (Promotion::isUnavailable())
		{
			return [];
		}

		$preparedData = [];
		foreach ($sortedByUserData as $userId => $command)
		{
			$validationResult = $this->validation->validate($command);
			if ($validationResult->isSuccess())
			{
				$preparedData[$userId] = $command->toRestFormat();
			}
		}

		return $preparedData;
	}

	protected function getRecipients(): array
	{
		return array_keys($this->sortedByUserData);
	}

	protected function getDiffByUser(int $userId): Diff
	{
		return new Diff($userId, $this->sortedByUserData[$userId]);
	}

	protected function getType(): EventType
	{
		return EventType::PromotionUpdated;
	}

	protected function getBasePullParamsInternal(): array
	{
		return [];
	}

	public function getTarget(): ?Chat
	{
		return null;
	}
}
