<?

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SidePanel\ToolbarItemTable;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

class MainSidePanelToolbarComponent extends \CBitrixComponent
{
	public function __construct($component = null)
	{
		parent::__construct($component);
	}

	public function executeComponent()
	{
		if (empty($this->arParams['CONTEXT']) || !is_string($this->arParams['CONTEXT']))
		{
			return;
		}

		$userId = (int)(\Bitrix\Main\Engine\CurrentUser::get()->getId());
		if ($userId < 1)
		{
			return;
		}

		$items = [];
		$toolbar = \Bitrix\Main\SidePanel\Toolbar::get($this->arParams['CONTEXT'], $userId);
		if ($toolbar !== null)
		{
			foreach ($toolbar->getItems() as $item)
			{
				$items[] = [
					'entityType' => $item->getEntityType(),
					'entityId' => $item->getEntityId(),
					'title' => $item->getTitle(),
					'url' => $item->getUrl(),
				];
			}
		}

		$position =
			!empty($this->arParams['POSITION']) && is_array($this->arParams['POSITION'])
				? $this->arParams['POSITION']
				: null
		;

		$shiftedPosition =
			!empty($this->arParams['SHIFTED_POSITION']) && is_array($this->arParams['SHIFTED_POSITION'])
				? $this->arParams['SHIFTED_POSITION']
				: null
		;

		$this->arResult['spotlight'] = false;
		$this->arResult['spotlightTitle'] = '';
		$this->arResult['spotlightHint'] = '';
		$this->arResult['options'] = [
			'items' => $items,
			'collapsed' => $toolbar === null || $toolbar->isCollapsed(),
			'context' => $this->arParams['CONTEXT'],
			'position' => $position,
			'shiftedPosition' => $shiftedPosition,
		];

		$spotlight = new \Bitrix\Main\UI\Spotlight('sidepanel_toolbar');
		$spotlight->setUserTimeSpan(3600 * 24 * 14);
		$spotlight->setEndDate(gmmktime(8, 30, 0, 1, 1, 2024));
		if ($spotlight->isAvailable())
		{
			$this->arResult['spotlight'] = true;
			$this->arResult['spotlightTitle'] = Loc::getMessage('SIDEPANEL_TOOLBAR_HINT_TITLE');
			$this->arResult['spotlightHint'] = Loc::getMessage('SIDEPANEL_TOOLBAR_HINT');
		}

		$this->includeComponentTemplate();
	}

	protected function getItems(int $toolbarId): array
	{
		$items = ToolbarItemTable::getList([
			'filter' => [
				'=TOOLBAR_ID' => $toolbarId,
			],
			'order' => ['LAST_USE_DATE' => 'DESC'],
			'limit' => 100,
		])->fetchCollection();

		$result = [];
		foreach ($items as $item)
		{
			$result[] = [
				'entityType' => $item->getEntityType(),
				'entityId' => $item->getEntityId(),
				'title' => $item->getTitle(),
				'url' => $item->getUrl(),
			];
		}

		return $result;
	}
}
