<?

namespace Bitrix\UI\Controller;

use Bitrix\Main\Engine;

class InfoHelper extends Engine\Controller
{
	public function getInitParamsAction()
	{
		return \Bitrix\UI\InfoHelper::getInitParams();
	}
}
