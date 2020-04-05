<?
IncludeModuleLangFile(__FILE__);
$rights = $APPLICATION->GetGroupRight("vote");
if ($rights == "D")
	return false;
$menuResults1 = array();
if (CModule::IncludeModule('vote'))
{
	$db_res = \Bitrix\Vote\Channel::getList(array(
		'select' => array("*"),
		'filter' => ($rights < "W" ? array(
			"ACTIVE" => "Y",
			"HIDDEN" => "N",
			">PERMISSION.PERMISSION" => 1,
			"PERMISSION.GROUP_ID" => $USER->GetUserGroupArray()
		) : array()),
		'order' => array(
			'TITLE' => 'ASC'
		),
		'group' => array("ID")
	));
	if (($arChannel = $db_res->fetch()) && $arChannel)
	{
		$db_res2 = \Bitrix\Vote\Channel::getList(array(
			'select' => array("*"),
			'filter' => ($rights < "W" ? array(
				"ACTIVE" => "Y",
				"HIDDEN" => "N",
				">=PERMISSION.PERMISSION" => 4,
				"PERMISSION.GROUP_ID" => $USER->GetUserGroupArray()
			) : array()),
			'order' => array(
				'TITLE' => 'ASC'
			),
			'group' => array("ID")
		));
		$channels = array();
		while ($res = $db_res2->fetch())
		{
			$channels[$res["ID"]] = $res;
		}

		do
		{
			$menuChannel1 = array(
				"text" => htmlspecialcharsEx($arChannel["TITLE"]),
				"url" => "vote_list.php?lang=".LANGUAGE_ID."&find_channel_id=".$arChannel['ID'],
				"module_id" => "vote",
				"page_icon" => "vote_page_icon",
				"items_id" => "vote_channel_".$arChannel["ID"],
				"more_url" => Array(
					"vote_edit.php?lang=".LANGUAGE_ID."&CHANNEL_ID=".$arChannel["ID"]
				),
				"items" => array(),
				"dynamic" => true
			);
			if (method_exists($this, "IsSectionActive") &&
				($this->IsSectionActive("vote_channel_".$arChannel["ID"]) ||
					$this->IsSectionActive("menu_vote_channels")))
			{
				$dbRes = \Bitrix\Vote\VoteTable::getList(array(
					"select" => array("ID", "TITLE"),
					"filter" => array("CHANNEL_ID" => $arChannel["ID"]),
					"order" => array("ID" => "DESC"),
					"limit" => 50));
				while ($row = $dbRes->fetch())
				{
					$menuChannel1["items"][] = array(
						"items_id" => "vote_item_".$row['ID'],
						"text" => htmlspecialcharsEx($row["TITLE"]),
						"title" => GetMessage("VOTE_MENU_POLL_DESCRIPTION").'\''.htmlspecialcharsEx($row["TITLE"]).'\'',
						"module_id" => "vote",
						"url" => (array_key_exists($arChannel["ID"], $channels) ? "vote_edit.php?lang=".LANGUAGE_ID."&ID=".$row['ID'] : "vote_results.php?lang=".LANGUAGE_ID."&VOTE_ID=".$row['ID']),
						"more_url" => Array(
							"vote_edit.php?lang=".LANGUAGE_ID."&COPY_ID=".$row['ID'],
							"vote_question_list.php?lang=".LANGUAGE_ID."&VOTE_ID=".$row['ID'],
							"vote_question_edit.php?lang=".LANGUAGE_ID."&VOTE_ID=".$row['ID'],
							"vote_results.php?lang=".LANGUAGE_ID."&VOTE_ID=".$row['ID'],
							"vote_preview.php?lang=".LANGUAGE_ID."&VOTE_ID=".$row['ID'],
							"vote_user_votes_table.php?lang=".LANGUAGE_ID."&VOTE_ID=".$row['ID'],
							"vote_user_votes.php?lang=".LANGUAGE_ID."&find_vote_id=".$row['ID']."&set_filter=Y",
							"vote_user_results_table.php?lang=".LANGUAGE_ID."&VOTE_ID=".$row['ID']
						),
					);
				}
			}
			$menuResults1[] = $menuChannel1;
		} while (($arChannel = $db_res->fetch()) && $arChannel);
	}
}

$aMenu = array(
	"parent_menu" => "global_menu_services",
	"section" => "vote",
	"sort" => 100,
	"module_id" => "vote",
	"text" => GetMessage("VOTE_MENU_MAIN"),
	"title" => GetMessage("VOTE_MENU_MAIN_TITLE"),
	"icon" => "vote_menu_icon",
	"page_icon" => "vote_page_icon",
	"items_id" => "menu_vote",
	"items" => array(
		array(
			"text" => GetMessage("VOTE_MENU_VOTE"),
			"items_id" => "menu_vote_channels",
		//	"url" => "vote_list.php?lang=".LANGUAGE_ID,
			"more_url" => Array(
				"vote_edit.php",
				"vote_question_list.php",
				"vote_question_edit.php",
				"vote_results.php",
				"vote_preview.php",
				"vote_user_votes_table.php",
				"vote_user_results_table.php"
			),
			"title" => GetMessage("VOTE_MENU_VOTE_ALT"),
			"items" => $menuResults1
		)
	)
);
if ($rights >= "W")
	$aMenu["items"][] = array(
		"text" => GetMessage("VOTE_MENU_ADDITIONAL"),
		"title" => "",
		"items_id" => "menu_vote_settings",
		"more_url" => Array(
			"vote_channel_edit.php",
			"vote_channel_list.php"
		),
		"items" => array(
			array(
				"text" => GetMessage("VOTE_MENU_CHANNEL"),
				"url" => "vote_channel_list.php?lang=".LANGUAGE_ID,
				"title" => GetMessage("VOTE_MENU_CHANNEL_ALT"),
				"more_url" => Array(
					"vote_channel_edit.php"
				)
			),
			array(
				"text" => GetMessage("VOTE_MENU_USER"),
				"url" => "vote_user_list.php?lang=".LANGUAGE_ID,
				"more_url" => Array(),
				"title" => GetMessage("VOTE_MENU_USER_ALT")
			)
		),
	);
return $aMenu;
?>