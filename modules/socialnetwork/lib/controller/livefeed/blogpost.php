<?php

namespace Bitrix\Socialnetwork\Controller\Livefeed;

use Bitrix\Disk\Driver;
use Bitrix\Disk\Security\DiskSecurityContext;
use Bitrix\Main\Engine\Response\Component;
use Bitrix\Main\Loader;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Blog\Item\Permissions;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\UI\EntitySelector;
use Bitrix\Main\Web\Json;
use Bitrix\Socialnetwork\ComponentHelper;
use Bitrix\Main\ArgumentException;
use Bitrix\Socialnetwork\Controller\Base;
use Bitrix\Socialnetwork\Item\Helper;

class BlogPost extends Base
{
	public function getDataAction(array $params = []): ?array
	{
		$postId = (int)($params['postId'] ?? 0);
		$public = ($params['public'] ?? 'N');
		$groupReadOnly = ($params['groupReadOnly'] ?? 'N');
		$pathToPost = ($params['pathToPost'] ?? '');
		$voteId = (int)($params['voteId'] ?? 0);
		$checkModeration = ($params['checkModeration'] ?? 'N');

		$currentUserId = (int)$this->getCurrentUser()->getId();
		$currentModuleAdmin = \CSocNetUser::isCurrentUserModuleAdmin(SITE_ID, false);

		$logPinnedUserId = 0;

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
			$postFields['PUBLISH_STATUS'] === BLOG_PUBLISH_STATUS_READY
			&& $checkModeration === 'Y'
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
				(int)$postFields['AUTHOR_ID'] === $currentUserId
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
		elseif ($public !== 'Y')
		{
			$filter["SITE_ID"] = [ SITE_ID, false ];
		}

		$res = \CSocNetLog::getList(
			[],
			$filter,
			false,
			false,
			[ 'ID', 'FAVORITES_USER_ID', 'PINNED_USER_ID' ],
			[ 'USE_PINNED' => 'Y' ]
		);

		if ($logEntry = $res->fetch())
		{
			$logId = (int)$logEntry['ID'];
			$logFavoritesUserId = (int)$logEntry['FAVORITES_USER_ID'];
			$logPinnedUserId = (int)$logEntry['PINNED_USER_ID'];
		}

		if ((int)$postFields["AUTHOR_ID"] === $currentUserId)
		{
			$perms = Permissions::FULL;
		}
		elseif (
			$currentModuleAdmin
			|| \CMain::getGroupRight('blog') >= 'W'
		)
		{
			$perms = Permissions::FULL;
		}
		elseif (!$logId)
		{
			$perms = Permissions::DENY;
		}
		else
		{
			$permsResult = $postItem->getSonetPerms([
				'PUBLIC' => ($public === 'Y'),
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
			'logPinnedUserId' => $logPinnedUserId,
			'authorId' => (int)$postFields['AUTHOR_ID'],
			'urlToPost' => $postUrl,
			'urlToVoteExport' => $voteExportUrl,
			'allowModerate' => ($allowModerate ? 'Y' : 'N'),
			'backgroundCode' => $postFields['BACKGROUND_CODE']
		];
	}

	public function shareAction(array $params = [])
	{
		$postId = (int)($params['postId'] ?? 0);
		$destCodesList = ($params['DEST_CODES'] ?? []);
		$destData = ($params['DEST_DATA'] ?? []);
		$invitedUserName = ($params['INVITED_USER_NAME'] ?? []);
		$invitedUserLastName = ($params['INVITED_USER_LAST_NAME'] ?? []);
		$invitedUserCrmEntity = ($params['INVITED_USER_CRM_ENTITY'] ?? []);
		$invitedUserCreateCrmContact = ($params['INVITED_USER_CREATE_CRM_CONTACT'] ?? []);
		$readOnly = (isset($params['readOnly']) && $params['readOnly'] === 'Y');
		$pathToUser = ($params['pathToUser'] ?? '');
		$pathToPost = ($params['pathToPost'] ?? '');
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
			$this->addError(
				new Error(
					Loc::getMessage('SONET_CONTROLLER_LIVEFEED_BLOGPOST_NOT_FOUND'),
					'SONET_CONTROLLER_LIVEFEED_BLOGPOST_NOT_FOUND'
				)
			);

			return null;
		}

		// todo for check access
		$currentUserPerm = Helper::getBlogPostPerm([
			'USER_ID' => $currentUserId,
			'POST_ID' => $postId,
		]);
		if ($currentUserPerm <= Permissions::DENY)
		{
			$this->addError(
				new Error(
					Loc::getMessage('SONET_CONTROLLER_LIVEFEED_BLOGPOST_NOT_FOUND'),
					'SONET_CONTROLLER_LIVEFEED_BLOGPOST_NOT_FOUND'
				)
			);

			return null;
		}

		$postFields = $postItem->getFields();

		if (
			(int)$postFields['AUTHOR_ID'] !== $currentUserId
			&& ComponentHelper::isCurrentUserExtranet()
		)
		{
			$visibleUserIdList = \CExtranet::getMyGroupsUsersSimple(SITE_ID);

			if (!empty(array_diff([(int)$postFields['AUTHOR_ID']], $visibleUserIdList)))
			{
				$this->addError(
					new Error(
						Loc::getMessage('SONET_CONTROLLER_LIVEFEED_BLOGPOST_NOT_FOUND'),
						'SONET_CONTROLLER_LIVEFEED_BLOGPOST_NOT_FOUND'
					)
				);

				return null;
			}
		}

		$perms2update = [];
		$sonetPermsListOld = \CBlogPost::getSocNetPerms($postId);
		foreach($sonetPermsListOld as $type => $val)
		{
			foreach($val as $id => $values)
			{
				if($type !== 'U')
				{
					$perms2update[] = $type . $id;
				}
				else
				{
					$perms2update[] = (
						in_array('US' . $id, $values, true)
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

		if (!empty($destData))
		{
			try
			{
				$entitites = Json::decode($destData);
				if (!empty($entitites))
				{
					$destCodesList = EntitySelector\Converter::convertToFinderCodes($entitites);
				}
			}
			catch(ArgumentException $e)
			{
			}
		}

		foreach($destCodesList as $destCode)
		{
			if ($destCode === 'UA')
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
			foreach($val as $code)
			{
				if(in_array($type, [ 'U', 'SG', 'DR', 'CRMCONTACT' ]))
				{
					if (!in_array($code, $perms2update))
					{
						if ($type === 'SG')
						{
							$sonetGroupId = (int)str_replace('SG', '', $code);

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
				elseif ($type === 'UA')
				{
					if (!in_array('UA', $perms2update, true))
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

	public function addAction(array $params = []): ?array
	{
		global $APPLICATION;

		$warnings = [];

		try
		{
			if (is_string($params['DEST_DATA'] ?? null))
			{
				$params['DEST'] = $this->convertDestData(['DEST_DATA' => $params['DEST_DATA']]);
			}

			$postId = Helper::addBlogPost($params, $this->getScope(), $resultFields);
			if ($postId <= 0)
			{
				if (
					is_array($resultFields)
					&& !empty($resultFields['ERROR_MESSAGE_PUBLIC'])
				)
				{
					$this->addError(new Error($resultFields['ERROR_MESSAGE_PUBLIC'], 0, [
						'public' => 'Y'
					]));
					return null;
				}

				$e = $APPLICATION->getException();
				throw new \Exception($e ? $e->getString() : 'Cannot add blog post');
			}

			if (
				is_array($resultFields)
				&& !empty($resultFields['WARNING_MESSAGE_PUBLIC'])
			)
			{
				$warnings[] = $resultFields['WARNING_MESSAGE_PUBLIC'];
			}

		}
		catch (\Exception $e)
		{
			$this->addError(new Error($e->getMessage(), $e->getCode()));
			return null;
		}

		return [
			'id' => $postId,
			'warnings' => $warnings
		];
	}

	public function updateAction($id = 0, array $params = []): ?array
	{
		global $APPLICATION;

		try
		{
			if (is_string($params['DEST_DATA'] ?? null))
			{
				$params['DEST'] = $this->convertDestData(['DEST_DATA' => $params['DEST_DATA']]);
			}

			$params['POST_ID'] = $id;
			$postId = Helper::updateBlogPost($params, $this->getScope(), $resultFields);
			if ($postId <= 0)
			{
				if (
					is_array($resultFields)
					&& !empty($resultFields['ERROR_MESSAGE_PUBLIC'])
				)
				{
					$this->addError(new Error($resultFields['ERROR_MESSAGE_PUBLIC'], 0, [
						'public' => 'Y'
					]));
					return null;
				}

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

	public function getBlogPostMobileFullDataAction(array $params = []): ?array
	{
		if (!Loader::includeModule('mobile'))
		{
			$this->addError(new Error('Mobile module not installed', 'SONET_CONTROLLER_LIVEFEED_MOBILE_MODULE_NOT_INSTALLED'));
			return null;
		}
		return \Bitrix\Mobile\Livefeed\Helper::getBlogPostFullData($params);
	}

	public function deleteAction($id = 0): ?bool
	{
		try
		{
			$result = Helper::deleteBlogPost([
				'POST_ID' => (int)$id,
			]);
		}
		catch (\Exception $e)
		{
			$this->addError(new Error($e->getMessage(), $e->getCode()));
			return null;
		}

		return $result;
	}

	public function getMainPostFormAction(array $params): ?Component
	{
		$postId = (int) $params['postId'];
		if (!$this->checkReadFormAccess($postId, 0))
		{
			$this->addError(new Error('Access denied'));

			return null;
		}

		$formId = (is_string($params['formId'] ?? null) ? $params['formId'] : '');
		$jsObjName = (is_string($params['jsObjName'] ?? null) ? $params['jsObjName'] : '');
		$LHEId = (is_string($params['LHEId'] ?? null) ? $params['LHEId'] : '');
		if (!$formId || !$jsObjName || !$LHEId)
		{
			$this->addError(new Error('Required parameters were not passed.'));

			return null;
		}

		$postId = (is_numeric($params['postId'] ?? null) ? (int) $params['postId'] : 0);
		$text = (is_string($params['text'] ?? null) ? $params['text'] : '');

		$ctrlEnterHandler = (is_string($params['ctrlEnterHandler'] ?? null)
			? $params['ctrlEnterHandler']
			: ''
		);
		$allowEmailInvitation = (is_bool($params['allowEmailInvitation'] ?? null)
			? $params['allowEmailInvitation']
			: false
		);
		$useCut = (is_bool($params['useCut'] ?? null) ? $params['useCut'] : false);
		$allowVideo = (is_bool($params['allowVideo'] ?? null) ? $params['allowVideo'] : false);

		global $USER_FIELD_MANAGER;
		$postFields = $USER_FIELD_MANAGER->getUserFields('BLOG_POST', $postId, LANGUAGE_ID);

		$properties = [];
		if (isset($postFields['UF_BLOG_POST_URL_PRV']))
		{
			$properties[] = $postFields['UF_BLOG_POST_URL_PRV'];
		}
		if (isset($postFields['UF_BLOG_POST_FILE']))
		{
			$properties[] = $postFields['UF_BLOG_POST_FILE'];
		}

		$tags = [];

		if ($postId)
		{
			$postFields = Helper::getBlogPostFields($postId);

			if ($postFields['CATEGORY_ID'] <> '')
			{
				$tags = $this->getPostTags($postId, $postFields['CATEGORY_ID']);
			}
		}

		$component = new Component(
			'bitrix:main.post.form',
			'',
			[
				'FORM_ID' => $formId,
				'SHOW_MORE' => 'Y',
				'DEST_CONTEXT' => 'BLOG_POST',
				'DESTINATION_SHOW' => 'Y',

				'LHE' => [
					'id' => $LHEId,
					'documentCSS' => 'body {color:#434343;}',
					'iframeCss' => 'html body { line-height: 20px!important;}',
					'ctrlEnterHandler' => $ctrlEnterHandler,
					'jsObjName' => $jsObjName,
					'fontSize' => '14px',
					'bInitByJS' => true,
					'width' => '100%',
					'minBodyWidth' => '100%',
					'normalBodyWidth' => '100%',
					'autoResizeMaxHeight' => 'Infinity',
					'minBodyHeight' => 200,
					'autoResize' => true,
					'saveOnBlur' => false,
					'lazyLoad' => true,
				],

				'TEXT' => [
					'NAME' => 'POST_MESSAGE',
					'VALUE' => \Bitrix\Main\Text\Emoji::decode(htmlspecialcharsBack($text)),
					'HEIGHT' => '120px'
				],

				'USE_CLIENT_DATABASE' => 'Y',
				'ALLOW_EMAIL_INVITATION' => $allowEmailInvitation ? 'Y' : 'N',
				'MENTION_ENTITIES' => [
					[
						'id' => 'user',
						'options' => [
							'emailUsers' => true,
							'inviteEmployeeLink' => false,
						],
					],
					[
						'id' => 'department',
						'options' => [
							'selectMode' => 'usersAndDepartments',
							'allowFlatDepartments' => false,
						],
					],
					[
						'id' => 'project',
						'options' => [
							'features' => [
								'blog' =>  [
									'premoderate_post',
									'moderate_post',
									'write_post',
									'full_post'
								],
							],
						],
					],
				],

				'PARSER' => [
					'Bold',
					'Italic',
					'Underline',
					'Strike',
					'ForeColor',
					'FontList',
					'FontSizeList',
					'RemoveFormat',
					'Quote',
					'Code',
					($useCut ? 'InsertCut' : ''),
					'CreateLink',
					'Image',
					'Table',
					'Justify',
					'InsertOrderedList',
					'InsertUnorderedList',
					'SmileList',
					'Source',
					'UploadImage',
					($allowVideo ? 'InputVideo' : ''),
					'MentionUser',
				],

				'BUTTONS' => [
					'UploadImage',
					'UploadFile',
					'CreateLink',
					($allowVideo ? 'InputVideo' : ''),
					'Quote',
					'MentionUser',
					'InputTag',
					'VideoMessage',
				],

				'TAGS' => [
					'ID' => 'TAGS',
					'NAME' => 'TAGS',
					'VALUE' => $tags,
					'USE_SEARCH' => 'Y',
					'FILTER' => 'blog',
				],

				'PROPERTIES' => $properties,
				'UPLOAD_FILE_PARAMS' => [
					'width' => 400,
					'height' => 400,
				],
			]
		);

		return $component;
	}

	public function getPostFormInitDataAction(int $postId, int $groupId): ?array
	{
		$postId = (int) $postId;
		$groupId = (int) $groupId;

		$editMode = $postId > 0;
		$groupMode = $groupId > 0;

		if (!$this->checkReadFormAccess($postId))
		{
			$this->addError(new Error('Access denied'));

			return null;
		}

		$userPostEditOption = \CUserOptions::getOption('socialnetwork', 'postEdit');

		$initData = [
			'isShownPostTitle' => ($userPostEditOption['showTitle'] ?? null) === 'Y' ? 'Y' : 'N',
			'allUsersTitle' => ModuleManager::isModuleInstalled('intranet')
				? Loc::getMessage('SN_MPF_DESTINATION_EMPLOYEES')
				: Loc::getMessage('SN_MPF_DESTINATION_USERS')
			,
			'allowEmailInvitation' => (
				ModuleManager::isModuleInstalled('mail')
				&& ModuleManager::isModuleInstalled('intranet')
				&& (
					!Loader::includeModule('bitrix24')
					|| \CBitrix24::isEmailConfirmed()
				)
			),
			'allowToAll' => ComponentHelper::getAllowToAllDestination(),
		];

		if ($editMode)
		{
			try
			{
				$postFields = Helper::getBlogPostFields($postId);

				$authorId = $postFields['AUTHOR_ID'];

				$initData['title'] = $postFields['MICRO'] === 'Y' ? '' : $postFields['TITLE'];
				$initData['message'] = $postFields['DETAIL_TEXT'];

				$perms = \CBlogPost::getSocnetPerms($postFields['ID']);
				if (
					is_array($perms['U'][$authorId] ?? null)
					&& in_array('US' . $authorId, $perms['U'][$authorId])
				)
				{
					$perms['U']['A'] = [];
				}
				if (
					!is_array($perms['U'][$authorId] ?? null)
					|| !in_array('U' . $authorId, $perms["U"][$authorId])
				)
				{
					unset($perms['U'][$authorId]);
				}

				$destList = $this->getPostFormDestList($perms);
				$initData['recipients'] = Json::encode($destList);

				$initData['fileIds'] = $postFields['UF_BLOG_POST_FILE'];
			}
			catch (\Exception $e)
			{
				$this->addError(new Error($e->getMessage(), $e->getCode()));

				return null;
			}
		}
		else
		{
			if ($groupMode)
			{
				if ($this->checkGroupAccess($groupId))
				{
					$initData['recipients'] = Json::encode($this->getPostFormDestList(
						['SG' => [$groupId => ['SG' . $groupId]]]
					));
				}
			}
			else
			{
				if (ComponentHelper::getAllowToAllDestination())
				{
					$initData['recipients'] = Json::encode($this->getPostFormDestList(
						['U' => ['A' => 'UA']]
					));
				}
			}
		}

		return $initData;
	}

	// todo
	public function uploadAIImageAction(string $imageUrl): ?array
	{
		$urlData = parse_url($imageUrl);
		if ($urlData['scheme'] !== 'https')
		{
			$this->addError(new Error('System error. Only https.'));

			return null;
		}

		if (!Loader::includeModule('disk'))
		{
			$this->addError(new Error('System error. Disk is not installed.'));

			return null;
		}

		$client = new \Bitrix\Main\Web\HttpClient();
		$client->setPrivateIp(false);

		$tempPath = \CFile::getTempName('', bx_basename($imageUrl));
		$isDownloaded = $client->download($imageUrl, $tempPath);
		if (!$isDownloaded)
		{
			$this->addError(new Error('System error. File cannot be downloaded.'));

			return null;
		}

		$currentUserId = $this->getCurrentUser()->getId();

		$fileType = $client->getHeaders()->getContentType() ?: \CFile::getContentType($tempPath);
		$recordFile = \CFile::makeFileArray($tempPath, $fileType);
		$recordFile['MODULE_ID'] = 'socialnetwork';

		$storage = Driver::getInstance()->getStorageByUserId($currentUserId);
		$folder = $storage->getFolderForUploadedFiles();

		if (!$folder->canAdd(new DiskSecurityContext($currentUserId)))
		{
			$this->addError(new Error('System error. Access denied.'));

			return null;
		}

		/** @var \Bitrix\Disk\File */
		$file = $folder->uploadFile(
			$recordFile,
			[
				'CREATED_BY' => $currentUserId,
			],
			[],
			true
		);

		return ['fileId' => 'n' . $file->getId()];
	}

	private function getPostFormDestList(array $permList): array
	{
		$destList = [];

		foreach ($permList as $type => $list)
		{
			if (!is_array($list))
			{
				continue;
			}

			foreach ($list as $id => $value)
			{
				if ($type === 'U')
				{
					if ($id === 'A' || $value === 'A')
					{
						if (ComponentHelper::getAllowToAllDestination())
						{
							$destList['UA'] = 'groups';
						}
					}
					else
					{
						$destList['U' . $id] = 'users';
					}
				}
				elseif ($type === 'SG')
				{
					$destList['SG' . $id] = 'sonetgroups';
				}
				elseif ($type === 'DR')
				{
					$destList['DR' . $id] = 'department';
				}
			}
		}

		return EntitySelector\Converter::sortEntities(
			EntitySelector\Converter::convertFromFinderCodes(array_keys($destList))
		);
	}

	private function checkReadFormAccess(int $postId): bool
	{
		$currentUserId = $this->getCurrentUser()->getId();

		$editMode = $postId > 0;
		if ($editMode)
		{
			$currentUserPerm = Helper::getBlogPostPerm([
				'USER_ID' => $currentUserId,
				'POST_ID' => $postId,
			]);
			if ($currentUserPerm >= Permissions::READ)
			{
				return true;
			}
		}
		else
		{
			return true;
		}

		return false;
	}

	private function checkGroupAccess(int $groupId): bool
	{
		$currentUserId = $this->getCurrentUser()->getId();

		if (\CSocNetGroup::getByID($groupId))
		{
			if (!\CSocNetFeatures::isActiveFeature(SONET_ENTITY_GROUP, $groupId, 'blog'))
			{
				return false;
			}
		}
		else
		{
			return false;
		}

		if (
			!\CSocNetFeaturesPerms::canPerformOperation(
				$currentUserId,
				SONET_ENTITY_GROUP,
				$groupId,
				'blog',
				'premoderate_post'
			)
		)
		{
			return false;
		}

		return true;
	}

	private function convertDestData(array $data): array
	{
		if (Loader::includeModule('blog'))
		{
			ComponentHelper::convertSelectorRequestData($data);

			$destData = [];
			foreach ($data['SPERM'] as $list)
			{
				$destData = array_merge($destData, $list);
			}

			return $destData;
		}

		return [];
	}

	private function getPostTags(int $postId, string $categoryId): array
	{
		$tags = [];

		$category = explode(",", $categoryId);

		$blogCategoryList = [];
		$res = \CBlogCategory::getList([], ['@ID' => $category]);
		while ($blogCategoryFields = $res->fetch())
		{
			$blogCategoryList[(int) $blogCategoryFields['ID']] = htmlspecialcharsEx($blogCategoryFields['NAME']);
		}

		$res = \CBlogPostCategory::getList(
			[ 'ID' => 'ASC' ],
			[
				'@CATEGORY_ID' => $category,
				'POST_ID' => $postId,
			],
			false,
			false,
			['CATEGORY_ID']
		);
		while ($blogPostCategoryFields = $res->fetch())
		{
			if (!isset($blogCategoryList[(int) $blogPostCategoryFields['CATEGORY_ID']]))
			{
				continue;
			}

			$tags[] = $blogCategoryList[(int) $blogPostCategoryFields['CATEGORY_ID']];
		}

		return $tags;
	}
}

