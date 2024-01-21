<?php
namespace Bitrix\Iblock\Grid\Panel;

use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Iblock;

/**
 * Group actions for admin list. For ui grid see `ElementPanelProvider`.
 *
 * @see \Bitrix\Iblock\Grid\Panel\UI\ElementPanelProvider
 */
class GroupAction
{
	public const GRID_TYPE_UI = 'main.ui.grid';
	public const GRID_TYPE_LIST = 'adminList';
	public const GRID_TYPE_SUBLIST = 'subList';

	private const PREFIX_ID = 'iblock_grid_action_';

	/** @var string Grid Id */
	protected $entityId = '';

	protected $gridType = self::GRID_TYPE_UI;

	/** @var array */
	protected $options = [];

	/** @var int */
	protected $iblockId = null;

	/** @var array */
	protected $iblockConfig = [
		'SECTIONS' => 'N',
		'SECTION_CHOOSER' => Iblock\IblockTable::SECTION_CHOOSER_SELECT
	];

	/** @var Main\Grid\Panel\Snippet */
	protected $mainSnippet = null;

	/** @var Main\HttpRequest */
	protected $request = null;

	/** @var array */
	protected $sections = null;

	protected $actionHandlers = [];

	public function __construct(array $options)
	{
		$this->options = $options;

		$this->entityId = $options['ENTITY_ID'];
		$this->iblockId = $options['IBLOCK_ID'];

		if (isset($options['GRID_TYPE']))
		{
			$this->setGridType($options['GRID_TYPE']);
		}

		$this->mainSnippet = new Main\Grid\Panel\Snippet();
		$this->request = Main\Context::getCurrent()->getRequest();

		$this->initConfig();

		$this->initActions();
	}

	/**
	 * @param array|null $actions
	 * @return array
	 */
	public function getList(?array $actions = null): array
	{
		$result = [];

		$actions ??= array_keys($this->actionHandlers);

		if (!empty($actions))
		{
			foreach ($actions as $code => $params)
			{
				if (is_string($params))
				{
					$code = $params;
					$params = [];
				}
				if (is_string($code) && is_array($params))
				{
					$row = $this->get($code, $params);
					if ($row !== null)
					{
						$result[$code] = $row;
					}
				}
			}
			unset($row, $code, $params);
		}

		return $result;
	}

	/**
	 * @param string $code
	 * @param array $params
	 * @return array|string|null
	 */
	public function get(string $code, array $params = [])
	{
		$code = trim($code);
		if ($code === '' || !isset($this->actionHandlers[$code]))
		{
			return null;
		}

		$method = 'action'.$this->actionHandlers[$code].'Panel';
		if (is_callable([$this, $method]))
		{
			return call_user_func_array([$this, $method], [$params]);
		}

		return [];
	}

	/**
	 * @param string $code
	 * @return array|null
	 */
	public function getRequest(string $code): ?array
	{
		$code = trim($code);
		if ($code === '' || !isset($this->actionHandlers[$code]))
		{
			return null;
		}

		$method = 'action'.$this->actionHandlers[$code].'Request';
		if (is_callable([$this, $method]))
		{
			return call_user_func_array([$this, $method], []);
		}

		return [];
	}

	/**
	 * @return string
	 */
	public function getEntityId(): string
	{
		return $this->entityId;
	}

	/**
	 * @return int
	 */
	public function getIblockId(): int
	{
		return $this->iblockId;
	}

	/**
	 * @return array
	 */
	public function getOptions(): array
	{
		return $this->options;
	}

	/**
	 * @return void
	 */
	protected function initConfig()
	{
		$iterator = Iblock\IblockTable::getList([
			'select' => [
				'ID',
				'SECTION_CHOOSER',
				'SECTIONS' => 'TYPE.SECTIONS'
			],
			'filter' => ['=ID' => $this->iblockId],
		]);
		$row = $iterator->fetch();
		if (!empty($row))
		{
			$this->iblockConfig['SECTIONS'] = $row['SECTIONS'];
			$this->iblockConfig['SECTION_CHOOSER'] = $row['SECTION_CHOOSER'];
		}
		unset($row, $iterator);
	}

	/**
	 * @return void
	 */
	protected function initActions(): void
	{
		$this->actionHandlers = $this->getActionHandlers();
	}

	/**
	 * @param string $value
	 * @return void
	 */
	protected function setGridType(string $value): void
	{
		if (
			$value === self::GRID_TYPE_UI
			|| $value === self::GRID_TYPE_LIST
			|| $value === self::GRID_TYPE_SUBLIST
		)
		{
			$this->gridType = $value;
		}
	}

	/**
	 * @return string
	 */
	public function getGridType(): string
	{
		return $this->gridType;
	}

	/**
	 * @return bool
	 */
	public function isUiGrid(): bool
	{
		return $this->getGridType() === self::GRID_TYPE_UI;
	}

	/**
	 * @return array
	 */
	protected function getActionHandlers()
	{
		$result = [];

		$result[Iblock\Grid\ActionType::EDIT] = 'Edit';
		$result[Iblock\Grid\ActionType::SELECT_ALL] = 'SelectAll';
		$result[Iblock\Grid\ActionType::DELETE] = 'Delete';
		$result[Iblock\Grid\ActionType::ACTIVATE] = 'Activate';
		$result[Iblock\Grid\ActionType::DEACTIVATE] = 'Deactivate';
		$result[Iblock\Grid\ActionType::CLEAR_COUNTER] = 'ClearCounter';
		$result[Iblock\Grid\ActionType::CODE_TRANSLIT] = 'CodeTranslit';
		$result[Iblock\Grid\ActionType::MOVE_TO_SECTION] = 'AdjustSection';
		$result[Iblock\Grid\ActionType::ADD_TO_SECTION] = 'AddSection';
		$result[Iblock\Grid\ActionType::ELEMENT_UNLOCK] = 'ElementUnlock';
		$result[Iblock\Grid\ActionType::ELEMENT_LOCK] = 'ElementLock';
		$result[Iblock\Grid\ActionType::ELEMENT_WORKFLOW_STATUS] = 'ElementWorkflowStatus';

		return $result;
	}

	/**
	 * @return array
	 */
	public function getDefaultApplyAction(): array
	{
		return ['JS' => "BX.adminUiList.SendSelected('{$this->getEntityId()}')"];
	}

	/**
	 * @param string $id
	 * @return string
	 */
	public function getElementId(string $id): string
	{
		return self::PREFIX_ID.$this->getEntityId().'_'.strtolower($id);
	}

	/**
	 * @param array $params
	 * @return array
	 */
	public function getApplyButton(array $params): array
	{
		$result = $this->mainSnippet->getApplyButton([]);
		$result['id'] = $this->getElementId($params['APPLY_BUTTON_ID']);
		$this->mainSnippet->setButtonActions(
			$result,
			[
				[
					'ACTION' => Main\Grid\Panel\Actions::CALLBACK,
					'DATA' => [
						$this->getDefaultApplyAction(),
					],
				],
			]
		);
		return $result;
	}

	/**
	 * @param array $params
	 * @return array
	 */
	public function getApplyButtonWithConfirm(array $params): array
	{
		$confirmMessage = null;
		if (
			isset($params['CONFIRM_MESSAGE'])
			&& is_string($params['CONFIRM_MESSAGE'])
			&& $params['CONFIRM_MESSAGE'] !== ''
		)
		{
			$confirmMessage = $params['CONFIRM_MESSAGE'];
		}
		elseif (
			isset($params['DEFAULT_CONFIRM_MESSAGE'])
			&& is_string($params['DEFAULT_CONFIRM_MESSAGE'])
			&& $params['DEFAULT_CONFIRM_MESSAGE'] !== ''
		)
		{
			$confirmMessage = $params['DEFAULT_CONFIRM_MESSAGE'];
		}

		$result = $this->mainSnippet->getApplyButton([]);
		$result['id'] = $this->getElementId($params['APPLY_BUTTON_ID']);
		$this->mainSnippet->setButtonActions(
			$result,
			[
				[
					'ACTION' => Main\Grid\Panel\Actions::CALLBACK,
					'CONFIRM' => true,
					'CONFIRM_MESSAGE' => $confirmMessage,
					'DATA' => [
						$this->getDefaultApplyAction(),
					],
				],
			]
		);

		return $result;
	}

	/**
	 * @return void
	 */
	protected function loadSections(): void
	{
		if ($this->sections === null)
		{
			$this->sections = [];
			if ($this->iblockId > 0)
			{
				$iterator = \CIBlockSection::getTreeList(
					['IBLOCK_ID' => $this->iblockId],
					['ID', 'NAME', 'DEPTH_LEVEL', 'LEFT_MARGIN']
				);
				while ($row = $iterator->Fetch())
				{
					$this->sections[] = [
						'NAME' => str_repeat(' . ', $row['DEPTH_LEVEL']).$row['NAME'],
						'VALUE' => $row['ID']
					];
				}
				unset($row, $iterator);
			}
		}
	}

	/**
	 * @param bool $addTop
	 * @return array
	 */
	protected function getSections(bool $addTop = false): array
	{
		$this->loadSections();
		$result = $this->sections;
		if ($addTop)
		{
			$result = array_merge(
				[
					[
						'NAME' => Loc::getMessage('IBLOCK_GRID_PANEL_ACTION_MESS_SECTION_TOP_LEVEL'),
						'VALUE' => '0',
					],
				],
				$result
			);
		}
		return $result;
	}

	/**
	 * @param array $action
	 * @return array
	 */
	protected function getAddSectionList(array $action): array
	{
		return [
			'name' => $action['NAME'],
			'type' => 'multicontrol',
			'action' => [
				[
					'ACTION' => Main\Grid\Panel\Actions::RESET_CONTROLS,
				],
				[
					'ACTION' => Main\Grid\Panel\Actions::CREATE,
					'DATA' => [
						[
							'TYPE' => Main\Grid\Panel\Types::DROPDOWN,
							'ID' => $this->getElementId($action['SECTION_LIST_ID']),
							'NAME' => 'section_to_move',
							'ITEMS' => $this->getSections(),
						],
						$this->getApplyButton($action),
					],
				],
			],
		];
	}

	/**
	 * @param array $action
	 * @return array
	 */
	protected function getAddSectionDialog(array $action): array
	{
		return [
			'name' => $action['NAME'],
			'type' => 'multicontrol',
			'action' => [
				[
					'ACTION' => Main\Grid\Panel\Actions::RESET_CONTROLS,
				],
				[
					'ACTION' => Main\Grid\Panel\Actions::CREATE,
					'DATA' => [
						[
							'TYPE' => Main\Grid\Panel\Types::TEXT,
							'ID' => '',
							'NAME' => '',
							'TITLE' => '',
						],
						/*[
							'TYPE' => Main\Grid\Panel\Types::DATE,
							'ID' => '',
							'NAME' => ''
						], */
						[
							'TYPE' => Main\Grid\Panel\Types::BUTTON,
							'ID' => '',
							'NAME' => ''
						],
						$this->getApplyButton($action)
					]
				]
			]
		];
	}

	/**
	 * @param array $action
	 * @return array
	 */
	protected function getAdjustSectionList(array $action): array
	{
		return [
			'name' => $action['NAME'],
			'type' => 'multicontrol',
			'action' => [
				[
					'ACTION' => Main\Grid\Panel\Actions::RESET_CONTROLS
				],
				[
					'ACTION' => Main\Grid\Panel\Actions::CREATE,
					'DATA' => [
						[
							'TYPE' => Main\Grid\Panel\Types::DROPDOWN,
							'ID' => $this->getElementId($action['SECTION_LIST_ID']),
							'NAME' => 'section_to_move',
							'ITEMS' => $this->getSections(true)
						],
						$this->getApplyButton($action)
					]
				]
			]
		];
	}

	/**
	 * @param array $params
	 * @return string
	 */
	protected function actionEditPanel(array $params = []): string
	{
		return (isset($params['NAME']) && $params['NAME'] != ''
			? $params['NAME']
			: Loc::getMessage('IBLOCK_GRID_PANEL_ACTION_EDIT')
		);
	}

	/**
	 * @param array $params
	 * @return true
	 */
	protected function actionSelectAllPanel(array $params = []): bool
	{
		return true;
	}

	/**
	 * @param array $params
	 * @return string
	 */
	protected function actionDeletePanel(array $params = []): string
	{
		return (isset($params['NAME']) && $params['NAME'] != ''
			? $params['NAME']
			: Loc::getMessage('IBLOCK_GRID_PANEL_ACTION_DELETE')
		);
	}

	/**
	 * @param array $params
	 * @return string
	 */
	protected function actionActivatePanel(array $params = []): string
	{
		return (isset($params['NAME']) && $params['NAME'] != ''
			? $params['NAME']
			: Loc::getMessage('IBLOCK_GRID_PANEL_ACTION_ACTIVATE_MSGVER_2')
		);
	}

	/**
	 * @param array $params
	 * @return string
	 */
	protected function actionDeactivatePanel(array $params = []): string
	{
		return (isset($params['NAME']) && $params['NAME'] != ''
			? $params['NAME']
			: Loc::getMessage('IBLOCK_GRID_PANEL_ACTION_DEACTIVATE_MSGVER_2')
		);
	}

	/**
	 * @param array $params
	 * @return array|string
	 */
	protected function actionClearCounterPanel(array $params = [])
	{
		$name = (isset($params['NAME']) && $params['NAME'] !== ''
			? $params['NAME']
			: Loc::getMessage('IBLOCK_GRID_PANEL_ACTION_CLEAR_COUNTER')
		);

		$params['APPLY_BUTTON_ID'] = 'clear_counter_confirm';
		$params['DEFAULT_CONFIRM_MESSAGE'] = Loc::getMessage('IBLOCK_GRID_PANEL_ACTION_CLEAR_COUNTER_CONFIRM');

		if ($this->isUiGrid())
		{
			return [
				'name' => $name,
				'type' => 'multicontrol',
				'action' => [
					[
						'ACTION' => Main\Grid\Panel\Actions::RESET_CONTROLS
					],
					[
						'ACTION' => Main\Grid\Panel\Actions::CREATE,
						'DATA' => [$this->getApplyButtonWithConfirm($params)]
					]
				]
			];
		}
		else
		{
			return $name;
		}
	}

	/**
	 * @param array $params
	 * @return array|string
	 */
	public function actionCodeTranslitPanel(array $params = [])
	{
		$name = (isset($params['NAME']) && $params['NAME'] !== ''
			? $params['NAME']
			: Loc::getMessage('IBLOCK_GRID_PANEL_ACTION_CODE_TRANSLITERATION_MSGVER_1')
		);

		$params['APPLY_BUTTON_ID'] = 'code_translit_confirm';
		$params['DEFAULT_CONFIRM_MESSAGE'] = Loc::getMessage('IBLOCK_GRID_PANEL_ACTION_CODE_TRANSLITERATION_CONFIRM');

		if ($this->isUiGrid())
		{
			return [
				'name' => $name,
				'type' => 'multicontrol',
				'action' => [
					[
						'ACTION' => Main\Grid\Panel\Actions::RESET_CONTROLS,
					],
					[
						'ACTION' => Main\Grid\Panel\Actions::CREATE,
						'DATA' => [$this->getApplyButtonWithConfirm($params)],
					],
				],
			];
		}
		else
		{
			return $name;
		}
	}

	/**
	 * @param array $params
	 * @return array|null
	 */
	protected function actionAdjustSectionPanel(array $params = []): ?array
	{
		if (!$this->isUiGrid())
		{
			return null;
		}
		if ($this->iblockConfig['SECTIONS'] != 'Y')
		{
			return null;
		}
		if (!isset($params['NAME']) || $params['NAME'] == '')
		{
			$params['NAME'] = Loc::getMessage('IBLOCK_GRID_PANEL_ACTION_ADJUST_SECTION');
		}

		$params['APPLY_BUTTON_ID'] = 'send_adjust_list';
		if ($this->iblockConfig['SECTION_CHOOSER'] == Iblock\IblockTable::SECTION_CHOOSER_PATH)
		{
			$params['SECTION_LIST_ID'] = 'set_sections';
			return $this->getAdjustSectionList($params);
		}
		else
		{
			$params['SECTION_LIST_ID'] = 'set_sections';
			return $this->getAdjustSectionList($params);
		}
	}

	/**
	 * @return array|null
	 */
	protected function actionAdjustSectionRequest(): ?array
	{
		$sectionId = $this->request->get('section_to_move');
		return (is_string($sectionId) ? ['SECTION_ID' => $sectionId] : null);
	}

	/**
	 * @param array $params
	 * @return array|null
	 */
	protected function actionAddSectionPanel(array $params = []): ?array
	{
		if (!$this->isUiGrid())
		{
			return null;
		}
		if ($this->iblockConfig['SECTIONS'] != 'Y')
		{
			return null;
		}
		if (!isset($params['NAME']) || $params['NAME'] === '')
		{
			$params['NAME'] = Loc::getMessage('IBLOCK_GRID_PANEL_ACTION_ADD_SECTION');
		}

		$params['APPLY_BUTTON_ID'] = 'send_add_list';
		if ($this->iblockConfig['SECTION_CHOOSER'] == Iblock\IblockTable::SECTION_CHOOSER_PATH)
		{
			$params['SECTION_LIST_ID'] = 'additional_sections';
			return $this->getAddSectionList($params);
		}
		else
		{
			$params['SECTION_LIST_ID'] = 'additional_sections';
			return $this->getAddSectionList($params);
		}
	}

	/**
	 * @return array|null
	 */
	protected function actionAddSectionRequest(): ?array
	{
		$sectionId = $this->request->get('section_to_move');

		return (is_string($sectionId) ? ['SECTION_ID' => $sectionId] : null);
	}

	/**
	 * @param array $params
	 * @return string
	 */
	protected function actionElementUnlockPanel(array $params = []): string
	{
		return (isset($params['NAME']) && $params['NAME'] !== ''
			? $params['NAME']
			: Loc::getMessage('IBLOCK_GRID_PANEL_ACTION_ELEMENT_UNLOCK')
		);
	}

	/**
	 * @param array $params
	 * @return string
	 */
	protected function actionElementLockPanel(array $params = []): string
	{
		return (isset($params['NAME']) && $params['NAME'] !== ''
			? $params['NAME']
			: Loc::getMessage('IBLOCK_GRID_PANEL_ACTION_ELEMENT_LOCK')
		);
	}

	/**
	 * @param array $params
	 * @return array|null
	 */
	protected function actionElementWorkflowStatusPanel(array $params = []): ?array
	{
		if (!Loader::includeModule('workflow'))
		{
			return null;
		}

		$name = (isset($params['NAME']) && $params['NAME'] !== ''
			? $params['NAME']
			: Loc::getMessage('IBLOCK_GRID_PANEL_ACTION_ELEMENT_WORKFLOW_STATUS')
		);

		$statusList = [];
		$iterator = \CWorkflowStatus::getDropDownList('N', 'desc');
		while ($row = $iterator->Fetch())
		{
			$statusList[] = [
				'NAME' => $row['REFERENCE'],
				'VALUE' => $row['REFERENCE_ID'],
			];
		}
		unset($row, $iterator);
		if (empty($statusList))
		{
			return null;
		}

		$params['APPLY_BUTTON_ID'] = 'send_workflow_status';
		$data = [];
		$data[] = [
			'TYPE' => Main\Grid\Panel\Types::DROPDOWN,
			'ID' => $this->getElementId('workflow_status'),
			'NAME' => 'wf_status_id',
			'ITEMS' => $statusList,
		];
		if ($this->isUiGrid())
		{
			$data[] = $this->getApplyButton($params);
		}

		return [
			'name' => $name,
			'type' => 'multicontrol',
			'action' => [
				[
					'ACTION' => Main\Grid\Panel\Actions::RESET_CONTROLS,
				],
				[
					'ACTION' => Main\Grid\Panel\Actions::CREATE,
					'DATA' => $data,
				],
			],
		];
	}

	/**
	 * @return array|null
	 */
	protected function actionElementWorkflowStatusRequest(): ?array
	{
		$result = $this->request->get('wf_status_id');

		return (is_string($result) ? ['WF_STATUS_ID' => $result] : null);
	}
}
