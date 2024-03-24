<?php

namespace Bitrix\MessageService\Sender\Sms;

use Bitrix\Main\Result;
use Bitrix\MessageService\Providers\Base\Option;
use Bitrix\MessageService\Providers\CacheManager;
use Bitrix\MessageService\Providers\Constants\InternalOption;
use Bitrix\MessageService\Providers\Edna;
use Bitrix\MessageService\Providers\Edna\SMS;
use Bitrix\MessageService\Sender;

class SmsEdnaru extends Sender\BaseConfigurable
{
	use Sender\Traits\RussianProvider;

	protected Edna\EdnaRu $utils;

	public const ID = 'smsednaru';

	public function __construct()
	{
		$this->informant = new SMS\Informant();
		$this->optionManager = new Option($this->getType(), $this->getId());
		$this->utils = new SMS\Utils($this->getId(), $this->optionManager);

		if (!$this->isMigratedToNewAPI())
		{
			$this->initializeOldApiComponents();

			return;
		}
		$this->initializeNewApiComponents();

		$this->migrateToNewApi();
	}

	public function getId(): string
	{
		return $this->informant->getId();
	}

	public function getName(): string
	{
		return $this->informant->getName();
	}

	public function getShortName(): string
	{
		return $this->informant->getShortName();
	}

	public function getType(): string
	{
		return $this->informant->getType();
	}

	public function getManageUrl(): string
	{
		return $this->informant->getManageUrl();
	}

	public function getExternalId(): string
	{
		return $this->informant->getExternalId();
	}

	public function isRegistered(): bool
	{
		return $this->registrar->isRegistered();
	}

	public function register(array $fields): Result
	{
		$result = $this->registrar->register($fields);
		if ($result->isSuccess())
		{
			\Bitrix\Main\Application::getInstance()->addBackgroundJob([$this, 'refreshFromList']);
			\Bitrix\Main\Application::getInstance()->addBackgroundJob([$this, 'addRefreshFromListAgent']);
		}
		return $result;
	}

	public function getOwnerInfo(): array
	{
		return $this->registrar->getOwnerInfo();
	}

	public function getExternalManageUrl(): string
	{
		return $this->registrar->getExternalManageUrl();
	}

	public function getCallbackUrl(): string
	{
		return $this->registrar->getCallbackUrl();
	}

	public function isConfirmed(): bool
	{
		return $this->registrar->isConfirmed();
	}

	public function confirmRegistration(array $fields): Result
	{
		return $this->registrar->confirmRegistration($fields);
	}

	public function sendConfirmationCode(): Result
	{
		return $this->registrar->sendConfirmationCode();
	}

	/**
	 * @return array<array{id: int, name: string, channelPhone: string}>
	 */
	public function getFromList(): array
	{
		return $this->initiator->getFromList();
	}

	/**
	 * The agent's goal is regular refreshing FromList.
	 * @return void
	 */
	public function refreshFromList(): void
	{
		$this->utils->updateSavedChannelList($this->initiator->getChannelType());
	}

	public function isCorrectFrom($from): bool
	{
		return $this->initiator->isCorrectFrom($from);
	}

	public function getDefaultFrom(): ?string
	{
		return $this->initiator->getDefaultFrom();
	}

	public function getFirstFromList()
	{
		return $this->initiator->getDefaultFrom();
	}

	public function getMessageStatus(array $messageFields): Sender\Result\MessageStatus
	{
		return $this->sender->getMessageStatus($messageFields);
	}

	public function sendMessage(array $messageFields): Sender\Result\SendMessage
	{
		return $this->sender->sendMessage($messageFields);
	}

	public function prepareMessageBodyForSave(string $text): string
	{
		return $this->sender->prepareMessageBodyForSave($text);
	}

	public function getMessageTemplates(string $subject = ''): Result
	{
		return $this->utils->getMessageTemplates($subject);
	}

	public function getSentTemplateMessage(string $from, string $to): string
	{
		return $this->utils->getSentTemplateMessage($from, $to);
	}

	public function setSocketTimeout(int $socketTimeout): Sender\Base
	{
		$this->optionManager->setSocketTimeout($socketTimeout);
		return $this;
	}

	public function setStreamTimeout(int $streamTimeout): Sender\Base
	{
		$this->optionManager->setStreamTimeout($streamTimeout);
		return $this;
	}

	public static function resolveStatus($serviceStatus): ?int
	{
		return (new SMS\StatusResolver())->resolveStatus($serviceStatus);
	}

	public function getRegistrationUrl(): string
	{
		return 'https://edna.ru/sms-bitrix/';
	}

	public function isMigratedToNewAPI(): bool
	{
		$isMigratedToNewAPI = \Bitrix\Main\Config\Option::get('messageservice',	$this->getMigratingOptionName(), 'N');
		if ($isMigratedToNewAPI === 'Y')
		{
			return true;
		}

		$currentDateTime = time();
		$migratedDateTime = gmmktime(-3, 15,0, 12,1,2022); //December 01, 2022 00:15 MSK

		return $currentDateTime >= $migratedDateTime;
	}

	private function initializeOldApiComponents(): void
	{
		$this->registrar = new SMS\Old\Registrar($this->getId(), $this->optionManager, $this->utils);
		$this->initiator = new SMS\Old\Initiator($this->optionManager, $this->registrar, $this->utils, $this->getId());
		$this->sender = new SMS\Old\Sender($this->optionManager,$this->registrar, $this->utils);
	}

	private function initializeNewApiComponents(): void
	{
		$this->registrar = new SMS\Registrar($this->getId(), $this->optionManager, $this->utils);
		$this->initiator = new SMS\Initiator($this->optionManager, $this->registrar, $this->utils, $this->getId());
		$this->sender = new SMS\Sender($this->optionManager,$this->registrar, $this->utils);
	}

	public function migrateToNewApi(): void
	{
		$oldRegistrar = new SMS\Old\Registrar($this->getId(), $this->optionManager, $this->utils);

		if (!$this->canUse() && !$oldRegistrar->canUse())
		{
			return;
		}

		if ($this->getOption(InternalOption::SENDER_ID) !== null)
		{
			return;
		}

		$channelListResult = $this->utils->getChannelList(Edna\Constants\ChannelType::SMS);

		$subjectIdList = [];
		foreach ($channelListResult->getData() as $channel)
		{
			if (isset($channel['active'], $channel['subjectId']) && $channel['active'] === true)
			{
				$subjectIdList[] = $channel['subjectId'];
				$this->utils->setCallback(
					$this->getCallbackUrl(),
					[
						Edna\Constants\CallbackType::MESSAGE_STATUS
					],
					$channel['subjectId']
				);
			}
		}
		$this->setOption(InternalOption::SENDER_ID, $subjectIdList);
		$this->setNewApi(true);
	}

	public function setNewApi(bool $mode): void
	{
		\Bitrix\Main\Config\Option::set('messageservice', $this->getMigratingOptionName(),$mode ? 'Y' : 'N');
	}

	private function getMigratingOptionName(): string
	{
		return $this->getId() . '_' . InternalOption::NEW_API_AVAILABLE;
	}

	/**
	 * Adds agent for execution.
	 * @return void
	 * @see refreshFromListAgent
	 */
	public function addRefreshFromListAgent(): void
	{
		$cacheManager = new CacheManager($this->getId());
		$period = (int)ceil( $cacheManager->getTtl(CacheManager::CHANNEL_CACHE_ENTITY_ID) * .9);// async with cache expiration

		\CAgent::AddAgent(static::class . "::refreshFromListAgent();", 'messageservice', 'Y', $period);
	}

	/**
	 * The agent's goal is regular refreshing FromList cache.
	 * @return string
	 */
	public static function refreshFromListAgent(): string
	{
		$sender = new static();
		if (!$sender::isSupported() || !$sender->isRegistered())
		{
			return '';
		}

		$sender->refreshFromList();

		return __METHOD__ . '();';
	}
}