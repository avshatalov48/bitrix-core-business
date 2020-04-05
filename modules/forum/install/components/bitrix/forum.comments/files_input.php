<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$this->IncludeComponentLang("files.php");
class CCommentFiles
{
	var $imageSize = 100;
	var $component = null;

	function __construct(&$component)
	{
		global $APPLICATION;
		$this->component = &$component;
		$arResult =& $component->arResult;
		$arParams =& $component->arParams;
		$arParams["mfi"] = md5("forum.comments");

		$_REQUEST["FILE_NEW"] = is_array($_REQUEST["FILE_NEW"]) ? $_REQUEST["FILE_NEW"] : array();

		if (isset($arParams['IMAGE_SIZE']) && (intval($arParams['IMAGE_SIZE']) > 0 || $arParams['IMAGE_SIZE']===0))
			$this->imageSize = intval($arParams['IMAGE_SIZE']);

		$APPLICATION->AddHeadScript("/bitrix/js/main/utils.js");

		AddEventHandler("forum", "OnPrepareComments", Array(&$this, "OnPrepareComments"));
		AddEventHandler("forum", "OnCommentPreview", Array(&$this, "OnCommentPreview"));
		AddEventHandler("forum", "OnCommentError", Array(&$this, "OnCommentError"));

		if ($arParams["ALLOW_UPLOAD"] !== "N")
		{
			AddEventHandler("forum", "OnCommentsInit", Array(&$this, "OnCommentsInit"));

			if ($arParams["ALLOW_UPLOAD"] != $arResult["FORUM"]["ALLOW_UPLOAD"] ||
				$arParams["ALLOW_UPLOAD_EXT"] != $arResult["FORUM"]["ALLOW_UPLOAD_EXT"])
				AddEventHandler("forum", "OnAfterCommentAdd", Array(&$this, "OnAfterCommentAdd"));
			else
				AddEventHandler("forum", "OnCommentAdd", Array(&$this, "OnCommentAdd"));
		}
	}

	public static function OnFileUploadToMFI(&$arCustomFile, $arParams = null)
	{
		static $arFileParams = array();
		if ($arParams !== null)
			$arFileParams = $arParams;
		$arFiles = array(array("FILE_ID" => $arCustomFile["fileID"]));
		if ((!is_array($arCustomFile)) || !isset($arCustomFile['fileID'])):
			return false;
		elseif(!CForumFiles::CheckFields($arFiles, $arFileParams, "NOT_CHECK_DB", array("FORUM" => $arFileParams["ALLOW"]))):
			$ex = $GLOBALS["APPLICATION"]->GetException();
			$res = ($ex ? $ex->GetString() : "File upload error.");
			return $res;
		elseif(!empty($arFiles)):
			$GLOBALS["APPLICATION"]->RestartBuffer();
			CForumFiles::Add($arCustomFile["fileID"], $arFileParams);
		endif;
	}

	function OnPrepareComments()
	{
		$arResult =& $this->component->arResult;
		$arParams =& $this->component->arParams;

		$arMessages = &$arResult['MESSAGES'];
		$arResult['FILES'] = array();
		if (!empty($arMessages))
		{
			$res = array_keys($arMessages);
			$arFilter = array(
				"FORUM_ID" => $arParams["FORUM_ID"],
				"TOPIC_ID" => $arResult["FORUM_TOPIC_ID"],
				"APPROVED_AND_MINE" => $GLOBALS["USER"]->GetId(),
				">MESSAGE_ID" => intVal(min($res)) - 1,
				"<MESSAGE_ID" => intVal(max($res)) + 1);
			if ($arResult["USER"]["RIGHTS"]["MODERATE"] == "Y")
				unset($arFilter["APPROVED_AND_MINE"]);
			$db_files = CForumFiles::GetList(array("MESSAGE_ID" => "ASC"), $arFilter);
			if ($db_files && $res = $db_files->Fetch())
			{
				do
				{
					$res["SRC"] = CFile::GetFileSRC($res);
					if ($arMessages[$res["MESSAGE_ID"]]["~ATTACH_IMG"] == $res["FILE_ID"])
					{
						// attach for custom
						$arMessages[$res["MESSAGE_ID"]]["~ATTACH_FILE"] = $res;
						$arMessages[$res["MESSAGE_ID"]]["ATTACH_IMG"] = CFile::ShowFile($res["FILE_ID"], 0,
							$this->imageSize, $this->imageSize, true, "border=0", false);
						$arMessages[$res["MESSAGE_ID"]]["ATTACH_FILE"] = $arMessages[$res["MESSAGE_ID"]]["ATTACH_IMG"];
					}
					$arMessages[$res["MESSAGE_ID"]]["FILES"][$res["FILE_ID"]] = $res;
					$arResult['FILES'][$res["FILE_ID"]] = $res;
				} while ($res = $db_files->Fetch());
			}
		}
	}

	function OnCommentError()
	{
		$arResult =& $this->component->arResult;
		$arParams =& $this->component->arParams;

		$arDummy = array();
		$this->OnCommentAdd(null, null, $arDummy);

		$arResult["REVIEW_FILES"] = array();
		foreach ($_REQUEST["FILE_NEW"] as $val)
			$arResult["REVIEW_FILES"][$val] = CFile::GetFileArray($val);
	}

	function OnCommentPreview()
	{
		$this->OnCommentError();
		$arResult["MESSAGE_VIEW"]["FILES"] = $_REQUEST["FILE_NEW"];
	}

	function OnCommentPreviewDisplay()
	{
		$arResult =& $this->component->arResult;
		$arParams =& $this->component->arParams;

		if (empty($arResult["REVIEW_FILES"]))
			return null;

		ob_start();
		if (!empty($arResult["REVIEW_FILES"]))
		{
?>
			<div class="comments-post-attachments">
				<label><?=GetMessage("F_ATTACH_FILES")?></label>
<?
			$parentComponent = null;
			if (isset($GLOBALS['forumComponent']) && is_object($GLOBALS['forumComponent']))
				$parentComponent =&$GLOBALS['forumComponent'];
				foreach ($arResult["REVIEW_FILES"] as $arFile)
				{
?>
					<div class="comments-post-attachment"><?
					?><?$GLOBALS["APPLICATION"]->IncludeComponent(
						"bitrix:forum.interface", "show_file",
						Array(
							"FILE" => $arFile,
							"WIDTH" => $arResult["PARSER"]->image_params["width"],
							"HEIGHT" => $arResult["PARSER"]->image_params["height"],
							"CONVERT" => "N",
							"FAMILY" => "FORUM",
							"SINGLE" => "Y",
							"RETURN" => "N",
							"SHOW_LINK" => "Y"),
						$parentComponent,
						array("HIDE_ICONS" => "Y"));
					?></div>
<?				}?>
			</div>
<?		}
		return array(array('DISPLAY' => 'AFTER', 'SORT' => '50', 'TEXT' => ob_get_clean()));
	}

	function OnCommentsInit()
	{
		$arResult =& $this->component->arResult;
		$arParams =& $this->component->arParams;
		if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_REQUEST["mfi_mode"]))
		{
			$arResult['DO_NOT_CACHE'] = true;
			if ($_REQUEST['mfi_mode'] == "upload")
			{
				AddEventHandler('main', "main.file.input.upload", 'CCommentFiles::OnFileUploadToMFI');
				$Null = null;
				self::OnFileUploadToMFI($Null, array(
					"FORUM_ID" => $arParams["FORUM_ID"],
					"TOPIC_ID" => $arResult['FORUM_TOPIC_ID'],
					"MESSAGE_ID" => 0,
					"USER_ID" => intval($GLOBALS["USER"]->GetID()),
					"ALLOW" => $arParams["ALLOW"]
				));
			}
		}
	}

	function OnCommentDisplay($arComment)
	{
		$arResult =& $this->component->arResult;
		$arParams =& $this->component->arParams;

		if (empty($arComment["FILES"]))
			return null;

		ob_start();
		foreach ($arComment["FILES"] as $arFile)
		{
			if (!in_array($arFile["FILE_ID"], $arComment["FILES_PARSED"]))
			{
				?><div class="comments-message-img"><?
				?><?$GLOBALS["APPLICATION"]->IncludeComponent(
					"bitrix:forum.interface", "show_file",
					Array(
						"FILE" => $arFile,
						"WIDTH" => $arResult["PARSER"]->imageWidth,
						"HEIGHT" => $arResult["PARSER"]->imageHeight,
						"CONVERT" => "N",
						"FAMILY" => "FORUM",
						"SINGLE" => "Y",
						"RETURN" => "N",
						"SHOW_LINK" => "Y"),
					$this->component,
					array("HIDE_ICONS" => "Y"));
				?></div><?
			}
		}
		return array(array('DISPLAY' => 'AFTER', 'SORT' => '50', 'TEXT' => ob_get_clean()));
	}

	function OnCommentFormDisplay()
	{
		$arResult =& $this->component->arResult;
		$arParams =& $this->component->arParams;

		ob_start();
		if ($arParams["ALLOW_UPLOAD"] != "N")
		{
?>
		<div class="comments-reply-field comments-reply-field-upload">
<?
			$iFileSize = intval(COption::GetOptionString("forum", "file_max_size", 5242880));
			$sFileSize = CFile::FormatSize($iFileSize);

?>
			<div class="comments-upload-info" id="upload_files_info_<?=$arParams["form_index"]?>"><?
			if ($arParams["ALLOW_UPLOAD"] == "F")
			{
				?><span><?=str_replace("#EXTENSION#", $arParams["ALLOW_UPLOAD_EXT"], GetMessage("F_FILE_EXTENSION"))?></span><?
			}
				?><span><?=str_replace("#SIZE#", $sFileSize, GetMessage("F_FILE_SIZE"))?></span>
			</div>
<?
			$componentParams = array(
				'INPUT_NAME' => 'FILE_NEW',
				'INPUT_NAME_UNSAVED' => 'FILE_NEW_TMP',
				'INPUT_VALUE' => (!empty($arResult["REVIEW_FILES"]) ? array_keys($arResult["REVIEW_FILES"]) : array()),
				'MAX_FILE_SIZE' => $iFileSize,
				'MODULE_ID' => 'forum',
				'CONTROL_ID' => 'fcomments_'.$arParams["ENTITY_TYPE"]."_".$arParams["ENTITY_ID"],
				'ALLOW_UPLOAD' => $arParams['ALLOW_UPLOAD'],
				'ALLOW_UPLOAD_EXT' => $arParams['ALLOW_UPLOAD_EXT']
			);
			if ($arParams['ALLOW_UPLOAD'] == 'Y')
				$componentParams['ALLOW_UPLOAD'] = 'I';
			$GLOBALS['APPLICATION']->IncludeComponent('bitrix:main.file.input', '', $componentParams, $this->component, array("HIDE_ICONS" => true));
?>
		</div>
<?
		}
		return array(array('DISPLAY' => 'AFTER', 'SORT' => '50', 'TEXT' => ob_get_clean()));
	}
	function OnCommentAdd($entityType, $entityID, &$arPost)
	{
		global $USER;

		$arParams =& $this->component->arParams;
		$arResult =& $this->component->arResult;

		$iFileSize = intval(COption::GetOptionString("forum", "file_max_size", 5242880));
		$_REQUEST['FILE_NEW'] = (isset($_REQUEST['FILE_NEW']) && is_array($_REQUEST['FILE_NEW']) ? $_REQUEST['FILE_NEW'] : array());
		$arPost["FILES"] = array();

		foreach($_REQUEST['FILE_NEW'] as $fileID)
		{
			$arPost["FILES"][$fileID] = array("FILE_ID" => $fileID);
			$attach_file = CFile::MakeFileArray(intval($fileID));
			$attach = "";
			if ($attach_file && is_set($attach_file, "name"))
			{
				if ($arParams["ALLOW_UPLOAD"]=="Y")
					$attach = CFile::CheckImageFile($attach_file, $iFileSize, 0, 0);
				elseif ($arParams["ALLOW_UPLOAD"]=="F")
					$attach = CFile::CheckFile($attach_file, $iFileSize, false, $arParams["ALLOW_UPLOAD_EXT"]);
				elseif ($arParams["ALLOW_UPLOAD"]=="A")
					$attach = CFile::CheckFile($attach_file, $iFileSize, false, false);
				if ($attach != '')
				{
					unset($arPost['FILES'][$fileID]);
					$arPost['ERROR'] = $attach_file['name'].': '.$attach;
					return false;
				}
			}
		}
		return true;
	}

	function OnAfterCommentAdd ($entityType, $entityID, $params = array())
	{
		$arPost = array();
		$arParams =& $this->component->arParams;
		$arResult =& $this->component->arResult;
		if (!!$this->OnCommentAdd($entityType, $entityID, $arPost) && !!$arPost["FILES"])
		{
			CForumFiles::UpdateByID(
				array_keys($arPost["FILES"]),
				array(
					"FORUM_ID" => $arParams["FORUM_ID"],
					"TOPIC_ID" => $params["TOPIC_ID"],
					"MESSAGE_ID" => $params["MESSAGE_ID"]
				)
			);
		}
	}
}
?>
