<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
/**
 * Class ReportVisualConstructorConfigFields
 */
class ReportVisualConstructorConfigFields extends CBitrixComponent
{

	public function executeComponent()
	{
		/** @var \Bitrix\Report\VisualConstructor\Fields\Base$configurationField */
		$configurationField = $this->arParams['CONFIGURATION_FIELD'];
		$events = array();
		$behaviours = array();
		foreach ($configurationField->getJsEvents() as $eventKey => $listeners)
		{
			foreach ($listeners as  $params)
			{
				$id = $params['behaviourOwner']->getId();
				$behaviours[] = array(
					'behaviorOwnerSelector' => '#' . $id,
					'eventName' => $configurationField->getId() . '_' . $eventKey,
					'handlerParams' => $params['handlerParams'],
				);
			}
		}


		foreach ($configurationField->getJsEventListeners() as $eventKey => $jsEventListeners)
		{
			foreach ($jsEventListeners as $params)
			{
				$id = $params['eventOwner']->getId();
				$events[] = array(
					'ownerFieldSelector' => '#' . $id,
					'eventName' => $id . '_' . $eventKey,
					'handlerParams' => $params['handlerParams'],
				);
			}
		}
		$this->arResult['CONFIGURATION_FIELD'] = $configurationField;
		$this->arResult['CONFIGURATION_FIELD_EVENTS'] = $events;
		$this->arResult['CONFIGURATION_FIELD_BEHAVIOURS'] = $behaviours;
		$this->includeComponentTemplate();
	}


}