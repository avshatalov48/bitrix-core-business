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

			$commentId = (!empty($params['commentId']) && intval($params['commentId']) > 0 ? intval($params['commentId']) : 0);
			$commentAuthorId = (!empty($params['commentAuthorId']) && intval($params['commentAuthorId']) > 0 ? intval($params['commentAuthorId']) : 0);

			$siteList = $intranetSiteId = $extranetSiteId = false;

			if (Loader::includeModule('extranet'))
			{
				$siteList = array();
				$intranetSiteId = \CExtranet::getExtranetSiteID();
				$extranetSiteId = \CSite::getDefSite();
				$res = \CSite::getList($by="sort", $order="desc", array("ACTIVE" => "Y"));
				while($site = $res->fetch())
				{
					$siteList[$site["ID"]] = array(
						"DIR" => (strlen(trim($site["DIR"])) > 0 ? $site["DIR"] : "/"),
						"SERVER_NAME" => (strlen(trim($site["SERVER_NAME"])) > 0 ? $site["SERVER_NAME"] : Option::get("main", "server_name", $_SERVER["HTTP_HOST"]))
					);
				}
			}

			$liveFeedProvider = Livefeed\Provider::init(array(
				'ENTITY_TYPE' => 'BLOG_COMMENT',
				'ENTITY_ID' => $commentId,
				'SITE_ID' => (!empty($options['siteId']) ? $options['siteId'] : SITE_ID)
			));
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

		return $result;
	}
}