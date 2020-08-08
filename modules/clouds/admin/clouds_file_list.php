<?
define("ADMIN_MODULE_NAME", "clouds");

/*.require_module 'standard';.*/
/*.require_module 'pcre';.*/
/*.require_module 'bitrix_main_include_prolog_admin_before';.*/
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

if(!$USER->CanDoOperation("clouds_browse"))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

/*.require_module 'bitrix_clouds_include';.*/
if(!CModule::IncludeModule('clouds'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);

$obBucket = new CCloudStorageBucket(intval($_GET["bucket"]), false);
if(!$obBucket->Init())
{
	$APPLICATION->SetTitle($obBucket->BUCKET);
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	$message = new CAdminMessage(array(
		"MESSAGE" => GetMessage("CLO_STORAGE_FILE_LIST_ERROR"),
		"DETAILS" => GetMessage("CLO_STORAGE_FILE_UNKNOWN_ERROR", array("#CODE#" => "L00")),
	));
	echo $message->Show();
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

$PHPchunkSize = 1024*1024; // 1M later TODO return_bytes(ini_get('post_max_size'))
$CLOchunkSize = $obBucket->GetService()->GetMinUploadPartSize();

$message = /*.(CAdminMessage).*/null;
$path = (string)$_GET["path"];
$sTableID = "tbl_clouds_file_list";
$oSort = new CAdminSorting($sTableID, "NAME", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

$arFilterFields = Array(
	"find_name",
);
$lAdmin->InitFilter($arFilterFields);

if (mb_strpos($find_name, "*") !== false)
{
	$re_find_name = "#^".str_replace(array("\\*", "\\?"), array(".*", ".{0,1}"), preg_quote($find_name, "#"))."$#";
}
else
{
	$re_find_name = "#".preg_quote($find_name, "#")."#";
}

if ($arID = $lAdmin->GroupAction())
{
	if ($_REQUEST['action_target'] == 'selected')
	{
		$arFiles = $obBucket->ListFiles($path);
		if (is_array($arFiles))
		{
			foreach($arFiles["file"] as $i => $file)
			{
				if ($find_name == "" || preg_match($re_find_name, $file))
				{
					$arID[] =  "F".urlencode($file);
				}
			}
			foreach($arFiles["dir"] as $i => $file)
			{
				if ($find_name == "" || preg_match($re_find_name, $file))
				{
					$arID[] =  "D".urlencode($file);
				}
			}
		}
	}
}

$action = isset($_REQUEST["action"]) && is_string($_REQUEST["action"])? "$_REQUEST[action]": "";
if($USER->CanDoOperation("clouds_upload") && is_array($arID))
{
	foreach($arID as $ID)
	{
		if($ID == '')
			continue;
		$ID = urldecode($ID);

		switch($action)
		{
		case "delete":
			if(mb_substr($ID, 0, 1) === "F")
			{
				$file_size = $obBucket->GetFileSize($path.mb_substr($ID, 1));
				if(!$obBucket->DeleteFile($path.mb_substr($ID, 1)))
				{
					$e = $APPLICATION->GetException();
					if(is_object($e))
						$lAdmin->AddUpdateError($e->GetString(), $ID);
					else
						$lAdmin->AddUpdateError(GetMessage("CLO_STORAGE_FILE_UNKNOWN_ERROR", array(
							"#CODE#" => "D01",
						)), $ID);
				}
				else
				{
					$obBucket->DecFileCounter($file_size);
				}
			}
			elseif(mb_substr($ID, 0, 1) === "D")
			{
				$arFiles = $obBucket->ListFiles($path.mb_substr($ID, 1), true);
				if (is_array($arFiles))
				{
					foreach($arFiles["file"] as $i => $file)
					{
						if(!$obBucket->DeleteFile($path.mb_substr($ID, 1)."/".$file))
						{
							$e = $APPLICATION->GetException();
							if(is_object($e))
								$lAdmin->AddUpdateError($e->GetString(), $ID);
							else
								$lAdmin->AddUpdateError(GetMessage("CLO_STORAGE_FILE_UNKNOWN_ERROR", array(
									"#CODE#" => "D02",
								)), $ID);
							break;
						}
						else
						{
							$obBucket->DecFileCounter($arFiles["file_size"][$i]);
						}
					}
				}
				else
				{
					$e = $APPLICATION->GetException();
					if(is_object($e))
						$lAdmin->AddUpdateError($e->GetString(), $ID);
					else
						$lAdmin->AddUpdateError(GetMessage("CLO_STORAGE_FILE_UNKNOWN_ERROR", array(
							"#CODE#" => "D03",
						)), $ID);
					break;
				}
			}
			break;
		case "chunk_upload":
			require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");
			$strError = "";
			$bytes = 0;
			$fileSize = doubleval($_REQUEST["file_size"]);
			$tempDir = CTempFile::GetDirectoryName(6, "clouds_ipload");
			$absPath = $tempDir."tmp_name";
			if(isset($_REQUEST["file_name"]))
			{
				$filePath = $APPLICATION->ConvertCharset($_REQUEST["file_name"], "UTF-8", LANG_CHARSET);
				$filePath = "/".$_REQUEST["path_to_upload"]."/".$filePath;
				$filePath = preg_replace("#[\\\\\\/]+#", "/", $filePath);

				if (isset($_REQUEST["chunk_start"]) && $_REQUEST["chunk_start"] == 0)
				{
					if($obBucket->FileExists($filePath))
						$strError = GetMessage("CLO_STORAGE_FILE_EXISTS_ERROR");
				}
			}

			if(isset($_REQUEST["chunk_start"]))
			{
				CheckDirPath($tempDir);

				// read contents from the input stream
				$inputHandler = fopen('php://input', "rb");
				// create a temp file where to save data from the input stream
				$fileHandler = fopen($absPath, "ab");
				// save data from the input stream
				while(!feof($inputHandler))
					fwrite($fileHandler, fread($inputHandler, 1024*1024));
				fclose($fileHandler);
			}
			else
			{
				@unlink($absPath);
			}

			if($strError == "")
			{
				if($fileSize <= $CLOchunkSize)
				{
					if(!file_exists($absPath))
					{
						$moveResult = CCloudStorage::FILE_PARTLY_UPLOADED;
						?><script>
							readFileChunk(0, <?echo $PHPchunkSize-1?>);
						</script><?
					}
					elseif(filesize($absPath) < $fileSize)
					{
						$bytes = filesize($absPath);
						$moveResult = CCloudStorage::FILE_PARTLY_UPLOADED;
						?><script>
							readFileChunk(<?echo $bytes?>, <?echo min($fileSize-1, $bytes+$PHPchunkSize-1)?>);
						</script><?
					}
					else
					{
						$ar = CFile::MakeFileArray($absPath);

						if(!is_array($ar) || !isset($ar["tmp_name"]))
						{
							$strError = GetMessage("CLO_STORAGE_FILE_UNKNOWN_ERROR", array("#CODE#" => "e11"));
						}
						else
						{
							$res = $obBucket->SaveFile($filePath, $ar);
							if($res)
							{
								$bytes = $fileSize;
								$moveResult = CCloudStorage::FILE_MOVED;
								$obBucket->IncFileCounter($fileSize);
							}
							else
							{
								$strError = GetMessage("CLO_STORAGE_FILE_UNKNOWN_ERROR", array("#CODE#" => "e12"));
							}
							@unlink($absPath);
						}
					}
				}
				else
				{
					$obUpload = new CCloudStorageUpload($filePath);
					if(!$obUpload->isStarted())
					{
						if($obUpload->Start($obBucket->ID, $fileSize, $_REQUEST["file_type"]))
						{
							$moveResult = CCloudStorage::FILE_PARTLY_UPLOADED;
							?><script>
								readFileChunk(0, <?echo $PHPchunkSize-1?>);
							</script><?
						}
						else
							$strError = GetMessage("CLO_STORAGE_FILE_UNKNOWN_ERROR", array("#CODE#" => "e01"));
					}
					else
					{
						$pos = $obUpload->getPos();
						if($pos > $fileSize)
						{
							if($obUpload->Finish())
							{
								$bytes = $fileSize;
								$obBucket->IncFileCounter($fileSize);
								@unlink($absPath);
								$moveResult = CCloudStorage::FILE_MOVED;
							}
							else
							{
								$strError = GetMessage("CLO_STORAGE_FILE_UNKNOWN_ERROR", array("#CODE#" => "e02"));
							}
						}
						else
						{
							if(!file_exists($absPath))
							{
								$moveResult = CCloudStorage::FILE_PARTLY_UPLOADED;
								?><script>
									readFileChunk(<?echo $pos?>, <?echo $pos + $PHPchunkSize-1?>);
								</script><?
							}
							elseif(
								filesize($absPath) < $obUpload->getPartSize()
								&& ($pos + filesize($absPath) < $fileSize)
							)
							{
								$bytes = $pos + filesize($absPath);
								$moveResult = CCloudStorage::FILE_PARTLY_UPLOADED;
								?><script>
									readFileChunk(<?echo $bytes?>, <?echo min($fileSize-1, $bytes+$PHPchunkSize-1)?>);
								</script><?
							}
							else
							{
								$part = file_get_contents($absPath);
								$bytes = $pos + filesize($absPath);
								$moveResult = CCloudStorage::FILE_SKIPPED;
								while($obUpload->hasRetries())
								{
									if($obUpload->Next($part))
									{
										$moveResult = CCloudStorage::FILE_PARTLY_UPLOADED;
										break;
									}
								}

								if($moveResult == CCloudStorage::FILE_SKIPPED)
									$strError = GetMessage("CLO_STORAGE_FILE_UNKNOWN_ERROR", array("#CODE#" => "e03"));
								else
								{
									?><script>
										readFileChunk(<?echo $obUpload->getPos()?>, <?echo min($fileSize-1, $obUpload->getPos()+$PHPchunkSize-1)?>);
									</script><?
									@unlink($absPath);
								}
							}
						}
					}
				}
			}

			if($strError != "")
			{
				$e = $APPLICATION->GetException();
				if(!is_object($e))
					$e = new CApplicationException($strError);
				$message = new CAdminMessage(GetMessage("CLO_STORAGE_FILE_UPLOAD_ERROR"), $e);
			}

			if(is_object($message))
			{
				echo $message->Show();
				$message = null;
			}
			elseif($moveResult == CCloudStorage::FILE_PARTLY_UPLOADED)
			{
				CAdminMessage::ShowMessage(array(
					"TYPE"=>"PROGRESS",
					"MESSAGE"=>GetMessage("CLO_STORAGE_FILE_UPLOAD_IN_PROGRESS"),
					"DETAILS"=>GetMessage("CLO_STORAGE_FILE_UPLOAD_PROGRESS", array(
						"#bytes#" => CFile::FormatSize($bytes),
						"#file_size#" => CFile::FormatSize($fileSize),
					))."#PROGRESS_BAR#",
					"HTML"=>true,
					"PROGRESS_TOTAL" => $fileSize,
					"PROGRESS_VALUE" => $bytes,
					"BUTTONS" => array(
						array(
							"VALUE" => GetMessage("CLO_STORAGE_FILE_STOP"),
							"ONCLICK" => 'window.location = \''.CUtil::AddSlashes("/bitrix/admin/clouds_file_list.php?lang=".urlencode(LANGUAGE_ID)."&bucket=".urlencode($obBucket->ID)."&path=".urlencode($path)).'\'',
						),
					),
				));
			}
			else
			{
				CAdminMessage::ShowMessage(array(
					"MESSAGE"=>GetMessage("CLO_STORAGE_FILE_UPLOAD_DONE"),
					"DETAILS"=>GetMessage("CLO_STORAGE_FILE_UPLOAD_PROGRESS", array(
						"#bytes#" => CFile::FormatSize($bytes),
						"#file_size#" => CFile::FormatSize($fileSize),
					)),
					"HTML"=>true,
					"TYPE"=>"OK",
				));
				?><script>
					<?=$sTableID?>.GetAdminList('<?echo CUtil::JSEscape($APPLICATION->GetCurPage().'?lang='.urlencode(LANGUAGE_ID).'&bucket='.urlencode($obBucket->ID).'&path='.urlencode($path))?>');
				</script><?
			}

			require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin_js.php");
		case "upload":
			$strError = "";
			$io = CBXVirtualIo::GetInstance();

			$f = null;
			if($ID === "Fnew" && isset($_FILES["upload"]))
			{
				if($_FILES["upload"]["error"] == 0)
				{
					$filePath = $_FILES["upload"]["name"];
					$filePath = "/".$_REQUEST["path_to_upload"]."/".$filePath;
					$filePath = preg_replace("#[\\\\\\/]+#", "/", $filePath);

					$f = $io->GetFile($_FILES["upload"]["tmp_name"]);
				}
				else
				{
					$message = new CAdminMessage(GetMessage("CLO_STORAGE_FILE_UPLOAD_ERROR"), new CApplicationException(GetMessage("CLO_STORAGE_FILE_OPEN_ERROR")));
				}
			}
			elseif($ID !== "Fnew")
			{
				//TODO check for ../../../
				$filePath = mb_substr($ID, 1);
				$filePath = "/".$path."/".$filePath;
				$filePath = preg_replace("#[\\\\\\/]+#", "/", $filePath);

				$f = $io->GetFile(preg_replace("#[\\\\\\/]+#", "/", $_SERVER["DOCUMENT_ROOT"]."/".$path."/".mb_substr($ID, 1)));
			}
			elseif(isset($_REQUEST["filePath"]))
			{
				$obUpload = new CCloudStorageUpload($_REQUEST["filePath"]);
				if($obUpload->isStarted())
				{
					$tempFile = $obUpload->getTempFileName();
					if($tempFile)
						$f = $io->GetFile($tempFile);
				}
			}

			if(!$f)
				break;

			if(
				mb_substr($ID, 0, 1) !== "F"
				|| $obBucket->ACTIVE !== "Y"
				|| $obBucket->READ_ONLY !== "N"
			)
				break;

			$fp = $f->Open("rb");
			if(!is_resource($fp))
			{
				$message = new CAdminMessage(GetMessage("CLO_STORAGE_FILE_UPLOAD_ERROR"), new CApplicationException(GetMessage("CLO_STORAGE_FILE_OPEN_ERROR")));
				break;
			}

			$bytes = 0;
			$fileSize = $f->GetFileSize();
			if($fileSize > $obBucket->GetService()->GetMinUploadPartSize())
			{
				$obUpload = new CCloudStorageUpload($filePath);

				if(!$obUpload->isStarted())
				{
					if($obBucket->FileExists($filePath))
					{
						$message = new CAdminMessage(GetMessage("CLO_STORAGE_FILE_UPLOAD_ERROR"), new CApplicationException(GetMessage("CLO_STORAGE_FILE_EXISTS_ERROR")));
						break;
					}

					$tempFile = CTempFile::GetDirectoryName(6, "clouds_upload").$f->GetName();
					$tempFileX = $io->GetPhysicalName($tempFile);
					CheckDirPath($tempFileX);
					if(copy($io->GetPhysicalName($f->GetPathWithName()), $tempFileX))
					{
						if($obUpload->Start($obBucket->ID, $fileSize, CFile::GetContentType($tempFile), $tempFile))
							$moveResult = CCloudStorage::FILE_PARTLY_UPLOADED;
						else
							$strError = GetMessage("CLO_STORAGE_FILE_UNKNOWN_ERROR", array("#CODE#" => "e01"));
					}
					else
					{
						$strError = GetMessage("CLO_STORAGE_FILE_UNKNOWN_ERROR", array("#CODE#" => "e04"));
					}
				}
				else
				{
					$pos = $obUpload->getPos();
					if($pos > $fileSize)
					{
						if($obUpload->Finish())
						{
							$bytes = $fileSize;
							$obBucket->IncFileCounter($fileSize);
							@unlink($io->GetPhysicalName($f->GetPathWithName()));
							$moveResult = CCloudStorage::FILE_MOVED;
						}
						else
						{
							$strError = GetMessage("CLO_STORAGE_FILE_UNKNOWN_ERROR", array("#CODE#" => "e02"));
						}
					}
					else
					{
						fseek($fp, $pos);
						$part = fread($fp, $obUpload->getPartSize());
						$bytes = $pos + $part;
						$moveResult = CCloudStorage::FILE_SKIPPED;
						while($obUpload->hasRetries())
						{
							if($obUpload->Next($part))
							{
								$moveResult = CCloudStorage::FILE_PARTLY_UPLOADED;
								break;
							}
						}

						if($moveResult == CCloudStorage::FILE_SKIPPED)
							$strError = GetMessage("CLO_STORAGE_FILE_UNKNOWN_ERROR", array("#CODE#" => "e03"));
					}
				}
			}
			else
			{
				if($obBucket->FileExists($filePath))
				{
					$message = new CAdminMessage(GetMessage("CLO_STORAGE_FILE_UPLOAD_ERROR"), new CApplicationException(GetMessage("CLO_STORAGE_FILE_EXISTS_ERROR")));
					break;
				}

				$ar = CFile::MakeFileArray($io->GetPhysicalName($f->GetPathWithName()));
				if(!is_array($ar) || !isset($ar["tmp_name"]))
				{
					$strError = GetMessage("CLO_STORAGE_FILE_UNKNOWN_ERROR", array("#CODE#" => "e11"));
				}
				else
				{
					$res = $obBucket->SaveFile($filePath, $ar);
					if($res)
					{
						$bytes = $fileSize;
						$moveResult = CCloudStorage::FILE_MOVED;
						$obBucket->IncFileCounter($fileSize);
						@unlink($io->GetPhysicalName($f->GetPathWithName()));
					}
					else
					{
						$strError = GetMessage("CLO_STORAGE_FILE_UNKNOWN_ERROR", array("#CODE#" => "e12"));
					}
				}
			}

			$lAdmin->BeginPrologContent();

			if($strError != "")
			{
				$e = $APPLICATION->GetException();
				if(!is_object($e))
					$e = new CApplicationException($strError);

				$message = new CAdminMessage(GetMessage("CLO_STORAGE_FILE_UPLOAD_ERROR"), $e);
			}

			if(is_object($message))
			{
				echo $message->Show();
				$message = null;
			}
			elseif($moveResult == CCloudStorage::FILE_PARTLY_UPLOADED)
			{
				CAdminMessage::ShowMessage(array(
					"MESSAGE"=>GetMessage("CLO_STORAGE_FILE_UPLOAD_IN_PROGRESS"),
					"DETAILS"=>GetMessage("CLO_STORAGE_FILE_UPLOAD_PROGRESS", array(
						"#bytes#" => CFile::FormatSize($bytes),
						"#file_size#" => CFile::FormatSize($fileSize),
					)).'#PROGRESS_BAR#',
					"HTML"=>true,
					"TYPE"=>"PROGRESS",
					"PROGRESS_TOTAL" => $fileSize,
					"PROGRESS_VALUE" => $bytes,
					"BUTTONS" => array(
						array(
							"VALUE" => GetMessage("CLO_STORAGE_FILE_STOP"),
							"ONCLICK" => 'window.location = \''.CUtil::JSEscape("/bitrix/admin/clouds_file_list.php?lang=".urlencode(LANGUAGE_ID)."&bucket=".urlencode($obBucket->ID)."&path=".urlencode($path)).'\'',
						),
					),
				));
			}
			else
			{
				CAdminMessage::ShowMessage(array(
					"MESSAGE"=>GetMessage("CLO_STORAGE_FILE_UPLOAD_DONE"),
					"DETAILS"=>GetMessage("CLO_STORAGE_FILE_UPLOAD_PROGRESS", array(
						"#bytes#" => CFile::FormatSize($bytes),
						"#file_size#" => CFile::FormatSize($fileSize),
					)),
					"HTML"=>true,
					"TYPE"=>"OK",
				));
			}
			$lAdmin->EndPrologContent();

			if($moveResult == CCloudStorage::FILE_PARTLY_UPLOADED)
			{
				$lAdmin->BeginEpilogContent();
				echo '<script>BX.ready(function(){', $lAdmin->ActionDoGroup(urlencode($ID), "upload", "bucket=".urlencode($obBucket->ID)."&path=".urlencode($path)."&filePath=".urlencode($filePath)), '});</script>';
				$lAdmin->EndEpilogContent();
			}
			break;
		default:
			break;
		}
	}
}

$arHeaders = array(
	array(
		"id" => "FILE_NAME",
		"content" => GetMessage("CLO_STORAGE_FILE_NAME"),
		"default" => true,
		"sort" => "NAME",
	),
	array(
		"id" => "FILE_SIZE",
		"content" => GetMessage("CLO_STORAGE_FILE_SIZE"),
		"align" => "right",
		"default" => true,
		"sort" => "FILE_SIZE",
	),
	array(
		"id" => "FILE_COUNT",
		"content" => GetMessage("CLO_STORAGE_FILE_COUNT"),
		"align" => "right",
		"default" => true,
		"sort" => "FILE_COUNT",
	),
	array(
		"id" => "FILE_MTIME",
		"content" => GetMessage("CLO_STORAGE_FILE_MTIME"),
		"align" => "right",
		"default" => true,
		"sort" => "FILE_MTIME",
	),
);

$lAdmin->AddHeaders($arHeaders);

$arData = /*.(array[int][string]string).*/array();

$arFiles = $obBucket->ListFiles($path, $_GET["size"] === "y");

if(is_array($arFiles))
{
	foreach($arFiles["file"] as $i => $file)
	{
		$p = mb_strpos($file, "/");
		if ($p !== false)
		{
			$dir = mb_substr($file, 0, $p);
			if (isset($arFiles["dir"][$dir]))
			{
				$arFiles["dir"][$dir]["FILE_SIZE"] += $arFiles["file_size"][$i];
				$arFiles["dir"][$dir]["FILE_COUNT"]++;
				$arFiles["dir"][$dir]["FILE_MTIME"] = max($arFiles["dir"][$dir]["FILE_MTIME"], CCloudUtil::gmtTimeToDateTime($arFiles["file_mtime"][$i]));
			}
			else
			{
				$arFiles["dir"][$dir] = array(
					"ID" => "D".urlencode($dir),
					"TYPE" => "dir",
					"NAME" => $dir,
					"FILE_SIZE" => $arFiles["file_size"][$i],
					"FILE_COUNT" => 1,
					"FILE_MTIME" => CCloudUtil::gmtTimeToDateTime($arFiles["file_mtime"][$i]),
				);
			}
		}
		elseif ($find_name == "" || preg_match($re_find_name, $file))
		{
			$arData[] = array(
				"ID" => "F".urlencode($file),
				"TYPE" => "file",
				"NAME" => $file,
				"FILE_SIZE" => $arFiles["file_size"][$i],
				"FILE_COUNT" => 1,
				"FILE_MTIME" => CCloudUtil::gmtTimeToDateTime($arFiles["file_mtime"][$i]),
			);
		}
	}

	foreach($arFiles["dir"] as $i => $dir)
	{
		if (is_array($dir))
		{
			if ($find_name == "" || preg_match($re_find_name, $dir["NAME"]))
			{
				$arData[] = $dir;
			}
		}
		elseif ($find_name == "" || preg_match($re_find_name, $dir))
		{
			$size = '';
			$count = '';
			$mtime = '';
			if($_GET["size"] === "y")
			{
				$arDirFiles = $obBucket->ListFiles($path.$dir."/", true);
				$size = array_sum($arDirFiles["file_size"]);
				$count = count($arDirFiles["file"]);
				$mtime = max($arDirFiles["file_mtime"]);
				$mtime = CCloudUtil::gmtTimeToDateTime($mtime);
			}

			$arData[] = array(
				"ID" => "D".urlencode($dir),
				"TYPE" => "dir",
				"NAME" => $dir,
				"FILE_SIZE" => $size,
				"FILE_COUNT" => $count,
				"FILE_MTIME" => $mtime,
			);
		}
	}

	if ($order && $by)
	{
		\Bitrix\Main\Type\Collection::sortByColumn($arData, array(
			'TYPE' => SORT_ASC,
			$by => $order == "desc"? SORT_DESC: SORT_ASC,
		));
	}
	else
	{
		\Bitrix\Main\Type\Collection::sortByColumn($arData, array(
			'TYPE' => SORT_ASC,
			'NAME' => SORT_ASC,
		));
	}

	if($path != "/")
	{
		array_unshift($arData, array(
			"ID" => "D..",
			"TYPE" => "dir",
			"NAME" => "..",
			"FILE_SIZE" => "",
			"FILE_COUNT" => "",
			"FILE_MTIME" => "",
		));
	}
}
else
{
	$e = $APPLICATION->GetException();
	if(is_object($e))
		$message = new CAdminMessage(GetMessage("CLO_STORAGE_FILE_LIST_ERROR"), $e);
	else
		$message = new CAdminMessage(array(
			"MESSAGE" => GetMessage("CLO_STORAGE_FILE_LIST_ERROR"),
			"DETAILS" => GetMessage("CLO_STORAGE_FILE_UNKNOWN_ERROR", array("#CODE#" => "L01")),
		));
}

$total_size = 0.0;
$total_count = 0;

$rsData = new CDBResult;
$rsData->InitFromArray($arData);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(''));

while(is_array($arRes = $rsData->NavNext()))
{
	$row =& $lAdmin->AddRow($arRes["ID"], $arRes);

	$total_size += (int)$arRes["FILE_SIZE"];
	$total_count += (int)$arRes["FILE_COUNT"];

	if ($arRes["FILE_SIZE"] != "")
	{
		$row->AddViewField("FILE_SIZE", CFile::FormatSize($arRes["FILE_SIZE"]));
	}

	if($arRes["TYPE"] === "dir")
	{
		if($arRes["NAME"] === "..")
		{
			$row->bReadOnly = true;
			$row->AddViewField("FILE_NAME", '<a href="'.htmlspecialcharsbx('clouds_file_list.php?lang='.urlencode(LANGUAGE_ID).'&bucket='.urlencode($obBucket->ID).'&path='.urlencode(preg_replace('#([^/]+)/$#', '', $path))).'" class="adm-list-table-icon-link"><span class="adm-submenu-item-link-icon adm-list-table-icon clouds-up-icon"></span><span class="adm-list-table-link">'.htmlspecialcharsex($arRes["NAME"]).'</span></a>');
		}
		else
		{
			$row->AddViewField("FILE_NAME", '<a href="'.htmlspecialcharsbx('clouds_file_list.php?lang='.urlencode(LANGUAGE_ID).'&bucket='.urlencode($obBucket->ID).'&path='.urlencode($path.$arRes["NAME"].'/')).'" class="adm-list-table-icon-link"><span class="adm-submenu-item-link-icon adm-list-table-icon clouds-directory-icon"></span><span class="adm-list-table-link">'.htmlspecialcharsex($arRes["NAME"]).'</span></a>');
		}
	}
	else
	{
		$row->AddViewField("FILE_NAME", '<a href="'.htmlspecialcharsbx($obBucket->GetFileSRC(array("URN" => $path.$arRes["NAME"]))).'">'.htmlspecialcharsex($arRes["NAME"]).'</a>');
	}

	$arActions = /*.(array[int][string]string).*/array();

	if($USER->CanDoOperation("clouds_upload"))
		$arActions[] = array(
			"ICON"=>"delete",
			"TEXT"=>GetMessage("CLO_STORAGE_FILE_DELETE"),
			"ACTION"=>"if(confirm('".GetMessage("CLO_STORAGE_FILE_DELETE_CONF")."')) ".$lAdmin->ActionDoGroup($arRes["ID"], "delete", 'bucket='.urlencode($obBucket->ID).'&path='.urlencode($path))
		);

	if(!empty($arActions))
		$row->AddActions($arActions);
}

if(
	($_GET["size"] === "y")
	&& is_array($arFiles)
	&& (
		(round($total_size/1024) != round($obBucket->FILE_SIZE/1024))
		|| ($total_count != $obBucket->FILE_COUNT)
	)
)
{
	$obBucket->SetFileCounter($total_size, $total_count);
}

$arFooter = array(
	array(
		"title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"),
		"value" => $path === "/"? $rsData->SelectedRowsCount(): $rsData->SelectedRowsCount()-1, // W/O ..
	),
	array(
		"title" => GetMessage("MAIN_ADMIN_LIST_CHECKED"),
		"value" => 0,
		"counter" => true,
	),
);
if($total_size > 0)
{
	$arFooter[] = array(
		"title" => GetMessage("CLO_STORAGE_FILE_SIZE").":",
		"value" => CFile::FormatSize($total_size),
	);
}
$lAdmin->AddFooter($arFooter);

$arGroupActions = array();

if($USER->CanDoOperation("clouds_upload"))
	$arGroupActions["delete"] = GetMessage("MAIN_ADMIN_LIST_DELETE");

$lAdmin->AddGroupActionTable($arGroupActions);

$chain = $lAdmin->CreateChain();
$arPath = explode("/", $path);
$curPath = "/";
foreach($arPath as $dir)
{
	if($dir != "")
	{
		$curPath .= $dir."/";
		$url = "clouds_file_list.php?lang=".urlencode(LANGUAGE_ID)."&bucket=".urlencode($obBucket->ID)."&path=".urlencode($curPath);
		$chain->AddItem(array(
			"TEXT" => htmlspecialcharsex($dir),
			"LINK" => htmlspecialcharsbx($url),
			"ONCLICK" => $lAdmin->ActionAjaxReload($url).';return false;',
		));
	}
}
$lAdmin->ShowChain($chain);

$aContext = array();
if(
	$obBucket->ACTIVE === "Y"
	&& $obBucket->READ_ONLY === "N"
	&& $USER->CanDoOperation("clouds_upload")
)
{
	$aContext[] = array(
		"TEXT" => GetMessage("CLO_STORAGE_FILE_UPLOAD"),
		"LINK" => "javascript:show_upload_form()",
		"TITLE" => GetMessage("CLO_STORAGE_FILE_UPLOAD_TITLE"),
		"ICON" => "btn_new",
	);
}
$aContext[] = array(
	"TEXT" => GetMessage("CLO_STORAGE_FILE_SHOW_DIR_SIZE"),
	"LINK" => "/bitrix/admin/clouds_file_list.php?lang=".urlencode(LANGUAGE_ID).'&bucket='.urlencode($obBucket->ID).'&path='.urlencode($path).'&size=y',
	"TITLE" => GetMessage("CLO_STORAGE_FILE_SHOW_DIR_SIZE_TITLE"),
);

$lAdmin->AddAdminContextMenu($aContext, /*$bShowExcel=*/false);

$lAdmin->BeginPrologContent();
if(is_object($message))
	echo $message->Show();
$lAdmin->EndPrologContent();

$lAdmin->CheckListMode();

$APPLICATION->SetTitle($obBucket->BUCKET);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if($USER->CanDoOperation("clouds_upload")):

CUtil::InitJSCore(array('fx'));

$aTabs = array(
	array(
		"DIV" => "edit1",
		"TAB" => GetMessage("CLO_STORAGE_FILE_UPLOAD"),
		"ICON"=>"main_user_edit",
		"TITLE"=>GetMessage("CLO_STORAGE_FILE_UPLOAD_TITLE"),
	),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs, true, true);
?>

<form name="find_form" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
<?
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
	)
);
$oFilter->Begin();
?>
<tr>
	<td><b><?= GetMessage("CLO_STORAGE_FILE_NAME")?>:</b></td>
	<td nowrap>
		<input type="text" name="find_name" value="<?= htmlspecialcharsbx($find_name)?>" size="35">
	</td>
</tr>
<?
$oFilter->Buttons(array(
	"table_id"=>$sTableID,
	"url"=>"/bitrix/admin/clouds_file_list.php?lang=".urlencode(LANGUAGE_ID).'&bucket='.urlencode($obBucket->ID).'&path='.urlencode($path),
	"form"=>"find_form",
));
$oFilter->End();
?>
</form>

<script>

function show_upload_form()
{
	(new BX.fx({
		start: 0,
		finish: 200,
		time: 0.5,
		type: 'accelerated',
		callback: function(res){
			BX('upload_form', true).style.height = res+'px';
		},
		callback_start: function(){
			BX('upload_form', true).style.height = '0px';
			BX('upload_form', true).style.overflow = 'hidden';
			BX('upload_form', true).style.display = 'block';
		},
		callback_complete: function(){
			BX('upload_form', true).style.height = 'auto';
			BX('upload_form', true).style.overflow = 'auto';
		}
	})).start();
}
function hide_upload_form()
{
	BX('upload_form').style.display='none';
	return;
}
function get_upload_url(additional_args)
{
	var result = 'clouds_file_list.php?'
		+ 'action=chunk_upload'
		+ '&ID=Fnew'
		+ '&lang=<?echo urlencode(LANGUAGE_ID)?>'
		+ '&path=<?echo urlencode($path)?>'
		+ '&path_to_upload=' + BX.util.urlencode(BX('path_to_upload').value)
		+ '&<?echo bitrix_sessid_get()?>'
		+ '&bucket=<?echo CUtil::JSEscape($obBucket->ID)?>'
	;
	if(additional_args)
	{
		for(x in additional_args)
			result += '&' + x + '=' + BX.util.urlencode(additional_args[x]);
	}
	return result;
}

function chunk_upload(opt_Chunk, file, opt_startByte)
{
	var data = new ArrayBuffer(opt_Chunk.length);
	var ui8a = new Uint8Array(data, 0);
	for (var i = 0; i < opt_Chunk.length; i++)
		ui8a[i] = (opt_Chunk.charCodeAt(i) & 0xff);

	var blob;

	try
	{

		blob = new Blob([ui8a]);
	}
	catch (e)
	{
		var bb = new (window.MozBlobBuilder || window.WebKitBlobBuilder || window.BlobBuilder)();
		bb.append(data);
		blob = bb.getBlob();
	}

	ShowWaitWindow();

	BX.ajax({
		'method': 'POST',
		'dataType': 'html',
		'url': get_upload_url({
			file_name: file.name,
			file_size: file.size,
			file_type: file.type,
			chunk_start: opt_startByte
		}),
		'data': blob,
		'onsuccess': function(result){
			BX('upload_progress').innerHTML = result;
			var href = BX('stop_button');
			if(!href)
			{
				CloseWaitWindow();
				BX('start_upload_button').enabled = true;
			}
		},
		'preparePost': false
	});
}

function start_upload()
{
	if (!window.File || !window.FileReader || !window.FileList || !window.Blob)
	{
		BX('editform').submit();
		return;
	}

	var files = BX('upload').files;
	if (!files || !files.length)
		return;

	var file = files[0];

	ShowWaitWindow();
	BX('start_upload_button').enabled = false;

	BX.ajax.post(
		get_upload_url({
			file_name: file.name,
			file_size: file.size,
			file_type: file.type
		}),
		{},
		function(result){
			BX('upload_progress').innerHTML = result;
			var href = BX('stop_button');
			if(!href)
			{
				CloseWaitWindow();
				BX('start_upload_button').disabled = false;
			}
		}
	);

}

function readFileChunk(opt_startByte, opt_stopByte)
{
	var files = BX('upload').files;
	if (!files || !files.length)
		return;

	var file = files[0];
	var start = parseInt(opt_startByte) || 0;
	var stop = parseInt(opt_stopByte) || file.size - 1;

	var reader = new FileReader();
	reader.onloadend = function(evt)
	{
		if (evt.target.readyState == FileReader.DONE)
			chunk_upload(evt.target.result, file, start);
	};

	if (file.webkitSlice) //Deprecated
		var blob = file.webkitSlice(start, stop + 1);
	else if (file.mozSlice) //Deprecated
		var blob = file.mozSlice(start, stop + 1);
	else if (file.slice)
		var blob = file.slice(start, stop + 1);

	reader.readAsBinaryString(blob);
}
</script>
<div id="upload_form" style="display:none;height:200px;">
<div id="upload_progress"></div>
<form method="POST" action="<?echo htmlspecialcharsbx($APPLICATION->GetCurPageParam())?>"  enctype="multipart/form-data" name="editform" id="editform">
<?
$tabControl->Begin();
$tabControl->BeginNextTab();
?>
<tr><td width="40%"><?echo GetMessage("CLO_STORAGE_FILE_PATH_INPUT")?>:</td><td width="60%"><input type="text" id="path_to_upload" name="path_to_upload" size="45" value="<?echo htmlspecialcharsbx($path)?>"></td></tr>
<tr><td><?echo GetMessage("CLO_STORAGE_FILE_UPLOAD_INPUT")?>:</td><td><input type="file" id="upload" name="upload"></td></tr>
<?$tabControl->Buttons(false);?>
<input type="hidden" name="action" value="upload">
<input type="hidden" name="ID" value="Fnew">
<?echo bitrix_sessid_post();?>
<input type="hidden" name="lang" value="<?echo LANGUAGE_ID?>">
<input type="button" id="start_upload_button" onclick="start_upload();" value="<?echo GetMessage("CLO_STORAGE_FILE_UPLOAD_BTN")?>" class="adm-btn-save">
<input type="button" value="<?echo GetMessage("CLO_STORAGE_FILE_CANCEL_BTN")?>" onclick="hide_upload_form()">
<?
$tabControl->End();
?>
</form>
</div>
<?

endif;

$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>