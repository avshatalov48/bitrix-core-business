<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2022 Bitrix
 */

namespace Bitrix\Main\Controller;

use Bitrix\Main;
use Bitrix\Main\Application;
use Bitrix\Main\Rating\Internal\Action;
use Bitrix\Main\Security\Sign\BadSignatureException;

class Rating extends Main\Engine\Controller
{
	private const LOCK_KEY_PREFIX = 'rating.lock.';

	public function configureActions(): array
	{
		$configureActions = parent::configureActions();

		$configureActions['list'] = [
			'-prefilters' => [
				Main\Engine\ActionFilter\Authentication::class,
			]
		];

		return $configureActions;
	}

	public function voteAction(array $params = []): ?array
	{
		$signedKey = (string) ($params['RATING_VOTE_KEY_SIGNED'] ?? '');
		$entityId = (int) ($params['RATING_VOTE_ENTITY_ID'] ?? 0);
		$entityTypeId = (string) ($params['RATING_VOTE_TYPE_ID'] ?? '');

		$payloadValue = $entityTypeId . '-' . $entityId;

		$signer = new \Bitrix\Main\Security\Sign\TimeSigner();
		
		$accessDenied = false;
		try
		{
			if (
				$signedKey === ''
				|| $signer->unsign($signedKey, 'main.rating.vote') !== $payloadValue
			)
			{
				$accessDenied = true;
			}
		}
		catch(BadSignatureException $e)
		{
			$accessDenied = true;
		}
		
		if ($accessDenied)
		{
			$this->addError(new Main\Error('Access denied'));

			return null;
		}

		$key = self::LOCK_KEY_PREFIX.$this->getCurrentUser()->getId();

		if (!Application::getConnection()->lock($key))
		{
			$this->addError(new Main\Error('Request already exists', 'ERR_PARAMS'));
			return null;
		}

		$action = (string)($params['RATING_VOTE_ACTION'] ?? '');
		$reaction = (string)($params['RATING_VOTE_REACTION'] ?? '');

		if (
			$entityTypeId === ''
			|| $entityId <= 0
		)
		{
			$this->addError(new Main\Error('Incorrect data', 'ERR_PARAMS'));
			return null;
		}

		$ratingParams = [
			'ENTITY_TYPE_ID' => $entityTypeId,
			'ENTITY_ID' => $entityId,
			'ACTION' => (in_array($action, [ 'plus', 'minus', 'change', 'cancel' ]) ? $action : 'list'),
			'REACTION' => $reaction,
			'RATING_RESULT' => 'N',
			'REMOTE_ADDR' => $_SERVER['REMOTE_ADDR'],
			'CURRENT_USER_ID' => $this->getCurrentUser()->getId(),
			'CHECK_RIGHTS' => 'Y',
		];

		$ratingVoteResult = \CRatings::getRatingVoteResult($ratingParams['ENTITY_TYPE_ID'], $ratingParams['ENTITY_ID']);
		if (!empty($ratingVoteResult))
		{
			$ratingParams['TOTAL_VALUE'] = $ratingVoteResult['TOTAL_VALUE'];
			$ratingParams['TOTAL_VOTES'] = $ratingVoteResult['TOTAL_VOTES'];
			$ratingParams['TOTAL_POSITIVE_VOTES'] = $ratingVoteResult['TOTAL_POSITIVE_VOTES'];
			$ratingParams['TOTAL_NEGATIVE_VOTES'] = $ratingVoteResult['TOTAL_NEGATIVE_VOTES'];
			$ratingParams['USER_HAS_VOTED'] = $ratingVoteResult['USER_HAS_VOTED'];
			$ratingParams['USER_VOTE'] = $ratingVoteResult['USER_VOTE'];
		}
		else
		{
			$ratingParams['TOTAL_VALUE'] = 0;
			$ratingParams['TOTAL_VOTES'] = 0;
			$ratingParams['TOTAL_POSITIVE_VOTES'] = 0;
			$ratingParams['TOTAL_NEGATIVE_VOTES'] = 0;
			$ratingParams['USER_HAS_VOTED'] = 'N';
			$ratingParams['USER_VOTE'] = '0';
		}

		$voteList = Action::vote($ratingParams);
		if (empty($voteList))
		{
			$this->addError(new Main\Error('Cannot do vote', 'CANNOT_VOTE'));
		}

		Application::getConnection()->unlock($key);

		return $voteList;
	}

	public function listAction(array $params = []): ?array
	{
		$signedKey = (string) ($params['RATING_VOTE_KEY_SIGNED'] ?? '');
		$entityId = (int) ($params['RATING_VOTE_ENTITY_ID'] ?? 0);
		$entityTypeId = (string) ($params['RATING_VOTE_TYPE_ID'] ?? '');

		$payloadValue = $entityTypeId . '-' . $entityId;

		$signer = new \Bitrix\Main\Security\Sign\TimeSigner();
		
		$accessDenied = false;
		try
		{
			if (
				$signedKey === ''
				|| $signer->unsign($signedKey, 'main.rating.vote') !== $payloadValue
			)
			{
				$accessDenied = true;
			}
		}
		catch(BadSignatureException $e)
		{
			$accessDenied = true;
		}
		
		if ($accessDenied)
		{
			$this->addError(new Main\Error('Access denied'));
			
			return null;
		}

		$page = (int)($params['RATING_VOTE_LIST_PAGE'] ?? 1);
		$listType = (
			isset($params['RATING_VOTE_LIST_TYPE'])
			&& $params['RATING_VOTE_LIST_TYPE'] === 'minus'
				? 'minus'
				: 'plus'
		);
		$reaction = (string)($params['RATING_VOTE_REACTION'] ?? '');
		$pathToUserProfile = (string)($params['PATH_TO_USER_PROFILE'] ?? '/people/user/#USER_ID#/');

		if (
			$entityTypeId === ''
			|| $entityId <= 0
		)
		{
			$this->addError(new Main\Error('Incorrect data', 'ERR_PARAMS'));
			return null;
		}

		return Action::list([
			'ENTITY_TYPE_ID' => $entityTypeId,
			'ENTITY_ID' => $entityId,
			'LIST_PAGE' => $page,
			'LIST_LIMIT' => 20,
			'REACTION' => $reaction,
			'LIST_TYPE' => $listType,
			'PATH_TO_USER_PROFILE' => $pathToUserProfile,
			'CURRENT_USER_ID' => $this->getCurrentUser()->getId(),
			'CHECK_RIGHTS' => 'Y',
		]);
	}
}
