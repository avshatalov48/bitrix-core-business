<?php

namespace Bitrix\Bizproc\Integration\UI\EntitySelector;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Query\Filter\ConditionTree;
use Bitrix\UI\EntitySelector\Dialog;
use Bitrix\UI\EntitySelector\Tab;

class AutomationTemplateProvider extends TemplateProvider
{
	protected const ENTITY_ID = 'bizproc-automation-template';
	protected const TAB_ID = 'automation-templates';

	public function __construct(array $options = [])
	{
		parent::__construct($options);
		$this->options = [];
	}

	protected function addTemplatesTab(Dialog $dialog): void
	{
		$icon =
			'data:image/svg+xml,%3Csvg width=%2224%22 height=%2224%22 viewBox=%220 0 24 24%22 fill=%22none%22'
			. ' xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cpath'
			. ' d=%22M16.9255 8.09465L17.7877 5.19898C17.8629 4.94722 17.7995 4.67404 17.6215 4.48235C17.4435'
			. ' 4.29066 17.1779 4.20958 16.9248 4.26966C16.6717 4.32973 16.4695 4.52183 16.3944 4.77359L15.6559'
			. ' 7.25933C13.36 6.04719 10.6225 6.05007 8.32912 7.26705L7.58698 4.77359C7.47085 4.3844 7.0648 4.16413'
			. ' 6.68004 4.28159C6.29529 4.39906 6.07752 4.80979 6.19365 5.19898L7.05948 8.10606C5.11513 9.67809 3.98597'
			. ' 12.0615 3.99271 14.5793C3.99271 19.1232 7.57607 18.0082 11.9998 18.0082C16.4235 18.0082 20.0069'
			. ' 19.1232 20.0069 14.5793C20.0147 12.0547 18.8794 9.6655 16.9255 8.09465ZM11.9925 13.9615C8.83473'
			. ' 13.9615 6.27363 14.2927 6.27363 12.9389C6.27363 11.5851 8.83473 10.4859 11.9925 10.4859C15.1502'
			. ' 10.4859 17.7149 11.584 17.7149 12.9389C17.7149 14.2938 15.1538 13.9615 11.9925 13.9615ZM9.37673'
			. ' 11.4998C8.9259 11.5831 8.61713 12.0071 8.67215 12.4673C8.72717 12.9276 9.12694 13.2649 9.58441'
			. ' 13.2371C10.0419 13.2093 10.3988 12.826 10.399 12.3624C10.3479 11.8409 9.89305 11.4571 9.37673'
			. ' 11.4998ZM13.9023 12.4699C13.9583 12.93 14.3586 13.2666 14.8161 13.2383C15.2736 13.2099 15.6303'
			. ' 12.8263 15.6303 12.3627C15.5792 11.8397 15.1221 11.4553 14.6044 11.5001C14.1538 11.5849 13.8462'
			. ' 12.0097 13.9023 12.4699Z%22 fill=%22%23525C69%22/%3E%3C/svg%3E%0A'
		;

		$dialog->addTab(new Tab([
			'id' => static::TAB_ID,
			'title' => Loc::getMessage('BIZPROC_ENTITY_SELECTOR_TEMPLATES_TAB_AUTOMATION_TEMPLATES_TITLE'),
			'itemOrder' => ['sort' => 'asc nulls last'],
			'stub' => true,
			'icon' => [
				'default' => $icon, // /bitrix/js/ui/icon-set/main/images/robot.svg
				'selected' => str_replace('525C69', 'fff', $icon),
			], // todo
		]));
	}

	protected function getDefaultTemplateFilter(): ConditionTree
	{
		return (
			\Bitrix\Main\ORM\Query\Query::filter()
				->where('ACTIVE', 'Y')
				->where('AUTO_EXECUTE', \CBPDocumentEventType::Automation)
		);
	}
}