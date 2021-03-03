<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main;
use Bitrix\Rest;

class AppPlacementAjax extends Main\Engine\Controller
{
	public function getComponentAction($placementId, $placementOptions = [])
	{
		$component = new Main\Engine\Response\Component(
			'bitrix:app.placement',
			'menu',
			[
				'PLACEMENT' => $placementId,
				'PLACEMENT_OPTIONS' => $placementOptions,
			],
			[],
			[]
		);
		return $component;
	}
}