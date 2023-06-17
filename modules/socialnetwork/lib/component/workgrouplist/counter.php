<?php

namespace Bitrix\Socialnetwork\Component\WorkgroupList;

use Bitrix\Main\Loader;
use Bitrix\Main\Grid;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Web\Json;
use Bitrix\Socialnetwork\Component\WorkgroupList;
use Bitrix\Socialnetwork\Helper;
use Bitrix\Tasks\Internals\Counter\Template\ProjectCounter;
use Bitrix\Tasks\Internals\Counter\Template\ScrumCounter;
use Bitrix\Tasks\Internals\Counter as TasksCounter;

class Counter
{
	public static function fillCounters(array $params = []): array
	{
		$result = [];

		if (
			!isset($params['groupIdList'], $params['counterData'])
			|| !is_array($params['groupIdList'])
			|| !is_array($params['counterData'])
		)
		{
			return $result;
		}

		$counterData = $params['counterData'];

		$groupIdList = Util::filterNumericIdList($params['groupIdList']);
		if (empty($groupIdList))
		{
			return $result;
		}

		$scrumIdList = [];
		if (isset($params['scrumIdList']))
		{
			$scrumIdList = Util::filterNumericIdList($params['scrumIdList']);
		}

		$currentUserId = static::getCurrentUserId();
		if ($currentUserId <= 0)
		{
			return $result;
		}

		$mode = ($params['mode'] ?? WorkgroupList::MODE_COMMON);
		$groupUrlTemplate = ($params['groupUrl'] ?? Helper\Path::get('group_path_template'));

		if (in_array($mode, WorkgroupList::getTasksModeList(), true))
		{
			$result = static::fillTasksCounters([
				'counterData' => $counterData,
				'mode' => $mode,
				'groupUrl' => $groupUrlTemplate,
				'groupIdList' => $groupIdList,
				'scrumIdList' => $scrumIdList,
			]);
		}
		else
		{
			$result = static::fillCommonCounters([
				'counterData' => $counterData,
				'groupUrl' => Helper\Path::get('group_livefeed_path_template'),
				'groupIdList' => $groupIdList,
				'scrumIdList' => $scrumIdList,
				'livefeedCounterSliderOptions' => $params['livefeedCounterSliderOptions'],
			]);
		}

		return $result;
	}

	public static function getCounterData(array $params = []): array
	{
		static $tasksCounterCache = [];

		$result = [];

		$mode = ($params['mode'] ?? WorkgroupList::MODE_COMMON);

		$groupIdList = $params['groupIdList'] ?? [];
		if (empty($groupIdList))
		{
			return $result;
		}

		$scrumIdList = $params['scrumIdList'] ?? [];

		$currentUserId = static::getCurrentUserId();
		if ($currentUserId <= 0)
		{
			return $result;
		}

		$tasksModuleInstalled = Loader::includeModule('tasks');

		if (in_array($mode, WorkgroupList::getTasksModeList(), true))
		{
			$projectCounter = (
				$mode === WorkgroupList::MODE_TASKS_SCRUM
					? new ScrumCounter($currentUserId)
					: new ProjectCounter($currentUserId)
				);

			foreach ($groupIdList as $groupId)
			{
				$counterData = $projectCounter->getRowCounter($groupId);

				$result[$groupId] = [
					'all' => [
						'VALUE' => $counterData['VALUE'],
						'COLOR' => $counterData['COLOR'],
					],
				];
			}
		}
		else
		{
			$counters = \CUserCounter::getValues($currentUserId);
			if (!isset($tasksCounterCache[$currentUserId]))
			{
				$tasksCounterCache[$currentUserId] = TasksCounter::getInstance($currentUserId);
			}

			$tasksCounter = $tasksCounterCache[$currentUserId];

			foreach ($groupIdList as $groupId)
			{
				$counterKey = \CUserCounter::LIVEFEED_CODE . 'SG' . $groupId;

				$result[$groupId] = [
					'livefeed' => [
						'VALUE' => (int)($counters[$counterKey] ?? 0),
					],
				];

				if ($tasksModuleInstalled)
				{
					$result[$groupId]['tasks_expired'] = [
						'VALUE' => (
							in_array($groupId, $scrumIdList, true)
								? 0
								: $tasksCounter->get(TasksCounter\CounterDictionary::COUNTER_EXPIRED, $groupId)
						)
					];

					$result[$groupId]['tasks_new_comments'] = [
						'VALUE' => $tasksCounter->get(TasksCounter\CounterDictionary::COUNTER_NEW_COMMENTS, $groupId),
					];
				}
			}
		}

		return $result;
	}

	protected static function fillTasksCounters(array $params = []): array
	{
		$result = [];

		$groupIdList = $params['groupIdList'] ?? [];
		if (empty($groupIdList))
		{
			return $result;
		}

		$currentUserId = static::getCurrentUserId();
		if ($currentUserId <= 0)
		{
			return $result;
		}

		$counterData = $params['counterData'] ?? [];

		$scrumIdList = $params['scrumIdList'] ?? [];

		$groupUrlTemplate = ($params['groupUrl'] ?? Helper\Path::get('group_path_template'));

		$colorMap = [
			Helper\UI\Grid\CounterStyle::STYLE_GRAY => Grid\Counter\Color::GRAY,
			Helper\UI\Grid\CounterStyle::STYLE_GREEN => Grid\Counter\Color::SUCCESS,
			Helper\UI\Grid\CounterStyle::STYLE_RED => Grid\Counter\Color::DANGER,
		];

		$sliderOptionsData = [
			'contentClassName' => 'bitrix24-group-slider-content',
			'loader' => 'intranet:slider-tasklist',
			'cacheable' => false,
			'customLeftBoundary' => 0,
			'newWindowLabel' => true,
			'copyLinkLabel' => true,
		];

		foreach ($groupIdList as $groupId)
		{
			$groupCounterData = ($counterData[$groupId] ?? []);

			$groupUrl = str_replace([ '#id#', '#ID#', '#GROUP_ID#', '#group_id#' ], $groupId, $groupUrlTemplate);

			if (in_array($groupId, $scrumIdList, true))
			{
				$sliderOptionsData['loader'] = 'intranet:scrum';
			}

			$sliderOptions = Json::encode($sliderOptionsData);

			$result[$groupId] = [
				'ACTIVITY_DATE' => [
					'type' => Grid\Counter\Type::LEFT_ALIGNED,
					'color' => $colorMap[$groupCounterData['all']['COLOR']],
					'value' => $groupCounterData['all']['VALUE'],
					'events' => [
						'click' => "BX.SidePanel.Instance.open.bind(BX.SidePanel.Instance, '{$groupUrl}', {$sliderOptions})",
					],
					'class' => 'sonet-ui-grid-counter',
				],
			];
		}

		return $result;
	}

	protected static function fillCommonCounters(array $params = []): array
	{
		$result = [];

		$groupIdList = $params['groupIdList'] ?? [];
		if (empty($groupIdList))
		{
			return $result;
		}

		$counterData = $params['counterData'] ?? [];

		$scrumIdList = $params['scrumIdList'] ?? [];

		$groupUrlTemplate = ($params['groupUrl'] ?? Helper\Path::get('group_livefeed_path_template'));
		$sliderOptionsData = ($params['sliderOptions'] ?? static::getLivefeedCounterSliderOptions());

		$tasksModuleInstalled = ModuleManager::isModuleInstalled('tasks');

		foreach ($groupIdList as $groupId)
		{
			$groupCounterData = ($counterData[$groupId] ?? []);

			$livefeedCounterValue = (int)($groupCounterData['livefeed']['VALUE'] ?? 0);
			$tasksCounterValue = (
				$tasksModuleInstalled
					? (int)($groupCounterData['tasks_expired']['VALUE'] ?? 0)
						+ (int)($groupCounterData['tasks_new_comments']['VALUE'] ?? 0)
					: 0
			);

			$groupUrl = str_replace([ '#id#', '#ID#', '#GROUP_ID#', '#group_id#' ], $groupId, $groupUrlTemplate);

			if (in_array($groupId, $scrumIdList, true))
			{
				$sliderOptionsData['loader'] = 'intranet:scrum';
			}

			$sliderOptions = Json::encode($sliderOptionsData);

			$result[$groupId] = [
				static::getLivefeedCounterColumnId() => [
					'type' => Grid\Counter\Type::RIGHT,
					'color' => Grid\Counter\Color::DANGER,
					'value' => $livefeedCounterValue + $tasksCounterValue,
					'events' => [
						'click' => "BX.SidePanel.Instance.open.bind(BX.SidePanel.Instance, '{$groupUrl}', {$sliderOptions})",
					],
					'class' => 'sonet-ui-grid-counter',
				],
			];
		}

		return $result;
	}

	protected static function getCurrentUserId(): int
	{
		static $result = null;
		if ($result === null)
		{
			global $USER;
			$result = (int)$USER->getId();
		}

		return $result;
	}

	public static function getLivefeedCounterColumnId(): string
	{
		return 'NAME';
	}

	public static function getLivefeedCounterSliderOptions(): array
	{
		return [
//			'contentClassName' => 'bitrix24-group-slider-content',
			'cacheable' => false,
//			'customLeftBoundary' => 0,
//			'newWindowLabel' => true,
//			'copyLinkLabel' => true,
		];
	}
}
