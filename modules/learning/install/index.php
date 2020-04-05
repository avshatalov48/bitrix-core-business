<?php
global $MESS;
$strPath2Lang = str_replace("\\", "/", __FILE__);
$strPath2Lang = substr($strPath2Lang, 0, strlen($strPath2Lang)-strlen("/install/index.php"));
include(GetLangFileName($strPath2Lang."/lang/", "/install/index.php"));


Class learning extends CModule
{
	var $MODULE_ID = "learning";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $errors;
	
	public $MODULE_GROUP_RIGHTS = 'Y';

	function learning()
	{
		$arModuleVersion = array();

		$path = str_replace("\\", "/", __FILE__);
		$path = substr($path, 0, strlen($path) - strlen("/index.php"));
		include($path."/version.php");

		if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion))
		{
			$this->MODULE_VERSION = $arModuleVersion["VERSION"];
			$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		}
		else
		{
			$this->MODULE_VERSION = LEARNING_VERSION;
			$this->MODULE_VERSION_DATE = LEARNING_VERSION_DATE;
		}

		$this->MODULE_NAME = GetMessage("LEARNING_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("LEARNING_MODULE_DESC");
	}

	function InstallDB($arParams = array())
	{
		global $DB, $DBType, $APPLICATION;
		$this->errors = false;

		if (is_object($GLOBALS['CACHE_MANAGER']))
		{
			$GLOBALS['CACHE_MANAGER']->CleanDir('/learning/LessonTreeComponent/');
			$GLOBALS['CACHE_MANAGER']->CleanDir('/learning/coursetolessonmap/');
			$GLOBALS['CACHE_MANAGER']->CleanDir('/learning/');
		}

		// Database tables creation
		// was:		if(!$DB->Query("SELECT 'x' FROM b_learn_course WHERE 1=0", true))
		if ( ! $DB->TableExists('b_learn_lesson') )
		{
			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/install/db/".strtolower($DB->type)."/install.sql");

			if ($this->errors === false)
			{
				$rc = self::InitializeNewRightsModel();
				if ($rc === false)
					$this->errors = 'FATAL: new rights model init failed';
			}

			// Mark that DB converted to new format
			COption::SetOptionString(
				'learning', 
				'~LearnInstall201203ConvertDB::_IsAlreadyConverted', 
				1	// STATUS_INSTALL_COMPLETE
			);
		}

		if($this->errors !== false)
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
				$sites = CLang::GetList($by, $order, Array("ACTIVE"=>"Y"));
				while($site = $sites->Fetch())
				{
					$path = "/learning/";
					if($_REQUEST["copy_".$site["LID"]] == "Y" && !empty($_REQUEST["path_".$site["LID"]]))
					{
						$path = $DB->ForSql($_REQUEST["path_".$site["LID"]]);
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


	function UnInstallDB($arParams = array())
	{
		global $DB, $DBType, $APPLICATION;
		$this->errors = false;

		if(!array_key_exists("savedata", $arParams) || $arParams["savedata"] != "Y")
		{
			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/install/db/".strtolower($DB->type)."/uninstall.sql");

			// remove module permissions data
			self::_RightsModelPurge();
		}

		//delete agents
		CAgent::RemoveModuleAgents("learning");

		if (CModule::IncludeModule("search"))
			CSearch::DeleteIndex("learning");

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

		if($this->errors !== false)
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
		$rs = $DB->Query("SELECT count(*) C FROM b_event_type WHERE EVENT_NAME IN (".$sIn.") ", false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$ar = $rs->Fetch();
		if($ar["C"] <= 0)
		{
			include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/install/events/set_events.php");
		}
		return true;
	}

	function UnInstallEvents()
	{
		global $DB;
		include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/install/events/del_events.php");
		return true;
	}

	function InstallFiles($arParams = array())
	{
		global $DB;

		if($_ENV["COMPUTERNAME"]!='BX')
		{
			//Admin files
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin", false);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/install/images", $_SERVER["DOCUMENT_ROOT"]."/bitrix/images/learning", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/install/public/js", $_SERVER["DOCUMENT_ROOT"]."/bitrix/js", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/install/js", $_SERVER["DOCUMENT_ROOT"]."/bitrix/js", true, true);

			//Theme
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/install/themes", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/install/components", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components", True, True);
		}
		//copy public scripts
		$arSITE_ID = Array();
		$sites = CLang::GetList($by, $order, Array("ACTIVE"=>"Y"));
		while($site = $sites->Fetch())
		{
			if($_REQUEST["copy_".$site["LID"]] == "Y" && !empty($_REQUEST["path_".$site["LID"]]))
			{
				$arSITE_ID[] = $site["LID"];
				$DOC_ROOT = (strlen($site["DOC_ROOT"])<=0) ? $_SERVER["DOCUMENT_ROOT"] : $site["DOC_ROOT"];

				$ldir = $site['LANGUAGE_ID'] == 'ru' ? 'ru' : 'en';

				CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/install/public/site/$ldir/", $DOC_ROOT.$_REQUEST["path_".$site["LID"]], true,true);
			}
		}
		
		if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/templates/learning/"))
		{
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/install/public/template/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/templates/learning/", true, true);
		}

		if(!empty($arSITE_ID))
		{
			if (strlen($_REQUEST["template_id"])<=0)
				$_REQUEST["template_id"] = "learning";

			//Copy Template
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/install/public/template/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/templates/".$_REQUEST["template_id"]."/", true, true);

			foreach($arSITE_ID as $SITE_ID)
			{
				$path = $_REQUEST["path_".$SITE_ID];
				if (strlen($path)<=0)
					continue;

				if(substr($path,-1,1)!="/")
					$path .= "/";

				$cond = "CSite::InDir('".$path."course/')";

				$DB->Query(
				"INSERT INTO b_site_template(SITE_ID, ".CMain::__GetConditionFName().", SORT, TEMPLATE) ".
				"VALUES('".$DB->ForSQL($SITE_ID)."', '".$DB->ForSQL($cond, 255)."', '100', '".$DB->ForSQL(trim($_REQUEST["template_id"]), 255)."')", true);
			}
		}

		return true;
	}

	function UnInstallFiles()
	{
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/install/admin/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/install/themes/.default/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes/.default");//css
		DeleteDirFilesEx("/bitrix/themes/.default/icons/learning/");//icons
		DeleteDirFilesEx("/bitrix/images/learning/");//images
		DeleteDirFilesEx("/bitrix/js/learning/");//scripts
		return true;
	}

	function DoInstall()
	{
		global $DB, $DOCUMENT_ROOT, $APPLICATION, $step;
		$step = IntVal($step);
		if($step < 2)
		{
			$APPLICATION->IncludeAdminFile(GetMessage("LEARNING_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/install/step1.php");
		}
		elseif($step==2)
		{
			$this->InstallFiles();
			$this->InstallDB();
			self::_AddConvertDbNotify();
			$GLOBALS["errors"] = $this->errors;
			$APPLICATION->IncludeAdminFile(GetMessage("LEARNING_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/install/step2.php");
		}
	}

	function DoUninstall()
	{
		global $DB, $DOCUMENT_ROOT, $APPLICATION, $step;
		$step = IntVal($step);
		if($step < 2)
		{
			$APPLICATION->IncludeAdminFile(GetMessage("LEARNING_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/install/unstep1.php");
		}
		elseif($step == 2)
		{
			$this->UnInstallDB(array(
					"savedata" => $_REQUEST["savedata"],
			));
			$this->UnInstallFiles();
			self::_RemoveConvertDbNotify();
			$GLOBALS["errors"] = $this->errors;
			$APPLICATION->IncludeAdminFile(GetMessage("LEARNING_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/install/unstep2.php");
		}
	}

	function GetModuleRightList()
	{
		$arr = array(
			"reference_id" => array("D", "W"),
			"reference" => array(
					"[D] ".GetMessage("LEARNING_PERM_ADMIN_D"),
					"[W] ".GetMessage("LEARNING_PERM_ADMIN_W")
				)
			);
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
		if ( $DB->TableExists('b_learn_lesson') )
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
					CAdminNotify::Add(
						array(
							'MESSAGE' => str_replace(
								'#LANG#', 
								LANGUAGE_ID, 
								GetMessage('LEARNING_ADMIN_NOTIFY_CONVERT_DB')
							),
							'TAG'          => 'learning_convert_11_5_0',
							'MODULE_ID'    => 'learning',
							'ENABLE_CLOSE' => 'N'
						)
					);
				}
			}
		}
	}


	protected static function _RightsModelPurge()
	{
		global $DB;

		$arQueries = array(
			"DELETE FROM b_task_operation 
			WHERE TASK_ID IN (SELECT ID FROM b_task WHERE MODULE_ID = 'learning')
				OR OPERATION_ID IN (SELECT ID FROM b_operation WHERE MODULE_ID = 'learning')",

			"DELETE FROM b_operation
			WHERE MODULE_ID = 'learning'",

			"DELETE FROM b_task
			WHERE MODULE_ID = 'learning'"
			);

		foreach ($arQueries as $key => $query)
		{
			$rc = $DB->Query($query, true);		// ignore_errors = true
			if ($rc === false)
				throw new Exception ('EA_SQLERROR in query #' . $key);
		}
	}


	protected static function _RightsModelCreateTasksAndRelation($arOperationsInDB)
	{
		global $DB;

		$arDefaultRights = array (
			'learning_lesson_access_read'        => 'G2',	// All users (includes not authorized users)
			'learning_lesson_access_manage_dual' => 'CR',	// Author
			'learning_lesson_access_manage_full' => 'G1'	// Admins
			);

		$arTasksOperations = self::_RightsModelGetTasksWithOperations();

		foreach ($arTasksOperations as $taskName => $arOperationsForTask)
		{
			if (substr($taskName, 0, 16) === 'learning_lesson_')
				$binding = 'lesson';
			else
				$binding = 'module';

			$arFields = array(
				'NAME'        => "'" . $DB->ForSql($taskName) . "'",
				'LETTER'      => 'NULL',
				'MODULE_ID'   => "'learning'",
				'SYS'         => "'Y'",
				'DESCRIPTION' => 'NULL',
				'BINDING'     => "'" . $binding . "'"
				);

			$taskId = $DB->Insert(
				'b_task',
				$arFields,
				"",		// $error_position
				false,	// $debug
				"",		// $exist_id
				false	// don't ignore errors, due to the bug in Database::Insert (it don't checks Query return status)
				);

			if ($taskId === false)
				throw new Exception();

			// Create relation for every operation per task
			foreach ($arOperationsForTask as $operationName)
			{
				if ( ! isset($arOperationsInDB[$operationName]) )
					throw new Exception();

				$operationId = (int) $arOperationsInDB[$operationName];

				$rc = $DB->Query(
					"INSERT INTO b_task_operation (TASK_ID, OPERATION_ID) 
					VALUES (" . (int) $taskId . ", " . (int) $operationId . ")", true);

				if ($rc === false)
					throw new Exception();
			}

			// Add default rights for this task, if it exists
			if ( array_key_exists($taskName, $arDefaultRights) )
			{
				$rc = $DB->Query (
					"INSERT INTO b_learn_rights_all (SUBJECT_ID, TASK_ID) 
					VALUES ('" . $DB->ForSQL($arDefaultRights[$taskName]) . "', " . (int) $taskId . ")",
					true);

				if ($rc === false)
					throw new Exception();
			}
		}
	}


	protected static function _RightsModelGetTasksWithOperations()
	{
		$arTasksOperations = array(
			'learning_lesson_access_denied'            => array(),
			'learning_lesson_access_read'              => array(
				'lesson_read'
				),
			'learning_lesson_access_manage_basic'      => array(
				'lesson_read', 
				'lesson_create',
				'lesson_write', 
				'lesson_remove'
				),
			'learning_lesson_access_linkage_as_child'  => array(
				'lesson_read', 
				'lesson_link_to_parents', 
				'lesson_unlink_from_parents'
				),
			'learning_lesson_access_linkage_as_parent' => array(
				'lesson_read', 
				'lesson_link_descendants',
				'lesson_unlink_descendants'
				),
			'learning_lesson_access_linkage_any'       => array(
				'lesson_read', 
				'lesson_link_to_parents', 
				'lesson_unlink_from_parents',
				'lesson_link_descendants',
				'lesson_unlink_descendants'
				),
			'learning_lesson_access_manage_as_child'   => array(
				'lesson_read', 
				'lesson_create',
				'lesson_write', 
				'lesson_remove',
				'lesson_link_to_parents', 
				'lesson_unlink_from_parents'
				),
			'learning_lesson_access_manage_as_parent'  => array(
				'lesson_read', 
				'lesson_create',
				'lesson_write', 
				'lesson_remove',
				'lesson_link_descendants',
				'lesson_unlink_descendants'
				),
			'learning_lesson_access_manage_dual'       => array(
				'lesson_read', 
				'lesson_create',
				'lesson_write', 
				'lesson_remove',
				'lesson_link_to_parents', 
				'lesson_unlink_from_parents',
				'lesson_link_descendants',
				'lesson_unlink_descendants'
				),
			'learning_lesson_access_manage_full'       => array(
				'lesson_read', 
				'lesson_create',
				'lesson_write', 
				'lesson_remove',
				'lesson_link_to_parents', 
				'lesson_unlink_from_parents',
				'lesson_link_descendants',
				'lesson_unlink_descendants',
				'lesson_manage_rights'
				)			
			);

		return ($arTasksOperations);
	}


	protected static function _RightsModelGetAllOperations()
	{
		$arAllOperations = array(
			'lesson_read',
			'lesson_create',
			'lesson_write',
			'lesson_remove',
			'lesson_link_to_parents',
			'lesson_unlink_from_parents',
			'lesson_link_descendants',
			'lesson_unlink_descendants',
			'lesson_manage_rights'
			);

		return ($arAllOperations);
	}


	protected static function _RightsModelCreateOperations()
	{
		global $DB;

		$arAllOperations = self::_RightsModelGetAllOperations();

		$arOperationsInDB = array();

		foreach ($arAllOperations as $operationName)
		{
			if (substr($operationName, 0, 7) === 'lesson_')
				$binding = 'lesson';
			else
				$binding = 'module';

			$arFields = array(
				'NAME'        => "'" . $DB->ForSql($operationName) . "'",
				'MODULE_ID'   => "'learning'",
				'DESCRIPTION' => 'NULL',
				'BINDING'     => "'" . $binding . "'"
				);

			$id = $DB->Insert(
					'b_operation',
					$arFields,
					"",		// $error_position
					false,	// $debug
					"",		// $exist_id
					false	// don't ignore errors, due to the bug in Database::Insert (it don't checks Query return status)
				);

			if ($id === false)
				throw new Exception();

			$arOperationsInDB[$operationName] = $id;
		}

		return ($arOperationsInDB);
	}


	protected static function InitializeNewRightsModel()
	{
		try
		{
			// Clean up learning module operations and tasks (if exists)
			self::_RightsModelPurge();

			$arOperationsInDB = self::_RightsModelCreateOperations();

			self::_RightsModelCreateTasksAndRelation($arOperationsInDB);
		}
		catch (Exception $e)
		{
			return (false);
		}

		return (true);
	}
}
