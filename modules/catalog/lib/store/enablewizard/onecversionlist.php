<?php

namespace Bitrix\Catalog\Store\EnableWizard;

use Bitrix\Main\Localization\Loc;

class OnecVersionList
{
	private const OUR_COMPANY_MANAGEMENT = 'OUR_COMPANY_MANAGEMENT';
	private const ENTERPRISE_ACCOUNTING_30 = 'ENTERPRISE_ACCOUNTING_30';
	private const TRADE_MANAGEMENT_11 = 'TRADE_MANAGEMENT_11';
	private const ERP_ENTERPRISE_MANAGEMENT = 'ERP_ENTERPRISE_MANAGEMENT';
	private const COMPLEX_AUTOMATION = 'COMPLEX_AUTOMATION';
	private const OTHER = 'OTHER';

	public static function getList(): array
	{
		return [
			self::OUR_COMPANY_MANAGEMENT => Loc::getMessage('ONEC_VERSION_LIST_OUR_COMPANY_MANAGEMENT'),
			self::ENTERPRISE_ACCOUNTING_30 => Loc::getMessage('ONEC_VERSION_LIST_ENTERPRISE_ACCOUNTING_30'),
			self::TRADE_MANAGEMENT_11 => Loc::getMessage('ONEC_VERSION_LIST_TRADE_MANAGEMENT_11'),
			self::ERP_ENTERPRISE_MANAGEMENT => Loc::getMessage('ONEC_VERSION_LIST_ERP_ENTERPRISE_MANAGEMENT'),
			self::COMPLEX_AUTOMATION => Loc::getMessage('ONEC_VERSION_LIST_COMPLEX_AUTOMATION'),
			self::OTHER => Loc::getMessage('ONEC_VERSION_LIST_OTHER'),
		];
	}
}
