<?if(!check_bitrix_sessid()) return;
IncludeModuleLangFile(__FILE__); 
if(is_array($GLOBALS["errors"]) && count($GLOBALS["errors"])>0):
	echo CAdminMessage::ShowMessage(Array("TYPE"=>"ERROR", "MESSAGE" =>GetMessage("MOD_INST_ERR"), "DETAILS"=>implode("<br>", $GLOBALS["errors"]), "HTML"=>true));
else:
	echo CAdminMessage::ShowNote(GetMessage("MOD_INST_OK"));
endif;

if ($_REQUEST["INSTALL_PUBLIC"] == "Y" && is_array($_REQUEST["PUBLIC_INFO"]) && !empty($_REQUEST["PUBLIC_INFO"]))
{
	$bREWRITE = ($_REQUEST["REWRITE_PUBLIC"] == "Y" ? true : false);
	foreach ($_REQUEST["PUBLIC_INFO"] as $res)
	{
		// Fatal errors
		if (!is_array($res) || empty($res))
			continue;
		// Errors
		$res["ID"] = intval($res["ID"]);
		$res["PATH"] = htmlspecialcharsbx(trim($res["PATH"]));
		$res["MODE"] = ($res["MODE"] == "sef" ? "sef" : "nsef");
		if ($res["PATH"] == '')
		{
			?><?=$res["ID"]?>. <?=GetMessage("FORUM_BAD_PATH")?><?
			continue;
		}
		$res["~PATH"] = preg_replace("/[\/\\\]+/", "/", "/".$res["PATH"]."/");
		$res["PATH"] = preg_replace("/[\/\\\]+/", "/", $_SERVER["DOCUMENT_ROOT"]."/".$res["PATH"]."/");
		CheckDirPath($res["PATH"]);
		$fileExistBefore = file_exists($res["PATH"]."index.php");
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/install/public/components/".$res["MODE"], 
			$res["PATH"], $bREWRITE, true);
			
		if ($res["MODE"] == "sef" && (!$fileExistBefore || $bREWRITE) && file_exists($res["PATH"]."index.php"))
		{
			$file = file_get_contents($res["PATH"]."index.php");
			if ($file)
			{
				$file = str_replace("#SEF_FOLDER#", $res["~PATH"], $file);
				if ($f = fopen($res["PATH"]."index.php", "w"))
				{
					@fwrite($f, $file);
					@fclose($f);
				}
			}
			$arFields = array(
				"CONDITION" => "#^".$res["~PATH"]."#",
				"RULE" => "",
				"ID" => "bitrix:forum",
				"PATH" => $res["~PATH"]."index.php"
			);
			Bitrix\Main\UrlRewriter::add(CSite::GetDefSite(), $arFields);
		}
		?><p><a href="<?=$res["~PATH"]?>"><?=$res["~PATH"]?></a></p><?
	}
}

?><form action="<?=$APPLICATION->GetCurPage()?>">
	<input type="hidden" name="lang" value="<?=LANG?>">
	<input type="submit" name="" value="<?=GetMessage("MOD_BACK")?>">	
<form><?