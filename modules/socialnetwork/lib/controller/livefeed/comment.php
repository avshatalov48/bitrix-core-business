<?php

namespace Bitrix\Socialnetwork\Controller\Livefeed;

use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Socialnetwork\Controller\Base;
use Bitrix\Socialnetwork\LogCommentTable;

class Comment extends Base
{
	public function getSourceAction(array $params = []): ?array
	{
		$postId = (int)($params['postId'] ?? 0);
		$commentId = (int)($params['commentId'] ?? 0);

		if ($commentId <= 0)
		{
			$this->addError(new Error(Loc::getMessage('SONET_CONTROLLER_LIVEFEED_COMMENT_COMMENT_EMPTY'), 'SONET_CONTROLLER_LIVEFEED_COMMENT_COMMENT_EMPTY'));
			return null;
		}

		if ($postId <= 0)
		{
			$res = LogCommentTable::getList([
				'filter' => [
					'=ID' => $commentId
				],
				'select' => [ 'LOG_ID' ]
			]);
			if ($logComment = $res->fetch())
			{
				$postId = (int)$logComment['LOG_ID'];
			}
		}

		if ($postId <= 0)
		{
			$this->addError(new Error(Loc::getMessage('SONET_CONTROLLER_LIVEFEED_COMMENT_POST_NOT_FOUND'), 'SONET_CONTROLLER_LIVEFEED_COMMENT_POST_NOT_FOUND'));
			return null;
		}

		$commentData = \CSocNetLogComponent::getCommentByRequest($commentId, $postId, 'edit');
		if ($commentData)
		{
			$result = [
				'id' => (int)$commentData['ID'],
				'message' => str_replace("<br />", "\n", $commentData['MESSAGE']),
				'sourceId' => (
					(int)$commentData['SOURCE_ID'] > 0
						? (int)$commentData['SOURCE_ID']
						: (int)$commentData['ID']
				),
				'UF' => (
					!empty($commentData['UF'])
						? $commentData['UF']
						: []
				)
			];
		}
		else
		{
			$this->addError(new Error(Loc::getMessage('SONET_CONTROLLER_LIVEFEED_COMMENT_COMMENT_NOT_FOUND'), 'SONET_CONTROLLER_LIVEFEED_COMMENT_COMMENT_NOT_FOUND'));
			return null;
		}

		return $result;
	}
}
