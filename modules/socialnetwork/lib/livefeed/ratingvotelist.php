<?php

namespace Bitrix\Socialnetwork\Livefeed;

use Bitrix\Main\Loader;

final class RatingVoteList extends Provider
{
	public const PROVIDER_ID = 'RATING_LIST';
	public const CONTENT_TYPE_ID = 'RATING_LIST';

	public static function getId(): string
	{
		return static::PROVIDER_ID;
	}

	public function getEventId(): array
	{
		return [];
	}

	public function getType(): string
	{
		return Provider::TYPE_POST;
	}

	public function setContentView($params = array())
	{
		global $USER;

		if (!is_array($params))
		{
			$params = [];
		}

		$userId = (isset($params['user_id']) && (int)$params["user_id"] > 0 ? (int)$params['user_id'] : $USER->getId());
		$contentEntityId = $this->getEntityId();

		[ $ratingVoteTypeId, $ratingVoteEntityId ] = explode('|', $contentEntityId);
		if (
			empty($ratingVoteTypeId)
			|| empty($ratingVoteEntityId)
			|| !Loader::includeModule('im')
		)
		{
			return false;
		}

		$CIMNotify = new \CIMNotify();
		$CIMNotify->markNotifyReadBySubTag([
			'RATING|' . $ratingVoteTypeId . '|' . $ratingVoteEntityId . '|' . $userId,
		]);

		return [
			'success' => true,
			'savedInDB' => false
		];
	}
}