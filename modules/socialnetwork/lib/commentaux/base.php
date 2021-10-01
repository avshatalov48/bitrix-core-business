<?php

namespace Bitrix\Socialnetwork\CommentAux;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Bitrix\Socialnetwork\Livefeed;

Loc::loadMessages(__FILE__);

abstract class Base
{
	public const TYPE = 'BASE';
	public const POST_TEXT = 'commentAuxBase';

	protected $params = [];
	protected $options = [];

	final public static function className(): string
	{
		return static::class;
	}

	final public static function getType(): string
	{
		return static::TYPE;
	}

	final public static function getPostText(): string
	{
		return static::POST_TEXT;
	}

	public function getText(): string
	{
		return '';
	}

	public function canDelete(): bool
	{
		return true;
	}

	final public function getLiveParams(): array
	{
		$result = [];

		if (
			!empty($this->params['liveParamList'])
			&& is_array($this->params['liveParamList'])
		)
		{
			$result = $this->params['liveParamList'];
		}

		return $result;
	}

	final public function setParams(array $params): void
	{
		$this->params = $params;
	}

	final public function setOptions(array $options): void
	{
		$this->options = $options;
	}

	final public function getOptions(): array
	{
		return $this->options;
	}

	public function checkRecalcNeeded($fields, $params): bool
	{
		return false;
	}

	public static function init($type = 'BASE', array $params = [], array $options = [])
	{
		static $extranet = null;
		static $extranetSite = null;
		static $handlerManager = null;

		if ($handlerManager === null)
		{
			$handlerManager = new HandlerManager();
		}

		/** @var bool|object $handler */
		if ($handler = $handlerManager->getHandlerByType($type))
		{
			$handler->setParams($params);

			if (!isset($options['extranet']))
			{
				if ($extranet === null)
				{
					$extranet = Loader::includeModule('extranet');
				}
				$options['extranet'] = $extranet;
			}
			if (!isset($options['extranetSite']))
			{
				if ($extranetSite === null)
				{
					$extranetSite = ($extranet ? \CExtranet::getExtranetSiteID() : false);
				}
				$options['extranetSite'] = $extranetSite;
			}

			$handler->setOptions($options);
		}

		return $handler;
	}

	final public static function findProvider($fields = array(), $options = array())
	{
		static $handlerManager = null;

		$handler = false;
		$needSetParams = true;
		if (
			isset($options['needSetParams'])
			&& $options['needSetParams'] === false
		)
		{
			$needSetParams = false;
		}

		if (
			is_array($fields)
			&& isset($fields['POST_TEXT'])
		)
		{
			if ($handlerManager === null)
			{
				$handlerManager = new HandlerManager();
			}

			/** @var bool|object $handler */
			if ($handler = $handlerManager->getHandlerByPostText($fields['POST_TEXT']))
			{
				$handler->setOptions($options);

				if ($needSetParams)
				{
					$params = $handler->getParamsFromFields($fields);
					if (!empty($params))
					{
						$handler->setParams($params);
					}
					else
					{
						$handler = false;
					}
				}
			}
		}

		return $handler;
	}

	public function getParamsFromFields($fields = []): array
	{
		return [];
	}

	public function sendRatingNotification($fields = [], $ratingVoteParams = []): void
	{
		$userId = (
			is_array($ratingVoteParams)
			&& isset($ratingVoteParams['OWNER_ID'])
				? (int)$ratingVoteParams['OWNER_ID']
				: 0
		);

		if (
			!$this->checkRatingNotificationData($userId, $fields)
			|| !$this->setRatingNotificationParams($fields)
		)
		{
			return;
		}

		$followValue = $this->getRatingNotificationFollowValue($userId, $ratingVoteParams, $fields);

		if ($followValue === 'N')
		{
			return;
		}

		$ratingVoteParams['ENTITY_LINK'] = $this->getRatingCommentLink([
			'commentId' => $fields['ID'],
			'commentAuthorId' => $ratingVoteParams['OWNER_ID'],
			'ratingEntityTypeId' => $ratingVoteParams['ENTITY_TYPE_ID'],
			'ratingEntityId' => $ratingVoteParams['ENTITY_ID'],
		]);

		$ratingVoteParams['ENTITY_PARAM'] = 'COMMENT';
		$ratingVoteParams['ENTITY_MESSAGE'] = $this->getRatingNotificationEntityMessage();
		$ratingVoteParams['ENTITY_TITLE'] = $ratingVoteParams['ENTITY_MESSAGE'];

		$messageFields = [
			'MESSAGE_TYPE' => IM_MESSAGE_SYSTEM,
			'TO_USER_ID' => $userId,
			'FROM_USER_ID' => (int)$ratingVoteParams['USER_ID'],
			'NOTIFY_TYPE' => IM_NOTIFY_FROM,
			'NOTIFY_MODULE' => 'main',
			'NOTIFY_EVENT' => 'rating_vote',
			'NOTIFY_TAG' => $this->getRatingNotificationNotigyTag($ratingVoteParams, $fields),
			'NOTIFY_MESSAGE' => \CIMEvent::getMessageRatingVote($ratingVoteParams),
			'NOTIFY_MESSAGE_OUT' => \CIMEvent::getMessageRatingVote($ratingVoteParams, true),
		];

		\CIMNotify::add($messageFields);
	}

	protected function setRatingNotificationParams(array $fields = []): bool
	{
		$params = $this->getParamsFromFields($fields);
		if (empty($params))
		{
			return false;
		}

		$this->setParams($params);

		return true;
	}

	protected function checkRatingNotificationData(int $userId = 0, array $fields = []): bool
	{
		return (
			$userId > 0
			&& is_array($fields)
			&& Loader::includeModule('im')
		);
	}

	protected function getRatingNotificationEntityMessage(): string
	{
		return $this->getText();
	}

	protected function getRatingNotificationNotigyTag(array $ratingVoteParams = [], array $fields = []): string
	{
		return '';
	}

	protected function getRatingNotificationFollowValue(int $userId = 0, array $ratingVoteParams = [], array $fields = [])
	{
		return \CSocNetLogFollow::getExactValueByRating(
			$userId,
			$ratingVoteParams['ENTITY_TYPE_ID'],
			$ratingVoteParams['ENTITY_ID']
		);
	}

	final protected function getRatingCommentLink($params)
	{
		$result = '';

		if (Loader::includeModule('im'))
		{
			$options = $this->options;

			$commentAuthorId = (!empty($params['commentAuthorId']) && (int)$params['commentAuthorId'] > 0 ? (int)$params['commentAuthorId'] : 0);

			$siteList = $intranetSiteId = $extranetSiteId = false;

			if (Loader::includeModule('extranet'))
			{
				$siteList = array();
				$intranetSiteId = \CExtranet::getExtranetSiteID();
				$extranetSiteId = \CSite::getDefSite();
				$res = \CSite::getList("sort", "desc", array("ACTIVE" => "Y"));
				while($site = $res->fetch())
				{
					$siteList[$site["ID"]] = array(
						"DIR" => (trim($site["DIR"]) !== '' ? $site["DIR"] : '/'),
						"SERVER_NAME" => (trim($site["SERVER_NAME"]) !== '' ? $site["SERVER_NAME"] : Option::get("main", "server_name", $_SERVER["HTTP_HOST"]))
					);
				}
			}

			$contentId = Livefeed\Provider::getContentId([
				'RATING_TYPE_ID' => $params['ratingEntityTypeId'],
				'RATING_ENTITY_ID' => $params['ratingEntityId'],
			]);

			if (
				!empty($contentId['ENTITY_TYPE'])
				&& ($liveFeedProvider = Livefeed\Provider::init([
					'ENTITY_TYPE' => $contentId['ENTITY_TYPE'],
					'ENTITY_ID' => $contentId['ENTITY_ID'],
					'SITE_ID' => (!empty($options['siteId']) ? $options['siteId'] : SITE_ID)
				]))
			)
			{
				$liveFeedProvider->initSourceFields();
				$originalLink = $liveFeedProvider->getLiveFeedUrl();

				$result = \CIMEvent::getMessageRatingEntityURL(
					$originalLink,
					$commentAuthorId,
					$siteList,
					$intranetSiteId,
					$extranetSiteId
				);
			}
		}

		return $result;
	}
}