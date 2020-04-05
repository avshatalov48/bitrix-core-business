<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

//Module
if (!CModule::IncludeModule("learning"))
{
	ShowError(GetMessage("LEARNING_MODULE_NOT_FOUND"));
	return;
}

try
{

//Params
$arParams["CHECK_PERMISSIONS"] = (isset($arParams["CHECK_PERMISSIONS"]) && $arParams["CHECK_PERMISSIONS"]=="N" ? "N" : "Y");
$arParams["COURSE_ID"] = (isset($arParams["COURSE_ID"]) && intval($arParams["COURSE_ID"]) > 0 ? intval($arParams["COURSE_ID"]) : intval($_REQUEST["COURSE_ID"]));
$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y" );

$arParams["PAGE_WINDOW"] = (isset($arParams["PAGE_WINDOW"]) && intval($arParams["PAGE_WINDOW"]) > 0 ? intval($arParams["PAGE_WINDOW"]) : "10");
$arParams["SHOW_TIME_LIMIT"] = (isset($arParams["SHOW_TIME_LIMIT"]) && $arParams["SHOW_TIME_LIMIT"] == "N" ? "N" : "Y");
$arParams["TESTS_PER_PAGE"] = (intval($arParams["TESTS_PER_PAGE"]) > 0 ? intval($arParams["TESTS_PER_PAGE"]) : 20);

if ( ! ( isset($arParams['LESSON_PATH']) && strlen($arParams['LESSON_PATH']) ) )
{
	$arParams['LESSON_PATH'] = '';

	if (isset($_REQUEST['LESSON_PATH']) && strlen($_REQUEST['LESSON_PATH']))
		$arParams['LESSON_PATH'] = $_REQUEST['LESSON_PATH'];
}

if (strlen($arParams["PAGE_NUMBER_VARIABLE"]) <=0 || !preg_match("#^[A-Za-z_][A-Za-z01-9_]*$#", $arParams["PAGE_NUMBER_VARIABLE"]))
	$arParams["PAGE_NUMBER_VARIABLE"] = "PAGE";

$arComponentVariables = Array(
	"COURSE_ID",
	"INDEX",
	"LESSON_ID",
	"LESSON_PATH",
	"CHAPTER_ID",
	"SELF_TEST_ID",
	"TEST_ID",
	"TYPE",
	"TEST_LIST",
	"GRADEBOOK",
	"FOR_TEST_ID",
	$arParams["PAGE_NUMBER_VARIABLE"],
);

//PHP converts dots into underscores (php.net/variables.external)
if (is_array($arParams["SEF_URL_TEMPLATES"]))
{
	foreach ($arParams["SEF_URL_TEMPLATES"] as $pageCode => $pageTemplate)
	{
		$newPageCode = str_replace("_", ".", $pageCode);
		$arParams["SEF_URL_TEMPLATES"][$newPageCode] = $pageTemplate;
		if ($newPageCode !== $pageCode)
		{
			unset($arParams["SEF_URL_TEMPLATES"][$pageCode]);
		}
	}
}

if ($arParams["SEF_MODE"] == "Y")
{
	$arDefaultUrlTemplates404 = array(
		"course.detail" => "course#COURSE_ID#/index",
		"lesson.detail" => "course#COURSE_ID#/lesson#LESSON_ID#/",
		"chapter.detail" => "course#COURSE_ID#/chapter#CHAPTER_ID#/",
		"test.self" => "course#COURSE_ID#/selftest#SELF_TEST_ID#/",
		"test" => "course#COURSE_ID#/test#TEST_ID#/",
		"test.list" => "course#COURSE_ID#/examination/",
		"course.contents" => "course#COURSE_ID#/contents/",
		"gradebook" => "course#COURSE_ID#/gradebook/",
		"search" => "course#COURSE_ID#/search/",
	);

	$arDefaultVariableAliases404 = Array(
		"course.detail" => Array("COURSE_ID" => "COURSE_ID"),
		"lesson.detail" => Array("LESSON_ID" => "LESSON_ID","COURSE_ID" => "COURSE_ID"),
		"chapter.detail" => Array("CHAPTER_ID" => "CHAPTER_ID", "COURSE_ID" => "COURSE_ID"),
		"test.self" => Array("SELF_TEST_ID" => "SELF_TEST_ID", "COURSE_ID" => "COURSE_ID"),
		"test" => Array("TEST_ID" => "TEST_ID", "COURSE_ID" => "COURSE_ID"),
		"test.list" => Array("COURSE_ID" => "COURSE_ID"),
		"course.contents" => Array("COURSE_ID" => "COURSE_ID"),
		"gradebook" => Array("FOR_TEST_ID" => "FOR_TEST_ID", "COURSE_ID" => "COURSE_ID"),
		"search" => Array("COURSE_ID" => "COURSE_ID"),
	);

	$arUrlTemplates = CComponentEngine::MakeComponentUrlTemplates($arDefaultUrlTemplates404, $arParams["SEF_URL_TEMPLATES"]);
	$componentPage = CComponentEngine::ParseComponentPath(
		$arParams["SEF_FOLDER"],
		$arUrlTemplates,
		$arVariables
	);

	if (isset($arVariables["COURSE_ID"]) && $arParams["COURSE_ID"] <= 0)
		$arParams["COURSE_ID"] = $arVariables["COURSE_ID"];


	if (!$componentPage)
		$componentPage = "course.detail";

	$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases404, $arParams["VARIABLE_ALIASES"]);
	CComponentEngine::InitComponentVariables($componentPage, $arComponentVariables, $arVariableAliases, $arVariables);

	$arResult = Array(
		"FOLDER" => $arParams["SEF_FOLDER"],
		"URL_TEMPLATES" => $arUrlTemplates,
		"VARIABLES" => $arVariables,
		"ALIASES" => $arVariableAliases
	);
}
else
{
	$arDefaultVariableAliases = Array(
		"COURSE_ID" => "COURSE_ID",
		"INDEX" => "INDEX",
		"LESSON_ID" => "LESSON_ID",
		"CHAPTER_ID" => "CHAPTER_ID",
		"SELF_TEST_ID" => "SELF_TEST_ID",
		"TEST_ID" => "TEST_ID",
		"TYPE" => "TYPE",
		"TEST_LIST" => "TEST_LIST",
		"GRADEBOOK" => "GRADEBOOK",
		"SEARCH" => "SEARCH",
		"FOR_TEST_ID" => "FOR_TEST_ID",
		$arParams["PAGE_NUMBER_VARIABLE"] => $arParams["PAGE_NUMBER_VARIABLE"],
	);

	$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases, $arParams["VARIABLE_ALIASES"]);
	CComponentEngine::InitComponentVariables(false, $arComponentVariables, $arVariableAliases, $arVariables);

	$componentPage = "";

	if(isset($arVariables["LESSON_ID"]) && intval($arVariables["LESSON_ID"]) > 0)
		$componentPage = "lesson.detail";
	elseif(isset($arVariables["CHAPTER_ID"]) && intval($arVariables["CHAPTER_ID"]) > 0)
		$componentPage = "chapter.detail";
	elseif(isset($arVariables["TEST_ID"]) && intval($arVariables["TEST_ID"]) > 0)
		$componentPage = "test";
	elseif(isset($arVariables["SELF_TEST_ID"]) && intval($arVariables["SELF_TEST_ID"]) > 0)
		$componentPage = "test.self";
	elseif(isset($arVariables["TYPE"]) && $arVariables["TYPE"] == "Y")
		$componentPage = "course.contents";
	elseif(isset($arVariables["TEST_LIST"]) && $arVariables["TEST_LIST"] == "Y")
		$componentPage = "test.list";
	elseif(isset($arVariables["GRADEBOOK"]) && $arVariables["GRADEBOOK"] == "Y")
		$componentPage = "gradebook";
	elseif(isset($arVariables["SEARCH"]) && $arVariables["SEARCH"] == "Y")
		$componentPage = "search";
	else
		$componentPage = "course.detail";

	$currentPage = GetPagePath(false, false);
	$queryString= htmlspecialcharsbx(DeleteParam(array_values($arVariableAliases)));
	$currentPage .= "?";

	$arResult = array(
		"FOLDER" => "",
		"URL_TEMPLATES" => Array(
			"course.detail" => $currentPage.$arVariableAliases["COURSE_ID"]."=#COURSE_ID#&".$arVariableAliases["INDEX"]."=Y",
			"course.contents" => $currentPage.$arVariableAliases["COURSE_ID"]."=".$arParams["COURSE_ID"]."&".$arVariableAliases["TYPE"]."=Y",
			"lesson.detail" => $currentPage.$arVariableAliases["COURSE_ID"]."=".$arParams["COURSE_ID"]."&".$arVariableAliases["LESSON_ID"]."=#LESSON_ID#",
			"chapter.detail" => $currentPage.$arVariableAliases["COURSE_ID"]."=".$arParams["COURSE_ID"]."&".$arVariableAliases["CHAPTER_ID"]."=#CHAPTER_ID#",
			"test" => $currentPage.$arVariableAliases["COURSE_ID"]."=#COURSE_ID#&".$arVariableAliases["TEST_ID"]."=#TEST_ID#",
			"test.list" => $currentPage.$arVariableAliases["COURSE_ID"]."=".$arParams["COURSE_ID"]."&".$arVariableAliases["TEST_LIST"]."=Y",
			"test.self" => $currentPage.$arVariableAliases["COURSE_ID"]."=".$arParams["COURSE_ID"]."&".$arVariableAliases["SELF_TEST_ID"]."=#LESSON_ID#",
			"gradebook" => $currentPage.$arVariableAliases["COURSE_ID"]."=".$arParams["COURSE_ID"]."&".$arVariableAliases["GRADEBOOK"]."=Y",
			"search" => $currentPage.$arVariableAliases["COURSE_ID"]."=".$arParams["COURSE_ID"]."&".$arVariableAliases["SEARCH"]."=Y",
		),
		"VARIABLES" => $arVariables,
		"ALIASES" => $arVariableAliases
	);

}

//Page properties
$APPLICATION->SetPageProperty("learning_course_contents_url", str_replace("#COURSE_ID#", $arParams["COURSE_ID"], $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["course.contents"]));
$APPLICATION->SetPageProperty("learning_test_list_url", str_replace("#COURSE_ID#", $arParams["COURSE_ID"], $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["test.list"]));
$APPLICATION->SetPageProperty("learning_gradebook_url", str_replace("#COURSE_ID#", $arParams["COURSE_ID"], $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["gradebook"]));
$arSearchURL = parse_url(str_replace("#COURSE_ID#", $arParams["COURSE_ID"], $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["search"]));
$APPLICATION->SetPageProperty("learning_search_url", htmlspecialcharsEx($arSearchURL["path"]));
$searchParams = "";
if ($arSearchURL["query"])
{
	foreach(explode("&", $arSearchURL["query"]) as $param)
	{
		list($name, $value) = explode("=", $param);
		$searchParams .= "<input type=\"hidden\" name=\"".htmlspecialcharsEx($name)."\" value=\"".htmlspecialcharsEx($value)."\" />";
	}
}
$APPLICATION->SetPageProperty("learning_search_params", $searchParams);

$lessonID = 0;
if ($arVariables["LESSON_ID"] > 0)
{
	$lessonID = intval($arVariables["LESSON_ID"]);
}
elseif (isset($arVariables['CHAPTER_ID']))
{
	// Lesson is not given, so try get chapter_id
	if (CLearnPath::IsUrlencodedPath($arVariables['CHAPTER_ID']))
	{
		$LESSON_PATH = new CLearnPath();
		$LESSON_PATH->ImportUrlencoded($arVariables['CHAPTER_ID']);
		$lessonID = (int) $LESSON_PATH->GetBottom();
	}
	elseif (substr($arVariables['CHAPTER_ID'], 0, 1) === '0')
	{
		$lessonID = (int) substr($arVariables['CHAPTER_ID'], 1);
	}
	else
	{
		$lessonID = (int) CLearnLesson::LessonIdByChapterId($arVariables['CHAPTER_ID']);
	}
}

$linkedLessonId = false;
if ($arParams["COURSE_ID"] > 0)
{
	$linkedLessonId = CCourse::CourseGetLinkedLesson ($arParams["COURSE_ID"]);
}

if ($arParams["CHECK_PERMISSIONS"] !== 'N')
{
	$isAccessible = false;
	try
	{
		if ($lessonID > 0)
		{
			$arPermissionsParams = array(
				'COURSE_ID' => $arParams['COURSE_ID'],
				'LESSON_ID' => $lessonID
			);

			$isAccessible = CLearnAccessMacroses::CanUserViewLessonAsPublic ($arPermissionsParams, $allowAccessViaLearningGroups = false);
		}
		elseif ($linkedLessonId !== false)
		{
			$arPermissionsParams = array(
				'lesson_id' => $linkedLessonId
			);

			$isAccessible = CLearnAccessMacroses::CanUserViewLessonContent ($arPermissionsParams, $allowAccessViaLearningGroups = false);
		}
	}
	catch (Exception $e)
	{
		$isAccessible = false;	// access denied
	}

	$arResult['LEARNING_GROUP_ACTIVE_FROM'] = false;
	$arResult['LEARNING_GROUP_ACTIVE_TO']   = false;
	$arResult['LEARNING_GROUP_CHAPTERS_ACTIVE_FROM'] = false;

	if ( ! $isAccessible )
	{
		if ($linkedLessonId !== false)
		{
			$arGroupsPeriods = CLearnAccessMacroses::getActiveLearningGroupsPeriod($linkedLessonId, $USER->getId());

			if ($arGroupsPeriods['IS_EXISTS'])
			{
				$isAccessible = true;
				$arResult['LEARNING_GROUP_ACTIVE_FROM'] = $arGroupsPeriods['ACTIVE_FROM'];
				$arResult['LEARNING_GROUP_ACTIVE_TO'] = $arGroupsPeriods['ACTIVE_TO'];

				$activeFromMap = CLearnAccessMacroses::getActiveLearningChaptersPeriod($linkedLessonId, $USER->getId());
				if ($activeFromMap !== false)
				{
					$arResult['LEARNING_GROUP_CHAPTERS_ACTIVE_FROM'] = $activeFromMap;
				}
			}
		}
	}

	if ( ! $isAccessible )
	{
		if ($lessonID > 0)
		{
			$courseDetailUrl = CComponentEngine::MakePathFromTemplate(
				$arResult["FOLDER"].$arResult["URL_TEMPLATES"]["course.detail"],
				array("COURSE_ID" => $arParams["COURSE_ID"])
			);

			LocalRedirect($courseDetailUrl);
		}

		ShowError(GetMessage('LEARNING_COURSE_DENIED'));
		return;
	}
}

if ($arParams['SEF_MODE'] === 'Y')
{
	$addReturnUrl = array(
		"lesson" => CComponentEngine::MakePathFromTemplate($arParams['~SEF_FOLDER'] . $arResult["URL_TEMPLATES"]["lesson.detail"], Array("COURSE_ID" => $arParams["COURSE_ID"])),
		"test" => CComponentEngine::MakePathFromTemplate($arParams['~SEF_FOLDER'] . $arResult["URL_TEMPLATES"]["test"], Array("COURSE_ID" => $arParams["COURSE_ID"])),
		"course" => str_replace("COURSE_ID=".$arParams["COURSE_ID"], "COURSE_ID=#COURSE_ID#", $arParams['~SEF_FOLDER'] . $arResult["URL_TEMPLATES"]["course.detail"]),
	);
}
else
{
	$addReturnUrl = array(
		"lesson" => CComponentEngine::MakePathFromTemplate($arResult["URL_TEMPLATES"]["lesson.detail"], Array("COURSE_ID" => $arParams["COURSE_ID"])),
		"test" => CComponentEngine::MakePathFromTemplate($arResult["URL_TEMPLATES"]["test"], Array("COURSE_ID" => $arParams["COURSE_ID"])),
		"course" => str_replace("COURSE_ID=".$arParams["COURSE_ID"], "COURSE_ID=#COURSE_ID#", $arResult["URL_TEMPLATES"]["course.detail"]),
	);
}

$addReturnUrl["lesson"] .= strpos($addReturnUrl["lesson"], "?") !== false ? "&" : "?";
$addReturnUrl["lesson"] .= "LESSON_PATH=#LESSON_PATH#";

$contextLessonId = $lessonID;

// If lessonId not determined and there is course id known - use it
if (($contextLessonId == 0) && ($linkedLessonId !== false))
	$contextLessonId = $linkedLessonId;

$arMenuButtons = array();

if ($contextLessonId > 0)
{
	if (CLearnAccessMacroses::CanUserAddLessonToParentLesson(array('parent_lesson_id' => $contextLessonId)))
	{
		$arMenuButtons[] = array(
			"TEXT" => GetMessage("LEARNING_COURSES_LESSON_ADD"),
			"TITLE" => GetMessage("LEARNING_COURSES_LESSON_ADD"),
			"URL" => 'javascript:'.$APPLICATION->GetPopupLink(
				array(
					"URL" => "/bitrix/admin/learn_unilesson_edit.php?lang=" . LANGUAGE_ID
						. "&PARENT_LESSON_ID=" . $contextLessonId
						. "&LESSON_PATH=" . urlencode($arParams["LESSON_PATH"])
						. "&bxpublic=Y&from_module=learning&return_url=" . urlencode($addReturnUrl["lesson"]),
					"PARAMS" => array(
						"width" => 700, 'height' => 500, 'resize' => false,
					),
				)
			),
			"ICON" => "bx-context-toolbar-create-icon",
			"ID" => "bx-context-toolbar-create-lesson"
		);

		$arMenuButtons[] = array(
			"TEXT" => GetMessage("LEARNING_COURSES_CHAPTER_ADD"),
			"TITLE" => GetMessage("LEARNING_COURSES_CHAPTER_ADD"),
			"URL" => 'javascript:'.$APPLICATION->GetPopupLink(
				array(
					"URL" => "/bitrix/admin/learn_unilesson_edit.php?lang=" . LANGUAGE_ID
						. "&PARENT_LESSON_ID=" . $contextLessonId
						. "&LESSON_PATH=" . urlencode($arParams["LESSON_PATH"])
						. "&bxpublic=Y&from_module=learning&return_url=" . urlencode($addReturnUrl["lesson"]),
					"PARAMS" => array(
						"width" => 700, 'height' => 500, 'resize' => false,
					),
				)
			),
			"ICON" => "bx-context-toolbar-create-icon",
			"ID" => "bx-context-toolbar-create-chapter"
		);
	}

	if (CLearnAccessMacroses::CanUserEditLesson(array('lesson_id' => $contextLessonId)))
	{
		$arMenuButtons[] = array(
			"TEXT" => GetMessage("LEARNING_COURSES_TEST_ADD"),
			"TITLE" => GetMessage("LEARNING_COURSES_TEST_ADD"),
			"URL" => 'javascript:'.$APPLICATION->GetPopupLink(
				array(
					"URL" => "/bitrix/admin/learn_test_edit.php?lang=" . LANGUAGE_ID
						. "&COURSE_ID=".$arParams["COURSE_ID"]
						. "&bxpublic=Y&from_module=learning&return_url="
						. urlencode($addReturnUrl["test"]),
					"PARAMS" => array(
						"width" => 700, 'height' => 500, 'resize' => false,
					),
				)
			),
			"ICON" => "bx-context-toolbar-create-icon",
			"ID" => "bx-context-toolbar-create-test",
		);
	}

	if (CLearnAccessMacroses::CanUserAddLessonWithoutParentLesson())
	{
		$arMenuButtons[] = array(
			"TEXT" => GetMessage("LEARNING_COURSES_COURSE_ADD"),
			"TITLE" => GetMessage("LEARNING_COURSES_COURSE_ADD"),
			"URL" => 'javascript:'.$APPLICATION->GetPopupLink(
				array(
					"URL" => "/bitrix/admin/learn_course_edit.php?lang=" . LANGUAGE_ID
						. "&bxpublic=Y&from_module=learning&return_url="
						. urlencode($addReturnUrl["course"]),
					"PARAMS" => array(
						"width" => 700, 'height' => 500, 'resize' => false,
					),
				)
			),
			"ICON" => "bx-context-toolbar-create-icon",
			"ID" => "bx-context-toolbar-create-course",
		);
	}

	if (count($arMenuButtons) > 0)
		$arMenuButtons[] = array("SEPARATOR" => "Y");

	if (CLearnAccessMacroses::CanUserEditLesson(array('lesson_id' => $contextLessonId)))
	{
		$arMenuButtons[] = array(
			"TEXT" => GetMessage("LEARNING_COURSES_QUEST_S_ADD"),
			"TITLE" => GetMessage("LEARNING_COURSES_QUEST_S_ADD"),
			"URL" => 'javascript:'.$APPLICATION->GetPopupLink(
				array(
					"URL" => "/bitrix/admin/learn_question_edit.php?lang=" . LANGUAGE_ID
						. "&COURSE_ID=" . $arParams["COURSE_ID"]
						. "&LESSON_PATH=" . $contextLessonId
						. "&QUESTION_TYPE=S&bxpublic=Y&from_module=learning",
					"PARAMS" => array(
						"width" => 700, 'height' => 500, 'resize' => false,
					),
				)
			),
			"ICON" => "bx-context-toolbar-create-icon",
			"ID" => "bx-context-toolbar-create-question-s",
		);

		$arMenuButtons[] = array(
			"TEXT" => GetMessage("LEARNING_COURSES_QUEST_M_ADD"),
			"TITLE" => GetMessage("LEARNING_COURSES_QUEST_M_ADD"),
			"URL" => 'javascript:'.$APPLICATION->GetPopupLink(
				array(
					"URL" => "/bitrix/admin/learn_question_edit.php?lang=" . LANGUAGE_ID
						. "&COURSE_ID=".$arParams["COURSE_ID"]
						. "&LESSON_PATH=" . $contextLessonId
						. "&QUESTION_TYPE=M&bxpublic=Y&from_module=learning",
					"PARAMS" => array(
						"width" => 700, 'height' => 500, 'resize' => false,
					),
				)
			),
			"ICON" => "bx-context-toolbar-create-icon",
			"ID" => "bx-context-toolbar-create-question-m",
		);

		$arMenuButtons[] = array(
			"TEXT" => GetMessage("LEARNING_COURSES_QUEST_R_ADD"),
			"TITLE" => GetMessage("LEARNING_COURSES_QUEST_R_ADD"),
			"URL" => 'javascript:'.$APPLICATION->GetPopupLink(
				array(
					"URL" => "/bitrix/admin/learn_question_edit.php?lang=" . LANGUAGE_ID
						. "&COURSE_ID=".$arParams["COURSE_ID"]
						. "&LESSON_PATH=" . $contextLessonId
						. "&QUESTION_TYPE=R&bxpublic=Y&from_module=learning",
					"PARAMS" => array(
						"width" => 700, 'height' => 500, 'resize' => false,
					),
				)
			),
			"ICON" => "bx-context-toolbar-create-icon",
			"ID" => "bx-context-toolbar-create-question-s",
		);

		$arMenuButtons[] = array(
			"TEXT" => GetMessage("LEARNING_COURSES_QUEST_T_ADD"),
			"TITLE" => GetMessage("LEARNING_COURSES_QUEST_T_ADD"),
			"URL" => 'javascript:'.$APPLICATION->GetPopupLink(
				array(
					"URL" => "/bitrix/admin/learn_question_edit.php?lang=" . LANGUAGE_ID
						. "&COURSE_ID=".$arParams["COURSE_ID"]."&LESSON_PATH=" . $contextLessonId
						. "&QUESTION_TYPE=T&bxpublic=Y&from_module=learning",
					"PARAMS" => array(
						"width" => 700, 'height' => 500, 'resize' => false,
					),
				)
			),
			"ICON" => "bx-context-toolbar-create-icon",
			"ID" => "bx-context-toolbar-create-question-m",
		);
	}
}

if (count($arMenuButtons) > 0)
{
	$arAreaButtons = array(
		array(
			"TEXT" => GetMessage("MAIN_ADD"),
			"TITLE" => GetMessage("MAIN_ADD"),
			"ICON" => "bx-context-toolbar-create-icon",
			"ID" => "bx-context-toolbar-learning-create",
			"MENU" => $arMenuButtons
		)
	);

	$this->AddIncludeAreaIcons($arAreaButtons);
}

}
catch (LearnException $e)
{
	ShowError(GetMessage('LEARNING_COURSE_DENIED'));
	return;
}

$this->IncludeComponentTemplate($componentPage);
?>