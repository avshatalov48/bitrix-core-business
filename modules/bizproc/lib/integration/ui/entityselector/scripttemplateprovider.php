<?php

namespace Bitrix\Bizproc\Integration\UI\EntitySelector;

use Bitrix\Bizproc\Workflow\Template\Tpl;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Query\Filter\ConditionTree;
use Bitrix\UI\EntitySelector\Dialog;
use Bitrix\UI\EntitySelector\Tab;

class ScriptTemplateProvider extends TemplateProvider
{
	protected const ENTITY_ID = 'bizproc-script-template';
	protected const TAB_ID = 'script-templates';

	public function __construct(array $options = [])
	{
		parent::__construct($options);
		$this->options = [];
	}

	public function fillDialog(Dialog $dialog): void
	{
		$this->addTemplatesTab($dialog);
		$currentUserId = $this->getCurrentUserId();

		$complexDocumentTypes = $this->getComplexDocumentTypes();
		foreach ($complexDocumentTypes as $documentType)
		{
			$moduleId = $documentType[0];
			if (IsModuleInstalled($moduleId) && $this->canUserStartWorkflow($currentUserId, $documentType))
			{
				$documentItem = $this->getDocumentItem($dialog, $documentType);
				if (!$dialog->getItemCollection()->has($documentItem))
				{
					$documentItem->setNodeOptions(['dynamic' => true, 'open' => false]);
					$dialog->addItem($documentItem);
				}
			}
		}

		if (count($dialog->getItemCollection()->getEntityItems(static::ENTITY_ID)) === 1)
		{
			$first = current($dialog->getItemCollection()->getEntityItems(static::ENTITY_ID));
			$first->setNodeOptions(['dynamic' => true, 'open' => true]);
		}

		$this->openPreselectedItemTree($dialog);
	}

	protected function addTemplatesTab(Dialog $dialog): void
	{
		$dialog->addTab(new Tab([
			'id' => self::TAB_ID,
			'title' => Loc::getMessage('BIZPROC_ENTITY_SELECTOR_TEMPLATES_TAB_SCRIPT_TEMPLATES_TITLE'),
			'itemOrder' => ['sort' => 'asc nulls last'],
			'stub' => true,
		]));
	}

	protected function openTemplateTree(Dialog $dialog, Tpl $template): void
	{
		$currentUserId = $this->getCurrentUserId();

		$documentItem = $dialog->getItemCollection()->get(
			static::ENTITY_ID,
			static::ITEM_DOCUMENT_TYPE_PREFIX . $template->getDocumentType()
		);

		if ($documentItem)
		{
			$documentItem
				->setNodeOptions(['open' => true, 'dynamic' => false, 'itemOrder' => ['sort' => 'asc nulls last']])
				->setSort(1)
			;

			$templateItem = $documentItem->getChildren()->get(static::ENTITY_ID, $template->getId());
			if (!$templateItem)
			{
				$this->fillDocumentItem($dialog, $documentItem, $currentUserId);
				$templateItem = $documentItem->getChildren()->get(self::ENTITY_ID, $template->getId());
			}

			$templateItem->setSort(1);
		}
	}

	protected function canUserStartWorkflow(int $userId, array $complexDocumentType): bool
	{
		// todo: use API \Bitrix\Bizproc\Script\Manager

		return (
			$this->isUserWorkflowTemplateAdmin($userId)
			|| \CBPDocument::canUserOperateDocumentType(
				\CBPCanUserOperateOperation::ViewWorkflow,
				$userId,
				$complexDocumentType
			)
		);
	}

	protected function getDefaultTemplateFilter(): ConditionTree
	{
		return (
			\Bitrix\Main\ORM\Query\Query::filter()
				->where('ACTIVE', 'Y')
				->where('AUTO_EXECUTE', \CBPDocumentEventType::Script)
		);
	}
}