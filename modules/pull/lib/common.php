<?php
namespace Bitrix\Pull;

class Common
{
	public static function jsonEncode($params)
	{
		$option = null;
		if (version_compare(phpversion(), '5.4') >= 0)
		{
			$option = JSON_UNESCAPED_UNICODE;
		}

		array_walk_recursive($params, function(&$item, $key){
			if ($item instanceof \Bitrix\Main\Type\DateTime)
			{
				$item = date('c', $item->getTimestamp());
			}
		});

		return \Bitrix\Main\Web\Json::encode($params, $option);
	}
}
