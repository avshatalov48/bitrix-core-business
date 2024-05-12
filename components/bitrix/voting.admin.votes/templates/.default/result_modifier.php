<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}
/**
 * @var \CBitrixComponentTemplate $this
 */

/** @var \Bitrix\Vote\Vote $votes */
/** @global array $arResult */
/** @global array $arParams */
$votes = $arResult['ITEMS'];
$channels = $arResult['CHANNELS'];
$gridSnippet = new Bitrix\Main\Grid\Panel\Snippet();

foreach ($votes as $voteCollection)
{
	$vote = $voteCollection->collectValues();
	$lamp = $vote['LAMP'];
	if ($vote['LAMP'] == 'yellow')
	{
		$vote['LAMP'] = ($vote['ID'] == CVote::GetActiveVoteId($vote['CHANNEL_ID']) ? 'green' : 'red');
	}
	if ($vote['LAMP'] == 'green')
	{
		$lamp = '<div class="lamp-green" title="' . Loc::getMessage('VOTE_LAMP_ACTIVE') . '"></div>';
	}
	elseif ($vote['LAMP'] == 'red')
	{
		$today = new \Bitrix\Main\Type\DateTime();
		$title = Loc::getMessage('VOTE_ACTIVE_RED_LAMP');
		if ($vote['ACTIVE'] != 'Y')
		{
			$title = Loc::getMessage('VOTE_NOT_ACTIVE');
		}
		else if ($vote['DATE_END'] < $today)
		{
			$title = Loc::getMessage('VOTE_ACTIVE_RED_LAMP_EXPIRED');
		}
		else if ($vote['DATE_START'] > $today)
		{
			$title = Loc::getMessage('VOTE_ACTIVE_RED_LAMP_UPCOMING');
		}
		$lamp = '<div class="lamp-red" title="' . $title . '"></div>';
	}
	$columns = [
		'ID' => str_replace(
			['#LANGUAGE_ID#', '#VOTE_ID#'],
			[LANGUAGE_ID, htmlspecialcharsbx($voteCollection->getId())],
			'<a href="vote_edit.php?lang=#LANGUAGE_ID#&ID=#VOTE_ID#">#VOTE_ID#</a>',
		),
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

	if ($vote['AUTHOR_ID'] > 0)
	{
		$columns['AUTHOR_ID'] = str_replace(
			['#LANGUAGE_ID#', '#AUTHOR_ID#', '#AUTHOR_NAME#'],
			[LANGUAGE_ID, $vote['AUTHOR_ID'], $columns['AUTHOR_ID']],
			'<a href="user_edit.php?lang=#LANGUAGE_ID#&ID=#AUTHOR_ID#">[#AUTHOR_ID#]&nbsp;#AUTHOR_NAME#</a>',
		);
	}

	if ($columns['COUNTER'] > 0)
	{
		$uriMain = (new Main\Web\Uri('vote_user_votes_table.php'))->addParams([
			'lang' => LANGUAGE_ID,
			'VOTE_ID' => $vote['ID'],
		]);
		$uriExport = (new Main\Web\Uri('vote_user_votes.php'))->addParams([
			'lang' => LANGUAGE_ID,
			'find_vote_id' => $vote['ID'],
			'export' => 'xls',
			'filename' => 'vote' . $vote['ID'] . '.xls',
		]);

		$columns['COUNTER'] = str_replace(
			['#URL_MAIN#', '#TITLE#', '#COUNTER#', '#URL_EXPORT#'],
			[$uriMain->getUri(), Loc::getMessage('VOTE_VOTES_TITLE'), $columns['COUNTER'], $uriExport->getUri()],
			'<a href="#URL_MAIN#" title="#TITLE#">#COUNTER#</a>&nbsp; [ <a href="#URL_EXPORT#">xls</a> ]',
		);
	}
	$action = [
		[
			'text' => Loc::getMessage('VOTE_RESULTS'),
			'href' => '/bitrix/admin/vote_results.php?lang=' . LANGUAGE_ID . '&VOTE_ID=' . $vote['ID']
		]
	];

	if (array_key_exists($vote['CHANNEL_ID'], $arResult['ADMIN_CHANNELS']))
	{
		$actions = [
			[
				'text' => Loc::getMessage('MAIN_ADMIN_MENU_EDIT'),
				'className' => 'menu-popup-item-edit',
				'href' => '/bitrix/admin/vote_edit.php?ID=' . $vote['ID']
			],
			[
				'text' => Loc::getMessage('VOTE_COPY'),
				'className' => 'menu-popup-item-copy',
				'href' => '/bitrix/admin/vote_edit.php?lang=' . LANGUAGE_ID . '&COPY_ID=' . $vote['ID'] . '&CHANNEL_ID=' . $vote['CHANNEL_ID']
			],
			[
				'text' => Loc::getMessage('MAIN_ADMIN_MENU_DELETE'),
				'className' => 'menu-popup-item-delete',
				'onclick' => 'if(confirm("' . Loc::getMessage('VOTE_CONFIRM_DEL_VOTE') . '")) '.
					'{BX.Main.gridManager.getInstanceById("' . $arParams['GRID_ID'] . '").removeRow(' .  $vote['ID'] . ')}'
			]
		];

		if ($vote['COUNTER'] > 0)
		{
			$actions[] = ['SEPARATOR' => true];

			$actions[] = [
				'text' => Loc::getMessage('VOTE_RESET_NULL'),
				'onclick' => 'if(confirm("' . Loc::getMessage('VOTE_CONFIRM_RESET_VOTE') . '"))' .
					'{window.location="/bitrix/admin/vote_list.php?'
					.'lang=' . LANGUAGE_ID.'&find_channel_id='.$vote['CHANNEL_ID']
					.'&reset_id='. $vote['ID']. '&'.bitrix_sessid_get().'"}',
			];
			$actions[] = [
				'text' => Loc::getMessage('VOTE_RESULTS'),
				'href' => '/bitrix/admin/vote_results.php?lang=' . LANGUAGE_ID . '&VOTE_ID=' . $vote['ID'],
			];
			$actions[] = [
				'text' => Loc::getMessage('VOTE_VOTES_TITLE'),
				'href' => '/bitrix/admin/vote_user_votes_table.php?lang=' . LANGUAGE_ID . '&VOTE_ID=' . $vote['ID'],
			];
		}
		$actions[] = ['SEPARATOR' => true];
		$actions[] = [
			'text' => Loc::getMessage('VOTE_PREVIEW'),
			'href' => '/bitrix/admin/vote_preview.php?lang=' . LANGUAGE_ID . '&VOTE_ID=' . $vote['ID']
		];
		$actions[] = [
			'text' => Loc::getMessage('VOTE_QUESTIONS'),
			'href' => '/bitrix/admin/vote_question_list.php?lang=' . LANGUAGE_ID . '&VOTE_ID=' . $vote['ID']
		];
	}


	$arResult['ROWS'][] = [
		'id' => $voteCollection->getId(),
		'columns' => $columns,
		'data' => $data,
		'actions' => $actions,
		'default_action' => array(
			'title' => Loc::getMessage('MAIN_ADMIN_MENU_EDIT'),
			'href' => '/bitrix/admin/vote_edit.php?ID=' . $vote['ID'],
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
