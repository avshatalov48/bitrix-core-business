<?php
if ( ! CModule::IncludeModule('learning') )
	return (false);

if (!($USER->GetID() > 0))
	return (false);

IncludeModuleLangFile(__FILE__);
$arSubMenu = $arSubCourse = Array();

function __learning_admin_get_menu ($THIS, $arPath = array(), $deep = 0, &$immediateChildsIds, $loadOnlySpecialEntities = false)
{
	$immediateChildsIds = array();	// puts here ids of all immediate childs
	$deep = (int) $deep;

	// Index in path for current parentLessonId
	$indexInPath = $deep - 1;	// Path doesn't include top root element ('all courses' or 'all unilessons')
	$arMenu = array();

	//$urlPath = $oPath->ExportUrlencoded();

	// current lesson id (not exists only for top root)
	if (isset($arPath[$deep]))
		$currentLessonIdInPath = (int) $arPath[$deep];
	else
		$currentLessonIdInPath = false;

	// Path to current element
	$arCurrentDeepPath = array();
	foreach ($arPath as $key => $value)
	{
		if ($key === $deep)
			break;

		$arCurrentDeepPath[] = $value;
	}

	if ($deep === 0)
	{
		// We are at the top level, so we must get list of all courses
		$CDBResult = CCourse::GetList (array('COURSE_SORT' => 'ASC'));
	}
	else
	{
		// If not parent with such indexInPath => we are deep too much.
		if ( ! isset($arPath[$indexInPath]) )
			return ($arMenu);	// no items

		$parentLessonId = $arPath[$indexInPath];

		$oCurrentDeepPath = new CLearnPath();
		$oCurrentDeepPath->SetPathFromArray(array_merge($arCurrentDeepPath));
		$urlPath = $oCurrentDeepPath->ExportUrlencoded();

		$arMenu[] = array(
			"text" => GetMessage("LEARNING_QUESTION"),
			"url" => "learn_question_admin.php?lang=" . LANG
				. '&PARENT_LESSON_ID=' . ($parentLessonId + 0)
				. '&LESSON_PATH=' . $urlPath
				. "&from=learn_menu",
			"icon" => "learning_icon_question",
			//"page_icon" => "learning_page_icon_question",
			"more_url" =>
				array(
					"learn_question_admin.php?lang=" . LANG
						. '&PARENT_LESSON_ID=' . ($parentLessonId + 0)
						. '&LESSON_PATH=' . $urlPath
						. "&from=learn_menu",
					"learn_question_edit.php?lang=" . LANG
						. '&LESSON_PATH=' . $urlPath
						. "&from=learn_menu",
					"learn_question_admin.php?lang=" . LANG
						. '&PARENT_LESSON_ID=' . ($parentLessonId + 0)
						. '&LESSON_PATH=' . $urlPath,
					"learn_question_edit.php?lang=" . LANG
						. '&LESSON_PATH=' . $urlPath,
					"learn_question_admin.php?lang=" . LANG
						. '&LESSON_PATH=' . $urlPath,
				),
			"title" => GetMessage("LEARNING_QUESTION_LIST"),
		);

		$CDBResult = CLearnLesson::GetListOfImmediateChilds (
						$parentLessonId,
						array('EDGE_SORT' => 'ASC')
		);

		// determine, is parent a course (only for courses in subroot level)?
		if ($deep === 1)
		{
			$immediateParentCourse = CLearnLesson::GetLinkedCourse ($parentLessonId);
			if ($immediateParentCourse !== false)
			{
				// immediate parent is a course, so we must add entity 'Tests'
				$arMenu[] = array(
					'text'      => GetMessage('LEARNING_TESTS'),
					'url'       => 'learn_test_admin.php?lang=' . LANG
						. '&filter=Y&set_filter=Y'
						. '&COURSE_ID=' . ($immediateParentCourse + 0)
						. '&PARENT_LESSON_ID=' . ($parentLessonId + 0)
						. '&LESSON_PATH=' . $urlPath,
					'icon'      => 'learning_icon_tests',
					//'page_icon' => 'learning_page_icon_tests',

					'more_url'  =>
						array(
							'learn_test_admin.php?lang=' . LANG
								. '&set_filter=Y'
								. '&COURSE_ID=' . ($immediateParentCourse + 0)
								. '&PARENT_LESSON_ID=' . ($parentLessonId + 0)
								. '&LESSON_PATH=' . $urlPath,
							'learn_test_edit.php?lang=' . LANG
								. '&filter=Y&set_filter=Y'
								. '&COURSE_ID=' . ($immediateParentCourse + 0)
								. '&PARENT_LESSON_ID=' . ($parentLessonId + 0)
								. '&LESSON_PATH=' . $urlPath
						),
					'title' => GetMessage('LEARNING_TESTS_LIST'),
				);

				unset ($urlPath);
				unset ($oCurrentDeepPath);
			}
		}
	}

	if ($loadOnlySpecialEntities)
		return ($arMenu);

	// When listing courses, limit it's count
	if ($deep === 0)
	{
		$items = 0;
		$learning_menu_max_courses = (int) COption::GetOptionString("learning", "menu_max_courses", "10");
	}
	while ($arData = $CDBResult->GetNext())
	{
		// When listing courses, limit it's count
		if ($deep === 0)
		{
			if ($items >= $learning_menu_max_courses)
			{
				// add element 'other courses'
				$arMenu[] = array(
					'text'     => GetMessage('LEARNING_MENU_COURSES_OTHER'),
					'url'      => 'learn_unilesson_admin.php?lang=' . LANG . '&PARENT_LESSON_ID=-1',
					'title'    => GetMessage('LEARNING_MENU_COURSES_ALT'),
					'more_url' => array(
						'learn_test_admin.php',
						'learn_test_edit.php',
						'learn_unilesson_admin.php',
						'learn_unilesson_edit.php',
						'learn_question_admin.php',
						'learn_question_edit.php'
						)
					);

				// stop adding courses to menu
				break;
			}

			$items++;
		}

		$arSubImmediateChildsIds = false;
		$arCurItemPath   = $arCurrentDeepPath;
		$arCurItemPath[] = ($arData['LESSON_ID'] + 0);

		// Remember all immediate childs
		$immediateChildsIds[] = ($arData['LESSON_ID'] + 0);

		$oCurItemPath = new CLearnPath();
		$oCurItemPath->SetPathFromArray ($arCurItemPath);

		$urlCurItemPath = $oCurItemPath->ExportUrlencoded();

		$arItem = array(
			'text'      => $arData['NAME'],
			'url'       => 'learn_unilesson_admin.php?lang=' . LANG
				. '&PARENT_LESSON_ID=' . ($arData['LESSON_ID'] + 0)
				. '&LESSON_PATH=' . $oCurItemPath->ExportUrlencoded(),
			'title'     => $arData['NAME'],
			'items_id'  => 'menu_learning_courses_new_' . implode ('_', $arCurItemPath),
			"icon"      => "learning_icon_courses",
			'module_id' => 'learning',
			'more_url'  => array(
				'learn_unilesson_admin.php?lang=' . LANG
					. '&set_filter=Y'
					. '&PARENT_LESSON_ID=' . ($arData['LESSON_ID'] + 0)
					. '&LESSON_PATH=' . $urlCurItemPath,
				'learn_unilesson_admin.php?lang=' . LANG
					. '&PARENT_LESSON_ID=' . ($arData['LESSON_ID'] + 0)
					. '&LESSON_PATH=' . $urlCurItemPath,
				'learn_unilesson_admin.php?lang=' . LANG
					. '&set_filter=Y'
					. '&LESSON_PATH=' . $urlCurItemPath,
				'learn_question_admin.php?lang=' . LANG
					. '&filter=Y&set_filter=Y'
					. '&LESSON_PATH=' . $urlCurItemPath,
				'learn_question_edit.php?lang=' . LANG
					. '&set_filter=Y'
					. '&LESSON_PATH=' . $urlCurItemPath,
				'learn_unilesson_edit.php?lang=' . LANG
					. '&LESSON_ID=' . ($arData['LESSON_ID'] + 0)
					. '&LESSON_PATH=' . $urlCurItemPath,
				'learn_unilesson_edit.php?lang=' . LANG
					. '&PARENT_LESSON_ID=' . ($arData['LESSON_ID'] + 0)
					. '&LESSON_PATH=' . $urlCurItemPath
				)
			);

		$arItem['items'] = array();

		if ( ( $deep === 0) && isset($arData['LINKED_LESSON_ID']) && ($arData['LINKED_LESSON_ID'] > 0 ) )
		{
			$arItem['page_icon'] = $arItem['icon'] = 'learning_icon_courses';
			$arItem['dynamic']   = true;

			$loadOnlySpecialEntities = false;

			// Load child items only for lesson in current path
			if ($arData['LESSON_ID'] == $currentLessonIdInPath)
				$arItem['items'] = __learning_admin_get_menu ($THIS, $arPath, $deep + 1, $arSubImmediateChildsIds);
		}
		else
		{
			$loadOnlySpecialEntities = false;
			$childsCnt    = CLearnLesson::CountImmediateChilds($arData['LESSON_ID']);
			$questionsCnt = CLQuestion::GetCount(array('LESSON_ID' => (int) $arData['LESSON_ID']));

			if ($childsCnt > 0)
				$arItem['page_icon'] = $arItem['icon'] = 'learning_icon_chapters';
			else
				$arItem['page_icon'] = $arItem['icon'] = 'learning_icon_lessons';

			$arItem['dynamic'] = true;

			// Load child items only for lesson in current path
			if ($arData['LESSON_ID'] == $currentLessonIdInPath)
				$arItem['items'] = __learning_admin_get_menu ($THIS, $arPath, $deep + 1, $arSubImmediateChildsIds);
		}

		// now, adds items into more_url (it needs when edit form for childs opened)
		if (is_array($arSubImmediateChildsIds))
		{
			$oSubItemPath = new CLearnPath();
			foreach ($arSubImmediateChildsIds as $k => $v)
			{
				// determine path for every child
				$arSubItemPath   = $arCurItemPath;
				$arSubItemPath[] = $v;			// child id added to current path
				$oSubItemPath->SetPathFromArray ($arSubItemPath);

				$arItem['more_url'][] =
					'learn_unilesson_edit.php?lang=' . LANG
					. '&filter=Y&set_filter=Y'
					. '&LESSON_ID=' . ($v + 0)
					. '&LESSON_PATH=' . $oSubItemPath->ExportUrlencoded();
			}
			unset ($oSubItemPath);

			$arSubImmediateChildsIds = false;
		}

		$arMenu[] = $arItem;
		unset($oCurItemPath, $arCurItemPath, $urlCurItemPath, $arItem);
	}

	return ($arMenu);
}

$oAccess = CLearnAccess::GetInstance($USER->GetID());

$module_id = "learning";

if (\CLearnAccessMacroses::CanViewAdminMenu())
{
	// Try to determine current path
	$oPath = new CLearnPath();
	if (isset($_GET['LESSON_PATH']))
		$oPath->ImportUrlencoded ($_GET['LESSON_PATH']);
	elseif (isset($_GET['admin_mnu_module_id'])
		&& isset($_GET['admin_mnu_menu_id'])
		&& ($_GET['admin_mnu_module_id'] === 'learning')
	)
	{
		$strLessonIds = substr($_GET['admin_mnu_menu_id'], strlen('menu_learning_courses_new_'));
		if (strlen($strLessonIds) > 0)
		{
			$arLessonIds = explode('_', $strLessonIds);
			if ( is_array($arLessonIds) && (count($arLessonIds) > 0) )
				$oPath->SetPathFromArray($arLessonIds);
		}
	}

	$arPath  = $oPath->GetPathAsArray();
	$urlPath = $oPath->ExportUrlencoded();

	$arSubImmediateChildsIds = null;

	$aMenu = array(
		"parent_menu" => "global_menu_services",
		"section" => "learning",
		"sort" => 600,
		"text" => GetMessage("LEARNING_MENU_LEARNING"),
		"title" => GetMessage("LEARNING_MENU_LEARNING_TITLE"),
		"icon" => "learning_menu_icon",
		"page_icon" => "learning_page_icon",
		"items_id" => "menu_learning",
		//"url" => "learn_index.php?lang=" . LANG,
		"items" =>
		Array(
			// Courses in new data model
			array(
				"text"      => GetMessage("LEARNING_MENU_COURSES"),
				"url"       => "learn_unilesson_admin.php?lang=" . LANG
					. '&PARENT_LESSON_ID=-1',	// '-1' means "List courses"
				"title"     => GetMessage("LEARNING_MENU_COURSES_ALT"),
				"items_id"  => "menu_learning_courses_new",
				"icon"      => "learning_icon_courses",
				//"page_icon" => "learning_page_icon_courses",
				"dynamic"   => true,
				"module_id" => "learning",
				"more_url"  =>
					Array(
						"learn_course_edit.php",
					),
				"items"     => __learning_admin_get_menu($this, $arPath, $deep = 0, $arSubImmediateChildsIds)	// Build menu from the top
			),

			// List any lessons
			array(
				"text"      => GetMessage("LEARNING_LESSONS_LIST"),		// "List of all lessons"
				"url"       => "learn_unilesson_admin.php?lang=" . LANG
					. '&set_filter=Y'
					. '&PARENT_LESSON_ID=-2',	// '-2' means "List any lessons"
				"title"     => GetMessage("LEARNING_LESSONS_LIST"),		// "List of all lessons"
				"items_id"  => "menu_learning_any_lessons36",
				"icon"      => "learning_icon_lessons",
				//"page_icon" => "learning_icon_lessons",
				"module_id" => "learning",
				"more_url"  =>
					Array(
						"learn_unilesson_admin.php",
						"learn_unilesson_edit.php",
					),
				"dynamic"   => false,
				"items"     => array()
			),

			//Certification
			array(
				"text" => GetMessage("LEARNING_MENU_CERTIFICATION"),
				"url" => "learn_certification_admin.php?lang=".LANG,
				"title" => GetMessage("LEARNING_MENU_CERTIFICATION_ALT"),
				"items_id" => "menu_learning_certification",
				"icon" => "learning_icon_certification",
				"page_icon" => "learning_page_icon_certification",
				"more_url" =>
					Array(
						"learn_certification_edit.php",
					),
			),

			//Gradebook
			array(
				"text" => GetMessage("LEARNING_MENU_GRADEBOOK"),
				"url" => "learn_gradebook_admin.php?lang=".LANG,
				"title" => GetMessage("LEARNING_MENU_GRADEBOOK_ALT"),
				"items_id" => "menu_learning_gradebook",
				"icon" => "learning_icon_gradebook",
				"page_icon" => "learning_page_icon_gradebook",
				"more_url" =>
					Array(
						"learn_gradebook_edit.php",
					),
			),

			//Attempts
			array(
				"text" => GetMessage("LEARNING_MENU_ATTEMPT"),
				"url" => "learn_attempt_admin.php?lang=".LANG,
				"title" => GetMessage("LEARNING_MENU_ATTEMPT_ALT"),
				"items_id" => "menu_learning_attempt",
				"icon" => "learning_icon_attempts",
				"page_icon" => "learning_page_icon_attempts",
				"more_url" =>
					Array(
						"learn_attempt_edit.php",
						"learn_test_result_edit.php",
						"learn_test_result_admin.php",
					),
			)
		)
	);

	if ($oAccess->IsBaseAccess(CLearnAccess::OP_LESSON_MANAGE_RIGHTS))
	{
		$aMenu['items'][] = array(
			'text'      =>  GetMessage('LEARNING_MENU_GROUPS'),
			'url'       => 'learn_group_admin.php?lang=' . LANG,
			'title'     =>  GetMessage('LEARNING_MENU_GROUPS_ALT'),
			'items_id'  => 'menu_learning_groups',
			'icon'      => 'learning_icon_groups',
			//'page_icon' => 'learning_page_icon_export',
			'more_url'  => array(
				'learn_group_edit.php'
			)
		);
	}

	// Export
	$aMenu['items'][] = array(
		"text"      =>  GetMessage("LEARNING_MENU_EXPORT"),
		"url"       => "learn_export.php?lang=" . LANG,
		"title"     =>  GetMessage("LEARNING_MENU_EXPORT_ALT"),
		"items_id"  => "menu_learning_export",
		"icon"      => "learning_icon_export",
		"page_icon" => "learning_page_icon_export",
		"more_url"  =>  array()
	);

	if ($oAccess->IsBaseAccess(
		CLearnAccess::OP_LESSON_CREATE
		| CLearnAccess::OP_LESSON_LINK_TO_PARENTS
		| CLearnAccess::OP_LESSON_LINK_DESCENDANTS
		)
		||
		(
			$oAccess->IsBaseAccess (CLearnAccess::OP_LESSON_CREATE )
			&& $oAccess->IsBaseAccessForCR (CLearnAccess::OP_LESSON_LINK_TO_PARENTS | CLearnAccess::OP_LESSON_LINK_DESCENDANTS)
		)
	)
	{
		$aMenu["items"][] = Array(
				"text" => GetMessage("LEARNING_MENU_IMPORT"),
				"url" => "learn_import.php?lang=".LANG,
				"title" => GetMessage("LEARNING_MENU_IMPORT_ALT"),
				"items_id" => "menu_learning_export",
				"icon" => "learning_icon_export",
				"page_icon" => "learning_page_icon_export",
				"more_url" =>
					Array(
					),
			);
	}

	if ($USER->IsAdmin() && isset($_GET['easter_egg']))
	{
		$aMenu['items'][] = array(
			'text'      => 'Easter Egg',
			'url'       => 'learn_eegg.php?lang=' . LANG,
			'title'     => 'Utilities',
			'items_id'  => 'menu_learning_eegg_utils',
			//'icon'      => 'util_page_icon',
			'page_icon' => 'util_page_icon',
			'more_url'  =>
				Array(
				),
			);
	}
}
else
{
	define("LEARNING_ADMIN_ACCESS_DENIED","Y");
	return false;
}

return $aMenu;

