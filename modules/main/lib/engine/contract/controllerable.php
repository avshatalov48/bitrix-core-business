<?php

namespace Bitrix\Main\Engine\Contract;

interface Controllerable
{
	const METHOD_ACTION_SUFFIX = 'Action';

	/**
	 * @return array
	 */
	public function configureActions();
}
