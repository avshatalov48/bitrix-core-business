<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class CBaseWizardStep extends CWizardStep
{
	var $arNode;
	var $nodeDB;
	var $arMaster;

	function InitStep()
	{
		global $DB;
		$wizard =& $this->GetWizard();
		$node_id = intval($wizard->GetVar('node_id'));

		if(!CModule::IncludeModule('cluster'))
			$this->SetError(GetMessage('CLUWIZ_NO_MODULE_ERROR'));
		elseif($DB->type != "MYSQL")
			$this->SetError(GetMessage('CLUWIZ_DATABASE_NOT_SUPPORTED'));
		elseif($node_id <= 1)
			$this->SetError(GetMessage('CLUWIZ_NO_NODE_ERROR'));
		else
		{
			$this->arNode = CClusterDBNode::GetByID($node_id);
			if(!is_array($this->arNode))
			{
				$this->SetError(GetMessage('CLUWIZ_NO_NODE_ERROR'));
			}
			else
			{
				// $arNode["ROLE_ID"] == "SLAVE"
				// $arNode["STATUS"] == "READY"
				$this->nodeDB = CDatabase::GetDBNodeConnection($this->arNode["ID"], true, false);
				if(!is_object($this->nodeDB))
					$this->SetError(GetMessage('CLUWIZ_NO_CONN_ERROR'));
				else
				{
					$arGroup = CClusterGroup::GetArrayByID(intval($wizard->GetVar("group_id")));
					if(!$arGroup)
						$this->SetError(GetMessage('CLUWIZ_NO_GROUP_ERROR'));
					else
					{
						$cData = new CClusterDBNode;
						$rsData = $cData->GetList(array() ,array(
							"=ROLE_ID" => array("MAIN", "MASTER"),
							"=GROUP_ID" => $arGroup["ID"],
							"=STATUS" => "ONLINE",
						));
						$this->arMaster = $rsData->Fetch();
						if(!$this->arMaster)
							$this->SetError(GetMessage('CLUWIZ_NO_MASTER_ERROR'));
					}
				}
			}
		}
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

	function CloseSite()
	{
		COption::SetOptionString("main", "site_stopped", "Y");
		COption::SetOptionString("main", "check_agents", "N");
		COption::SetOptionString("main", "check_events", "N");
	}

	function OpenSite()
	{
		COption::SetOptionString("main", "site_stopped", "N");
		COption::SetOptionString("main", "check_agents", "Y");
		COption::SetOptionString("main", "check_events", "Y");
	}
}

//Move module to selected node
class Step2 extends CBaseWizardStep
{
	var $nodeDB;
	function InitStep()
	{
		parent::InitStep();
		$this->SetTitle(GetMessage('CLUWIZ_STEP2_TITLE'));
		$this->SetStepID("step2");
		$this->SetCancelStep("cancel");
	}

	function ShowStepNoError()
	{
		global $APPLICATION;
		$wizard =& $this->GetWizard();
		$path = $wizard->package->path;

		$arTablesToDelete = array();
		$rsTables = $this->nodeDB->Query("show tables");
		while($arTable = $rsTables->Fetch())
			$arTablesToDelete[] = $arTable["Tables_in_".$this->nodeDB->DBName];

		if(empty($arTablesToDelete))
		{
			$this->content .= GetMessage("CLUWIZ_STEP2_NO_TABLES");
			$this->SetNextStep("step4");
		}
		else
		{
			$wizard->SetVar('action', '');
			$this->content .= GetMessage("CLUWIZ_STEP2_TABLES_EXIST");
			$this->content .= '<br /><a style="text-decoration:none;border-bottom:1px dashed #2775C7;" onclick="if(document.getElementById(\'tables\').style.display==\'block\'){document.getElementById(\'tables\').style.display=\'none\';}else{document.getElementById(\'tables\').style.display=\'block\';}">'.GetMessage("CLUWIZ_STEP2_TABLES_LIST").'</a>';
			$this->content .= '<div id="tables" style="display:none">'.implode('<br />', $arTablesToDelete).'</div>';
			$this->content .= '<br /><br />'.$this->ShowCheckboxField('action', 'delete', array(
				'id' => 'action',
				'onclick' => 'if(this.checked){EnableButton();}else{DisableButton();}',
			)).'<label for="action">'.GetMessage("CLUWIZ_STEP2_DELETE_TABLES", array("#database#" => $this->arNode["NAME"])).'</label>';

			CJSCore::Init(array("ajax"));
			$APPLICATION->AddHeadScript($path.'/js/import.js');

			$this->content .= '
				<script type="text/javascript">
					var nextButtonID = "'.$wizard->GetNextButtonID().'";
					var formID = "'.$wizard->GetFormName().'";
					var path = "'.CUtil::JSEscape($path).'";
					var sessid = "'.bitrix_sessid().'";
					BX.ready(DisableButton);
				</script>
			';
			$this->SetNextStep("step3");
		}
		$this->content .= '<p style="color:red">'.GetMessage("CLUWIZ_STEP2_WARNING").'</p>';
	}

	function OnPostForm()
	{
		if($this->GetNextStepID() == "step4")
			$this->CloseSite();
	}
}

//Drop tables
class Step3 extends CBaseWizardStep
{
	function InitStep()
	{
		parent::InitStep();
		$this->SetTitle(GetMessage('CLUWIZ_STEP3_TITLE'));
		$this->SetPrevStep("step2");
		$this->SetStepID("step3");
		$this->SetNextStep("step4");
		$this->SetCancelStep("cancel");
	}

	function ShowStepNoError()
	{
		global $APPLICATION;
		$wizard =& $this->GetWizard();
		$path = $wizard->package->path;

		CJSCore::Init(array("ajax"));
		$APPLICATION->AddHeadScript($path.'/js/import.js');

		$this->content = '';
		$this->content .= '<div style="padding: 20px;">';
		$this->content .= '<div id="output">'.GetMessage("CLUWIZ_INIT").'<br /></div>';
		$this->content .= '</div>';
		$this->content .= '
			<script type="text/javascript">
				var nextButtonID = "'.$wizard->GetNextButtonID().'";
				var formID = "'.$wizard->GetFormName().'";
				var LANG = \''.LANG.'\';
				var node_id = "'.CUtil::JSEscape($this->arNode["ID"]).'";
				var path = "'.CUtil::JSEscape($path).'";
				var sessid = "'.bitrix_sessid().'";
				BX.ready(DisableButton);
				BX.ready(DropTables);
			</script>
		';
	}

	function OnPostForm()
	{
		$this->CloseSite();
	}
}

//Datamove
class Step4 extends CBaseWizardStep
{
	protected $location = '';

	function InitStep()
	{
		parent::InitStep();
		$this->SetTitle(GetMessage('CLUWIZ_STEP4_TITLE'));
		$this->SetStepID("step4");
		$this->SetNextStep("step4");
		$this->SetNextCaption(GetMessage('CLUWIZ_FINALSTEP_BUTTONTITLE'));
	}

	function OnPostForm()
	{
		$wizard =& $this->GetWizard();
		$group_id = intval($wizard->GetVar("group_id"));

		CClusterSlave::SetOnLine($this->arNode["ID"], $this->arMaster["ID"]);
		$this->OpenSite();
		$this->location = '/bitrix/admin/cluster_slave_list.php?lang='.LANGUAGE_ID.'&group_id='.$group_id;
	}

	function ShowStepNoError()
	{
		global $APPLICATION;
		$wizard =& $this->GetWizard();
		$path = $wizard->package->path;

		if ($this->location)
		{
			$this->content = '<script>top.window.location = \''.CUtil::JSEscape($this->location).'\';</script>';
		}
		else
		{
			CJSCore::Init(array("ajax"));
			$APPLICATION->AddHeadScript($path.'/js/import.js');

			$this->content = '';
			$this->content .= '<div style="padding: 20px;">';
			$this->content .= '<div id="output">'.GetMessage("CLUWIZ_INIT").'<br /></div>';
			$this->content .= '</div>';
			$this->content .= '
				<script type="text/javascript">
					var nextButtonID = "'.$wizard->GetNextButtonID().'";
					var formID = "'.$wizard->GetFormName().'";
					var LANG = \''.LANG.'\';
					var node_id = "'.CUtil::JSEscape($this->arNode["ID"]).'";
					var path = "'.CUtil::JSEscape($path).'";
					var sessid = "'.bitrix_sessid().'";
					BX.ready(DisableButton);
					BX.ready(MoveTables);
				</script>
			';
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

	function ShowStepNoError()
	{
		$this->OpenSite();
		$this->content = GetMessage('CLUWIZ_CANCELSTEP_CONTENT');
	}
}
?>