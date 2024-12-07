<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
require_once($_SERVER["DOCUMENT_ROOT"].$componentPath."/functions.php");

if (!CModule::IncludeModule("learning"))
{
	ShowError(GetMessage("LEARNING_MODULE_NOT_FOUND"));
	return;
}

//Params
$arParams["CHAPTER_DETAIL_TEMPLATE"] = ($arParams["CHAPTER_DETAIL_TEMPLATE"] <> '' ? $arParams["CHAPTER_DETAIL_TEMPLATE"]: "chapter.php?CHAPTER_ID=#CHAPTER_ID#");
$arParams["LESSON_DETAIL_TEMPLATE"] = ($arParams["LESSON_DETAIL_TEMPLATE"] <> '' ? $arParams["LESSON_DETAIL_TEMPLATE"] : "lesson.php?LESSON_ID=#LESSON_ID#");
$arParams["SELF_TEST_TEMPLATE"] = ($arParams["SELF_TEST_TEMPLATE"] <> '' ? $arParams["SELF_TEST_TEMPLATE"] : "self.php?LESSON_ID=#LESSON_ID#");
$arParams["TESTS_LIST_TEMPLATE"] = ($arParams["TESTS_LIST_TEMPLATE"] <> '' ? $arParams["TESTS_LIST_TEMPLATE"] :"course/test_list.php?COURSE_ID=#COURSE_ID#");
$arParams["TEST_DETAIL_TEMPLATE"] = ($arParams["TEST_DETAIL_TEMPLATE"] <> '' ? $arParams["TEST_DETAIL_TEMPLATE"] :"course/test.php?COURSE_ID=#COURSE_ID#&TEST_ID=#TEST_ID#");
$arParams["COURSE_DETAIL_TEMPLATE"] = ($arParams["COURSE_DETAIL_TEMPLATE"] <> '' ? $arParams["COURSE_DETAIL_TEMPLATE"] :"course/index.php?COURSE_ID=#COURSE_ID#");

//Check permissions
$arParams["CHECK_PERMISSIONS"] = (isset($arParams["CHECK_PERMISSIONS"]) && $arParams["CHECK_PERMISSIONS"]=="N" ? "N" : "Y");
$arParams["COURSE_ID"] = (isset($arParams["COURSE_ID"]) && intval($arParams["COURSE_ID"]) > 0 ? intval($arParams["COURSE_ID"]) : intval($_REQUEST["COURSE_ID"]));

if ($arParams['CHECK_PERMISSIONS'] !== 'N')
{
	$isAccessible = false;
	$linkedLessonId = CCourse::CourseGetLinkedLesson ($arParams["COURSE_ID"]);
	if ($linkedLessonId !== false)
	{
		try
		{
			$isAccessible = CLearnAccessMacroses::CanUserViewLessonContent (array('lesson_id' => $linkedLessonId));
		}
		catch (Exception $e)
		{
			$isAccessible = false;	// access denied
		}
	}

	if ( ! $isAccessible )
	{
		ShowError(GetMessage('LEARNING_COURSE_DENIED'));
		return;
		exit();
	}
}

$rsCourse = CCourse::GetList(
	Array(),
	Array(
		"ID" => $arParams["COURSE_ID"],
		"ACTIVE" => "Y",
		"ACTIVE_DATE" => "Y",
		"SITE_ID" => LANG,
		"CHECK_PERMISSIONS" => 'N'
	)
);

if (!$arCourse = $rsCourse->GetNext())
{
	ShowError(GetMessage("LEARNING_COURSE_DENIED"));
	return;
}

// Resolve links "?COURSE_ID={SELF}". Don't relay on it, this behaviour
// can be changed in future without any nitifications.
if (isset($arCourse['DETAIL_TEXT']))
{
	$arCourse['DETAIL_TEXT'] = CLearnHelper::PatchLessonContentLinks(
		$arCourse['DETAIL_TEXT'],
		$arParams["COURSE_ID"]
	);
}

if (isset($arCourse['PREVIEW_TEXT']))
{
	$arCourse['PREVIEW_TEXT'] = CLearnHelper::PatchLessonContentLinks(
		$arCourse['PREVIEW_TEXT'],
		$arParams["COURSE_ID"]
	);
}

//arResult
$arResult = Array(
	"ITEMS" => Array(),
	"COURSE" => $arCourse,
);

//Set Title
$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y" );
if ($arParams["SET_TITLE"] == "Y")
	$APPLICATION->SetTitle($arResult["COURSE"]["NAME"]);


$parent = &$this->GetParent();

//Course description item
$url = CComponentEngine::MakePathFromTemplate($arParams["COURSE_DETAIL_TEMPLATE"], Array("COURSE_ID" => $arParams["COURSE_ID"]));
$arResult["ITEMS"][] = Array(
	"NAME" => GetMessage("LEARNING_COURSE_DESCRIPTION"),
	"URL" => $url,
	"TYPE" => "CD",
	"SELECTED" => isset($parent->arResult["VARIABLES"]["INDEX"]) && $parent->arResult["VARIABLES"]["INDEX"] === "Y",
	"DEPTH_LEVEL" => 1
);

$CHAPTER_ID = $parent->arResult["VARIABLES"]["CHAPTER_ID"] ?? 0;

if ($CHAPTER_ID > 0)
{
	if (CLearnPath::IsUrlencodedPath($CHAPTER_ID))
	{
		$oTmp = new CLearnPath();
		$oTmp->ImportUrlencoded($CHAPTER_ID);
		$CHAPTER_ID = (int) $oTmp->GetBottom();
	}
	elseif (mb_substr($CHAPTER_ID, 0, 1) === '0')
		$CHAPTER_ID = (int)mb_substr($CHAPTER_ID, 1);
	else
		$CHAPTER_ID = (int) CLearnLesson::LessonIdByChapterId ($CHAPTER_ID);
}
else
	$CHAPTER_ID = false;

$lessonCount = 0;
$lessonCurrent = 0;

// Get Course Content
$arContents = CLearnCacheOfLessonTreeComponent::GetData($arParams['COURSE_ID']);


$bDelayChapters = isset($arParams['LEARNING_GROUP_CHAPTERS_ACTIVE_FROM']) &&
					is_array($arParams['LEARNING_GROUP_CHAPTERS_ACTIVE_FROM']);

$skipSubLessons = false;
foreach ($arContents as $arContent)
{
	$arContent['DELAYED'] = false;

	if ($skipSubLessons)
	{
		if ($arContent['DEPTH_LEVEL'] > 1)
			continue;

		$skipSubLessons = false;
	}

	if (
		$bDelayChapters
		&& ($arContent['DEPTH_LEVEL'] == 1)
	)
	{
		if (isset($arParams['LEARNING_GROUP_CHAPTERS_ACTIVE_FROM'][$arContent['LESSON_ID']]))
		{
			$activeFrom = $arParams['LEARNING_GROUP_CHAPTERS_ACTIVE_FROM'][$arContent['LESSON_ID']];
			if (time() < MakeTimeStamp($activeFrom))
			{
				$arContent['DELAYED'] = $activeFrom;
				$skipSubLessons = true;
			}
		}
	}

	if($arContent["TYPE"]=="CH")
	{
		$itemURL = CComponentEngine::MakePathFromTemplate($arParams["CHAPTER_DETAIL_TEMPLATE"],
			Array("CHAPTER_ID" => '0' . $arContent["ID"],"COURSE_ID" => $arParams["COURSE_ID"])
		);

		if ($CHAPTER_ID == $arContent["ID"])
			$arContent["SELECTED"] = true;
		else
			$arContent["SELECTED"] = false;

		$arContent["CHAPTER_OPEN"] = $arContent["SELECTED"];
	}
	elseif (($CHAPTER_ID > 0) && ($CHAPTER_ID == $arContent["ID"]))
	{
		$itemURL = CComponentEngine::MakePathFromTemplate($arParams["LESSON_DETAIL_TEMPLATE"],
			array(
				"LESSON_ID" => $arContent["ID"],
				"COURSE_ID" => $arParams["COURSE_ID"]
			)
		);

		$arContent["SELECTED"] = true;
	}
	else
	{
		$itemURL = CComponentEngine::MakePathFromTemplate($arParams["LESSON_DETAIL_TEMPLATE"],
			array(
				"LESSON_ID" => $arContent["ID"],
				"COURSE_ID" => $arParams["COURSE_ID"]
			)
		);

		/*$selftestURL = CComponentEngine::MakePathFromTemplate($arParams["SELF_TEST_TEMPLATE"],
			Array("LESSON_ID" => $arContent["ID"], "SELF_TEST_ID" => $arContent["ID"], "COURSE_ID" => $arParams["COURSE_ID"])
		);*/

		$arContent["SELECTED"] = (
			isset($parent->arResult["VARIABLES"]["LESSON_ID"])
			&& $parent->arResult["VARIABLES"]["LESSON_ID"] == $arContent["ID"]
		); //_IsItemSelected(Array($itemURL, $selftestURL));
	}

	$lessonCount++;

	// quick hack due to low time
	if($arContent['~#LESSON_PATH'] <> '')
	{
		if(!mb_strpos($itemURL, '?'))
		{
			$itemURL .= '?';
		}
		else
		{
			$itemURL .= '&';
		}

		$itemURL .= 'LESSON_PATH='.$arContent['~#LESSON_PATH'];
	}

	$arContent["URL"] = htmlspecialcharsbx($itemURL);

	if ($arContent["SELECTED"])
		$lessonCurrent = $lessonCount;

	$arResult["ITEMS"][] = $arContent;
}

//Page Properties
$APPLICATION->SetPageProperty("learning_course_name", $arResult["COURSE"]["NAME"]);
$APPLICATION->SetPageProperty("learning_lesson_count", $lessonCount);
$APPLICATION->SetPageProperty("learning_lesson_current", $lessonCurrent);

//Test list item
$url = CComponentEngine::MakePathFromTemplate($arParams["TESTS_LIST_TEMPLATE"], Array("COURSE_ID" => $arParams["COURSE_ID"]));

$testsCount = CTest::GetCount(array("COURSE_ID"=>$arParams["COURSE_ID"], "ACTIVE" => "Y", 'CHECK_PERMISSIONS' => 'N'));

if ($testsCount > 0)
{
	$arResult['ITEMS'][] = Array(
		'NAME'        =>  GetMessage('LEARNING_TEST_LIST') . '&nbsp;(' . $testsCount . ')',
		'URL'         =>  $url,
		'TYPE'        => 'TL',
		'SELECTED'    =>  isset($parent->arResult['VARIABLES']['TEST_LIST']) && $parent->arResult['VARIABLES']['TEST_LIST'] == 'Y',
		'DEPTH_LEVEL' =>  1
	);
}

unset($arContent);
unset($rsContent);

//Open chapters from Cookies
$arOpenChapters = Array();
if (array_key_exists("LEARN_MENU_".$arParams["COURSE_ID"],$_COOKIE))
	$arOpenChapters = explode(",", $_COOKIE["LEARN_MENU_".$arParams["COURSE_ID"]]);

//Chapter open if child selected
for ($itemIndex = 0, $size = count($arResult["ITEMS"]); $itemIndex < $size; $itemIndex++)
{
	if ($arResult["ITEMS"][$itemIndex]["TYPE"] != "CH" || $arResult["ITEMS"][$itemIndex]["SELECTED"] === true)
		continue;

	$arResult["ITEMS"][$itemIndex]["CHAPTER_OPEN"] = (
		in_array($arResult["ITEMS"][$itemIndex]["ID"], $arOpenChapters) || _IsInsideSelect($arResult["ITEMS"], ($itemIndex+1), $arResult["ITEMS"][$itemIndex]["DEPTH_LEVEL"])
	);
}

$this->IncludeComponentTemplate();
