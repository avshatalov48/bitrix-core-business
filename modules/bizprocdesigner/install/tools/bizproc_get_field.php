<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bizproc/include.php");

if (!check_bitrix_sessid())
	die();
if (!CBPDocument::CanUserOperateDocumentType(CBPCanUserOperateOperation::CreateWorkflow, $GLOBALS["USER"]->GetID(), $_REQUEST['DocumentType']))
	die();

CUtil::DecodeUriComponent($_REQUEST);
CUtil::DecodeUriComponent($_POST);

if (LANG_CHARSET != "UTF-8" && isset($_REQUEST['Type']['Options']) && is_array($_REQUEST['Type']['Options']))
{
	$newarr = array();
	foreach ($_REQUEST['Type']['Options'] as $k => $v)
		$newarr[CharsetConverter::ConvertCharset($k, "UTF-8", LANG_CHARSET)] = $v;
	$_REQUEST['Type']['Options'] = $newarr;
}

$runtime = CBPRuntime::GetRuntime();
$runtime->StartRuntime();
$documentService = $runtime->GetService("DocumentService");

$type = $_REQUEST['Type'];
$value = $_REQUEST['Value'];

if ($_REQUEST['Mode'] == "Type")
{
	echo $documentService->GetFieldInputControlOptions(
		$_REQUEST['DocumentType'],
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
		$sortedOptions = array();
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

	/** @var CBPDocumentService $documentService*/
	echo $documentService->GetFieldInputControl(
		$_REQUEST['DocumentType'],
		$type,
		$_REQUEST['Field'],
		$value,
		$_REQUEST['Als'] ? true : false
	);
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
