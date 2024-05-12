<?if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;

\Bitrix\Main\UI\Extension::load(array("popup", "ajax", "ui.buttons", "ui.forms"));

$gridSnippet = new Bitrix\Main\Grid\Panel\Snippet();
/** @var CDBResult $data */
$data = $arResult["DATA"];
/** @var string $gridId */
$gridId = $arParams["GRID_ID"];
$controlPanel = array(
	"GROUPS" => [["ITEMS" => [
		$gridSnippet->getEditButton(),
		$gridSnippet->getRemoveButton(),
		[
			"TYPE" => "DROPDOWN",
			"ID" => "base_action_select_".$gridId,
			"NAME" => "action_button_".$gridId,
			"ITEMS" => [
//region Group action buttons Activate, Deactivate, Delete
/*				[
					"NAME" => GetMessage("admin_lib_list_actions"),
					"VALUE" => "default",
					"ONCHANGE" => [ ["ACTION" => "RESET_CONTROLS",]]
				],
				[
					"NAME" => "Activate",
					"VALUE" => "activate",
					"ONCHANGE" => [
						["ACTION" => "RESET_CONTROLS" ],
						[
							"ACTION" => "CREATE",
							"DATA" => [$gridSnippet->getApplyButton([
								"ONCHANGE" => [[
										"ACTION" => \Bitrix\Main\Grid\Panel\Actions::CALLBACK,
										"DATA" => [["JS" => "BX.Main.gridManager.getInstanceById('".$gridId."').sendSelected()"]]
									]]
								]
							)]
						]
					]
				],
				[
					"NAME" => "Deactivate",
					"VALUE" => "deactivate",
					"ONCHANGE" => [
						["ACTION" => "RESET_CONTROLS"],
						[
							"ACTION" => "CREATE",
							"DATA" => [$gridSnippet->getApplyButton([
								"ONCHANGE" => [[
										"ACTION" => \Bitrix\Main\Grid\Panel\Actions::CALLBACK,
										"DATA" => [["JS" => "BX.Main.gridManager.getInstanceById('".$gridId."').sendSelected()"]]
									]]
								]
							)]
						]
					]
				],
*/
//endregion
				[
					"NAME" => Loc::getMessage("BUTTON_CLEAR_HTML"),
					"VALUE" => "clear_html",
					"ONCHANGE" => [
						["ACTION" => "RESET_CONTROLS"],
						[
							"ACTION" => "CREATE",
							"DATA" => [$gridSnippet->getApplyButton([
									"ONCHANGE" => [[
										"ACTION" => \Bitrix\Main\Grid\Panel\Actions::CALLBACK,
										"DATA" => [["JS" => "BX.Main.gridManager.getInstanceById('".$gridId."').sendSelected()"]]
									]]
								]
							)]
						]
					]
				]
			]
		]
	]]],
	"ITEMS" => array()
);
$rows = [];
$sortBy = \Bitrix\Forum\ForumSort::getList();
$sortOrder = ["ASC" => Loc::getMessage("FORUM_SORT_ORDER_ASC"), "DESC" => Loc::getMessage("FORUM_SORT_ORDER_DESC")];
foreach ($arResult["DATA"] as $row)
{
	$gridRow = array(
		"id" => $row["ID"],
		"actions" => array(
			array(
				"text" => Loc::getMessage("MAIN_ADMIN_MENU_EDIT"),
				"className" => "edit",
				"href" => "/bitrix/admin/forum_edit.php?ID={$row['ID']}"
			),
			array(
				"text" => Loc::getMessage("MAIN_ADMIN_MENU_DELETE"),
				"className" => "remove",
				"onclick" => "if(confirm('" . Loc::getMessage("FORUM_DELETE_CONFIRM") . "')) {BX.Main.gridManager.getInstanceById('{$gridId}').removeRow({$row["ID"]})}"
			)
		),
		"default_action" => array(
			"title" => Loc::getMessage("MAIN_ADMIN_MENU_EDIT"),
			"href" => "/bitrix/admin/forum_edit.php?ID={$row["ID"]}"
		),
		"columnClasses" => array(),
		"columns" => array(),
		"data" => array()
	);
	foreach ($row as $fieldId => $field)
	{
		if ($fieldId == "SITES")
		{
			$gridRow["columns"][$fieldId] = implode("<br/>", array_intersect_key($arParams["SITES"], $field));
		}
		else if ($fieldId == "PERMISSIONS")
		{
			$basePermission = isset($field[2]) ? $field[2] : \Bitrix\Forum\Permission::ACCESS_DENIED; // 2 is a guest
			$permissions = [2 => $arParams["USER_GROUPS"][2] .": ". $arParams["FORUM_PERMISSIONS"][$basePermission]];
			foreach ($field as $groupId => $permission)
			{
				if ($permission > $basePermission)
				{
					$permissions[$groupId] = (isset($arParams["USER_GROUPS"][$groupId]) ? $arParams["USER_GROUPS"][$groupId] : "")."[{$groupId}]: ".$arParams["FORUM_PERMISSIONS"][$permission];
				}
			}
			$gridRow["columns"][$fieldId] = implode("<br/>", $permissions);
		}
		else if ($fieldId == "FORUM_GROUP_ID")
		{
			if ($field > 0 && array_key_exists($field, $arParams["FORUM_GROUP_IDS"]))
			{
				$forumGroup = reset($arParams["FORUM_GROUPS"]);
				do {
					if ($forumGroup["ID"] == $field)
					{
						break;
					}
				} while ($forumGroup = next($arParams["FORUM_GROUPS"]));
				$groups = [$forumGroup["NAME"]];
				while ($prevForumGroup = prev($arParams["FORUM_GROUPS"]))
				{
					if ($forumGroup["DEPTH_LEVEL"] > $prevForumGroup["DEPTH_LEVEL"])
					{
						$forumGroup = $prevForumGroup;
						array_unshift($groups, $forumGroup["NAME"]);
						if ($forumGroup["DEPTH_LEVEL"] <= 1)
						{
							break;
						}
					}
				}
				$gridRow["columns"][$fieldId] = implode(" > ", $groups);
			}
		}
		else if ($fieldId == "DESCRIPTION")
		{
			$gridRow["columns"][$fieldId] = $field;
		}
		else if (
			in_array($fieldId, ["ACTIVE", "ASK_GUEST_EMAIL", "USE_CAPTCHA", "INDEXATION", "DEDUPLICATION", "MODERATION"]) ||
			mb_substr($fieldId, 0, 6) == "ALLOW_"
		)
		{
			$gridRow["columns"][$fieldId] = $field == "Y" ? GetMessage("admin_lib_list_yes") : GetMessage("admin_lib_list_no");
		}
		else if ($fieldId == "ORDER_BY")
		{
			if (array_key_exists($field, $sortBy))
			{
				$gridRow["columns"][$fieldId] = $sortBy[$field];
			}
		}
		else if ($fieldId == "ORDER_DIRECTION")
		{
			if (array_key_exists($field, $sortOrder))
			{
				$gridRow["columns"][$fieldId] = $sortOrder[$field];
			}
		}

		$gridRow["data"][$fieldId] = $field;
	}

	$rows[] = $gridRow;
}
?>
<div class="adm-toolbar-panel-container">
	<div class="adm-toolbar-panel-flexible-space">
		<?
		?><?$APPLICATION->includeComponent(
			"bitrix:main.ui.filter",
			"",
			[
				"FILTER_ID" => $arResult["FILTER_ID"],
				"GRID_ID" => $gridId,
				"FILTER" => $arResult["FILTER_FIELDS"],
				"ENABLE_LABEL" => true,
				"ENABLE_LIVE_SEARCH" => true
			],
			false,
			["HIDE_ICONS" => true]
		);?><?
		?>

	</div>
	<a class="ui-btn ui-btn-primary ui-btn-icon-add" href="/bitrix/admin/forum_edit.php?lang=<?=LANG?>"><?=Loc::getMessage("FORUM_ADD_BUTTON")?></a>
</div>
<?
?><?$APPLICATION->includeComponent(
	"bitrix:main.ui.grid",
	"",
	array(
		"GRID_ID" => $gridId,
		"COLUMNS" => [
			[
				"id" => "ID",
				"name" => "ID",
				"sort" => "ID",
				"default" => false
			],
			[
				"id" => "NAME",
				"name" => Loc::getMessage("FORUM_COLUMN_NAME"),
				"sticked_default" => true,
				"sticked" => true,
				"sort" => "NAME",
				"default" => true,
				"editable" => [
					"TYPE" => \Bitrix\Main\Grid\Editor\Types::TEXTAREA
				]
			],
			[
				"id" => "DESCRIPTION",
				"name" => Loc::getMessage("FORUM_COLUMN_DESCRIPTION"),
				"sort" => "DESCRIPTION",
				"default" => false,
				"editable" => [
					"TYPE" => \Bitrix\Main\Grid\Editor\Types::TEXTAREA
				]
			],
			[
				"id" => "FORUM_GROUP_ID",
				"name" => Loc::getMessage("FORUM_COLUMN_FORUM_GROUP_ID"),
				"sort" => "GROUP.LEFT_MARGIN",
				"default" => false,
				"editable" => [
					"TYPE" => \Bitrix\Main\Grid\Editor\Types::DROPDOWN,
					"items" => $arParams["FORUM_GROUP_IDS"]
				]
			],
			[
				"id" => "SORT",
				"name" => Loc::getMessage("FORUM_COLUMN_SORT"),
				"sort" => "SORT",
				"default" => true,
				"editable" => array(
						"TYPE" => \Bitrix\Main\Grid\Editor\Types::NUMBER
				)
			],
			[
				"id" => "ACTIVE",
				"name" => Loc::getMessage("FORUM_COLUMN_ACTIVE"),
				"sort" => "ACTIVE",
				"default" => false,
				"editable" => [
					"TYPE" => \Bitrix\Main\Grid\Editor\Types::CHECKBOX,
					"VALUE" => "Y"
				]
			],
			[
				"id" => "SITES",
				"name" => Loc::getMessage("FORUM_COLUMN_SITES"),
				"default" => true
			],
			[
				"id" => "PERMISSIONS",
				"name" => Loc::getMessage("FORUM_COLUMN_PERMISSIONS"),
				"default" => true,
			],
			[
				"id" => "MODERATION",
				"name" => Loc::getMessage("FORUM_COLUMN_MODERATION"),
				"sort" => "MODERATION",
				"default" => false,
				"editable" => [
					"TYPE" => \Bitrix\Main\Grid\Editor\Types::CHECKBOX,
					"VALUE" => "Y"
				]
			],
			[
				"id" => "INDEXATION",
				"name" => Loc::getMessage("FORUM_COLUMN_INDEXATION"),
				"sort" => "INDEXATION",
				"default" => false,
				"editable" => [
					"TYPE" => \Bitrix\Main\Grid\Editor\Types::CHECKBOX,
					"VALUE" => "Y"
				]
			],
			[
				"id" => "ORDER_BY",
				"name" => Loc::getMessage("FORUM_COLUMN_ORDER_BY"),
				"sort" => "ORDER_BY",
				"default" => false,
				"editable" => [
					"TYPE" => \Bitrix\Main\Grid\Editor\Types::DROPDOWN,
					"items" => \Bitrix\Forum\ForumSort::getList()
				]
			],
			[
				"id" => "ORDER_DIRECTION",
				"name" => Loc::getMessage("FORUM_COLUMN_ORDER_DIRECTION"),
				"sort" => "ORDER_DIRECTION",
				"default" => false,
				"editable" => [
					"TYPE" => \Bitrix\Main\Grid\Editor\Types::DROPDOWN,
					"items" => $sortOrder
				]
			],
			[
				"id" => "TOPICS",
				"name" => Loc::getMessage("FORUM_COLUMN_TOPICS"),
				"sort" => "TOPICS",
				"default" => false
			],
			[
				"id" => "POSTS",
				"name" => Loc::getMessage("FORUM_COLUMN_POSTS"),
				"sort" => "POSTS",
				"default" => false
			],
			[
				"id" => "POSTS_UNAPPROVED",
				"name" => Loc::getMessage("FORUM_COLUMN_UNAPPROVED_POSTS"),
				"sort" => "POSTS_UNAPPROVED"
			],

		],
		"ROWS" => $rows,
		"NAV_STRING" => "", //$navString,
		"NAV_PARAM_NAME" => "", //$navParamName,
		"NAV_OBJECT" => $arResult["NAV_OBJECT"],
		"CURRENT_PAGE" => $APPLICATION->GetCurPageParam(),
		"MESSAGES" => $arParams["ERRORS"] ?? null,

		"SORT" => array(
			"sort" => array("SORT" => "ASC")
		),

		"AJAX_MODE" => "Y",
	//	"AJAX_ID" => $arParams["ANSWER_PARAMS"]["AJAX_ID"],
		"AJAX_OPTION_STYLE" => "N",
		"AJAX_OPTION_JUMP" => "N",
		"AJAX_OPTION_HISTORY" => "N",

		"ENABLE_NEXT_PAGE" => false,
		"PAGE_SIZES" => array(),
		"ACTION_PANEL" => $controlPanel,
		"TOTAL_ROWS_COUNT" => $arResult["TOTAL_ROWS_COUNT"],
		"SHOW_CHECK_ALL_CHECKBOXES" => true,
		"SHOW_ROW_CHECKBOXES" => true,
		"SHOW_ROW_ACTIONS_MENU" => true,
		"SHOW_GRID_SETTINGS_MENU" => true,
		"SHOW_MORE_BUTTON" => true,
		"SHOW_NAVIGATION_PANEL" => false,
		"SHOW_PAGINATION" => false,
		"SHOW_SELECTED_COUNTER" => true,
		"SHOW_TOTAL_COUNTER" => true,
		"SHOW_PAGESIZE" => false,
		"ALLOW_COLUMNS_SORT" => true,
		"ALLOW_ROWS_SORT" => false,
		"ALLOW_COLUMNS_RESIZE" => true,
		"ALLOW_HORIZONTAL_SCROLL" => true,
		"ALLOW_SORT" => true,
		"ALLOW_PIN_HEADER" => true,
		"SHOW_ACTION_PANEL" => true,
		"ALLOW_VALIDATE" => false
	),
	false,
	array("HIDE_ICONS" => "Y")
);?>
