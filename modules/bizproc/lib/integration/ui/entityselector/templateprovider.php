<?php

namespace Bitrix\Bizproc\Integration\UI\EntitySelector;

use Bitrix\Bizproc\Workflow\Template\Entity\EO_WorkflowTemplate_Collection;
use Bitrix\Bizproc\Workflow\Template\Tpl;
use Bitrix\Bizproc\WorkflowTemplateTable;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Query\Filter\ConditionTree;
use Bitrix\UI\EntitySelector\BaseProvider;
use Bitrix\UI\EntitySelector\Dialog;
use Bitrix\UI\EntitySelector\Item;
use Bitrix\UI\EntitySelector\SearchQuery;
use Bitrix\UI\EntitySelector\Tab;

class TemplateProvider extends BaseProvider
{
	protected const ENTITY_ID = 'bizproc-template';
	protected const TAB_ID = 'templates';
	protected const ITEM_MODULE_ID_PREFIX = 'module:';
	protected const ITEM_DOCUMENT_TYPE_PREFIX = 'document:';

	protected ?array $complexDocumentTypesCache = null;

	public function __construct(array $options = [])
	{
		parent::__construct();

		$this->options['showManual'] = (isset($options['showManual']) && $options['showManual'] === true);
	}

	public function isAvailable(): bool
	{
		return $this->getCurrentUserId() > 0;
	}

	public function getItems(array $ids): array
	{
		$ids = array_filter(array_map('intval', $ids));
		$templates = $this->getTemplatesByIds($ids);
		$currentUserId = $this->getCurrentUserId();

		$items = [];
		foreach ($templates as $template)
		{
			if ($this->canUserStartWorkflow($currentUserId, $template->getDocumentComplexType()))
			{
				$items[] = $this->makeItem(['id' => $template->getId(), 'title' => $template->getName()], false);
			}
		}

		return $items;
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
				$moduleItem = $this->getModuleItem($dialog, $moduleId);
				if (!$dialog->getItemCollection()->has($moduleItem))
				{
					$moduleItem->setNodeOptions(['dynamic' => true, 'open' => false]);
					$dialog->addItem($moduleItem);
				}
			}
		}

		$this->openPreselectedItemTree($dialog);
	}

	protected function addTemplatesTab(Dialog $dialog): void
	{
		$icon =
			'data:image/svg+xml,%3Csvg width=%2224%22 height=%2224%22 viewBox=%220 0 24 24%22 fill=%22none%22'
			. ' xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cpath'
			. ' d=%22M5.89991 7.87642C5.89991 6.8374 6.7422 5.99512 7.78121 5.99512H16.2483C17.2873 5.99512 18.1296'
			. ' 6.8374 18.1296 7.87642V9.7578H19.6965C19.8641 9.7578 19.948 9.96044 19.8295 10.079L17.1106'
			. ' 12.7978C17.0372 12.8713 16.9181 12.8713 16.8446 12.7978L14.1257 10.079C14.0072 9.96044 14.0912 9.7578'
			. ' 14.2588 9.7578H15.8878V8.63549C15.8878 8.39305 15.6913 8.19651 15.4489 8.19651H8.58066C8.33822 8.19651'
			. ' 8.14169 8.39305 8.14169 8.63549V9.51372L5.89991 9.26298V7.87642Z%22'
			. ' fill=%22%23525C69%22/%3E%3Cpath d=%22M5.89991 14.2046V16.1233C5.89991 17.1623 6.7422 18.0046 7.78121'
			. ' 18.0046H16.2483C17.2873 18.0046 18.1296 17.1623 18.1296 16.1233V14.6184L15.8878'
			. ' 14.3677V15.3642C15.8878 15.6067 15.6913 15.8032 15.4489 15.8032H8.58066C8.33822 15.8032 8.14169 15.6067'
			. ' 8.14169 15.3642V14.2046H9.7412C9.9088 14.2046 9.99274 14.002 9.87423 13.8835L7.15538 11.1646C7.08191'
			. ' 11.0911 6.96279 11.0911 6.88932 11.1646L4.17047 13.8835C4.05195 14.002 4.13589 14.2046 4.3035'
			. ' 14.2046H5.89991Z%22 fill=%22%23525C69%22/%3E%3C/svg%3E%0A'
		;

		$dialog->addTab(new Tab([
			'id' => static::TAB_ID,
			'title' => Loc::getMessage('BIZPROC_ENTITY_SELECTOR_TEMPLATES_TAB_TEMPLATES_TITLE'),
			'itemOrder' => ['sort' => 'asc nulls last'],
			'stub' => true,
			'stubOptions' => [
				'title' => Loc::getMessage('BIZPROC_ENTITY_SELECTOR_TEMPLATES_TAB_STUB_TITLE'),
			],
			'icon' => [
				'default' => $icon, // /bitrix/js/ui/icon-set/actions/images/refresh-1.svg
				'selected' => str_replace('525C69', 'fff', $icon),
			], // todo
		]));
	}

	protected function openPreselectedItemTree(Dialog $dialog): void
	{
		$currentUserId = $this->getCurrentUserId();

		$preselectedItems = $dialog->getPreselectedCollection()->getEntityItems(static::ENTITY_ID);
		$ids = array_keys($preselectedItems);
		$templates = $this->getTemplatesByIds(array_filter(array_map('intval', $ids)));

		foreach ($templates as $template)
		{
			if (
				IsModuleInstalled($template->getModuleId())
				&& $this->canUserStartWorkflow($currentUserId, $template->getDocumentComplexType())
			)
			{
				$this->openTemplateTree($dialog, $template);
			}
		}
	}

	protected function openTemplateTree(Dialog $dialog, Tpl $template): void
	{
		$currentUserId = $this->getCurrentUserId();

		$moduleItem = $dialog->getItemCollection()->get(
			static::ENTITY_ID,
			$this->createModuleId($template->getModuleId())
		);
		if ($moduleItem)
		{
			$moduleItem
				->setNodeOptions(['open' => true, 'dynamic' => false, 'itemOrder' => ['sort' => 'asc nulls last']])
				->setSort(1)
			;

			$documentItem = $moduleItem->getChildren()->get(
				static::ENTITY_ID,
				$this->createDocumentId($template->getModuleId(), $template->getDocumentType())
			);
			if (!$documentItem)
			{
				$this->fillModuleItem($dialog, $moduleItem, $currentUserId);
				$documentItem = $moduleItem->getChildren()->get(
					static::ENTITY_ID,
					$this->createDocumentId($template->getModuleId(), $template->getDocumentType())
				);
			}
			$documentItem
				->setNodeOptions(['open' => true, 'dynamic' => false, 'itemOrder' => ['sort' => 'asc nulls last']]) // dynamic => true
				->setSort(1)
			;

			$templateItem = $documentItem->getChildren()->get(static::ENTITY_ID, $template->getId());
			if (!$templateItem)
			{
				$this->fillDocumentItem($dialog, $documentItem, $currentUserId);
				$templateItem = $documentItem->getChildren()->get(static::ENTITY_ID, $template->getId());
			}
			$templateItem->setSort(1);
		}
	}

	protected function getModuleItem(Dialog $dialog, string $moduleId): Item
	{
		$id = $this->createModuleId($moduleId);
		$moduleItem = $dialog->getItemCollection()->get(static::ENTITY_ID, $id);
		if ($moduleItem === null)
		{
			$title =
				Loc::getMessage('BIZPROC_ENTITY_SELECTOR_TEMPLATES_MODULE_' . mb_strtoupper($moduleId))
					?: $moduleId
			;
			$moduleItem = $this->makeItem(['id' => $id, 'title' => $title]);
			$moduleItem->setCustomData(['moduleId' => $moduleId]);
			$moduleItem->setSearchable(false);
		}

		return $moduleItem;
	}

	protected function getDocumentItem(Dialog $dialog, array $complexDocumentType): Item
	{
		$id = $this->createDocumentId($complexDocumentType[0], $complexDocumentType[2]);
		$documentItem = $dialog->getItemCollection()->get(static::ENTITY_ID, $id);
		if ($documentItem === null)
		{
			$documentService = \CBPRuntime::getRuntime()->getDocumentService();

			$title = $documentService->getDocumentTypeCaption($complexDocumentType);
			if (\CBPHelper::isEmptyValue($title))
			{
				$title = $complexDocumentType[2];
			}

			$documentItem = $this->makeItem(['id' => $id, 'title' => $title]);
			$documentItem->setCustomData([
				'moduleId' => $complexDocumentType[0],
				'documentType' => $complexDocumentType[2],
			]);
			$documentItem->setSearchable(false);
		}

		return $documentItem;
	}

	public function getChildren(Item $parentItem, Dialog $dialog): void
	{
		$currentUserId = $this->getCurrentUserId();
		$parentItemId = $parentItem->getId();

		if (mb_strpos($parentItemId, static::ITEM_MODULE_ID_PREFIX) === 0)
		{
			if (mb_strpos($parentItemId, static::ITEM_DOCUMENT_TYPE_PREFIX) !== false)
			{
				$this->fillDocumentItem($dialog, $parentItem, $currentUserId);
			}
			else
			{
				$this->fillModuleItem($dialog, $parentItem, $currentUserId);
			}

			$dialog->addItems($parentItem->getChildren()->getAll());
		}

		parent::getChildren($parentItem, $dialog);
	}

	protected function fillModuleItem(Dialog $dialog, Item $moduleItem, int $currentUserId): void
	{
		$moduleId = $this->parseModuleItemId($moduleItem->getId());
		if ($moduleId && IsModuleInstalled($moduleId))
		{
			$complexDocumentTypes = $this->getComplexDocumentTypes($moduleId);
			foreach ($complexDocumentTypes as $complexDocumentType)
			{
				if ($this->canUserStartWorkflow($currentUserId, $complexDocumentType))
				{
					$documentItem = $this->getDocumentItem($dialog, $complexDocumentType);
					$documentItem->setNodeOptions(['dynamic' => true]);
					$moduleItem->addChild($documentItem);
				}
			}
		}
	}

	protected function fillDocumentItem(Dialog $dialog, Item $documentItem, int $currentUserId): void
	{
		[$moduleId, $documentType] = $this->parseDocumentItemId($documentItem->getId());

		if ($moduleId && $documentType && IsModuleInstalled($moduleId))
		{
			$templates = $this->getTemplatesByDocumentType($moduleId, $documentType);
			foreach ($templates as $template)
			{
				if ($this->canUserStartWorkflow($currentUserId, $template->getDocumentComplexType()))
				{
					$item = $this->makeItem(['id' => $template->getId(), 'title' => $template->getName()]);
					$documentItem->addChild($item);
				}
			}
		}
	}

	private function getTemplatesByIds(array $ids): EO_WorkflowTemplate_Collection
	{
		if (!$ids)
		{
			return new EO_WorkflowTemplate_Collection();
		}

		$query =
			WorkflowTemplateTable::query()
				->setSelect(['ID', 'MODULE_ID', 'ENTITY', 'DOCUMENT_TYPE', 'NAME'])
				->where($this->getDefaultTemplateFilter())
		;
		if (count($ids) === 1)
		{
			$query->where('ID', $ids[0]);
		}
		else
		{
			$query->whereIn('ID', $ids);
		}

		return $query->exec()->fetchCollection();
	}

	private function getTemplatesByDocumentType(
		string $moduleId,
		string $documentType
	): \Bitrix\Bizproc\Workflow\Template\Entity\EO_WorkflowTemplate_Collection
	{
		$query =
			WorkflowTemplateTable::query()
				->setSelect(['ID', 'MODULE_ID', 'ENTITY', 'DOCUMENT_TYPE', 'NAME'])
				->where('MODULE_ID', $moduleId)
				->where('DOCUMENT_TYPE', $documentType)
				->where($this->getDefaultTemplateFilter())
		;

		return $query->exec()->fetchCollection();
	}

	protected function getComplexDocumentTypes(string $moduleId = ''): array
	{
		if ($this->complexDocumentTypesCache === null)
		{
			$query =
				WorkflowTemplateTable::query()
					->setDistinct()
					->setSelect(['MODULE_ID', 'ENTITY', 'DOCUMENT_TYPE'])
					->where($this->getDefaultTemplateFilter())
			;
			$complexDocumentTypes = $query->exec()->fetchAll();

			$this->complexDocumentTypesCache = [];
			foreach ($complexDocumentTypes as $documentType)
			{
				$this->complexDocumentTypesCache[] =
					[$documentType['MODULE_ID'], $documentType['ENTITY'], $documentType['DOCUMENT_TYPE']]
				;
			}
		}

		$filter = static fn ($docType) => ($docType[0] === $moduleId);

		return $moduleId ? array_filter($this->complexDocumentTypesCache, $filter) : $this->complexDocumentTypesCache;
	}

	public function doSearch(SearchQuery $searchQuery, Dialog $dialog): void
	{
		$currentUserId = $this->getCurrentUserId();

		$templates =
			WorkflowTemplateTable::query()
				->setSelect(['ID', 'MODULE_ID', 'ENTITY', 'DOCUMENT_TYPE', 'NAME'])
				->whereLike('NAME', "%{$searchQuery->getQuery()}%")
				->where($this->getDefaultTemplateFilter())
				->exec()
				->fetchCollection();

		$items = [];
		foreach ($templates as $template)
		{
			if ($this->canUserStartWorkflow($currentUserId, $template->getDocumentComplexType()))
			{
				$items[] = $this->makeItem(['id' => $template->getId(), 'title' => $template->getName()], false);
			}
		}

		if ($items)
		{
			$dialog->addItems($items);
		}
	}

	protected function getDefaultTemplateFilter(): ConditionTree
	{
		$filter = \Bitrix\Main\ORM\Query\Query::filter();
		$filter->where('ACTIVE', 'Y');

		$autoExecuteFilter =
			\Bitrix\Main\ORM\Query\Query::filter()
				->logic(ConditionTree::LOGIC_OR)
				->where('AUTO_EXECUTE', '<', \CBPDocumentEventType::Automation)
		;

		if ($this->options['showManual'])
		{
			$autoExecuteFilter->where('AUTO_EXECUTE', \CBPDocumentEventType::Manual);
		}

		return $filter->where($autoExecuteFilter);
	}

	protected function canUserStartWorkflow(int $userId, array $complexDocumentType): bool
	{
		if ($this->isUserWorkflowTemplateAdmin($userId))
		{
			return true;
		}

		try
		{
			return \CBPDocument::canUserOperateDocumentType(
				\CBPCanUserOperateOperation::StartWorkflow,
				$userId,
				$complexDocumentType
			);
		}
		catch (\CBPArgumentNullException $exception)
		{
			//return false;
		}

		return false;
	}

	private function makeItem(array $data, bool $addTab = true): Item
	{
		$item = new Item([
			'id' => $data['id'],
			'entityId' => static::ENTITY_ID,
			'title' => $data['title'],
		]);
		if ($addTab)
		{
			$item->addTab(static::TAB_ID);
		}

		return $item;
	}

	protected function isUserWorkflowTemplateAdmin(int $userId): bool
	{
		return (new \CBPWorkflowTemplateUser($userId))->isAdmin();
	}

	protected function getCurrentUserId(): int
	{
		return (int)(CurrentUser::get()->getId());
	}

	protected function createModuleId(string $moduleId): string
	{
		return static::ITEM_MODULE_ID_PREFIX . $moduleId;
	}

	protected function createDocumentId(string $moduleId, string $documentId): string
	{
		return $this->createModuleId($moduleId) . '@' . static::ITEM_DOCUMENT_TYPE_PREFIX . $documentId;
	}

	protected function parseModuleItemId(string $id): string
	{
		return mb_substr($id, strlen(static::ITEM_MODULE_ID_PREFIX));
	}

	protected function parseDocumentItemId(string $id): array
	{
		[$moduleItemId, $documentItemId] = mb_split('@', $id);

		$document = mb_substr($documentItemId, strlen(static::ITEM_DOCUMENT_TYPE_PREFIX));

		return [$this->parseModuleItemId($moduleItemId), $document];
	}
}