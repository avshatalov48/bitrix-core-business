<?php

namespace Bitrix\Sale\Cashbox\Restrictions;

use Bitrix\Sale\Services\Base;

class Manager extends Base\RestrictionManager
{
	protected static $classNames = null;

	/**
	 * @return string
	 */
	public static function getEventName()
	{
		return 'onSaleCashboxRestrictionsClassNamesBuildList';
	}

	/**
	 * @return array
	 */
	public static function getBuildInRestrictions()
	{
		return array(
			'\Bitrix\Sale\Cashbox\Restrictions\Company' => 'lib/cashbox/restrictions/company.php',
			'\Bitrix\Sale\Cashbox\Restrictions\PaySystem' => 'lib/cashbox/restrictions/paysystem.php',
		);
	}

	/**
	 * @return int
	 */
	protected static function getServiceType()
	{
		return parent::SERVICE_TYPE_CASHBOX;
	}
}