<?php

namespace Bitrix\MessageService\Providers\Edna;

use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use Bitrix\MessageService\Providers\Constants\InternalOption;
use Bitrix\MessageService\Providers\Edna\EdnaRu;
use Bitrix\MessageService\Providers;
use Bitrix\MessageService\Providers\Edna;
use Bitrix\MessageService\Sender\Traits\RussianProvider;

abstract class Registrar extends Providers\Base\Registrar implements Providers\SupportChecker
{

	use RussianProvider { RussianProvider::isSupported as isRussianRegion; }

	protected EdnaRu $utils;
	protected string $channelType = '';

	abstract protected function getCallbackTypeList(): array;

	public function __construct(string $providerId, Providers\OptionManager $optionManager, EdnaRu $utils)
	{
		parent::__construct($providerId, $optionManager);

		$this->utils = $utils;
	}

	public function isRegistered(): bool
	{
		return
			!is_null($this->optionManager->getOption(InternalOption::API_KEY))
			&& !is_null($this->optionManager->getOption(InternalOption::SENDER_ID));
	}

	public function register(array $fields): Result
	{
		if (isset($fields['subject_id']))
		{
			$fields[InternalOption::SENDER_ID] = $fields['subject_id'];
		}

		if (!isset($fields[InternalOption::API_KEY], $fields[InternalOption::SENDER_ID]))
		{
			return (new Result())->addError(new Error(Loc::getMessage('MESSAGESERVICE_SENDER_SMS_EDNARU_EMPTY_REQUIRED_FIELDS')));
		}
		$this->optionManager->setOption(InternalOption::API_KEY, (string)$fields[InternalOption::API_KEY]);

		$subjectIdList = [];
		foreach (explode(';', (string)$fields[InternalOption::SENDER_ID]) as $senderId)
		{
			$senderId = trim($senderId);
			if ($senderId !== '')
			{
				$subjectIdList[] = (int)$senderId;
			}
		}
		if (!$this->utils->checkActiveChannelBySubjectIdList($subjectIdList, $this->channelType))
		{
			$this->optionManager->clearOptions();

			return (new Result())->addError(new Error(Loc::getMessage('MESSAGESERVICE_EDNARU_INACTIVE_CHANNEL_ERROR')));
		}

		foreach ($subjectIdList as $subjectId)
		{
			$setCallbackResult = $this->utils->setCallback(
				$this->getCallbackUrl(),
				$this->getCallbackTypeList(),
				$subjectId
			);
			if (!$setCallbackResult->isSuccess())
			{
				$this->optionManager->clearOptions();

				$errorData = $setCallbackResult->getData();

				if (isset($errorData['detail']))
				{
					return (new Result())->addError(new Error($errorData['detail']));
				}

				return $setCallbackResult;
			}
		}

		$this->optionManager->setOption(InternalOption::SENDER_ID, $subjectIdList);
		$this->optionManager->setOption(InternalOption::MIGRATED_TO_STANDART_SETTING_NAMES, 'Y');

		return new Result();
	}

	/**
	 * @return array{api_key: string, sender_id: array}
	 */
	public function getOwnerInfo(): array
	{
		return [
			InternalOption::API_KEY => $this->optionManager->getOption(InternalOption::API_KEY),
			InternalOption::SENDER_ID => $this->optionManager->getOption(InternalOption::SENDER_ID),
		];
	}

	public function isSupported(): bool
	{
		return self::isRussianRegion();
	}

	public function canUse(): bool
	{
		return ($this->isRegistered() && $this->isConfirmed());
	}

	public function getExternalManageUrl(): string
	{
		return 'https://app.edna.ru/';
	}
}