<?php
namespace Bitrix\Socialnetwork\CommentAux;

use Bitrix\Main\Loader;

final class TaskInfo extends Base
{
	const TYPE = 'TASKINFO';
	const POST_TEXT = 'commentAuxTaskInfo';

	public function getParamsFromFields($fields = array())
	{
		$params = [];

		if (!empty($fields['SHARE_DEST']))
		{
			$paramsList = unserialize(htmlspecialcharsback($fields['SHARE_DEST']));
			if (!empty($paramsList))
			{
				$params = $paramsList;
			}
			else
			{
				$paramsList = explode('|', $fields['SHARE_DEST']);

				if (!empty($paramsList))
				{
					foreach($paramsList as $pair)
					{
						list($key, $value) = explode('=', $pair);
						if (isset($key) && isset($value))
						{
							$params[$key] = $value;
						}
					}
				}
			}
		}

		return $params;
	}

	public function getText()
	{
		$result = '';
		$params = $this->params;

		if (
			isset($params['auxData'])
			&& isset($params['text'])
			&& $params['text'] <> ''
		)
		{
			$result = $params['text'];
		}
		elseif(
			is_array($params)
			&& !empty($params)
			&& Loader::includeModule('tasks')
		)
		{
			$result = htmlspecialcharsEx(\Bitrix\Tasks\Comments\Task\CommentPoster::getCommentText($params));
		}

		return $result;
	}

	public function canDelete()
	{
		return false;
	}

	public function checkRecalcNeeded($fields, $params)
	{
		return true;
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
			&& isset($fields["SHARE_DEST"])
			&& Loader::includeModule('im')
		)
		{
			$params = $this->getParamsFromFields($fields);
			if (!empty($params))
			{
				$this->setParams($params);

				$followValue = \CSocNetLogFollow::getExactValueByRating(
					$userId,
					$ratingVoteParams['ENTITY_TYPE_ID'],
					$ratingVoteParams['ENTITY_ID']
				);

				if ($followValue != "N")
				{
					$ratingVoteParams['ENTITY_LINK'] = $this->getRatingCommentLink(array(
						'commentId' => $fields['ID'],
						'commentAuthorId' => $ratingVoteParams['OWNER_ID'],
						'ratingEntityTypeId' => $ratingVoteParams['ENTITY_TYPE_ID'],
						'ratingEntityId' => $ratingVoteParams['ENTITY_ID']
					));

					$ratingVoteParams["ENTITY_PARAM"] = 'COMMENT';
					$ratingVoteParams["ENTITY_TITLE"] = $ratingVoteParams["ENTITY_MESSAGE"] = $this->getText();

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
}