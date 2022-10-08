<?php

namespace Bitrix\Calendar\Core\Base;

abstract class IndexCollection extends Collection
{
	/**
	 * @param $item
	 * @param $index
	 * @return $this
	 * @throws BaseException
	 */
	public function add($item, $index = null): IndexCollection
	{
		if (!$index)
		{
			throw new BaseException('you should pass index');
		}

		$this->collection[$index] = $item;

		return $this;
	}
}