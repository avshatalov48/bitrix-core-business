<?php

namespace Bitrix\Socialnetwork;

use Bitrix\Blog\Item\Post;
use Bitrix\Crm\Activity\Provider\Tasks\Task;
use Bitrix\Main\Component\ParameterSigner;
use Bitrix\Disk\Driver;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bitrix\Main\EventManager;
use Bitrix\Main\Security\Sign\BadSignatureException;
use Bitrix\Main\Update\Stepper;
use Bitrix\Main\UrlPreview\UrlPreview;
use Bitrix\Socialnetwork\Item\Log;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Application;
use Bitrix\Disk\Uf\FileUserType;
use Bitrix\Disk\AttachedObject;
use Bitrix\Disk\File;
use Bitrix\Disk\TypeFile;
use Bitrix\Socialnetwork\Helper\Mention;
use Bitrix\Main\Web\Json;
use Bitrix\Main\ArgumentException;

Loc::loadMessages(__FILE__);

class ComponentHelper
{
	protected static $postsCache = [];
	protected static $commentsCache = [];
	protected static $commentListsCache = [];
	protected static $commentCountCache = [];
	protected static $authorsCache = [];
	protected static $destinationsCache = [];

	/**
	 * Returns data of a blog post
	 *
	 * @param int $postId Blog Post Id.
	 * @param string $languageId 2-char language Id.
	 * @return array|bool|false|mixed|null
	 * @throws Main\LoaderException
	 * @throws Main\SystemException
	*/
	public static function getBlogPostData($postId, $languageId)
	{
		global $USER_FIELD_MANAGER;

		if (isset(self::$postsCache[$postId]))
		{
			$result = self::$postsCache[$postId];
		}
		else
		{
			if (!Loader::includeModule('blog'))
			{
				throw new Main\SystemException("Could not load 'blog' module.");
			}

			$res = \CBlogPost::getList(
				[],
				[
					"ID" => $postId
				],
				false,
				false,
				[ 'ID', 'BLOG_GROUP_ID', 'BLOG_GROUP_SITE_ID', 'BLOG_ID', 'PUBLISH_STATUS', 'TITLE', 'AUTHOR_ID', 'ENABLE_COMMENTS', 'NUM_COMMENTS', 'VIEWS', 'CODE', 'MICRO', 'DETAIL_TEXT', 'DATE_PUBLISH', 'CATEGORY_ID', 'HAS_SOCNET_ALL', 'HAS_TAGS', 'HAS_IMAGES', 'HAS_PROPS', 'HAS_COMMENT_IMAGES' ]
			);

			if ($result = $res->fetch())
			{
				if (!empty($result['DETAIL_TEXT']))
				{
					$result['DETAIL_TEXT'] = \Bitrix\Main\Text\Emoji::decode($result['DETAIL_TEXT']);
				}

				$result["ATTACHMENTS"] = [];

				if($result["HAS_PROPS"] !== "N")
				{
					$userFields = $USER_FIELD_MANAGER->getUserFields("BLOG_POST", $postId, $languageId);
					$postUf = [ 'UF_BLOG_POST_FILE' ];
					foreach ($userFields as $fieldName => $userField)
					{
						if (!in_array($fieldName, $postUf))
						{
							unset($userFields[$fieldName]);
						}
					}

					if (
						!empty($userFields["UF_BLOG_POST_FILE"])
						&& !empty($userFields["UF_BLOG_POST_FILE"]["VALUE"])
					)
					{
						$result["ATTACHMENTS"] = self::getAttachmentsData($userFields["UF_BLOG_POST_FILE"]["VALUE"], $result["BLOG_GROUP_SITE_ID"]);
					}
				}

				$result["DETAIL_TEXT"] = self::convertDiskFileBBCode(
					$result["DETAIL_TEXT"],
					'BLOG_POST',
					$postId,
					$result["AUTHOR_ID"],
					$result["ATTACHMENTS"]
				);

				$result["DETAIL_TEXT_FORMATTED"] = preg_replace(
					[
						'|\[DISK\sFILE\sID=[n]*\d+\]|',
						'|\[DOCUMENT\sID=[n]*\d+\]|'
					],
					'',
					$result["DETAIL_TEXT"]
				);

				$result['DETAIL_TEXT_FORMATTED'] = Mention::clear($result['DETAIL_TEXT_FORMATTED']);

				$p = new \blogTextParser();
				$p->arUserfields = [];

				$images = [];
				$allow = [ 'IMAGE' => 'Y' ];
				$parserParameters = [];

				$result["DETAIL_TEXT_FORMATTED"] = $p->convert($result["DETAIL_TEXT_FORMATTED"], false, $images, $allow, $parserParameters);

				$title = (
					$result["MICRO"] === "Y"
						? \blogTextParser::killAllTags($result["DETAIL_TEXT_FORMATTED"])
						: htmlspecialcharsEx($result["TITLE"])
				);

				$title = preg_replace(
					'|\[MAIL\sDISK\sFILE\sID=[n]*\d+\]|',
					'',
					$title
				);

				$title = str_replace([ "\r\n", "\n", "\r" ], " ", $title);
				$result["TITLE_FORMATTED"] = \TruncateText($title, 100);
				$result["DATE_PUBLISH_FORMATTED"] = self::formatDateTimeToGMT($result['DATE_PUBLISH'], $result['AUTHOR_ID']);
			}

			self::$postsCache[$postId] = $result;
		}

		return $result;
	}

	/**
	 * Returns data of blog post destinations
	 *
	 * @param int $postId Blog Post Id.
	 * @return array
	 * @throws Main\LoaderException
	 * @throws Main\SystemException
	*/
	public static function getBlogPostDestinations($postId)
	{
		if (isset(self::$destinationsCache[$postId]))
		{
			$result = self::$destinationsCache[$postId];
		}
		else
		{
			$result = [];

			if (!Loader::includeModule('blog'))
			{
				throw new Main\SystemException("Could not load 'blog' module.");
			}

			$sonetPermission = \CBlogPost::getSocnetPermsName($postId);
			if (!empty($sonetPermission))
			{
				foreach ($sonetPermission as $typeCode => $type)
				{
					foreach ($type as $destination)
					{
						$name = false;

						if ($typeCode === "SG")
						{
							if ($sonetGroup = \CSocNetGroup::getByID($destination["ENTITY_ID"]))
							{
								$name = $sonetGroup["NAME"];
							}
						}
						elseif ($typeCode === "U")
						{
							if(in_array("US" . $destination["ENTITY_ID"], $destination["ENTITY"], true))
							{
								$name = "#ALL#";
								Loader::includeModule('intranet');
							}
							else
							{
								$name = \CUser::formatName(
									\CSite::getNameFormat(false),
									[
										"NAME" => $destination["~U_NAME"],
										"LAST_NAME" => $destination["~U_LAST_NAME"],
										"SECOND_NAME" => $destination["~U_SECOND_NAME"],
										"LOGIN" => $destination["~U_LOGIN"]
									],
									true
								);
							}
						}
						elseif ($typeCode === "DR")
						{
							$name = $destination["EL_NAME"];
						}

						if ($name)
						{
							$result[] = $name;
						}
					}
				}
			}

			self::$destinationsCache[$postId] = $result;
		}

		return $result;
	}

	/**
	 * Returns data of a blog post/comment author
	 *
	 * @param int $authorId User Id.
	 * @param array $params Format parameters (avatar size etc).
	 * @return array
	 * @throws Main\LoaderException
	 * @throws Main\SystemException
	*/
	public static function getBlogAuthorData($authorId, $params): array
	{
		if (isset(self::$authorsCache[$authorId]))
		{
			$result = self::$authorsCache[$authorId];
		}
		else
		{
			if (!Loader::includeModule('blog'))
			{
				throw new Main\SystemException("Could not load 'blog' module.");
			}

			$result = \CBlogUser::getUserInfo(
				(int)$authorId,
				'',
				[
					"AVATAR_SIZE" => (
						isset($params["AVATAR_SIZE"])
						&& (int)$params["AVATAR_SIZE"] > 0
							? (int)$params["AVATAR_SIZE"]
							: false
					),
					"AVATAR_SIZE_COMMENT" => (
						isset($params["AVATAR_SIZE_COMMENT"])
						&& (int)$params["AVATAR_SIZE_COMMENT"] > 0
							? (int)$params["AVATAR_SIZE_COMMENT"]
							: false
					),
					"RESIZE_IMMEDIATE" => "Y"
				]
			);

			$result["NAME_FORMATTED"] = \CUser::formatName(
				\CSite::getNameFormat(false),
				[
					"NAME" => $result["~NAME"],
					"LAST_NAME" => $result["~LAST_NAME"],
					"SECOND_NAME" => $result["~SECOND_NAME"],
					"LOGIN" => $result["~LOGIN"]
				],
				true
			);

			self::$authorsCache[$authorId] = $result;
		}

		return $result;
	}

	/**
	 * Returns full list of blog post comments
	 *
	 * @param int $postId Blog Post Id.
	 * @param array $params Additional paramaters.
	 * @param string $languageId Language Id (2-char).
	 * @param array &$authorIdList List of User Ids.
	 * @return array
	 * @throws Main\LoaderException
	 * @throws Main\SystemException
	*/
	public static function getBlogCommentListData($postId, $params, $languageId, &$authorIdList = []): array
	{
		if (isset(self::$commentListsCache[$postId]))
		{
			$result = self::$commentListsCache[$postId];
		}
		else
		{
			$result = [];

			if (!Loader::includeModule('blog'))
			{
				throw new Main\SystemException("Could not load 'blog' module.");
			}

			$p = new \blogTextParser();

			$selectedFields = [ 'ID', 'BLOG_GROUP_ID', 'BLOG_GROUP_SITE_ID', 'BLOG_ID', 'POST_ID', 'AUTHOR_ID', 'AUTHOR_NAME', 'AUTHOR_EMAIL', 'POST_TEXT', 'DATE_CREATE', 'PUBLISH_STATUS', 'HAS_PROPS', 'SHARE_DEST' ];

			$connection = Application::getConnection();
			if ($connection instanceof \Bitrix\Main\DB\MysqlCommonConnection)
			{
				$selectedFields[] = "DATE_CREATE_TS";
			}

			$res = \CBlogComment::getList(
				[ 'ID' => 'DESC' ],
				[
					"PUBLISH_STATUS" => BLOG_PUBLISH_STATUS_PUBLISH,
					"POST_ID" => $postId
				],
				false,
				[
					"nTopCount" => $params["COMMENTS_COUNT"]
				],
				$selectedFields
			);

			while ($comment = $res->fetch())
			{
				self::processCommentData($comment, $languageId, $p, [ "MAIL" => (isset($params["MAIL"]) && $params["MAIL"] === "Y" ? "Y" : "N") ]);

				$result[] = $comment;

				if (!in_array((int)$comment["AUTHOR_ID"], $authorIdList, true))
				{
					$authorIdList[] = (int)$comment["AUTHOR_ID"];
				}
			}

			if (!empty($result))
			{
				$result = array_reverse($result);
			}

			self::$commentListsCache[$postId] = $result;
		}

		return $result;
	}

	/**
	 * Returns a number of blog post comments
	 *
	 * @param int $postId Blog Post Id.
	 * @return bool|int
	 * @throws Main\LoaderException
	 * @throws Main\SystemException
	*/
	public static function getBlogCommentListCount($postId)
	{
		if (isset(self::$commentCountCache[$postId]))
		{
			$result = self::$commentCountCache[$postId];
		}
		else
		{
			if (!Loader::includeModule('blog'))
			{
				throw new Main\SystemException("Could not load 'blog' module.");
			}

			$selectedFields = [ 'ID' ];

			$result = \CBlogComment::getList(
				[ 'ID' => 'DESC' ],
				[
					"PUBLISH_STATUS" => BLOG_PUBLISH_STATUS_PUBLISH,
					"POST_ID" => $postId,
				],
				[], // count only
				false,
				$selectedFields
			);

			self::$commentCountCache[$postId] = $result;
		}

		return $result;
	}


	/**
	 * Returns data of a blog comment
	 *
	 * @param int $commentId Comment Id.
	 * @param string $languageId Language id (2-chars).
	 * @return array|bool|false|mixed|null
	*/
	public static function getBlogCommentData($commentId, $languageId)
	{
		$result = [];

		if (isset(self::$commentsCache[$commentId]))
		{
			$result = self::$commentsCache[$commentId];
		}
		else
		{
			$selectedFields = [ "ID", "BLOG_GROUP_ID", "BLOG_GROUP_SITE_ID", "BLOG_ID", "POST_ID", "AUTHOR_ID", "AUTHOR_NAME", "AUTHOR_EMAIL", "POST_TEXT", "DATE_CREATE", "PUBLISH_STATUS", "HAS_PROPS", "SHARE_DEST" ];

			$connection = Application::getConnection();
			if ($connection instanceof \Bitrix\Main\DB\MysqlCommonConnection)
			{
				$selectedFields[] = "DATE_CREATE_TS";
			}

			$res = \CBlogComment::getList(
				[],
				[
					"ID" => $commentId
				],
				false,
				false,
				$selectedFields
			);

			if ($comment = $res->fetch())
			{
				$p = new \blogTextParser();

				self::processCommentData($comment, $languageId, $p);

				$result = $comment;
			}

			self::$commentsCache[$commentId] = $result;
		}

		return $result;
	}

	/**
	 * Processes comment data, rendering formatted text and date
	 *
	 * @param array $comment Comment fields set.
	 * @param string $languageId Language Id (2-chars).
	 * @param \blogTextParser $p TextParser object.
	*/
	private static function processCommentData(&$comment, $languageId, $p, $params = []): void
	{
		global $USER_FIELD_MANAGER;

		$isMail = (
			is_array($params)
			&& isset($params["MAIL"])
			&& $params["MAIL"] === 'Y'
		);

		$comment["ATTACHMENTS"] = $comment["PROPS"] = [];

		if ($commentAuxProvider = \Bitrix\Socialnetwork\CommentAux\Base::findProvider(
			$comment,
			[
				"mobile" => (isset($params["MOBILE"]) && $params["MOBILE"] === "Y"),
				"mail" => (isset($params["MAIL"]) && $params["MAIL"] === "Y"),
				"cache" => true
			]
		))
		{
			$comment["POST_TEXT_FORMATTED"] = $commentAuxProvider->getText();
			$arComment["AUX_TYPE"] = $commentAuxProvider->getType();
		}
		else
		{
			if($comment["HAS_PROPS"] !== "N")
			{
				$userFields = $comment["PROPS"] = $USER_FIELD_MANAGER->getUserFields("BLOG_COMMENT", $comment["ID"], $languageId);
				$commentUf = [ 'UF_BLOG_COMMENT_FILE' ];
				foreach ($userFields as $fieldName => $userField)
				{
					if (!in_array($fieldName, $commentUf, true))
					{
						unset($userFields[$fieldName]);
					}
				}

				if (
					!empty($userFields["UF_BLOG_COMMENT_FILE"])
					&& !empty($userFields["UF_BLOG_COMMENT_FILE"]["VALUE"])
				)
				{
					$comment["ATTACHMENTS"] = self::getAttachmentsData($userFields["UF_BLOG_COMMENT_FILE"]["VALUE"], $comment["BLOG_GROUP_SITE_ID"]);
				}

				if (
					$isMail
					&& isset($comment["PROPS"]["UF_BLOG_COMM_URL_PRV"])
				)
				{
					unset($comment["PROPS"]["UF_BLOG_COMM_URL_PRV"]);
				}
			}

			$comment["POST_TEXT"] = self::convertDiskFileBBCode(
				$comment["POST_TEXT"],
				'BLOG_COMMENT',
				$comment["ID"],
				$comment["AUTHOR_ID"],
				$comment["ATTACHMENTS"]
			);

			$comment["POST_TEXT_FORMATTED"] = preg_replace(
				[
					'|\[DISK\sFILE\sID=[n]*\d+\]|',
					'|\[DOCUMENT\sID=[n]*\d+\]|'
				],
				'',
				$comment["POST_TEXT"]
			);

			$comment['POST_TEXT_FORMATTED'] = Mention::clear($comment['POST_TEXT_FORMATTED']);

			if ($p)
			{
				$p->arUserfields = [];
			}
			$images = [];
			$allow = [ 'IMAGE' => 'Y' ];
			$parserParameters = [];

			$comment["POST_TEXT_FORMATTED"] = $p->convert($comment["POST_TEXT_FORMATTED"], false, $images, $allow, $parserParameters);
		}

		$comment["DATE_CREATE_FORMATTED"] = self::formatDateTimeToGMT($comment['DATE_CREATE'], $comment['AUTHOR_ID']);
	}

	/**
	 * Returns mail-hash url
	 *
	 * @param string $url Entity Link.
	 * @param int $userId User Id.
	 * @param string $entityType Entity Type.
	 * @param int $entityId Entity Id.
	 * @param string $siteId Site id (2-char).
	 * @return bool|string
	 * @throws Main\LoaderException
	*/
	public static function getReplyToUrl($url, $userId, $entityType, $entityId, $siteId, $backUrl = null)
	{
		$result = false;

		$url = (string)$url;
		$userId = (int)$userId;
		$entityType = (string)$entityType;
		$entityId = (int)$entityId;
		$siteId = (string)$siteId;

		if (
			$url === ''
			|| $userId <= 0
			|| $entityType === ''
			|| $entityId <= 0
			|| $siteId === ''
			|| !Loader::includeModule('mail')
		)
		{
			return $result;
		}

		$urlRes = \Bitrix\Mail\User::getReplyTo(
			$siteId,
			$userId,
			$entityType,
			$entityId,
			$url,
			$backUrl
		);
		if (is_array($urlRes))
		{
			[ , $backUrl ] = $urlRes;

			if ($backUrl)
			{
				$result = $backUrl;
			}
		}

		return $result;
	}

	/**
	 * Returns data of attached files
	 *
	 * @param array $valueList Attachments List.
	 * @param string|bool|false $siteId Site Id (2-chars).
	 * @return array
	 * @throws Main\LoaderException
	*/
	public static function getAttachmentsData($valueList, $siteId = false): array
	{
		$result = [];

		if (!Loader::includeModule('disk'))
		{
			return $result;
		}

		if (
			!$siteId
			|| (string)$siteId === ''
		)
		{
			$siteId = SITE_ID;
		}

		foreach ($valueList as $value)
		{
			$attachedObject = AttachedObject::loadById($value, [ 'OBJECT' ]);
			if(
				!$attachedObject
				|| !$attachedObject->getFile()
			)
			{
				continue;
			}

			$attachedObjectUrl = \Bitrix\Disk\UrlManager::getUrlUfController('show', [ 'attachedId' => $value ]);

			$result[$value] = [
				"ID" => $value,
				"OBJECT_ID" => $attachedObject->getFile()->getId(),
				"NAME" => $attachedObject->getFile()->getName(),
				"SIZE" => \CFile::formatSize($attachedObject->getFile()->getSize()),
				"URL" => $attachedObjectUrl,
				"IS_IMAGE" => TypeFile::isImage($attachedObject->getFile())
			];
		}

		return $result;
	}

	/**
	 * Processes disk objects list and generates external links (for inline images) if needed
	 *
	 * @param array $valueList
	 * @param string $entityType Entity Type.
	 * @param int $entityId Entity Id.
	 * @param int $authorId User Id.
	 * @param array $attachmentList Attachments List.
	 * @return array
	 * @throws Main\LoaderException
	*/
	public static function getAttachmentUrlList($valueList = [], $entityType = '', $entityId = 0, $authorId = 0, $attachmentList = []): array
	{
		$result = [];

		if (
			empty($valueList)
			|| empty($attachmentList)
			|| (int)$authorId <= 0
			|| (int)$entityId <= 0
			|| !Loader::includeModule('disk')
		)
		{
			return $result;
		}

		$userFieldManager = Driver::getInstance()->getUserFieldManager();
		[ $connectorClass, $moduleId ] = $userFieldManager->getConnectorDataByEntityType($entityType);

		foreach($valueList as $value)
		{
			$attachedFileId = false;
			$attachedObject = false;

			[ $type, $realValue ] = FileUserType::detectType($value);
			if ($type === FileUserType::TYPE_NEW_OBJECT)
			{
				$attachedObject = AttachedObject::load([
					'=ENTITY_TYPE' => $connectorClass,
					'ENTITY_ID' => $entityId,
					'=MODULE_ID' => $moduleId,
					'OBJECT_ID'=> $realValue
				], [ 'OBJECT' ]);

				if($attachedObject)
				{
					$attachedFileId = $attachedObject->getId();
				}
			}
			else
			{
				$attachedFileId = $realValue;
			}

			if (
				(int)$attachedFileId > 0
				&& !empty($attachmentList[$attachedFileId])
			)
			{
				if (!$attachmentList[$attachedFileId]["IS_IMAGE"])
				{
					$result[$value] = [
						'TYPE' => 'file',
						'URL' => $attachmentList[$attachedFileId]["URL"]
					];
				}
				else
				{
					if (!$attachedObject)
					{
						$attachedObject = AttachedObject::loadById($attachedFileId, [ 'OBJECT' ]);
					}

					if ($attachedObject)
					{
						$file = $attachedObject->getFile();

						$extLinks = $file->getExternalLinks([
							'filter' => [
								'OBJECT_ID' => $file->getId(),
								'CREATED_BY' => $authorId,
								'TYPE' => \Bitrix\Disk\Internals\ExternalLinkTable::TYPE_MANUAL,
								'IS_EXPIRED' => false,
							],
							'limit' => 1,
						]);

						if (empty($extLinks))
						{
							$externalLink = $file->addExternalLink([
								'CREATED_BY' => $authorId,
								'TYPE' => \Bitrix\Disk\Internals\ExternalLinkTable::TYPE_MANUAL,
							]);
						}
						else
						{
							/** @var \Bitrix\Disk\ExternalLink $externalLink */
							$externalLink = reset($extLinks);
						}

						if ($externalLink)
						{
							$originalFile = $file->getFile();

							$result[$value] = [
								'TYPE' => 'image',
								'URL' => Driver::getInstance()->getUrlManager()->getUrlExternalLink(
									[
										'hash' => $externalLink->getHash(),
										'action' => 'showFile'
									],
									true
								),
								'WIDTH' => (int)$originalFile["WIDTH"],
								'HEIGHT' => (int)$originalFile["HEIGHT"]
							];
						}
					}
				}
			}
		}

		return $result;
	}

	/**
	 * Converts formatted text replacing pseudo-BB code MAIL DISK, using calculated URLs
	 *
	 * @param string $text Text to convert.
	 * @param array $attachmentList Attachments List.
	 * @return mixed|string
	*/
	public static function convertMailDiskFileBBCode($text = '', $attachmentList = [])
	{
		if (preg_match_all('|\[MAIL\sDISK\sFILE\sID=([n]*\d+)\]|', $text, $matches))
		{
			foreach($matches[1] as $inlineFileId)
			{
				$attachmentId = false;
				if (mb_strpos($inlineFileId, 'n') === 0)
				{
					$found = false;
					foreach($attachmentList as $attachmentId => $attachment)
					{
						if (
							isset($attachment["OBJECT_ID"])
							&& (int)$attachment["OBJECT_ID"] === (int)mb_substr($inlineFileId, 1)
						)
						{
							$found = true;
							break;
						}
					}
					if (!$found)
					{
						$attachmentId = false;
					}
				}
				else
				{
					$attachmentId = $inlineFileId;
				}

				if ((int)$attachmentId > 0)
				{
					$text = preg_replace(
						'|\[MAIL\sDISK\sFILE\sID='.$inlineFileId.'\]|',
						'[URL='.$attachmentList[$attachmentId]["URL"].']['.$attachmentList[$attachmentId]["NAME"].'][/URL]',
						$text
					);
				}
			}

			$p = new \CTextParser();
			$p->allow = [ 'HTML' => 'Y', 'ANCHOR' => 'Y' ];
			$text = $p->convertText($text);
		}

		return $text;
	}

	/**
	 * Converts DISK FILE BB-code to the pseudo-BB code MAIL DISK FILE or IMG BB-code
	 *
	 * @param string $text Text to convert.
	 * @param string $entityType Entity Type.
	 * @param int $entityId Entity Type.
	 * @param int $authorId User id.
	 * @param array $attachmentList Attachments List.
	 * @return mixed
	*/
	public static function convertDiskFileBBCode($text, $entityType, $entityId, $authorId, $attachmentList = [])
	{
		$text = trim((string)$text);
		$authorId = (int)$authorId;
		$entityType = (string)$entityType;
		$entityId = (int)$entityId;

		if (
			$text === ''
			|| empty($attachmentList)
			|| $authorId <= 0
			|| $entityType === ''
			|| $entityId <= 0
		)
		{
			return $text;
		}

		if (preg_match_all('|\[DISK\sFILE\sID=([n]*\d+)\]|', $text, $matches))
		{
			$attachmentUrlList = self::getAttachmentUrlList(
				$matches[1],
				$entityType,
				$entityId,
				$authorId,
				$attachmentList
			);

			foreach($matches[1] as $inlineFileId)
			{
				if (!empty($attachmentUrlList[$inlineFileId]))
				{
					$needCreatePicture = false;
					$sizeSource = $sizeDestination = [];
					\CFile::scaleImage(
						$attachmentUrlList[$inlineFileId]['WIDTH'], $attachmentUrlList[$inlineFileId]['HEIGHT'],
						[ 'width' => 400, 'height' => 1000 ], BX_RESIZE_IMAGE_PROPORTIONAL,
						$needCreatePicture, $sizeSource, $sizeDestination
					);

					$replacement = (
						$attachmentUrlList[$inlineFileId]["TYPE"] === 'image'
							? '[IMG WIDTH='.(int)$sizeDestination['width'].' HEIGHT='.(int)$sizeDestination['height'].']'.\htmlspecialcharsBack($attachmentUrlList[$inlineFileId]["URL"]).'[/IMG]'
							: '[MAIL DISK FILE ID='.$inlineFileId.']'
					);
					$text = preg_replace(
						'|\[DISK\sFILE\sID='.$inlineFileId.'\]|',
						$replacement,
						$text
					);
				}
			}
		}

		return $text;
	}

	/**
	 * Calculates if text has inline disk file images
	 *
	 * @param string $text text with BB-codes
	 * @param array $ufData uf of disk type.
	 * @return boolean
	 */
	public static function hasTextInlineImage(string $text = '', array $ufData = []): bool
	{
		$result = false;

		if (
			preg_match_all("#\\[disk file id=(n?\\d+)\\]#is".BX_UTF_PCRE_MODIFIER, $text, $matches)
			&& Loader::includeModule('disk')
		)
		{
			$userFieldManager = Driver::getInstance()->getUserFieldManager();

			foreach ($matches[1] as $id)
			{
				$fileModel = null;
				[ $type, $realValue ] = FileUserType::detectType($id);

				if ($type === FileUserType::TYPE_NEW_OBJECT)
				{
					$fileModel = File::loadById($realValue);
					if(!$fileModel)
					{
						continue;
					}
				}
				else
				{
					$attachedModel = $userFieldManager->getAttachedObjectById($realValue);
					if(!$attachedModel)
					{
						continue;
					}

					$attachedModel->setOperableEntity([
						'ENTITY_ID' => $ufData['ENTITY_ID'],
						'ENTITY_VALUE_ID' => $ufData['ENTITY_VALUE_ID']
					]);
					$fileModel = $attachedModel->getFile();
				}

				if(TypeFile::isImage($fileModel))
				{
					$result = true;
					break;
				}
			}
		}

		return $result;
	}

	/**
	 * Formsts date time to the value of author + GMT offset
	 *
	 * @param string $dateTimeSource Date/Time in site format.
	 * @param int $authorId User Id.
	 * @return string
	*/
	public static function formatDateTimeToGMT($dateTimeSource, $authorId): string
	{
		if (empty($dateTimeSource))
		{
			return '';
		}

		$serverTs = \MakeTimeStamp($dateTimeSource) - \CTimeZone::getOffset();
		$serverGMTOffset = (int)date('Z');
		$authorOffset = (int)\CTimeZone::getOffset($authorId);

		$authorGMTOffset = $serverGMTOffset + $authorOffset;
		$authorGMTOffsetFormatted = 'GMT';
		if ($authorGMTOffset !== 0)
		{
			$authorGMTOffsetFormatted .= ($authorGMTOffset >= 0 ? '+' : '-').sprintf('%02d', floor($authorGMTOffset / 3600)).':'.sprintf('%02u', ($authorGMTOffset % 3600) / 60);
		}

		return \FormatDate(
				preg_replace('/[\/.,\s:][s]/', '', \Bitrix\Main\Type\Date::convertFormatToPhp(FORMAT_DATETIME)),
				($serverTs + $authorOffset)
			).' ('.$authorGMTOffsetFormatted.')';
	}

	/**
	 * Returns (non-idea) blog group list
	 *
	 * @param array $params Parameters.
	 * @return array
	 */
	public static function getSonetBlogGroupIdList($params): array
	{
		$result = [];

		if (!Loader::includeModule('blog'))
		{
			throw new Main\SystemException("Could not load 'blog' module.");
		}

		$cacheTtl = 3153600;
		$cacheId = 'blog_group_list_'.md5(serialize($params));
		$cacheDir = '/blog/group/';
		$cache = new \CPHPCache;

		if($cache->initCache($cacheTtl, $cacheId, $cacheDir))
		{
			$result = $cache->getVars();
		}
		else
		{
			$cache->startDataCache();

			$ideaBlogGroupIdList = array();
			if (ModuleManager::isModuleInstalled("idea"))
			{
				$res = \CSite::getList("sort", "desc", Array("ACTIVE" => "Y"));
				while ($site = $res->fetch())
				{
					$val = Option::get("idea", "blog_group_id", false, $site["LID"]);
					if ($val)
					{
						$ideaBlogGroupIdList[] = $val;
					}
				}
			}

			$filter = array();
			if (!empty($params["SITE_ID"]))
			{
				$filter['SITE_ID'] = $params["SITE_ID"];
			}
			if (!empty($ideaBlogGroupIdList))
			{
				$filter['!@ID'] = $ideaBlogGroupIdList;
			}

			$res = \CBlogGroup::getList(array(), $filter, false, false, array("ID"));
			while($blogGroup = $res->fetch())
			{
				$result[] = $blogGroup["ID"];
			}

			$cache->endDataCache($result);
		}

		return $result;
	}

	/**
	 * Creates a user blog (when it is the first post of the user)
	 *
	 * @param array $params Parameters.
	 * @return bool|array
	 * @throws Main\ArgumentException
	 * @throws Main\LoaderException
	 * @throws Main\SystemException
	*/
	public static function createUserBlog($params)
	{
		$result = false;

		if (!Loader::includeModule('blog'))
		{
			throw new Main\SystemException("Could not load 'blog' module.");
		}

		if (
			!isset($params["BLOG_GROUP_ID"], $params["USER_ID"], $params["SITE_ID"])
			|| (int)$params["BLOG_GROUP_ID"] <= 0
			|| (int)$params["USER_ID"] <= 0
			|| (string)$params["SITE_ID"] === ''
		)
		{
			return false;
		}

		if (
			!isset($params["PATH_TO_BLOG"])
			|| $params["PATH_TO_BLOG"] == ''
		)
		{
			$params["PATH_TO_BLOG"] = "";
		}

		$connection = Application::getConnection();
		$helper = $connection->getSqlHelper();

		$fields = array(
			"=DATE_UPDATE" => $helper->getCurrentDateTimeFunction(),
			"=DATE_CREATE" => $helper->getCurrentDateTimeFunction(),
			"GROUP_ID" => (int)$params["BLOG_GROUP_ID"],
			"ACTIVE" => "Y",
			"OWNER_ID" => (int)$params["USER_ID"],
			"ENABLE_COMMENTS" => "Y",
			"ENABLE_IMG_VERIF" => "Y",
			"EMAIL_NOTIFY" => "Y",
			"ENABLE_RSS" => "Y",
			"ALLOW_HTML" => "N",
			"ENABLE_TRACKBACK" => "N",
			"SEARCH_INDEX" => "Y",
			"USE_SOCNET" => "Y",
			"PERMS_POST" => Array(
				1 => "I",
				2 => "I"
			),
			"PERMS_COMMENT" => Array(
				1 => "P",
				2 => "P"
			)
		);

		$res = \Bitrix\Main\UserTable::getList(array(
			'order' => array(),
			'filter' => array(
				"ID" => $params["USER_ID"]
			),
			'select' => array("NAME", "LAST_NAME", "LOGIN")
		));

		if ($user = $res->fetch())
		{
			$fields["NAME"] = Loc::getMessage("BLG_NAME")." ".(
				$user["NAME"]."".$user["LAST_NAME"] === ''
					? $user["LOGIN"]
					: $user["NAME"]." ".$user["LAST_NAME"]
			);

			$fields["URL"] = str_replace(" ", "_", $user["LOGIN"])."-blog-".$params["SITE_ID"];
			$urlCheck = preg_replace("/[^a-zA-Z0-9_-]/i", "", $fields["URL"]);
			if ($urlCheck !== $fields["URL"])
			{
				$fields["URL"] = "u".$params["USER_ID"]."-blog-".$params["SITE_ID"];
			}

			if(\CBlog::getByUrl($fields["URL"]))
			{
				$uind = 0;
				do
				{
					$uind++;
					$fields["URL"] .= $uind;
				}
				while (\CBlog::getByUrl($fields["URL"]));
			}

			$fields["PATH"] = \CComponentEngine::makePathFromTemplate(
				$params["PATH_TO_BLOG"],
				array(
					"blog" => $fields["URL"],
					"user_id" => $fields["OWNER_ID"]
				)
			);

			if ($blogID = \CBlog::add($fields))
			{
				BXClearCache(true, "/blog/form/blog/");

				$rightsFound = false;

				$featureOperationPerms = \CSocNetFeaturesPerms::getOperationPerm(
					SONET_ENTITY_USER,
					$fields["OWNER_ID"],
					"blog",
					"view_post"
				);

				if ($featureOperationPerms === SONET_RELATIONS_TYPE_ALL)
				{
					$rightsFound = true;
				}

				if ($rightsFound)
				{
					\CBlog::addSocnetRead($blogID);
				}

				$result = \CBlog::getByID($blogID);
			}
		}

		return $result;
	}

	/**
	 * get urlPreview property value from text with links
	 *
	 * @param $text string
	 * @param bool|true $html
	 * @return bool|string
	 * @throws Main\ArgumentTypeException
	*/
	public static function getUrlPreviewValue($text, $html = true)
	{
		static $parser = false;
		$value = false;

		if (empty($text))
		{
			return $value;
		}

		if (!$parser)
		{
			$parser = new \CTextParser();
		}

		if ($html)
		{
			$text = $parser->convertHtmlToBB($text);
		}

		preg_match_all("/\[url\s*=\s*([^\]]*)\](.+?)\[\/url\]/is".BX_UTF_PCRE_MODIFIER, $text, $res);

		if (
			!empty($res)
			&& !empty($res[1])
		)
		{
			$url = (
				!Application::isUtfMode()
					? \Bitrix\Main\Text\Encoding::convertEncoding($res[1][0], 'UTF-8', \Bitrix\Main\Context::getCurrent()->getCulture()->getCharset())
					: $res[1][0]
			);

			$metaData = UrlPreview::getMetadataAndHtmlByUrl($url, true, false);
			if (
				!empty($metaData)
				&& !empty($metaData["ID"])
				&& (int)$metaData["ID"] > 0
			)
			{
				$signer = new \Bitrix\Main\Security\Sign\Signer();
				$value = $signer->sign($metaData["ID"].'', UrlPreview::SIGN_SALT);
			}
		}

		return $value;
	}

	/**
	 * Returns rendered url preview block
	 *
	 * @param array $uf
	 * @param array $params
	 * @return string|boolean
	*/
	public static function getUrlPreviewContent($uf, $params = array())
	{
		global $APPLICATION;
		$res = false;

		if ($uf["USER_TYPE"]["USER_TYPE_ID"] !== 'url_preview')
		{
			return $res;
		}

		ob_start();

		$APPLICATION->includeComponent(
			"bitrix:system.field.view",
			$uf["USER_TYPE"]["USER_TYPE_ID"],
			array(
				"LAZYLOAD" => (isset($params["LAZYLOAD"]) && $params["LAZYLOAD"] === "Y" ? "Y" : "N"),
				"MOBILE" => (isset($params["MOBILE"]) && $params["MOBILE"] === "Y" ? "Y" : "N"),
				"arUserField" => $uf,
				"arAddField" => array(
					"NAME_TEMPLATE" => ($params["NAME_TEMPLATE"] ?? false),
					"PATH_TO_USER" => ($params["PATH_TO_USER"] ?? '')
				)
			), null, array("HIDE_ICONS"=>"Y")
		);

		$res = ob_get_clean();

		return $res;
	}

	public static function getExtranetUserIdList()
	{
		static $result = false;
		global $CACHE_MANAGER;

		if ($result === false)
		{
			$result = array();

			if (!ModuleManager::isModuleInstalled('extranet'))
			{
				return $result;
			}

			$ttl = (defined("BX_COMP_MANAGED_CACHE") ? 2592000 : 600);
			$cacheId = 'sonet_ex_userid';
			$cache = new \CPHPCache;
			$cacheDir = '/bitrix/sonet/user_ex';

			if($cache->initCache($ttl, $cacheId, $cacheDir))
			{
				$tmpVal = $cache->getVars();
				$result = $tmpVal['EX_USER_ID'];
				unset($tmpVal);
			}
			else
			{
				if (defined("BX_COMP_MANAGED_CACHE"))
				{
					$CACHE_MANAGER->startTagCache($cacheDir);
				}

				$filter = array(
					'UF_DEPARTMENT_SINGLE' => false
				);

				$externalAuthIdList = self::checkPredefinedAuthIdList(array('bot', 'email', 'controller', 'sale', 'imconnector'));
				if (!empty($externalAuthIdList))
				{
					$filter['!=EXTERNAL_AUTH_ID'] = $externalAuthIdList;
				}

				$res = \Bitrix\Main\UserTable::getList(array(
					'order' => [],
					'filter' => $filter,
					'select' => array('ID')
				));

				while($user = $res->fetch())
				{
					$result[] = $user["ID"];
				}

				$adminList = [];
				$res = \Bitrix\Main\UserGroupTable::getList(array(
					'order' => [],
					'filter' => [
						'=GROUP_ID' => 1
					],
					'select' => [ 'USER_ID' ]
				));
				while($relationFields = $res->fetch())
				{
					$adminList[] = $relationFields["USER_ID"];
				}
				$result = array_diff($result, $adminList);

				if (defined("BX_COMP_MANAGED_CACHE"))
				{
					$CACHE_MANAGER->registerTag('sonet_user2group');
					$CACHE_MANAGER->registerTag('sonet_extranet_user_list');
					$CACHE_MANAGER->endTagCache();
				}

				if($cache->startDataCache())
				{
					$cache->endDataCache(array(
						'EX_USER_ID' => $result
					));
				}
			}
		}

		return $result;
	}

	public static function getEmailUserIdList()
	{
		global $CACHE_MANAGER;

		$result = array();

		if (
			!ModuleManager::isModuleInstalled('mail')
			|| !ModuleManager::isModuleInstalled('intranet')
		)
		{
			return $result;
		}

		$ttl = (defined("BX_COMP_MANAGED_CACHE") ? 2592000 : 600);
		$cacheId = 'sonet_email_userid';
		$cache = new \CPHPCache;
		$cacheDir = '/bitrix/sonet/user_email';

		if($cache->initCache($ttl, $cacheId, $cacheDir))
		{
			$tmpVal = $cache->getVars();
			$result = $tmpVal['EMAIL_USER_ID'];
			unset($tmpVal);
		}
		else
		{
			if (defined("BX_COMP_MANAGED_CACHE"))
			{
				$CACHE_MANAGER->startTagCache($cacheDir);
			}

			$res = \Bitrix\Main\UserTable::getList(array(
				'order' => array(),
				'filter' => array(
					'=EXTERNAL_AUTH_ID' => 'email'
				),
				'select' => array('ID')
			));

			while($user = $res->fetch())
			{
				$result[] = $user["ID"];
			}

			if (defined("BX_COMP_MANAGED_CACHE"))
			{
				$CACHE_MANAGER->registerTag('USER_CARD');
				$CACHE_MANAGER->endTagCache();
			}

			if($cache->startDataCache())
			{
				$cache->endDataCache(array(
					'EMAIL_USER_ID' => $result
				));
			}
		}

		return $result;
	}

	public static function getExtranetSonetGroupIdList()
	{
		$result = array();

		$ttl = (defined("BX_COMP_MANAGED_CACHE") ? 2592000 : 600);
		$cacheId = 'sonet_ex_groupid';
		$cache = new \CPHPCache;
		$cacheDir = '/bitrix/sonet/group_ex';

		if($cache->initCache($ttl, $cacheId, $cacheDir))
		{
			$tmpVal = $cache->getVars();
			$result = $tmpVal['EX_GROUP_ID'];
			unset($tmpVal);
		}
		elseif (Loader::includeModule('extranet'))
		{
			global $CACHE_MANAGER;
			if (defined("BX_COMP_MANAGED_CACHE"))
			{
				$CACHE_MANAGER->startTagCache($cacheDir);
			}

			$res = WorkgroupTable::getList(array(
				'order' => array(),
				'filter' => array(
					"=WorkgroupSite:GROUP.SITE_ID" => \CExtranet::getExtranetSiteID()
				),
				'select' => array('ID')
			));

			while($sonetGroup = $res->fetch())
			{
				$result[] = $sonetGroup["ID"];
				if (defined("BX_COMP_MANAGED_CACHE"))
				{
					$CACHE_MANAGER->registerTag('sonet_group_'.$sonetGroup["ID"]);
				}
			}

			if (defined("BX_COMP_MANAGED_CACHE"))
			{
				$CACHE_MANAGER->registerTag('sonet_group');
				$CACHE_MANAGER->endTagCache();
			}

			if($cache->startDataCache())
			{
				$cache->endDataCache(array(
					'EX_GROUP_ID' => $result
				));
			}
		}

		return $result;
	}

	public static function hasCommentSource($params): bool
	{
		$res = false;

		if (empty($params["LOG_EVENT_ID"]))
		{
			return $res;
		}

		$commentEvent = \CSocNetLogTools::findLogCommentEventByLogEventID($params["LOG_EVENT_ID"]);

		if (
			isset($commentEvent["DELETE_CALLBACK"])
			&& $commentEvent["DELETE_CALLBACK"] !== "NO_SOURCE"
		)
		{
			if (
				$commentEvent["EVENT_ID"] === "crm_activity_add_comment"
				&& isset($params["LOG_ENTITY_ID"])
				&& (int)$params["LOG_ENTITY_ID"] > 0
				&& Loader::includeModule('crm')
			)
			{
				$result = \CCrmActivity::getList(
					array(),
					array(
						'ID' => (int)$params["LOG_ENTITY_ID"],
						'CHECK_PERMISSIONS' => 'N'
					)
				);

				if ($activity = $result->fetch())
				{
					$res = ((int)$activity['TYPE_ID'] === \CCrmActivityType::Task);
				}
			}
			else
			{
				$res = true;
			}
		}

		return $res;
	}

	// only by current userid
	public static function processBlogPostShare($fields, $params)
	{
		$postId = (int)$fields["POST_ID"];
		$blogId = (int)$fields["BLOG_ID"];
		$siteId = $fields["SITE_ID"];
		$sonetRights = $fields["SONET_RIGHTS"];
		$newRights = $fields["NEW_RIGHTS"];
		$userId = (int)$fields["USER_ID"];

		$clearCommentsCache = (!isset($params['CLEAR_COMMENTS_CACHE']) || $params['CLEAR_COMMENTS_CACHE'] !== 'N');

		$commentId = false;
		$logId = false;

		if (
			Loader::includeModule('blog')
			&& \CBlogPost::update($postId, array("SOCNET_RIGHTS" => $sonetRights, "HAS_SOCNET_ALL" => "N"))
		)
		{
			BXClearCache(true, self::getBlogPostCacheDir(array(
				'TYPE' => 'post',
				'POST_ID' => $postId
			)));
			BXClearCache(true, self::getBlogPostCacheDir(array(
				'TYPE' => 'post_general',
				'POST_ID' => $postId
			)));
			BXClearCache(True, self::getBlogPostCacheDir(array(
				'TYPE' => 'posts_popular',
				'SITE_ID' => $siteId
			)));

			$logSiteListNew = array();
			$user2NotifyList = array();
			$sonetPermissionList = \CBlogPost::getSocnetPermsName($postId);
			$extranet = Loader::includeModule("extranet");
			$extranetSite = ($extranet ? \CExtranet::getExtranetSiteID() : false);
			$tzOffset = \CTimeZone::getOffset();

			$res = \CBlogPost::getList(
				array(),
				array("ID" => $postId),
				false,
				false,
				array("ID", "BLOG_ID", "PUBLISH_STATUS", "TITLE", "AUTHOR_ID", "ENABLE_COMMENTS", "NUM_COMMENTS", "VIEWS", "CODE", "MICRO", "DETAIL_TEXT", "DATE_PUBLISH", "CATEGORY_ID", "HAS_SOCNET_ALL", "HAS_TAGS", "HAS_IMAGES", "HAS_PROPS", "HAS_COMMENT_IMAGES")
			);
			$post = $res->fetch();
			if (!$post)
			{
				return false;
			}

			if (!empty($post['DETAIL_TEXT']))
			{
				$post['DETAIL_TEXT'] = \Bitrix\Main\Text\Emoji::decode($post['DETAIL_TEXT']);
			}

			$intranetUserIdList = ($extranet ? \CExtranet::getIntranetUsers() : false);
			$auxLiveParamList = array();
			$sharedToIntranetUser = false;

			foreach ($sonetPermissionList as $type => $v)
			{
				foreach ($v as $vv)
				{
					if (
						$type === "SG"
						&& in_array($type . $vv["ENTITY_ID"], $newRights, true)
					)
					{
						$renderParts = new Livefeed\RenderParts\SonetGroup();
						$renderData = $renderParts->getData($vv["ENTITY_ID"]);

						if($sonetGroup = \CSocNetGroup::getByID($vv["ENTITY_ID"]))
						{
							$res = \CSocNetGroup::getSite($vv["ENTITY_ID"]);
							while ($groupSiteList = $res->fetch())
							{
								$logSiteListNew[] = $groupSiteList["LID"];
							}

							$auxLiveParamList[] = array(
								"ENTITY_TYPE" => 'SG',
								"ENTITY_ID" => $renderData['id'],
								"NAME" => $renderData['name'],
								"LINK" => $renderData['link'],
								"VISIBILITY" => ($sonetGroup["VISIBLE"] === "Y" ? "all" : "group_members")
							);
						}
					}
					elseif ($type === "U")
					{
						if (
							in_array("US" . $vv["ENTITY_ID"], $vv["ENTITY"], true)
							&& in_array("UA", $newRights, true)
						)
						{
							$renderParts = new Livefeed\RenderParts\User();
							$renderData = $renderParts->getData(0);

							$auxLiveParamList[] = array(
								"ENTITY_TYPE" => 'UA',
								"ENTITY_ID" => 'UA',
								"NAME" => $renderData['name'],
								"LINK" => $renderData['link'],
								"VISIBILITY" => 'all'
							);
						}
						elseif (in_array($type . $vv["ENTITY_ID"], $newRights, true))
						{
							$renderParts = new Livefeed\RenderParts\User();
							$renderData = $renderParts->getData($vv["ENTITY_ID"]);

							$user2NotifyList[] = $vv["ENTITY_ID"];

							if (
								$extranet
								&& is_array($intranetUserIdList)
								&& !in_array($vv["ENTITY_ID"], $intranetUserIdList)
							)
							{
								$logSiteListNew[] = $extranetSite;
								$visibility = 'extranet';
							}
							else
							{
								$sharedToIntranetUser = true;
								$visibility = 'intranet';
							}

							$auxLiveParamList[] = array(
								"ENTITY_TYPE" => 'U',
								"ENTITY_ID" => $renderData['id'],
								"NAME" => $renderData['name'],
								"LINK" => $renderData['link'],
								"VISIBILITY" => $visibility
							);
						}
					}
					elseif (
						$type === "DR"
						&& in_array($type.$vv["ENTITY_ID"], $newRights)
					)
					{
						$renderParts = new Livefeed\RenderParts\Department();
						$renderData = $renderParts->getData($vv["ENTITY_ID"]);

						$auxLiveParamList[] = array(
							"ENTITY_TYPE" => 'DR',
							"ENTITY_ID" => $renderData['id'],
							"NAME" => $renderData['name'],
							"LINK" => $renderData['link'],
							"VISIBILITY" => 'intranet'
						);
					}
				}
			}

			$userIP = \CBlogUser::getUserIP();
			$auxText = CommentAux\Share::getPostText();
			$mention = (
				isset($params["MENTION"])
				&& $params["MENTION"] === "Y"
			);

			$commentFields = Array(
				"POST_ID" => $postId,
				"BLOG_ID" => $blogId,
				"POST_TEXT" => $auxText,
				"DATE_CREATE" => convertTimeStamp(time() + $tzOffset, "FULL"),
				"AUTHOR_IP" => $userIP[0],
				"AUTHOR_IP1" => $userIP[1],
				"PARENT_ID" => false,
				"AUTHOR_ID" => $userId,
				"SHARE_DEST" => implode(",", $newRights).($mention ? '|mention' : ''),
			);

			$userIdSent = [];

			if($commentId = \CBlogComment::add($commentFields, false))
			{
				if ($clearCommentsCache)
				{
					BXClearCache(true, self::getBlogPostCacheDir(array(
						'TYPE' => 'post_comments',
						'POST_ID' => $postId
					)));
				}

				if ((int)$post["AUTHOR_ID"] !== $userId)
				{
					$fieldsIM = array(
						"TYPE" => "SHARE",
						"TITLE" => htmlspecialcharsback($post["TITLE"]),
						"URL" => \CComponentEngine::makePathFromTemplate(
							htmlspecialcharsBack($params["PATH_TO_POST"]),
							array(
								"post_id" => $postId,
								"user_id" => $post["AUTHOR_ID"]
							)
						),
						"ID" => $postId,
						"FROM_USER_ID" => $userId,
						"TO_USER_ID" => array($post["AUTHOR_ID"]),
					);
					\CBlogPost::notifyIm($fieldsIM);
					$userIdSent[] = array_merge($userIdSent, $fieldsIM["TO_USER_ID"]);
				}

				if(!empty($user2NotifyList))
				{
					$fieldsIM = array(
						"TYPE" => "SHARE2USERS",
						"TITLE" => htmlspecialcharsback($post["TITLE"]),
						"URL" => \CComponentEngine::makePathFromTemplate(
							htmlspecialcharsBack($params["PATH_TO_POST"]),
							array(
								"post_id" => $postId,
								"user_id" => $post["AUTHOR_ID"]
							)),
						"ID" => $postId,
						"FROM_USER_ID" => $userId,
						"TO_USER_ID" => $user2NotifyList,
					);
					\CBlogPost::notifyIm($fieldsIM);
					$userIdSent[] = array_merge($userIdSent, $fieldsIM["TO_USER_ID"]);

					\CBlogPost::notifyMail(array(
						"type" => "POST_SHARE",
						"siteId" => $siteId,
						"userId" => $user2NotifyList,
						"authorId" => $userId,
						"postId" => $post["ID"],
						"postUrl" => \CComponentEngine::makePathFromTemplate(
							'/pub/post.php?post_id=#post_id#',
							array(
								"post_id"=> $post["ID"]
							)
						)
					));
				}
			}

			$blogPostLivefeedProvider = new \Bitrix\Socialnetwork\Livefeed\BlogPost;

			/* update socnet log rights*/
			$res = \CSocNetLog::getList(
				array("ID" => "DESC"),
				array(
					"EVENT_ID" => $blogPostLivefeedProvider->getEventId(),
					"SOURCE_ID" => $postId
				),
				false,
				false,
				array("ID", "ENTITY_TYPE", "ENTITY_ID", "USER_ID", "EVENT_ID")
			);
			if ($logEntry = $res->fetch())
			{
				$logId = $logEntry["ID"];
				$logSiteList = array();
				$res = \CSocNetLog::getSite($logId);
				while ($logSite = $res->fetch())
				{
					$logSiteList[] = $logSite["LID"];
				}
				$logSiteListNew = array_unique(array_merge($logSiteListNew, $logSiteList));

				if (
					$extranet
					&& $sharedToIntranetUser
					&& count($logSiteListNew) == 1
					&& $logSiteListNew[0] == $extranetSite
				)
				{
					$logSiteListNew[] = \CSite::getDefSite();
				}

				$socnetPerms = self::getBlogPostSocNetPerms(array(
					'postId' => $postId,
					'authorId' => $post["AUTHOR_ID"]
				));

				\CSocNetLogRights::deleteByLogID($logId);
				\CSocNetLogRights::add($logId, $socnetPerms, true, false);

				foreach($newRights as $GROUP_CODE)
				{
					if (preg_match('/^U(\d+)$/', $GROUP_CODE, $matches))
					{
						ComponentHelper::userLogSubscribe(array(
							'logId' => $logId,
							'userId' => $matches[1],
							'siteId' => $siteId,
							'typeList' => array(
								'FOLLOW',
								'COUNTER_COMMENT_PUSH'
							),
							'followDate' => 'CURRENT'
						));
					}
				}

				if (count(array_diff($logSiteListNew, $logSiteList)) > 0)
				{
					\CSocNetLog::update($logId, array(
						"ENTITY_TYPE" => $logEntry["ENTITY_TYPE"], // to use any real field
						"SITE_ID" => $logSiteListNew
					));
				}

				if ($commentId > 0)
				{
					$connection = \Bitrix\Main\Application::getConnection();
					$helper = $connection->getSqlHelper();

					$logCommentFields = array(
						'ENTITY_TYPE' => SONET_ENTITY_USER,
						'ENTITY_ID' => $post["AUTHOR_ID"],
						'EVENT_ID' => 'blog_comment',
						'=LOG_DATE' => $helper->getCurrentDateTimeFunction(),
						'LOG_ID' => $logId,
						'USER_ID' => $userId,
						'MESSAGE' => $auxText,
						"TEXT_MESSAGE" => $auxText,
						'MODULE_ID' => false,
						'SOURCE_ID' => $commentId,
						'RATING_TYPE_ID' => 'BLOG_COMMENT',
						'RATING_ENTITY_ID' => $commentId
					);

					\CSocNetLogComments::add($logCommentFields, false, false);
				}

				\CSocNetLogFollow::deleteByLogID($logId, "Y", true);

				/* subscribe share author */
				self::userLogSubscribe([
					'logId' => $logId,
					'userId' => $userId,
					'typeList' => [
						'FOLLOW',
						'COUNTER_COMMENT_PUSH',
					],
					'followDate' => 'CURRENT',
				]);
			}

			/* update socnet groupd activity*/
			foreach($newRights as $v)
			{
				if(mb_substr($v, 0, 2) === "SG")
				{
					$groupId = (int)mb_substr($v, 2);
					if($groupId > 0)
					{
						\CSocNetGroup::setLastActivity($groupId);
					}
				}
			}

			\Bitrix\Blog\Broadcast::send(array(
				"EMAIL_FROM" => \COption::getOptionString("main","email_from", "nobody@nobody.com"),
				"SOCNET_RIGHTS" => $newRights,
				"ENTITY_TYPE" => "POST",
				"ENTITY_ID" => $post["ID"],
				"AUTHOR_ID" => $post["AUTHOR_ID"],
				"URL" => \CComponentEngine::makePathFromTemplate(
					htmlspecialcharsBack($params["PATH_TO_POST"]),
					array(
						"post_id" => $post["ID"],
						"user_id" => $post["AUTHOR_ID"]
					)
				),
				"EXCLUDE_USERS" => $userIdSent
			));

			if (!$mention)
			{
				\Bitrix\Main\FinderDestTable::merge(array(
					"CONTEXT" => "blog_post",
					"CODE" => \Bitrix\Main\FinderDestTable::convertRights($newRights)
				));
			}

			if (\Bitrix\Main\Loader::includeModule('crm'))
			{
				\CCrmLiveFeedComponent::processCrmBlogPostRights($logId, $logEntry, $post, 'share');
			}

			if (
				(int)$commentId > 0
				&& (
					!isset($params["LIVE"])
					|| $params["LIVE"] !== "N"
				)
			)
			{
				$provider = \Bitrix\Socialnetwork\CommentAux\Base::init(\Bitrix\Socialnetwork\CommentAux\Share::getType(), array(
					'liveParamList' => $auxLiveParamList
				));

				\CBlogComment::addLiveComment($commentId, array(
					"PATH_TO_USER" => $params["PATH_TO_USER"],
					"PATH_TO_POST" => \CComponentEngine::makePathFromTemplate(
						htmlspecialcharsBack($params["PATH_TO_POST"]),
						array(
							"post_id" => $post["ID"],
							"user_id" => $post["AUTHOR_ID"]
						)
					),
					"LOG_ID" => ($logId ? (int)$logId : 0),
					"AUX" => 'share',
					"AUX_LIVE_PARAMS" => $provider->getLiveParams(),
					"CAN_USER_COMMENT" => (!empty($params["CAN_USER_COMMENT"]) && $params["CAN_USER_COMMENT"] === 'Y' ? 'Y' : 'N')
				));
			}
		}

		return $commentId;
	}

	public static function getUrlContext(): array
	{
		$result = [];

		if (
			isset($_GET["entityType"])
			&& $_GET["entityType"] <> ''
		)
		{
			$result["ENTITY_TYPE"] = $_GET["entityType"];
		}

		if (
			isset($_GET["entityId"])
			&& (int)$_GET["entityId"] > 0
		)
		{
			$result["ENTITY_ID"] = (int)$_GET["entityId"];
		}

		return $result;
	}

	public static function addContextToUrl($url, $context)
	{
		$result = $url;

		if (
			!empty($context)
			&& !empty($context["ENTITY_TYPE"])
			&& !empty($context["ENTITY_ID"])
		)
		{
			$result = $url.(mb_strpos($url, '?') === false ? '?' : '&').'entityType='.$context["ENTITY_TYPE"].'&entityId='.$context["ENTITY_ID"];
		}

		return $result;
	}

	public static function checkPredefinedAuthIdList($authIdList = array())
	{
		if (!is_array($authIdList))
		{
			$authIdList = array($authIdList);
		}

		foreach($authIdList as $key => $authId)
		{
			if (
				$authId === 'replica'
				&& !ModuleManager::isModuleInstalled("replica")
			)
			{
				unset($authIdList[$key]);
			}

			if (
				$authId === 'imconnector'
				&& !ModuleManager::isModuleInstalled("imconnector")
			)
			{
				unset($authIdList[$key]);
			}

			if (
				$authId === 'bot'
				&& !ModuleManager::isModuleInstalled("im")
			)
			{
				unset($authIdList[$key]);
			}

			if (
				$authId === 'email'
				&& !ModuleManager::isModuleInstalled("mail")
			)
			{
				unset($authIdList[$key]);
			}

			if (
				in_array($authId, [ 'sale', 'shop' ])
				&& !ModuleManager::isModuleInstalled("sale")
			)
			{
				unset($authIdList[$key]);
			}
		}

		return $authIdList;
	}

	public static function setModuleUsed(): void
	{
		$optionValue = Option::get('socialnetwork', 'is_used', false);

		if (!$optionValue)
		{
			Option::set('socialnetwork', 'is_used', true);
		}
	}

	public static function getModuleUsed(): bool
	{
		return (bool)Option::get('socialnetwork', 'is_used', false);
	}

	public static function setComponentOption($list, $params = array()): bool
	{
		if (!is_array($list))
		{
			return false;
		}

		$siteId = (!empty($params["SITE_ID"]) ? $params["SITE_ID"] : SITE_ID);
		$sefFolder = (!empty($params["SEF_FOLDER"]) ? $params["SEF_FOLDER"] : false);

		foreach ($list as $value)
		{
			if (
				empty($value["OPTION"])
				|| empty($value["OPTION"]["MODULE_ID"])
				|| empty($value["OPTION"]["NAME"])
				|| empty($value["VALUE"])
			)
			{
				continue;
			}

			$optionValue = Option::get($value["OPTION"]["MODULE_ID"], $value["OPTION"]["NAME"], false, $siteId);

			if (
				!$optionValue
				|| (
					!!($value["CHECK_SEF_FOLDER"] ?? false)
					&& $sefFolder
					&& mb_substr($optionValue, 0, mb_strlen($sefFolder)) !== $sefFolder
				)
			)
			{
				Option::set($value["OPTION"]["MODULE_ID"], $value["OPTION"]["NAME"], $value["VALUE"], $siteId);
			}
		}

		return true;
	}

	public static function getSonetGroupAvailable($params = array(), &$limitReached = false)
	{
		global $USER;

		$currentUserId = $USER->getId();
		$limit = (isset($params['limit']) && (int)$params['limit'] > 0 ? (int)$params['limit'] : 500);
		$useProjects = (!empty($params['useProjects']) && $params['useProjects'] === 'Y' ? 'Y' : 'N');
		$siteId = (!empty($params['siteId']) ? $params['siteId'] : SITE_ID);
		$landing = (!empty($params['landing']) && $params['landing'] === 'Y' ? 'Y' : '');

		$currentCache = \Bitrix\Main\Data\Cache::createInstance();

		$cacheTtl = defined("BX_COMP_MANAGED_CACHE") ? 3153600 : 3600*4;
		$cacheId = 'dest_group_'.$siteId.'_'.$currentUserId.'_'.$limit.$useProjects.$landing;
		$cacheDir = '/sonet/dest_sonet_groups/'.$siteId.'/'.$currentUserId;

		if($currentCache->startDataCache($cacheTtl, $cacheId, $cacheDir))
		{
			global $CACHE_MANAGER;

			$limitReached = false;

			$filter = [
				'features' => array("blog", array("premoderate_post", "moderate_post", "write_post", "full_post")),
				'limit' => $limit,
				'useProjects' => $useProjects,
				'site_id' => $siteId,
			];

			if ($landing === 'Y')
			{
				$filter['landing'] = 'Y';
			}

			$groupList = \CSocNetLogDestination::getSocnetGroup($filter, $limitReached);

			if(defined("BX_COMP_MANAGED_CACHE"))
			{
				$CACHE_MANAGER->startTagCache($cacheDir);
				foreach($groupList as $group)
				{
					$CACHE_MANAGER->registerTag("sonet_features_G_".$group["entityId"]);
					$CACHE_MANAGER->registerTag("sonet_group_".$group["entityId"]);
				}
				$CACHE_MANAGER->registerTag("sonet_user2group_U".$currentUserId);
				if ($landing === 'Y')
				{
					$CACHE_MANAGER->registerTag("sonet_group");
				}
				$CACHE_MANAGER->endTagCache();
			}
			$currentCache->endDataCache(array(
				'groups' => $groupList,
				'limitReached' => $limitReached
			));
		}
		else
		{
			$tmp = $currentCache->getVars();
			$groupList = $tmp['groups'];
			$limitReached = $tmp['limitReached'];
		}

		if (
			!$limitReached
			&& \CSocNetUser::isCurrentUserModuleAdmin()
		)
		{
			$limitReached = true;
		}

		return $groupList;
	}

	public static function canAddComment($logEntry = array(), $commentEvent = array())
	{
		$canAddComments = false;

		global $USER;

		if (
			!is_array($logEntry)
			&& (int)$logEntry > 0
		)
		{
			$res = \CSocNetLog::getList(
				array(),
				array(
					"ID" => (int)$logEntry
				),
				false,
				false,
				array("ID", "ENTITY_TYPE", "ENTITY_ID", "EVENT_ID", "USER_ID")
			);

			if (!($logEntry = $res->fetch()))
			{
				return $canAddComments;
			}
		}

		if (
			!is_array($logEntry)
			|| empty($logEntry)
			|| empty($logEntry["EVENT_ID"])
		)
		{
			return $canAddComments;
		}

		if (
			!is_array($commentEvent)
			|| empty($commentEvent)
		)
		{
			$commentEvent = \CSocNetLogTools::findLogCommentEventByLogEventID($logEntry["EVENT_ID"]);
		}

		if (is_array($commentEvent))
		{
			$feature = \CSocNetLogTools::findFeatureByEventID($commentEvent["EVENT_ID"]);

			if (
				array_key_exists("OPERATION_ADD", $commentEvent)
				&& $commentEvent["OPERATION_ADD"] === "log_rights"
			)
			{
				$canAddComments = \CSocNetLogRights::checkForUser($logEntry["ID"], $USER->getID());
			}
			elseif (
				$feature
				&& array_key_exists("OPERATION_ADD", $commentEvent)
				&& $commentEvent["OPERATION_ADD"] <> ''
			)
			{
				$canAddComments = \CSocNetFeaturesPerms::canPerformOperation(
					$USER->getID(),
					$logEntry["ENTITY_TYPE"],
					$logEntry["ENTITY_ID"],
					($feature === "microblog" ? "blog" : $feature),
					$commentEvent["OPERATION_ADD"],
					\CSocNetUser::isCurrentUserModuleAdmin()
				);
			}
			else
			{
				$canAddComments = true;
			}
		}

		return $canAddComments;
	}

	public static function addLiveComment($comment = [], $logEntry = [], $commentEvent = [], $params = []): array
	{
		global $USER_FIELD_MANAGER;

		$result = [];

		if (
			!is_array($comment)
			|| empty($comment)
			|| !is_array($logEntry)
			|| empty($logEntry)
			|| !is_array($commentEvent)
			|| empty($commentEvent)
		)
		{
			return $result;
		}

		$aux = !empty($params['AUX']);

		if (
			!isset($params["ACTION"])
			|| !in_array($params["ACTION"], array("ADD", "UPDATE"))
		)
		{
			$params["ACTION"] = "ADD";
		}

		if (
			!isset($params["LANGUAGE_ID"])
			|| empty($params["LANGUAGE_ID"])
		)
		{
			$params["LANGUAGE_ID"] = LANGUAGE_ID;
		}

		if (
			!isset($params["SITE_ID"])
			|| empty($params["SITE_ID"])
		)
		{
			$params["SITE_ID"] = SITE_ID;
		}

		if ($params["ACTION"] === "ADD")
		{
			if (
				!empty($commentEvent)
				&& !empty($commentEvent["METHOD_CANEDIT"])
				&& !empty($comment["SOURCE_ID"])
				&& (int)$comment["SOURCE_ID"] > 0
				&& !empty($logEntry["SOURCE_ID"])
				&& (int)$logEntry["SOURCE_ID"] > 0
			)
			{
				$canEdit = call_user_func($commentEvent["METHOD_CANEDIT"], array(
					"LOG_SOURCE_ID" => $logEntry["SOURCE_ID"],
					"COMMENT_SOURCE_ID" => $comment["SOURCE_ID"],
					"USER_ID" => $comment["USER_ID"]
				));
			}
			else
			{
				$canEdit = !$aux;
			}
		}

		$result["hasEditCallback"] = (
			$canEdit
			&& is_array($commentEvent)
			&& isset($commentEvent["UPDATE_CALLBACK"])
			&& (
				$commentEvent["UPDATE_CALLBACK"] === "NO_SOURCE"
				|| is_callable($commentEvent["UPDATE_CALLBACK"])
			)
				? "Y"
				: "N"
		);

		$result["hasDeleteCallback"] = (
			$canEdit
			&& is_array($commentEvent)
			&& isset($commentEvent["DELETE_CALLBACK"])
			&& (
				$commentEvent["DELETE_CALLBACK"] === "NO_SOURCE"
				|| is_callable($commentEvent["DELETE_CALLBACK"])
			)
				? "Y"
				: "N"
		);

		if (
			!isset($params["SOURCE_ID"])
			|| (int)$params["SOURCE_ID"] <= 0
		)
		{
			foreach (EventManager::getInstance()->findEventHandlers('socialnetwork', 'OnAfterSonetLogEntryAddComment') as $handler)  // send notification
			{
				ExecuteModuleEventEx($handler, array($comment));
			}
		}

		$result["arComment"] = $comment;
		foreach($result["arComment"] as $key => $value)
		{
			if (mb_strpos($key, "~") === 0)
			{
				unset($result["arComment"][$key]);
			}
		}

		$result["arComment"]["RATING_USER_HAS_VOTED"] = "N";

		$result["sourceID"] = $comment["SOURCE_ID"];
		$result["timestamp"] = makeTimeStamp(
			array_key_exists("LOG_DATE_FORMAT", $comment)
				? $comment["LOG_DATE_FORMAT"]
				: $comment["LOG_DATE"]
		);

		$comment["UF"] = $USER_FIELD_MANAGER->getUserFields("SONET_COMMENT", $comment["ID"], LANGUAGE_ID);

		if (
			array_key_exists("UF_SONET_COM_DOC", $comment["UF"])
			&& array_key_exists("VALUE", $comment["UF"]["UF_SONET_COM_DOC"])
			&& is_array($comment["UF"]["UF_SONET_COM_DOC"]["VALUE"])
			&& count($comment["UF"]["UF_SONET_COM_DOC"]["VALUE"]) > 0
			&& $commentEvent["EVENT_ID"] !== "tasks_comment"
		)
		{
			$logEntryRights = array();
			$res = \CSocNetLogRights::getList(array(), array("LOG_ID" => $logEntry["ID"]));
			while ($right = $res->fetch())
			{
				$logEntryRights[] = $right["GROUP_CODE"];
			}

			\CSocNetLogTools::setUFRights($comment["UF"]["UF_SONET_COM_DOC"]["VALUE"], $logEntryRights);
		}

		$result['timeFormatted'] = formatDateFromDB(
			($comment['LOG_DATE_FORMAT'] ?? $comment['LOG_DATE']),
			self::getTimeFormat($params['TIME_FORMAT'])
		);

		$authorFields = self::getAuthorData([
			'userId' => $comment['USER_ID'],
		]);
		$createdBy = self::getCreatedByData([
			'userFields' => $authorFields,
			'languageId' => $params['LANGUAGE_ID'],
			'nameTemplate' => $params['NAME_TEMPLATE'],
			'showLogin' => $params['SHOW_LOGIN'],
			'pathToUser' => $params['PATH_TO_USER'],
		]);

		$commentFormatted = array(
			'LOG_DATE' => $comment["LOG_DATE"],
			"LOG_DATE_FORMAT" => $comment["LOG_DATE_FORMAT"] ?? null,
			"LOG_DATE_DAY" => ConvertTimeStamp(MakeTimeStamp($comment['LOG_DATE']), 'SHORT'),
			'LOG_TIME_FORMAT' => $result['timeFormatted'],
			"MESSAGE" => $comment["MESSAGE"],
			"MESSAGE_FORMAT" => $comment["~MESSAGE"] ?? null,
			'CREATED_BY' => $createdBy,
			"AVATAR_SRC" => \CSocNetLogTools::formatEvent_CreateAvatar($authorFields, $params, ""),
			'USER_ID' => $comment['USER_ID'],
		);

		if (
			array_key_exists("CLASS_FORMAT", $commentEvent)
			&& array_key_exists("METHOD_FORMAT", $commentEvent)
		)
		{
			$fieldsFormatted = call_user_func(
				array($commentEvent["CLASS_FORMAT"], $commentEvent["METHOD_FORMAT"]),
				$comment,
				$params
			);
			$commentFormatted["MESSAGE_FORMAT"] = htmlspecialcharsback($fieldsFormatted["EVENT_FORMATTED"]["MESSAGE"]);
		}
		else
		{
			$commentFormatted["MESSAGE_FORMAT"] = $comment["MESSAGE"];
		}

		if (
			array_key_exists("CLASS_FORMAT", $commentEvent)
			&& array_key_exists("METHOD_FORMAT", $commentEvent)
		)
		{
			$fieldsFormatted = call_user_func(
				array($commentEvent["CLASS_FORMAT"], $commentEvent["METHOD_FORMAT"]),
				$comment,
				array_merge(
					$params,
					array(
						"MOBILE" => "Y"
					)
				)
			);
			$messageMobile = htmlspecialcharsback($fieldsFormatted["EVENT_FORMATTED"]["MESSAGE"]);
		}
		else
		{
			$messageMobile = $comment["MESSAGE"];
		}

		$result["arCommentFormatted"] = $commentFormatted;

		if (
			isset($params["PULL"])
			&& $params["PULL"] === "Y"
		)
		{
			$liveFeedCommentsParams = self::getLFCommentsParams([
				'ID' => $logEntry['ID'],
				'EVENT_ID' => $logEntry['EVENT_ID'],
				'ENTITY_TYPE' => $logEntry['ENTITY_TYPE'],
				'ENTITY_ID' => $logEntry['ENTITY_ID'],
				'SOURCE_ID' => $logEntry['SOURCE_ID'],
				'PARAMS' => $logEntry['PARAMS']
			]);

			$entityXMLId = $liveFeedCommentsParams['ENTITY_XML_ID'];

			$listCommentId = (
				!!$comment["SOURCE_ID"]
					? $comment["SOURCE_ID"]
					: $comment["ID"]
			);

			$eventHandlerID = EventManager::getInstance()->addEventHandlerCompatible("main", "system.field.view.file", array("CSocNetLogTools", "logUFfileShow"));
			$rights = \CSocNetLogComponent::getCommentRights([
				'EVENT_ID' => $logEntry['EVENT_ID'],
				'SOURCE_ID' => $logEntry['SOURCE_ID'],
				'USER_ID' => $comment['USER_ID']
			]);

			$postContentTypeId = '';
			$commentContentTypeId = '';

			$postContentId = \Bitrix\Socialnetwork\Livefeed\Provider::getContentId($logEntry);
			$canGetCommentContent = false;

			if (
				!empty($postContentId['ENTITY_TYPE'])
				&& ($postProvider = \Bitrix\Socialnetwork\Livefeed\Provider::getProvider($postContentId['ENTITY_TYPE']))
				&& ($commentProvider = $postProvider->getCommentProvider())
			)
			{
				$postContentTypeId = $postProvider->getContentTypeId();
				$commentProviderClassName = get_class($commentProvider);
				$reflectionClass = new \ReflectionClass($commentProviderClassName);

				$canGetCommentContent = ($reflectionClass->getMethod('initSourceFields')->class == $commentProviderClassName);
				if ($canGetCommentContent)
				{
					$commentContentTypeId = $commentProvider->getContentTypeId();
				}
			}

			$records = static::getLiveCommentRecords([
				'commentId' => $listCommentId,
				'ratingTypeId' => $comment['RATING_TYPE_ID'],
				'timestamp' => $result['timestamp'],
				'author' => [
					'ID' => $authorFields['ID'],
					'NAME' => $authorFields['NAME'],
					'LAST_NAME' => $authorFields['LAST_NAME'],
					'SECOND_NAME' => $authorFields['SECOND_NAME'],
					'PERSONAL_GENDER' => $authorFields['PERSONAL_GENDER'],
					'AVATAR' => $commentFormatted['AVATAR_SRC'],
				],
				'uf' => $comment['UF'],
				'ufFormatted' => $commentFormatted['UF'],
				'postMessageTextOriginal' => $comment['~MESSAGE'],
				'postMessageTextFormatted' => $commentFormatted['MESSAGE_FORMAT'],
				'mobileMessage' => $messageMobile,
				'aux' => ($params['AUX'] ?? ''),
				'auxLiveParams' => ($params['AUX_LIVE_PARAMS'] ?? []),
			]);

			$viewUrl = (string)($comment['EVENT']['URL'] ?? '');
			if (empty($viewUrl))
			{
				$pathToLogEntry = (string)($params['PATH_TO_LOG_ENTRY'] ?? '');
				if (!empty($pathToLogEntry))
				{
					$viewUrl = \CComponentEngine::makePathFromTemplate(
							$pathToLogEntry,
							[
								'log_id' => $logEntry['ID']
							]
						) . (mb_strpos($pathToLogEntry, '?') === false ? '?' : '&') . 'commentId=#ID#';
				}
			}

			$rights['COMMENT_RIGHTS_CREATETASK'] = (
				$canGetCommentContent
				&& ModuleManager::isModuleInstalled('tasks')
					? 'Y'
					: 'N'
			);

			$res = static::sendLiveComment([
				'ratingTypeId' => $comment['RATING_TYPE_ID'],
				'entityXMLId' => $entityXMLId,
				'postContentTypeId' => $postContentTypeId,
				'commentContentTypeId' => $commentContentTypeId,
				'records' => $records,
				'rights' => $rights,
				'commentId' => $listCommentId,
				'action' => ($params['ACTION'] === 'UPDATE' ? 'EDIT' : 'REPLY'),
				'urlList' => [
					'view' => $viewUrl,
					'edit' => "__logEditComment('" . $entityXMLId . "', '#ID#', '" . $logEntry["ID"] . "');",
					'delete' => '/bitrix/components/bitrix/socialnetwork.log.entry/ajax.php?lang=' . $params['LANGUAGE_ID'] . '&action=delete_comment&delete_comment_id=#ID#&post_id=' . $logEntry['ID'] . '&site=' . $params['SITE_ID'],
				],
				'avatarSize' => $params['AVATAR_SIZE_COMMENT'],
				'nameTemplate' => $params['NAME_TEMPLATE'],
				'dateTimeFormat' => $params['DATE_TIME_FORMAT'],
			]);

			if ($eventHandlerID > 0)
			{
				EventManager::getInstance()->removeEventHandler('main', 'system.field.view.file', $eventHandlerID);
			}

			$result['return_data'] = $res['JSON'];
		}

		return $result;
	}

	public static function addLiveSourceComment(array $params = []): void
	{
		global $USER_FIELD_MANAGER, $APPLICATION, $USER;

		$siteId = (string)($params['siteId'] ?? SITE_ID);
		$languageId = (string)($params['siteId'] ?? LANGUAGE_ID);
		$entityXmlId = (string)($params['entityXmlId'] ?? '');
		$nameTemplate = (string)($params['nameTemplate'] ?? '');
		$showLogin = (string)($params['showLogin'] ?? 'N');
		$pathToUser = (string)($params['pathToUser'] ?? '');
		$avatarSize = (int)($params['avatarSize'] ?? 100);

		$postProvider = $params['postProvider'];
		$commentProvider = $params['commentProvider'];

		if (
			!$postProvider
			|| !$commentProvider
		)
		{
			return;
		}

		$commentId = $commentProvider->getEntityId();
		$ratingTypeId = $commentProvider->getRatingTypeId();
		$commentDateTime = $commentProvider->getSourceDateTime();
		$commentAuthorId = $commentProvider->getSourceAuthorId();
		$commentText = $commentProvider->getSourceDescription();
		$userTypeEntityId = $commentProvider->getUserTypeEntityId();
		$commentContentTypeId = $commentProvider->getContentTypeId();

		$postContentTypeId = $postProvider->getContentTypeId();

		$timestamp = ($commentDateTime ? makeTimeStamp($commentDateTime) : 0);

		if (!empty($userTypeEntityId))
		{
			$comment['UF'] = $USER_FIELD_MANAGER->getUserFields($userTypeEntityId, $commentId, $languageId);
		}

		$timeFormatted = formatDateFromDB($commentDateTime, self::getTimeFormat());

		$authorFields = self::getAuthorData([
			'userId' => $commentAuthorId,
		]);

		$createdBy = self::getCreatedByData([
			'userFields' => $authorFields,
			'languageId' => $languageId,
			'nameTemplate' => $nameTemplate,
			'showLogin' => $showLogin,
			'pathToUser' => $pathToUser,
		]);

		$commentFormatted = [
			'LOG_DATE' => $commentDateTime->toString(),
			"LOG_DATE_FORMAT" => $comment["LOG_DATE_FORMAT"],
			'LOG_DATE_DAY' => convertTimeStamp($timestamp, 'SHORT'),
			'LOG_TIME_FORMAT' => $timeFormatted,
			'MESSAGE' => $commentText,
			'MESSAGE_FORMAT' => $commentText,
			'CREATED_BY' => $createdBy,
			'AVATAR_SRC' => \CSocNetLogTools::formatEvent_CreateAvatar($authorFields, [
				'AVATAR_SIZE' => $avatarSize,
			], ''),
			'USER_ID' => $commentAuthorId,
		];

		if (
			empty($entityXmlId)
			&& $commentProvider->getContentTypeId() === Livefeed\ForumPost::CONTENT_TYPE_ID
		)
		{
			$feedParams = $commentProvider->getFeedParams();
			if (!empty($feedParams['xml_id']))
			{
				$entityXmlId = $feedParams['xml_id'];
			}
		}

		if (empty($entityXmlId))
		{
			return;
		}

		$rights = [
			'COMMENT_RIGHTS_EDIT' => 'N',
			'COMMENT_RIGHTS_DELETE' => 'N',
			'COMMENT_RIGHTS_CREATETASK' => 'N'
		];

		$records = static::getLiveCommentRecords([
			'commentId' => $commentId,
			'ratingTypeId' => $ratingTypeId,
			'timestamp' => $timestamp,
			'author' => [
				'ID' => $authorFields['ID'],
				'NAME' => $authorFields['NAME'],
				'LAST_NAME' => $authorFields['LAST_NAME'],
				'SECOND_NAME' => $authorFields['SECOND_NAME'],
				'PERSONAL_GENDER' => $authorFields['PERSONAL_GENDER'],
				'AVATAR' => $commentFormatted['AVATAR_SRC'],
			],
			'uf' => $comment['UF'],
			'ufFormatted' => $commentFormatted['UF'],
			'postMessageTextOriginal' => $comment['~MESSAGE'],
			'postMessageTextFormatted' => $commentFormatted['MESSAGE_FORMAT'],
			'mobileMessage' => $commentText,
			'aux' => ($params['aux'] ?? ''),
			'auxLiveParams' => ($params['auxLiveParams'] ?? []),
		]);
/*
		$viewUrl = (string)($comment['EVENT']['URL'] ?? '');
		if (empty($viewUrl))
		{
			$pathToLogEntry = (string)($params['PATH_TO_LOG_ENTRY'] ?? '');
			if (!empty($pathToLogEntry))
			{
				$viewUrl = \CComponentEngine::makePathFromTemplate(
						$pathToLogEntry,
						[
							'log_id' => $logEntry['ID']
						]
					) . (mb_strpos($pathToLogEntry, '?') === false ? '?' : '&') . 'commentId=#ID#';
			}
		}
*/
		$res = static::sendLiveComment([
			'ratingTypeId' => $ratingTypeId,
			'entityXMLId' => $entityXmlId,
			'postContentTypeId' => $postContentTypeId,
			'commentContentTypeId' => $commentContentTypeId,
			'records' => $records,
			'rights' => $rights,
			'commentId' => $commentId,
			'action' => 'REPLY',
			'urlList' => [
//				'view' => $viewUrl,
//				'edit' => "__logEditComment('" . $entityXMLId . "', '#ID#', '" . $logEntry["ID"] . "');",
//				'delete' => '/bitrix/components/bitrix/socialnetwork.log.entry/ajax.php?lang=' . $params['LANGUAGE_ID'] . '&action=delete_comment&delete_comment_id=#ID#&post_id=' . $logEntry['ID'] . '&site=' . $params['SITE_ID'],
			],
			'avatarSize' => $avatarSize,
			'nameTemplate' => $nameTemplate,
//			'dateTimeFormat' => $params['DATE_TIME_FORMAT'],
		]);
	}

	private static function getAuthorData(array $params = []): array
	{
		$userId = (int)($params['userId'] ?? 0);

		if ($userId > 0)
		{
			$result = [
				'ID' => $userId
			];
			$res = Main\UserTable::getList([
				'filter' => [
					'ID' => $userId,
				],
				'select' => [ 'ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'LOGIN', 'PERSONAL_PHOTO', 'PERSONAL_GENDER' ]
			]);

			if ($userFields = $res->fetch())
			{
				$result = $userFields;
			}
		}
		else
		{
			$result = [];
		}

		return $result;
	}

	private static function getCreatedByData(array $params = []): array
	{
		$userFields = (array)($params['userFields'] ?? []);
		$languageId = ($params['languageId'] ?? null);
		$nameTemplate = (string)($params['nameTemplate'] ?? '');
		$showLogin = (string)($params['showLogin'] ?? 'N');
		$pathToUser = (string)($params['pathToUser'] ?? '');

		if (!empty($userFields))
		{
			$result = [
				'FORMATTED' => \CUser::formatName($nameTemplate, $userFields, ($showLogin !== 'N')),
				'URL' => \CComponentEngine::makePathFromTemplate(
					$pathToUser,
					[
						'user_id' => $userFields['ID'],
						'id' => $userFields['ID'],
					]
				)
			];
		}
		else
		{
			$result = [
				'FORMATTED' => Loc::getMessage('SONET_HELPER_CREATED_BY_ANONYMOUS', false, $languageId)
			];
		}

		return $result;
	}


	private static function getTimeFormat($siteTimeFormat = ''): string
	{
		if (empty($siteTimeFormat))
		{
			$siteTimeFormat = \CSite::getTimeFormat();
		}

		return  (
			mb_stripos($siteTimeFormat, 'a')
			|| (
				$siteTimeFormat === 'FULL'
				&& (
					mb_strpos(FORMAT_DATETIME, 'T') !== false
					|| mb_strpos(FORMAT_DATETIME, 'TT') !== false
				)
			)
				? (mb_strpos(FORMAT_DATETIME, 'TT') !== false ? 'H:MI TT' : 'H:MI T')
				: 'HH:MI'
		);
	}

	private static function getLiveCommentRecords(array $params = []): array
	{
		$commentId = (int)($params['commentId'] ?? 0);
		$ratingTypeId = (string)($params['ratingTypeId'] ?? '');
		$timestamp = (int)($params['timestamp'] ?? 0);
		$author = (array)($params['author'] ?? []);
		$uf = (array)($params['uf'] ?? []);
		$ufFormatted = (string)($params['ufFormatted'] ?? '');
		$postMessageTextOriginal = (string)($params['postMessageTextOriginal'] ?? '');
		$postMessageTextFormatted = (string)($params['postMessageTextFormatted'] ?? '');
		$mobileMessage = (string)($params['mobileMessage'] ?? '');
		$aux = (string)($params['aux'] ?? '');
		$auxLiveParams = (array)($params['auxLiveParams'] ?? []);

		$records = [
			$commentId => [
				'ID' => $commentId,
				'RATING_VOTE_ID' => $ratingTypeId . '_' . $commentId . '-' . (time() + random_int(0, 1000)),
				'APPROVED' => 'Y',
				'POST_TIMESTAMP' => $timestamp,
				'AUTHOR' => $author,
				'FILES' => false,
				'UF' => $uf,
				'~POST_MESSAGE_TEXT' => $postMessageTextOriginal,
				'WEB' => [
					'CLASSNAME' => '',
					'POST_MESSAGE_TEXT' => $postMessageTextFormatted,
					'AFTER' => $ufFormatted,
				],
				'MOBILE' => [
					'CLASSNAME' => '',
					'POST_MESSAGE_TEXT' => $mobileMessage
				],
				'AUX' => $aux,
				'AUX_LIVE_PARAMS' => $auxLiveParams,
			]
		];

		if (
			!empty($uf)
			&& !empty($uf['UF_SONET_COM_DOC'])
			&& !empty($uf['UF_SONET_COM_DOC']['VALUE'])

		)
		{
			$inlineDiskAttachedObjectIdImageList = self::getInlineDiskImages([
				'text' => $postMessageTextOriginal,
				'commentId' => $commentId,
			]);

			if (!empty($inlineDiskAttachedObjectIdImageList))
			{
				$records[$commentId]['WEB']['UF'] = $records[$commentId]['UF'];
				$records[$commentId]['MOBILE']['UF'] = $records[$commentId]['UF'];
				$records[$commentId]['MOBILE']['UF']['UF_SONET_COM_DOC']['VALUE'] = array_diff($records[$commentId]['MOBILE']['UF']['UF_SONET_COM_DOC']['VALUE'], $inlineDiskAttachedObjectIdImageList);
			}
		}

		return $records;
	}

	private static function sendLiveComment(array $params = []): array
	{
		global $APPLICATION;

		$ratingTypeId = (string)($params['ratingTypeId'] ?? '');
		$entityXMLId = (string)($params['entityXMLId'] ?? '');
		$postContentTypeId = (string)($params['postContentTypeId'] ?? '');
		$commentContentTypeId = (string)($params['commentContentTypeId'] ?? '');
		$records = (array)($params['records'] ?? []);
		$rights = (array)($params['rights'] ?? []);
		$commentId = (int)($params['commentId'] ?? 0);
		$action = (string)($params['action'] ?? '');
		$urlList = (array)($params['urlList'] ?? []);
		$avatarSize = (int)($params['avatarSize'] ?? 0);
		$nameTemplate = (string)($params['nameTemplate'] ?? '');
		$showLogin = (isset($params['showLogin']) && $params['showLogin'] === 'Y' ? 'Y' : 'N');
		$dateTimeFormat = (string)($params['dateTimeFormat'] ?? '');

		return $APPLICATION->includeComponent(
			'bitrix:main.post.list',
			'',
			[
				'TEMPLATE_ID' => '',
				'RATING_TYPE_ID' => $ratingTypeId,
				'ENTITY_XML_ID' => $entityXMLId,
				'POST_CONTENT_TYPE_ID' => $postContentTypeId,
				'COMMENT_CONTENT_TYPE_ID' => $commentContentTypeId,
				'RECORDS' => $records,
				'NAV_STRING' => '',
				'NAV_RESULT' => '',
				'PREORDER' => "N",
				'RIGHTS' => [
					'MODERATE' => 'N',
					'EDIT' => $rights['COMMENT_RIGHTS_EDIT'],
					'DELETE' => $rights['COMMENT_RIGHTS_DELETE'],
					'CREATETASK' => $rights['COMMENT_RIGHTS_CREATETASK'],
				],
				'VISIBLE_RECORDS_COUNT' => 1,
				'ERROR_MESSAGE' => '',
				'OK_MESSAGE' => '',
				'RESULT' => $commentId,
				'PUSH&PULL' => [
					'ACTION' => $action,
					'ID' => $commentId
				],
				'MODE' => 'PULL_MESSAGE',
				'VIEW_URL' => ($urlList['view'] ?? ''),
				'EDIT_URL' => ($urlList['edit'] ?? ''),
				'MODERATE_URL' => '',
				'DELETE_URL' => ($urlList['delete'] ?? ''),
				'AUTHOR_URL' => '',
				'AVATAR_SIZE' => $avatarSize,
				'NAME_TEMPLATE' => $nameTemplate,
				'SHOW_LOGIN' => $showLogin,
				'DATE_TIME_FORMAT' => $dateTimeFormat,
				'LAZYLOAD' => 'N',
				'NOTIFY_TAG' => '',
				'NOTIFY_TEXT' => '',
				'SHOW_MINIMIZED' => 'Y',
				'SHOW_POST_FORM' => 'Y',
				'IMAGE_SIZE' => '',
				'mfi' => '',
			],
			[],
			null
		);

	}

	private static function getInlineDiskImages(array $params = []): array
	{
		$result = [];

		$text = (string)($params['text'] ?? '');
		$commentId = (int)($params['commentId'] ?? 0);

		if (
			$text === ''
			|| $commentId <= 0
			|| !ModuleManager::isModuleInstalled('disk')
		)
		{
			return $result;
		}

		$inlineDiskObjectIdList = [];
		$inlineDiskAttachedObjectIdList = [];

		// parse inline disk object ids
		if (preg_match_all('#\\[disk file id=(n\\d+)\\]#is' . BX_UTF_PCRE_MODIFIER, $text, $matches))
		{
			$inlineDiskObjectIdList = array_map(function($a) { return (int)mb_substr($a, 1); }, $matches[1]);
		}

		// parse inline disk attached object ids
		if (preg_match_all('#\\[disk file id=(\\d+)\\]#is' . BX_UTF_PCRE_MODIFIER, $text, $matches))
		{
			$inlineDiskAttachedObjectIdList = array_map(function($a) { return (int)$a; }, $matches[1]);
		}

		if (
			(
				empty($inlineDiskObjectIdList)
				&& empty($inlineDiskAttachedObjectIdList)
			)
			|| !Loader::includeModule('disk')
		)
		{
			return $result;
		}

		$filter = [
			'=OBJECT.TYPE_FILE' => TypeFile::IMAGE
		];

		$subFilter = [];
		if (!empty($inlineDiskObjectIdList))
		{
			$subFilter['@OBJECT_ID'] = $inlineDiskObjectIdList;
		}
		elseif (!empty($inlineDiskAttachedObjectIdList))
		{
			$subFilter['@ID'] = $inlineDiskAttachedObjectIdList;
		}

		if(count($subFilter) > 1)
		{
			$subFilter['LOGIC'] = 'OR';
			$filter[] = $subFilter;
		}
		else
		{
			$filter = array_merge($filter, $subFilter);
		}

		$res = \Bitrix\Disk\Internals\AttachedObjectTable::getList([
			'filter' => $filter,
			'select' => array('ID', 'ENTITY_ID')
		]);

		while ($attachedObjectFields = $res->fetch())
		{
			if ((int)$attachedObjectFields['ENTITY_ID'] === $commentId)
			{
				$result[] = (int)$attachedObjectFields['ID'];
			}
		}

		return $result;
	}

	public static function fillSelectedUsersToInvite($HTTPPost, $componentParams, &$componentResult): void
	{
		if (
			empty($HTTPPost["SPERM"])
			|| empty($HTTPPost["SPERM"]["UE"])
			|| !is_array($HTTPPost["SPERM"]["UE"])
		)
		{
			return;
		}

		$nameFormat = \CSite::getNameFormat(false);
		foreach ($HTTPPost["SPERM"]["UE"] as $invitedEmail)
		{
			$name = (!empty($HTTPPost["INVITED_USER_NAME"][$invitedEmail]) ? $HTTPPost["INVITED_USER_NAME"][$invitedEmail] : '');
			$lastName = (!empty($HTTPPost["INVITED_USER_LAST_NAME"][$invitedEmail]) ? $HTTPPost["INVITED_USER_LAST_NAME"][$invitedEmail] : '');

			$createCrmContact = (
				!empty($HTTPPost["INVITED_USER_CREATE_CRM_CONTACT"][$invitedEmail])
				&& $HTTPPost["INVITED_USER_CREATE_CRM_CONTACT"][$invitedEmail] === 'Y'
			);

			$userName = \CUser::formatName(
				empty($componentParams["NAME_TEMPLATE"]) ? $nameFormat : $componentParams["NAME_TEMPLATE"],
				array(
					'NAME' => $name,
					'LAST_NAME' => $lastName,
					'LOGIN' => $invitedEmail
				),
				true,
				false
			);

			$componentResult["PostToShow"]["FEED_DESTINATION"]['USERS'][$invitedEmail] = [
				'id' => $invitedEmail,
				'email' => $invitedEmail,
				'showEmail' => 'Y',
				'name' => $userName,
				'isEmail' => 'Y',
				'isCrmEmail' => ($createCrmContact ? 'Y' : 'N'),
				'params' => [
					'name' => $name,
					'lastName' => $lastName,
					'createCrmContact' => $createCrmContact,
				],
			];
			$componentResult["PostToShow"]["FEED_DESTINATION"]['SELECTED'][$invitedEmail] = 'users';
		}
	}

	public static function processBlogPostNewMailUser(&$HTTPPost, &$componentResult): void
	{
		$newName = false;
		if (isset($HTTPPost['SONET_PERMS']))
		{
			$HTTPPost['SPERM'] = $HTTPPost['SONET_PERMS'];
			$newName = true;
		}

		self::processBlogPostNewCrmContact($HTTPPost, $componentResult);

		if ($newName)
		{
			$HTTPPost['SONET_PERMS'] = $HTTPPost['SPERM'];
			unset($HTTPPost['SPERM']);
		}
	}

	private static function processUserEmail($params, &$errorText): array
	{
		$result = [];

		if (
			!is_array($params)
			|| empty($params['EMAIL'])
			|| !check_email($params['EMAIL'])
			|| !Loader::includeModule('mail')
		)
		{
			return $result;
		}

		$userEmail = $params['EMAIL'];

		if (
			empty($userEmail)
			|| !check_email($userEmail)
		)
		{
			return $result;
		}

		$res = \CUser::getList(
			'ID',
			'ASC',
			[
				'=EMAIL' => $userEmail,
				'!EXTERNAL_AUTH_ID' => array_diff(\Bitrix\Main\UserTable::getExternalUserTypes(), [ 'email' ]),
			],
			[
				'FIELDS' => [ 'ID', 'EXTERNAL_AUTH_ID', 'ACTIVE' ]
			]
		);

		$userId = false;

		while (
			($emailUser = $res->fetch())
			&& !$userId
		)
		{
			if (
				(int)$emailUser["ID"] > 0
				&& (
					$emailUser["ACTIVE"] === "Y"
					|| $emailUser["EXTERNAL_AUTH_ID"] === "email"
				)
			)
			{
				if ($emailUser["ACTIVE"] === "N") // email only
				{
					$user = new \CUser;
					$user->update($emailUser["ID"], [
						'ACTIVE' => 'Y'
					]);
				}

				$userId = $emailUser['ID'];
			}
		}

		if ($userId)
		{
			$result = [
				'U'.$userId
			];
		}

		if (!$userId)
		{
			$userFields = array(
				'EMAIL' => $userEmail,
				'NAME' => ($params["NAME"] ?? ''),
				'LAST_NAME' => ($params["LAST_NAME"] ?? '')
			);

			if (
				!empty($params["CRM_ENTITY"])
				&& Loader::includeModule('crm')
			)
			{
				$userFields['UF'] = [
					'UF_USER_CRM_ENTITY' => $params["CRM_ENTITY"],
				];
				$res = \CCrmLiveFeedComponent::resolveLFEntityFromUF($params["CRM_ENTITY"]);
				if (!empty($res))
				{
					[ $k, $v ] = $res;
					if ($k && $v)
					{
						$result[] = $k.$v;

						if (
							$k === \CCrmLiveFeedEntity::Contact
							&& ($contact = \CCrmContact::getById($v))
							&& (int)$contact['PHOTO'] > 0
						)
						{
							$userFields['PERSONAL_PHOTO_ID'] = (int)$contact['PHOTO'];
						}
					}
				}
			}
			elseif (
				!empty($params["CREATE_CRM_CONTACT"])
				&& $params["CREATE_CRM_CONTACT"] === 'Y'
				&& Loader::includeModule('crm')
				&& ($contactId = \CCrmLiveFeedComponent::createContact($userFields))
			)
			{
				$userFields['UF'] = [
					'UF_USER_CRM_ENTITY' => 'C_'.$contactId
				];
				$result[] = "CRMCONTACT".$contactId;
			}

			// invite extranet user by email
			$userId = \Bitrix\Mail\User::create($userFields);

			$errorMessage = false;

			if (
				is_object($userId)
				&& $userId->LAST_ERROR <> ''
			)
			{
				$errorMessage = $userId->LAST_ERROR;
			}

			if (
				!$errorMessage
				&& (int)$userId > 0
			)
			{
				$result[] = "U".$userId;
			}
			else
			{
				$errorText = $errorMessage;
			}
		}

		if (
			!is_object($userId)
			&& (int)$userId > 0
		)
		{
			\Bitrix\Main\UI\Selector\Entities::save([
				'context' => (isset($params['CONTEXT']) && $params['CONTEXT'] <> '' ? $params['CONTEXT'] : 'BLOG_POST'),
				'code' => 'U'.$userId
			]);

			if (Loader::includeModule('intranet') && class_exists('\Bitrix\Intranet\Integration\Mail\EmailUser'))
			{
				\Bitrix\Intranet\Integration\Mail\EmailUser::invite($userId);
			}
		}

		return $result;
	}

	public static function processBlogPostNewMailUserDestinations(&$destinationList)
	{
		foreach($destinationList as $key => $code)
		{
			if 	(preg_match('/^UE(.+)$/i', $code, $matches))
			{

				$userEmail = $matches[1];
				$errorText = '';

				$destRes = self::processUserEmail(array(
					'EMAIL' => $userEmail,
					'CONTEXT' => 'BLOG_POST'
				), $errorText);

				if (
					!empty($destRes)
					&& is_array($destRes)
				)
				{
					unset($destinationList[$key]);
					$destinationList = array_merge($destinationList, $destRes);
				}
			}
		}
	}

	public static function processBlogPostNewCrmContact(&$HTTPPost, &$componentResult)
	{
		$USent = (
			isset($HTTPPost["SPERM"]["U"])
			&& is_array($HTTPPost["SPERM"]["U"])
			&& !empty($HTTPPost["SPERM"]["U"])
		);

		$UESent = (
			$componentResult["ALLOW_EMAIL_INVITATION"]
			&& isset($HTTPPost["SPERM"]["UE"])
			&& is_array($HTTPPost["SPERM"]["UE"])
			&& !empty($HTTPPost["SPERM"]["UE"])
		);

		if (
			($USent || $UESent)
			&& Loader::includeModule('mail')
		)
		{
			if (
				$USent
				&& Loader::includeModule('crm')
			) // process mail users/contacts
			{
				$userIdList = array();
				foreach ($HTTPPost["SPERM"]["U"] as $code)
				{
					if (preg_match('/^U(\d+)$/i', $code, $matches))
					{
						$userIdList[] = (int)$matches[1];
					}
				}

				if (!empty($userIdList))
				{
					$res = Main\UserTable::getList(array(
						'filter' => array(
							'ID' => $userIdList,
							'!=UF_USER_CRM_ENTITY' => false
						),
						'select' => array('ID', 'UF_USER_CRM_ENTITY')
					));
					while ($user = $res->fetch())
					{
						$livefeedCrmEntity = \CCrmLiveFeedComponent::resolveLFEntityFromUF($user['UF_USER_CRM_ENTITY']);

						if (!empty($livefeedCrmEntity))
						{
							list($k, $v) = $livefeedCrmEntity;
							if ($k && $v)
							{
								if (!isset($HTTPPost["SPERM"][$k]))
								{
									$HTTPPost["SPERM"][$k] = array();
								}
								$HTTPPost["SPERM"][$k][] = $k.$v;
							}
						}
					}
				}
			}

			if ($UESent) // process emails
			{
				foreach ($HTTPPost["SPERM"]["UE"] as $key => $userEmail)
				{
					if (!check_email($userEmail))
					{
						continue;
					}

					$errorText = '';

					$destRes = self::processUserEmail([
						'EMAIL' => $userEmail,
						'NAME' => (
							isset($HTTPPost["INVITED_USER_NAME"])
							&& isset($HTTPPost["INVITED_USER_NAME"][$userEmail])
								? $HTTPPost["INVITED_USER_NAME"][$userEmail]
								: ''
						),
						'LAST_NAME' => (
							isset($HTTPPost["INVITED_USER_LAST_NAME"])
							&& isset($HTTPPost["INVITED_USER_LAST_NAME"][$userEmail])
								? $HTTPPost["INVITED_USER_LAST_NAME"][$userEmail]
								: ''
						),
						'CRM_ENTITY' => (
							isset($HTTPPost["INVITED_USER_CRM_ENTITY"])
							&& isset($HTTPPost["INVITED_USER_CRM_ENTITY"][$userEmail])
								? $HTTPPost["INVITED_USER_CRM_ENTITY"][$userEmail]
								: ''
						),
						"CREATE_CRM_CONTACT" => (
							isset($HTTPPost["INVITED_USER_CREATE_CRM_CONTACT"])
							&& isset($HTTPPost["INVITED_USER_CREATE_CRM_CONTACT"][$userEmail])
							? $HTTPPost["INVITED_USER_CREATE_CRM_CONTACT"][$userEmail]
							: 'N'
						),
						'CONTEXT' => 'BLOG_POST'
					], $errorText);

					foreach($destRes as $code)
					{
						if (preg_match('/^U(\d+)$/i', $code, $matches))
						{
							$HTTPPost["SPERM"]["U"][] = $code;
						}
						elseif (
							Loader::includeModule('crm')
							&& (preg_match('/^CRM(CONTACT|COMPANY|LEAD|DEAL)(\d+)$/i', $code, $matches))
						)
						{
							if (!isset($HTTPPost["SPERM"]["CRM".$matches[1]]))
							{
								$HTTPPost["SPERM"]["CRM".$matches[1]] = array();
							}
							$HTTPPost["SPERM"]["CRM".$matches[1]][] = $code;
						}
					}

					if (!empty($errorText))
					{
						$componentResult["ERROR_MESSAGE"] .= $errorText;
					}
				}
//				unset($HTTPPost["SPERM"]["UE"]);
			}
		}
	}

	public static function getUserSonetGroupIdList($userId = false, $siteId = false)
	{
		$result = array();

		if ((int)$userId <= 0)
		{
			global $USER;
			$userId = (int)$USER->getId();
		}

		if (!$siteId)
		{
			$siteId = SITE_ID;
		}

		$currentCache = \Bitrix\Main\Data\Cache::createInstance();

		$cacheTtl = defined("BX_COMP_MANAGED_CACHE") ? 3153600 : 3600*4;
		$cacheId = 'user_group_member'.$siteId.'_'.$userId;
		$cacheDir = '/sonet/user_group_member/'.$siteId.'/'.$userId;

		if($currentCache->startDataCache($cacheTtl, $cacheId, $cacheDir))
		{
			global $CACHE_MANAGER;

			$res = UserToGroupTable::getList(array(
				'filter' => array(
					'<=ROLE' => UserToGroupTable::ROLE_USER,
					'=USER_ID' => $userId,
					'=GROUP.ACTIVE' => 'Y',
					'=GROUP.WorkgroupSite:GROUP.SITE_ID' => $siteId
				),
				'select' => array('GROUP_ID')
			));

			while ($record = $res->fetch())
			{
				$result[] = $record["GROUP_ID"];
			}

			if(defined("BX_COMP_MANAGED_CACHE"))
			{
				$CACHE_MANAGER->startTagCache($cacheDir);
				$CACHE_MANAGER->registerTag("sonet_user2group_U".$userId);
				$CACHE_MANAGER->endTagCache();
			}
			$currentCache->endDataCache($result);
		}
		else
		{
			$result = $currentCache->getVars();
		}

		return $result;
	}

	public static function getAllowToAllDestination($userId = 0)
	{
		global $USER;

		$userId = (int)$userId;
		if ($userId <= 0)
		{
			$userId = (int)$USER->getId();
		}

		$allowToAll = (Option::get("socialnetwork", "allow_livefeed_toall", "Y") === "Y");

		if ($allowToAll)
		{
			$toAllRightsList = unserialize(Option::get("socialnetwork", "livefeed_toall_rights", 'a:1:{i:0;s:2:"AU";}'), [ 'allowed_classes' => false ]);
			if (!$toAllRightsList)
			{
				$toAllRightsList = array("AU");
			}

			$userGroupCodeList = array_merge(array("AU"), \CAccess::getUserCodesArray($userId));
			if (count(array_intersect($toAllRightsList, $userGroupCodeList)) <= 0)
			{
				$allowToAll = false;
			}
		}

		return $allowToAll;
	}

	public static function getLivefeedStepper()
	{
		$res = array();
		if (ModuleManager::isModuleInstalled('blog'))
		{
			$res["blog"] = array('Bitrix\Blog\Update\LivefeedIndexPost', 'Bitrix\Blog\Update\LivefeedIndexComment');
		}
		if (ModuleManager::isModuleInstalled('tasks'))
		{
			$res["tasks"] = array('Bitrix\Tasks\Update\LivefeedIndexTask');
		}
		if (ModuleManager::isModuleInstalled('calendar'))
		{
			$res["calendar"] = array('Bitrix\Calendar\Update\LivefeedIndexCalendar');
		}
		if (ModuleManager::isModuleInstalled('forum'))
		{
			$res["forum"] = array('Bitrix\Forum\Update\LivefeedIndexMessage', 'Bitrix\Forum\Update\LivefeedIndexComment');
		}
		if (ModuleManager::isModuleInstalled('xdimport'))
		{
			$res["xdimport"] = array('Bitrix\XDImport\Update\LivefeedIndexLog', 'Bitrix\XDImport\Update\LivefeedIndexComment');
		}
		if (ModuleManager::isModuleInstalled('wiki'))
		{
			$res["wiki"] = array('Bitrix\Wiki\Update\LivefeedIndexLog', 'Bitrix\Wiki\Update\LivefeedIndexComment');
		}
		if (!empty($res))
		{
			echo Stepper::getHtml($res, Loc::getMessage(ModuleManager::isModuleInstalled('intranet') ? 'SONET_HELPER_STEPPER_LIVEFEED2': 'SONET_HELPER_STEPPER_LIVEFEED'));
		}
	}

	public static function checkProfileRedirect($userId = 0)
	{
		$userId = (int)$userId;
		if ($userId <= 0)
		{
			return;
		}

		$select = array('ID', 'EXTERNAL_AUTH_ID');
		if (ModuleManager::isModuleInstalled('crm'))
		{
			$select[] = 'UF_USER_CRM_ENTITY';
		}
		$res = Main\UserTable::getList(array(
			'filter' => array(
				'=ID' => $userId
			),
			'select' => $select
		));

		if ($userFields = $res->fetch())
		{
			$event = new Main\Event(
				'socialnetwork',
				'onUserProfileRedirectGetUrl',
				array(
					'userFields' => $userFields
				)
			);
			$event->send();

			foreach ($event->getResults() as $eventResult)
			{
				if ($eventResult->getType() === \Bitrix\Main\EventResult::SUCCESS)
				{
					$eventParams = $eventResult->getParameters();

					if (
						is_array($eventParams)
						&& isset($eventParams['url'])
					)
					{
						LocalRedirect($eventParams['url']);
					}
					break;
				}
			}
		}
	}

	// used when video transform
	public static function getBlogPostLimitedViewStatus($params = array())
	{
		$result = false;

		$logId = (
			is_array($params)
			&& !empty($params['logId'])
			&& (int)$params['logId'] > 0
				? (int)$params['logId']
				: 0
		);

		if ($logId <= 0)
		{
			return $result;
		}

		if ($logItem = Log::getById($logId))
		{
			$logItemFields = $logItem->getFields();
			if (
				isset($logItemFields['TRANSFORM'])
				&& $logItemFields['TRANSFORM'] === "Y"
			)
			{
				$result = true;
			}
		}

		return $result;
	}

	public static function setBlogPostLimitedViewStatus($params = array())
	{
		static $extranetSiteId = null;

		$result = false;

		$show = (
			is_array($params)
			&& isset($params['show'])
			&& $params['show'] === true
		);

		$postId = (
			is_array($params)
			&& !empty($params['postId'])
			&& (int)$params['postId'] > 0
				? (int)$params['postId']
				: 0
		);

		if (
			$postId <= 0
			|| !Loader::includeModule('blog')
		)
		{
			return $result;
		}

		if ($show)
		{
			$liveFeedEntity = Livefeed\Provider::init(array(
				'ENTITY_TYPE' => 'BLOG_POST',
				'ENTITY_ID' => $postId,
			));

			$logId = $liveFeedEntity->getLogId();
			if (!self::getBlogPostLimitedViewStatus(array(
				'logId' => $logId
			)))
			{
				return $result;
			}

			$post = Post::getById($postId);
			$postFields = $post->getFields();

			$socnetPerms = self::getBlogPostSocNetPerms(array(
				'postId' => $postId,
				'authorId' => $postFields["AUTHOR_ID"]
			));

			\CSocNetLogRights::deleteByLogID($logId);
			\CSocNetLogRights::add($logId, $socnetPerms, true, false);
			LogTable::update($logId, array(
				'LOG_UPDATE' => new SqlExpression(Application::getConnection()->getSqlHelper()->getCurrentDateTimeFunction()),
				'TRANSFORM' => 'N'
			));

			if (\Bitrix\Main\Loader::includeModule('crm'))
			{
				$logItem = Log::getById($logId);
				\CCrmLiveFeedComponent::processCrmBlogPostRights($logId, $logItem->getFields(), $postFields, 'new');
			}

			\Bitrix\Blog\Integration\Socialnetwork\CounterPost::increment(array(
				'socnetPerms' => $socnetPerms,
				'logId' => $logId,
				'logEventId' => $liveFeedEntity->getEventId()
			));

			$logSiteIdList = array();
			$resSite = \CSocNetLog::getSite($logId);
			while($logSite = $resSite->fetch())
			{
				$logSiteIdList[] = $logSite["LID"];
			}

			if (
				$extranetSiteId === null
				&& Loader::includeModule('extranet')
			)
			{
				$extranetSiteId = \CExtranet::getExtranetSiteID();
			}

			$siteId = false;
			foreach($logSiteIdList as $logSiteId)
			{
				if ($logSiteId != $extranetSiteId)
				{
					$siteId = $logSiteId;
					break;
				}
			}

			if (!$siteId)
			{
				$siteId = \CSite::getDefSite();
			}

			$postUrl = \CComponentEngine::makePathFromTemplate(
				\Bitrix\Socialnetwork\Helper\Path::get('userblogpost_page', $siteId),
				array(
					"post_id" => $postId,
					"user_id" => $postFields["AUTHOR_ID"]
				)
			);

			$notificationParamsList = array(
				'post' => array(
					'ID' => $postFields["ID"],
					'TITLE' => $postFields["TITLE"],
					'AUTHOR_ID' => $postFields["AUTHOR_ID"]
				),
				'siteId' => $siteId,
				'postUrl' => $postUrl,
				'socnetRights' => $socnetPerms,
			);

			$notificationParamsList['mentionList'] = Mention::getUserIds($postFields['DETAIL_TEXT']);

			self::notifyBlogPostCreated($notificationParamsList);

			if (
				!isset($params['notifyAuthor'])
				|| $params['notifyAuthor']
			)
			{
				self::notifyAuthorOnSetBlogPostLimitedViewStatusShow(array(
					'POST_ID' => $postId,
					'POST_FIELDS' => $postFields,
					'POST_URL' => $postUrl,
					'LOG_ID' => $logId,
					'SITE_ID' => $siteId
				));
			}

			BXClearCache(true, self::getBlogPostCacheDir(array(
				'TYPE' => 'post',
				'POST_ID' => $postId
			)));
		}

		$result = true;

		return $result;
	}


	private static function notifyAuthorOnSetBlogPostLimitedViewStatusShow($params = array())
	{
		$postId = $params['POST_ID'];
		$postFields = $params['POST_FIELDS'];
		$postUrl = $params['POST_URL'];
		$logId = $params['LOG_ID'];
		$siteId = $params['SITE_ID'];


		if (Loader::includeModule('im'))
		{
			$authorPostUrl = $postUrl;
			if (ModuleManager::isModuleInstalled("extranet"))
			{
				$tmp = \CSocNetLogTools::processPath(
					array(
						"URL" => $authorPostUrl,
					),
					$postFields["AUTHOR_ID"],
					$siteId
				);
				$authorPostUrl = $tmp["URLS"]["URL"];

				$serverName = (
				mb_strpos($authorPostUrl, "http://") === 0
					|| mb_strpos($authorPostUrl, "https://") === 0
						? ""
						: $tmp["SERVER_NAME"]
					);
			}

			$messageFields = array(
				"MESSAGE_TYPE" => IM_MESSAGE_SYSTEM,
				"TO_USER_ID" => $postFields["AUTHOR_ID"],
				"FROM_USER_ID" => $postFields["AUTHOR_ID"],
				"NOTIFY_TYPE" => IM_NOTIFY_SYSTEM,
				"NOTIFY_ANSWER" => "N",
				"NOTIFY_MODULE" => "socialnetwork",
				"NOTIFY_EVENT" => "transform",
				"NOTIFY_TAG" => "SONET|BLOG_POST_CONVERT|".$postId,
				"PARSE_LINK" => "N",
				"LOG_ID" => $logId,
				"NOTIFY_MESSAGE" => Loc::getMessage('SONET_HELPER_VIDEO_CONVERSION_COMPLETED', array(
					'#POST_TITLE#' => '<a href="'.$authorPostUrl.'" class="bx-notifier-item-action">'.htmlspecialcharsbx($postFields["TITLE"]).'</a>'
				)),
				"NOTIFY_MESSAGE_OUT" => Loc::getMessage('SONET_HELPER_VIDEO_CONVERSION_COMPLETED', array(
						'#POST_TITLE#' => htmlspecialcharsbx($postFields["TITLE"]),
					))." ".$serverName.$authorPostUrl,
			);

			$messageFields['PUSH_MESSAGE'] = $messageFields['NOTIFY_MESSAGE'];
			$messageFields['PUSH_PARAMS'] = array(
				'ACTION' => 'transform',
				'TAG' => $messageFields['NOTIFY_TAG']
			);

			\CIMNotify::add($messageFields);
		}
	}

	public static function getBlogPostSocNetPerms($params = array())
	{
		$result = array();

		$postId = (
			is_array($params)
			&& !empty($params['postId'])
			&& (int)$params['postId'] > 0
				? (int)$params['postId']
				: 0
		);

		$authorId = (
			is_array($params)
			&& !empty($params['authorId'])
			&& (int)$params['authorId'] > 0
				? (int)$params['authorId']
				: 0
		);

		if ($postId <= 0)
		{
			return $result;
		}

		if ($authorId <= 0)
		{
			$blogPostFields = \CBlogPost::getByID($postId);
			$authorId = (int)$blogPostFields["AUTHOR_ID"];
		}

		if ($authorId <= 0)
		{
			return $result;
		}

		$result = \CBlogPost::getSocNetPermsCode($postId);

		$profileBlogPost = false;
		foreach($result as $perm)
		{
			if (preg_match('/^UP(\d+)$/', $perm, $matches))
			{
				$profileBlogPost = true;
				break;
			}
		}
		if (!$profileBlogPost)
		{
			if (!in_array("U".$authorId, $result, true))
			{
				$result[] = "U".$authorId;
			}
			$result[] = "SA"; // socnet admin

			if (
				in_array("AU", $result, true)
				|| in_array("G2", $result, true)
			)
			{
				$socnetPermsAdd = array();

				foreach ($result as $perm)
				{
					if (preg_match('/^SG(\d+)$/', $perm, $matches))
					{
						if (
							!in_array("SG".$matches[1]."_".UserToGroupTable::ROLE_USER, $result, true)
							&& !in_array("SG".$matches[1]."_".UserToGroupTable::ROLE_MODERATOR, $result, true)
							&& !in_array("SG".$matches[1]."_".UserToGroupTable::ROLE_OWNER, $result, true)
						)
						{
							$socnetPermsAdd[] = "SG".$matches[1]."_".$result;
						}
					}
				}
				if (count($socnetPermsAdd) > 0)
				{
					$result = array_merge($result, $socnetPermsAdd);
				}
			}
		}

		return $result;
	}

	public static function notifyBlogPostCreated($params = array())
	{
		if (!Loader::includeModule('blog'))
		{
			return false;
		}

		$post = (
			!empty($params)
			&& is_array($params)
			&& !empty($params['post'])
			&& is_array($params['post'])
				? $params['post']
				: []
		);

		$siteId = (
			!empty($params)
			&& is_array($params)
			&& !empty($params['siteId'])
				? $params['siteId']
				: \CSite::getDefSite()
		);

		$postUrl = (
			!empty($params)
			&& is_array($params)
			&& !empty($params['postUrl'])
				? $params['postUrl']
				: ''
		);

		$socnetRights = (
			!empty($params)
			&& is_array($params)
			&& !empty($params['socnetRights'])
			&& is_array($params['socnetRights'])
				? $params['socnetRights']
				: []
		);

		$socnetRightsOld = (
			!empty($params)
			&& is_array($params)
			&& !empty($params['socnetRightsOld'])
			&& is_array($params['socnetRightsOld'])
				? $params['socnetRightsOld']
				: array(
					'U' => [],
					'SG' => []
				)
		);

		$mentionListOld = (
			!empty($params)
			&& is_array($params)
			&& !empty($params['mentionListOld'])
			&& is_array($params['mentionListOld'])
				? $params['mentionListOld']
				: []
		);

		$mentionList = (
			!empty($params)
			&& is_array($params)
			&& !empty($params['mentionList'])
			&& is_array($params['mentionList'])
				? $params['mentionList']
				: []
		);

		$gratData = (
			!empty($params)
			&& is_array($params)
			&& !empty($params['gratData'])
			&& is_array($params['gratData'])
				? $params['gratData']
				: []
		);

		$IMNotificationFields = array(
			"TYPE" => "POST",
			"TITLE" => $post["TITLE"],
			"URL" => $postUrl,
			"ID" => $post["ID"],
			"FROM_USER_ID" => $post["AUTHOR_ID"],
			"TO_USER_ID" => array(),
			"TO_SOCNET_RIGHTS" => $socnetRights,
			"TO_SOCNET_RIGHTS_OLD" => $socnetRightsOld,
			"GRAT_DATA" => $gratData
		);
		if (!empty($mentionListOld))
		{
			$IMNotificationFields["MENTION_ID_OLD"] = $mentionListOld;
		}
		if (!empty($mentionList))
		{
			$IMNotificationFields["MENTION_ID"] = $mentionList;
		}

		$userIdSentList = \CBlogPost::notifyIm($IMNotificationFields);
		if (!$userIdSentList)
		{
			$userIdSentList = [];
		}

		$userIdToMailList = [];

		if (!empty($socnetRights))
		{
			\Bitrix\Blog\Broadcast::send(array(
				"EMAIL_FROM" => Option::get('main', 'email_from', 'nobody@nobody.com'),
				"SOCNET_RIGHTS" => $socnetRights,
				"SOCNET_RIGHTS_OLD" => $socnetRightsOld,
				"ENTITY_TYPE" => "POST",
				"ENTITY_ID" => $post["ID"],
				"AUTHOR_ID" => $post["AUTHOR_ID"],
				"URL" => $postUrl,
				'EXCLUDE_USERS' => array_merge([ $post['AUTHOR_ID'] ], $userIdSentList),
			));

			foreach ($socnetRights as $right)
			{
				if (mb_strpos($right, "U") === 0)
				{
					$rightUserId = (int)mb_substr($right, 1);
					if (
						$rightUserId > 0
						&& empty($socnetRightsOld["U"][$rightUserId])
						&& $rightUserId !== (int)$post["AUTHOR_ID"]
						&& !in_array($rightUserId, $userIdToMailList, true)
					)
					{
						$userIdToMailList[] = $rightUserId;
					}
				}
			}
		}

		if (!empty($userIdToMailList))
		{
			\CBlogPost::notifyMail([
				"type" => "POST",
				"siteId" => $siteId,
				"userId" => $userIdToMailList,
				"authorId" => $post["AUTHOR_ID"],
				"postId" => $post["ID"],
				"postUrl" => \CComponentEngine::makePathFromTemplate(
					'/pub/post.php?post_id=#post_id#',
					[
						"post_id" => $post["ID"],
					]
				),
			]);
		}

		return true;
	}

	public static function getUserSEFUrl($params = array())
	{
		$siteId = (
			is_array($params)
			&& isset($params['siteId'])
				? $params['siteId']
				: false
		);

		$siteDir = SITE_DIR;
		if ($siteId)
		{
			$res = \CSite::getById($siteId);
			if ($site = $res->fetch())
			{
				$siteDir = $site['DIR'];
			}
		}

		return Option::get('socialnetwork', 'user_page', $siteDir.'company/personal/', $siteId);
	}

	public static function getWorkgroupSEFUrl($params = []): string
	{
		$siteId = (
			is_array($params)
			&& isset($params['siteId'])
				? $params['siteId']
				: false
		);

		$siteDir = SITE_DIR;
		if ($siteId)
		{
			$res = \CSite::getById($siteId);
			if ($site = $res->fetch())
			{
				$siteDir = $site['DIR'];
			}
		}

		return Option::get('socialnetwork', 'workgroups_page', $siteDir.'workgroups/', $siteId);
	}

	public static function convertBlogPostPermToDestinationList($params, &$resultFields)
	{
		global $USER;

		$result = array();

		if (!Loader::includeModule('blog'))
		{
			return $result;
		}

		$postId = (
			isset($params['POST_ID'])
			&& (int)$params['POST_ID'] > 0
				? (int)$params['POST_ID']
				: false
		);

		$postFields = array();

		if ($postId)
		{
			$postFields = \Bitrix\Blog\Item\Post::getById($postId)->getFields();
		}

		$authorId = (
			!$postId
			&& isset($params['AUTHOR_ID'])
			&& (int)$params['AUTHOR_ID'] > 0
				? (int)$params['AUTHOR_ID']
				: $postFields['AUTHOR_ID']
		);

		$extranetUser = (
			$params['IS_EXTRANET_USER'] ?? self::isCurrentUserExtranet([
				'siteId' => SITE_ID,
				'userId' => $USER->getId(),
			])
		);

		$siteId = (
			!empty($params['SITE_ID'])
				? $params['SITE_ID']
				: SITE_ID
		);

		$socNetPermsListOld = array();

		if ($postId > 0)
		{
			$socNetPermsListOld = \CBlogPost::getSocNetPerms($postId);
		}

		$authorInDest = (
			!empty($postFields)
			&& !empty($postFields['AUTHOR_ID'])
			&& !empty($socNetPermsListOld)
			&& !empty($socNetPermsListOld['U'])
			&& isset($socNetPermsListOld['U'][$postFields['AUTHOR_ID']])
			&& in_array('U' . $postFields['AUTHOR_ID'], $socNetPermsListOld['U'][$postFields['AUTHOR_ID']], true)
		);

		$permList = (
			isset($params['PERM'])
			&& is_array($params['PERM'])
				? $params['PERM']
				: array()
		);

		$allowToAll = self::getAllowToAllDestination();

		if(
			empty($permList)
			&& isset($params["IS_REST"])
			&& $params["IS_REST"]
			&& $allowToAll
		)
		{
			$permList = array("UA" => array("UA"));
		}

		foreach ($permList as $v => $k)
		{
			if (
				$v <> ''
				&& is_array($k)
				&& !empty($k)
			)
			{
				foreach ($k as $vv)
				{
					if (
						$vv <> ''
						&& (
							empty($postFields['AUTHOR_ID'])
							|| $vv !== 'U'.$postFields['AUTHOR_ID']
							|| $authorInDest
						)
					)
					{
						$result[] = $vv;
					}
				}
			}
		}

		$result = self::checkBlogPostDestinationList(array(
			'DEST' => $result,
			'SITE_ID' => $siteId,
			'AUTHOR_ID' => $authorId,
			'IS_EXTRANET_USER' => $extranetUser,
			'POST_ID' => $postId
		), $resultFields);

		return $result;
	}

	public static function checkBlogPostDestinationList($params, &$resultFields)
	{
		global $USER;

		$destinationList = (
			isset($params["DEST"])
			&& is_array($params["DEST"])
				? $params["DEST"]
				: array()
		);

		$siteId = (
			!empty($params['SITE_ID'])
				? $params['SITE_ID']
				: SITE_ID
		);

		$currentUserId = $USER->getId();

		if (!$currentUserId)
		{
			return false;
		}

		$extranetUser = (
			$params['IS_EXTRANET_USER'] ?? self::isCurrentUserExtranet([
				'siteId' => SITE_ID,
				'userId' => $USER->getId()
			])
		);

		$postId = (
			isset($params['POST_ID'])
			&& (int)$params['POST_ID'] > 0
				? (int)$params['POST_ID']
				: false
		);

		$postFields = [];
		$oldSonetGroupIdList = [];

		if ($postId)
		{
			$socNetPermsListOld = \CBlogPost::getSocNetPerms($postId);
			$postFields = \Bitrix\Blog\Item\Post::getById($postId)->getFields();
			if (!empty($socNetPermsListOld['SG']))
			{
				$oldSonetGroupIdList = array_keys($socNetPermsListOld['SG']);
			}
		}

		$userAdmin = \CSocNetUser::isUserModuleAdmin($currentUserId, $siteId);
		$allowToAll = self::getAllowToAllDestination();

		$newSonetGroupIdList = [];
		$newUserIdList = [];

		foreach($destinationList as $code)
		{
			if (preg_match('/^SG(\d+)/i', $code, $matches))
			{
				$newSonetGroupIdList[] = (int)$matches[1];
			}
			elseif (preg_match('/^U(\d+)/i', $code, $matches))
			{
				$newUserIdList[] = (int)$matches[1];
			}
		}

		if (!empty($newSonetGroupIdList))
		{
			$oneSG = false;
			$firstSG = true;

			$premoderateSGList = [];
			$canPublish = true;

			foreach ($newSonetGroupIdList as $groupId)
			{
				if (
					!empty($postFields)
					&& $postFields["PUBLISH_STATUS"] === BLOG_PUBLISH_STATUS_PUBLISH
					&& in_array($groupId, $oldSonetGroupIdList)
				)
				{
					continue;
				}

				$canPublishToGroup = (
					$userAdmin
					|| \CSocNetFeaturesPerms::canPerformOperation($currentUserId, SONET_ENTITY_GROUP, $groupId, 'blog', 'write_post')
					|| \CSocNetFeaturesPerms::canPerformOperation($currentUserId, SONET_ENTITY_GROUP, $groupId, 'blog', 'full_post')
					|| \CSocNetFeaturesPerms::canPerformOperation($currentUserId, SONET_ENTITY_GROUP, $groupId, 'blog', 'moderate_post')
				);

				$canPremoderateToGroup = \CSocNetFeaturesPerms::canPerformOperation($currentUserId, SONET_ENTITY_GROUP, $groupId, 'blog', 'premoderate_post');

				if (
					!$canPublishToGroup
					&& $canPremoderateToGroup
				)
				{
					$premoderateSGList[] = $groupId;
				}

				$canPublish = (
					$canPublish
					&& $canPublishToGroup
				);

				if($firstSG)
				{
					$oneSG = true;
					$firstSG = false;
				}
				else
				{
					$oneSG = false;
				}
			}

			if (!$canPublish)
			{
				if (!empty($premoderateSGList))
				{
					if ($oneSG)
					{
						if ($resultFields['PUBLISH_STATUS'] === BLOG_PUBLISH_STATUS_PUBLISH)
						{
							if (!$postId) // new post
							{
								$resultFields['PUBLISH_STATUS'] = BLOG_PUBLISH_STATUS_READY;
							}
							elseif ($postFields['PUBLISH_STATUS'] !== BLOG_PUBLISH_STATUS_PUBLISH)
							{
								$resultFields['PUBLISH_STATUS'] = $postFields['PUBLISH_STATUS'];
							}
							else
							{
								$resultFields['ERROR_MESSAGE'] = Loc::getMessage('SBPE_EXISTING_POST_PREMODERATION');
								$resultFields['ERROR_MESSAGE_PUBLIC'] = $resultFields['ERROR_MESSAGE'];
							}
						}
					}
					else
					{
						$groupNameList = [];
						$groupUrl = Option::get('socialnetwork', 'workgroups_page', SITE_DIR.'workgroups/', SITE_ID).'group/#group_id#/';

						$res = WorkgroupTable::getList([
							'filter' => [
								'@ID' => $premoderateSGList
							],
							'select' => [ 'ID', 'NAME' ]
						]);
						while ($groupFields = $res->fetch())
						{
							$groupNameList[] = (
								isset($params['MOBILE']) && $params['MOBILE'] === 'Y'
									? $groupFields['NAME']
									: '<a href="' . \CComponentEngine::makePathFromTemplate($groupUrl, [ 'group_id' => $groupFields['ID'] ]) . '">' . htmlspecialcharsEx($groupFields['NAME']) . '</a>'
							);
						}

						$resultFields['ERROR_MESSAGE'] = Loc::getMessage('SBPE_MULTIPLE_PREMODERATION2', [
							'#GROUPS_LIST#' => implode(', ', $groupNameList)
						]);
						$resultFields['ERROR_MESSAGE_PUBLIC'] = $resultFields['ERROR_MESSAGE'];
					}
				}
				else
				{
					$resultFields['ERROR_MESSAGE'] = Loc::getMessage('SONET_HELPER_NO_PERMISSIONS');
				}
			}
		}

		if ($extranetUser)
		{
			$destinationList = array_filter($destinationList, static function ($code) {
				return (!preg_match('/^(DR|D)(\d+)$/i', $code, $matches));
			});

			if (
				!empty($newUserIdList)
				&& Loader::includeModule('extranet')
			)
			{
				$visibleUserIdList = \CExtranet::getMyGroupsUsersSimple(SITE_ID);

				if (!empty(array_diff($newUserIdList, $visibleUserIdList)))
				{
					$resultFields['ERROR_MESSAGE'] = Loc::getMessage('SONET_HELPER_NO_PERMISSIONS');
				}
			}
		}

		if (
			!$allowToAll
			&& in_array("UA", $destinationList, true)
		)
		{
			foreach ($destinationList as $key => $value)
			{
				if ($value === "UA")
				{
					unset($destinationList[$key]);
					break;
				}
			}
		}

		if ($extranetUser)
		{
			if (
				empty($destinationList)
				|| in_array("UA", $destinationList, true)
			)
			{
				$resultFields["ERROR_MESSAGE"] .= Loc::getMessage("BLOG_BPE_EXTRANET_ERROR");
			}
		}
		elseif (empty($destinationList))
		{
			$resultFields["ERROR_MESSAGE"] .= Loc::getMessage("BLOG_BPE_DESTINATION_EMPTY");
		}

		return $destinationList;
	}

	public static function getBlogPostCacheDir($params = array())
	{
		static $allowedTypes = array(
			'post_general',
			'post',
			'post_urlpreview',
			'posts_popular',
			'post_comments',
			'posts_last',
			'posts_last_blog'
		);

		$result = false;

		if (!is_array($params))
		{
			return $result;
		}

		$type = ($params['TYPE'] ?? false);

		if (
			!$type
			|| !in_array($type, $allowedTypes, true)
		)
		{
			return $result;
		}

		$postId = (
			isset($params['POST_ID'])
			&& (int)$params['POST_ID'] > 0
				? (int)$params['POST_ID']
				: false
		);

		if (
			!$postId
			&& in_array($type, array('post_general', 'post', 'post_comments', 'post_urlpreview'))
		)
		{
			return $result;
		}

		$siteId = ($params['SITE_ID'] ?? SITE_ID);

		switch($type)
		{
			case 'post':
				$result = "/blog/socnet_post/".(int)($postId / 100)."/".$postId."/";
				break;
			case 'post_general':
				$result = "/blog/socnet_post/gen/".(int)($postId / 100)."/".$postId;
				break;
			case 'post_urlpreview':
				$result = "/blog/socnet_post/urlpreview/".(int)($postId / 100)."/".$postId;
				break;
			case 'posts_popular':
				$result = "/".$siteId."/blog/popular_posts/";
				break;
			case 'posts_last':
				$result = "/".$siteId."/blog/last_messages_list/";
				break;
			case 'posts_last_blog':
				$result = "/".$siteId."/blog/last_messages/";
				break;
			case 'post_comments':
				$result = "/blog/comment/".(int)($postId / 100)."/".$postId."/";
				break;
			default:
				$result = false;
		}

		return $result;
	}

	public static function getLivefeedRatingData($params = [])
	{
		global $USER;

		$result = [];

		$logIdList = (
			!empty($params['logId'])
				? $params['logId']
				: []
		);

		if (!is_array($logIdList))
		{
			$logIdList = [ $logIdList ];
		}

		if (empty($logIdList))
		{
			return $result;
		}

		$ratingId = \CRatings::getAuthorityRating();
		if ((int)$ratingId <= 0)
		{
			return $result;
		}

		$result = array_fill_keys($logIdList, []);

		$topCount = (
			isset($params['topCount'])
				? (int)$params['topCount']
				: 0
		);

		if ($topCount <= 0)
		{
			$topCount = 2;
		}

		if ($topCount > 5)
		{
			$topCount = 5;
		}

		$avatarSize = (
			isset($params['avatarSize'])
				? (int)$params['avatarSize']
				: 100
		);

		$connection = Application::getConnection();
		$connection->queryExecute('SET @user_rank = 0');
		$connection->queryExecute('SET @current_log_id = 0');

		if (ModuleManager::isModuleInstalled('intranet'))
		{
			$res = $connection->query('SELECT /*+ NO_DERIVED_CONDITION_PUSHDOWN() */
				@user_rank := IF(
					@current_log_id = tmp.LOG_ID,
					@user_rank + 1,
					1
				) as USER_RANK,
				@current_log_id := tmp.LOG_ID,
				tmp.USER_ID as USER_ID,
				tmp.LOG_ID as LOG_ID,
				tmp.WEIGHT as WEIGHT
			FROM (
				SELECT /*+ NO_DERIVED_CONDITION_PUSHDOWN() */
					@rownum := @rownum + 1 as ROWNUM,
					RS1.ENTITY_ID as USER_ID,
					SL.ID as LOG_ID,
					MAX(RS1.VOTES) as WEIGHT
				FROM
					b_rating_subordinate RS1,
					b_rating_vote RV1
				INNER JOIN b_sonet_log SL
					ON SL.RATING_TYPE_ID = RV1.ENTITY_TYPE_ID
					AND SL.RATING_ENTITY_ID = RV1.ENTITY_ID
					AND SL.ID IN ('.implode(',', $logIdList).')
				WHERE
					RS1.ENTITY_ID = RV1.USER_ID
					AND RS1.RATING_ID = '.(int)$ratingId.'
				GROUP BY
					SL.ID, RS1.ENTITY_ID
				ORDER BY
					SL.ID,
					WEIGHT DESC
			) tmp');
		}
		else
		{
			$res = $connection->query('SELECT /*+ NO_DERIVED_CONDITION_PUSHDOWN() */
				@user_rank := IF(
					@current_log_id = tmp.LOG_ID,
					@user_rank + 1,
					1
				) as USER_RANK,
				@current_log_id := tmp.LOG_ID,
				tmp.USER_ID as USER_ID,
				tmp.LOG_ID as LOG_ID,
				tmp.WEIGHT as WEIGHT
			FROM (
				SELECT /*+ NO_DERIVED_CONDITION_PUSHDOWN() */
					@rownum := @rownum + 1 as ROWNUM,
					RV1.USER_ID as USER_ID,
					SL.ID as LOG_ID,
					RV1.VALUE as WEIGHT
				FROM
					b_rating_vote RV1
				INNER JOIN b_sonet_log SL
					ON SL.RATING_TYPE_ID = RV1.ENTITY_TYPE_ID
					AND SL.RATING_ENTITY_ID = RV1.ENTITY_ID
					AND SL.ID IN ('.implode(',', $logIdList).')
				ORDER BY
					SL.ID,
					WEIGHT DESC
			) tmp');
		}

		$userWeightData = [];
		$logUserData = [];

		$currentLogId = 0;
		$hasMine = false;
		$cnt = 0;

		while ($voteFields = $res->fetch())
		{
			$voteUserId = (int)$voteFields['USER_ID'];
			$voteLogId = (int)$voteFields['LOG_ID'];

			if (
				!$hasMine
				&& $voteUserId === (int)$USER->getId()
			)
			{
				$hasMine = true;
			}

			if ($voteLogId !== $currentLogId)
			{
				$cnt = 0;
				$hasMine = false;
				$logUserData[$voteLogId] = [];
			}

			$currentLogId = $voteLogId;

			if (in_array($voteUserId, $logUserData[$voteLogId], true))
			{
				continue;
			}

			$cnt++;

			if ($cnt > ($hasMine ? $topCount+1 : $topCount))
			{
				continue;
			}

			$logUserData[$voteLogId][] = $voteUserId;
			if (!isset($userWeightData[$voteUserId]))
			{
				$userWeightData[$voteUserId] = (float)$voteFields['WEIGHT'];
			}
		}

		$userData = [];

		if (!empty($userWeightData))
		{
			$res = Main\UserTable::getList([
				'filter' => [
					'@ID' => array_keys($userWeightData)
				],
				'select' => [ 'ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'LOGIN', 'PERSONAL_PHOTO', 'PERSONAL_GENDER' ]
			]);

			while ($userFields = $res->fetch())
			{
				$userData[$userFields["ID"]] = [
					'NAME_FORMATTED' => \CUser::formatName(
						\CSite::getNameFormat(false),
						$userFields,
						true
					),
					'PERSONAL_PHOTO' => [
						'ID' => $userFields['PERSONAL_PHOTO'],
						'SRC' => false
					],
					'PERSONAL_GENDER' => $userFields['PERSONAL_GENDER']
				];

				if ((int)$userFields['PERSONAL_PHOTO'] > 0)
				{
					$imageFile = \CFile::getFileArray($userFields["PERSONAL_PHOTO"]);
					if ($imageFile !== false)
					{
						$file = \CFile::resizeImageGet(
							$imageFile,
							[
								'width' => $avatarSize,
								'height' => $avatarSize,
							],
							BX_RESIZE_IMAGE_EXACT,
							false
						);
						$userData[$userFields["ID"]]['PERSONAL_PHOTO']['SRC'] = $file['src'];
					}
				}
			}
		}

		foreach ($logUserData as $logId => $userIdList)
		{
			$result[$logId] = [];

			foreach ($userIdList as $userId)
			{
				$result[$logId][] = [
					'ID' => $userId,
					'NAME_FORMATTED' => $userData[$userId]['NAME_FORMATTED'],
					'PERSONAL_PHOTO' => $userData[$userId]['PERSONAL_PHOTO']['ID'],
					'PERSONAL_PHOTO_SRC' => $userData[$userId]['PERSONAL_PHOTO']['SRC'],
					'PERSONAL_GENDER' => $userData[$userId]['PERSONAL_GENDER'],
					'WEIGHT' => $userWeightData[$userId]
				];
			}
		}

		foreach ($result as $logId => $data)
		{
			usort(
				$data,
				static function($a, $b)
				{
					if (
						!isset($a['WEIGHT'], $b['WEIGHT'])
						|| $a['WEIGHT'] === $b['WEIGHT']
					)
					{
						return 0;
					}
					return ($a['WEIGHT'] > $b['WEIGHT']) ? -1 : 1;
				}
			);
			$result[$logId] = $data;
		}

		return $result;
	}

	public static function isCurrentUserExtranet($params = [])
	{
		static $result = [];

		$siteId = (!empty($params['siteId']) ? $params['siteId'] : SITE_ID);

		if (!isset($result[$siteId]))
		{
			$result[$siteId] = (
				!\CSocNetUser::isCurrentUserModuleAdmin($siteId, false)
				&& Loader::includeModule('extranet')
				&& !\CExtranet::isIntranetUser()
			);
		}

		return $result[$siteId];
	}

	public static function userLogSubscribe($params = array())
	{
		static
			$logAuthorList = array(),
			$logDestUserList = array();

		$userId = (isset($params['userId']) ? (int)$params['userId'] : 0);
		$logId = (isset($params['logId']) ? (int)$params['logId'] : 0);
		$typeList = ($params['typeList'] ?? []);
		$siteId = (isset($params['siteId']) ? (int)$params['siteId'] : SITE_ID);
		$followByWF = !empty($params['followByWF']);

		if (!is_array($typeList))
		{
			$typeList = array($typeList);
		}

		if (
			$userId <= 0
			|| $logId <= 0
		)
		{
			return false;
		}

		$followRes = false;

		if (in_array('FOLLOW', $typeList))
		{
			$followRes = \CSocNetLogFollow::set(
				$userId,
				"L".$logId,
				"Y",
				(
					!empty($params['followDate'])
						? (
							mb_strtoupper($params['followDate']) === 'CURRENT'
								? ConvertTimeStamp(time() + \CTimeZone::getOffset(), "FULL", $siteId)
								: $params['followDate']
						)
						: false
				),
				$siteId,
				$followByWF
			);
		}

		if (in_array('COUNTER_COMMENT_PUSH', $typeList))
		{
			if (!isset($logAuthorList[$logId]))
			{
				$res = LogTable::getList(array(
					'filter' => array(
						'=ID' => $logId
					),
					'select' => array('USER_ID')
				));
				if ($logFields = $res->fetch())
				{
					$logAuthorList[$logId] = $logFields['USER_ID'];
				}
			}

			if (!isset($logDestUserList[$logId]))
			{
				$logDestUserList[$logId] = array();
				$res = LogRightTable::getList(array(
					'filter' => array(
						'=LOG_ID' => $logId
					),
					'select' => array('GROUP_CODE')
				));
				while ($logRightFields = $res->fetch())
				{
					if (preg_match('/^U(\d+)$/', $logRightFields['GROUP_CODE'], $matches))
					{
						$logDestUserList[$logId][] = $matches[1];
					}
				}
			}

			if (
				$userId != $logAuthorList[$logId]
				&& !in_array($userId, $logDestUserList[$logId])
			)
			{
				LogSubscribeTable::set(array(
					'userId' => $userId,
					'logId' => $logId,
					'type' => LogSubscribeTable::TYPE_COUNTER_COMMENT_PUSH,
					'ttl' => true
				));
			}
		}

		return (
			in_array('FOLLOW', $typeList)
				? $followRes
				: true
		);
	}

	public static function getLFCommentsParams($eventFields = array()): array
	{
		$forumMetaData = \CSocNetLogTools::getForumCommentMetaData($eventFields["EVENT_ID"]);

		if (
			$forumMetaData
			&& $eventFields["SOURCE_ID"] > 0
		)
		{
			$result = [
				"ENTITY_TYPE" => $forumMetaData[1],
				"ENTITY_XML_ID" => $forumMetaData[0]."_".$eventFields["SOURCE_ID"],
				"NOTIFY_TAGS" => $forumMetaData[2]
			];

			// Calendar events could generate different livefeed entries with same SOURCE_ID
			// That's why we should add entry ID to make comment interface work
			if (
				$eventFields["EVENT_ID"] === 'calendar'
				&& !empty($eventFields["PARAMS"])
				&& ($calendarEventParams = unserialize(htmlspecialcharsback($eventFields["PARAMS"]), [ 'allowed_classes' => false ]))
				&& !empty($calendarEventParams['COMMENT_XML_ID'])
			)
			{
				$result["ENTITY_XML_ID"] = $calendarEventParams['COMMENT_XML_ID'];
			}
		}
		elseif ($eventFields["EVENT_ID"] === 'photo') // photo album
		{
			$result = array(
				"ENTITY_TYPE" => 'PA',
				"ENTITY_XML_ID" => 'PHOTO_ALBUM_'.$eventFields["ID"],
				"NOTIFY_TAGS" => ''
			);
		}
		else
		{
			$result = array(
				"ENTITY_TYPE" => mb_substr(mb_strtoupper($eventFields["EVENT_ID"])."_".$eventFields["ID"], 0, 2),
				"ENTITY_XML_ID" => mb_strtoupper($eventFields["EVENT_ID"])."_".$eventFields["ID"],
				"NOTIFY_TAGS" => ""
			);
		}

		if (
			mb_strtoupper($eventFields["ENTITY_TYPE"]) === "CRMACTIVITY"
			&& Loader::includeModule('crm')
			&& ($activityFields = \CCrmActivity::getById($eventFields["ENTITY_ID"], false))
			&& (
				$activityFields["TYPE_ID"] == \CCrmActivityType::Task
				|| (
					(int)$activityFields['TYPE_ID'] === \CCrmActivityType::Provider
					&& $activityFields['PROVIDER_ID'] === Task::getId()
				)
			)
		)
		{
			$result["ENTITY_XML_ID"] = "TASK_".$activityFields["ASSOCIATED_ENTITY_ID"];
		}
		elseif (
			$eventFields["ENTITY_TYPE"] === "WF"
			&& is_numeric($eventFields["SOURCE_ID"])
			&& (int)$eventFields["SOURCE_ID"] > 0
			&& Loader::includeModule('bizproc')
			&& ($workflowId = \CBPStateService::getWorkflowByIntegerId($eventFields["SOURCE_ID"]))
		)
		{
			$result["ENTITY_XML_ID"] = "WF_".$workflowId;
		}

		return $result;
	}

	public static function checkCanCommentInWorkgroup($params)
	{
		static $canCommentCached = [];

		$userId = (isset($params['userId']) ? (int)$params['userId'] : 0);
		$workgroupId = (isset($params['workgroupId']) ? (int)$params['workgroupId'] : 0);
		if (
			$userId <= 0
			|| $workgroupId <= 0
		)
		{
			return false;
		}

		$cacheKey = $userId.'_'.$workgroupId;

		if (!isset($canCommentCached[$cacheKey]))
		{
			$canCommentCached[$cacheKey] = (
				\CSocNetFeaturesPerms::canPerformOperation($userId, SONET_ENTITY_GROUP, $workgroupId, "blog", "premoderate_comment")
				|| \CSocNetFeaturesPerms::canPerformOperation($userId, SONET_ENTITY_GROUP, $workgroupId, "blog", "write_comment")
			);
		}

		return $canCommentCached[$cacheKey];
	}

	public static function checkLivefeedTasksAllowed()
	{
		return Option::get('socialnetwork', 'livefeed_allow_tasks', true);
	}

	public static function convertSelectorRequestData(array &$postFields = [], array $params = []): void
	{
		$perms = (string)($params['perms'] ?? '');
		$crm = (bool)($params['crm'] ?? false);

		$mapping = [
			'DEST_DATA' => 'DEST_CODES',
			'GRAT_DEST_DATA' => 'GRAT_DEST_CODES',
		];

		foreach ($mapping as $from => $to)
		{
			if (isset($postFields[$from]))
			{
				try
				{
					$entities = Json::decode($postFields[$from]);
				}
				catch (ArgumentException $e)
				{
					$entities = [];
				}

				$postFields[$to] = array_merge(
					($postFields[$to] ?? []),
					\Bitrix\Main\UI\EntitySelector\Converter::convertToFinderCodes($entities)
				);
			}
		}

		$mapping = [
			'DEST_CODES' => 'SPERM',
			'GRAT_DEST_CODES' => 'GRAT',
			'EVENT_DEST_CODES' => 'EVENT_PERM'
		];

		foreach ($mapping as $from => $to)
		{
			if (isset($postFields[$from]))
			{
				if (
					!isset($postFields[$to])
					|| !is_array($postFields[$to])
				)
				{
					$postFields[$to] = [];
				}

				foreach ($postFields[$from] as $destCode)
				{
					if ($destCode === 'UA')
					{
						if (empty($postFields[$to]['UA']))
						{
							$postFields[$to]['UA'] = [];
						}
						$postFields[$to]['UA'][] = 'UA';
					}
					elseif (preg_match('/^UE(.+)$/i', $destCode, $matches))
					{
						if (empty($postFields[$to]['UE']))
						{
							$postFields[$to]['UE'] = [];
						}
						$postFields[$to]['UE'][] = $matches[1];
					}
					elseif (preg_match('/^U(\d+)$/i', $destCode, $matches))
					{
						if (empty($postFields[$to]['U']))
						{
							$postFields[$to]['U'] = [];
						}
						$postFields[$to]['U'][] = 'U' . $matches[1];
					}
					elseif (
						$from === 'DEST_CODES'
						&& $perms === BLOG_PERMS_FULL
						&& preg_match('/^UP(\d+)$/i', $destCode, $matches)
						&& Loader::includeModule('blog')
					)
					{
						if (empty($postFields[$to]['UP']))
						{
							$postFields[$to]['UP'] = [];
						}
						$postFields[$to]['UP'][] = 'UP' . $matches[1];
					}
					elseif (preg_match('/^SG(\d+)$/i', $destCode, $matches))
					{
						if (empty($postFields[$to]['SG']))
						{
							$postFields[$to]['SG'] = [];
						}
						$postFields[$to]['SG'][] = 'SG' . $matches[1];
					}
					elseif (preg_match('/^DR(\d+)$/i', $destCode, $matches))
					{
						if (empty($postFields[$to]['DR']))
						{
							$postFields[$to]['DR'] = [];
						}
						$postFields[$to]['DR'][] = 'DR' . $matches[1];
					}
					elseif ($crm && preg_match('/^CRMCONTACT(\d+)$/i', $destCode, $matches))
					{
						if (empty($postFields[$to]['CRMCONTACT']))
						{
							$postFields[$to]['CRMCONTACT'] = [];
						}
						$postFields[$to]['CRMCONTACT'][] = 'CRMCONTACT' . $matches[1];
					}
					elseif ($crm && preg_match('/^CRMCOMPANY(\d+)$/i', $destCode, $matches))
					{
						if (empty($postFields[$to]['CRMCOMPANY']))
						{
							$postFields[$to]['CRMCOMPANY'] = [];
						}
						$postFields[$to]['CRMCOMPANY'][] = 'CRMCOMPANY' . $matches[1];
					}
					elseif ($crm && preg_match('/^CRMLEAD(\d+)$/i', $destCode, $matches))
					{
						if (empty($postFields[$to]['CRMLEAD']))
						{
							$postFields[$to]['CRMLEAD'] = [];
						}
						$postFields[$to]['CRMLEAD'][] = 'CRMLEAD' . $matches[1];
					}
					elseif ($crm && preg_match('/^CRMDEAL(\d+)$/i', $destCode, $matches))
					{
						if (empty($postFields[$to]['CRMDEAL']))
						{
							$postFields[$to]['CRMDEAL'] = [];
						}
						$postFields[$to]['CRMDEAL'][] = 'CRMDEAL' . $matches[1];
					}
				}

				unset($postFields[$from]);
			}
		}
	}

	public static function isCurrentPageFirst(array $params = []): bool
	{
		$result = false;

		$componentName = (string)($params['componentName'] ?? '');
		$page = (string)($params['page'] ?? '');
		$entityId = (int)($params['entityId'] ?? 0);
		$firstMenuItemCode = (string)($params['firstMenuItemCode'] ?? '');
		$canViewTasks = (bool)($params['canView']['tasks'] ?? false);

		if ($entityId <= 0)
		{
			return $result;
		}

		if ($componentName === 'bitrix:socialnetwork_group')
		{
			if ($firstMenuItemCode !== '')
			{
				return (
					mb_strpos($page, $firstMenuItemCode) !== false
					|| in_array($page, [ 'group', 'group_general', 'group_tasks' ])
				);
			}

			$result = (
				(
					$page === 'group_tasks'
					&& \CSocNetFeatures::IsActiveFeature(SONET_ENTITY_GROUP, $entityId, 'tasks')
					&& $canViewTasks
				)
				|| (
					$page === 'group'
					|| $page === 'group_general'
				)
			);
		}

		return $result;
	}

	public static function getWorkgroupSliderMenuUrlList(array $componentResult = []): array
	{
		return [
			'CARD' => (string)($componentResult['PATH_TO_GROUP_CARD'] ?? ''),
			'EDIT' => (string)($componentResult['PATH_TO_GROUP_EDIT'] ?? ''),
			'COPY' => (string)($componentResult['PATH_TO_GROUP_COPY'] ?? ''),
			'DELETE' => (string)($componentResult['PATH_TO_GROUP_DELETE'] ?? ''),
			'LEAVE' => (string)($componentResult['PATH_TO_USER_LEAVE_GROUP'] ?? ''),
			'JOIN' => (string)($componentResult['PATH_TO_USER_REQUEST_GROUP'] ?? ''),
			'MEMBERS' => (string)($componentResult['PATH_TO_GROUP_USERS'] ?? ''),
			'REQUESTS_IN' => (string)($componentResult['PATH_TO_GROUP_REQUESTS'] ?? ''),
			'REQUESTS_OUT' => (string)($componentResult['PATH_TO_GROUP_REQUESTS_OUT'] ?? ''),
			'FEATURES' => (string)($componentResult['PATH_TO_GROUP_FEATURES'] ?? ''),
		];
	}

	public static function listWorkgroupSliderMenuSignedParameters(array $componentParameters = []): array
	{
		return array_filter($componentParameters, static function ($key) {
/*
			'PATH_TO_USER',
			'PATH_TO_GROUP_EDIT',
			'PATH_TO_GROUP_INVITE',
			'PATH_TO_GROUP_CREATE',
			'PATH_TO_GROUP_COPY',
			'PATH_TO_GROUP_REQUEST_SEARCH',
			'PATH_TO_USER_REQUEST_GROUP',
			'PATH_TO_GROUP_REQUESTS',
			'PATH_TO_GROUP_REQUESTS_OUT',
			'PATH_TO_GROUP_MODS',
			'PATH_TO_GROUP_USERS',
			'PATH_TO_USER_LEAVE_GROUP',
			'PATH_TO_GROUP_DELETE',
			'PATH_TO_GROUP_FEATURES',
			'PATH_TO_GROUP_BAN',
			'PATH_TO_SEARCH',
			'PATH_TO_SEARCH_TAG',
			'PATH_TO_GROUP_BLOG_POST',
			'PATH_TO_GROUP_BLOG',
			'PATH_TO_BLOG',
			'PATH_TO_POST',
			'PATH_TO_POST_EDIT',
			'PATH_TO_USER_BLOG_POST_IMPORTANT',
			'PATH_TO_GROUP_FORUM',
			'PATH_TO_GROUP_FORUM_TOPIC',
			'PATH_TO_GROUP_FORUM_MESSAGE',
			'PATH_TO_GROUP_SUBSCRIBE',
			'PATH_TO_MESSAGE_TO_GROUP',
			'PATH_TO_GROUP_TASKS',
			'PATH_TO_GROUP_TASKS_TASK',
			'PATH_TO_GROUP_TASKS_VIEW',
			'PATH_TO_GROUP_CONTENT_SEARCH',
			'PATH_TO_MESSAGES_CHAT',
			'PATH_TO_VIDEO_CALL',
			'PATH_TO_CONPANY_DEPARTMENT',
			'PATH_TO_USER_LOG',
			'PATH_TO_GROUP_LOG',

			'PAGE_VAR',
			'USER_VAR',
			'GROUP_VAR',
			'TASK_VAR',
			'TASK_ACTION_VAR',

			'SET_NAV_CHAIN',
			'USER_ID',
			'GROUP_ID',
			'ITEMS_COUNT',
			'FORUM_ID',
			'BLOG_GROUP_ID',
			'TASK_FORUM_ID',
			'THUMBNAIL_LIST_SIZE',
			'DATE_TIME_FORMAT',
			'SHOW_YEAR',
			'NAME_TEMPLATE',
			'SHOW_LOGIN',
			'CAN_OWNER_EDIT_DESKTOP',
			'CACHE_TYPE',
			'CACHE_TIME',
			'USE_MAIN_MENU',
			'LOG_SUBSCRIBE_ONLY',
			'GROUP_PROPERTY',
			'GROUP_USE_BAN',
			'BLOG_ALLOW_POST_CODE',
			'SHOW_RATING',
			'LOG_THUMBNAIL_SIZE',
			'LOG_COMMENT_THUMBNAIL_SIZE',
			'LOG_NEW_TEMPLATE',

*/
			return (in_array($key, [
				'GROUP_ID',
				'SET_TITLE',
				'PATH_TO_GROUP',
			]));
		}, ARRAY_FILTER_USE_KEY);
	}

	public static function getWorkgroupSliderMenuSignedParameters(array $params): string
	{
		return Main\Component\ParameterSigner::signParameters(self::getWorkgroupSliderMenuSignedParametersSalt(), $params);
	}


	public static function getWorkgroupSliderMenuUnsignedParameters(array $sourceParametersList = [])
	{
		foreach ($sourceParametersList as $source)
		{
			if (isset($source['signedParameters']) && is_string($source['signedParameters']))
			{
				try
				{
					$componentParameters = ParameterSigner::unsignParameters(
						self::getWorkgroupSliderMenuSignedParametersSalt(),
						$source['signedParameters']
					);
					$componentParameters['IFRAME'] = 'Y';
					return $componentParameters;
				}
				catch (BadSignatureException $exception)
				{}

				return [];
			}
		}

		return [];
	}

	public static function getWorkgroupSliderMenuSignedParametersSalt(): string
	{
		return 'bitrix:socialnetwork.group.card.menu';
	}

	public static function getWorkgroupAvatarToken($fileId = 0): string
	{
		if ($fileId <= 0)
		{
			return '';
		}

		$filePath = \CFile::getPath($fileId);
		if ((string)$filePath === '')
		{
			return '';
		}

		$signer = new \Bitrix\Main\Security\Sign\Signer;
		return $signer->sign(serialize([ $fileId, $filePath ]), 'workgroup_avatar_token');
	}

	public static function checkEmptyParamInteger(&$params, $paramName, $defaultValue): void
	{
		$params[$paramName] = (isset($params[$paramName]) && (int)$params[$paramName] > 0 ? (int)$params[$paramName] : $defaultValue);
	}

	public static function checkEmptyParamString(&$params, $paramName, $defaultValue): void
	{
		$params[$paramName] = (isset($params[$paramName]) && trim($params[$paramName]) !== '' ? trim($params[$paramName]) : $defaultValue);
	}

	public static function checkTooltipComponentParams($params): array
	{
		if (ModuleManager::isModuleInstalled('intranet'))
		{
			$defaultFields = [
				'EMAIL',
				'PERSONAL_MOBILE',
				'WORK_PHONE',
				'PERSONAL_ICQ',
				'PERSONAL_PHOTO',
				'PERSONAL_CITY',
				'WORK_COMPANY',
				'WORK_POSITION',
			];
			$defaultProperties = [
				'UF_DEPARTMENT',
				'UF_PHONE_INNER',
			];
		}
		else
		{
			$defaultFields = [
				"PERSONAL_ICQ",
				"PERSONAL_BIRTHDAY",
				"PERSONAL_PHOTO",
				"PERSONAL_CITY",
				"WORK_COMPANY",
				"WORK_POSITION"
			];
			$defaultProperties = [];
		}

		return [
			'SHOW_FIELDS_TOOLTIP' => ($params['SHOW_FIELDS_TOOLTIP'] ?? unserialize(Option::get('socialnetwork', 'tooltip_fields', serialize($defaultFields)), ['allowed_classes' => false])),
			'USER_PROPERTY_TOOLTIP' => ($params['USER_PROPERTY_TOOLTIP'] ?? unserialize(Option::get('socialnetwork', 'tooltip_properties', serialize($defaultProperties)), ['allowed_classes' => false])),
		];
	}

	public static function getWorkgroupPageTitle(array $params = []): string
	{
		$workgroupName = (string)($params['WORKGROUP_NAME'] ?? '');
		$workgroupId = (int)($params['WORKGROUP_ID'] ?? 0);

		if (
			$workgroupName === ''
			&& $workgroupId > 0
		)
		{
			$groupFields = \CSocNetGroup::getById($workgroupId, true);
			if (!empty($groupFields))
			{
				$workgroupName = $groupFields['NAME'];
			}
		}

		return Loc::getMessage('SONET_HELPER_PAGE_TITLE_WORKGROUP_TEMPLATE', [
			'#WORKGROUP#' => $workgroupName,
			'#TITLE#' => ($params['TITLE'] ?? ''),
		]);
	}
}