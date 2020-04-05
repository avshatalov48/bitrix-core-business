<?php
namespace Bitrix\Report\VisualConstructor\Helper;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;

/**
 * Class Analytic
 */
class Analytic
{
	/**
	 * @TODO maybe need to add some logic of access for different analytic pages
	 *
	 * @return bool
	 */
	public static function isEnable()
	{
		if (Loader::includeModule('crm'))
		{
			return \CCrmPerms::IsAccessEnabled();
		}
		else
		{
			return false;
		}
	}



	public static function isEnabledCrmAnalytics()
	{
		if (Loader::includeModule('crm'))
		{
			return Option::get("report", '~analytics_enabled', 'N') === 'Y' && \CCrmPerms::IsAccessEnabled();
		}
		else
		{
			return false;
		}
	}
}