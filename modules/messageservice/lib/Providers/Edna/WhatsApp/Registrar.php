<?php

namespace Bitrix\MessageService\Providers\Edna\WhatsApp;

use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use \Bitrix\MessageService\Providers;

class Registrar extends Providers\Base\Registrar implements Providers\SupportChecker
{
	protected EdnaRu $utils;

	public function __construct(string $providerId, Providers\OptionManager $optionManager, EdnaRu $utils)
	{
		parent::__construct($providerId, $optionManager);

		$this->utils = $utils;
	}

	public function isRegistered(): bool
	{
		return
			!is_null($this->optionManager->getOption(Constants::API_KEY_OPTION))
			&& !is_null($this->optionManager->getOption(Constants::SENDER_ID_OPTION));
	}

	public function register(array $fields): Result
	{
		if (isset($fields['subject_id']))
		{
			$fields[Constants::SENDER_ID_OPTION] = $fields['subject_id'];
		}

		if (!isset($fields[Constants::API_KEY_OPTION], $fields[Constants::SENDER_ID_OPTION]))
		{
			return (new Result())->addError(new Error(Loc::getMessage('MESSAGESERVICE_SENDER_SMS_EDNARU_EMPTY_REQUIRED_FIELDS')));
		}
		$this->optionManager->setOption(Constants::API_KEY_OPTION, (string)$fields[Constants::API_KEY_OPTION]);

		$subjectIds = [];
		foreach (explode(';', (string)$fields[Constants::SENDER_ID_OPTION]) as $senderId)
		{
			$senderId = trim($senderId);
			if ($senderId !== '')
			{
				$subjectIds[] = (int)$senderId;
			}
		}
		if (!$this->checkSubjectIds($subjectIds))
		{
			return (new Result())->addError(new Error('Invalid sender ids'));
		}

		$this->optionManager->setOption(Constants::SENDER_ID_OPTION, $subjectIds);

		return new Result();
	}

	public function getOwnerInfo(): array
	{
		return [
			Constants::API_KEY_OPTION => $this->optionManager->getOption(Constants::API_KEY_OPTION),
			Constants::SENDER_ID_OPTION => $this->optionManager->getOption(Constants::SENDER_ID_OPTION),
		];
	}

	public function isSupported(): bool
	{
		$region = \Bitrix\Main\Application::getInstance()->getLicense()->getRegion();

		return in_array($region, ['ru', 'kz', 'by']);
	}

	public function canUse(): bool
	{
		return ($this->isRegistered() && $this->isConfirmed());
	}

	public function getExternalManageUrl(): string
	{
		return 'https://app.edna.ru/';
	}

	protected function checkSubjectIds($subjectId): bool
	{
		if (empty($subjectId))
		{
			return false;
		}

		$channelResult = $this->utils->getChannelList();
		if (!$channelResult->isSuccess())
		{
			return false;
		}

		$checkedChannels = [];
		$channelList = $channelResult->getData();
		foreach ($channelList as $channel)
		{
			if (isset($channel['subjectId']) && in_array($channel['subjectId'], $subjectId, true))
			{
				$checkedChannels[] = $channel['subjectId'];
			}
		}

		return count($checkedChannels) === count($subjectId);
	}
}