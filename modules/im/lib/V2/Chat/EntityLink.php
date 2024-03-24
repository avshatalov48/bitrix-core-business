<?php

namespace Bitrix\Im\V2\Chat;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Chat\EntityLink\CalendarType;
use Bitrix\Im\V2\Chat\EntityLink\CrmType;
use Bitrix\Im\V2\Chat\EntityLink\MailType;
use Bitrix\Im\V2\Chat\EntityLink\SonetType;
use Bitrix\Im\V2\Chat\EntityLink\TasksType;
use Bitrix\Im\V2\Chat\EntityLink\CallType;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Rest\RestConvertible;
use Bitrix\Main\Application;
use Bitrix\Main\Loader;

class EntityLink implements RestConvertible
{
	use ContextCustomer;

	public const TYPE_TASKS = 'TASKS';
	public const TYPE_SONET = 'SONET_GROUP';
	public const TYPE_CRM = 'CRM';
	public const TYPE_MAIL = 'MAIL';
	public const TYPE_CALL = 'CALL';

	protected const HAS_URL = false;

	private const CACHE_TTL = 18144000;

	protected int $chatId;
	protected string $entityId = '';
	protected string $type = '';
	protected string $url = '';

	protected function __construct()
	{
	}

	public static function getInstance(Chat $chat): self
	{
		$type = $chat->getEntityType() ?? '';
		if ($type === self::TYPE_SONET && Loader::includeModule('socialnetwork'))
		{
			$instance = new SonetType();
		}
		elseif ($type === self::TYPE_TASKS && Loader::includeModule('tasks'))
		{
			$instance = new TasksType();
		}
		elseif (Loader::includeModule('calendar') && $type === \CCalendar::CALENDAR_CHAT_ENTITY_TYPE)
		{
			$instance = new CalendarType();
		}
		elseif ($type === self::TYPE_CRM && Loader::includeModule('crm'))
		{
			$instance = new CrmType($chat->getEntityId() ?? '');
		}
		elseif ($type === self::TYPE_CALL && Loader::includeModule('crm'))
		{
			$instance = new CallType($chat->getEntityData1() ?? '');
		}
		elseif ($type === self::TYPE_MAIL && Loader::includeModule('mail'))
		{
			$instance = new MailType();
		}
		else
		{
			$instance = new self();
		}

		$instance->type = $instance->type ?: $type;
		$instance->chatId = $chat->getId() ?? 0;
		$instance->entityId = $chat->getEntityId() ?? '';
		$instance->fillUrl();

		return $instance;
	}

	private function fillUrl(): void
	{
		if (!static::HAS_URL)
		{
			return;
		}

		$cache = Application::getInstance()->getCache();
		if ($cache->initCache(self::CACHE_TTL, $this->getCacheId(), $this->getCacheDir()))
		{
			$cachedEntityUrl = $cache->getVars();

			if (!is_array($cachedEntityUrl))
			{
				$cachedEntityUrl = [];
			}

			$this->url = $cachedEntityUrl['url'] ?? '';
			return;
		}

		$this->url = $this->getUrl();
		$cache->startDataCache();
		$cache->endDataCache(['url' => $this->url]);
	}

	public static function cleanCache(int $chatId): void
	{
		Application::getInstance()->getCache()->cleanDir(static::getCacheDirByChatId($chatId));
	}

	private function getCacheDir(): string
	{
		return static::getCacheDirByChatId($this->chatId);
	}

	private static function getCacheDirByChatId(int $chatId): string
	{
		$cacheSubDir = $chatId % 100;

		return "/bx/imc/chatentitylink/1/{$cacheSubDir}/{$chatId}";
	}

	private function getCacheId(): string
	{
		return "chat_entity_link_{$this->chatId}";
	}

	protected function getUrl(): string
	{
		return '';
	}

	protected function getRestType(): string
	{
		return $this->type;
	}

	public static function getRestEntityName(): string
	{
		return 'entityLink';
	}

	public function toRestFormat(array $option = []): array
	{
		return [
			'type' => $this->getRestType(),
			'url' => $this->url,
		];
	}

	/**
	 * @deprecated
	 * @see \Bitrix\Im\V2\Chat\EntityLink::toRestFormat
	 */
	public function toArray(array $options = []): array
	{
		return [
			'TYPE' => $this->getRestType(),
			'URL' => $this->url,
		];
	}
}