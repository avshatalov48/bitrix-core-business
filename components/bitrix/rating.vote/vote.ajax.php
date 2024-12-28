<?php

const PUBLIC_AJAX_MODE = true;
const NO_KEEP_STATISTIC = "Y";
const NO_AGENT_STATISTIC = "Y";
const NO_AGENT_CHECK = true;
const DisableEventsCheck = true;

use Bitrix\Main\Application;
use Bitrix\Main\Rating\Internal\Action;
use Bitrix\Main\Security\Sign\BadSignatureException;
use Bitrix\Main\Web\Json;

/** @global CMain $APPLICATION */
/** @global CUser $USER */

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$signedKey = (string) ($_POST['RATING_VOTE_KEY_SIGNED'] ?? '');
$entityId = (int) ($_POST['RATING_VOTE_ENTITY_ID'] ?? 0);
$entityTypeId = (string) ($_POST['RATING_VOTE_TYPE_ID'] ?? '');

if ($entityId && $entityTypeId !== '')
{
	$payloadValue = $entityTypeId . '-' . $entityId;

	$signer = new \Bitrix\Main\Security\Sign\TimeSigner();

	try
	{
		$isAccess = ($signedKey !== '' && $signer->unsign($signedKey, 'main.rating.vote') === $payloadValue);
	}
	catch(BadSignatureException $e)
	{
		$isAccess = false;
	}
}
else
{
	$isAccess = false;
}

if ($isAccess && check_bitrix_sessid())
{
	$currentUserId = ($USER->isAuthorized() ? (int)$USER->getId() : 0);

	$key = 'rating.lock.'.$currentUserId;
	if (!Application::getConnection()->lock($key))
	{
		CMain::FinalActions();
	}

	if (isset($_POST['RATING_VOTE_LIST']) && $_POST['RATING_VOTE_LIST'] === 'Y')
	{
		$APPLICATION->RestartBuffer();

		$params = [
			'ENTITY_TYPE_ID' => $entityTypeId,
			'ENTITY_ID' => $entityId,
			'LIST_PAGE' => (int)$_POST['RATING_VOTE_LIST_PAGE'],
			'LIST_LIMIT' => 20,
			'REACTION' => ($_POST['RATING_VOTE_REACTION'] ?? ''),
			'LIST_TYPE' => (
				isset($_POST['RATING_VOTE_LIST_TYPE'])
				&& $_POST['RATING_VOTE_LIST_TYPE'] === 'minus'
					? 'minus'
					: 'plus'
			),
			'PATH_TO_USER_PROFILE' => (
				!empty($_POST['PATH_TO_USER_PROFILE'])
					? $_POST['PATH_TO_USER_PROFILE']
					: '/people/user/#USER_ID#/'
			),
			'CURRENT_USER_ID' => $currentUserId,
			'CHECK_RIGHTS' => 'Y',
		];

		$voteList = Action::list($params);

		header('Content-Type: application/x-javascript; charset=' . LANG_CHARSET);
		echo Json::encode($voteList);
	}
	else if (isset($_POST['RATING_VOTE']) && $_POST['RATING_VOTE'] === 'Y')
	{
		$params = [
			'ENTITY_TYPE_ID' => $entityTypeId,
			'ENTITY_ID' => $entityId,
			'ACTION' => (
				in_array($_POST['RATING_VOTE_ACTION'], [ 'plus', 'minus', 'change', 'cancel' ])
					? $_POST['RATING_VOTE_ACTION']
					: 'list'
			),
			'REACTION' => ($_POST['RATING_VOTE_REACTION'] ?? ''),
			'RATING_RESULT' => ($_POST['RATING_RESULT'] === 'Y' ? $_POST['RATING_RESULT'] : 'N'),
			'REMOTE_ADDR' => $_SERVER['REMOTE_ADDR'],
			'CURRENT_USER_ID' => $currentUserId,
			'CHECK_RIGHTS' => 'Y',
		];

		$ratingVoteResult = CRatings::getRatingVoteResult($params['ENTITY_TYPE_ID'], $params['ENTITY_ID']);
		if (!empty($ratingVoteResult))
		{
			$params['TOTAL_VALUE'] = $ratingVoteResult['TOTAL_VALUE'];
			$params['TOTAL_VOTES'] = $ratingVoteResult['TOTAL_VOTES'];
			$params['TOTAL_POSITIVE_VOTES'] = $ratingVoteResult['TOTAL_POSITIVE_VOTES'];
			$params['TOTAL_NEGATIVE_VOTES'] = $ratingVoteResult['TOTAL_NEGATIVE_VOTES'];
			$params['USER_HAS_VOTED'] = $ratingVoteResult['USER_HAS_VOTED'];
			$params['USER_VOTE'] = $ratingVoteResult['USER_VOTE'];
		}
		else
		{
			$params['TOTAL_VALUE'] = 0;
			$params['TOTAL_VOTES'] = 0;
			$params['TOTAL_POSITIVE_VOTES'] = 0;
			$params['TOTAL_NEGATIVE_VOTES'] = 0;
			$params['USER_HAS_VOTED'] = 'N';
			$params['USER_VOTE'] = '0';
		}

		$voteList = Action::vote($params);
		if (!empty($voteList))
		{
			header('Content-Type: application/x-javascript; charset=' . LANG_CHARSET);
			echo Json::encode($voteList);
		}
	}
	else if (isset($_POST['RATING_RESULT']) && $_POST['RATING_RESULT'] === 'Y')
	{
		header('Content-Type: application/x-javascript; charset=' . LANG_CHARSET);
		echo Json::encode(Action::getVoteResult($entityTypeId, $entityId));
	}

	Application::getConnection()->unlock($key);
}

CMain::FinalActions();
