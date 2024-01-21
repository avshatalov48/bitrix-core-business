<?php

namespace Bitrix\Socialnetwork\Helper\UI\Discussions;

class DiscussionsFilterSpaces extends DiscussionsFilter
{
	public function getParamsForPresets(): array
	{
		$presets[] = [
			"ID" => "work",
			"SORT" => 100,
			"NAME" => "#WORK#",
			"FILTER" =>[
				"EVENT_ID" => [
					"tasks",
					"timeman_entry",
					"report"
				]
			]
		];

		$presets[] = [
			"ID" => "favorites",
			"SORT" => 200,
			"NAME" => "#FAVORITES#",
			"FILTER" => [
				"FAVORITES_USER_ID" => "Y"
			]
		];

		$presets[] = [
			"ID" => "my",
			"SORT" => 300,
			"NAME" => "#MY#",
			"FILTER" => [
				"CREATED_BY_ID" => "#CURRENT_USER_ID#"
			]
		];

		if (IsModuleInstalled("blog"))
		{
			$presets[] = [
				"ID" => "important",
				"SORT" => 350,
				"NAME" => "#important#",
				"FILTER" => [
					"EXACT_EVENT_ID" => "blog_post_important"
				]
			];
		}

		if (
			IsModuleInstalled("lists")
			&& IsModuleInstalled("bizproc")
			&& IsModuleInstalled("intranet")
		)
		{
			$presets[] = [
				"ID" => "bizproc",
				"SORT" => 400,
				"NAME" => "#BIZPROC#",
				"FILTER" => [
					"EXACT_EVENT_ID" => "lists_new_element"
				]
			];
		}

		if (IsModuleInstalled("intranet"))
		{
			$presets[] = [
				"ID" => "extranet",
				"SORT" => 500,
				"NAME" => "#EXTRANET#",
				"FILTER" => [
					"SITE_ID" => "#EXTRANET_SITE_ID#",
					"!EXACT_EVENT_ID" => [
						"lists_new_element",
						"tasks",
						"timeman_entry",
						"report",
						"crm_activity_add"
					]
				]
			];
		}

		return $presets;
	}
}