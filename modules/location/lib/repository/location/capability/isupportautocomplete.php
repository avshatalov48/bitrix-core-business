<?php

namespace Bitrix\Location\Repository\Location\Capability;

interface ISupportAutocomplete
{
	/**
	 * @param array $params
	 * @return array
	 */
	public function autocomplete(array $params): array;
}
