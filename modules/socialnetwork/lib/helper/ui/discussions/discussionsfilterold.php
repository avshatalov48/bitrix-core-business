<?php

namespace Bitrix\Socialnetwork\Helper\UI\Discussions;

class DiscussionsFilterOld extends DiscussionsFilter
{
	public function getParamsForPresets(?array $presetFiltersTop = [], ?array $presetFilters = []): array
	{
		$result["presetFiltersTop"] = $presetFiltersTop ?? [];
		$result["presetFilters"] = $presetFilters ?? [];
		$result["pageParamsToClear"] = ['set_follow_type'];
		$result["allItemTitle"] = false;

		$event = GetModuleEvents("socialnetwork", "OnBeforeSonetLogFilterFill");
		while ($arEvent = $event->Fetch())
		{
			ExecuteModuleEventEx(
				$arEvent,
				[
					&$result["pageParamsToClear"],
					&$result["presetFiltersTop"],
					&$result["presetFilters"],
					&$result["allItemTitle"]
				]
			);
		}

		return $result;
	}
}