<?php
namespace Bitrix\Socialnetwork\CommentAux;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Bitrix\Socialnetwork\Livefeed;

Loc::loadMessages(__FILE__);

abstract class Base
{
	const TYPE = 'BASE';
	const POST_TEXT = 'commentAuxBase';

	protected $params = array();
	protected $options = array();

	public static function className()
	{
		return get_called_class();
	}

	public static function getType()
	{
		return static::TYPE;
	}

	public static function getPostText()
	{
		return static::POST_TEXT;
	}

	public function getText()
	{
		return '';
	}

	public function canDelete()
	{
		return true;
	}

	public function getLiveParams()
	{
		$result = array();

		if (
			!empty($this->params['liveParamList'])
			&& is_array($this->params['liveParamList'])
		)
		{
			$result = $this->params['liveParamList'];
		}

		return $result;
	}

	public function setParams(array $params)
	{
		$this->params = $params;
	}

	public function setOptions(array $options)
	{
		$this->options = $options;
	}

	public function getOptions()
	{
		return $this->options;
	}

	public function checkRecalcNeeded($fields, $params)
	{
		return false;
	}

	public static function init($type = 'BASE', array $params = array(), array $options = array())
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

	public function sendLikeNotification()
	{
		return false;
	}

	public function getParamsFromFields($fields = array())
	{
		return array();
	}

	public function sendRatingNotification($fields = array(), $ratingVoteParams = array())
	{
		return false;
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
						"DIR" => (trim($site["DIR"]) <> '' ? $site["DIR"] : "/"),
						"SERVER_NAME" => (trim($site["SERVER_NAME"]) <> '' ? $site["SERVER_NAME"] : Option::get("main", "server_name", $_SERVER["HTTP_HOST"]))
					);
				}
			}

			$contentId = Livefeed\Provider::getContentId(array(
				"RATING_TYPE_ID" => $params['ratingEntityTypeId'],
				"RATING_ENTITY_ID" => $params['ratingEntityId']
			));

			if (!empty($contentId['ENTITY_TYPE']))
			{
				if ($liveFeedProvider = Livefeed\Provider::init(array(
					'ENTITY_TYPE' => $contentId['ENTITY_TYPE'],
					'ENTITY_ID' => $contentId['ENTITY_ID'],
					'SITE_ID' => (!empty($options['siteId']) ? $options['siteId'] : SITE_ID)
				)))
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
		}

		return $result;
	}
}