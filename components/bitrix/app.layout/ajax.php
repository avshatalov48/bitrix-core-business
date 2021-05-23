<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main;
use Bitrix\Rest;

class AppLayoutAjax extends Main\Engine\Controller
{
	public function getComponentAction($placementId, $placementOptions = [])
	{
		if ($placement = Rest\PlacementTable::getById($placementId)->fetchObject())
		{
			$component = new Main\Engine\Response\Component(
				'bitrix:app.layout',
				'',
				[
					'ID' => $placement->getAppId(),
					'PLACEMENT' => $placement->getPlacement(),
					'PLACEMENT_ID' => $placementId,
					'PLACEMENT_OPTIONS' => $placementOptions,
					'PARAM' => array(
						'FRAME_WIDTH' => '0',
						'FRAME_HEIGHT' => '0'
					),
				],
				[],
				[
					"APP_SID"
				]
			);
			return $component;
		}
		$this->errorCollection->add([new Main\Error("Placement was not found.")]);
		return [];
	}
}