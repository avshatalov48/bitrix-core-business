<?php

namespace Bitrix\Sale;

use Bitrix\Main\EventResult;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Services\Company\Manager;

class UserConsent
{
	const PROVIDER_CODE = 'sale/order';
	const DATA_PROVIDER_CODE = 'sale/company';

	/**
	 * Event `main/OnUserConsentProviderList` handler.
	 *
	 * @return EventResult
	 */
	public static function onProviderList()
	{
		$parameters = array(
			array(
				'CODE' => self::PROVIDER_CODE,
				'NAME' => Loc::getMessage('SALE_USER_CONSENT_PROVIDER_NAME'),
				'DATA' => function ($id = null)
				{
					return array(
						'NAME' => Loc::getMessage('SALE_USER_CONSENT_PROVIDER_ITEM_NAME', array('%id%' => $id)),
						'URL' => str_replace('%id%', $id, '/bitrix/admin/sale_order_view.php?ID=%id%')
					);
				}
			)
		);

		return new EventResult(EventResult::SUCCESS, $parameters, 'sale');
	}

	/**
	 * Event `main/OnUserConsentDataProviderList` handler.
	 *
	 * @return EventResult
	 */
	public static function onDataProviderList()
	{
		$parameters = array(
			array(
				'CODE' => self::DATA_PROVIDER_CODE,
				'NAME' => Loc::getMessage('SALE_USER_CONSENT_DATA_PROVIDER_NAME'),
				'EDIT_URL' => '/bitrix/admin/sale_company.php',
				'DATA' => function ()
				{
					$data = array();
					$companyNames = array();
					$companyAddresses = array();

					$dbRes = Manager::getList(array(
						'select' => array('NAME', 'ADDRESS'),
						'filter' => array('ACTIVE' => 'Y'),
						'order' => array('SORT' => 'ASC', 'ID' => 'ASC')
					));
					while ($company = $dbRes->fetch())
					{
						$companyNames[] = $company['NAME'];
						$companyAddresses[] = $company['ADDRESS'];
					}

					if (!empty($companyNames))
					{
						$data = array(
							'COMPANY_NAME' => implode('; ', $companyNames),
							'COMPANY_ADDRESS' => implode('; ', $companyAddresses)
						);
					}

					return $data;
				}
			)
		);

		return new EventResult(EventResult::SUCCESS, $parameters, 'sale');
	}
}