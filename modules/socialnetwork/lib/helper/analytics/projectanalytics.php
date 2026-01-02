<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Helper\Analytics;

use Bitrix\Main\Analytics\AnalyticsEvent;
use Bitrix\Socialnetwork\Helper\Feature;
use Bitrix\Tasks\Util\Restriction\Bitrix24Restriction\Limit\ProjectLimit;
use Bitrix\Tasks\Util\Restriction\Bitrix24Restriction\Limit\ScrumLimit;

class ProjectAnalytics extends Analytics
{
	public const TOOL_TASKS = 'tasks';
	public const CATEGORY_SCRUM = 'scrum';
	public const CATEGORY_PROJECT = 'project';
	public const EVENT_PROJECT_VIEW = 'projects_view';
	public const EVENT_SCRUM_VIEW = 'scrum_view';
	public const EVENT_SCRUM_CREATE_START = 'scrum_create_start';
	public const EVENT_SCRUM_CREATE_FINISH = 'scrum_create_finish';
	public const EVENT_PROJECT_CREATE_START = 'project_create_start';
	public const EVENT_PROJECT_CREATE_FINISH = 'project_create_finish';
	public const SECTION_SCRUM = 'scrum';
	public const SECTION_PROJECT = 'project';
	public const SUBSECTION_PROJECT_GRID = 'project_grid';
	public const SUBSECTION_SCRUM_GRID = 'scrum_grid';
	public const ELEMENT_SECTION_BUTTON = 'section_button';
	public const ELEMENT_CREATE_BUTTON = 'create_button';

	public function onProjectCreateFormOpened(
		string $eventName = self::EVENT_PROJECT_CREATE_START,
		string $category = self::CATEGORY_PROJECT,
		string $section = self::SECTION_PROJECT,
		string $subSection = self::SUBSECTION_PROJECT_GRID,
		string $element = self::ELEMENT_CREATE_BUTTON,
		string $tool = self::TOOL_TASKS,
		array $params = [],
	): void
	{
		$event = new AnalyticsEvent(
			$eventName,
			$tool,
			$category,
		);

		$defaultParams = [ 'p1' => $this->getTrialParamByCategory($category) ];
		$params = array_merge($params, $defaultParams);

		$this->sendAnalytics(
			analyticsEvent: $event,
			section: $section,
			element: $element,
			subSection: $subSection,
			params: $params,
		);
	}

	public function onProjectCreated(
		string $privacyType,
		string $eventName = self::EVENT_PROJECT_CREATE_FINISH,
		string $category = self::CATEGORY_PROJECT,
		string $section = self::SECTION_PROJECT,
		string $subSection = self::SUBSECTION_PROJECT_GRID,
		string $element = self::ELEMENT_CREATE_BUTTON,
		string $tool = self::TOOL_TASKS,
		array $params = [],
	): void
	{
		$event = new AnalyticsEvent(
			$eventName,
			$tool,
			$category,
		);

		$defaultParams = [ 'p1' => 'privacyType_' . $privacyType ];
		$params = array_merge($params, $defaultParams);

		$this->sendAnalytics(
			analyticsEvent: $event,
			section: $section,
			element: $element,
			subSection: $subSection,
			params: $params,
		);
	}

	public function onProjectListOpened(
		string $eventName = self::EVENT_PROJECT_VIEW,
		string $category = self::CATEGORY_PROJECT,
		string $section = self::SECTION_PROJECT,
		string $subSection = self::SUBSECTION_PROJECT_GRID,
		string $element = self::ELEMENT_SECTION_BUTTON,
		string $tool = self::TOOL_TASKS,
		array $params = [],
	): void
	{
		$event = new AnalyticsEvent(
			$eventName,
			$tool,
			$category,
		);

		$defaultParams = [ 'p1' => $this->getTrialParamByCategory($category) ];
		$params = array_merge($params, $defaultParams);

		$this->sendAnalytics(
			analyticsEvent: $event,
			section: $section,
			element: $element,
			subSection: $subSection,
			params: $params,
		);
	}

	private function getTrialParamByCategory(string $category): string
	{
		$isTrialEnabled = match ($category) {
			self::CATEGORY_SCRUM => Feature::isFeatureEnabledByTrial(Feature::SCRUM_CREATE),
			self::CATEGORY_PROJECT => Feature::isFeatureEnabledByTrial(Feature::PROJECTS_GROUPS),
			default => false,
		};

		return 'isDemo_' . ($isTrialEnabled ? 'Y' : 'N');
	}
}