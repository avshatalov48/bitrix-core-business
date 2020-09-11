<?
namespace Bitrix\Socialnetwork\Controller\Livefeed;

use Bitrix\Main\Loader;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Blog\Item\Permissions;
use Bitrix\Main\ModuleManager;
use Bitrix\Socialnetwork\ComponentHelper;

class BlogPost extends \Bitrix\Socialnetwork\Controller\Base
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

		$shareForbidden = ComponentHelper::getBlogPostLimitedViewStatus(array(
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

	public function shareAction(array $params = [])
	{
		$postId = (isset($params['postId']) ? intval($params['postId']) : 0);
		$destCodesList = (isset($params['DEST_CODES']) ? $params['DEST_CODES'] : []);
		$invitedUserName = (isset($params['INVITED_USER_NAME']) ? $params['INVITED_USER_NAME'] : []);
		$invitedUserLastName = (isset($params['INVITED_USER_LAST_NAME']) ? $params['INVITED_USER_LAST_NAME'] : []);
		$invitedUserCrmEntity = (isset($params['INVITED_USER_CRM_ENTITY']) ? $params['INVITED_USER_CRM_ENTITY'] : []);
		$invitedUserCreateCrmContact = (isset($params['INVITED_USER_CREATE_CRM_CONTACT']) ? $params['INVITED_USER_CREATE_CRM_CONTACT'] : []);
		$readOnly = (isset($params['readOnly']) && $params['readOnly'] == 'Y');
		$pathToUser = (isset($params['pathToUser']) ? $params['pathToUser'] : '');
		$pathToPost = (isset($params['pathToPost']) ? $params['pathToPost'] : '');
		$currentUserId = $this->getCurrentUser()->getId();

		$data = [
			'ALLOW_EMAIL_INVITATION' => (
				ModuleManager::isModuleInstalled('mail')
				&& ModuleManager::isModuleInstalled('intranet')
				&& (
					!Loader::includeModule('bitrix24')
					|| \CBitrix24::isEmailConfirmed()
				)
			)
		];

		if ($postId <= 0)
		{
			$this->addError(new Error(Loc::getMessage('SONET_CONTROLLER_LIVEFEED_BLOGPOST_EMPTY'), 'SONET_CONTROLLER_LIVEFEED_BLOGPOST_EMPTY'));
			return null;
		}

		if (
			!Loader::includeModule('blog')
			|| !($postItem = \Bitrix\Blog\Item\Post::getById($postId))
		)
		{
			$this->addError(new Error(Loc::getMessage('SONET_CONTROLLER_LIVEFEED_BLOGPOST_NOT_FOUND'), 'SONET_CONTROLLER_LIVEFEED_BLOGPOST_NOT_FOUND'));
			return null;
		}

		$currentUserPerm = \Bitrix\Socialnetwork\Item\Helper::getBlogPostPerm([
			'USER_ID' => $currentUserId,
			'POST_ID' => $postId
		]);

		if ($currentUserPerm <= Permissions::DENY)
		{
			$this->addError(new Error(Loc::getMessage('SONET_CONTROLLER_LIVEFEED_BLOGPOST_NOT_FOUND'), 'SONET_CONTROLLER_LIVEFEED_BLOGPOST_NOT_FOUND'));
			return null;
		}

		$postFields = $postItem->getFields();

		$perms2update = [];
		$sonetPermsListOld = \CBlogPost::getSocNetPerms($postId);
		foreach($sonetPermsListOld as $type => $val)
		{
			foreach($val as $id => $values)
			{
				if($type != "U")
				{
					$perms2update[] = $type.$id;
				}
				else
				{
					$perms2update[] = (
						in_array('US'.$id, $values)
							? 'UA'
							: $type.$id
					);
				}
			}
		}

		$newRightsList = [];

		$sonetPermsListNew = [
			'UA' => [],
			'U' => [],
			'UE' => [],
			'SG' => [],
			'DR' => []
		];
		foreach($destCodesList as $destCode)
		{
			if ($destCode == 'UA')
			{
				$sonetPermsListNew['UA'][] = 'UA';
			}
			elseif (preg_match('/^UE(.+)$/i', $destCode, $matches))
			{
				$sonetPermsListNew['UE'][] = $matches[1];
			}
			elseif (preg_match('/^U(\d+)$/i', $destCode, $matches))
			{
				$sonetPermsListNew['U'][] = 'U'.$matches[1];
			}
			elseif (preg_match('/^SG(\d+)$/i', $destCode, $matches))
			{
				$sonetPermsListNew['SG'][] = 'SG'.$matches[1];
			}
			elseif (preg_match('/^DR(\d+)$/i', $destCode, $matches))
			{
				$sonetPermsListNew['DR'][] = 'DR'.$matches[1];
			}
		}

		$HTTPPost = [
			'SONET_PERMS' => $sonetPermsListNew,
			'INVITED_USER_NAME' => $invitedUserName,
			'INVITED_USER_LAST_NAME' => $invitedUserLastName,
			'INVITED_USER_CRM_ENTITY' => $invitedUserCrmEntity,
			'INVITED_USER_CREATE_CRM_CONTACT' => $invitedUserCreateCrmContact
		];
		ComponentHelper::processBlogPostNewMailUser($HTTPPost, $data);
		$sonetPermsListNew = $HTTPPost['SONET_PERMS'];

		$currentAdmin = \CSocNetUser::isCurrentUserModuleAdmin();
		$canPublish = true;

		foreach($sonetPermsListNew as $type => $val)
		{
			foreach($val as $id => $code)
			{
				if(in_array($type, [ 'U', 'SG', 'DR', 'CRMCONTACT' ]))
				{
					if(!in_array($code, $perms2update))
					{
						if ($type == 'SG')
						{
							$sonetGroupId = intval(str_replace("SG", "", $code));

							$canPublish = (
								$currentAdmin
								|| \CSocNetFeaturesPerms::canPerformOperation($currentUserId, SONET_ENTITY_GROUP, $sonetGroupId, 'blog', 'write_post')
								|| \CSocNetFeaturesPerms::canPerformOperation($currentUserId, SONET_ENTITY_GROUP, $sonetGroupId, 'blog', 'moderate_post')
								|| \CSocNetFeaturesPerms::canPerformOperation($currentUserId, SONET_ENTITY_GROUP, $sonetGroupId, 'blog', 'full_post')
							);

							if (!$canPublish)
							{
								break;
							}
						}

						$perms2update[] = $code;
						$newRightsList[] = $code;
					}
				}
				elseif($type == 'UA')
				{
					if(!in_array('UA', $perms2update))
					{
						$perms2update[] = 'UA';
						$newRightsList[] = 'UA';
					}
				}
			}

			if (!$canPublish)
			{
				break;
			}
		}

		if (
			!empty($newRightsList)
			&& $canPublish
		)
		{
			ComponentHelper::processBlogPostShare(
				[
					'POST_ID' => $postId,
					'BLOG_ID' => $postFields['BLOG_ID'],
					'SITE_ID' => SITE_ID,
					'SONET_RIGHTS' => $perms2update,
					'NEW_RIGHTS' => $newRightsList,
					'USER_ID' => $currentUserId
				],
				[
					'MENTION' => 'N',
					'LIVE' => 'Y',
					'CAN_USER_COMMENT' => (!$readOnly ? 'Y' : 'N'),
					'PATH_TO_USER' => $pathToUser,
					'PATH_TO_POST' => $pathToPost,
				]
			);
		}
		elseif (!$canPublish)
		{
			$this->addError(new Error(Loc::getMessage('SONET_CONTROLLER_LIVEFEED_BLOGPOST_SHARE_PREMODERATION'), 'SONET_CONTROLLER_LIVEFEED_BLOGPOST_SHARE_PREMODERATION'));
			return null;
		}
	}

	public function addAction(array $params = [])
	{
		global $APPLICATION;

		try
		{
			$postId = \Bitrix\Socialnetwork\Item\Helper::addBlogPost($params, $this->getScope());
			if ($postId <= 0)
			{
				$e = $APPLICATION->getException();
				throw new \Exception($e ? $e->getString() : 'Cannot add blog post');
			}
		}
		catch (\Exception $e)
		{
			$this->addError(new Error($e->getMessage(), $e->getCode()));
			return null;
		}

		return [
			'id' => $postId
		];
	}

	public function updateAction($id = 0, array $params = [])
	{
		global $APPLICATION;

		try
		{
			$params['POST_ID'] = $id;
			$postId = \Bitrix\Socialnetwork\Item\Helper::updateBlogPost($params, $this->getScope());
			if ($postId <= 0)
			{
				$e = $APPLICATION->getException();
				throw new \Exception($e ? $e->getString() : 'Cannot update blog post');
			}
		}
		catch (\Exception $e)
		{
			$this->addError(new Error($e->getMessage(), $e->getCode()));
			return null;
		}

		return [
			'id' => $postId
		];
	}
}

