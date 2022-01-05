<?php

namespace Bitrix\Seo\Retargeting;

interface PagingInterface
{
	/**
	 * Prepare params to next request
	 * @param array $params
	 *
	 * @return array|null
	 */
	public function prepareRequestParams(array $params) : ?array;

}