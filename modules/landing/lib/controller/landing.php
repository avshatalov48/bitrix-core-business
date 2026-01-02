<?php

namespace Bitrix\Landing\Controller;

use Bitrix\Landing\Landing as LandingCore;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine;

class Landing extends Controller
{
	public function getDefaultPreFilters(): array
	{
		return [
			new Engine\ActionFilter\Authentication(),
		];
	}

	public function configureActions(): array
	{
		return [
			'getByIdAction' => [
				'+prefilters' => [
					new ActionFilter\Extranet(),
				],
			]
		];
	}

	/**
	 * Returns landing's data.
	 * @param int $landingId Landing id.
	 * @return array|null
	 */
	public function getByIdAction(int $landingId): ?array
	{
		$res = LandingCore::getList([
			'select' => [
				'*',
			],
			'filter' => [
				'ID' => $landingId,
			],
		]);
		if ($row = $res->fetch())
		{
			$row['ADDITIONAL_FIELDS'] = LandingCore::getAdditionalFieldsAsArray($landingId);

			return $row;
		}

		return null;
	}

	public function isPhoneRegionCodeTourAlreadySeenAction(): bool
	{
		return \CUserOptions::GetOption('ui-tour', 'landing_phone_aha_shown', null) !== null;
	}
}