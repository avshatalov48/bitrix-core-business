<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

class BizprocWorkflowTimelineSlider extends CBitrixComponent
{
	public function executeComponent()
	{
		$this->arResult['timelineProps'] = [
			'workflowId' => $this->arParams['workflowId'],
			'taskId' => $this->arParams['taskId'],
		];

		$this->includeComponentTemplate();
	}
}