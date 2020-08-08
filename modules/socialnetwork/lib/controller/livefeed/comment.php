<?
namespace Bitrix\Socialnetwork\Controller\Livefeed;

use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Socialnetwork\LogCommentTable;

class Comment extends \Bitrix\Socialnetwork\Controller\Base
{
	public function getSourceAction(array $params = [])
	{
		$postId = (isset($params['postId']) ? intval($params['postId']) : 0);
		$commentId = (isset($params['commentId']) ? intval($params['commentId']) : 0);

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
				$postId = intval($logComment['LOG_ID']);
			}
		}

		if ($postId <= 0)
		{
			$this->addError(new Error(Loc::getMessage('SONET_CONTROLLER_LIVEFEED_COMMENT_POST_NOT_FOUND'), 'SONET_CONTROLLER_LIVEFEED_COMMENT_POST_NOT_FOUND'));
			return null;
		}

		$result = false;

		$commentData = \CSocNetLogComponent::getCommentByRequest($commentId, $postId, 'edit');
		if ($commentData)
		{
			$result = [
				'id' => intval($commentData['ID']),
				'message' => str_replace("<br />", "\n", $commentData['MESSAGE']),
				'sourceId' => (
					intval($commentData['SOURCE_ID']) > 0 ?
						intval($commentData['SOURCE_ID'])
						: intval($commentData['ID'])
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

