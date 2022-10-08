<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

class CBPSocnetBlogPostActivity extends CBPActivity
{
	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = array(
			'Title' => '',
			'OwnerId' => '',
			'UsersTo' => '',
			'PostTitle' => '',
			'PostMessage' => '',
			'PostSite' => '',
		);
	}

	public function Execute()
	{
		global $DB, $APPLICATION;

		if (!CModule::IncludeModule("socialnetwork") || !CModule::IncludeModule("blog"))
		{
			return CBPActivityExecutionStatus::Closed;
		}

		$rootActivity = $this->GetRootActivity();
		$documentId = $rootActivity->GetDocumentId();

		$siteId = $this->PostSite ? $this->PostSite : SITE_ID;
		$ownerId = CBPHelper::ExtractUsers($this->OwnerId, $documentId, true);
		$usersTo = $this->UsersTo;
		$title = trim($this->PostTitle);
		$message = HTMLToTxt(nl2br($this->PostMessage), '', [], 0);

		$this->logDebug($title, $ownerId, $usersTo);

		if (empty($ownerId))
		{
			$this->WriteToTrackingService(GetMessage('SNBPA_EMPTY_OWNER'), 0, CBPTrackingType::Error);
			return CBPActivityExecutionStatus::Closed;
		}

		$pathToPost = \Bitrix\Socialnetwork\Helper\Path::get('userblogpost_page', $siteId);
		$pathToSmile = COption::GetOptionString("socialnetwork", "smile_page", false, $siteId);
		$blogGroupID = COption::GetOptionString("socialnetwork", "userbloggroup_id", false, $siteId);

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
				array("/\n+/is".BX_UTF_PCRE_MODIFIER, '/\s+/is'.BX_UTF_PCRE_MODIFIER),
				" ",
				blogTextParser::killAllTags($this->PostMessage)
			));
		}

		$socnetRights = $this->getSocnetRights($usersTo);

		if (empty($socnetRights))
		{
			$this->WriteToTrackingService(GetMessage('SNBPA_EMPTY_USERS'), 0, CBPTrackingType::Error);
			return CBPActivityExecutionStatus::Closed;
		}

		try
		{
			$postFields = array(
				'TITLE'            => $title,
				'DETAIL_TEXT'      => $message,
				'DETAIL_TEXT_TYPE' => 'text',
				'=DATE_PUBLISH'    => $DB->CurrentTimeFunction(),
				'PUBLISH_STATUS'   => BLOG_PUBLISH_STATUS_PUBLISH,
				'CATEGORY_ID'      => '',
				'PATH'             => CComponentEngine::MakePathFromTemplate($pathToPost, array("post_id" => "#post_id#", "user_id" => $ownerId)),
				'URL'              => $blog['URL'],
				'PERMS_POST'       => array(),
				'PERMS_COMMENT'    => array(),
				'MICRO'            => $micro,
				'SOCNET_RIGHTS'    => $socnetRights,
				'=DATE_CREATE'     => $DB->CurrentTimeFunction(),
				'AUTHOR_ID'        => $ownerId,
				'BLOG_ID'          => $blog['ID'],
				"HAS_IMAGES"       => "N",
				"HAS_TAGS"         => "N",
				"HAS_PROPS"        => "N",
				"HAS_SOCNET_ALL"   => "N",
				"SEARCH_GROUP_ID"  => $blogGroupID
			);

			if(!empty($postFields["SOCNET_RIGHTS"]) && count($postFields["SOCNET_RIGHTS"]) == 1 && in_array("UA", $postFields["SOCNET_RIGHTS"]))
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

			$postFields["ID"] = $newId;

			$arParamsNotify = Array(
				"bSoNet" => true,
				"UserID" => $ownerId,
				"allowVideo" => COption::GetOptionString("blog","allow_video", "Y"),
				"PATH_TO_SMILE" => $pathToSmile,
				"PATH_TO_POST" => $pathToPost,
				"user_id" => $ownerId,
				"NAME_TEMPLATE" => CSite::GetNameFormat(false),
				"SITE_ID" => $siteId
			);
			CBlogPost::Notify($postFields, $blog, $arParamsNotify);

			BXClearCache(true, \Bitrix\Socialnetwork\ComponentHelper::getBlogPostCacheDir(array(
				'TYPE' => 'posts_last',
				'SITE_ID' => $siteId
			)));

			$postUrl = CComponentEngine::MakePathFromTemplate($pathToPost, ["post_id" => $newId, "user_id" => $ownerId]);
			$arFieldsIM = Array(
				"TYPE" => "POST",
				"TITLE" => $postFields["TITLE"],
				"URL" => $postUrl,
				"ID" => $newId,
				"FROM_USER_ID" => $ownerId,
				"TO_USER_ID" => array(),
				"TO_SOCNET_RIGHTS" => $postFields["SOCNET_RIGHTS"],
				"TO_SOCNET_RIGHTS_OLD" => array()
			);
			CBlogPost::NotifyIm($arFieldsIM);

			$this->logDebugPost($postUrl);
		}
		catch (Exception $e)
		{
			$this->WriteToTrackingService($e->getMessage());
		}

		return CBPActivityExecutionStatus::Closed;
	}

	private function getSocnetRights($users)
	{
		$users = (array) $users;
		$result = array();
		$toExtract = array();
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
		$arFields = array(
			"=DATE_UPDATE" => $DB->CurrentTimeFunction(),
			"GROUP_ID" => $blogGroupId,
			"ACTIVE" => "Y",
			"ENABLE_COMMENTS" => "Y",
			"ENABLE_IMG_VERIF" => "Y",
			"EMAIL_NOTIFY" => "Y",
			"ENABLE_RSS" => "Y",
			"ALLOW_HTML" => "N",
			"ENABLE_TRACKBACK" => "N",
			"SEARCH_INDEX" => "Y",
			"USE_SOCNET" => "Y",
			"=DATE_CREATE" => $DB->CurrentTimeFunction(),
			"PERMS_POST" => Array(
				1 => "I",
				2 => "I" ),
			"PERMS_COMMENT" => Array(
				1 => "P",
				2 => "P" ),
		);

		$bRights = false;
		$rsUser = CUser::GetByID($userId);
		$arUser = $rsUser->Fetch();
		if($arUser["NAME"]."".$arUser["LAST_NAME"] == '')
		{
			$arFields["NAME"] = GetMessage("SNBPA_BLOG_NAME")." ".$arUser["LOGIN"];
		}
		else
		{
			$arFields["NAME"] = GetMessage("SNBPA_BLOG_NAME")." ".$arUser["NAME"]." ".$arUser["LAST_NAME"];
		}

		$arFields["URL"] = str_replace(" ", "_", $arUser["LOGIN"])."-blog-".$siteId;
		$arFields["OWNER_ID"] = $userId;

		$urlCheck = preg_replace("/[^a-zA-Z0-9_-]/is", "", $arFields["URL"]);
		if ($urlCheck != $arFields["URL"])
		{
			$arFields["URL"] = "u".$userId."-blog-".$siteId;
		}

		if(CBlog::GetByUrl($arFields["URL"]))
		{
			$uind = 0;
			do
			{
				$uind++;
				$arFields["URL"] = $arFields["URL"].$uind;
			}
			while (CBlog::GetByUrl($arFields["URL"]));
		}

		$featureOperationPerms = CSocNetFeaturesPerms::GetOperationPerm(SONET_ENTITY_USER, $arFields["OWNER_ID"], "blog", "view_post");
		if ($featureOperationPerms == SONET_RELATIONS_TYPE_ALL)
		{
			$bRights = true;
		}

		$blogID = CBlog::Add($arFields);
		BXClearCache(true, "/blog/form/blog/");
		if ($bRights)
		{
			CBlog::AddSocnetRead($blogID);
		}

		return CBlog::GetByID($blogID, $blogGroupId);
	}

	public static function ValidateProperties($arTestProperties = array(), CBPWorkflowTemplateUser $user = null)
	{
		$errors = [];
		if (empty($arTestProperties["PostMessage"]))
		{
			$errors[] = [
				"code" => "NotExist",
				"parameter" => "GroupName",
				"message" => GetMessage("SNBPA_EMPTY_POST_MESSAGE"),
			];
		}
		if (empty($arTestProperties["OwnerId"]))
		{
			$errors[] = [
				"code" => "NotExist",
				"parameter" => "OwnerId",
				"message" => GetMessage("SNBPA_EMPTY_OWNER"),
			];
		}

		$ownerId = $arTestProperties["OwnerId"] ?? null;
		if ($user && $ownerId && $ownerId !== $user->getBizprocId() && !$user->isAdmin())
		{
			$errors[] = [
				"code" => "NotExist",
				"parameter" => "OwnerId",
				"message" => GetMessage("SNBPA_OWNER_DENIED"),
			];
		}

		if (empty($arTestProperties["UsersTo"]))
		{
			$errors[] = [
				"code" => "NotExist",
				"parameter" => "UsersTo",
				"message" => GetMessage("SNBPA_EMPTY_USERS"),
			];
		}

		return array_merge($errors, parent::ValidateProperties($arTestProperties, $user));
	}

	public static function GetPropertiesDialog($documentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues = null, $formName = "")
	{
		$dialog = new \Bitrix\Bizproc\Activity\PropertiesDialog(__FILE__, array(
			'documentType' => $documentType,
			'activityName' => $activityName,
			'workflowTemplate' => $arWorkflowTemplate,
			'workflowParameters' => $arWorkflowParameters,
			'workflowVariables' => $arWorkflowVariables,
			'currentValues' => $arCurrentValues
		));

		$user = new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser);

		$dialog->setMap(static::getPropertiesMap($documentType, ['user' => $user]));

		$dialog->setRuntimeData(array(
			'user' => $user
		));

		return $dialog;
	}

	public static function GetPropertiesDialogValues($documentType, $activityName, &$arWorkflowTemplate, &$arWorkflowParameters, &$arWorkflowVariables, $arCurrentValues, &$arErrors)
	{
		$arErrors = array();

		$arMap = array(
			"owner_id" => "OwnerId",
			"users_to" => "UsersTo",
			"post_title" => "PostTitle",
			"post_message" => "PostMessage",
			'post_site' => "PostSite",
		);

		$arProperties = array();
		foreach ($arMap as $key => $value)
		{
			if ($key == "owner_id" || $key == "users_to")
				continue;
			$arProperties[$value] = $arCurrentValues[$key];
		}

		if ($arProperties["PostSite"] == '')
			$arProperties["PostSite"] = $arCurrentValues["post_site_x"];

		$user = new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser);
		if ($user->isAdmin())
		{
			$arProperties["OwnerId"] = CBPHelper::UsersStringToArray($arCurrentValues["owner_id"], $documentType, $arErrors);
			if (count($arErrors) > 0)
				return false;
		}
		else
		{
			$arProperties["OwnerId"] = $user->getBizprocId();
		}

		$arProperties["UsersTo"] = CBPHelper::UsersStringToArray($arCurrentValues["users_to"], $documentType, $arErrors);
		if (count($arErrors) > 0)
			return false;

		$arErrors = self::ValidateProperties($arProperties, new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser));
		if (count($arErrors) > 0)
			return false;

		$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$arCurrentActivity["Properties"] = $arProperties;

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

		return [
			'PostTitle' => [
				'Name' => GetMessage("SNBPA_POST_TITLE"),
				'Description' => GetMessage("SNBPA_POST_TITLE"),
				'FieldName' => 'post_title',
				'Type' => 'string'
			],
			'PostMessage' => [
				'Name' => GetMessage("SNBPA_POST_MESSAGE"),
				'Description' => GetMessage("SNBPA_POST_MESSAGE"),
				'FieldName' => 'post_message',
				'Type' => 'text',
				'Required' => true
			],
			'OwnerId' => [
				'Name' => GetMessage("SNBPA_OWNER_ID"),
				'FieldName' => 'owner_id',
				'Type' => 'user',
				'Required' => true,
				'Default' => $user->getBizprocId()
			],
			'UsersTo' => [
				'Name' => GetMessage("SNBPA_USERS_TO"),
				'FieldName' => 'users_to',
				'Type' => 'user',
				'Required' => true,
				'Multiple' => true,
				'Default' => \Bitrix\Bizproc\Automation\Helper::getResponsibleUserExpression($documentType),
			],
			'PostSite' => [
				'Name' => GetMessage("SNBPA_POST_SITE"),
				'FieldName' => 'post_site',
				'Type' => 'select',
				'Options' => $sites
			]
		];
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
			'propertyName' => GetMessage("SNBPA_POST_MESSAGE"),
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
}
