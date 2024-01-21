<?php
IncludeModuleLangFile(__FILE__);

/**
 * Formats float number according to flags.
 *
 * @param float $num Number value to be formatted.
 * @param int $dec How many digits after decimal point.
 * @param int $mode Output mode.
 *
 * @return string
**/
function perfmon_NumberFormat($num, $dec = 2, $mode = 0)
{
	switch ($mode)
	{
	case 1:
		$str = number_format($num, $dec, '.', '');
		break;
	case 2:
		$str = number_format($num, $dec, '.', ' ');
		$str = str_replace(' ', '<span></span>', $str);
		$str = '<span class="perfmon_number">' . $str . '</span>';
		break;
	default:
		if (isset($_REQUEST['mode']) && $_REQUEST['mode'] == 'excel')
		{
			$str = perfmon_NumberFormat($num, $dec, 1);
		}
		else
		{
			$str = perfmon_NumberFormat($num, $dec, 2);
		}
		break;
	}
	return $str;
}

class CAdminListColumn
{
	public $id = '';
	public $info = [];

	public function __construct($id, $info)
	{
		$this->id = $id;
		$this->info = $info;
	}

	public function getRowView($arRes)
	{
		return false;
	}

	public function getRowEdit($arRes)
	{
		return false;
	}

	public function getFilterInput()
	{
		return '<input type="text" name="' . $this->info['filter'] . '" size="47" value="' . htmlspecialcharsbx($GLOBALS[$this->info['filter']]) . '">';
	}
}

class CAdminListColumnList extends CAdminListColumn
{
	public $list = [];

	public function __construct($id, $info, array $list = [])
	{
		parent::__construct($id, $info);
		$this->list = $list;
	}

	public function getRowView($arRes)
	{
		$value = $arRes[$this->id];
		return $this->list[$value];
	}

	public function getRowEdit($arRes)
	{
		return false;
	}

	public function getFilterInput()
	{
		$arr = [
			'reference' => [],
			'reference_id' => [],
		];
		foreach ($this->list as $key => $value)
		{
			$arr['reference'][] = $value;
			$arr['reference_id'][] = $key;
		}
		return SelectBoxFromArray($this->info['filter'], $arr, htmlspecialcharsbx($GLOBALS[$this->info['filter']]), GetMessage('MAIN_ALL'));
	}
}

class CAdminListColumnNumber extends CAdminListColumn
{
	public $precision = 0;

	public function __construct($id, $info, $precision)
	{
		$info['align'] = 'right';
		parent::__construct($id, $info);
		$this->precision = $precision;
	}

	public function getRowView($arRes)
	{
		if (isset($_REQUEST['mode']) && $_REQUEST['mode'] == 'excel')
		{
			return number_format($arRes[$this->id], $this->precision, '.', '');
		}
		else
		{
			return str_replace(' ', '&nbsp;', number_format($arRes[$this->id], $this->precision, '.', ' '));
		}
	}
}

class CAdminListPage
{
	protected $pageTitle = '';
	protected $sTableID = '';
	protected $navLabel = '';
	protected $sort = null;
	protected $list = null;
	protected $data = null;
	protected $columns = [];

	/**
	 * @param string $pageTitle
	 * @param string $sTableID
	 * @param bool|array[] $arSort
	 * @param string $navLabel
	 */
	public function __construct($pageTitle, $sTableID, $arSort = false, $navLabel = '')
	{
		$this->pageTitle = $pageTitle;
		$this->sTableID = $sTableID;
		$this->navLabel = $navLabel;
		if (is_array($arSort))
		{
			$this->sort = new CAdminSorting($this->sTableID, key($arSort), current($arSort));
		}
		else
		{
			$this->sort = false;
		}
		$this->list = new CAdminList($this->sTableID, $this->sort);
	}

	public function addColumn(CAdminListColumn $column)
	{
		$this->columns[$column->id] = $column;
	}

	public function initFilter()
	{
		$FilterArr = [
			'find',
			'find_type',
		];
		foreach ($this->columns as $column)
		{
			if (isset($column->info['filter']))
			{
				$FilterArr[] = $column->info['filter'];
			}
		}
		$this->list->InitFilter($FilterArr);
	}

	public function getFilter()
	{
		global $find, $find_type;

		$arFilter = [];
		foreach ($this->columns as $column)
		{
			if (
				isset($column->info['filter'])
				&& isset($column->info['filter_key'])
			)
			{
				if (
					isset($column->info['find_type'])
					&& $find !== ''
					&& $find_type === $column->info['find_type']
				)
				{
					$arFilter[$column->info['filter_key']] = $find;
				}
				elseif (
					isset($GLOBALS[$column->info['filter']])
				)
				{
					$arFilter[$column->info['filter_key']] = $GLOBALS[$column->info['filter']];
				}
			}
		}

		foreach ($arFilter as $key => $value)
		{
			if ((string)$value === '')
			{
				unset($arFilter[$key]);
			}
		}

		return $arFilter;
	}

	public function getHeaders()
	{
		$arHeaders = [];
		foreach ($this->columns as $column)
		{
			$arHeaders[] = [
				'id' => $column->id,
				'content' => $column->info['content'],
				'sort' => $column->info['sort'],
				'align' => $column->info['align'] ?? '',
				'default' => $column->info['default'] ?? '',
			];
		}
		return $arHeaders;
	}

	public function getSelectedFields()
	{
		$arSelectedFields = $this->list->GetVisibleHeaderColumns();
		if (!is_array($arSelectedFields) || empty($arSelectedFields))
		{
			$arSelectedFields = [];
			foreach ($this->columns as $column)
			{
				if ($column->info['default'])
				{
					$arSelectedFields[] = $column->id;
				}
			}
		}
		return $arSelectedFields;
	}

	public function getDataSource($arOrder, $arFilter, $arSelect)
	{
		$rsData = new CDBResult;
		$rsData->InitFromArray([]);
		return $rsData;
	}

	public function getOrder()
	{
		global $by, $order;
		return [$by => $order];
	}

	public function getFooter()
	{
		return [];
	}

	public function getContextMenu()
	{
		return [];
	}

	public function displayFilter()
	{
		global $APPLICATION, $find, $find_type;

		$findFilter = [
			'reference' => [],
			'reference_id' => [],
		];
		$listFilter = [];
		foreach ($this->columns as $column)
		{
			if (isset($column->info['filter']))
			{
				$listFilter[$column->info['filter']] = $column->info['content'];
				if (isset($column->info['find_type']))
				{
					$findFilter['reference'][] = $column->info['content'];
					$findFilter['reference_id'][] = $column->info['find_type'];
				}
			}
		}

		if (!empty($listFilter))
		{
			$this->filter = new CAdminFilter($this->sTableID . '_filter', $listFilter);
			?>
			<form name="find_form" method="get" action="<?php echo $APPLICATION->GetCurPage(); ?>">
				<?php $this->filter->Begin(); ?>
				<?php if (!empty($findFilter['reference'])): ?>
					<tr>
						<td><b><?=GetMessage('PERFMON_HIT_FIND')?>:</b></td>
						<td><input
							type="text" size="25" name="find"
							value="<?php echo htmlspecialcharsbx($find) ?>"><?php echo SelectBoxFromArray('find_type', $findFilter, $find_type, '', ''); ?>
						</td>
					</tr>
				<?php endif; ?>
				<?php
				foreach ($this->columns as $column)
				{
					if (isset($column->info['filter']))
					{
						?>
						<tr>
						<td><?php echo $column->info['content'] ?></td>
						<td><?php echo $column->getFilterInput() ?></td>
						</tr><?php
					}
				}
				$this->filter->Buttons([
					'table_id' => $this->sTableID,
					'url' => $APPLICATION->GetCurPage(),
					'form' => 'find_form',
				]);
				$this->filter->End();
				?>
			</form>
		<?php
		}
	}

	public function show()
	{
		global $APPLICATION;

		$this->initFilter();
		$this->list->AddHeaders($this->getHeaders());
		$select = $this->getSelectedFields();

		$dataSource = $this->getDataSource($this->getOrder(), $this->getFilter(), $select);
		$this->data = new CAdminResult($dataSource, $this->sTableID);
		$this->data->NavStart();
		$this->list->NavText($this->data->GetNavPrint($this->navLabel));

		$i = 0;
		while ($arRes = $this->data->GetNext())
		{
			$row = $this->list->AddRow(++$i, $arRes);
			foreach ($select as $fieldId)
			{
				/** @var CAdminListColumn $column */
				$column = $this->columns[$fieldId] ?? '';
				if ($column)
				{
					$view = $column->getRowView($arRes);
					if ($view !== false)
					{
						$row->AddViewField($column->id, $view);
					}
					$edit = $column->getRowEdit($arRes);
					if ($edit !== false)
					{
						$row->AddEditField($column->id, $edit);
					}
				}
			}
		}

		$this->list->AddFooter($this->getFooter());
		$this->list->AddAdminContextMenu($this->getContextMenu());
		$this->list->CheckListMode();
		$APPLICATION->SetTitle($this->pageTitle);
		global /** @noinspection PhpUnusedLocalVariableInspection */
		$adminPage, $adminMenu, $adminChain, $USER;
		require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';
		$this->displayFilter();
		$this->list->DisplayList();
	}
}
