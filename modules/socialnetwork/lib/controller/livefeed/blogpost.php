<?
namespace Bitrix\Socialnetwork\Controller\Livefeed;

use Bitrix\Main\Loader;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Blog\Item\Permissions;

class BlogPost extends \Bitrix\Main\Engine\Controller
{
	public function getDataAction(array $params = [])
	{
		global $APPLICATION;

		$postId = (isset($params['postId']) ? intval($params['postId']) : 0);
		$public = (isset($params['public']) ? $params['public'] : 'N');
		$mobile = (isset($params['mobile']) ? $params['mobile'] : 'N');
		$groupReadOnly = (isset($params['groupReadOnly']) ? $params['groupReadOnly'] : 'N');
		$pathToPost = (isset($params['pathToPost']) ? $params['pathToPost'] : '');
		$voteId = (isset($params['voteId']) ? intval($params['voteId']) : 0);
		$checkModeration = (isset($params['checkModeration']) ? $params['checkModeration'] : 'N');

		$currentUserId = $this->getCurrentUser()->getId();
		$currentModuleAdmin = \CSocNetUser::isCurrentUserModuleAdmin(SITE_ID, ($mobile != 'Y'));

		if ($postId <= 0)
		{
			$this->addError(new Error(Loc::getMessage('SONET_CONTROLLER_LIVEFEED_BLOGPOST_EMPTY'), 'SONET_CONTROLLER_LIVEFEED_BLOGPOST_EMPTY'));
			return null;
		}

		if (
			!Loader::includeModule('blog')
			|| !Loader::includeModule('socialnetwork')
			|| !($postItem = \Bitrix\Blog\Item\Post::getById($postId))
		)
		{
			$this->addError(new Error(Loc::getMessage('SONET_CONTROLLER_LIVEFEED_BLOGPOST_NOT_FOUND'), 'SONET_CONTROLLER_LIVEFEED_BLOGPOST_NOT_FOUND'));
			return null;
		}

		$postFields = $postItem->getFields();

		$logId = 0;
		$logFavoritesUserId = 0;
		$allowModerate = false;

		if (
			$postFields['PUBLISH_STATUS'] == BLOG_PUBLISH_STATUS_READY
			&& $checkModeration == 'Y'
		)
		{
			$postSocnetPermsList = \CBlogPost::getSocNetPerms($postId);
			if (
				!empty($postSocnetPermsList['SG'])
				&& is_array($postSocnetPermsList['SG'])
			)
			{
				$groupIdList = array_keys($postSocnetPermsList['SG']);
				foreach($groupIdList as $groupId)
				{
					if (
						\CSocNetFeaturesPerms::canPerformOperation($currentUserId, SONET_ENTITY_GROUP, $groupId, 'blog', 'full_post', $currentModuleAdmin)
						|| \CSocNetFeaturesPerms::canPerformOperation($currentUserId, SONET_ENTITY_GROUP, $groupId, 'blog', 'write_post')
						|| \CSocNetFeaturesPerms::canPerformOperation($currentUserId, SONET_ENTITY_GROUP, $groupId, 'blog', 'moderate_post')
					)
					{
						$allowModerate = true;
						break;
					}
				}
			}
			elseif(
				$postFields["AUTHOR_ID"] == $currentUserId
				|| $currentModuleAdmin
			)
			{
				$allowModerate = true;
			}
		}

		$blogPostLivefeedProvider = new \Bitrix\Socialnetwork\Livefeed\BlogPost;

		$filter = array(
			"EVENT_ID" => $blogPostLivefeedProvider->getEventId(),
			"SOURCE_ID" => $postId,
		);

		if (
			Loader::includeModule('extranet')
			&& \CExtranet::isExtranetSite(SITE_ID)
		)
		{
			$filter["SITE_ID"] = SITE_ID;
		}
		elseif ($public != 'Y')
		{
			$filter["SITE_ID"] = [ SITE_ID, false ];
		}

		$res = \CSocNetLog::getList(
			[],
			$filter,
			false,
			false,
			[ 'ID', 'FAVORITES_USER_ID' ]
		);

		if ($logEntry = $res->fetch())
		{
			$logId = intval($logEntry['ID']);
			$logFavoritesUserId = intval($logEntry['FAVORITES_USER_ID']);
		}

		if($postFields["AUTHOR_ID"] == $currentUserId)
		{
			$perms = Permissions::FULL;
		}
		else
		{
			if (!$logId)
			{
				$perms = Permissions::DENY;
			}
			elseif (
				$currentModuleAdmin
				|| $APPLICATION->getGroupRight('blog') >= 'W'
			)
			{
				$perms = Permissions::FULL;
			}
			else
			{
				$permsResult = $postItem->getSonetPerms([
					'PUBLIC' => ($public == 'Y'),
					'CHECK_FULL_PERMS' => true,
					'LOG_ID' => $logId
				]);
				$perms = $permsResult['PERM'];
				$groupReadOnly = (
					$permsResult['PERM'] <= \Bitrix\Blog\Item\Permissions::READ
					&& $permsResult['READ_BY_OSG']
						? 'Y'
						: 'N'
				);
			}
		}

		$shareForbidden = \Bitrix\Socialnetwork\ComponentHelper::getBlogPostLimitedViewStatus(array(
			'logId' => $logId,
			'postId' => $postId,
			'authorId' => $postFields['AUTHOR_ID']
		));

		$postUrl = \CComponentEngine::makePathFromTemplate(
			$pathToPost,
			[
				'post_id' => $postFields['ID'],
				'user_id' => $postFields['AUTHOR_ID']
			]
		);

		$voteExportUrl = '';

		if ($voteId > 0)
		{
			$voteExportUrl = \CHTTP::urlAddParams(
				\CHTTP::urlDeleteParams(
					$postUrl,
					[ 'exportVoting ' ]
				),
				[ 'exportVoting' => $voteId ]
			);
		}

		return [
			'perms' => $perms,
			'isGroupReadOnly' => $groupReadOnly,
			'isShareForbidden' => ($shareForbidden ? 'Y' : 'N'),
			'logId' => $logId,
			'logFavoritesUserId' => $logFavoritesUserId,
			'authorId' => intval($postFields['AUTHOR_ID']),
			'urlToPost' => $postUrl,
			'urlToVoteExport' => $voteExportUrl,
			'allowModerate' => ($allowModerate ? 'Y' : 'N')
		];
	}
}

