<?php


namespace Bitrix\Sale\Helpers\Admin\Blocks;


class FactoryMode
{
	/**
	 * @param $type
	 * @return \Bitrix\Sale\Exchange\Integration\Admin\Blocks\Factory|Factory
	 * @throws \Bitrix\Main\NotSupportedException
	 */
	static public function create($type)
	{
		if($type == ModeType::DEFAULT_TYPE)
		{
			return new Factory();
		}
		elseif ($type == ModeType::APP_LAYOUT_TYPE)
		{
			return new \Bitrix\Sale\Exchange\Integration\Admin\Blocks\Factory();
		}
		else
		{
			throw new \Bitrix\Main\NotSupportedException("Mode type: '".$type."' is not supported in current context");
		}
	}
}