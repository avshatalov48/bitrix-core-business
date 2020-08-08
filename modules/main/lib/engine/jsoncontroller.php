<?php
namespace Bitrix\Main\Engine;

/**
 * Class JsonController
 * @package Bitrix\Main\Engine
 */
class JsonController extends Controller
{
	/**
	 * Returns default pre-filters for action.
	 * @return array
	 */
	protected function getDefaultPreFilters()
	{
		return array_merge(
			[
				new ActionFilter\ContentType([ActionFilter\ContentType::JSON]),
			],
			parent::getDefaultPreFilters()
		);
	}
}