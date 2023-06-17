<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$this->IncludeComponentLang("files.php");
include_once __DIR__."/base.php";

class CCommentFiles extends CCommentBase
{
	var $imageSize = 100;
	protected $mfiParams = [];

	function __construct(&$component)
	{
		parent::__construct($component);

		global $APPLICATION, $USER;

		$arResult =& $component->arResult;
		$arParams =& $component->arParams;
		$arParams["mfi"] = md5("forum.comments");
		$this->mfiParams = [
			"FORUM_ID" => $arParams["FORUM_ID"],
			"TOPIC_ID" => $arResult["FORUM_TOPIC_ID"],
			"MESSAGE_ID" => 0,
			"USER_ID" => $USER->getId(),
			"ALLOW" => [
				"ALLOW_UPLOAD" => ($arParams["ALLOW_UPLOAD"] == "I" ? "Y" : $arParams["ALLOW_UPLOAD"]),
				"ALLOW_UPLOAD_EXT" => $arParams["ALLOW_UPLOAD_EXT"]
			]
		];

		$_REQUEST["FILE_NEW"] = isset($_REQUEST["FILE_NEW"]) && is_array($_REQUEST["FILE_NEW"]) ? $_REQUEST["FILE_NEW"] : [];

		if (isset($arParams['IMAGE_SIZE']) && (intval($arParams['IMAGE_SIZE']) > 0 || $arParams['IMAGE_SIZE']===0))
			$this->imageSize = intval($arParams['IMAGE_SIZE']);

		$APPLICATION->AddHeadScript("/bitrix/js/main/utils.js");

		$this->removeHandler("OnCommentFormDisplay");
		$this->removeHandler("OnCommentsInit");
		if ($this->mfiParams["ALLOW"]["ALLOW_UPLOAD"] === "N")
		{
			$this->removeHandler("OnCommentAdd");
		}
	}

	public function OnFileUploadToMFI(&$file)
	{
		if (!is_array($file) || !isset($file["fileID"]))
		{
			return false;
		}

		global $APPLICATION;
		$params = $this->mfiParams;
		$files = [["FILE_ID" => $file["fileID"]] + $file];
		if (!CForumFiles::CheckFields($files, $params, "NOT_CHECK_DB", ["FORUM" => $params["ALLOW"]]))
		{
			$error = "File upload error.";
			if ($ex = $APPLICATION->GetException())
			{
				$error = $ex->GetString();
			}
			return $error;
		}

		if (!empty($files))
		{
			CForumFiles::Add($file["fileID"], $params);
		}
		return true;
	}

	function OnPrepareComments($component)
	{
		if ($this->component !== $component)
		{
			return;
		}
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
				">MESSAGE_ID" => intval(min($res)) - 1,
				"<MESSAGE_ID" => intval(max($res)) + 1);
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

	function OnCommentsInit($component)
	{
		if ($this->component !== $component)
		{
			return;
		}
		if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_REQUEST["mfi_mode"]))
		{
			$arResult['DO_NOT_CACHE'] = true;
			if ($_REQUEST['mfi_mode'] == "upload")
			{
				$this->addHandler("main.file.input.upload", [$this, "OnFileUploadToMFI"], "main");
			}
		}
	}

	function OnCommentDisplay($arComment)
	{
		$arResult =& $this->component->arResult;

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
		if ($this->mfiParams["ALLOW"]["ALLOW_UPLOAD"] != "N")
		{
?>
		<div class="comments-reply-field comments-reply-field-upload">
<?
			$iFileSize = intval(COption::GetOptionString("forum", "file_max_size", 5242880));
			$sFileSize = CFile::FormatSize($iFileSize);

?>
			<div class="comments-upload-info" id="upload_files_info_<?=$arParams["form_index"]?>"><?
			if ($this->mfiParams["ALLOW"]["ALLOW_UPLOAD"] == "F")
			{
				?><span><?=str_replace("#EXTENSION#", $this->mfiParams["ALLOW"]["ALLOW_UPLOAD_EXT"], GetMessage("F_FILE_EXTENSION"))?></span><?
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
				'ALLOW_UPLOAD' => $this->mfiParams["ALLOW"]['ALLOW_UPLOAD'],
				'ALLOW_UPLOAD_EXT' => $this->mfiParams["ALLOW"]['ALLOW_UPLOAD_EXT']
			);
			if ($this->mfiParams["ALLOW"]['ALLOW_UPLOAD'] == 'Y')
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
		if (!is_array($_REQUEST["FILE_NEW"]) || empty($_REQUEST["FILE_NEW"]))
		{
			return true;
		}

		$arPost["FILES"] = array();
		foreach($_REQUEST["FILE_NEW"] as $fileID)
		{
			if (($file = CFile::MakeFileArray((int) $fileID)) &&
				array_key_exists("name", $file))
			{
				$file = ["fileID" => $fileID] + $file;
				if ($this->OnFileUploadToMFI($file) === true)
				{
					$arPost["FILES"][$fileID] = array("FILE_ID" => $fileID);
				}
				else
				{
					$arPost["ERROR"] = $file["name"].": errored. ";
					break;
				}
			}
		}
		return !array_key_exists("ERROR", $arPost);
	}
}