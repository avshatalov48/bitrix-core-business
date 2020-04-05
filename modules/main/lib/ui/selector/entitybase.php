<?

namespace Bitrix\Main\UI\Selector;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\FinderDestTable;
use Bitrix\Main\Loader;

abstract class EntityBase
{
	public function getData()
	{
		return array(
			'ITEMS' => array(),
			'ITEMS_LAST' => array(),
			'ADDITIONAL_INFO' => array()
		);
	}

	public function search()
	{
		return array(
			'ITEMS' => array(),
			'ADDITIONAL_INFO' => array()
		);
	}

	public function getTabList()
	{
		return array();
	}

	public function loadAll()
	{
		return array();
	}

	public function getItemName($itemCode = '')
	{
		return '';
	}
}