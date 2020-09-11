<?php
namespace Bitrix\Landing\Controller;

use \Bitrix\Main\Engine\Controller;

class Cookies extends Controller
{
	public function getDefaultPreFilters()
	{
		return [];
	}

	/**
	 * Returns site cookie agreements.
	 * @param int $siteId Site id.
	 * @return array
	 */
	public function getAgreementsAction(int $siteId): array
	{
		$mainAgreement = \Bitrix\Landing\Site\Cookies::getMainAgreement();
		$agreements = \Bitrix\Landing\Site\Cookies::getAgreements($siteId, true);

		$data = [
			'main' => $mainAgreement,
			'analytic' => array_filter($agreements, function($item)
			{
				return $item['ACTIVE'] == 'Y' && $item['TYPE'] == 'analytic';
			}),
			'technical' => array_filter($agreements, function($item)
			{
				return $item['ACTIVE'] == 'Y' && $item['TYPE'] == 'technical';
			}),
			'other' => array_filter($agreements, function($item)
			{
				return $item['ACTIVE'] == 'Y' && $item['TYPE'] == 'other';
			})
		];

		foreach ($data as $key => $val)
		{
			if (!$val)
			{
				unset($data[$key]);
			}
		}

		return $data;
	}

	/**
	 * Accepts agreements from user.
	 * @param int $siteId Site id.
	 * @param array $accepted Agreements codes which user has accepted.
	 * @return void
	 */
	public function acceptAgreementsAction(int $siteId, array $accepted = []): void
	{
		\Bitrix\Landing\Site\Cookies::acceptAgreement($siteId, $accepted);
	}
}