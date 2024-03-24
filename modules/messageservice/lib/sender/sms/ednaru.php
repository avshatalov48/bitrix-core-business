<?php

namespace Bitrix\MessageService\Sender\Sms;

use Bitrix\Main\Result;
use Bitrix\MessageService\Providers;
use Bitrix\MessageService\Providers\Base;
use Bitrix\MessageService\Providers\CacheManager;
use Bitrix\MessageService\Providers\Edna\RegionHelper;
use Bitrix\MessageService\Providers\Edna\WhatsApp;
use Bitrix\MessageService\Sender;

class Ednaru extends Sender\BaseConfigurable
{
	public const ID = 'ednaru';
	public const DISABLE_INTERNATIONAL = 'disable_international';

	protected Providers\Edna\EdnaRu $utils;
	protected WhatsApp\EmojiConverter $emoji;

	public function __construct()
	{
		$this->informant = new WhatsApp\Informant();
		$this->optionManager = new Base\Option($this->getType(), $this->getId());
		$this->utils = new WhatsApp\Utils($this->getId(), $this->optionManager);
		$this->registrar = new WhatsApp\Registrar($this->getId(), $this->optionManager, $this->utils);
		$this->initiator = new WhatsApp\Initiator($this->optionManager, $this->registrar, $this->utils, $this->getId());
		$emojiConverter = new WhatsApp\EmojiConverter();
		$this->sender = new WhatsApp\Sender($this->optionManager, $this->registrar, $this->utils, $emojiConverter);
		$this->templateManager = new WhatsApp\TemplateManager($this->getId(), $this->utils, $emojiConverter);
	}

	public function isAvailable(): bool
	{
		return self::isSupported();
	}

	public static function isSupported()
	{
		if (
			RegionHelper::isInternational()
			&& \COption::GetOptionString('messageservice', self::DISABLE_INTERNATIONAL) === 'Y'
		)
		{
			return false;
		}

		return parent::isSupported();
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

	public function getMessageStatus(array $messageFields): Sender\Result\MessageStatus
	{
		return $this->sender->getMessageStatus($messageFields);
	}

	public function sendMessage(array $messageFields): Sender\Result\SendMessage
	{
		return $this->sender->sendMessage($messageFields);
	}

	public function testConnection(): Result
	{
		return (new WhatsApp\ConnectorLine($this->utils))->testConnection();
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

	public static function resolveStatus($serviceStatus): ?int
	{
		return (new WhatsApp\StatusResolver())->resolveStatus($serviceStatus);
	}

	public function getLineId(): ?int
	{
		return (new WhatsApp\ConnectorLine($this->utils))->getLineId();
	}

	public function getCallbackUrl(): string
	{
		return $this->registrar->getCallbackUrl();
	}

	/**
	 * @inheritDoc
	 */
	public function isTemplatesBased(): bool
	{
		return $this->templateManager->isTemplatesBased();
	}

	public function isCorrectFrom($from)
	{
		return $this->initiator->isCorrectFrom($from);
	}

	/**
	 * @inheritDoc
	 */
	public function getTemplatesList(array $context = null): array
	{
		return $this->templateManager->getTemplatesList($context);
	}

	/**
	 * @inheritDoc
	 */
	public function prepareTemplate($templateData): array
	{
		return $this->templateManager->prepareTemplate($templateData);
	}

	public function getMessageTemplates(string $subject = ''): Result
	{
		return $this->utils->getMessageTemplates($subject);
	}

	public function getManageUrl(): string
	{
		return $this->informant->getManageUrl();
	}

	public function getSentTemplateMessage(string $from, string $to): string
	{
		return $this->utils->getSentTemplateMessage($from, $to);
	}

	public function prepareMessageBodyForSave(string $text): string
	{
		return $this->sender->prepareMessageBodyForSave($text);
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