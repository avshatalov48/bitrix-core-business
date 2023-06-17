<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

/** @property-write int PostId */
/** @property-write string PostUrl */
/** @property-write string PostUrlBb */
class CBPSocnetBlogPostActivity extends CBPActivity
{
	private const ATTACHMENT_TYPE_FILE = 'file';
	private const ATTACHMENT_TYPE_DISK = 'disk';

	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = [
			'Title' => '',
			'OwnerId' => '',
			'UsersTo' => '',
			'PostTitle' => '',
			'PostMessage' => '',
			'PostSite' => '',
			'AttachmentType' => static::ATTACHMENT_TYPE_FILE,
			'Attachment' => null,
			'Tags' => [],
			//Return
			'PostId'=> null,
			'PostUrl' => null,
			'PostUrlBb' => null,
		];

		$this->setPropertiesTypes([
			'PostId' => ['Type' => 'int'],
			'PostUrl' => ['Type' => 'string'],
			'PostUrlBb' => ['Type' => 'string'],
		]);
	}

	protected function reInitialize()
	{
		parent::reInitialize();
		$this->PostId = null;
		$this->PostUrl = null;
		$this->PostUrlBb = null;
	}

	public function execute()
	{
		global $DB, $APPLICATION;

		if (!CModule::IncludeModule('socialnetwork') || !CModule::IncludeModule('blog'))
		{
			return CBPActivityExecutionStatus::Closed;
		}

		$documentId = $this->getDocumentId();

		$siteId = $this->PostSite ? $this->PostSite : SITE_ID;
		$ownerId = CBPHelper::extractUsers($this->OwnerId, $documentId, true);
		$usersTo = $this->UsersTo;
		$title = $this->getPostTitle();
		$message = $this->postMessageToText();

		$this->logDebug($title, $ownerId, $usersTo);

		if (empty($ownerId))
		{
			$this->writeToTrackingService(
				GetMessage('SNBPA_EMPTY_OWNER'),
				0,
				CBPTrackingType::Error
			);

			return CBPActivityExecutionStatus::Closed;
		}

		$pathToPost = \Bitrix\Socialnetwork\Helper\Path::get('userblogpost_page', $siteId);
		$pathToSmile = COption::GetOptionString('socialnetwork', 'smile_page', false, $siteId);
		$blogGroupID = COption::GetOptionString('socialnetwork', 'userbloggroup_id', false, $siteId);

		$blog = CBlog::GetByOwnerID($ownerId);
		if (!$blog)
		{
			$blog = $this->createBlog($ownerId, $blogGroupID, $siteId);
		}

		$micro = 'N';
		if (!$title)
		{
			$micro = 'Y';
			$title = trim(preg_replace(
				["/\n+/is" . BX_UTF_PCRE_MODIFIER, '/\s+/is' . BX_UTF_PCRE_MODIFIER],
				' ',
				blogTextParser::killAllTags($this->PostMessage)
			));
		}

		$socnetRights = $this->getSocnetRights($usersTo);

		if (empty($socnetRights))
		{
			$this->writeToTrackingService(
				GetMessage('SNBPA_EMPTY_USERS'),
				0,
				CBPTrackingType::Error
			);

			return CBPActivityExecutionStatus::Closed;
		}

		try
		{
			$postFields = [
				'TITLE' => $title,
				'DETAIL_TEXT' => $message,
				'DETAIL_TEXT_TYPE' => 'text',
				'=DATE_PUBLISH' => $DB->CurrentTimeFunction(),
				'PUBLISH_STATUS' => BLOG_PUBLISH_STATUS_PUBLISH,
				'CATEGORY_ID' => '',
				'PATH' => CComponentEngine::MakePathFromTemplate(
					$pathToPost,
					['post_id' => '#post_id#', 'user_id' => $ownerId]
				),
				'URL' => $blog['URL'],
				'PERMS_POST' => [],
				'PERMS_COMMENT' => [],
				'MICRO' => $micro,
				'SOCNET_RIGHTS' => $socnetRights,
				'=DATE_CREATE' => $DB->CurrentTimeFunction(),
				'AUTHOR_ID' => $ownerId,
				'BLOG_ID' => $blog['ID'],
				'HAS_IMAGES' => 'N',
				'HAS_TAGS' => 'N',
				'HAS_PROPS' => 'N',
				'HAS_SOCNET_ALL' => 'N',
				'SEARCH_GROUP_ID' => $blogGroupID,
			];

			if (
				!empty($postFields['SOCNET_RIGHTS'])
				&& count($postFields['SOCNET_RIGHTS']) == 1
				&& in_array('UA', $postFields['SOCNET_RIGHTS'])
			)
			{
				$postFields['HAS_SOCNET_ALL'] = 'Y';
			}

			$newId = CBlogPost::add($postFields);
			if ($newId === false && $APPLICATION->GetException())
			{
				$this->writeToTrackingService(
					$APPLICATION->GetException()->GetString(),
					0,
					CBPTrackingType::Error
				);

				return CBPActivityExecutionStatus::Closed;
			}

			$this->PostId = $newId;
			$postFields['ID'] = $newId;

			if (class_exists(\Bitrix\Disk\Integration\Bizproc\File::class))
			{
				$this->attachFiles($newId);
			}
			$this->addTags($postFields);

			$arParamsNotify = [
				'bSoNet' => true,
				'UserID' => $ownerId,
				'allowVideo' => COption::GetOptionString('blog', 'allow_video', 'Y'),
				'PATH_TO_SMILE' => $pathToSmile,
				'PATH_TO_POST' => $pathToPost,
				'user_id' => $ownerId,
				'NAME_TEMPLATE' => CSite::GetNameFormat(false),
				'SITE_ID' => $siteId,
			];
			CBlogPost::Notify($postFields, $blog, $arParamsNotify);

			BXClearCache(true, \Bitrix\Socialnetwork\ComponentHelper::getBlogPostCacheDir([
				'TYPE' => 'posts_last',
				'SITE_ID' => $siteId,
			]));

			$postUrl = CComponentEngine::MakePathFromTemplate(
				$pathToPost,
				['post_id' => $newId, 'user_id' => $ownerId]
			);
			$arFieldsIM = [
				'TYPE' => 'POST',
				'TITLE' => $postFields['TITLE'],
				'URL' => $postUrl,
				'ID' => $newId,
				'FROM_USER_ID' => $ownerId,
				'TO_USER_ID' => [],
				'TO_SOCNET_RIGHTS' => $postFields['SOCNET_RIGHTS'],
				'TO_SOCNET_RIGHTS_OLD' => [],
			];
			CBlogPost::NotifyIm($arFieldsIM);

			$this->PostUrl = $postUrl;
			$this->PostUrlBb = sprintf('[url=%s]%s[/url]', $postUrl, $title);
			$this->logDebugPost($postUrl);
		}
		catch (Exception $e)
		{
			$this->writeToTrackingService($e->getMessage());
		}

		return CBPActivityExecutionStatus::Closed;
	}

	private function attachFiles(int $postId)
	{
		if (!\Bitrix\Main\Loader::includeModule('disk'))
		{
			return;
		}

		$attachmentType = $this->AttachmentType;
		$attachments = $this->Attachment;

		if (!is_array($attachments) || !$attachments)
		{
			return;
		}

		$fileIds = [];
		if ($attachmentType === static::ATTACHMENT_TYPE_DISK)
		{
			$fileIds = $this->copyDiskFiles($attachments);
		}
		elseif ($attachmentType === static::ATTACHMENT_TYPE_FILE)
		{
			$fileIds = $this->uploadFilesToDisk($attachments);
		}

		\CBlogPost::update(
			$postId,
			[
				'HAS_PROPS' => 'Y',
				'UF_BLOG_POST_FILE' => $fileIds,
			]
		);
	}

	private function copyDiskFiles(array $fileIds): array
	{
		if (!\Bitrix\Main\Loader::includeModule('disk'))
		{
			return [];
		}

		$attachments = [];
		foreach ($fileIds as $id)
		{
			$result = \Bitrix\Disk\Integration\Bizproc\File::openById($id)->map(fn ($data) => $data['file']->copy());
			if ($result->isSuccess())
			{
				$attachments[] = $result->getData()['attachmentId'];
			}
			else
			{
				$this->writeToTrackingService(
					implode(', ', $result->getErrorMessages()),
					0,
					CBPTrackingType::Error,
				);
			}
		}

		return $attachments;
	}

	private function uploadFilesToDisk(array $fileIds): array
	{
		if (!\Bitrix\Main\Loader::includeModule('disk'))
		{
			return [];
		}

		$attachments = [];
		foreach ($fileIds as $id)
		{
			$id = (int)$id;
			if (!$id)
			{
				continue;
			}

			$userId = CBPHelper::extractUsers($this->OwnerId, $this->getDocumentId(), true);
			$fileOpenResult = \Bitrix\Bizproc\File::openById($id);
			$uploadResult = $fileOpenResult->map(function ($data) use ($userId)
			{
				return \Bitrix\Disk\Integration\Bizproc\File::uploadUserFile($data['file'], $userId);
			});

			if ($uploadResult->isSuccess())
			{
				$attachments[] = $uploadResult->getData()['attachmentId'];
			}
			else
			{
				$this->writeToTrackingService(
					implode(', ', $uploadResult->getErrorMessages()),
					0,
					CBPTrackingType::Error,
				);
			}
		}

		return $attachments;
	}

	private function addTags($postFields)
	{
		if (!isset($postFields['BLOG_ID'], $postFields['ID']))
		{
			return;
		}

		if (!isset($postFields['DETAIL_TEXT']))
		{
			$postFields['DETAIL_TEXT'] = '';
		}

		$tags = [];
		if (is_array($this->Tags))
		{
			$existingTags = [];
			$existingCategoriesIterator = \CBlogCategory::GetList(
				[],
				[
					'@NAME' => $this->Tags,
					'BLOG_ID' => (int)$postFields['BLOG_ID'],
				],
				false,
				false,
				['ID', 'NAME'],
			);
			while ($category = $existingCategoriesIterator->Fetch())
			{
				$existingTags[$category['NAME']] = $category['ID'];
			}

			foreach ($this->Tags as $tagName)
			{
				if (is_string($tagName) && $tagName)
				{
					if (isset($existingTags[$tagName]))
					{
						$tags[] = $existingTags[$tagName];
					}
					else
					{
						$tags[] = (int)\CBlogCategory::add([
							'BLOG_ID' => $postFields['BLOG_ID'],
							'NAME' => $tagName,
						]);
					}
				}
			}
		}

		$tags = array_merge(
			\Bitrix\Socialnetwork\Component\BlogPostEdit\Tag::parseTagsFromFields([
				'postFields' => $postFields,
				'blogId' => $postFields['BLOG_ID'],
			]),
			$tags,
		);
		if ($tags)
		{
			foreach ($tags as $categoryId)
			{
				\CBlogPostCategory::add([
					'BLOG_ID' => $postFields['BLOG_ID'],
					'POST_ID' => $postFields['ID'],
					'CATEGORY_ID' => $categoryId,
				]);
			}

			\CBlogPost::update(
				$postFields['ID'],
				[
					'CATEGORY_ID' => implode(',', $tags),
					'HAS_TAGS' => 'Y'
				]
			);
		}
	}

	private function getSocnetRights($users)
	{
		$users = (array) $users;
		$result = [];
		$toExtract = [];
		foreach ($users as $user)
		{
			$user = (string) $user;
			if (mb_strpos($user, 'user_') === 0)
			{
				$result[] = 'U'.mb_substr($user, mb_strlen('user_'));
			}
			elseif (mb_strpos($user, 'group_') === 0)
			{
				$code = mb_strtoupper(mb_substr($user, mb_strlen('group_')));
				if (preg_match('#^(DR[0-9]+|SG[0-9]+)$#', $code))
				{
					$result[] = $code;
				}
				elseif (preg_match('#^SG([0-9]+)_K$#', $code, $matches))
				{
					$result[] = 'SG'.$matches[1];
				}
				else
					$toExtract[] = $user;
			}
			else
				$toExtract[] = $user;
		}
		if ($toExtract)
		{
			$extracted = CBPHelper::ExtractUsers($toExtract, $this->GetDocumentId());
			if (is_array($extracted))
			{
				foreach($extracted as $u)
					$result[] = 'U'.$u;
			}
		}

		return array_unique($result);
	}

	private function createBlog($userId, $blogGroupId, $siteId)
	{
		global $DB;
		$arFields = [
			'=DATE_UPDATE' => $DB->CurrentTimeFunction(),
			'GROUP_ID' => $blogGroupId,
			'ACTIVE' => 'Y',
			'ENABLE_COMMENTS' => 'Y',
			'ENABLE_IMG_VERIF' => 'Y',
			'EMAIL_NOTIFY' => 'Y',
			'ENABLE_RSS' => 'Y',
			'ALLOW_HTML' => 'N',
			'ENABLE_TRACKBACK' => 'N',
			'SEARCH_INDEX' => 'Y',
			'USE_SOCNET' => 'Y',
			'=DATE_CREATE' => $DB->CurrentTimeFunction(),
			'PERMS_POST' => [
				1 => 'I',
				2 => 'I',
			],
			'PERMS_COMMENT' => [
				1 => 'P',
				2 => 'P',
			],
		];

		$bRights = false;
		$rsUser = CUser::GetByID($userId);
		$arUser = $rsUser->Fetch();
		if($arUser['NAME'].''.$arUser['LAST_NAME'] == '')
		{
			$arFields['NAME'] = GetMessage('SNBPA_BLOG_NAME').' '.$arUser['LOGIN'];
		}
		else
		{
			$arFields['NAME'] = GetMessage('SNBPA_BLOG_NAME').' '.$arUser['NAME'].' '.$arUser['LAST_NAME'];
		}

		$arFields['URL'] = str_replace(' ', '_', $arUser['LOGIN']).'-blog-'.$siteId;
		$arFields['OWNER_ID'] = $userId;

		$urlCheck = preg_replace("/[^a-zA-Z0-9_-]/is", '', $arFields['URL']);
		if ($urlCheck != $arFields['URL'])
		{
			$arFields['URL'] = 'u'.$userId.'-blog-'.$siteId;
		}

		if(CBlog::GetByUrl($arFields['URL']))
		{
			$uind = 0;
			do
			{
				$uind++;
				$arFields['URL'] = $arFields['URL'].$uind;
			}
			while (CBlog::GetByUrl($arFields['URL']));
		}

		$featureOperationPerms = CSocNetFeaturesPerms::GetOperationPerm(SONET_ENTITY_USER, $arFields['OWNER_ID'], 'blog', 'view_post');
		if ($featureOperationPerms == SONET_RELATIONS_TYPE_ALL)
		{
			$bRights = true;
		}

		$blogID = CBlog::Add($arFields);
		BXClearCache(true, '/blog/form/blog/');
		if ($bRights)
		{
			CBlog::AddSocnetRead($blogID);
		}

		return CBlog::GetByID($blogID, $blogGroupId);
	}

	public static function validateProperties($arTestProperties = [], CBPWorkflowTemplateUser $user = null)
	{
		$errors = [];
		if (empty($arTestProperties['PostMessage']))
		{
			$errors[] = [
				'code' => 'NotExist',
				'parameter' => 'GroupName',
				'message' => GetMessage('SNBPA_EMPTY_POST_MESSAGE'),
			];
		}
		if (empty($arTestProperties['OwnerId']))
		{
			$errors[] = [
				'code' => 'NotExist',
				'parameter' => 'OwnerId',
				'message' => GetMessage('SNBPA_EMPTY_OWNER'),
			];
		}

		$ownerId = $arTestProperties['OwnerId'] ?? null;
		if ($user && $ownerId && $ownerId !== $user->getBizprocId() && !$user->isAdmin())
		{
			$errors[] = [
				'code' => 'NotExist',
				'parameter' => 'OwnerId',
				'message' => GetMessage('SNBPA_OWNER_DENIED'),
			];
		}

		if (empty($arTestProperties['UsersTo']))
		{
			$errors[] = [
				'code' => 'NotExist',
				'parameter' => 'UsersTo',
				'message' => GetMessage('SNBPA_EMPTY_USERS'),
			];
		}

		return array_merge($errors, parent::validateProperties($arTestProperties, $user));
	}

	public static function getPropertiesDialog($documentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues = null, $formName = '')
	{
		$dialog = new \Bitrix\Bizproc\Activity\PropertiesDialog(__FILE__, [
			'documentType' => $documentType,
			'activityName' => $activityName,
			'workflowTemplate' => $arWorkflowTemplate,
			'workflowParameters' => $arWorkflowParameters,
			'workflowVariables' => $arWorkflowVariables,
			'currentValues' => $arCurrentValues,
		]);

		$user = new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser);

		$dialog->setMap(static::getPropertiesMap($documentType, ['user' => $user]));

		$dialog->setRuntimeData([
			'user' => $user,
		]);

		return $dialog;
	}

	public static function getPropertiesDialogValues($documentType, $activityName, &$arWorkflowTemplate, &$arWorkflowParameters, &$arWorkflowVariables, $arCurrentValues, &$arErrors)
	{
		$arErrors = [];

		$arMap = [
			'owner_id' => 'OwnerId',
			'users_to' => 'UsersTo',
			'post_title' => 'PostTitle',
			'post_message' => 'PostMessage',
			'post_site' => 'PostSite',
		];

		$arProperties = [];
		foreach ($arMap as $key => $value)
		{
			if ($key === 'owner_id' || $key === 'users_to')
			{
				continue;
			}
			$arProperties[$value] = $arCurrentValues[$key];
		}

		if ($arProperties['PostSite'] === '')
		{
			$arProperties['PostSite'] = $arCurrentValues['post_site_x'];
		}

		$arErrors = [];
		$user = new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser);
		if ($user->isAdmin())
		{
			$arProperties['OwnerId'] = CBPHelper::UsersStringToArray($arCurrentValues['owner_id'], $documentType, $arErrors);
			if ($arErrors)
			{
				return false;
			}
		}
		else
		{
			$arProperties['OwnerId'] = $user->getBizprocId();
		}

		$arProperties['UsersTo'] = CBPHelper::UsersStringToArray($arCurrentValues['users_to'], $documentType, $arErrors);
		if ($arErrors)
		{
			return false;
		}

		$map = static::getPropertiesMap($documentType);
		$documentService = CBPRuntime::getRuntime()->getDocumentService();
		if (\Bitrix\Main\Loader::includeModule('disk') && class_exists(\Bitrix\Disk\Integration\Bizproc\File::class))
		{
			foreach (['AttachmentType', 'Tags'] as $fieldId)
			{
				if (!isset($map[$fieldId]))
				{
					continue;
				}
				$field = $documentService->getFieldTypeObject($documentType, $map[$fieldId]);
				if (!$field)
				{
					continue;
				}

				$arProperties[$fieldId] = $field->extractValue(
					['Field' => $map[$fieldId]['FieldName']],
					$arCurrentValues,
					$arErrors,
				);
			}

			if ($arProperties['AttachmentType'] === static::ATTACHMENT_TYPE_DISK)
			{
				foreach ((array)$arCurrentValues['attachment'] as $attachmentId)
				{
					$attachmentId = (int)$attachmentId;
					if ($attachmentId > 0)
					{
						$arProperties['Attachment'][] = $attachmentId;
					}
				}
			}
			else
			{
				$arProperties['Attachment'] = $arCurrentValues["attachment"] ?? $arCurrentValues["attachment_text"] ?? [];
			}
		}
		else
		{
			$field = $documentService->getFieldTypeObject($documentType, $map['Tags']);
			if ($field)
			{
				$arProperties['Tags'] = $field->extractValue(
					['Field' => $map['Tags']['FieldName']],
					$arCurrentValues,
					$arErrors,
				);
			}
			else
			{
				$arProperties['Tags'] = [];
			}
		}

		$arErrors = self::validateProperties($arProperties, new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser));
		if ($arErrors)
		{
			return false;
		}

		$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$arCurrentActivity['Properties'] = $arProperties;

		return true;
	}

	protected static function getPropertiesMap(array $documentType, array $context = []): array
	{
		$user = $context['user'] ?? new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser);

		$sites = [];
		$sitesIterator = CSite::GetList('', '', ['ACTIVE' => 'Y']);
		while ($site = $sitesIterator->fetch())
		{
			$sites[$site['LID']] = $site['NAME'];
		}

		$map = [
			'PostTitle' => [
				'Name' => GetMessage('SNBPA_POST_TITLE'),
				'Description' => GetMessage('SNBPA_POST_TITLE'),
				'FieldName' => 'post_title',
				'Type' => 'string'
			],
			'PostMessage' => [
				'Name' => GetMessage('SNBPA_POST_MESSAGE'),
				'Description' => GetMessage('SNBPA_POST_MESSAGE'),
				'FieldName' => 'post_message',
				'Type' => 'text',
				'Required' => true
			],
			'OwnerId' => [
				'Name' => GetMessage('SNBPA_OWNER_ID'),
				'FieldName' => 'owner_id',
				'Type' => 'user',
				'Required' => true,
				'Default' => $user->getBizprocId()
			],
			'UsersTo' => [
				'Name' => GetMessage('SNBPA_USERS_TO'),
				'FieldName' => 'users_to',
				'Type' => 'user',
				'Required' => true,
				'Multiple' => true,
				'Default' => \Bitrix\Bizproc\Automation\Helper::getResponsibleUserExpression($documentType),
			],
			'PostSite' => [
				'Name' => GetMessage('SNBPA_POST_SITE'),
				'FieldName' => 'post_site',
				'Type' => 'select',
				'Options' => $sites
			],
			'Tags' => [
				'Name' => Loc::getMessage('SNBPA_POST_TAG'),
				'FieldName' => 'tags',
				'Type' => \Bitrix\Bizproc\FieldType::STRING,
				'Multiple' => true,
			],
		];

		if (
			\Bitrix\Main\Loader::includeModule('disk')
			&& class_exists(\Bitrix\Disk\Integration\Bizproc\File::class)
		) {
			$map['AttachmentType'] = [
				'Name' => Loc::getMessage('SNBPA_POST_ATTACHMENT_TYPE'),
				'FieldName' => 'attachment_type',
				'Type' => \Bitrix\Bizproc\FieldType::SELECT,
				'Options' => [
					static::ATTACHMENT_TYPE_FILE => Loc::getMessage('SNBPA_ATTACHMENT_FILE'),
					static::ATTACHMENT_TYPE_DISK => Loc::getMessage('SNBPA_ATTACHMENT_DISK'),
				],
			];
			$map['Attachment'] = [
				'Name' => Loc::getMessage('SNBPA_POST_ATTACHMENT'),
				'FieldName' => 'attachment',
				'Type' => \Bitrix\Bizproc\FieldType::FILE,
				'Multiple' => true,
			];
		}

		return $map;
	}

	private function logDebug($title, $ownerId, $usersTo)
	{
		if (!method_exists($this, 'getDebugInfo'))
		{
			return;
		}

		$debugInfo = $this->getDebugInfo([
			'PostTitle' => $title,
			'OwnerId' => $ownerId ? 'user_' . $ownerId : $this->OwnerId,
			'UsersTo' => $usersTo,
		]);

		unset($debugInfo['PostMessage']);
		unset($debugInfo['PostSite']);

		$this->writeDebugInfo($debugInfo);
	}

	private function logDebugPost($url)
	{
		if (!method_exists($this, 'getDebugInfo'))
		{
			return;
		}

		$toWrite = [
			'propertyName' => GetMessage('SNBPA_POST_MESSAGE'),
			'propertyValue' => $url,
			'propertyLinkName' => GetMessage('SNBPA_POST_URL_LABEL'),
		];

		$this->writeDebugTrack(
			$this->getWorkflowInstanceId(),
			$this->getName(),
			$this->executionStatus,
			$this->executionResult,
			$this->Title ?? '',
			$toWrite,
			CBPTrackingType::DebugLink
		);
	}

	private function postMessageToText(): string
	{
		$message = $this->PostMessage;
		if (!is_string($message))
		{
			/** @var CBPDocumentService $documentService */
			$documentService = $this->workflow->getService('DocumentService');
			$fieldType = $documentService->getFieldTypeObject($this->getDocumentType(), ['Type' => 'text']);
			if ($fieldType)
			{
				if (is_array($message))
				{
					$fieldType->setMultiple(true);
				}
				$message = $fieldType->formatValue($message);
			}
		}

		return HTMLToTxt(nl2br($message), '', [], 0);
	}

	private function getPostTitle(): string
	{
		$title = $this->PostTitle;
		if (is_array($title))
		{
			$title = implode(', ', CBPHelper::makeArrayFlat($title));
		}

		return trim((string)$title);
	}
}
