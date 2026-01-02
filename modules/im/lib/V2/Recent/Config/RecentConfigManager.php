<?php

namespace Bitrix\Im\V2\Recent\Config;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Chat\ExternalChat\ExternalTypeRegistry;
use Bitrix\Im\V2\Chat\ExtendedType;
use Bitrix\Main\Engine\Response\Converter;

class RecentConfigManager
{
	public const EXTERNAL_CHAT_USE_DEFAULT_RECENT_SECTION = false;

	private static self $instance;

	private array $configByTypes = [];
	private bool $isLoaded = false;
	private ExternalTypeRegistry $externalTypeRegistry;
	private Converter $converterToCamelCase;

	private function __construct()
	{
		$this->externalTypeRegistry = ExternalTypeRegistry::getInstance();
		$this->converterToCamelCase = new Converter(Converter::TO_CAMEL | Converter::LC_FIRST);
	}

	public static function getInstance(): self
	{
		self::$instance ??= new self();

		return self::$instance;
	}

	public function getByExtendedType(string $type): RecentConfig
	{
		if (!$this->isLoaded)
		{
			$this->load();
		}

		return $this->configByTypes[$type] ?? new RecentConfig();
	}

	public function getRecentSectionsByChat(Chat $chat): array
	{
		return $this->getRecentSectionsByChatExtendedType($chat->getExtendedType(false));
	}

	public function getRecentSectionsByChatExtendedType(string $type): array
	{
		$config = $this->getByExtendedType($type);
		$recentSections = [];

		if ($config->useDefaultRecentSection)
		{
			$recentSections[] = 'default';
		}

		if ($config->hasOwnRecentSection)
		{
			$recentSections[] = $config->getOwnSectionName() ?? $this->converterToCamelCase->process($type);
		}

		return $recentSections;
	}

	private function load(): void
	{
		$this->isLoaded = true;
		$this->loadInternal();
		$this->loadExternal();
	}

	private function loadInternal(): void
	{
		$this->configByTypes[ExtendedType::Copilot->value] = new RecentConfig(true, true);
		$this->configByTypes[ExtendedType::Collab->value] = new RecentConfig(true, true);
		$this->configByTypes[ExtendedType::Lines->value] = new RecentConfig(false, true);
		$this->configByTypes[ExtendedType::Comment->value] = new RecentConfig(false, false);
		$this->configByTypes[ExtendedType::OpenChannel->value] = new RecentConfig(true, true);
		$this->configByTypes[ExtendedType::GeneralChannel->value]
			= (new RecentConfig(true, true))->setOwnSectionName('openChannel')
		;
	}

	private function loadExternal(): void
	{
		$configs = $this->externalTypeRegistry->getConfigs();

		foreach ($configs as $type => $config)
		{
			if (isset($this->configByTypes[$type]))
			{
				continue;
			}

			$this->configByTypes[$type] = new RecentConfig(
				self::EXTERNAL_CHAT_USE_DEFAULT_RECENT_SECTION,
				$config->hasOwnRecentSection
			);
		}
	}
}
