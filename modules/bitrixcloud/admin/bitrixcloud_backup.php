<?
define("ADMIN_MODULE_NAME", "bitrixcloud");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
IncludeModuleLangFile(__FILE__);
/** @global CMain $APPLICATION */
/** @global CUser $USER */
if (!$USER->CanDoOperation("bitrixcloud_backup") || !CModule::IncludeModule("bitrixcloud"))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$APPLICATION->SetTitle(GetMessage("BCL_BACKUP_TITLE"));

try
{
	$backup = CBitrixCloudBackup::getInstance();
	$arFiles = $backup->listFiles();
	$backup->saveToOptions();

	$sTableID = "t_bitrixcloud_backup";
	$lAdmin = new CAdminList($sTableID);
	$arHeaders = array(
		array(
			"id" => "FILE_NAME",
			"content" => GetMessage("BCL_BACKUP_FILE_NAME"),
			"default" => true,
		),
		array(
			"id" => "FILE_SIZE",
			"content" => GetMessage("BCL_BACKUP_FILE_SIZE"),
			"align" => "right",
			"default" => true,
		),
	);
	$lAdmin->AddHeaders($arHeaders);
	$rsData = new CDBResult;
	$rsData->InitFromArray($arFiles);
	$rsData = new CAdminResult($rsData, $sTableID);

	while($arRes = $rsData->GetNext())
	{
		$row = $lAdmin->AddRow($arRes["FILE_NAME"], $arRes);
		$row->AddViewField("FILE_SIZE", CFile::FormatSize($arRes["FILE_SIZE"]));
	}

	if(CModule::IncludeModule('clouds'))
	{
		$aContext = array(
			array(
				"TEXT" => GetMessage("BCL_BACKUP_DO_BACKUP"),
				"LINK" => "/bitrix/admin/dump.php?lang=".LANGUAGE_ID."&from=bitrixcloud",
				"TITLE" => "",
				"ICON" => "btn_new",
			),
		);
		$lAdmin->AddAdminContextMenu($aContext, /*$bShowExcel=*/false);
	}

	$lAdmin->CheckListMode();

	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	/*
	$fileName = FormatDate("Ydm_His_", time()).mt_rand(0, 999).".enc.gz";
	if($_GET["action"] == "write")
	{
		$obBucket = $backup->getBucketToWriteFile($check_word, $fileName);
		echo "<pre>",htmlspecialcharsbx(print_r($fileName,1)),"</pre>";
		if($obBucket->Init())
		{
			$obBucket->setPublic(false);
			//$obBucket->SaveFile($fileName, CFile::MakeFileArray(1));

			$obUpload = new CCloudStorageUpload($fileName);
			if(!$obUpload->isStarted())
			{
				$obBucket->setCheckWordHeader();
				echo "start: ", $res = $obUpload->Start($obBucket, 7*1024*1024), "<br>";
				if(!$res)
				{
					var_dump($APPLICATION->getException());
				}
				else
				{
					$obBucket->unsetCheckWordHeader();
					echo "first: ", $obUpload->Next(str_repeat("1234\n", 1024*1024), $obBucket), "<br>";
					$obBucket->unsetCheckWordHeader();
					echo "end: ", $obUpload->Next(str_repeat("5\n", 1024*1024), $obBucket), "<br>";
					$obBucket->unsetCheckWordHeader();
					echo "finish: ", $obUpload->Finish($obBucket), "<br>";
				}
			}
		}
	}
	*/
	CAdminMessage::ShowMessage(array(
		"TYPE" => "PROGRESS",
		"DETAILS" => '<p><b>'.GetMessage("BCL_BACKUP_USAGE", array(
			"#QUOTA#" => CFile::FormatSize($backup->getQuota()),
			"#USAGE#" => CFile::FormatSize($backup->getUsage()),
		)).'</b></p>#PROGRESS_BAR#',
		"HTML" => true,
		"PROGRESS_TOTAL" => $backup->getQuota(),
		"PROGRESS_VALUE" => $backup->getUsage(),
	));

	$lAdmin->DisplayList();
	echo BeginNote(), GetMessage("BCL_BACKUP_NOTE"), EndNote();
}
catch (Exception $e)
{
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	CAdminMessage::ShowMessage($e->getMessage());
	$arFiles = false;
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>