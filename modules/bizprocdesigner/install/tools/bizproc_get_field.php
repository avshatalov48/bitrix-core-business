<?php
/**
 * @deprecated
 * Now use \Bitrix\Bizproc\Controller\FieldType
 */
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
\Bitrix\Main\Loader::includeModule('bizproc');

if (!check_bitrix_sessid())
	die();

if (empty($_REQUEST['DocumentType']) || !is_array($_REQUEST['DocumentType']))
{
	die();
}

$documentType = $_REQUEST['DocumentType'];
$user = new \CBPWorkflowTemplateUser(\CBPWorkflowTemplateUser::CurrentUser);
$operationParameters = [];

if (isset($documentType[3]))
{
	$operationParameters['DocumentCategoryId'] = $documentType[3];
}

if (
	!$user->isAdmin()
	&& !CBPDocument::CanUserOperateDocumentType(
		CBPCanUserOperateOperation::ViewWorkflow,
		$user->getId(),
		$documentType,
		$operationParameters
	)
)
{
	die();
}

if (LANG_CHARSET != "UTF-8" && isset($_REQUEST['Type']['Options']) && is_array($_REQUEST['Type']['Options']))
{
	$newarr = [];
	foreach ($_REQUEST['Type']['Options'] as $k => $v)
		$newarr[CharsetConverter::ConvertCharset($k, "UTF-8", LANG_CHARSET)] = $v;
	$_REQUEST['Type']['Options'] = $newarr;
}

$runtime = CBPRuntime::GetRuntime();
$runtime->StartRuntime();
$documentService = $runtime->GetService("DocumentService");

$type = $_REQUEST['Type'];
$value = $_REQUEST['Value'] ?? null;
$publicMode = (!empty($_REQUEST['RenderMode']) && $_REQUEST['RenderMode'] === 'public');

if ($_REQUEST['Mode'] == "Type")
{
	echo $documentService->GetFieldInputControlOptions(
		$documentType,
		$type,
		$_REQUEST['Func'],
		$value
	);
}
else
{
	global $APPLICATION;
	$APPLICATION->ShowAjaxHead();

	//Fix array sorting after js sorting %)
	if (
		isset($type['OptionsSort']) && is_array($type['OptionsSort'])
		&& isset($type['Options']) && is_array($type['Options'])
		&& count($type['OptionsSort']) === count($type['Options'])
	)
	{
		$sortedOptions = [];
		$sortSuccess = true;
		foreach ($type['OptionsSort'] as $optionKey)
		{
			if (!isset($type['Options'][$optionKey]))
			{
				$sortSuccess = false;
				break;
			}
			$sortedOptions[$optionKey] = $type['Options'][$optionKey];
		}
		if ($sortSuccess)
		{
			$type['Options'] = $sortedOptions;
		}
		unset($sortSuccess, $sortedOptions);
	}

	/** @var CBPDocumentService $documentService */
	echo $documentService->GetFieldInputControl(
		$documentType,
		$type,
		$_REQUEST['Field'],
		$value,
		$_REQUEST['Als'] ? true : false,
		$publicMode
	);
}

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
