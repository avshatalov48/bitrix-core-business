<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!CModule::IncludeModule("blog"))
{
	ShowError(GetMessage("BLOG_MODULE_NOT_INSTALL"));
	return;
}
if (!CModule::IncludeModule("idea"))
{
	ShowError(GetMessage("IDEA_MODULE_NOT_INSTALL"));
	return;
}
/**
 * @global CMain $APPLICATION
 * @global CUser $USER
 * @param array $arParams
 * @param array $arResult
 * @param CBitrixComponent $this
 */
$arParams['NAME_TEMPLATE'] = empty($arParams['NAME_TEMPLATE']) ? CSite::GetNameFormat() : $arParams["NAME_TEMPLATE"];
$arParams['ACTION'] = (array_key_exists("ACTION", $_REQUEST) && check_bitrix_sessid() ? $_REQUEST["ACTION"] : (array_key_exists('ACTION', $arParams) ? $arParams["ACTION"] : false));
//ACTION PROCESSING
if($arParams['ACTION'])
{
	switch ($arParams['ACTION'])
	{
		case "ADD":
			break;
		case "DELETE":
			if(array_key_exists("ID", $_REQUEST))
				CIdeaManagment::getInstance()->Notification()->getEmailNotify()->Delete($_REQUEST["ID"]);
			else if (array_key_exists("ENTITY_TYPE", $_REQUEST))
			{
				$notifyEmail = new \Bitrix\Idea\NotifyEmail();
				if ($_REQUEST["ENTITY_TYPE"] == \Bitrix\Idea\NotifyEmailTable::ENTITY_TYPE_CATEGORY)
					$notifyEmail->deleteCategory($_REQUEST["ENTITY_CODE"]);
				else
					$notifyEmail->deleteIdea($_REQUEST["ENTITY_CODE"]);
			}
			LocalRedirect($APPLICATION->GetCurPageParam("", array("ACTION", "ID", "ENTITY_TYPE", "ENTITY_CODE", "sessid")));
		break;
	}
}

$arResult = array(
	"USER_ID" => $USER->GetID(),
	"IDEA" => array(),
	"SUBSCRIBE" => array(),
	"IDEA_STATUS" => array(),
	"GRID" => array()
);

//Get Idea subscribtion
if($arResult["USER_ID"]>0)
{
	//InitGrid
	$GridOptions = new CGridOptions($arResult["GRID_ID"]);

	//Grid Sort
	$arSort = $GridOptions->GetSorting(
		array(
			"sort" => array("DATE_PUBLISH" => "DESC"),
			"vars" => array("by" => "by", "order" => "order")
		)
	);
	$arResult["GRID"]["SORT"] = $arSort["sort"];
	$arResult["GRID"]["SORT_VARS"] = $arSort["vars"];

	$arNav = $GridOptions->GetNavParams(array("nPageSize"=>25));

	$db_res = \Bitrix\Idea\NotifyEmailTable::getList(
		array(
			'filter' => array("USER_ID" => $arResult["USER_ID"]),
			'select' => array("ID" => "RUNTIME_ID", "SUBSCRIBE_TYPE", "ENTITY_TYPE", "ENTITY_CODE", "CATEGORY_NAME" => "ASCENDED_CATEGORIES.NAME"),
			'order' => array("ENTITY_TYPE" => "ASC", "ENTITY_CODE" => "ASC"),
			'runtime' => array(new \Bitrix\Main\Entity\ExpressionField(
						'RUNTIME_ID',
							\Bitrix\Main\Application::getConnection()->getSqlHelper()->getConcatFunction(
							"CASE ".
								"WHEN %s='".\Bitrix\Idea\NotifyEmailTable::ENTITY_TYPE_IDEA."' AND %s='' THEN '".CIdeaManagmentEmailNotify::SUBSCRIBE_ALL."' ".
								"WHEN %s='".\Bitrix\Idea\NotifyEmailTable::ENTITY_TYPE_IDEA."' THEN '".CIdeaManagmentEmailNotify::SUBSCRIBE_IDEA_COMMENT."' ".
								"WHEN %s='".\Bitrix\Idea\NotifyEmailTable::ENTITY_TYPE_CATEGORY."' AND %s='' THEN '".CIdeaManagmentEmailNotify::SUBSCRIBE_ALL_IDEA."' ".
								"WHEN %s='".\Bitrix\Idea\NotifyEmailTable::ENTITY_TYPE_CATEGORY."' THEN '".\Bitrix\Idea\NotifyEmailTable::ENTITY_TYPE_CATEGORY."' ".
								"ELSE 'UNK' END",
							"%s"),
						array("ENTITY_TYPE", "ENTITY_CODE", "ENTITY_TYPE", "ENTITY_TYPE", "ENTITY_CODE", "ENTITY_TYPE", "ENTITY_CODE")
					)
				)
		)
	);

	$oIdeaSubscribe = new CDBResult($db_res);
	$oIdeaSubscribe->NavStart($arNav["nPageSize"], false);
	//Select Subscribe
	$arBlogPostId = array();
	while($r = $oIdeaSubscribe->GetNext())
	{
		$arResult["SUBSCRIBE"][] = $r["ID"];
		if ($r["ID"] == CIdeaManagmentEmailNotify::SUBSCRIBE_ALL)
			$arResult["IDEA"] = array(CIdeaManagmentEmailNotify::SUBSCRIBE_ALL => $r + array(
				"TITLE" => GetMessage("IDEA_SUBSCRIBE_ALL_SUBSCRIBED"),
				"ID" => CIdeaManagmentEmailNotify::SUBSCRIBE_ALL,
			)) + $arResult["IDEA"];
		else if ($r["ID"] == CIdeaManagmentEmailNotify::SUBSCRIBE_ALL_IDEA)
			$arResult["IDEA"] = array(CIdeaManagmentEmailNotify::SUBSCRIBE_ALL_IDEA => $r + array(
				"TITLE" => GetMessage("IDEA_SUBSCRIBE_ALL_IDEA_SUBSCRIBED"),
				"ID" => CIdeaManagmentEmailNotify::SUBSCRIBE_ALL_IDEA,
			)) + $arResult["IDEA"];
		else if ($r["ENTITY_TYPE"] == \Bitrix\Idea\NotifyEmailTable::ENTITY_TYPE_CATEGORY)
			$arResult["IDEA"][$r["ID"]] = $r + array(
				"TITLE" => (!!$r["CATEGORY_NAME"] ? $r["CATEGORY_NAME"] : GetMessage("IDEA_SUBSCRIBE_NOT_FOUND_2", array("#CODE#" => $r["ENTITY_CODE"]))));
		else if ($r["ENTITY_TYPE"] == \Bitrix\Idea\NotifyEmailTable::ENTITY_TYPE_IDEA)
		{
			$arResult["IDEA"][$r["ID"]] = $r + array(
				"TITLE" => GetMessage("IDEA_SUBSCRIBE_NOT_FOUND")
			);
			$arBlogPostId[] = $r["ENTITY_CODE"];
		}
	}
	//Grid Nav
	$arResult["GRID"]["NAVIGATION"] = $oIdeaSubscribe;


	if(!empty($arBlogPostId))
	{
		$oIdeaPost = CBlogPost::GetList(
			array(key($arResult["GRID"]["SORT"]) => current($arResult["GRID"]["SORT"])),
			array("ID" => $arBlogPostId),
			false,
			false,
			array("ID", "TITLE", "PATH", "DATE_PUBLISH", CIdeaManagment::UFStatusField, "AUTHOR_LOGIN", "AUTHOR_NAME", "AUTHOR_LAST_NAME", "AUTHOR_SECOND_NAME")
		);

		while($r = $oIdeaPost->GetNext())
			$arResult["IDEA"][CIdeaManagmentEmailNotify::SUBSCRIBE_IDEA_COMMENT.$r["ID"]] = $r;
		$arResult["IDEA_STATUS"] = CIdeaManagment::getInstance()->Idea()->GetStatusList();
	}

	//Make Grid
	$arResult["GRID"]["ID"] = "idea_subscribe_".$arResult["USER_ID"];

	foreach($arResult["IDEA"] as $res)
	{
		$arColumns = array();


		if ($res["ENTITY_TYPE"] == \Bitrix\Idea\NotifyEmailTable::ENTITY_TYPE_CATEGORY)
		{
			$data = array(
				"NAME" => $res["TITLE"],
				"STATUS" => ($res["SUBSCRIBE_TYPE"] == \Bitrix\Idea\NotifyEmailTable::SUBSCRIBE_TYPE_NEW_IDEAS ? GetMessage("IDEA_SUBSCRIBE_NEW_IDEAS") : ""),
			);
		}
		else
		{
			if (array_key_exists("PATH", $res))
				$arColumns = array(
					"NAME" => "<a href='".CComponentEngine::MakePathFromTemplate(htmlspecialcharsBack($res["PATH"]), array("post_id" => $res["ID"]))."'>".$res["TITLE"]."</a>",
				);

			$AuthorName = CUser::FormatName($arParams["NAME_TEMPLATE"],
				array("NAME"		=> $res["AUTHOR_NAME"],
					"LAST_NAME"	 => $res["AUTHOR_LAST_NAME"],
					"SECOND_NAME"   => $res["AUTHOR_SECOND_NAME"],
					"LOGIN"		 => $res["AUTHOR_LOGIN"]
					), true);
			$data = array(
				"NAME" => $res["TITLE"],
				"STATUS" => $arResult["IDEA_STATUS"][$res[CIdeaManagment::UFStatusField]]["VALUE"],
				"PUBLISHED" => $res["DATE_PUBLISH"],
				"AUTHOR" => $AuthorName,
			);
		}
		$arResult["GRID"]["DATA"][] = array(
			"data" => $data,
			"actions" => array(
				array(
					"ICONCLASS" => "delete",
					"TEXT" => GetMessage("IDEA_POST_UNSUBSCRIBE"),
					"ONCLICK" => "window.location.href='".
						CUtil::JSEscape($APPLICATION->GetCurPageParam(
							"ACTION=DELETE&ENTITY_TYPE=".$res["ENTITY_TYPE"]."&ENTITY_CODE=".$res["ENTITY_CODE"]."&".bitrix_sessid_get(),
							array("ACTION", "ID", "ENTITY_TYPE", "ENTITY_CODE", "sessid"
							)
						))."';"
				)
			),
			"columns" => $arColumns,
			"editable" => false,
		);
	}
	//END -> Make Grid
}
//END -> Get Idea subscribtion

$this->IncludeComponentTemplate();
?>