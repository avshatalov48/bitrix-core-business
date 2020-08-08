<?php
namespace Bitrix\Sale\Exchange\Integration\Admin;

class Factory
{

	static public function create($type)
	{
		if($type == ModeType::DEFAULT_TYPE)
		{
			return new DefaultLink();
		}
		elseif ($type == ModeType::APP_LAYOUT_TYPE)
		{
			return new AppLayoutLink();
		}
		else
		{
			throw new \Bitrix\Main\NotSupportedException("Mode type: '".$type."' is not supported in current context");
		}
	}
}