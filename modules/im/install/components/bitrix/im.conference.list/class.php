<?php

use Bitrix\Im\Call\Call;
use Bitrix\Im\Call\Conference;
use Bitrix\Im\User;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Main\Grid\Options as GridOptions;
use Bitrix\Main\UI\Filter\Options as FilterOptions;
use Bitrix\Main\UI\PageNavigation;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

class ImComponentConferenceList extends CBitrixComponent
{
	protected function checkModules(): bool
	{
		if (!Loader::includeModule('im'))
		{
			ShowError(Loc::getMessage('IM_COMPONENT_MODULE_IM_NOT_INSTALLED'));
			return false;
		}
		return true;
	}

	protected function initParams(): void
	{
		$this->arResult['GRID_ID'] = 'CONFERENCE_LIST_GRID';
		$this->arResult['FILTER_ID'] = 'CONFERENCE_LIST_GRID_FILTER';
		$this->arResult['STATUS_LIST'] = Conference::getStatusList();
		$this->arResult['COLUMNS'] = $this->getGridColumns();
		$this->arResult['FILTERS'] = $this->getFilter();
		$this->arResult['FILTER_PRESETS'] = $this->getFilterPresets();
		$this->arResult['SLIDER_WIDTH'] = 800;
		$this->arResult['USER_LIMIT'] = Call::getMaxCallServerParticipants();

		$this->arResult['ROWS'] = [];

		$pageSizes = [];
		foreach ([5, 10, 20, 30, 50, 100] as $index)
		{
			$pageSizes[] = ['NAME' => $index, 'VALUE' => $index];
		}

		$gridOptions = new GridOptions($this->arResult['GRID_ID']);
		$navData = $gridOptions->getNavParams(['nPageSize' => 10]);
		$nav = new PageNavigation('conference-list');
		$nav->allowAllRecords(true)
			->setPageSize($navData['nPageSize'])
			->setPageSizes($pageSizes)
			->initFromUri();

		$queryParams = [
			'filter' => $this->getDataFilter(),
			'offset' => $nav->getOffset(),
			'limit' => $nav->getLimit(),
			'count_total' => true,
			'order' => $this->getGridOrder(),
			'select' => Conference::getDefaultSelectFields(),
			'runtime' => Conference::getRuntimeChatField()
		];
		$conferenceData = Conference::getAll($queryParams);

		foreach ($conferenceData as $item)
		{
			$conference = Conference::createWithArray($item);
			$formattedItem = [];
			$formattedItem['ID'] = $conference->getId();
			$formattedItem['CONFERENCE_START'] = $conference->getStartDate();
			$formattedItem['CHAT_NAME'] = $this->getChatNameHTML($conference);
			$formattedItem['RECORD'] = 'NOPE';

			$formattedItem['CONTROLS'] = $this->getControlsHTML($conference);
			$formattedItem['HOST'] = $this->getHostHTML($conference);
//			$formattedItem['STATUS'] = $this->getStatusText($conference->getStatus());

			$row = [
				'id' => $item['ID'],
				'columns' => $formattedItem,
				'actions' => $this->getActions($conference),
				'attrs' => [
					'data-conference-id' => $conference->getId(),
					'data-chat-id' => $conference->getChatId(),
					'data-public-link' => $conference->getPublicLink()
				]
			];
//			if ($conference->isFinished())
//			{
//				$row['attrs']['data-conference-finished'] = true;
//			}
			$this->arResult['ROWS'][] = $row;
		}

		$this->arResult['TOTAL_ROWS_COUNT'] = $conferenceData->getCount();
		$nav->setRecordCount($conferenceData->getCount());
		$this->arResult['NAV_OBJECT'] = $nav;
	}

	protected function getGridColumns(): array
	{
		return [
			[
				"id" => "ID",
				"name" => "ID",
				"sort" => "ID",
				"default" => false
			],
//			[
//				"id" => "CONFERENCE_START",
//				"name" => Loc::getMessage('CONFERENCE_LIST_GRID_COLUMN_DATE'),
//				"sort" => "CONFERENCE_START",
//				"default" => true
//			],
			[
				"id" => "CHAT_NAME",
				"name" => Loc::getMessage('CONFERENCE_LIST_GRID_COLUMN_NAME'),
				"default" => true
			],
			[
				"id" => "CONTROLS",
				"name" => Loc::getMessage('CONFERENCE_LIST_GRID_COLUMN_CONTROLS'),
				"default" => true
			],
			[
				"id" => "HOST",
				"name" => Loc::getMessage('CONFERENCE_LIST_GRID_COLUMN_HOST'),
				"default" => true
			]
		];
	}

	protected function getGridOrder(): array
	{
		$defaultSort = array('ID' => 'DESC');

		$gridOptions = new GridOptions($this->arResult['GRID_ID']);
		$sorting = $gridOptions->getSorting(array('sort' => $defaultSort));

		$by = key($sorting['sort']);
		$order = strtoupper(current($sorting['sort'])) === 'ASC' ? 'ASC' : 'DESC';

		$list = array();
		foreach ($this->getGridColumns() as $column)
		{
			if (!isset($column['sort']) || !$column['sort'])
			{
				continue;
			}

			$list[] = $column['sort'];
		}

		if (!in_array($by, $list, true))
		{
			return $defaultSort;
		}

		return array($by => $order);
	}

	protected function getDataFilter(): array
	{
		$filterOptions = new FilterOptions($this->arResult['FILTER_ID']);
		$requestFilter = $filterOptions->getFilter($this->arResult['FILTERS']);
		$searchString = $filterOptions->getSearchString();

		$filter = [];

//		if (isset($requestFilter['CONFERENCE_START_from']) && $requestFilter['CONFERENCE_START_from'])
//		{
//			$filter['>=CONFERENCE_START'] = $requestFilter['CONFERENCE_START_from'];
//		}
//		if (isset($requestFilter['CONFERENCE_START_to']) && $requestFilter['CONFERENCE_START_to'])
//		{
//			$filter['<=CONFERENCE_START'] = $requestFilter['CONFERENCE_START_to'];
//		}

		if (isset($requestFilter['CHAT_NAME']) && $requestFilter['CHAT_NAME'])
		{
			$filter['CHAT_NAME'] = '%' . $requestFilter['CHAT_NAME'] . '%';
		}

		if (isset($requestFilter['HOST']) && $requestFilter['HOST'])
		{
			foreach ($requestFilter['HOST'] as $uid)
			{
				$filter['=HOST'][] = substr($uid, 1);
			}
		}

		$filter['=RELATION.USER_ID'] = User::getInstance()->getId();

		return $filter;
	}

	protected function getFilter(): array
	{
		return [
//			[
//				"id" => "CONFERENCE_START",
//				"name" => Loc::getMessage('CONFERENCE_LIST_GRID_COLUMN_DATE'),
//				"type" => "date",
//				"default" => true
//			],
			[
				"id" => "CHAT_NAME",
				"name" => Loc::getMessage('CONFERENCE_LIST_GRID_COLUMN_NAME'),
				"default" => true
			],
			[
				"id" => "HOST",
				"name" => Loc::getMessage('CONFERENCE_LIST_GRID_COLUMN_HOST'),
				"type" => "dest_selector",
				"default" => true,
				'params' => [
					'apiVersion' => 3,
					'multiple' => 'Y',
					'departmentSelectDisable' => 'Y'
				]
			]
		];
	}

	protected function getFilterPresets(): array
	{
		return [
//			'filter_conference_my' => array(
//				'name' => Loc::getMessage('CONFERENCE_LIST_PRESET_MY'),
//				'default' => true,
//				'fields' => array(
//					'HOST' => ['U' . $GLOBALS['USER']->GetID()]
//				)
//			),
			//			'filter_conference_planned' => array(
			//				'name' => Loc::getMessage(''),
			//				'default' => true,
			//				'fields' => array(
			//					'STATE' => ,
			//				)
			//			),
			//			'filter_conference_finished' => array(
			//				'name' => Loc::getMessage(''),
			//				'fields' => array(
			//					'STATE' => ,
			//				)
			//			),
			'filter_conference_all' => array(
				'name' => Loc::getMessage('CONFERENCE_LIST_PRESET_ALL'),
				'fields' => array(),
				'default' => true,
			),
		];
	}

	protected function getActions(Conference $conference): array
	{
		$actions = [
			[
				'TITLE' => Loc::getMessage('CONFERENCE_LIST_ACTION_OPEN_CHAT'),
				'TEXT' => Loc::getMessage('CONFERENCE_LIST_ACTION_OPEN_CHAT'),
				'ONCLICK' => $this->getOpenMessengerCode($conference),
			]
		];

		if ($conference->canUserEdit(CurrentUser::get()->getId()))
		{
			$actions[] = [
				'TITLE' => Loc::getMessage('CONFERENCE_LIST_ACTION_EDIT'),
				'TEXT' => Loc::getMessage('CONFERENCE_LIST_ACTION_EDIT'),
				'ONCLICK' => $this->getEditSliderCode($conference, $this->arResult['SLIDER_WIDTH']),
			];
		}

		return $actions;
	}

	protected function getStatusText($statusCode): string
	{
		if ($statusCode === Conference::STATE_NOT_STARTED)
		{
			return Loc::getMessage('CONFERENCE_LIST_STATUS_NOT_STARTED');
		}

		if ($statusCode === Conference::STATE_ACTIVE)
		{
			return Loc::getMessage('CONFERENCE_LIST_STATUS_ACTIVE');
		}

		return Loc::getMessage('CONFERENCE_LIST_STATUS_FINISHED');
	}

	protected function getChatNameHTML(Conference $conference): string
	{
		$resultString = "";
		$chatName = htmlspecialcharsbx($conference->getChatName());

		if ($conference->canUserEdit(CurrentUser::get()->getId()))
		{
			$resultString .= "
				<div class='im-conference-list-chat-name'>
					<a class='im-conference-list-chat-name-link'>
						{$chatName}
					</a>
				</div>
			";
		}
		else
		{
			$resultString .= "<div class='im-conference-list-chat-name im-conference-list-chat-name-no-hover'>{$chatName}</div>";
		}

		return $resultString;
	}

	protected function getHostHTML(Conference $conference): string
	{
		$pathToProfile = str_replace('#id#', $conference->getHostId(), $this->arParams['PATH_TO_USER_PROFILE']);
		$hostName = htmlspecialcharsbx($conference->getHostName());

		return "<a href=\"{$pathToProfile}\">{$hostName}</a>";
	}

	protected function getControlsHTML(Conference $conference): string
	{
		$resultString = "";
		$publicLink = $conference->getPublicLink();
		$startTitle = Loc::getMessage('CONFERENCE_LIST_GRID_CONTROLS_START');
		$copyTitle = Loc::getMessage('CONFERENCE_LIST_GRID_CONTROLS_COPY');

//		if (!$conference->isFinished())
//		{
			$resultString .= "
			<div class='im-conference-list-controls-buttons'>
				<a href='{$publicLink}' class='im-conference-list-controls-button-start ui-btn ui-btn-sm ui-btn-primary ui-btn-icon-start'>{$startTitle}</a>
				<button class='im-conference-list-controls-button-copy ui-btn ui-btn-sm ui-btn-light-border ui-btn-icon-page'>{$copyTitle}</button>
				<button class='im-conference-list-controls-button-more ui-btn ui-btn-sm ui-btn-light-border'></button>
			</div>
			";
//		}
//		else
//		{
//			$finishedStatusTitle = Loc::getMessage('CONFERENCE_LIST_GRID_CONTROLS_FINISHED_TITLE');
//			$resultString .= "
//				<div class='im-conference-list-controls-finished'>
//					<div class='im-conference-list-controls-finished-title'>
//						<span class='im-conference-list-controls-finished-icon'></span>
//						{$finishedStatusTitle}
//					</div>
//					<button class='im-conference-list-controls-button-more ui-btn ui-btn-sm ui-btn-light-border'></button>
//				</div>
//			";
//		}

		return $resultString;
	}

	protected function getOpenMessengerCode(Conference $conference): string
	{
		$chatId = $conference->getChatId();

		return "BXIM.openMessenger(\"chat{$chatId}\")";
	}

	protected function getEditSliderCode(Conference $conference, $sliderWidth): string
	{
		$pathToEdit = str_replace('#id#', $conference->getId(), $this->arParams['PATH_TO_EDIT']);

		return "BX.SidePanel.Instance.open(\"{$pathToEdit}\", {width: {$sliderWidth}, cacheable: false}); return false;";
	}

	public function executeComponent()
	{
		if (!$this->checkModules())
		{
			return false;
		}

		$this->initParams();

		$this->includeComponentTemplate();

		return $this->arResult;
	}
}