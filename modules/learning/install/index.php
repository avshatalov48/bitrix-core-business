<?php

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class learning extends CModule
{
	var $MODULE_ID = "learning";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $errors;

	public $MODULE_GROUP_RIGHTS = 'Y';

	function __construct()
	{
		$arModuleVersion = [];

		include(__DIR__ . '/version.php');

		if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion))
		{
			$this->MODULE_VERSION = $arModuleVersion["VERSION"];
			$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		}

		$this->MODULE_NAME = Loc::getMessage("LEARNING_MODULE_NAME");
		$this->MODULE_DESCRIPTION = Loc::getMessage("LEARNING_MODULE_DESC");
	}

	function InstallDB($arParams = [])
	{
		global $DB, $APPLICATION;
		$connection = \Bitrix\Main\Application::getConnection();
		$this->errors = false;

		if (is_object($GLOBALS['CACHE_MANAGER']))
		{
			$GLOBALS['CACHE_MANAGER']->CleanDir('/learning/LessonTreeComponent/');
			$GLOBALS['CACHE_MANAGER']->CleanDir('/learning/coursetolessonmap/');
			$GLOBALS['CACHE_MANAGER']->CleanDir('/learning/');
		}

		// Database tables creation
		// was:		if(!$DB->Query("SELECT 'x' FROM b_learn_course WHERE 1=0", true))
		if (!$DB->TableExists('b_learn_lesson'))
		{
			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/learning/install/db/" . $connection->getType() . "/install.sql");

			if ($this->errors === false)
			{
				$rc = $this->InitializeNewRightsModel();
				if ($rc === false)
				{
					$this->errors = 'FATAL: new rights model init failed';
				}
			}

			// Mark that DB converted to new format
			COption::SetOptionString(
				'learning',
				'~LearnInstall201203ConvertDB::_IsAlreadyConverted',
				1    // STATUS_INSTALL_COMPLETE
			);
		}

		if ($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode("<br>", $this->errors));
			return false;
		}
		else
		{
			RegisterModule("learning");
			RegisterModuleDependences("main", "OnGroupDelete", "learning", "CCourse", "OnGroupDelete");
			RegisterModuleDependences("main", "OnBeforeLangDelete", "learning", "CCourse", "OnBeforeLangDelete");
			RegisterModuleDependences("main", "OnUserDelete", "learning", "CCourse", "OnUserDelete");
			RegisterModuleDependences("main", "OnSiteDelete", "learning", "CSitePath", "DeleteBySiteID");
			RegisterModuleDependences("search", "OnReindex", "learning", "CCourse", "OnSearchReindex");
			RegisterModuleDependences("main", "OnGetRatingContentOwner", "learning", "CRatingsComponentsLearning", "OnGetRatingContentOwner", 200);
			RegisterModuleDependences("main", "OnAddRatingVote", "learning", "CRatingsComponentsLearning", "OnAddRatingVote", 200);
			RegisterModuleDependences("main", "OnCancelRatingVote", "learning", "CRatingsComponentsLearning", "OnCancelRatingVote", 200);
			RegisterModuleDependences("main", "OnEventLogGetAuditTypes", "learning", "CLearningEvent", "GetAuditTypes");
			RegisterModuleDependences("main", "OnEventLogGetAuditHandlers", "learning", "CLearningEvent", "MakeMainObject");
			RegisterModuleDependences("learning", "OnAfterLearningGroupDelete", "learning", "CLearningGroupMember", "onAfterLearningGroupDelete");
			RegisterModuleDependences("learning", "OnAfterLearningGroupDelete", "learning", "CLearningGroupLesson", "onAfterLearningGroupDelete");

			if ($DB->Query("SELECT 'x' FROM b_learn_site_path WHERE 1=0", true))
			{
				$sites = CLang::GetList('', '', ["ACTIVE" => "Y"]);
				while ($site = $sites->Fetch())
				{
					$path = "/learning/";
					if ($_REQUEST["copy_" . $site["LID"]] == "Y" && !empty($_REQUEST["path_" . $site["LID"]]))
					{
						$path = $DB->ForSql($_REQUEST["path_" . $site["LID"]]);
					}

					$DB->Query(
						"INSERT INTO b_learn_site_path(ID, SITE_ID, PATH,TYPE) 
						VALUES
						(NULL , '" . $site["LID"] . "', '" . $path . "course/index.php?COURSE_ID=#COURSE_ID#&INDEX=Y', 'C'),
						(NULL , '" . $site["LID"] . "', '" . $path . "course/index.php?COURSE_ID=#COURSE_ID#&CHAPTER_ID=#CHAPTER_ID#', 'H'),
						(NULL , '" . $site["LID"] . "', '" . $path . "course/index.php?COURSE_ID=#COURSE_ID#&LESSON_ID=#LESSON_ID#', 'L'),
						(NULL , '" . $site["LID"] . "', '" . $path . "course/index.php?LESSON_PATH=#LESSON_PATH#', 'U')"
						, true);
				}
			}

			return true;
		}
	}

	function UnInstallDB($arParams = [])
	{
		global $DB, $APPLICATION;
		$this->errors = false;

		if (!array_key_exists("savedata", $arParams) || $arParams["savedata"] != "Y")
		{
			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/learning/install/db/mysql/uninstall.sql");

			// remove module permissions data
			$this->UnInstallTasks();
		}

		//delete agents
		CAgent::RemoveModuleAgents("learning");

		if (CModule::IncludeModule("search"))
		{
			CSearch::DeleteIndex("learning");
		}

		UnRegisterModuleDependences("search", "OnReindex", "learning", "CCourse", "OnSearchReindex");
		UnRegisterModuleDependences("main", "OnGroupDelete", "learning", "CCourse", "OnGroupDelete");
		UnRegisterModuleDependences("main", "OnBeforeLangDelete", "learning", "CCourse", "OnBeforeLangDelete");
		UnRegisterModuleDependences("main", "OnUserDelete", "learning", "CCourse", "OnUserDelete");
		UnRegisterModuleDependences("main", "OnGetRatingContentOwner", "learning", "CRatingsComponentsLearning", "OnGetRatingContentOwner");
		UnRegisterModuleDependences("main", "OnAddRatingVote", "learning", "CRatingsComponentsLearning", "OnAddRatingVote");
		UnRegisterModuleDependences("main", "OnCancelRatingVote", "learning", "CRatingsComponentsLearning", "OnCancelRatingVote");
		UnRegisterModuleDependences("main", "OnEventLogGetAuditTypes", "learning", "CLearningEvent", "GetAuditTypes");
		UnRegisterModuleDependences("main", "OnEventLogGetAuditHandlers", "learning", "CLearningEvent", "MakeMainObject");
		UnRegisterModuleDependences("learning", "OnAfterLearningGroupDelete", "learning", "CLearningGroupMember", "onAfterLearningGroupDelete");
		UnRegisterModuleDependences("learning", "OnAfterLearningGroupDelete", "learning", "CLearningGroupLesson", "onAfterLearningGroupDelete");

		UnRegisterModule("learning");

		if ($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode("<br>", $this->errors));
			return false;
		}

		return true;
	}

	function InstallEvents()
	{
		global $DB;

		$sIn = "'NEW_LEARNING_TEXT_ANSWER'";
		$rs = $DB->Query("SELECT count(*) C FROM b_event_type WHERE EVENT_NAME IN (" . $sIn . ") ");
		$ar = $rs->Fetch();
		if ($ar["C"] <= 0)
		{
			$langs = CLanguage::GetList();
			while($lang = $langs->Fetch())
			{
				$lid = $lang["LID"];
				IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/install/events.php", $lid);

				$et = new CEventType;
				$et->Add(array(
					"LID" => $lid,
					"EVENT_NAME" => "NEW_LEARNING_TEXT_ANSWER",
					"NAME" => GetMessage("NEW_LEARNING_TEXT_ANSWER_NAME"),
					"DESCRIPTION" => GetMessage("NEW_LEARNING_TEXT_ANSWER_DESC"),
				));

				$arSites = array();
				$sites = CSite::GetList('', '', Array("LANGUAGE_ID"=>$lid));
				while ($site = $sites->Fetch())
					$arSites[] = $site["LID"];

				if(count($arSites) > 0)
				{

					$emess = new CEventMessage;
					$emess->Add(array(
						"ACTIVE" => "Y",
						"EVENT_NAME" => "NEW_LEARNING_TEXT_ANSWER",
						"LID" => $arSites,
						"EMAIL_FROM" => "#EMAIL_FROM#",
						"EMAIL_TO" => "#EMAIL_TO#",
						"SUBJECT" => GetMessage("NEW_LEARNING_TEXT_ANSWER_SUBJECT"),
						"MESSAGE" => GetMessage("NEW_LEARNING_TEXT_ANSWER_MESSAGE"),
						"BODY_TYPE" => "text",
					));
				}
			}
		}
		return true;
	}

	function UnInstallEvents()
	{
		global $DB;

		$DB->Query("DELETE FROM b_event_type WHERE EVENT_NAME in ('NEW_LEARNING_TEXT_ANSWER')");
		$DB->Query("DELETE FROM b_event_message WHERE EVENT_NAME in ('NEW_LEARNING_TEXT_ANSWER')");

		return true;
	}

	function InstallFiles($arParams = [])
	{
		//Admin files
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/learning/install/admin", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin", false);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/learning/install/images", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/images/learning", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/learning/install/public/js", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/js", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/learning/install/js", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/js", true, true);

		//Theme
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/learning/install/themes", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/themes", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/learning/install/components", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components", true, true);

		//copy public scripts
		$arSITE_ID = [];
		$sites = CLang::GetList('', '', ["ACTIVE" => "Y"]);
		while ($site = $sites->Fetch())
		{
			if ($_REQUEST["copy_" . $site["LID"]] == "Y" && !empty($_REQUEST["path_" . $site["LID"]]))
			{
				$arSITE_ID[] = $site["LID"];
				$DOC_ROOT = ($site["DOC_ROOT"] == '') ? $_SERVER["DOCUMENT_ROOT"] : $site["DOC_ROOT"];

				$ldir = $site['LANGUAGE_ID'] == 'ru' ? 'ru' : 'en';

				CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/learning/install/public/site/$ldir/", $DOC_ROOT . $_REQUEST["path_" . $site["LID"]], true, true);
			}
		}

		if (!file_exists($_SERVER["DOCUMENT_ROOT"] . "/bitrix/templates/learning/"))
		{
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/learning/install/public/template/", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/templates/learning/", true, true);
		}

		if (!empty($arSITE_ID))
		{
			if ($_REQUEST["template_id"] == '')
			{
				$_REQUEST["template_id"] = "learning";
			}

			//Copy Template
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/learning/install/public/template/", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/templates/" . $_REQUEST["template_id"] . "/", true, true);

			foreach ($arSITE_ID as $SITE_ID)
			{
				$path = $_REQUEST["path_" . $SITE_ID];
				if ($path == '')
				{
					continue;
				}

				if (mb_substr($path, -1, 1) != "/")
				{
					$path .= "/";
				}

				$cond = "CSite::InDir('" . $path . "course/')";

				\Bitrix\Main\SiteTemplateTable::add([
					'SITE_ID' => $SITE_ID,
					'CONDITION' => $cond,
					'SORT' => 100,
					'TEMPLATE' => trim($_REQUEST["template_id"]),
				]);
			}
		}

		return true;
	}

	function UnInstallFiles()
	{
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/learning/install/admin/", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin");
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/learning/install/themes/.default/", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/themes/.default");//css
		DeleteDirFilesEx("/bitrix/themes/.default/icons/learning/");//icons
		DeleteDirFilesEx("/bitrix/images/learning/");//images
		DeleteDirFilesEx("/bitrix/js/learning/");//scripts
		return true;
	}

	function DoInstall()
	{
		global $APPLICATION, $step;
		$step = intval($step);
		if ($step < 2)
		{
			$APPLICATION->IncludeAdminFile(Loc::getMessage("LEARNING_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/learning/install/step1.php");
		}
		elseif ($step == 2)
		{
			$this->InstallFiles();
			$this->InstallDB();
			self::_AddConvertDbNotify();
			$GLOBALS["errors"] = $this->errors;
			$APPLICATION->IncludeAdminFile(Loc::getMessage("LEARNING_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/learning/install/step2.php");
		}
	}

	function DoUninstall()
	{
		global $APPLICATION, $step;
		$step = intval($step);
		if ($step < 2)
		{
			$APPLICATION->IncludeAdminFile(Loc::getMessage("LEARNING_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/learning/install/unstep1.php");
		}
		elseif ($step == 2)
		{
			$this->UnInstallDB([
				"savedata" => $_REQUEST["savedata"],
			]);
			$this->UnInstallFiles();
			self::_RemoveConvertDbNotify();
			$GLOBALS["errors"] = $this->errors;
			$APPLICATION->IncludeAdminFile(Loc::getMessage("LEARNING_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/learning/install/unstep2.php");
		}
	}

	function GetModuleRightList()
	{
		$arr = [
			"reference_id" => ["D", "W"],
			"reference" => [
				"[D] " . Loc::getMessage("LEARNING_PERM_ADMIN_D"),
				"[W] " . Loc::getMessage("LEARNING_PERM_ADMIN_W"),
			],
		];
		return $arr;
	}

	protected static function _RemoveConvertDbNotify()
	{
		CAdminNotify::DeleteByTag('learning_convert_11_5_0');
	}

	protected static function _AddConvertDbNotify()
	{
		global $DB;

		// Is module data exists?
		if ($DB->TableExists('b_learn_lesson'))
		{
			// Ensure, that data in database converted to 11.5.0 version of module
			if (COption::GetOptionString(
					'learning',
					'~LearnInstall201203ConvertDB::_IsAlreadyConverted',
					'-9',
					''
				)
				!== '1'
			)
			{
				// Data for module not converted yet, generate message
				if (method_exists('CAdminNotify', 'Add'))
				{
					CAdminNotify::Add([
						'MESSAGE' => str_replace(
							'#LANG#',
							LANGUAGE_ID,
							Loc::getMessage('LEARNING_ADMIN_NOTIFY_CONVERT_DB')
						),
						'TAG' => 'learning_convert_11_5_0',
						'MODULE_ID' => 'learning',
						'ENABLE_CLOSE' => 'N',
					]);
				}
			}
		}
	}

	protected function CreateDefaultRoles()
	{
		global $DB;

		$arDefaultRights = [
			'learning_lesson_access_read' => 'G2',    // All users (includes not authorized users)
			'learning_lesson_access_manage_dual' => 'CR',    // Author
			'learning_lesson_access_manage_full' => 'G1',    // Admins
		];

		$rsDB = \Bitrix\Main\TaskTable::getList([
			'select' => ['ID', 'NAME'],
			'filter' => ['=MODULE_ID' => $this->MODULE_ID, '=NAME' => array_keys($arDefaultRights)],
		]);
		while ($arTask = $rsDB->fetch())
		{
			$rc = $DB->Query(
				"INSERT INTO b_learn_rights_all (SUBJECT_ID, TASK_ID) 
				VALUES ('" . $DB->ForSQL($arDefaultRights[$arTask['NAME']]) . "', " . (int)$arTask['ID'] . ")",
				true
			);

			if ($rc === false)
			{
				throw new Exception();
			}
		}
	}

	public function GetModuleTasks()
	{
		return [
			'learning_lesson_access_denied' => [
				'BINDING' => 'lesson',
				'OPERATIONS' => [],
			],
			'learning_lesson_access_read' => [
				'BINDING' => 'lesson',
				'OPERATIONS' => [
					'lesson_read',
				],
			],
			'learning_lesson_access_manage_basic' => [
				'BINDING' => 'lesson',
				'OPERATIONS' => [
					'lesson_read',
					'lesson_create',
					'lesson_write',
					'lesson_remove',
				],
			],
			'learning_lesson_access_linkage_as_child' => [
				'BINDING' => 'lesson',
				'OPERATIONS' => [
					'lesson_read',
					'lesson_link_to_parents',
					'lesson_unlink_from_parents',
				],
			],
			'learning_lesson_access_linkage_as_parent' => [
				'BINDING' => 'lesson',
				'OPERATIONS' => [
					'lesson_read',
					'lesson_link_descendants',
					'lesson_unlink_descendants',
				],
			],
			'learning_lesson_access_linkage_any' => [
				'BINDING' => 'lesson',
				'OPERATIONS' => [
					'lesson_read',
					'lesson_link_to_parents',
					'lesson_unlink_from_parents',
					'lesson_link_descendants',
					'lesson_unlink_descendants',
				],
			],
			'learning_lesson_access_manage_as_child' => [
				'BINDING' => 'lesson',
				'OPERATIONS' => [
					'lesson_read',
					'lesson_create',
					'lesson_write',
					'lesson_remove',
					'lesson_link_to_parents',
					'lesson_unlink_from_parents',
				],
			],
			'learning_lesson_access_manage_as_parent' => [
				'BINDING' => 'lesson',
				'OPERATIONS' => [
					'lesson_read',
					'lesson_create',
					'lesson_write',
					'lesson_remove',
					'lesson_link_descendants',
					'lesson_unlink_descendants',
				],
			],
			'learning_lesson_access_manage_dual' => [
				'BINDING' => 'lesson',
				'OPERATIONS' => [
					'lesson_read',
					'lesson_create',
					'lesson_write',
					'lesson_remove',
					'lesson_link_to_parents',
					'lesson_unlink_from_parents',
					'lesson_link_descendants',
					'lesson_unlink_descendants',
				],
			],
			'learning_lesson_access_manage_full' => [
				'BINDING' => 'lesson',
				'OPERATIONS' => [
					'lesson_read',
					'lesson_create',
					'lesson_write',
					'lesson_remove',
					'lesson_link_to_parents',
					'lesson_unlink_from_parents',
					'lesson_link_descendants',
					'lesson_unlink_descendants',
					'lesson_manage_rights',
				],
			],
		];
	}

	protected function InitializeNewRightsModel()
	{
		try
		{
			// Clean up learning module operations and tasks (if exists)
			$this->UnInstallTasks();

			$this->InstallTasks();

			$this->CreateDefaultRoles();
		}
		catch (Exception)
		{
			return (false);
		}

		return (true);
	}
}
