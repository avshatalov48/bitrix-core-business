<?
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

define("ADMIN_MODULE_NAME", "fileman");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

if(!Loader::includeModule("fileman"))
{
	ShowError(Loc::getMessage("MAIN_MODULE_NOT_INSTALLED"));
}

/** @var CAllMain $APPLICATION Application. */
$modulePermission = $APPLICATION->GetGroupRight("fileman");
if($modulePermission == "D")
{
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");
$request = \Bitrix\Main\Context::getCurrent()->getRequest();
switch($request->get('action'))
{
	case 'save_file':

		$result = array(
			'error' => false,
			'errorText' => '',
			'data' => array(
				'list' => array(),
			)
		);
		$fileList = array();

		//New from media library and file structure
		$isCheckedSuccess = false;
		$requestFiles = $request->getPost('NEW_FILE_EDITOR');
		if($requestFiles && is_array($requestFiles))
		{
			foreach($requestFiles as $index=>$value)
			{
				if(is_array($value))
				{
					$filePath = urldecode($value['tmp_name']);
				}
				else
				{
					continue;
				}

				$isCheckedSuccess = false;
				$io = CBXVirtualIo::GetInstance();
				$docRoot = \Bitrix\Main\Application::getDocumentRoot();
				if(mb_strpos($filePath, CTempFile::GetAbsoluteRoot()) === 0)
				{
					$absPath = $filePath;
				}
				elseif(mb_strpos($io->CombinePath($docRoot, $filePath), CTempFile::GetAbsoluteRoot()) === 0)
				{
					$absPath = $io->CombinePath($docRoot, $filePath);
				}
				else
				{
					$absPath = $io->CombinePath(CTempFile::GetAbsoluteRoot(), $filePath);
				}

				if ($io->ValidatePathString($absPath) && $io->FileExists($absPath))
				{
					$docRoot = $io->CombinePath($docRoot, '/');
					$relPath = str_replace($docRoot, '', $absPath);
					$perm = $APPLICATION->GetFileAccessPermission($relPath);
					if ($perm >= "W")
					{
						$isCheckedSuccess = true;
					}
				}

				if($isCheckedSuccess)
				{
					$fileList[$filePath] = CFile::MakeFileArray($io->GetPhysicalName($absPath));
					if(isset($value['name']))
					{
						$fileList[$filePath]['name'] = $value['name'];
					}
				}
				else
				{
					$result['data']['list'][] = array(
						'tmp' => $filePath,
						'path' => ''
					);
				}
			}
		}


		foreach($fileList as $tmpFileName => $file)
		{
			if($file["name"] == '' || intval($file["size"]) <= 0)
			{
				continue;
			}

			$resultInsertAttachFile = false;
			$file["MODULE_ID"] = "fileman";
			$fid = intval(CFile::SaveFile($file, "fileman", true));
			if($fid > 0 && ($filePath = CFile::GetPath($fid)) && $filePath <> '')
			{
				$result['data']['list'][] = array(
					'tmp' => $tmpFileName,
					'path' => $filePath
				);
			}
		}

		if (!$isCheckedSuccess && count($fileList) == 0)
		{
			$result['error'] = true;
			$result['errorText'] = GetMessage("ACCESS_DENIED");
		}

		echo CUtil::PhpToJSObject($result);
		break;

	case 'preview_mail':

		$request = \Bitrix\Main\Context::getCurrent()->getRequest();
		$previewParams = array(
			'CAN_EDIT_PHP' => $GLOBALS["USER"]->CanDoOperation('edit_php'),
			'CAN_USE_LPA' => $GLOBALS["USER"]->CanDoOperation('lpa_template_edit'),
			'SITE' => $request->get('site_id'),
			'HTML' => $request->get('content'),
			'FIELDS' => array(
				'SENDER_CHAIN_CODE' => 'sender_chain_item_0',
			),
		);
		echo \Bitrix\Fileman\Block\EditorMail::getPreview($previewParams);
		break;
}


require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_js.php");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_after.php");