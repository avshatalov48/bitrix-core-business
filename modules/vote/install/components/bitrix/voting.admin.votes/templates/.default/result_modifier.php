<?php

use Bitrix\Main\Localization\Loc;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var \Bitrix\Vote\Vote $votes */
/** @global array $arResult */
/** @global array $arParams */
$votes = $arResult["ITEMS"];
$channels = $arResult["CHANNELS"];
$gridSnippet = new Bitrix\Main\Grid\Panel\Snippet();

foreach ($votes as $voteCollection)
{
	$vote = $voteCollection->collectValues();
	$lamp = $vote["LAMP"];
	if ($vote["LAMP"] == "yellow")
	{
		$vote["LAMP"] = ($vote["ID"] == CVote::GetActiveVoteId($vote["CHANNEL_ID"]) ? "green" : "red");
	}
	if ($vote["LAMP"] == "green")
	{
		$lamp = "<div class=\"lamp-green\" title=\"".GetMessage("VOTE_LAMP_ACTIVE")."\"></div>";
	}
	elseif ($vote["LAMP"] == "red")
	{
		$today = new \Bitrix\Main\Type\DateTime();
		$title = GetMessage("VOTE_ACTIVE_RED_LAMP");
		if ($vote["ACTIVE"] != "Y")
		{
			$title = GetMessage("VOTE_NOT_ACTIVE");
		}
		else if ($vote["DATE_END"] < $today)
		{
			$title = GetMessage("VOTE_ACTIVE_RED_LAMP_EXPIRED");
		}
		else if ($vote["DATE_START"] > $today)
		{
			$title = GetMessage("VOTE_ACTIVE_RED_LAMP_UPCOMING");
		}
		$lamp = "<div class=\"lamp-red\" title=\"{$title}\"></div>";
	}
	$columns = [
		'ID' => '<a href="vote_edit.php?lang='.LANGUAGE_ID.'&id='.$voteCollection->getId().'">'.
			htmlspecialcharsbx($voteCollection->getId()).
			'</a>',
		'LAMP' => $lamp,
		'TITLE' => $vote['TITLE'],
		'DATE_START' => $vote['DATE_START'],
		'DATE_END' => $vote['DATE_END'],
		'AUTHOR_ID' => \CUser::FormatName(
			\CSite::GetNameFormat(),
			$vote['AUTHOR'],
			true,
			true
		),
		'CHANNEL_ID' => $channels[$vote['CHANNEL_ID']] ?? $vote['CHANNEL_ID'],
		'ACTIVE' => $vote['ACTIVE'] ? Loc::getMessage('MAIN_YES') : Loc::getMessage('MAIN_NO'),
		'C_SORT' => $vote['C_SORT'],
		'COUNTER' => $vote['COUNTER'],
	];
	$data = [
		'ID' => $vote['ID'],
		'LAMP' => $vote['LAMP'],
		'TITLE' => $vote['TITLE'],
		'DATE_START' => $vote['DATE_START'],
		'DATE_END' => $vote['DATE_END'],
		'AUTHOR_ID' => $vote['AUTHOR_ID'],
		'CHANNEL_ID' => $vote['CHANNEL_ID'],
		'ACTIVE' => $vote['ACTIVE'] ? 'Y' : 'N',
		'C_SORT' => $vote['C_SORT'],
		'COUNTER' => $vote['COUNTER'],
	];

	if ($vote["AUTHOR_ID"] > 0)
	{
		$columns["AUTHOR_ID"] =
			"<a href=\"user_edit.php?lang=".LANGUAGE_ID."&ID={$vote["AUTHOR_ID"]}\">
				[{$vote["AUTHOR_ID"]}]&nbsp;{$columns["AUTHOR_ID"]}</a>"
		;
	}

	if ($columns["COUNTER"] > 0)
	{
		$columns["COUNTER"] = "<a href=\"vote_user_votes_table.php?lang=".LANGUAGE_ID."&VOTE_ID={$columns["ID"]}\" title=\"".GetMessage("VOTE_VOTES_TITLE")."\">{$columns["COUNTER"]}</a>&nbsp;".
			" [ <a href=\"vote_user_votes.php?lang=".LANGUAGE_ID."&find_vote_id={$columns["ID"]}&export=xls&filename=vote{$columns["ID"]}.xls\">xls</a> ]"
		;
	}

	$arResult["ROWS"][] = [
		"id" => $voteCollection->getId(),
		"columns" => $columns,
		"data" => $data,
		"actions" => array(
			array(
				"text" => Loc::getMessage("MAIN_ADMIN_MENU_EDIT"),
				"className" => "edit",
				"href" => "/bitrix/admin/vote_edit.php?ID={$vote['ID']}"
			),
			$gridSnippet->getRemoveAction(),
			array(
				"text" => Loc::getMessage("MAIN_ADMIN_MENU_DELETE"),
				"className" => "remove",
				"onclick" => "if(confirm('" . GetMessage("VOTE_CONFIRM_DEL_QUESTION") . "')) {BX.Main.gridManager.getInstanceById('{$arParams["GRID_ID"]}').removeRow({$vote['ID']})}"
			)
		),
		"default_action" => array(
			"title" => Loc::getMessage("MAIN_ADMIN_MENU_EDIT"),
			"href" => "/bitrix/admin/vote_edit.php?ID={$vote['ID']}"
		),
		'editableColumns' => [
			'ID' => false,
			'LAMP' => false,
			'COUNTER' => false,
			'AUTHOR_ID' => false,
			'DATE_CREATE' => true,
		],
	];
}
