<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php');	// first system's prolog
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/learning/prolog.php');	// init module
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/learning/include.php');	// module's prolog

// load language file for our module
IncludeModuleLangFile(__FILE__);

// prepare buffer to be showed
$html  = '';
$title = GetMessage("LEARNING_ADMIN_TITLE");	// by default, but maybe overrided below

$wasError         = false;
$needShowAuthForm = false;
$strCAdminMessage = false;

try
{
	$oRE = new CLearnRenderAdminExceptionsList();

	$oRE->EnsureReadRights()	// broke execution flow and show auth form, if not enough access level
		->Init();				// init filter, list

	// save inline edited items
	if ($oRE->IsNeedSaveInlineEditedItems())
		$oRE->SaveInlineEditedItems();

	// process group or single actions on list's item(s)
	if ($oRE->IsNeedProcessActionsOnList())
		$oRE->ProcessActionsOnList();

	if (isset($_REQUEST['return_url']) && (strlen($_REQUEST['return_url']) > 0) && check_bitrix_sessid())
		LocalRedirect($_REQUEST['return_url']);


	$oRE->FetchData()					// get data for list
		->BuildList()					// Build list
		->BuildListGroupActionsButton()	// Build group actions buttons in list
		->BuildListContextMenu()		// Build context menu in list
		->RenderInto($html);			// Render html into argument (if AJAX then terminate the script)
}
catch (Exception $e)
{
	$wasError = true;

	$strCAdminMessage = GetMessage('LEARNING_ERROR');

	$errmsg = $e->GetMessage();
	if (strlen($errmsg) > 0)
		$strCAdminMessage .= ' (' . $e->GetMessage() . ')';
}

$APPLICATION->SetTitle($title);

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php'); // second system's prolog

if ($wasError)
{
	$title = GetMessage('LEARNING_LESSONS');

	if ($needShowAuthForm)
	{
		$APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'), false);
	}
	else
	{
		if ($strCAdminMessage !== false)
			CAdminMessage::ShowMessage($strCAdminMessage);

		$aContext = array(
			array(
				'TEXT'  => GetMessage('LEARNING_BACK_TO_ADMIN'),
				'LINK'  => 'learn_index.php?lang=' . LANG,
				'TITLE' => GetMessage('LEARNING_BACK_TO_ADMIN')
			),
		);

		$context = new CAdminContextMenu($aContext);
		$context->Show();
	}
}
else
	echo $html;		// output

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php');	// system's epilog

// The end
exit();

class CLearnRenderAdminExceptionsList
{
	// This constants for special values on

	protected $oList, $oFilter;

// is it need?	protected $isInsideCourse = null;
	protected $tableID        = 'tbl_exceptions_list';
	protected $rsData;							// list of items

	public function __construct()
	{
		global $USER;

		if ( ! $USER->IsAdmin() )
			throw new Exception();

		// Removes all global variables with prefix "str_"
		ClearVars();
	}


	public function EnsureReadRights()
	{
		global $USER;

		if ( ! $USER->IsAdmin() )
			throw new Exception();

		return ($this);
	}


	public function Init()
	{
		$oSort = new CAdminSorting($this->tableID, 'DATE_REGISTERED', 'asc');	// sort initialization
		$this->oList = new CAdminList($this->tableID, $oSort);		// list initialization

		$arHeaders = array(
			array('id'    => 'DATE_REGISTERED', 
				'content' => 'DATE_REGISTERED', 
				'sort'    => 'DATE_REGISTERED', 
				'default' => true),

			array('id'    => 'CODE', 
				'content' => 'CODE',	
				'sort'    => 'CODE', 
				'default' => true),

			array('id'    => 'MESSAGE',
				'content' => 'MESSAGE', 
				'sort'    => 'MESSAGE', 
				'default' => true),

			array('id'    => 'FFILE', 
				'content' => 'FFILE',
				'sort'    => 'FFILE', 
				'default' => true),

			array('id'    => 'LINE', 
				'content' => 'LINE',
				'sort'    => 'LINE', 
				'default' => true),

			array('id'    => 'BACKTRACE', 
				'content' => 'BACKTRACE',
				'sort'    => 'BACKTRACE', 
				'default' => true)
			);

		// list's header
		$this->oList->AddHeaders($arHeaders);

		$this->oFilter = new CAdminFilter(
			$this->tableID . "_filter",
			array()
		);

		$arFilterFields = Array();

		// filter initialization (can puts data into global vars)
		$this->oList->InitFilter($arFilterFields);

		$this->arFilter = Array();

		return ($this);
	}

	public function IsNeedProcessActionsOnList()
	{
		if ($this->oList->GroupAction() === false)
			return (false);
		else
			return (true);
	}

	public function ProcessActionsOnList()
	{
		return ($this);		// nothing to do
	}


	public function FetchData()
	{
		global $DB;
		$CDBResult = $DB->Query("SELECT * FROM b_learn_exceptions_log ORDER BY DATE_REGISTERED DESC");

		$this->rsData = new CAdminResult($CDBResult, $this->tableID);

		// navigation setup
		$this->rsData->NavStart();
		$this->oList->NavText($this->rsData->GetNavPrint(''));

		return ($this);
	}

	public function BuildList()
	{
		global $USER;

		// list's footer
		$this->oList->AddFooter(
			array(
				array('title' => GetMessage('MAIN_ADMIN_LIST_SELECTED'), 
					'value' => $this->rsData->SelectedRowsCount()),
				array('counter' => true, 
					'title' => GetMessage('MAIN_ADMIN_LIST_CHECKED'), 'value' => '0')
			)
		);

		// building list
		while ($arRes = $this->rsData->NavNext(false))	// NavNext: don't extract fields to globals
		{
			ob_start();

			$arBacktrace = unserialize(base64_decode($arRes['BACKTRACE']));

			foreach ($arBacktrace as $arItem)
			{
				echo $arItem['file'] . ':' . $arItem['line'] . "\n";
				echo $arItem['class'] . '::' . $arItem['function'] . "\n";
				$argsCnt = 0;
				foreach ($arItem['args'] as $arArg)
				{
					$argsCnt++;
					$dots = '';
					if (strlen($arArg) > 100)
						$dots = '...';
					echo '[' . $argsCnt . ']: ' . substr(serialize($arArg), 0, 100) . $dots . "\n";
				}
				echo "\n";
			}

			$BACKTRACE = str_replace("\n", '<br>', ob_get_clean());
			$arRes['BACKTRACE'] = '';

			$row =& $this->oList->AddRow(
				$arRes['DATE_REGISTERED'], 
				$arRes
			);

			$row->AddViewField('BACKTRACE', $BACKTRACE);

			$arActions = array();

			$row->AddActions($arActions);
		}

		return ($this);
	}

	// Build group actions buttons in list
	public function BuildListGroupActionsButton()
	{
		$this->oList->AddGroupActionTable(array());

		return ($this);
	}

	public function BuildListContextMenu()
	{
		$aContext = array();

		$this->oList->AddAdminContextMenu($aContext);

		return ($this);
	}

	public function RenderInto (&$html)
	{
		// list mode check (if AJAX then terminate the script)
		$this->oList->CheckListMode();

		ob_start();
		$this->ShowFilter();
		$this->ShowList();
		$html = ob_get_clean();

		return ($this);
	}

	public function IsNeedSaveInlineEditedItems()
	{
		static $cache = -1;

		if ($cache === -1)
		{
			$cache = (boolean) ($this->oList->EditAction()
				&& isset ($_POST['FIELDS'])
				&& is_array($_POST['FIELDS'])
				&& count($_POST['FIELDS']) > 0
				);
		}

		return ($cache);
	}

	public function SaveInlineEditedItems()
	{
		if ( ! $this->IsNeedSaveInlineEditedItems() )
			return ($this);

		// nothing to do
		return ($this);
	}

	protected function ShowFilter()
	{
		global $APPLICATION;

		return ($this);
	}

	public function ShowList()
	{
		$this->oList->DisplayList();
	}
}
