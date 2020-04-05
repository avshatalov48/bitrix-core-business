<?php
namespace Bitrix\Socialnetwork\Livefeed;

use Bitrix\Main\Loader;

final class RatingVoteList extends Provider
{
	const PROVIDER_ID = 'RATING_LIST';
	const TYPE = 'entry';
	const CONTENT_TYPE_ID = 'RATING_LIST';

	public static function getId()
	{
		return static::PROVIDER_ID;
	}

	public function getEventId()
	{
		return array();
	}

	public function getType()
	{
		return static::TYPE;
	}

	public function setContentView($params = array())
	{
		global $USER;

		if (!is_array($params))
		{
			$params = array();
		}

		$userId = (isset($params["user_id"]) && intval($params["user_id"]) > 0 ? intval($params["user_id"]) : $USER->getId());
		$contentEntityId = $this->getEntityId();

		list($ratingVoteTypeId, $ratingVoteEntityId) = explode('|', $contentEntityId);
		if (
			empty($ratingVoteTypeId)
			|| empty($ratingVoteEntityId)
			|| !Loader::includeModule('im')
		)
		{
			return false;
		}

		$CIMNotify = new \CIMNotify();
		$CIMNotify->markNotifyReadBySubTag(array(
			"RATING|".$ratingVoteTypeId."|".$ratingVoteEntityId.'|'.$userId
		));

		return array(
			'success' => true,
			'savedInDB' => false
		);
	}
}