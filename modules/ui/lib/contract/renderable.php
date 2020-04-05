<?php

namespace Bitrix\UI\Contract;

interface Renderable
{
	/**
	 * Returns content as string.
	 *
	 * @param bool $jsInit
	 *
	 * @return string
	 */
	public function render($jsInit = true);
}