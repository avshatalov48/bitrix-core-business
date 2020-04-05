<?php

namespace Bitrix\Main\Web\WebPacker\Output;

use Bitrix\Main\Web\WebPacker;

/**
 * Class Base
 *
 * @package Bitrix\Main\Web\WebPacker
 */
class Base
{
	/**
	 * Output.
	 *
	 * @param WebPacker\Builder $builder Builder.
	 * @return Result
	 */
	public function output(WebPacker\Builder $builder)
	{
		return (new Result())->setContent($builder->stringify());
	}
}