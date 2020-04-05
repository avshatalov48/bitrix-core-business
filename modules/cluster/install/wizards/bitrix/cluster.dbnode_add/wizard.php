<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class CBaseWizardStep extends CWizardStep
{
	function InitStep()
	{
		global $DB;

		if(!CModule::IncludeModule('cluster'))
			$this->SetError(GetMessage('CLUWIZ_NO_MODULE_ERROR'));
		elseif($DB->type != "MYSQL")
			$this->SetError(GetMessage('CLUWIZ_DATABASE_NOT_SUPPORTED'));
	}

	function ShowCheckList($arList)
	{
		if(count($arList) > 0)
		{
			$this->content .= '<ul>';
			foreach($arList as $rec)
			{
				if($rec["IS_OK"] == CClusterDBNodeCheck::OK)
					$this->content .= '<li class="cluwiz_okli">'.$rec["MESSAGE"].' ... <span class="cluwiz_ok">'.GetMessage("CLUWIZ_CHEKED").'</span></li>';
				elseif($rec["IS_OK"] == CClusterDBNodeCheck::WARNING)
					$this->content .= '<li class="cluwiz_erli">'.$rec["MESSAGE"].' ... <span class="cluwiz_ok">'.GetMessage("CLUWIZ_CHEKED").'</span></li>';
				else
					$this->content .= '<li class="cluwiz_erli">'.$rec["MESSAGE"].'<p class="cluwiz_err">'.$rec["WIZ_REC"].'</p></li>';
			}
			$this->content .= '</ul>';
		}
	}

	function CheckListHasNoError($arList)
	{
		foreach($arList as $rec)
			if($rec["IS_OK"] == CClusterDBNodeCheck::ERROR)
				return false;
		return true;
	}

	function ShowStep()
	{
		if(count($this->GetErrors()) == 0)
		{
			$this->ShowStepNoError();
		}

		$this->content .= "<style>
			li.cluwiz_erli { list-style-image:url(/bitrix/themes/.default/images/lamp/red.gif) }
			li.cluwiz_okli { list-style-image:url(/bitrix/themes/.default/images/lamp/green.gif) }
			p.cluwiz_err { color:red }
			span.cluwiz_ok { color:green }
			</style>
		";
	}
}

//Check master DB parameters
class Step1 extends CBaseWizardStep
{
	function InitStep()
	{
		parent::InitStep();
		$this->SetTitle(GetMessage('CLUWIZ_STEP1_TITLE'));
		$this->SetStepID("step1");
		$this->SetCancelStep("cancel");

		$wizard = $this->GetWizard();
		$wizard->SetDefaultVars(array(
			"node_name" => "node",
		));
	}

	function ShowStepNoError()
	{
		$this->content = GetMessage('CLUWIZ_STEP1_CONTENT');
		$this->content .= "<br />";

		$obCheck = new CClusterDBNodeCheck;
		$arCheckList = $obCheck->MainNodeCommon(CClusterDBNode::GetByID(1));

		$this->ShowCheckList($arCheckList);

		if($this->CheckListHasNoError($arCheckList))
			$this->SetNextStep("step3");
		else
			$this->SetNextStep("step1");
	}
}

//Ask for connection credentials
class Step3 extends CBaseWizardStep
{
	function InitStep()
	{
		parent::InitStep();
		$this->SetTitle(GetMessage('CLUWIZ_STEP3_TITLE'));
		$this->SetPrevStep("step1");
		$this->SetStepID("step3");
		$this->SetNextStep("step4");
		$this->SetCancelStep("cancel");
	}

	function ShowStepNoError()
	{
		$this->content = '
		<table cellpadding="2" cellspacing="0" border="0" width="100%">
			<tr>
				<td width="40%" align="right">'.GetMessage("CLUWIZ_STEP3_DB_HOST").':</td>
				<td width="60%">'.$this->ShowInputField('text', 'db_host', array(
					'size' => 30,
					'maxsize' => 50,
				)).'</td>
			</tr>
			<tr>
				<td align="right">'.GetMessage("CLUWIZ_STEP3_DB_LOGIN").':</td>
				<td>'.$this->ShowInputField('text', 'db_login', array(
					'size' => 30,
					'maxsize' => 50,
				)).'</td>
			</tr>
			<tr>
				<td align="right">'.GetMessage("CLUWIZ_STEP3_DB_PASSWORD").':</td>
				<td>'.$this->ShowInputField('password', 'db_password', array(
					'size' => 30,
					'maxsize' => 50,
					'autocomplete' => 'off',
				)).'</td>
			</tr>
			<tr>
				<td align="right">'.GetMessage("CLUWIZ_STEP3_DB_NAME").':</td>
				<td>'.$this->ShowInputField('text', 'db_name', array(
					'size' => 30,
					'maxsize' => 50,
				)).'</td>
			</tr>
		</table>
		';
	}
}

//Check master parameters for replication
class Step4 extends CBaseWizardStep
{
	function InitStep()
	{
		parent::InitStep();
		$this->SetTitle(GetMessage('CLUWIZ_STEP4_TITLE'));
		$this->SetPrevStep("step3");
		$this->SetStepID("step4");
		$this->SetCancelStep("cancel");
	}

	function ShowStepNoError()
	{
		$wizard = $this->GetWizard();

		$obCheck = new CClusterDBNodeCheck;
		$DB = $obCheck->SlaveNodeConnection(
			$wizard->GetVar("db_host"),
			$wizard->GetVar("db_name"),
			$wizard->GetVar("db_login"),
			$wizard->GetVar("db_password")
		);
		if(is_object($DB))
		{
			$arCheckList = $obCheck->SlaveNodeCommon($DB);
			$this->ShowCheckList($arCheckList);
			$bNextStep = $this->CheckListHasNoError($arCheckList);
		}
		else
		{
			$this->content .= '<p class="cluwiz_err">'.$DB.'</p><p>'.GetMessage("CLUWIZ_STEP4_CONN_ERROR").'</p>';
			$bNextStep = false;
		}

		if($bNextStep)
			$this->SetNextStep("final");
		else
			$this->SetNextStep("step4");
	}
}

class FinalStep extends CBaseWizardStep
{
	protected $location = '';

	function InitStep()
	{
		parent::InitStep();
		$this->SetTitle(GetMessage('CLUWIZ_FINALSTEP_TITLE'));
		$this->SetPrevStep("step4");
		$this->SetStepID("final");
		$this->SetNextStep("final");
		$this->SetNextCaption(GetMessage('CLUWIZ_FINALSTEP_BUTTONTITLE'));
	}

	function ShowStepNoError()
	{
		if ($this->location)
		{
			$this->content = '<script>top.window.location = \''.CUtil::JSEscape($this->location).'\';</script>';
		}
		else
		{
			$this->content = '
			<table cellpadding="2" cellspacing="0" border="0" width="100%">
				<tr>
					<td width="40%" align="right">'.GetMessage("CLUWIZ_FINALSTEP_NAME").':</td>
					<td width="60%">'.$this->ShowInputField('text', 'node_name', array(
						'size' => 40,
						'maxsize' => 50,
					)).'</td>
				</tr>
			</table>
			';
		}
	}

	function OnPostForm()
	{
		$wizard =& $this->GetWizard();
		if($wizard->IsNextButtonClick())
		{
			$obNode = new CClusterDBNode;
			$obNode->Add(array(
				"ACTIVE" => "Y",
				"ROLE_ID" => "MODULE",
				"NAME" => $wizard->GetVar("node_name"),
				"DESCRIPTION" => false,
				"DB_HOST" => $wizard->GetVar("db_host"),
				"DB_NAME" => $wizard->GetVar("db_name"),
				"DB_LOGIN" => $wizard->GetVar("db_login"),
				"DB_PASSWORD" => $wizard->GetVar("db_password"),
				"MASTER_ID" => false,
				"SERVER_ID" => false,
				"STATUS" => "READY",
			));

			$this->location = '/bitrix/admin/cluster_dbnode_list.php?lang='.LANGUAGE_ID;
		}
	}
}

class CancelStep extends CBaseWizardStep
{
	function InitStep()
	{
		parent::InitStep();
		$this->SetTitle(GetMessage('CLUWIZ_CANCELSTEP_TITLE'));
		$this->SetStepID("cancel");
		$this->SetCancelStep("cancel");
		$this->SetCancelCaption(GetMessage('CLUWIZ_CANCELSTEP_BUTTONTITLE'));
	}

	function ShowStep()
	{
		$this->content = GetMessage('CLUWIZ_CANCELSTEP_CONTENT');
	}
}
?>