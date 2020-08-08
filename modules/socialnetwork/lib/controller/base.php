<?
namespace Bitrix\Socialnetwork\Controller;

use Bitrix\Main\Loader;

class Base extends \Bitrix\Main\Engine\Controller
{
	protected function getDefaultPreFilters()
	{
		$preFilters = parent::getDefaultPreFilters();

		if (Loader::includeModule('intranet'))
		{
			$preFilters[] =  new \Bitrix\Intranet\ActionFilter\UserType([
				'employee',
				'extranet',
				'email',
				'replica'
			]);
		}

		return $preFilters;
	}
}

