<?php

namespace Bitrix\Calendar\OpenEvents\Updater;

use Bitrix\Calendar\Core\Common;
use Bitrix\Calendar\Core\Event\Tools\Dictionary;
use Bitrix\Calendar\OpenEvents\Service\DefaultCategoryService;
use Bitrix\Main\Config;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

class Agent
{
	private const RESTRICT_OPTION_NAME = 'restrict_default_open_event_category_creating';

	private static bool $processing = false;

	// return empty string '' to delete agent
	public static function execute(): string
	{
		if (self::$processing)
		{
			return '';
		}

		self::$processing = true;

		(new self())->run();

		self::$processing = false;

		return '';
	}

	public function run(): void
	{
		if (!Loader::includeModule('calendar'))
		{
			return;
		}

		$this->createCalendarType();
		$this->createDefaultSection();
		if ($this->shouldCreateDefaultCategory())
		{
			$this->createDefaultCategory();
		}
	}

	protected function createCalendarType(): void
	{
		\CCalendarType::Edit([
			'NEW' => true,
			'arFields' => [
				'XML_ID' => Dictionary::CALENDAR_TYPE['open_event'],
				'NAME' => Loc::getMessage('CALENDAR_OPEN_EVENTS_DEFAULT_SECTION_NAME'),
				'DESCRIPTION' => '',
			],
		]);
	}

	protected function createDefaultSection(): void
	{
		if ($this->hasOpenEventSection())
		{
			return;
		}

		\CCalendarSect::Edit([
			'arFields' => [
				'NAME' => Loc::getMessage('CALENDAR_OPEN_EVENTS_DEFAULT_SECTION_NAME'),
				'DESCRIPTION' => Loc::getMessage('CALENDAR_OPEN_EVENTS_DEFAULT_SECTION_NAME'),
				'CAL_TYPE' => Dictionary::CALENDAR_TYPE['open_event'],
				'COLOR' => '#442056',
				'TEXT_COLOR' => '#FFFFFF',
				'OWNER_ID' => Common::SYSTEM_USER_ID,
				'CREATED_BY' => Common::SYSTEM_USER_ID,
			],
		]);
	}

	private function hasOpenEventSection(): bool
	{
		$openEventSections = \CCalendarSect::GetList([
			'arSelect' => ['ID', 'CAL_TYPE'],
			'arFilter' => [
				'CAL_TYPE' => Dictionary::CALENDAR_TYPE['open_event'],
			],
			'limit' => 1,
			'checkPermissions' => false,
			'getPermissions' => false,
		]);

		return !empty($openEventSections);
	}

	protected function createDefaultCategory(): void
	{
		DefaultCategoryService::getInstance()->createDefaultCategory();
	}

	private function shouldCreateDefaultCategory(): bool
	{
		return Config\Option::get(
			moduleId: Common::CALENDAR_MODULE_ID,
			name: self::RESTRICT_OPTION_NAME,
			default: 'N',
		) !== 'Y';
	}
}
