<?php

namespace Bitrix\Calendar\OpenEvents\Filter;

use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Filter\Theme;

class Filter
{
	public static function getId(): string
	{
		return 'CALENDAR_OPEN_EVENTS_FILTER_ID';
	}

	public function getOptions(): array
	{
		return [
			'FILTER_ID' => self::getId(),
			'FILTER' => $this->getFields(),
			'FILTER_PRESETS' => $this->getPresets(),
			'ENABLE_LABEL' => true,
			'ENABLE_LIVE_SEARCH' => true,
			'RESET_TO_DEFAULT_MODE' => true,
			'THEME' => Theme::LIGHT,
		];
	}

	public function getFields(): array
	{
		return [
			'DATE' => [
				'id' => 'DATE',
				'name' => Loc::getMessage('CALENDAR_OPEN_EVENTS_FILTER_DATE'),
				'type' => 'date',
				'default' => true,
			],
			'I_AM_ATTENDEE' => [
				'id' => 'I_AM_ATTENDEE',
				'name' => Loc::getMessage('CALENDAR_OPEN_EVENTS_FILTER_I_AM_ATTENDEE'),
				'type' => 'checkbox',
				'default' => true,
			],
			'CATEGORIES_IDS' => [
				'id' => 'CATEGORIES_IDS',
				'name' => Loc::getMessage('CALENDAR_OPEN_EVENTS_FILTER_CATEGORIES_IDS'),
				'type' => 'entity_selector',
				'params' => [
					'multiple' => 'Y',
					'dialogOptions' => [
						'height' => 330,
						'context' => 'filter',
						'entities' => [
							[
								'id' => 'event-category',
								'dynamicLoad' => true,
								'dynamicSearch' => true,
							],
						],
					],
				],
				'default' => true,
			],
			'CREATOR_ID' => [
				'id' => 'CREATOR_ID',
				'name' => Loc::getMessage('CALENDAR_OPEN_EVENTS_FILTER_CREATOR_ID'),
				'type' => 'entity_selector',
				'params' => [
					'multiple' => 'N',
					'dialogOptions' => [
						'height' => 330,
						'context' => 'filter',
						'entities' => [
							[
								'id' => 'user',
								'dynamicLoad' => true,
								'dynamicSearch' => true,
							],
						],
					],
				],
				'default' => true,
			],
		];
	}

	public function getPresets(): array
	{
		return [
			'calendar-open-events-i-am-attendee' => [
				'id' => 'calendar-open-events-i-am-attendee',
				'name' => Loc::getMessage('CALENDAR_OPEN_EVENTS_FILTER_PRESET_I_AM_ATTENDEE'),
				'default' => false,
				'fields' => [
					'I_AM_ATTENDEE' => 'Y',
				],
			],
			'calendar-open-events-i-am-creator' => [
				'id' => 'calendar-open-events-i-am-creator',
				'name' => Loc::getMessage('CALENDAR_OPEN_EVENTS_FILTER_PRESET_I_AM_CREATOR'),
				'default' => false,
				'fields' => [
					'CREATOR_ID' => CurrentUser::get()?->getId(),
					'CREATOR_ID_label' => CurrentUser::get()?->getFormattedName(),
				],
			],
			'calendar-open-events-popular' => [
				'id' => 'calendar-open-events-popular',
				'name' => Loc::getMessage('CALENDAR_OPEN_EVENTS_FILTER_PRESET_POPULAR'),
				'default' => false,
			],
		];
	}
}
