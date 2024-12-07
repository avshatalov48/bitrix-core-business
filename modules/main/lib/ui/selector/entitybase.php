<?

namespace Bitrix\Main\UI\Selector;

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