<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("blog"))
{
	ShowError(GetMessage("BLOG_MODULE_NOT_INSTALL"));
	return;
}

$arParams["BLOG_URL"] = preg_replace("/[^a-zA-Z0-9_-]/is", "", Trim($arParams["BLOG_URL"]));

if($arParams["SET_TITLE"]=="Y")
	$APPLICATION->SetTitle(GetMessage("BLOG_USER_GROUP_TITLE"));

$USER_ID = intval($USER->GetID());
$arResult["USER_GROUP"] = Array();

if ($arBlog = CBlog::GetByUrl($arParams["BLOG_URL"]))
{
	if($arBlog["ACTIVE"] == "Y")
	{
		$arGroup = CBlogGroup::GetByID($arBlog["GROUP_ID"]);
		if($arGroup["SITE_ID"] == SITE_ID)
		{
			$arResult["BLOG"] = $arBlog;
				
			if (CBlog::CanUserManageBlog($arBlog["ID"], $USER_ID))
			{
				if($arParams["SET_TITLE"]=="Y")
					$APPLICATION->SetTitle(GetMessage("BLOG_USER_GROUP_TITLE")."\"".$arBlog["NAME"]."\"");
				if ($_POST["save"] && check_bitrix_sessid()) // save on button click
				{
					$arFields=array(
						'NAME' => $_POST["NAME"],
					);

					if (IntVal($_POST['ID']) > 0) // Check: new record or update old one
					{
						$res = CBlogUserGroup::GetList(Array("NAME"=>"ASC"), Array("BLOG_ID"=>$arBlog["ID"], "ID" => IntVal($_POST["ID"])));
						if ($res->Fetch())
						{
							if ($_POST["group_del"]=="Y")
								CBlogUserGroup::Delete(IntVal($_POST['ID']));
							else
								$newID = CBlogUserGroup::Update(IntVal($_POST["ID"]), $arFields);
						}
						else
							$arResult["ERROR_MESSAGE"] = GetMessage("BLOG_ERR_NO_RIGHTS");
					}
					else
					{
						$arFields["BLOG_ID"] = $arBlog["ID"];
						$res = CBlogUserGroup::GetList(Array("NAME"=>"ASC"), Array("BLOG_ID"=>$arFields["BLOG_ID"], "NAME" => $arFields["NAME"]));
						if (!$res->Fetch())
						{
							$newID = CBlogUserGroup::Add($arFields);
						}	
						else
							$arResult["ERROR_MESSAGE"] = GetMessage("BLOG_GROUP_EXIST_1")." \"".htmlspecialcharsEx($arFields["NAME"])."\" ".GetMessage("BLOG_GROUP_EXIST_2");
					}
				
					if(strlen($arResult["ERROR_MESSAGE"])<=0)
						LocalRedirect($_POST["BACK_URL"]);
				}

				if(strlen($_POST["BACK_URL"])>0)
					$arResult["BACK_URL"] = htmlspecialcharsbx($_POST["BACK_URL"]);
				else
					$arResult["BACK_URL"] = htmlspecialcharsbx($APPLICATION->GetCurPageParam());

				$res=CBlogUserGroup::GetList(Array("NAME" => "ASC"), Array("BLOG_ID" => $arBlog["ID"]), array("ID", "NAME", "BLOG_ID", "COUNT" => "USER2GROUP_ID"));
				while ($arGroup=$res->Fetch())
				{
					$arSumGroup[$arGroup["ID"]] = $arGroup["CNT"];
				}

				$res=CBlogUserGroup::GetList($arOrder = Array("NAME" => "ASC"), $arFilter = Array("BLOG_ID" => $arBlog["ID"]));
				while($arGroupCount = $res->GetNext())
				{
					$arGroupCount['CNT'] = IntVal($arSumGroup[$arGroupCount["ID"]]);
					$arGroupCnt[] = $arGroupCount;
				}
				
				$arResult["USER_GROUP"] = $arGroupCnt;
			}
			else
				$arResult["FATAL_ERROR_MESSAGE"] = GetMessage("BLOG_ERR_NO_RIGHTS");
		}
		else
			$arResult["FATAL_ERROR_MESSAGE"] = GetMessage("BLOG_ERR_NO_BLOG");
	}
	else
		$arResult["FATAL_ERROR_MESSAGE"] = GetMessage("BLOG_ERR_NO_BLOG");
}
else
	$arResult["FATAL_ERROR_MESSAGE"] = GetMessage("BLOG_ERR_NO_BLOG");

$this->IncludeComponentTemplate();
?>