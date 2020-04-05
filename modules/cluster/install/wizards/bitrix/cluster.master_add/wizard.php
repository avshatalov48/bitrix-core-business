<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class CBaseWizardStep extends CWizardStep
{
	function InitStep()
	{
		global $DB;
		$wizard = $this->GetWizard();

		if(!CModule::IncludeModule('cluster'))
			$this->SetError(GetMessage('CLUWIZ_NO_MODULE_ERROR'));
		elseif($DB->type != "MYSQL")
			$this->SetError(GetMessage('CLUWIZ_DATABASE_NOT_SUPPORTED'));
		elseif(!CClusterGroup::GetArrayByID(intval($wizard->GetVar("group_id"))))
			$this->SetError(GetMessage('CLUWIZ_NO_GROUP_ERROR'));

		if(preg_match("/^(.+):(\\d+)$/", $GLOBALS["DB"]->DBHost, $match))
		{
			$wizard->SetDefaultVar("master_host", $match[1]);
			$wizard->SetDefaultVar("master_port", $match[2]);
		}
		else
		{
			$wizard->SetDefaultVar("master_host", '');
			$wizard->SetDefaultVar("master_port", '');
		}
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
	}

	function ShowStepNoError()
	{
		$this->content = GetMessage('CLUWIZ_STEP1_CONTENT');
		$this->content .= "<br />";

		$obCheck = new CClusterDBNodeCheck;
		$arCheckList = array_merge(
			$obCheck->MainNodeCommon(CClusterDBNode::GetByID(1)),
			$obCheck->MainNodeForReplication(CClusterDBNode::GetByID(1)),
			$obCheck->MainNodeForSlave()
		);

		$this->ShowCheckList($arCheckList);

		if($this->CheckListHasNoError($arCheckList))
			$this->SetNextStep("step2");
		else
			$this->SetNextStep("step1");
	}
}

//Ask for connection credentials
class Step2 extends CBaseWizardStep
{
	function InitStep()
	{
		parent::InitStep();
		$this->SetTitle(GetMessage('CLUWIZ_STEP2_TITLE'));
		$this->SetPrevStep("step1");
		$this->SetStepID("step2");
		$this->SetNextStep("step4");
		$this->SetCancelStep("cancel");
	}

	function ShowStepNoError()
	{
		$wizard = $this->GetWizard();
		$this->content = '
		<table cellpadding="1" cellspacing="0" border="0" width="100%">
			<tr valign="top">
				<td width="40%" align="right">'.GetMessage("CLUWIZ_STEP2_DB_HOST").':</td>
				<td width="60%">'.$this->ShowInputField('text', 'db_host', array(
					'size' => 35,
					'maxsize' => 50,
				)).'</td>
			</tr>
			<tr valign="top">
				<td align="right">'.GetMessage("CLUWIZ_STEP2_DB_NAME").':</td>
				<td>'.htmlspecialcharsbx($GLOBALS["DB"]->DBName).'</td>
			</tr>
			<tr valign="top">
				<td colspan="2" align="right"><span style="font-size:11px">'.GetMessage("CLUWIZ_STEP2_DB_NAME_HINT").'</span></td>
			</tr>
			<tr valign="top">
				<td align="right">'.GetMessage("CLUWIZ_STEP2_DB_LOGIN").':</td>
				<td>'.$this->ShowInputField('text', 'db_login', array(
					'size' => 25,
					'maxsize' => 50,
				)).'</td>
			</tr>
			<tr valign="top">
				<td align="right">'.GetMessage("CLUWIZ_STEP2_DB_PASSWORD").':</td>
				<td>'.$this->ShowInputField('password', 'db_password', array(
					'size' => 25,
					'maxsize' => 50,
					'autocomplete' => 'off',
				)).'</td>
			</tr>
		</table>
		';
		if(!$wizard->GetDefaultVar("master_host") || !$wizard->GetDefaultVar("master_port"))
		{
			$this->content .= '
			<br />'.GetMessage("CLUWIZ_STEP2_MASTER_CONN").'
			<table cellpadding="2" cellspacing="0" border="0" width="100%">
				<tr valign="top">
					<td width="40%" align="right">'.GetMessage("CLUWIZ_STEP2_MASTER_HOST").':</td>
					<td width="60%">'.$this->ShowInputField('text', 'master_host', array(
						'size' => 25,
						'maxsize' => 50,
					)).'</td>
				</tr>
				<tr valign="top">
					<td width="40%" align="right">'.GetMessage("CLUWIZ_STEP2_MASTER_PORT").':</td>
					<td width="60%">'.$this->ShowInputField('text', 'master_port', array(
						'size' => 6,
						'maxsize' => 50,
					)).'</td>
				</tr>
			</table>
			';
		}
	}
}

class Step4 extends CBaseWizardStep
{
	function InitStep()
	{
		parent::InitStep();
		$this->SetTitle(GetMessage('CLUWIZ_STEP4_TITLE'));
		$this->SetPrevStep("step2");
		$this->SetStepID("step4");
		$this->SetCancelStep("cancel");
	}

	function ShowStepNoError()
	{
		$wizard = $this->GetWizard();
		$wizard->SetVar('status', '');

		$obCheck = new CClusterDBNodeCheck;
		$IsReplicationRunning = $obCheck->SlaveNodeIsReplicationRunning(
			$wizard->GetVar("db_host"),
			$GLOBALS["DB"]->DBName,
			$wizard->GetVar("db_login"),
			$wizard->GetVar("db_password"),
			$wizard->GetVar("master_host", true),
			$wizard->GetVar("master_port", true)
		);

		if(is_object($IsReplicationRunning))
		{
			$this->content .= '<p>'.GetMessage("CLUWIZ_STEP4_SLAVE_IS_RUNNING").'</p>';

			$arCheckList = array_merge(
				$obCheck->SlaveNodeCommon($IsReplicationRunning),
				$obCheck->SlaveNodeForReplication($IsReplicationRunning)
			);
			$this->ShowCheckList($arCheckList);
			$bNextStep = $this->CheckListHasNoError($arCheckList);
			if($bNextStep)
				$wizard->SetVar('status', 'online');
		}
		elseif($IsReplicationRunning === false)
		{
			$DB = $obCheck->SlaveNodeConnection(
				$wizard->GetVar("db_host"),
				$GLOBALS["DB"]->DBName,
				$wizard->GetVar("db_login"),
				$wizard->GetVar("db_password"),
				$wizard->GetVar("master_host", true),
				$wizard->GetVar("master_port", true)
			);
			if(is_object($DB))
			{
				$arCheckList = array_merge(
					$obCheck->SlaveNodeCommon($DB),
					$obCheck->SlaveNodeForReplication($DB),
					$obCheck->SlaveNodeForMaster($DB)
				);
				$this->ShowCheckList($arCheckList);
				$bNextStep = $this->CheckListHasNoError($arCheckList);
			}
			else
			{
				$this->content .= '<p class="cluwiz_err">'.$DB.'</p><p>'.GetMessage("CLUWIZ_STEP4_CONN_ERROR").'</p>';
				$bNextStep = false;
			}
		}
		else
		{
			$this->content .= '<p class="cluwiz_err">'.$IsReplicationRunning.'</p><p>'.GetMessage("CLUWIZ_STEP4_CONN_ERROR").'</p>';
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
		$group_id = intval($wizard->GetVar("group_id"));

		if($wizard->IsNextButtonClick())
		{
			$obNode = new CClusterDBNode;
			$node_id = $obNode->Add(array(
				"ACTIVE" => "Y",
				"ROLE_ID" => "MASTER",
				"GROUP_ID" => $group_id,
				"NAME" => $wizard->GetVar("node_name"),
				"DESCRIPTION" => false,
				"DB_HOST" => $wizard->GetVar("db_host"),
				"DB_NAME" => $GLOBALS["DB"]->DBName,
				"DB_LOGIN" => $wizard->GetVar("db_login"),
				"DB_PASSWORD" => $wizard->GetVar("db_password"),
				"MASTER_ID" => 1,
				"MASTER_HOST" => $wizard->GetVar("master_host", true),
				"MASTER_PORT" => $wizard->GetVar("master_port", true),
				"SERVER_ID" => false,
				"STATUS" => $wizard->GetVar("status") === "online"? "ONLINE": "READY",
				"SELECTABLE" => "Y",
				"WEIGHT" => 100,
			));
			$this->location = '/bitrix/admin/cluster_slave_list.php?lang='.LANGUAGE_ID.'&group_id='.$group_id;
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