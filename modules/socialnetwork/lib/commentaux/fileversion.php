<?php
namespace Bitrix\Socialnetwork\CommentAux;

use Bitrix\Disk\Configuration;
use Bitrix\Main\UserTable;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;

Loc::loadMessages(__FILE__);

final class FileVersion extends Base
{
	const TYPE = 'FILEVERSION';
	const POST_TEXT = 'commentAuxFileVersion';

	public function getParamsFromFields($fields = array())
	{
		$params = array();

		if (!empty($fields['AUTHOR_ID']))
		{
			$params['userId'] = intval($fields['AUTHOR_ID']);
		}

		return $params;
	}

	public function getText()
	{
		static $userCache = array();

		$params = $this->params;

		$gender = '';

		if (
			!empty($params['userId'])
			&& intval($params['userId']) > 0
		)
		{
			if (
				isset($userCache[intval($params['userId'])])
				&& is_array($userCache[intval($params['userId'])])
				&& isset($userCache[intval($params['userId'])]['PERSONAL_GENDER'])
			)
			{
				$gender = $userCache[intval($params['userId'])]['PERSONAL_GENDER'];
			}
			else
			{
				$res = UserTable::getList(array(
					'filter' => array(
						'=ID' => intval($params['userId'])
					),
					'select' => array('ID', 'PERSONAL_GENDER')
				));

				if ($user = $res->fetch())
				{
					$userCache[$user['ID']] = $user;
					$gender = $user['PERSONAL_GENDER'];
				}
			}
		}

		if(Loader::includeModule('disk') && !Configuration::isEnabledKeepVersion())
		{
			return Loc::getMessage('SONET_COMMENTAUX_HEAD_FILEVERSION_TEXT'.(!empty($gender) ? '_'.$gender : ''));
		}

		return Loc::getMessage('SONET_COMMENTAUX_FILEVERSION_TEXT'.(!empty($gender) ? '_'.$gender : ''));
	}

	public function sendRatingNotification($fields = array(), $ratingVoteParams = array())
	{
		$userId = (
			is_array($ratingVoteParams)
			&& isset($ratingVoteParams['OWNER_ID'])
				? intval($ratingVoteParams['OWNER_ID'])
				: 0
		);

		if (
			$userId > 0
			&& is_array($fields)
			&& Loader::includeModule('im')
		)
		{
			$params = $this->getParamsFromFields($fields);
			if (!empty($params))
			{
				$this->setParams($params);

				$followValue = \CSocNetLogFollow::getExactValueByRating(
					$userId,
					'BLOG_COMMENT',
					$fields['ID']
				);

				if ($followValue != "N")
				{
					$ratingVoteParams['ENTITY_LINK'] = $this->getRatingCommentLink(array(
						'commentId' => $fields['ID'],
						'commentAuthorId' => $ratingVoteParams['OWNER_ID']
					));

					$ratingVoteParams["ENTITY_PARAM"] = 'COMMENT';
					$ratingVoteParams["ENTITY_MESSAGE"] = $this->getText();

					$messageFields = array(
						"MESSAGE_TYPE" => IM_MESSAGE_SYSTEM,
						"TO_USER_ID" => $userId,
						"FROM_USER_ID" => intval($ratingVoteParams['USER_ID']),
						"NOTIFY_TYPE" => IM_NOTIFY_FROM,
						"NOTIFY_MODULE" => "main",
						"NOTIFY_EVENT" => "rating_vote",
						"NOTIFY_TAG" => "RATING|".($ratingVoteParams['VALUE'] >= 0 ? "" : "DL|")."BLOG_COMMENT|".$fields['ID'],
						"NOTIFY_MESSAGE" => \CIMEvent::getMessageRatingVote($ratingVoteParams),
						"NOTIFY_MESSAGE_OUT" => \CIMEvent::getMessageRatingVote($ratingVoteParams, true)
					);

					\CIMNotify::add($messageFields);
				}
			}
		}
	}

	public function checkRecalcNeeded($fields, $params)
	{
		return false;
	}
}