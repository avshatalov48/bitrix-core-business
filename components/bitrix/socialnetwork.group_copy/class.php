<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Blog\Copy\Integration\Group as BlogFeature;
use Bitrix\Disk\Copy\Integration\Group as DiskFeature;
use Bitrix\Landing\Copy\Integration\Group as LandingFeature;
use Bitrix\Lists\Copy\Integration\Group as ListsFeature;
use Bitrix\Main\AccessDeniedException;
use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Config\Configuration;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Context;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Error;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorableImplementation;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\SystemException;
use Bitrix\Main\Text\Emoji;
use Bitrix\Photogallery\Copy\Integration\Group as PhotoFeature;
use Bitrix\Socialnetwork\Component\WorkgroupForm;
use Bitrix\Socialnetwork\Copy\GroupManager;
use Bitrix\Socialnetwork\Item\Workgroup;
use Bitrix\Socialnetwork\WorkgroupTable;
use Bitrix\Tasks\Copy\Integration\Group as TasksFeature;

class SocialnetworkGroupCopy extends CBitrixComponent implements Controllerable, Errorable
{
	use ErrorableImplementation;

	public function configureActions()
	{
		return [];
	}

	protected function listKeysSignedParameters()
	{
		return [
			"PATH_TO_GROUP",
		];
	}

	public function onIncludeComponentLang()
	{
		$this->includeComponentLang(basename(__FILE__));
		Loc::loadMessages(__FILE__);
	}

	public function onPrepareComponentParams($params)
	{
		$this->errorCollection = new ErrorCollection();

		$params["GROUP_ID"] = (int) ($params["GROUP_ID"] ?? 0);

		return $params;
	}

	public function executeComponent()
	{
		try
		{
			$this->checkModules();

			if (!$this->isFeatureEnabled())
			{
				$this->includeComponentTemplate('limit');

				return;
			}

			$executiveUserId = $this->getUser()->getID();

			if ($executiveUserId)
			{
				$this->checkAccess($executiveUserId, $this->arParams["GROUP_ID"]);
			}

			$this->arParams["IS_PROJECT"] = $this->isProject($this->arParams["GROUP_ID"]);

			$this->setResult();
			$this->setTitle();

			$this->includeComponentTemplate();
		}
		catch (SystemException $exception)
		{
			ShowError($exception->getMessage());
		}
	}

	public function copyGroupAction()
	{
		$this->checkModules();

		if (!$this->isFeatureEnabled())
		{
			return null;
		}

		$request = Context::getCurrent()->getRequest();
		$post = $request->getPostList()->toArray();

		$this->checkRequiredCreationParams($post);
		if ($this->hasErrors())
		{
			return null;
		}

		$executiveUserId = $this->getUser()->getID();

		$groupId = $post["id"];
		$groupIdsToCopy = [$groupId];

		$this->checkAccess($executiveUserId, $groupId);

		$features = $this->getFeaturesFromRequest($post["features"]);

		$copyManager = new GroupManager($executiveUserId, $groupIdsToCopy);
		$this->setFeatures($copyManager, $executiveUserId, $features, $post);
		$copyManager->setProjectTerm($this->getProjectTerm($post));

		$changedFields = $this->getChangedFields($post);
		$copyManager->setChangedFields($changedFields);

		$result = $copyManager->startCopy();

		if ($result->getErrors())
		{
			$this->errorCollection->set($result->getErrors());
			return null;
		}

		return $this->getUrlToCopiedGroup($copyManager, $groupId);
	}

	private function isFeatureEnabled(): bool
	{
		return \Bitrix\Socialnetwork\Helper\Workgroup::isGroupCopyFeatureEnabled();
	}

	private function setFeatures(GroupManager $copyManager, $executiveUserId, array $features, array $post)
	{
		if (!array_key_exists("users", $features))
		{
			$copyManager->setMarkerUsers(false);
		}
		if (array_key_exists("tasks", $features) && Loader::includeModule("tasks"))
		{
			$copyManager->setFeature(new TasksFeature($executiveUserId, $features["tasks"]));
		}
		if (array_key_exists("files", $features) && Loader::includeModule("disk"))
		{
			$copyManager->setFeature(new DiskFeature($executiveUserId, $features["files"]));
		}
		if (array_key_exists("group_lists", $features) && Loader::includeModule("lists"))
		{
			$features["group_lists"][] = "field";
			$copyManager->setFeature(new ListsFeature($executiveUserId, $features["group_lists"]));
		}
		if (array_key_exists("blog", $features) && Loader::includeModule("blog"))
		{
			$copyManager->setFeature(new BlogFeature($executiveUserId, $features["blog"]));
		}
		if (array_key_exists("photo", $features) && Loader::includeModule("photogallery"))
		{
			$copyManager->setFeature(new PhotoFeature($executiveUserId, $features["photo"]));
		}
		if (array_key_exists("landing_knowledge", $features) && Loader::includeModule("landing"))
		{
			$copyManager->setFeature(new LandingFeature($executiveUserId));
		}

		$ufIgnoreList = $this->getUfIgnoreList($features);

		$copyManager->setUfIgnoreList($ufIgnoreList);
	}

	/**
	 * @throws SystemException
	 */
	private function checkModules()
	{
		try
		{
			if (!Loader::includeModule("socialnetwork"))
			{
				throw new SystemException("Module \"socialnetwork\" not found");
			}
		}
		catch (LoaderException $exception)
		{
			throw new SystemException("System error");
		}
	}

	/**
	 * @param int $userId
	 * @param int $groupId
	 * @throws AccessDeniedException
	 */
	private function checkAccess(int $userId, int $groupId)
	{
		try
		{
			$group = CSocNetGroup::getByID($groupId);
			if ($group && $group['ACTIVE'] === 'Y')
			{
				$currentUserPerms = \Bitrix\Socialnetwork\Helper\Workgroup::getPermissions([
					'userId' => $userId,
					'groupId' => $groupId,
				]);

				if (!$currentUserPerms["UserCanModifyGroup"])
				{
					throw new AccessDeniedException();
				}
			}
			else
			{
				throw new AccessDeniedException();
			}
		}
		catch (Exception $exception)
		{
			throw new AccessDeniedException();
		}
	}

	private function setTitle()
	{
		global $APPLICATION;

		$name = Loc::getMessage("SOCNET_GROUP_COPY_TITLE_BASE");
		if ($this->arParams["GROUP_ID"])
		{
			$name = (
				$this->arParams['IS_PROJECT']
					? Loc::getMessage('SOCNET_GROUP_COPY_TITLE_BASE_PROJECT')
					: Loc::getMessage('SOCNET_GROUP_COPY_TITLE_BASE_GROUP')
			);
		}

		$this->arResult['PageTitle'] = $name;
		$APPLICATION->SetTitle($name);
	}

	private function setResult()
	{
		$this->arResult = [];

		$this->arResult["INTRANET_INSTALLED"] = ModuleManager::isModuleInstalled("intranet");
		$this->arResult["EXTRANET_INSTALLED"] = $this->isExtranetInstalled();
		$this->arResult["EXTRANET"] = ($this->isExtranet() ? "Y" : "N");
		$this->arResult["LANDING_INSTALLED"] = ModuleManager::isModuleInstalled("landing");

		$this->arResult["CURRENT_USER_ID"] = $this->getUser()->getID();
		$this->arResult["GROUP_ID"] = $this->arParams["GROUP_ID"];
		$this->arResult["IS_PROJECT"] = $this->arParams["IS_PROJECT"] ? "Y" : "N";
		$this->arResult["LIST"] = $this->getListToGroupSelector();
		$this->arResult["GROUP"] = $this->getGroupData();
		$this->arResult["IS_EXTRANET_GROUP"] = (($this->arResult["GROUP"]["IS_EXTRANET_GROUP"] == "Y") ? "Y" : "N");
	}

	private function checkRequiredCreationParams($post)
	{
		$fieldName = null;

		if ($post["name"] == '')
		{
			$fieldName = "name";
		}
		if (!intval($post["id"]))
		{
			$fieldName = "groupId";
		}

		if ($fieldName)
		{
			$this->errorCollection->setError(new Error("The \"".$fieldName."\" field is required"));
		}
	}

	private function isProject($groupId)
	{
		$group = Workgroup::getById($groupId);
		return $group->isProject();
	}

	private function getListToGroupSelector()
	{
		$list = [];

		if ($this->arParams["GROUP_ID"])
		{
			$list[] = "SG".$this->arParams["GROUP_ID"];
		}

		return $list;
	}

	private function getUser()
	{
		global $USER;
		return $USER;
	}

	private function getFeatures($groupId)
	{
		$whiteList = [
			"tasks" => [
				"checklists" => [
					"Title" => $this->getFeatureTitle("checklists"),
					"Name" => "checklists",
				],
				"comments" => [
					"Title" => $this->getFeatureTitle("comments"),
					"Name" => "comments",
				],
				"robots" => [
					"Title" => $this->getFeatureTitle("robots"),
					"Name" => "robots",
				]
			],
			"files" => [
				"onlyFolders" => [
					"Title" => $this->getFeatureTitle("only_Folders"),
					"Name" => "onlyFolders",
					"Checked" => false
				]
			],
			"group_lists" => [
				"section" => [
					"Title" => $this->getFeatureTitle("section"),
					"Name" => "section",
				],
				"element" => [
					"Title" => $this->getFeatureTitle("element"),
					"Name" => "element",
				],
				"workflow" => [
					"Title" => $this->getFeatureTitle("workflow"),
					"Name" => "workflow",
				]
			],
			"blog" => [
				"comments" => [
					"Title" => $this->getFeatureTitle("comments"),
					"Name" => "comments",
				],
				/*"voteResult" => [
					"Title" => $this->getFeatureTitle("vote_result"),
					"Name" => "voteResult",
					"Checked" => false
				]*/
			],
			"photo" => [],
			"landing_knowledge" => [],
		];

		$group = Workgroup::getById($groupId);
		if ($group && $group->isScrumProject())
		{
			unset($whiteList['tasks']);
		}

		$features = [];
		WorkgroupForm::processWorkgroupFeatures($groupId, $features);

		$result = [
			"users" => [
				"Active" => true,
				"Title" => $this->getFeatureTitle("users"),
				"Name" => "users",
				"Children" => [
					"departments" => [
						"Title" => $this->getFeatureTitle("departments"),
						"Name" => "departments",
					]
				]
			]
		];
		foreach ($features as $featureId => $feature)
		{
			if (array_key_exists($featureId, $whiteList) && !empty($feature["Active"]))
			{
				$feature["Title"] = $this->getFeatureTitle($featureId);
				$feature["Name"] = $featureId;
				$feature["Children"] = $whiteList[$featureId];
				$result[$featureId] = $feature;
			}
		}

		return $result;
	}

	private function getFeatureTitle($featureId)
	{
		$featureId = mb_strtoupper($featureId);
		if (
			$featureId === 'BLOG'
			&& ModuleManager::isModuleInstalled('intranet')
		)
		{
			$featureId = 'BLOG2';
		}
		return Loc::getMessage("SOCNET_GROUP_COPY_FEATURE_".$featureId);
	}

	private function getFeaturesFromRequest(array $features)
	{
		$featuresToCopy = [];

		foreach ($features as $featureId => $feature)
		{
			if (isset($feature["active"]) && $feature["active"] == "Y")
			{
				unset($feature["active"]);

				$featuresToCopy[$featureId] = [];

				foreach ($feature as $featureName => $marker)
				{
					if ($marker == "Y")
					{
						$featuresToCopy[$featureId][] = $featureName;
					}
				}
			}
		}

		return $featuresToCopy;
	}

	private function getExceptionErrorMessage(\Exception $exception)
	{
		$message = $exception->getMessage();
		if (is_array($message))
		{
			if (array_key_exists("message", $message))
			{
				$message = $message["message"];
			}
			else
			{
				$message = "System error";
			}
		}
		return $message;
	}

	private function getUrlToCopiedGroup(GroupManager $copyManager, int $groupId): string
	{
		$mapIdsCopiedGroups = $copyManager->getMapIdsCopiedGroups();

		$copiedGroupId = 0;
		if (array_key_exists($groupId, $mapIdsCopiedGroups))
		{
			$copiedGroupId = $mapIdsCopiedGroups[$groupId];
		}

		return CComponentEngine::makePathFromTemplate($this->arParams["PATH_TO_GROUP"], ["group_id" => $copiedGroupId]);
	}

	private function getUfIgnoreList(array $features)
	{
		$ufIgnoreList = [
			"UF_SG_DEPT"
		];

		if (!empty($features["users"]) && in_array("departments", $features["users"]))
		{
			$currentPos = array_search("UF_SG_DEPT", $ufIgnoreList);
			if ($currentPos !== false)
			{
				unset($ufIgnoreList[$currentPos]);
			}
		}

		return $ufIgnoreList;
	}

	private function setErrors(Exception $exception)
	{
		if ($message = $this->getExceptionErrorMessage($exception))
		{
			$this->errorCollection->setError(new Error($message, $exception->getCode()));
		}
		else
		{
			$this->errorCollection->setError(new Error("System error", $exception->getCode()));
		}
		return null;
	}

	private function getGroupData()
	{
		$groupData = [
			"ID" => $this->arResult["GROUP_ID"],
			"GROUP_PROPERTIES" => [],
			"SUBJECTS" => [],
			"SPAM_PERMS" => [],
			"MODERATOR_IDS" => []
		];

		WorkgroupForm::processWorkgroupData($groupData["ID"], $groupData["GROUP_PROPERTIES"], $groupData, "edit");

		$groupData["SUBJECTS"] = $this->getSubjects();

		$groupData["LIST_INITIATE_PERMS"] = $this->getInitiatePerms();

		$groupData["SPAM_PERMS"] = $this->getSpamPerms();

		$groupData["FEATURES"] = $this->getFeatures($this->arResult["GROUP_ID"]);

		return $groupData;
	}

	private function getSubjects()
	{
		$subjects = [];
		$queryObject = CSocNetGroupSubject::getList(
			["SORT"=>"ASC", "NAME" => "ASC"],
			["SITE_ID" => $this->getSiteId()],
			false,
			false,
			["ID", "NAME"]
		);
		while ($subject = $queryObject->getNext())
		{
			$subjects[$subject["ID"]] = $subject["NAME"];
		}

		return $subjects;
	}

	private function getInitiatePerms(): array
	{
		return [
			"group" => Workgroup::getInitiatePermOptionsList(),
			"project" => Workgroup::getInitiatePermOptionsList(["project" => true])
		];
	}

	private function getSpamPerms(): array
	{
		return Workgroup::getSpamPermOptionsList();
	}

	private function isExtranet()
	{
		return (
			$this->isExtranetInstalled() &&
			Loader::includeModule("extranet") &&
			CExtranet::isExtranetSite()
		);
	}

	private function isExtranetInstalled(): bool
	{
		return (
			ModuleManager::isModuleInstalled("intranet") &&
			ModuleManager::isModuleInstalled("extranet") &&
			!empty(Option::get("extranet", "extranet_site"))
		);
	}

	private function getChangedFields(array $post)
	{
		//todo check $post isset keys

		$changedFields = [
			"NAME" => $post["name"],
			"DESCRIPTION" => $post["description"],
			"VISIBLE" => ($post["visible"] == "Y" ? "Y" : "N"),
			"OPENED" => ($post["opened"] == "Y" ? "Y" : "N"),
			"CLOSED" => ($post["closed"] == "Y" ? "Y" : "N"),
			"IS_EXTRANET_GROUP" => ($post["extranet_group"] == "Y" ? "Y" : "N"),
			"SUBJECT_ID" => $post["subject_id"],
			"KEYWORDS" => $post["keywords"],
			"INITIATE_PERMS" => $post["initiate_perms"],
			"SPAM_PERMS" => $post["spam_perms"] ?? null,
			"PROJECT" => ($post["project"] == "Y" ? "Y" : "N"),
			"LANDING" => ($post["landing"] == "Y" ? "Y" : "N"),
			"OWNER_ID" => $post["owner_id"],
			"MODERATORS" => $post["moderators"],
			"IMAGE_ID" => null,
		];

		if(Configuration::getValue("utf_mode") === true)
		{
			$connection = Application::getConnection();
			$table = WorkgroupTable::getTableName();

			if ($changedFields["NAME"] <> "")
			{
				if (!$connection->isUtf8mb4($table, "NAME"))
				{
					$changedFields["NAME"] = Emoji::encode($changedFields["NAME"]);
				}
			}

			if ($changedFields["DESCRIPTION"] <> "")
			{
				if (!$connection->isUtf8mb4($table, "DESCRIPTION"))
				{
					$changedFields["DESCRIPTION"] = Emoji::encode($changedFields["DESCRIPTION"]);
				}
			}
		}

		if ($post["image_id"])
		{
			$imageId = CFile::makeFileArray($post["image_id"]);
			//$arImageID["old_file"] = $arResult["POST"]["IMAGE_ID"]; todo
			$imageId["del"] = "N";
			CFile::ResizeImage($imageId, array("width" => 300, "height" => 300), BX_RESIZE_IMAGE_PROPORTIONAL);
			$changedFields["IMAGE_ID"] = $imageId;
		}

		if ($this->isExtranet())
		{
			$siteIds = [];
			$queryObject = WorkgroupTable::getList([
				"filter" => [
					"GROUP_ID" => $post["id"]
				],
				"select" => ["SITE_ID"]
			]);
			while ($fields = $queryObject->fetch())
			{
				$siteIds[] = $fields["SITE_ID"];
			}
			$siteIds[] = $this->getSiteId();

			$siteIds = array_unique($siteIds);
			if (!empty($siteIds))
			{
				$changedFields["SITE_ID"] = $siteIds;
			}
		}
		else
		{
			$changedFields["SITE_ID"] = [$this->getSiteId()];
			if (
				Loader::includeModule("extranet") &&
				!CExtranet::isExtranetSite() &&
				($post["is_extranet_group"] ?? null) == "Y"
			)
			{
				$changedFields["SITE_ID"][] = CExtranet::getExtranetSiteID();
				$changedFields["VISIBLE"] = "N";
				$changedFields["OPENED"] = "N";
			}
		}

		return $changedFields;
	}

	/**
	 * @param array $post
	 * @return array|mixed
	 * @throws ArgumentException
	 */
	private function getProjectTerm(array $post)
	{
		$projectTerm = [
			"project" => ($post["project"] == "Y")
		];
		if (!empty($post["start_point"]))
		{
			if (!$this->checkDateFormat($post["start_point"]))
			{
				throw new ArgumentException(Loc::getMessage("SOCNET_GROUP_COPY_DATE_FORMAT_ERROR"));
			}
			$projectTerm["start_point"] = $post["start_point"];
			return $projectTerm;
		}
		else
		{
			if (
				!$this->checkDateFormat($post["project_term"]["start_point"]) ||
				(!empty($post["project_term"]["end_point"]) &&
					!$this->checkDateFormat($post["project_term"]["end_point"])))
			{
				throw new ArgumentException(Loc::getMessage("SOCNET_GROUP_COPY_DATE_FORMAT_ERROR"));
			}
			$projectTerm = array_merge($projectTerm, $post["project_term"]);
			return $projectTerm;
		}
	}

	private function checkDateFormat($date)
	{
		$phpDateFormat = Bitrix\Main\Type\DateTime::convertFormatToPhp(FORMAT_DATE);
		return Bitrix\Main\Type\DateTime::isCorrect($date, $phpDateFormat);
	}
}