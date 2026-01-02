<?php

namespace Bitrix\Seo\Analytics\Services\Helpers\Yandex\Dto;

use Bitrix\Main\Type\Dictionary;

final class AdCollection extends Dictionary
{
	/**
	 * @param $name
	 * @param Ad | null $value
	 *
	 * @return void
	 */
	public function set($name, $value = null)
	{
		if ($value instanceof Ad)
		{
			parent::set($name, $value);
		}
	}
}
