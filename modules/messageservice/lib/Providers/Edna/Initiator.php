<?php

namespace Bitrix\MessageService\Providers\Edna;

use Bitrix\MessageService\Providers;

class Initiator extends Providers\Base\Initiator
{
	protected Providers\OptionManager $optionManager;
	protected Providers\SupportChecker $supportChecker;
	protected EdnaRu $utils;
	protected string $channelType = '';

	public function __construct(
		Providers\OptionManager $optionManager,
		Providers\SupportChecker $supportChecker,
		EdnaRu $utils
	)
	{
		$this->optionManager = $optionManager;
		$this->supportChecker = $supportChecker;
		$this->utils = $utils;
	}

	public function getFromList(): array
	{
		if (!$this->supportChecker->canUse())
		{
			return [];
		}

		$activeChannelListResult = $this->utils->getActiveChannelList($this->channelType);
		if (!$activeChannelListResult->isSuccess())
		{
			return [];
		}

		$registeredSubjectIdList = $this->optionManager->getOption(Providers\Constants\InternalOption::SENDER_ID, []);
		$fromList = [];
		foreach ($activeChannelListResult->getData() as $channel)
		{
			if (in_array((int)$channel['subjectId'], $registeredSubjectIdList,true))
			{
				$fromList[] = [
					'id' => $channel['subjectId'],
					'name' => $channel['name'],
				];
			}
		}

		return $fromList;
	}

	public function isCorrectFrom($from): bool
	{
		$fromList = $this->getFromList();
		foreach ($fromList as $item)
		{
			if ((int)$from === $item['id'])
			{
				return true;
			}
		}
		return false;
	}
}