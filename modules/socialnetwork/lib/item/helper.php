<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2017 Bitrix
 */
namespace Bitrix\Socialnetwork\Item;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Socialnetwork\ComponentHelper;
use Bitrix\Socialnetwork\Controller\Livefeed;
use Bitrix\Disk\Uf\FileUserType;

class Helper
{
	public static function addBlogPost($params, $scope = \Bitrix\Main\Engine\Controller::SCOPE_AJAX, &$resultFields = [])
	{
		global $USER, $CACHE_MANAGER, $APPLICATION;

		$siteId = (
			is_set($params, 'SITE_ID')
			&& !empty($params['SITE_ID'])
				? $params['SITE_ID']
				: SITE_ID
		);

		$authorId = (
			isset($params['USER_ID'])
			&& (int)$params['USER_ID'] > 0
			&& Livefeed::isAdmin()
				? $params['USER_ID']
				: $USER->getId()
		);

		if (!Loader::includeModule('blog'))
		{
			$APPLICATION->throwException('No blog module installed', 'SONET_CONTROLLER_LIVEFEED_BLOGPOST_MODULE_BLOG_NOT_INSTALLED');
			return false;
		}

		$blogGroupId = Option::get('socialnetwork', 'userbloggroup_id', false, $siteId);
		if (empty($blogGroupId))
		{
			$blogGroupIdList = \Bitrix\Socialnetwork\ComponentHelper::getSonetBlogGroupIdList([
				'SITE_ID' => $siteId
			]);
			if (!empty($blogGroupIdList))
			{
				$blogGroupId = array_shift($blogGroupIdList);
			}
		}

		$blog = \Bitrix\Blog\Item\Blog::getByUser([
			'GROUP_ID' => $blogGroupId,
			'SITE_ID' => $siteId,
			'USER_ID' => $authorId,
			'CREATE' => 'Y'
		]);

		if (!$blog)
		{
			$APPLICATION->throwException('Blog not found', 'SONET_CONTROLLER_LIVEFEED_BLOG_NOT_FOUND');
			return false;
		}

		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		$postFields = [
			'BLOG_ID' => $blog['ID'],
			'AUTHOR_ID' => $authorId,
			'=DATE_CREATE' => $helper->getCurrentDateTimeFunction(),
			'=DATE_PUBLISH' => $helper->getCurrentDateTimeFunction(),
			'MICRO' => 'N',
			'TITLE' => ($params['POST_TITLE'] <> '' ? $params['POST_TITLE'] : ''),
			'DETAIL_TEXT' => $params['POST_MESSAGE'],
			'DETAIL_TEXT_TYPE' => 'text',
			'PUBLISH_STATUS' => BLOG_PUBLISH_STATUS_PUBLISH,
			'HAS_IMAGES' => 'N',
			'HAS_TAGS' => 'N',
			'HAS_SOCNET_ALL' => 'N'
		];

		$emailUserAllowed = (
			ModuleManager::isModuleInstalled('mail')
			&& ModuleManager::isModuleInstalled('intranet')
			&& (
				!Loader::includeModule('bitrix24')
				|| \CBitrix24::isEmailConfirmed()
			)
		);

		if (
			!empty($params['DEST'])
			&& is_array($params['DEST'])
		)
		{
			$resultFields = [
				'ERROR_MESSAGE' => false,
				'PUBLISH_STATUS' => $postFields['PUBLISH_STATUS']
			];

			if ($emailUserAllowed)
			{
				$destinationList = $params['DEST'];
				ComponentHelper::processBlogPostNewMailUserDestinations($destinationList);
				$params['DEST'] = array_unique($destinationList);
			}

			$postFields['SOCNET_RIGHTS'] = ComponentHelper::checkBlogPostDestinationList([
				'DEST' => $params['DEST'],
				'SITE_ID' => $siteId,
				'AUTHOR_ID' => $authorId,
				'MOBILE' => ($params['MOBILE'] ?? 'N'),
			], $resultFields);

			if ($resultFields['ERROR_MESSAGE_PUBLIC'])
			{
				return false;
			}

			if ($resultFields['ERROR_MESSAGE'])
			{
				$APPLICATION->throwException($resultFields['ERROR_MESSAGE']);
				return false;
			}

			$postFields['PUBLISH_STATUS'] = $resultFields['PUBLISH_STATUS'];
		}
		elseif (
			!empty($params['SPERM'])
			&& $scope === \Bitrix\Main\Engine\Controller::SCOPE_REST
		)
		{
			if ($emailUserAllowed)
			{
				$pseudoHttpPostFields = [
					'SPERM' => $params['SPERM'],
					'INVITED_USER_NAME' => (!empty($params['INVITED_USER_NAME']) && is_array($params['INVITED_USER_NAME']) ? $params['INVITED_USER_NAME'] : []),
					'INVITED_USER_LAST_NAME' => (!empty($params['INVITED_USER_NAME']) && is_array($params['INVITED_USER_LAST_NAME']) ? $params['INVITED_USER_LAST_NAME'] : []),
					'INVITED_USER_CRM_ENTITY' => (!empty($params['INVITED_USER_CRM_ENTITY']) && is_array($params['INVITED_USER_CRM_ENTITY']) ? $params['INVITED_USER_CRM_ENTITY'] : []),
					'INVITED_USER_CREATE_CRM_CONTACT' => (!empty($params['INVITED_USER_CREATE_CRM_CONTACT']) && is_array($params['INVITED_USER_CREATE_CRM_CONTACT']) ? $params['INVITED_USER_CREATE_CRM_CONTACT'] : []),
				];
				$temporaryParams = [
					'ALLOW_EMAIL_INVITATION' => true
				];
				ComponentHelper::processBlogPostNewMailUser($pseudoHttpPostFields, $temporaryParams);
				if (!empty($temporaryParams['ERROR_MESSAGE']))
				{
					$APPLICATION->throwException($temporaryParams['ERROR_MESSAGE'], 'SONET_CONTROLLER_LIVEFEED_BLOGPOST_ADD_ERROR');
					return false;
				}

				$params['SPERM'] = $pseudoHttpPostFields['SPERM'];
			}

			$resultFields = [
				'ERROR_MESSAGE' => false,
				'PUBLISH_STATUS' => $postFields['PUBLISH_STATUS'],
			];

			$postFields['SOCNET_RIGHTS'] = ComponentHelper::convertBlogPostPermToDestinationList([
				'PERM' => $params['SPERM'],
				'IS_REST' => true,
				'AUTHOR_ID' => $authorId,
				'SITE_ID' => $siteId
			], $resultFields);

			$postFields['PUBLISH_STATUS'] = $resultFields['PUBLISH_STATUS'];
			if (!empty($resultFields['ERROR_MESSAGE']))
			{
				$APPLICATION->throwException($resultFields['ERROR_MESSAGE'], 'SONET_CONTROLLER_LIVEFEED_BLOGPOST_ADD_ERROR');
				return false;
			}
		}
		elseif (
			!Loader::includeModule('extranet')
			|| \CExtranet::isIntranetUser()
		)
		{
			$postFields['SOCNET_RIGHTS'] = [ 'UA' ];
		}

		if (empty($postFields['SOCNET_RIGHTS']))
		{
			$APPLICATION->throwException('No destination specified', 'SONET_CONTROLLER_LIVEFEED_BLOGPOST_ADD_ERROR');
			return false;
		}

		if ($postFields['TITLE'] == '')
		{
			$postFields['MICRO'] = 'Y';
			$postFields['TITLE'] = preg_replace([ "/\n+/is" . BX_UTF_PCRE_MODIFIER, "/\s+/is" . BX_UTF_PCRE_MODIFIER ], ' ', \blogTextParser::killAllTags($postFields['DETAIL_TEXT']));
			$postFields['TITLE'] = trim($postFields['TITLE'], " \t\n\r\0\x0B\xA0");
		}

		if (
			isset($params['IMPORTANT'])
			&& $params['IMPORTANT'] === 'Y'
		)
		{
			$postFields['UF_BLOG_POST_IMPRTNT'] = true;

			if (!empty($params['IMPORTANT_DATE_END']))
			{
				$endDate = \CRestUtil::unConvertDate($params['IMPORTANT_DATE_END']);
				if ($endDate)
				{
					$postFields['UF_IMPRTANT_DATE_END'] = \Bitrix\Main\Type\DateTime::createFromUserTime($endDate);
				}
			}
		}

		if (
			isset($params['GRATITUDE_MEDAL'])
			&& isset($params['GRATITUDE_EMPLOYEES'])
		)
		{
			$gratitudeElementId = \Bitrix\Socialnetwork\Helper\Gratitude::create([
				'medal' => $params['GRATITUDE_MEDAL'],
				'employees' => $params['GRATITUDE_EMPLOYEES']
			]);
			if ($gratitudeElementId)
			{
				$postFields['UF_GRATITUDE'] = $gratitudeElementId;
			}
		}

		if (
			!empty($params['UF_BLOG_POST_VOTE'])
			&& !empty($params['UF_BLOG_POST_VOTE_' . $params['UF_BLOG_POST_VOTE'] . '_DATA'])
		)
		{
			$postFields['UF_BLOG_POST_VOTE'] = $params['UF_BLOG_POST_VOTE'];
			$GLOBALS['UF_BLOG_POST_VOTE_' . $params['UF_BLOG_POST_VOTE'] . '_DATA'] = $params['UF_BLOG_POST_VOTE_' . $params['UF_BLOG_POST_VOTE'] . '_DATA'];
		}

		if (!empty($params['BACKGROUND_CODE']))
		{
			$postFields['BACKGROUND_CODE'] = $params['BACKGROUND_CODE'];
		}

		if (
			isset($params['PARSE_PREVIEW'])
			&& $params['PARSE_PREVIEW'] === 'Y'
			&& !empty($postFields['DETAIL_TEXT'])
			&& ($urlPreviewValue = ComponentHelper::getUrlPreviewValue($postFields['DETAIL_TEXT']))
		)
		{
			$postFields['UF_BLOG_POST_URL_PRV'] = $urlPreviewValue;
		}

		$result = \CBlogPost::add($postFields);

		if (!$result)
		{
			$APPLICATION->throwException('Blog post hasn\'t been added', 'SONET_CONTROLLER_LIVEFEED_BLOGPOST_ADD_ERROR');
			return false;
		}

		$socnetPerms = \Bitrix\Socialnetwork\ComponentHelper::getBlogPostSocNetPerms([
			'postId' => $result,
			'authorId' => $postFields['AUTHOR_ID']
		]);

		\Bitrix\Main\FinderDestTable::merge([
			'CONTEXT' => 'blog_post',
			'CODE' => \Bitrix\Main\FinderDestTable::convertRights($socnetPerms, [ 'U' . $postFields['AUTHOR_ID'] ])
		]);

		if (
			isset($params['IMPORTANT'])
			&& $params['IMPORTANT'] === 'Y'
		)
		{
			\CBlogUserOptions::setOption($result, 'BLOG_POST_IMPRTNT', 'Y', $authorId);

			if (defined('BX_COMP_MANAGED_CACHE'))
			{
				$CACHE_MANAGER->clearByTag('blogpost_important_all');
			}
		}

		$inlineTagsList = \Bitrix\Socialnetwork\Util::detectTags($postFields, ($postFields['MICRO'] === 'Y' ? [ 'DETAIL_TEXT' ] : [ 'DETAIL_TEXT', 'TITLE' ]));
		if (!empty($inlineTagsList))
		{
			$inlineTagsList = array_unique(array_map('ToLower', $inlineTagsList));

			$existingCategoriesList = [];
			$res = \CBlogCategory::getList(
				[],
				[
					'@NAME' => $inlineTagsList,
					'BLOG_ID' => $postFields['BLOG_ID']
				],
				false,
				false,
				[ 'ID', 'NAME' ]
			);
			while ($category = $res->fetch())
			{
				$existingCategoriesList[$category['NAME']] = $category['ID'];
			}

			$categoryIdList = [];

			foreach ($inlineTagsList as $tag)
			{
				if (array_key_exists($tag, $existingCategoriesList))
				{
					$categoryIdList[] = $existingCategoriesList[$tag];
				}
				else
				{
					$categoryIdList[] = \CBlogCategory::add([
						'BLOG_ID' => $postFields['BLOG_ID'],
						'NAME' => $tag
					]);
				}
			}

			foreach ($categoryIdList as $categoryId)
			{
				\CBlogPostCategory::add([
					'BLOG_ID' => $postFields['BLOG_ID'],
					'POST_ID' => $result,
					'CATEGORY_ID' => $categoryId
				]);
			}

			\CBlogPost::update(
				$result,
				[
					'CATEGORY_ID' => implode(',', $categoryIdList),
					'HAS_TAGS' => 'Y'
				]
			);
		}

		if (
			Option::get('disk', 'successfully_converted', false)
			&& Loader::includeModule('disk')
			&& ($storage = \Bitrix\Disk\Driver::getInstance()->getStorageByUserId($authorId))
			&& ($folder = $storage->getFolderForUploadedFiles())
		)
		{
			$filesList = [];

			if (
				isset($params['FILES'])
				&& $scope === \Bitrix\Main\Engine\Controller::SCOPE_REST
			)
			{
				foreach ($params['FILES'] as $fileData)
				{
					$fileFields = \CRestUtil::saveFile($fileData);

					if (is_array($fileFields))
					{
						$file = $folder->uploadFile(
							$fileFields, // file array
							[
								'NAME' => $fileFields['name'],
								'CREATED_BY' => $authorId
							],
							[],
							true
						);

						if ($file)
						{
							$filesList[] = FileUserType::NEW_FILE_PREFIX . $file->getId();
						}
					}
				}
			}
			elseif (
				isset($params['UF_BLOG_POST_FILE'])
				&& is_array($params['UF_BLOG_POST_FILE'])
			)
			{
				$filesList = $params['UF_BLOG_POST_FILE'];
			}

			if (!empty($filesList)) // update post
			{
				\CBlogPost::update(
					$result,
					[
						'HAS_PROPS' => 'Y',
						'UF_BLOG_POST_FILE' => $filesList
					]
				);
			}
		}

		$pathToPost = Option::get('socialnetwork', 'userblogpost_page', false, $siteId);

		$postFields['ID'] = $result;

		$postUrl = \CComponentEngine::makePathFromTemplate(htmlspecialcharsBack($pathToPost), [
			'post_id' => $result,
			'user_id' => $blog['OWNER_ID']
		]);

		if ($postFields['PUBLISH_STATUS'] === BLOG_PUBLISH_STATUS_PUBLISH)
		{
			$paramsNotify = [
				'bSoNet' => true,
				'allowVideo' => Option::get('blog', 'allow_video', 'Y'),
				'PATH_TO_POST' => $pathToPost,
				'user_id' => $authorId,
				'NAME_TEMPLATE' => \CSite::getNameFormat(null, $siteId),
			];

			$logId = \CBlogPost::notify($postFields, $blog, $paramsNotify);
			if ($logId)
			{
				if ($post = \Bitrix\Blog\Item\Post::getById($result))
				{
					\CSocNetLog::update((int)$logId, [
						'EVENT_ID' => self::getBlogPostEventId([
							'postId' => $post->getId()
						]),
						'SOURCE_ID' => $result, // table column field
						'TAG' => $post->getTags(),
					]);
				}
			}

			BXClearCache(true, ComponentHelper::getBlogPostCacheDir([
				'TYPE' => 'posts_last',
				'SITE_ID' => $siteId
			]));

			$mentionList = \Bitrix\Socialnetwork\Helper\Mention::getUserIds($postFields['DETAIL_TEXT']);

			ComponentHelper::notifyBlogPostCreated([
				'post' => [
					'ID' => $result,
					'TITLE' => $postFields['TITLE'],
					'AUTHOR_ID' => $authorId
				],
				'siteId' => $siteId,
				'postUrl' => $postUrl,
				'socnetRights' => ($logId ? \Bitrix\Socialnetwork\Item\LogRight::get($logId) : $postFields['SOCNET_RIGHTS']),
				'socnetRightsOld' => [],
				'mentionListOld' => [],
				'mentionList' => $mentionList
			]);
		}
		elseif (
			$postFields['PUBLISH_STATUS'] === BLOG_PUBLISH_STATUS_READY
			&& !empty($postFields['SOCNET_RIGHTS'])
		)
		{
			\CBlogPost::notifyImReady([
				'TYPE' => 'POST',
				'POST_ID' => $result,
				'TITLE' => $postFields['TITLE'],
				'POST_URL' => $postUrl,
				'FROM_USER_ID' => $authorId,
				'TO_SOCNET_RIGHTS' => $postFields['SOCNET_RIGHTS']
			]);

			$resultFields['WARNING_MESSAGE_PUBLIC'] = Loc::getMessage('SOCIALNETWORK_ITEM_HELPER_MODERATION_WARNING');
		}

		foreach ($postFields['SOCNET_RIGHTS'] as $destination)
		{
			if (preg_match('/^SG(\d+)/i', $destination, $matches))
			{
				\CSocNetGroup::setLastActivity($matches[1]);
			}
		}

		return $result;
	}

	public static function updateBlogPost($params = [], $scope = \Bitrix\Main\Engine\Controller::SCOPE_AJAX, &$resultFields = [])
	{
		global $USER, $USER_FIELD_MANAGER, $APPLICATION, $CACHE_MANAGER;

		$postId = (int)$params['POST_ID'];

		if ($postId <= 0)
		{
			$APPLICATION->throwException('Wrong post ID', 'SONET_CONTROLLER_LIVEFEED_BLOGPOST_UPDATE_ERROR');
			return false;
		}

		if (!Loader::includeModule('blog'))
		{
			$APPLICATION->throwException('Blog module not installed', 'SONET_CONTROLLER_LIVEFEED_BLOGPOST_UPDATE_ERROR');
			return false;
		}

		$currentUserId = (
			isset($params['USER_ID'])
			&& (int)$params['USER_ID'] > 0
			&& Livefeed::isAdmin()
				? $params['USER_ID']
				: $USER->getId()
		);

		$siteId = (
			is_set($params, 'SITE_ID')
			&& !empty($params['SITE_ID'])
				? $params['SITE_ID']
				: SITE_ID
		);

		$currentUserPerm = self::getBlogPostPerm([
			'USER_ID' => $currentUserId,
			'POST_ID' => $postId
		]);

		if ($currentUserPerm <= \Bitrix\Blog\Item\Permissions::WRITE)
		{
			$APPLICATION->throwException('No write perms', 'SONET_CONTROLLER_LIVEFEED_BLOGPOST_UPDATE_ERROR');
			return false;
		}

		$postFields = \Bitrix\Blog\Item\Post::getById($postId)->getFields();
		if (empty($postFields))
		{
			$APPLICATION->throwException('No post found', 'SONET_CONTROLLER_LIVEFEED_BLOGPOST_UPDATE_ERROR');
			return false;
		}

		$blog = \Bitrix\Blog\Item\Blog::getByUser([
			'GROUP_ID' => Option::get('socialnetwork', 'userbloggroup_id', false, $siteId),
			'SITE_ID' => $siteId,
			'USER_ID' => $postFields['AUTHOR_ID']
		]);

		if (!$blog)
		{
			$APPLICATION->throwException('No blog found', 'SONET_CONTROLLER_LIVEFEED_BLOGPOST_UPDATE_ERROR');
			return false;
		}

		$updateFields = [
			'PUBLISH_STATUS' => $postFields['PUBLISH_STATUS']
		];

		$updateFields['TITLE'] = '';
		$updateFields['MICRO'] = 'N';

		if (isset($params['POST_TITLE']))
		{
			$updateFields['TITLE'] = $params['POST_TITLE'];
		}

		if (
			$updateFields['TITLE'] == ''
			&& isset($params['POST_MESSAGE'])
		)
		{
			$updateFields['MICRO'] = 'Y';
			$updateFields['TITLE'] = preg_replace([ "/\n+/is" . BX_UTF_PCRE_MODIFIER, "/\s+/is".BX_UTF_PCRE_MODIFIER ], ' ', \blogTextParser::killAllTags($params['POST_MESSAGE']));
			$updateFields['TITLE'] = trim($updateFields['TITLE'], " \t\n\r\0\x0B\xA0");
		}

		if ($params['POST_MESSAGE'] <> '')
		{
			$updateFields['DETAIL_TEXT'] = $params['POST_MESSAGE'];
		}

		if (!empty($params['DEST']))
		{
			if (
				ModuleManager::isModuleInstalled('mail')
				&& ModuleManager::isModuleInstalled('intranet')
				&& (
					!Loader::includeModule('bitrix24')
					|| \CBitrix24::isEmailConfirmed()
				)
			)
			{
				$destinationList = $params['DEST'];
				ComponentHelper::processBlogPostNewMailUserDestinations($destinationList);
				$params['DEST'] = array_unique($destinationList);
			}

			$resultFields = [
				'ERROR_MESSAGE' => false,
				'PUBLISH_STATUS' => $updateFields['PUBLISH_STATUS']
			];

			$updateFields['SOCNET_RIGHTS'] = ComponentHelper::checkBlogPostDestinationList([
				'DEST' => $params['DEST'],
				'SITE_ID' => $siteId,
				'AUTHOR_ID' => $postFields['AUTHOR_ID'],
				'MOBILE' => ($params['MOBILE'] ?? 'N'),
			], $resultFields);

			if ($resultFields['ERROR_MESSAGE_PUBLIC'])
			{
				return false;
			}

			if ($resultFields['ERROR_MESSAGE'])
			{
				$APPLICATION->throwException($resultFields['ERROR_MESSAGE'], 'SONET_CONTROLLER_LIVEFEED_BLOGPOST_UPDATE_ERROR');
				return false;
			}

			$updateFields['PUBLISH_STATUS'] = $resultFields['PUBLISH_STATUS'];
		}

		if (isset($params['IMPORTANT']))
		{
			if ($params['IMPORTANT'] === 'Y')
			{
				$updateFields['UF_BLOG_POST_IMPRTNT'] = true;

				if (!empty($params['IMPORTANT_DATE_END']))
				{
					$endDate = \CRestUtil::unConvertDate($params['IMPORTANT_DATE_END']);
					if ($endDate)
					{
						$updateFields['UF_IMPRTANT_DATE_END'] = \Bitrix\Main\Type\DateTime::createFromUserTime($endDate);
					}
				}
			}
			else
			{
				$updateFields['UF_BLOG_POST_IMPRTNT'] = false;
				$updateFields['UF_IMPRTANT_DATE_END'] = false;
			}
		}

		if (isset($params['GRATITUDE_MEDAL']))
		{
			if (
				!empty($params['GRATITUDE_MEDAL'])
				&& isset($params['GRATITUDE_EMPLOYEES'])
			)
			{
				$gratitudeElementId = \Bitrix\Socialnetwork\Helper\Gratitude::create([
					'medal' => $params['GRATITUDE_MEDAL'],
					'employees' => $params['GRATITUDE_EMPLOYEES']
				]);
				if ($gratitudeElementId)
				{
					$updateFields['UF_GRATITUDE'] = $gratitudeElementId;
					$updateFields['HAS_PROPS'] = 'Y';
				}
			}
			else
			{
				$updateFields['UF_GRATITUDE'] = false;
			}
		}

		if (isset($params['UF_BLOG_POST_VOTE']))
		{
			if (
				!empty($params['UF_BLOG_POST_VOTE'])
				&& !empty($params['UF_BLOG_POST_VOTE_' . $params['UF_BLOG_POST_VOTE'] . '_DATA'])
			)
			{
				$updateFields['UF_BLOG_POST_VOTE'] = $params['UF_BLOG_POST_VOTE'];
				$GLOBALS['UF_BLOG_POST_VOTE_' . $params['UF_BLOG_POST_VOTE'] . '_DATA'] = $params['UF_BLOG_POST_VOTE_' . $params['UF_BLOG_POST_VOTE'] . '_DATA'];
				$updateFields['HAS_PROPS'] = 'Y';
			}
			else
			{
				$updateFields['UF_BLOG_POST_VOTE'] = false;
			}
		}

		if (isset($params['BACKGROUND_CODE']))
		{
			$updateFields['BACKGROUND_CODE'] = (!empty($params['BACKGROUND_CODE']) ? $params['BACKGROUND_CODE'] : false);
		}

		if (
			isset($params['PARSE_PREVIEW'])
			&& $params['PARSE_PREVIEW'] === 'Y'
			&& !empty($updateFields['DETAIL_TEXT'])
			&& ($urlPreviewValue = ComponentHelper::getUrlPreviewValue($updateFields['DETAIL_TEXT']))
		)
		{
			$updateFields['UF_BLOG_POST_URL_PRV'] = $urlPreviewValue;
			$updateFields['HAS_PROPS'] = 'Y';
		}

		if ($result = \CBlogPost::update($postId, $updateFields))
		{
			if (
				Option::get('disk', 'successfully_converted', false)
				&& Loader::includeModule('disk')
				&& ($storage = \Bitrix\Disk\Driver::getInstance()->getStorageByUserId($postFields['AUTHOR_ID']))
				&& ($folder = $storage->getFolderForUploadedFiles())
			)
			{
				$filesList = [];

				if (
					(
						!empty($params['FILES'])
						|| !empty($params['UF_BLOG_POST_FILE'])
					)
					&& $scope === \Bitrix\Main\Engine\Controller::SCOPE_REST
				)
				{
					$postUF = $USER_FIELD_MANAGER->getUserFields('BLOG_POST', $postId, LANGUAGE_ID);
					if (
						!empty($postUF['UF_BLOG_POST_FILE'])
						&& !empty($postUF['UF_BLOG_POST_FILE']['VALUE'])
					)
					{
						$filesList = array_merge($filesList, $postUF['UF_BLOG_POST_FILE']['VALUE']);
					}

					$needToDelete = false;

					if (!empty($params['FILES']))
					{
						foreach ($params['FILES'] as $key => $fileData)
						{
							if (
								$fileData === 'del'
								&& in_array($key, $filesList)
							)
							{
								foreach ($filesList as $i => $v)
								{
									if ($v == $key)
									{
										unset($filesList[$i]);
										$needToDelete = true;
									}
								}
							}
							else
							{
								$fileFields = \CRestUtil::saveFile($fileData);

								if (is_array($fileFields))
								{
									$file = $folder->uploadFile(
										$fileFields,
										[
											'NAME' => $fileFields['name'],
											'CREATED_BY' => $postFields['AUTHOR_ID']
										],
										[],
										true
									);

									if ($file)
									{
										$filesList[] = FileUserType::NEW_FILE_PREFIX . $file->getId();
									}
								}
							}
						}
					}
					elseif (
						isset($params['UF_BLOG_POST_FILE'])
						&& is_array($params['UF_BLOG_POST_FILE'])
					)
					{
						if (
							count($params['UF_BLOG_POST_FILE']) === 1
							&& $params['UF_BLOG_POST_FILE'][0] === 'empty'
						)
						{
							$filesList = [];
							$needToDelete = true;
						}
						else
						{
							$filesList = array_unique(array_merge($filesList, array_map(function($value) {
								return (
									preg_match('/^' . FileUserType::NEW_FILE_PREFIX . '(\d+)$/i', $value)
										? $value
										: (int)$value
								);
							}, $params['UF_BLOG_POST_FILE'])));
						}
					}
				}
				elseif (
					!empty($params['UF_BLOG_POST_FILE'])
					&& $scope === \Bitrix\Main\Engine\Controller::SCOPE_AJAX
				)
				{
					$filesList = array_unique(array_merge($filesList, array_map(function($value) {
						return (
						preg_match('/^' . FileUserType::NEW_FILE_PREFIX . '(\d+)$/i', $value)
							? $value
							: (int)$value
						);
					}, $params['UF_BLOG_POST_FILE'])));
				}

				if (
					!empty($filesList)
					|| $needToDelete
				)
				{
					\CBlogPost::update($postId, [
						'HAS_PROPS' => 'Y',
						'UF_BLOG_POST_FILE' => $filesList
					]);
				}
			}

			BXClearCache(true, ComponentHelper::getBlogPostCacheDir([
				'TYPE' => 'post',
				'POST_ID' => $postId
			]));
			BXClearCache(true, ComponentHelper::getBlogPostCacheDir([
				'TYPE' => 'post_general',
				'POST_ID' => $postId
			]));
			BXClearCache(true, ComponentHelper::getBlogPostCacheDir([
				'TYPE' => 'posts_popular',
				'SITE_ID' => $siteId
			]));

			$updateFields['AUTHOR_ID'] = $postFields['AUTHOR_ID'];

			if ($postFields['PUBLISH_STATUS'] === BLOG_PUBLISH_STATUS_PUBLISH)
			{
				if ($updateFields['PUBLISH_STATUS'] === BLOG_PUBLISH_STATUS_DRAFT)
				{
					\CBlogPost::deleteLog($postId);
				}
				elseif ($updateFields['PUBLISH_STATUS'] === BLOG_PUBLISH_STATUS_PUBLISH)
				{
					\CBlogPost::updateLog($postId, $updateFields, $blog, [
						'allowVideo' => Option::get('blog', 'allow_video', 'Y'),
						'PATH_TO_SMILE' => false
					]);
				}
			}

			if (
				isset($params['IMPORTANT'])
				&& $params['IMPORTANT'] === 'Y'
			)
			{
				\CBlogUserOptions::setOption($result, 'BLOG_POST_IMPRTNT', 'Y', $currentUserId);

				if (defined('BX_COMP_MANAGED_CACHE'))
				{
					$CACHE_MANAGER->clearByTag('blogpost_important_all');
				}
			}
		}

		return $result;
	}

	public static function getBlogPostPerm(array $params = [])
	{
		global $USER, $APPLICATION;

		if (!Loader::includeModule('blog'))
		{
			$APPLICATION->throwException('Blog module not installed', 'SONET_CONTROLLER_LIVEFEED_BLOGPOST_UPDATE_ERROR');
			return false;
		}

		$postId = $params['POST_ID'];

		$currentUserId = (
			isset($params['USER_ID'])
			&& (int)$params['USER_ID'] > 0
			&& Livefeed::isAdmin()
				? (int)$params['USER_ID']
				: (int)$USER->getId()
		);

		$arPost = self::getBlogPostFields($postId);

		if ((int)$arPost['AUTHOR_ID'] === $currentUserId)
		{
			$result = \Bitrix\Blog\Item\Permissions::FULL;
		}
		else
		{
			if (\CSocNetUser::isUserModuleAdmin($currentUserId, SITE_ID))
			{
				$result = \Bitrix\Blog\Item\Permissions::FULL;
			}
			else
			{
				$postItem = \Bitrix\Blog\Item\Post::getById($postId);
				$permsResult = $postItem->getSonetPerms([
					'CHECK_FULL_PERMS' => true
				]);
				$result = $permsResult['PERM'];
				if (
					$result <= \Bitrix\Blog\Item\Permissions::READ
					&& $permsResult['READ_BY_OSG']
				)
				{
					$result = \Bitrix\Blog\Item\Permissions::READ;
				}
			}
		}

		return $result;
	}

	public static function getBlogPostFields($postId)
	{
		global $APPLICATION;
		$tzOffset = \CTimeZone::getOffset();

		$cacheTtl = 2592000;
		$cacheId = 'blog_post_socnet_general_' . $postId . '_' . LANGUAGE_ID.($tzOffset <> 0 ? '_' . $tzOffset : '') . '_' . \Bitrix\Main\Context::getCurrent()->getCulture()->getDateTimeFormat() . '_rest';
		$cacheDir = ComponentHelper::getBlogPostCacheDir([
			'TYPE' => 'post_general',
			'POST_ID' => $postId
		]);

		if (!Loader::includeModule('blog'))
		{
			$APPLICATION->throwException('Blog module not installed', 'SONET_CONTROLLER_LIVEFEED_BLOGPOST_MODULE_ERROR');
			return false;
		}

		$cache = new \CPHPCache;
		if ($cache->initCache($cacheTtl, $cacheId, $cacheDir))
		{
			$postFields = $cache->getVars();
			$postItem = new \Bitrix\Blog\Item\Post;
			$postItem->setFields($postFields);
		}
		else
		{
			$cache->startDataCache();
			$postItem = \Bitrix\Blog\Item\Post::getById($postId);
			$postFields = $postItem->getFields();
			$cache->endDataCache($postFields);
		}

		return $postFields;
	}

	public static function getBlogPostEventId(array $params = [])
	{
		global $USER_FIELD_MANAGER;

		if (!Loader::includeModule('blog'))
		{
			throw new \Bitrix\Main\SystemException('No blog module installed', 'SONET_CONTROLLER_LIVEFEED_BLOGPOST_MODULE_BLOG_NOT_INSTALLED');
		}

		$postId = (isset($params['postId']) && (int)$params['postId'] > 0 ? (int)$params['postId'] : 0);
		if ($postId <= 0)
		{
			throw new \Bitrix\Main\SystemException('Empty post ID', 'SONET_CONTROLLER_LIVEFEED_BLOGPOST_EMPTY_POST_ID');
		}

		$eventId = \Bitrix\Blog\Integration\Socialnetwork\Log::EVENT_ID_POST;
		$postUserFields = $USER_FIELD_MANAGER->getUserFields('BLOG_POST', $postId, LANGUAGE_ID);

		if (
			isset($postUserFields['UF_BLOG_POST_IMPRTNT'])
			&& isset($postUserFields['UF_BLOG_POST_IMPRTNT']['VALUE'])
			&& (int)$postUserFields['UF_BLOG_POST_IMPRTNT']['VALUE'] > 0
		)
		{
			$eventId = \Bitrix\Blog\Integration\Socialnetwork\Log::EVENT_ID_POST_IMPORTANT;
		}
		elseif (
			isset($postUserFields['UF_BLOG_POST_VOTE'])
			&& isset($postUserFields['UF_BLOG_POST_VOTE']['VALUE'])
			&& (int)$postUserFields['UF_BLOG_POST_VOTE']['VALUE'] > 0
		)
		{
			$eventId = \Bitrix\Blog\Integration\Socialnetwork\Log::EVENT_ID_POST_VOTE;
		}
		elseif (
			isset($postUserFields['UF_GRATITUDE'])
			&& isset($postUserFields['UF_GRATITUDE']['VALUE'])
			&& (int)$postUserFields['UF_GRATITUDE']['VALUE'] > 0
		)
		{
			$eventId = \Bitrix\Blog\Integration\Socialnetwork\Log::EVENT_ID_POST_GRAT;
		}

		return $eventId;
	}
}
