<?php

namespace Bitrix\Socialnetwork\Integration\Mobile;

use Bitrix\Main\EventResult;
use Bitrix\Main\Localization\Loc;
use Bitrix\Socialnetwork\Helper\Feature;

class TariffPlanRestriction
{
	/**
	 * Handler for mobile event onTariffRestrictionsCollect
	 *
	 * @return EventResult
	 */
	public static function getTariffPlanRestrictions(): EventResult
	{
		return new EventResult(
			EventResult::SUCCESS,
			[
				'restrictions' => [
					Feature::PROJECTS_GROUPS => [
						'code' => Feature::PROJECTS_GROUPS,
						'title' => Loc::getMessage('SOCIALNETWORK_TARIFF_PLAN_RESTRICTION_SOCIALNETWORK_PROJECTS_GROUPS'),
						'isRestricted' => (
							!Feature::isFeatureEnabled(Feature::PROJECTS_GROUPS)
							&& !Feature::canTurnOnTrial(Feature::PROJECTS_GROUPS)
						),
						'isPromo' => Feature::isFeaturePromo(Feature::PROJECTS_GROUPS),
					],
				],
			],
			'socialnetwork',
		);
	}
}
