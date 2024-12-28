<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\User\Stepper;

use Bitrix\Main\Update\Stepper;
use Bitrix\Socialnetwork\Collab\Integration\IM\ActionType;
use Bitrix\Socialnetwork\Collab\Integration\IM\ActionMessageFactory;

class SendCollabLeaveMessageStepper extends Stepper
{
	protected const LIMIT = 50;

	protected const FIRED_ID_INDEX = 0;
	protected const WHO_FIRED_ID_INDEX = 1;
	protected const COLLABS_INDEX = 2;

	protected static $moduleId = 'socialnetwork';

	public function execute(array &$option): bool
	{
		$parameters = $this->getOuterParams();

		$firedId = $option['firedId'] ?? $parameters[static::FIRED_ID_INDEX];
		$whoFiredId = $option['whoFiredId'] ?? $parameters[static::WHO_FIRED_ID_INDEX];
		$collabs = $option['collabs'] ?? $parameters[static::COLLABS_INDEX];

		$collabs = unserialize($collabs, ['allowed_classes' => false]);

		if (empty($collabs) || $firedId <= 0 || $whoFiredId <= 0)
		{
			return static::FINISH_EXECUTION;
		}

		$collabSlice = array_splice($collabs, 0, static::LIMIT);

		$option['firedId'] = $firedId;
		$option['whoFiredId'] = $whoFiredId;
		$option['collabs'] = serialize($collabs);

		$factory = ActionMessageFactory::getInstance();
		foreach ($collabSlice as $collabId)
		{
			$factory->getActionMessage(ActionType::ExcludeUser, $collabId, $whoFiredId)
				->runAction([$firedId]);
		}

		return static::CONTINUE_EXECUTION;
	}
}