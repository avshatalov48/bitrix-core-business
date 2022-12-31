<?php

namespace Bitrix\MessageService\Providers\Edna\SMS\Old;

use Bitrix\MessageService\Providers\Constants\InternalOption;
use Bitrix\MessageService\Providers\Edna\SMS\ExternalSender;

class Initiator extends \Bitrix\MessageService\Providers\Edna\Initiator
{
	/**
	 * @return array{array{id: int, name: string}}
	 */
	public function getFromList(): array
	{
		$fromList = [];
		if (!$this->supportChecker->canUse())
		{
			return $fromList;
		}

		$externalSender = new ExternalSender(
			$this->optionManager->getOption(InternalOption::API_KEY, ''),
			Constants::API_ENDPOINT
		);
		$apiResult = $externalSender->callExternalMethod('smsSubject/');
		if (!$apiResult->isSuccess())
		{
			return $fromList;
		}

		foreach ($apiResult->getData() as $subjectInfo)
		{
			if ($subjectInfo['active'])
			{
				$fromList[] = [
					'id' => $subjectInfo['subject'],
					'name' => $subjectInfo['subject'],
				];
			}
		}

		return $fromList;
	}

	/**
	 * @param string $from
	 * @return bool
	 */
	public function isCorrectFrom($from): bool
	{
		$fromList = $this->getFromList();
		foreach ($fromList as $item)
		{
			if ($from === $item['id'])
			{
				return true;
			}
		}
		return false;
	}

}