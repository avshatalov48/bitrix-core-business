<?php
namespace Bitrix\Socialnetwork\CommentAux;

use Bitrix\Forum\MessageTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Web\Json;

final class TaskInfo extends Base
{
	const TYPE = 'TASKINFO';
	const POST_TEXT = 'commentAuxTaskInfo';

	protected static $forumMessageTableClass = MessageTable::class;

	public function getParamsFromFields($fields = array())
	{
		static $cacheData = [];

		$params = [];

		if (!empty($fields['SHARE_DEST'])) // old
		{
			$paramsList = unserialize(htmlspecialcharsback($fields['SHARE_DEST']), ['allowed_classes' => false]);
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
		elseif (
			!empty($fields['EVENT_ID'])
			&& in_array($fields['EVENT_ID'], [ 'tasks_comment', 'crm_activity_add_comment' ])
			&& !empty($fields['SOURCE_ID'])
			&& (int)$fields['SOURCE_ID'] > 0
			&& Loader::includeModule('forum')
		) // new
		{
			$messageId = (int)$fields['SOURCE_ID'];

			if (isset($cacheData[$messageId]))
			{
				$params = $cacheData[$messageId];
			}
			else
			{
				$forumPostLivefeedProvider = new \Bitrix\Socialnetwork\Livefeed\ForumPost();
				$commentData = $forumPostLivefeedProvider->getAuxCommentCachedData($messageId);

				if (
					!empty($commentData)
					&& isset($commentData['SERVICE_TYPE'])
					&& $commentData['SERVICE_TYPE'] === \Bitrix\Forum\Comments\Service\Manager::TYPE_TASK_INFO
					&& (
						!empty($commentData['SERVICE_DATA'])
						|| !empty($commentData['POST_MESSAGE'])
					)
				)
				{
					try
					{
						$messageParams = Json::decode(!empty($commentData['SERVICE_DATA']) ? $commentData['SERVICE_DATA'] : $commentData['POST_MESSAGE']);
						if (!is_array($messageParams))
						{
							$messageParams = [];
						}
					}
					catch(\Bitrix\Main\ArgumentException $e)
					{
						$messageParams = [];
					}

					$cacheData[$messageId] = $params = $messageParams;
				}
				else
				{
					$res = self::$forumMessageTableClass::getList([
						'filter' => [
							'=ID' => $messageId
						],
						'select' => ['TOPIC_ID']
					]);
					if (
						($forumMessageFields = $res->fetch())
						&& !empty($forumMessageFields['TOPIC_ID'])
					)
					{
						$res = self::$forumMessageTableClass::getList([
							'filter' => [
								'=TOPIC_ID' => (int)$forumMessageFields['TOPIC_ID']
							],
							'select' => [ 'ID', 'SERVICE_DATA', 'POST_MESSAGE' ]
						]);
						while (
							($forumMessageFields = $res->fetch())
							&& (
								!empty($forumMessageFields['SERVICE_DATA'])
								|| !empty($forumMessageFields['POST_MESSAGE'])
							)
						)
						{
							try
							{
								$messageParams = Json::decode(!empty($forumMessageFields['SERVICE_DATA']) ? $forumMessageFields['SERVICE_DATA'] : $forumMessageFields['POST_MESSAGE']);
								if (!is_array($messageParams))
								{
									$messageParams = [];
								}
							}
							catch(\Bitrix\Main\ArgumentException $e)
							{
								$messageParams = [];
							}

							$cacheData[$forumMessageFields['ID']] = $messageParams;
						}

						$params = ($cacheData[$messageId] ?? []);
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
			$parser = new \CTextParser();

			$parser->allow = [
				'HTML' => 'N',
				'ANCHOR' => 'Y',
				'USER' => 'Y',
			];

			$result = $parser->convertText($result);
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
				? (int)$ratingVoteParams['OWNER_ID']
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
					$ratingVoteParams['ENTITY_TYPE_ID'],
					$ratingVoteParams['ENTITY_ID']
				);

				if ($followValue !== "N")
				{
					$ratingVoteParams['ENTITY_LINK'] = $this->getRatingCommentLink(array(
						'commentId' => $fields['ID'],
						'commentAuthorId' => $ratingVoteParams['OWNER_ID'],
						'ratingEntityTypeId' => $ratingVoteParams['ENTITY_TYPE_ID'],
						'ratingEntityId' => $ratingVoteParams['ENTITY_ID']
					));

					$ratingVoteParams["ENTITY_PARAM"] = 'COMMENT';

					$CBXSanitizer = new \CBXSanitizer;
					$CBXSanitizer->delAllTags();
					$ratingVoteParams["ENTITY_TITLE"] = $ratingVoteParams["ENTITY_MESSAGE"] = strip_tags(str_replace('<br>', ' ', $CBXSanitizer->sanitizeHtml($this->getText())));

					$messageFields = array(
						"MESSAGE_TYPE" => IM_MESSAGE_SYSTEM,
						"TO_USER_ID" => $userId,
						"FROM_USER_ID" => (int)$ratingVoteParams['USER_ID'],
						"NOTIFY_TYPE" => IM_NOTIFY_FROM,
						"NOTIFY_MODULE" => "main",
						"NOTIFY_EVENT" => "rating_vote",
						"NOTIFY_TAG" => "RATING|".($ratingVoteParams['VALUE'] >= 0 ? "" : "DL|")."FORUM_POST|".$fields['SOURCE_ID'],
						"NOTIFY_MESSAGE" => \CIMEvent::getMessageRatingVote($ratingVoteParams),
						"NOTIFY_MESSAGE_OUT" => \CIMEvent::getMessageRatingVote($ratingVoteParams, true)
					);

					\CIMNotify::add($messageFields);
				}
			}
		}
	}
}