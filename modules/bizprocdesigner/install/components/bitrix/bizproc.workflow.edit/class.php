<?php

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

if(!Main\Loader::includeModule('bizproc') || !Main\Loader::includeModule('bizprocdesigner'))
{
	return;
}

Loc::loadMessages(__FILE__);

class BizprocWorkflowEditComponent extends \CBitrixComponent
{
	private \Bitrix\Bizproc\Activity\Settings $activitySettings;

	public function __construct($component = null)
	{
		parent::__construct($component);

		$this->activitySettings = new \Bitrix\Bizproc\Activity\Settings('~bizprocdesigner');
	}

protected function listKeysSignedParameters()
	{
		return ['MODULE_ID', 'ENTITY', 'DOCUMENT_TYPE'];
	}

	/**
	 * @param array $params
	 * @return array
	 */
	public function onPrepareComponentParams($params)
	{
		$params['SET_TITLE'] = !(isset($params['SET_TITLE']) && $params['SET_TITLE'] == 'N');
		$params['BACK_URL'] = (isset($_REQUEST['back_url']) && $_REQUEST['back_url'][0] === '/' && $_REQUEST['back_url'][1] !== '/') ? (string)$_REQUEST['back_url'] : null;

		if (!isset($params['MODULE_ID']) && !defined('MODULE_ID') && !empty($params['ID']))
		{
			$tpl = \Bitrix\Bizproc\WorkflowTemplateTable::getList([
				'filter' => ['=ID' => $params['ID']],
				'select' => ['MODULE_ID', 'ENTITY', 'DOCUMENT_TYPE']
			])->fetch();
			[$params['MODULE_ID'], $params['ENTITY'], $params['DOCUMENT_TYPE']] = array_values($tpl);
		}

		if (!isset($params['MODULE_ID']) && defined('MODULE_ID'))
		{
			$params['MODULE_ID'] = MODULE_ID;
		}

		if (!isset($params['ENTITY']) && defined('ENTITY'))
		{
			$params['ENTITY'] = ENTITY;
		}

		if (!defined('MODULE_ID'))
		{
			define("MODULE_ID", $params["MODULE_ID"]);
		}
		if (!defined('ENTITY'))
		{
			define("ENTITY", $params["ENTITY"]);
		}

		$params['DOCUMENT_TYPE'] = preg_replace("/[^0-9A-Za-z_-]/", "", $params['DOCUMENT_TYPE']);

		return $params;
	}

	protected function isAuthorizationNeeded()
	{
		global $USER;
		return !(isset($USER) && is_object($USER) && $USER->IsAuthorized());
	}

	protected function setPageTitle($title)
	{
		global $APPLICATION;
		$APPLICATION->SetTitle($title);
	}

	public function executeComponent()
	{
		global $APPLICATION, $USER;

		$this->arResult = [
			'NeedAuth' => $this->isAuthorizationNeeded()? 'Y' : 'N',
			'FatalErrorMessage' => '',
			'ErrorMessage' => ''
		];

		$this->arResult['DOCUMENT_TYPE'] = $this->arParams["DOCUMENT_TYPE"];
		$this->arResult['ID'] = $this->arParams["ID"];

		$this->arResult['LIST_PAGE_URL'] = $this->arParams['LIST_PAGE_URL'];
		$this->arResult["EDIT_PAGE_TEMPLATE"] = $this->arParams["EDIT_PAGE_TEMPLATE"];
		$backUrl = $this->arParams['BACK_URL'];

		$this->arResult['DOCUMENT_TYPE'] = $this->arParams['DOCUMENT_TYPE'];

		$documentType = $this->arResult['DOCUMENT_TYPE'];

		$canWrite = false;

		$ID = intval($this->arResult['ID']);
		if($ID > 0)
		{
			$dbTemplatesList = CBPWorkflowTemplateLoader::GetList([], ["ID" =>$ID]);
			if ($arTemplate = $dbTemplatesList->Fetch())
			{
				$canWrite = CBPDocument::CanUserOperateDocumentType(
					CBPCanUserOperateOperation::CreateWorkflow,
					$GLOBALS["USER"]->GetID(),
					$arTemplate["DOCUMENT_TYPE"]
				);

				$documentType = $arTemplate["DOCUMENT_TYPE"][2];

				$workflowTemplateName = $arTemplate["NAME"];
				$workflowTemplateDescription = $arTemplate["DESCRIPTION"];
				$workflowTemplateAutostart = $arTemplate["AUTO_EXECUTE"];
				$workflowTemplateIsSystem = $arTemplate["IS_SYSTEM"];
				$workflowTemplateSort = $arTemplate["SORT"];
				$arWorkflowTemplate = $arTemplate["TEMPLATE"];
				$arWorkflowParameters = $arTemplate["PARAMETERS"];
				$arWorkflowVariables = $arTemplate["VARIABLES"];
				$arWorkflowConstants = $arTemplate["CONSTANTS"];
			}
			else
			{
				$ID = 0;
			}
		}

		if($ID <= 0)
		{
			if(!$documentType)
			{
				$APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED")." ".Loc::getMessage("BIZPROC_WFEDIT_ERROR_TYPE"));
			}

			$canWrite = CBPDocument::CanUserOperateDocumentType(
				CBPCanUserOperateOperation::CreateWorkflow,
				$GLOBALS["USER"]->GetID(),
				[MODULE_ID, ENTITY, $documentType]
			);

			$workflowTemplateName = Loc::getMessage("BIZPROC_WFEDIT_DEFAULT_TITLE");
			$workflowTemplateDescription = '';
			$workflowTemplateAutostart = 1;
			$workflowTemplateIsSystem = 'N';
			$workflowTemplateSort = 10;

			if (isset($_GET['init']) && $_GET['init'] == 'statemachine')
			{
				$arWorkflowTemplate = [
					[
						"Type" => "StateMachineWorkflowActivity",
						"Name" => "Template",
						"Properties" => [],
						"Children" => []
					]
				];
			}
			else
			{
				$arWorkflowTemplate = [
					[
						"Type" => "SequentialWorkflowActivity",
						"Name" => "Template",
						"Properties" => [],
						"Children" => []
					]
				];
			}

			$arWorkflowParameters = [];
			$arWorkflowVariables = [];
			$arWorkflowConstants = [];
		}

		if(!$canWrite)
		{
			$APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
		}

		$saveUrl = $this->arResult["LIST_PAGE_URL"];
		$applyUrl = str_replace("#ID#", $ID, $this->arResult["EDIT_PAGE_TEMPLATE"]);
		if ($backUrl)
		{
			$saveUrl = $backUrl;
			$applyUrl = CHTTP::urlAddParams($applyUrl, ['back_url' => $backUrl], ['encode' => true]);
			$this->arResult['BACK_URL'] = $backUrl;
		}

		if($_SERVER['REQUEST_METHOD']=='POST' && isset($_REQUEST['saveajax']) && $_REQUEST['saveajax'] === 'Y' && check_bitrix_sessid())
		{
			$APPLICATION->RestartBuffer();
			CBPHelper::decodeTemplatePostData($_POST);

			if (!empty($_REQUEST['saveuserparams']))
			{
				$d = is_array($_POST['USER_PARAMS']) ? $_POST['USER_PARAMS'] : null;
				$maxLength = 16777215;//pow(2, 24) - 1; //mysql mediumtext column length
				if (!$d || strlen(serialize($d)) > $maxLength)
				{
				?><!--SUCCESS--><script>
					alert('<?=GetMessageJS("BIZPROC_USER_PARAMS_SAVE_ERROR")?>');
				</script><?
				die();
				}
				$this->activitySettings->save($d);
				die('<!--SUCCESS-->');
			}

			$arFields = [
				"DOCUMENT_TYPE" => [MODULE_ID, ENTITY, $documentType],
				//		"ACTIVE" 		=> $_POST["ACTIVE"],
				"AUTO_EXECUTE" 	=> $_POST["workflowTemplateAutostart"],
				"NAME" 			=> $_POST["workflowTemplateName"],
				"DESCRIPTION" 	=> $_POST["workflowTemplateDescription"],
				"TEMPLATE" 		=> $_POST["arWorkflowTemplate"],
				"PARAMETERS"	=> $_POST["arWorkflowParameters"],
				"VARIABLES" 	=> $_POST["arWorkflowVariables"],
				"CONSTANTS" 	=> $_POST["arWorkflowConstants"],
				"IS_SYSTEM" 	=> $_POST["workflowTemplateIsSystem"] ?? 'N',
				"SORT" 	=> $_POST["workflowTemplateSort"] ?? 10,
				"USER_ID"		=> intval($USER->GetID()),
				"MODIFIER_USER" => new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser),
			];

			if(!is_array($arFields["VARIABLES"]))
			{
				$arFields["VARIABLES"] = [];
			}
			if(!is_array($arFields["CONSTANTS"]))
			{
				$arFields["CONSTANTS"] = [];
			}

			if (!empty($arFields['PARAMETERS']))
			{
				$maxParametersLength = 65535;
				if (self::getCompressedFieldLength($arFields['PARAMETERS']) > $maxParametersLength)
				{
					self::showError(Loc::getMessage('BIZPROC_WFEDIT_PARAMETERS_SAVE_ERROR'));
					die('<!--SUCCESS-->');
				}
			}

			if (!empty($arFields['VARIABLES']))
			{
				$maxVariablesLength = 65535;
				if (self::getCompressedFieldLength($arFields['VARIABLES']) > $maxVariablesLength)
				{
					self::showError(Loc::getMessage('BIZPROC_WFEDIT_VARIABLES_SAVE_ERROR'));
					die('<!--SUCCESS-->');
				}
			}

			if (!empty($arFields['CONSTANTS']))
			{
				$maxConstantsLength = 16777215;
				if (self::getCompressedFieldLength($arFields['CONSTANTS']) > $maxConstantsLength)
				{
					self::showError(Loc::getMessage('BIZPROC_WFEDIT_CONSTANTS_SAVE_ERROR'));
					die('<!--SUCCESS-->');
				}
			}

			try
			{
				if($ID>0)
				{
					CBPWorkflowTemplateLoader::Update($ID, $arFields);
				}
				else
				{
					$ID = CBPWorkflowTemplateLoader::Add($arFields);
					$applyUrl = str_replace("#ID#", $ID, $this->arResult["EDIT_PAGE_TEMPLATE"]);
					if ($backUrl)
					{
						$applyUrl = CHTTP::urlAddParams($applyUrl, ['back_url' => $backUrl], ['encode' => true]);
					}
				}
			}
			catch (Exception $e)
			{
				$errorMessages = [];
				$errors = [];
				if (method_exists($e, 'getErrors'))
				{
					$errors = $e->getErrors();
					foreach($errors as $error)
					{
						$errorMessages[] = CUtil::JSEscape($error['message']);
					}
				}
				else
				{
					$errorMessages[] = CUtil::JSEscape($e->getMessage());
				}
				?><!--SUCCESS--><script>
					alert('<?=GetMessageJS("BIZPROC_WFEDIT_SAVE_ERROR")?>\n<?=implode('\n', $errorMessages)?>');
					(function(){
						var i, setFocus = true, activity, error, errors = [];
						errors = <?=\Bitrix\Main\Web\Json::encode($errors);?>;

						for (i = 0; i < errors.length; ++i)
						{
							error = errors[i];
							if (error.activityName)
							{
								activity = window.rootActivity.findChildById(error.activityName);
								/** @var BizProcActivity activity */
								if (activity)
								{
									activity.SetError(true, setFocus);
									setFocus = false;
								}
							}
						}
					})();
				</script><?
				die();
			}
			?><!--SUCCESS--><script>
				BPTemplateIsModified = false;
				window.location = '<?=(!empty($_REQUEST["apply"])? CUtil::JSEscape($applyUrl) : CUtil::JSEscape($saveUrl))?>';
			</script><?
			die();
		}

		if($_SERVER['REQUEST_METHOD']=='GET' && !empty($_REQUEST['export_template']) && check_bitrix_sessid())
		{
			$APPLICATION->RestartBuffer();
			if ($ID > 0)
			{
				$datum = CBPWorkflowTemplateLoader::ExportTemplate($ID);

				header("HTTP/1.1 200 OK");
				header("Content-Type: application/force-download; name=\"bp-".$ID.".bpt\"");
				header("Content-Transfer-Encoding: binary");
				header("Content-Length: ". \Bitrix\Main\Text\BinaryString::getLength($datum));
				header("Content-Disposition: attachment; filename=\"bp-".$ID.".bpt\"");
				header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
				header("Expires: 0");
				header("Pragma: public");

				echo $datum;
			}
			die();
		}

		if($_SERVER['REQUEST_METHOD']=='POST' && $_REQUEST['import_template']=='Y' && check_bitrix_sessid())
		{
			$APPLICATION->RestartBuffer();
			//CUtil::DecodeUriComponent($_POST);

			$r = 0;
			$errTmp = "";
			if (is_uploaded_file($_FILES['import_template_file']['tmp_name']))
			{
				$f = fopen($_FILES['import_template_file']['tmp_name'], "rb");
				$datum = fread($f, filesize($_FILES['import_template_file']['tmp_name']));
				fclose($f);

				try
				{
					if ($ID > 0 && $workflowTemplateAutostart == \CBPDocumentEventType::Automation)
					{
						$_POST["import_template_autostart"] = \CBPDocumentEventType::Automation;
					}

					$r = CBPWorkflowTemplateLoader::ImportTemplate(
						$ID,
						[MODULE_ID, ENTITY, $documentType],
						$_POST["import_template_autostart"],
						$_POST["import_template_name"],
						$_POST["import_template_description"],
						$datum
					);
				}
				catch (Exception $e)
				{
					$errTmp = preg_replace("#[\r\n]+#", " ", $e->getMessage());
				}
			}
			?>
			<script>
				<?if (intval($r) <= 0):?>
				alert('<?= GetMessageJS("BIZPROC_WFEDIT_IMPORT_ERROR").($errTmp <> '' ? ": ".CUtil::JSEscape($errTmp) : "" ) ?>');
				<?else:?>
				<?$ID = $r;?>
				<?endif;
				$applyUrl = str_replace("#ID#", $ID, $this->arResult["EDIT_PAGE_TEMPLATE"]);
				if ($backUrl)
				{
					$applyUrl = CHTTP::urlAddParams($applyUrl, ['back_url' => $backUrl], ['encode' => true]);
				}
				?>
				window.location = '<?=CUtil::JSEscape($applyUrl)?>';
			</script>
			<?
			die();
		}

		$arAllActGroups = [
			"document" => Loc::getMessage("BIZPROC_WFEDIT_CATEGORY_DOC_1"),
			'task' => Loc::getMessage('BIZPROC_WFEDIT_CATEGORY_TASKS_1'),
			"logic" => Loc::getMessage("BIZPROC_WFEDIT_CATEGORY_CONSTR_1"),
			"interaction" => Loc::getMessage("BIZPROC_WFEDIT_CATEGORY_INTER"),
			"rest" => Loc::getMessage("BIZPROC_WFEDIT_CATEGORY_REST_1"),
		];

		$runtime = CBPRuntime::GetRuntime();
		$runtime->StartRuntime();
		$arAllActivities = $runtime->SearchActivitiesByType("activity", [MODULE_ID, ENTITY, $documentType]);

		foreach ($arAllActivities as $activity)
		{
			if (!empty($activity['CATEGORY']['OWN_ID']) && !empty($activity['CATEGORY']['OWN_NAME']))
			{
				$arAllActGroups[$activity['CATEGORY']['OWN_ID']] = $activity['CATEGORY']['OWN_NAME'];
			}
		}
		$arAllActGroups['other'] = Loc::getMessage("BIZPROC_WFEDIT_CATEGORY_OTHER");

		$this->arResult['DOCUMENT_TYPE'] = $documentType;

		$this->arResult['ACTIVITY_GROUPS'] = $arAllActGroups;
		$this->arResult['ACTIVITIES'] = $arAllActivities;

		$this->arResult['TEMPLATE_NAME'] = $workflowTemplateName;
		$this->arResult['TEMPLATE_DESC'] = $workflowTemplateDescription;
		$this->arResult['TEMPLATE_AUTOSTART'] = $workflowTemplateAutostart;
		$this->arResult['TEMPLATE_IS_SYSTEM'] = $workflowTemplateIsSystem;
		$this->arResult['TEMPLATE_SORT'] = $workflowTemplateSort;
		$this->arResult['TEMPLATE'] = $arWorkflowTemplate;
		$this->arResult['TEMPLATE_CHECK_STATUS'] = CBPWorkflowTemplateLoader::checkTemplateActivities($arWorkflowTemplate);
		$this->arResult['PARAMETERS'] = $arWorkflowParameters;
		$this->arResult['VARIABLES'] = $arWorkflowVariables;
		$this->arResult['CONSTANTS'] = $arWorkflowConstants;
		$this->arResult['GLOBAL_CONSTANTS'] = \Bitrix\Bizproc\Workflow\Type\GlobalConst::getAll([MODULE_ID, ENTITY, $documentType]);
		$this->arResult['GLOBAL_VARIABLES'] = \Bitrix\Bizproc\Workflow\Type\GlobalVar::getAll([MODULE_ID, ENTITY, $documentType]);
		$this->arResult['GLOBAL_CONSTANTS_VISIBILITY_NAMES'] =
			\Bitrix\Bizproc\Workflow\Type\GlobalConst::getVisibilityFullNames([MODULE_ID, ENTITY, $documentType])
		;
		$this->arResult['GLOBAL_VARIABLES_VISIBILITY_NAMES'] =
			\Bitrix\Bizproc\Workflow\Type\GlobalVar::getVisibilityFullNames([MODULE_ID, ENTITY, $documentType])
		;

		/** @var CBPDocumentService $documentService */
		$documentService = $runtime->getDocumentService();
		$this->arResult['DOCUMENT_FIELDS'] = $documentService->GetDocumentFields([MODULE_ID, ENTITY, $documentType]);

		$this->arResult["ID"] = $ID;

		$userParamsStr = $this->activitySettings->get();
		if (is_array($userParamsStr))
		{
			$userParams = $userParamsStr;
		}
		elseif ($userParamsStr && CheckSerializedData($userParamsStr))
		{
			$userParams = unserialize($userParamsStr, ['allowed_classes' => false]);
		}

		if (empty($userParams) || !is_array($userParams))
		{
			$userParams = ['SNIPPETS' => []];
		}

		$this->arResult["USER_PARAMS"] = $userParams;
		$this->arResult["DOCUMENT_TYPE_SIGNED"] = \CBPDocument::signDocumentType([MODULE_ID, ENTITY, $documentType]);

		if ($this->arParams['SET_TITLE'])
		{
			$this->setPageTitle(Loc::getMessage(
					$ID > 0 ? 'BIZPROC_WFEDIT_TITLE_EDIT' : 'BIZPROC_WFEDIT_TITLE_ADD'
			));
		}

		$this->includeComponentTemplate();
	}

	private static function getCompressedFieldLength($field)
	{
		if (CBPWorkflowTemplateLoader::useGZipCompression())
		{
			return mb_strlen(gzcompress(serialize($field), 9));
		}

		return mb_strlen(serialize($field));
	}

	private static function showError($message): void
	{
		$message = htmlspecialcharsbx($message);

		echo <<<HTML
			<script>alert('$message');</script>
			HTML
		;
	}
}