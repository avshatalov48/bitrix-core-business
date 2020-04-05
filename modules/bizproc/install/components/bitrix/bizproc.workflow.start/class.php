<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main;

class BizprocWorkflowStart extends \CBitrixComponent
{
	public function onPrepareComponentParams($arParams)
	{
		$arParams["MODULE_ID"] = trim(empty($arParams["MODULE_ID"]) ? $_REQUEST["module_id"] : $arParams["MODULE_ID"]);
		$arParams["ENTITY"] = trim(empty($arParams["ENTITY"]) ? $_REQUEST["entity"] : $arParams["ENTITY"]);
		$arParams["DOCUMENT_TYPE"] = trim(empty($arParams["DOCUMENT_TYPE"]) ? $_REQUEST["document_type"] : $arParams["DOCUMENT_TYPE"]);
		$arParams["DOCUMENT_ID"] = trim(empty($arParams["DOCUMENT_ID"]) ? $_REQUEST["document_id"] : $arParams["DOCUMENT_ID"]);
		$arParams["TEMPLATE_ID"] = isset($arParams["TEMPLATE_ID"]) ? (int)$arParams["TEMPLATE_ID"] : (int)$_REQUEST["workflow_template_id"];
		$arParams["AUTO_EXECUTE_TYPE"] = isset($arParams["AUTO_EXECUTE_TYPE"]) ? (int)$arParams["AUTO_EXECUTE_TYPE"] : null;

		$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y");

		return $arParams;
	}

	public function executeComponent()
	{
		if (!Main\Loader::includeModule('bizproc'))
		{
			return false;
		}

		$this->arResult["DOCUMENT_ID"] = $this->arParams["DOCUMENT_ID"];
		$this->arResult["DOCUMENT_TYPE"] = $this->arParams["DOCUMENT_TYPE"];
		$this->arResult["back_url"] = trim($_REQUEST["back_url"]);

		$arError = array();
		if (strlen($this->arParams["MODULE_ID"]) <= 0)
			$arError[] = array(
				"id" => "empty_module_id",
				"text" => GetMessage("BPATT_NO_MODULE_ID"));
		if (strlen($this->arParams["ENTITY"]) <= 0)
			$arError[] = array(
				"id" => "empty_entity",
				"text" => GetMessage("BPABS_EMPTY_ENTITY"));
		if (strlen($this->arParams["DOCUMENT_TYPE"]) <= 0)
			$arError[] = array(
				"id" => "empty_document_type",
				"text" => GetMessage("BPABS_EMPTY_DOC_TYPE"));

		$this->arParams["DOCUMENT_TYPE"] = array($this->arParams["MODULE_ID"], $this->arParams["ENTITY"], $this->arParams["DOCUMENT_TYPE"]);

		if (strlen($this->arParams["DOCUMENT_ID"]) <= 0 && $this->arParams["AUTO_EXECUTE_TYPE"] === null)
			$arError[] = array(
				"id" => "empty_document_id",
				"text" => GetMessage("BPABS_EMPTY_DOC_ID"));

		$this->arParams["DOCUMENT_ID"] = array($this->arParams["MODULE_ID"], $this->arParams["ENTITY"], $this->arParams["DOCUMENT_ID"]);
		$this->arParams["USER_GROUPS"] = $GLOBALS["USER"]->GetUserGroupArray();

		if ($this->arParams["AUTO_EXECUTE_TYPE"] === null && !check_bitrix_sessid())
		{
			$arError[] = array(
				"id" => "access_denied",
				"text" => GetMessage("BPABS_NO_PERMS"));
		}

		if (method_exists($this->arParams["DOCUMENT_TYPE"][1], "GetUserGroups"))
		{
			$this->arParams["USER_GROUPS"] = call_user_func_array(
				array($this->arParams["DOCUMENT_TYPE"][1], "GetUserGroups"),
				array($this->arParams["DOCUMENT_TYPE"], $this->arParams["DOCUMENT_ID"], $GLOBALS["USER"]->GetID()));
		}

		if (empty($arError) && $this->arParams["AUTO_EXECUTE_TYPE"] !== null)
		{
			$this->autoStartParametersAction($this->arParams["AUTO_EXECUTE_TYPE"]);
			return true;
		}

		if (empty($arError))
		{
			$arDocumentStates = CBPDocument::GetDocumentStates($this->arParams["DOCUMENT_TYPE"], $this->arParams["DOCUMENT_ID"]);

			if (!CBPDocument::CanUserOperateDocument(
				CBPCanUserOperateOperation::StartWorkflow,
				$GLOBALS["USER"]->GetID(),
				$this->arParams["DOCUMENT_ID"],
				array(
					"DocumentStates" => $arDocumentStates,
					"UserGroups" => $this->arParams["USER_GROUPS"]))):
				$arError[] = array(
					"id" => "access_denied",
					"text" => GetMessage("BPABS_NO_PERMS"));
			endif;
		}
		if (!empty($arError))
		{
			$e = new CAdminException($arError);
			ShowError($e->GetString());
			return false;
		}
		elseif (!empty($_REQUEST["cancel"]) && !empty($_REQUEST["back_url"]))
		{
			LocalRedirect(str_replace("#WF#", "", $_REQUEST["back_url"]));
		}

		$this->arResult["SHOW_MODE"] = "SelectWorkflow";
		$this->arResult["TEMPLATES"] = array();
		$this->arResult["PARAMETERS_VALUES"] = array();
		$this->arResult["ERROR_MESSAGE"] = "";

		$runtime = CBPRuntime::GetRuntime();
		$runtime->StartRuntime();
		$this->arResult["DocumentService"] = $runtime->GetService("DocumentService");


		$dbWorkflowTemplate = CBPWorkflowTemplateLoader::GetList(
			array(),
			array(
				"DOCUMENT_TYPE" => $this->arParams["DOCUMENT_TYPE"], "ACTIVE" => "Y",
				'!AUTO_EXECUTE' => CBPDocumentEventType::Automation
			),
			false,
			false,
			array("ID", "NAME", "DESCRIPTION", "MODIFIED", "USER_ID", "PARAMETERS")
		);
		while ($arWorkflowTemplate = $dbWorkflowTemplate->GetNext())
		{
			if (!CBPDocument::CanUserOperateDocument(
				CBPCanUserOperateOperation::StartWorkflow,
				$GLOBALS["USER"]->GetID(),
				$this->arParams["DOCUMENT_ID"],
				array(
					"UserGroups" => $this->arParams["USER_GROUPS"],
					"DocumentStates" => $arDocumentStates,
					"WorkflowTemplateId" => $arWorkflowTemplate["ID"]))):
				continue;
			endif;
			$this->arResult["TEMPLATES"][$arWorkflowTemplate["ID"]] = $arWorkflowTemplate;
			$this->arResult["TEMPLATES"][$arWorkflowTemplate["ID"]]["URL"] =
				htmlspecialcharsex($GLOBALS['APPLICATION']->GetCurPageParam(
					"workflow_template_id=".$arWorkflowTemplate["ID"].'&'.bitrix_sessid_get(),
					Array("workflow_template_id", "sessid")));
		}

		if ($this->arParams["TEMPLATE_ID"] > 0 && strlen($_POST["CancelStartParamWorkflow"]) <= 0
			&& array_key_exists($this->arParams["TEMPLATE_ID"], $this->arResult["TEMPLATES"]))
		{
			$arWorkflowTemplate = $this->arResult["TEMPLATES"][$this->arParams["TEMPLATE_ID"]];

			$arWorkflowParameters = array();
			$bCanStartWorkflow = false;
			$isConstantsTuned = CBPWorkflowTemplateLoader::isConstantsTuned($arWorkflowTemplate["ID"]);

			if (count($arWorkflowTemplate["PARAMETERS"]) <= 0)
			{
				$bCanStartWorkflow = true;
			}
			elseif ($_SERVER["REQUEST_METHOD"] == "POST" && strlen($_POST["DoStartParamWorkflow"]) > 0)
			{
				$arErrorsTmp = array();

				$arRequest = $_REQUEST;

				foreach ($_FILES as $k => $v)
				{
					if (array_key_exists("name", $v))
					{
						if (is_array($v["name"]))
						{
							$ks = array_keys($v["name"]);
							for ($i = 0, $cnt = count($ks); $i < $cnt; $i++)
							{
								$ar = array();
								foreach ($v as $k1 => $v1)
									$ar[$k1] = $v1[$ks[$i]];

								$arRequest[$k][] = $ar;
							}
						}
						else
						{
							$arRequest[$k] = $v;
						}
					}
				}

				$arWorkflowParameters = CBPWorkflowTemplateLoader::CheckWorkflowParameters(
					$arWorkflowTemplate["PARAMETERS"],
					$arRequest,
					$this->arParams["DOCUMENT_TYPE"],
					$arErrorsTmp
				);

				if (count($arErrorsTmp) > 0)
				{
					$bCanStartWorkflow = false;

					foreach ($arErrorsTmp as $e)
						$arError[] = array(
							"id" => "CheckWorkflowParameters",
							"text" => $e["message"]);
				}
				else
				{
					$bCanStartWorkflow = true;
				}
			}

			if(!$isConstantsTuned)
			{
				$arError[] = array(
					"id" => "required_constants",
					"text" => GetMessage("BPABS_REQUIRED_CONSTANTS"));
				$bCanStartWorkflow = false;
			}

			if ($bCanStartWorkflow)
			{
				$arErrorsTmp = array();

				$wfId = CBPDocument::StartWorkflow(
					$this->arParams["TEMPLATE_ID"],
					$this->arParams["DOCUMENT_ID"],
					array_merge($arWorkflowParameters, array(
						CBPDocument::PARAM_TAGRET_USER => "user_".intval($GLOBALS["USER"]->GetID()),
						CBPDocument::PARAM_DOCUMENT_EVENT_TYPE => CBPDocumentEventType::Manual
					)),
					$arErrorsTmp
				);

				if (count($arErrorsTmp) > 0)
				{
					$this->arResult["SHOW_MODE"] = "StartWorkflowError";
					foreach ($arErrorsTmp as $e)
						$arError[] = array(
							"id" => "StartWorkflowError",
							"text" => ($e['code'] > 0 ? '['.$e['code'].'] ': '').$e['message']
						);
				}
				else
				{
					$this->arResult["SHOW_MODE"] = "StartWorkflowSuccess";
					if (strlen($this->arResult["back_url"]) > 0):
						LocalRedirect(str_replace("#WF#", $wfId, $_REQUEST["back_url"]));
						die();
					endif;
				}
			}
			else
			{
				$p = ($_SERVER["REQUEST_METHOD"] == "POST" && strlen($_POST["DoStartParamWorkflow"]) > 0);
				$keys = array_keys($arWorkflowTemplate["PARAMETERS"]);
				foreach ($keys as $key)
				{
					$v = ($p ? $_REQUEST[$key] : $arWorkflowTemplate["PARAMETERS"][$key]["Default"]);
					if (!is_array($v))
					{
						$this->arResult["PARAMETERS_VALUES"][$key] = CBPHelper::ConvertParameterValues($v);
					}
					else
					{
						$keys1 = array_keys($v);
						foreach ($keys1 as $key1)
							$this->arResult["PARAMETERS_VALUES"][$key][$key1] = CBPHelper::ConvertParameterValues($v[$key1]);
					}
				}

				$this->arResult["SHOW_MODE"] = $isConstantsTuned ? "WorkflowParameters" : "StartWorkflowError";
			}

			if (!empty($arError))
			{
				$e = new CAdminException($arError);
				$this->arResult["ERROR_MESSAGE"] = $e->GetString();
			}
		}
		else
		{
			$this->arResult["SHOW_MODE"] = "SelectWorkflow";
		}

		$this->IncludeComponentTemplate();

		if($this->arParams["SET_TITLE"] == "Y")
		{
			$GLOBALS['APPLICATION']->SetTitle(GetMessage("BPABS_TITLE"));
		}
	}

	protected function autoStartParametersAction($execType)
	{
		$arError = array();

		$arDocumentStates = CBPWorkflowTemplateLoader::GetDocumentTypeStates(
			$this->arParams['DOCUMENT_TYPE'], $execType
		);

		if (!CBPDocument::CanUserOperateDocumentType(
			CBPCanUserOperateOperation::StartWorkflow,
			$GLOBALS["USER"]->GetID(),
			$this->arParams["DOCUMENT_TYPE"],
			array(
				"DocumentStates" => $arDocumentStates,
				"UserGroups" => $this->arParams["USER_GROUPS"])))
		{

			$arError[] = array(
				"id"   => "access_denied",
				"text" => GetMessage("BPABS_NO_PERMS"));
		}

		if (!empty($arError))
		{
			$e = new CAdminException($arError);
			ShowError($e->GetString());
			return false;
		}

		$this->arResult["TEMPLATES"] = array();
		foreach ($arDocumentStates as $template)
		{
			if (count($template['TEMPLATE_PARAMETERS']) > 0)
			{
				$parameters = array();

				foreach ($template['TEMPLATE_PARAMETERS'] as $parameterKey => $parameter)
				{
					if ($parameterKey == "TargetUser")
						continue;

					if (!is_array($parameter['Default']))
					{
						$parameter['Default'] = CBPHelper::ConvertParameterValues($parameter['Default']);
					}
					else
					{
						foreach ($parameter['Default'] as $key => $value)
						{
							$parameter['Default'][$key] = CBPHelper::ConvertParameterValues($value);
						}
					}
					$parameters["bizproc".$template['TEMPLATE_ID']."_".$parameterKey] = $parameter;
				}

				$this->arResult["TEMPLATES"][] = array(
					'ID' => $template['TEMPLATE_ID'],
					'NAME' => $template['TEMPLATE_NAME'],
					'DESCRIPTION' => $template['TEMPLATE_DESCRIPTION'],
					'PARAMETERS' => $parameters,
				);
			}
		}

		if (empty($this->arResult["TEMPLATES"]))
		{
			$arError[] = array(
				"id"   => "access_denied",
				"text" => GetMessage("BPABS_NO_AUTOSTART_PARAMETERS")
			);
			$e = new CAdminException($arError);
			ShowError($e->GetString());
			return false;
		}

		$runtime = CBPRuntime::GetRuntime();
		$runtime->StartRuntime();
		$this->arResult["DocumentService"] = $runtime->GetService("DocumentService");
		$this->arResult['EXEC_TYPE'] = $execType;

		$this->IncludeComponentTemplate('autostart');
	}
}