<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("learning"))
{
	ShowError(GetMessage("LEARNING_MODULE_NOT_FOUND"));
	return;
}

$arParams["COURSE_ID"] = (isset($arParams["COURSE_ID"]) && intval($arParams["COURSE_ID"]) > 0 ? intval($arParams["COURSE_ID"]) : intval($_REQUEST["COURSE_ID"]));
$arParams["TEST_DETAIL_TEMPLATE"] = ($arParams["TEST_DETAIL_TEMPLATE"] <> '' ? htmlspecialcharsbx($arParams["TEST_DETAIL_TEMPLATE"]) : 'test.php?TEST_ID=#TEST#');
$arParams["CHECK_PERMISSIONS"] = (isset($arParams["CHECK_PERMISSIONS"]) && $arParams["CHECK_PERMISSIONS"]=="N" ? "N" : "Y");
$arParams["TESTS_PER_PAGE"] = (intval($arParams["TESTS_PER_PAGE"]) > 0 ? intval($arParams["TESTS_PER_PAGE"]) : 20);

//Title
$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y" );
if ($arParams["SET_TITLE"] == "Y")
	$APPLICATION->SetTitle(GetMessage("LEARNING_TESTS_LIST"));

//Course
$rsCourse = CCourse::GetList(
	array(),
	array(
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

//arResult
$arResult = Array(
	"TESTS" => Array(),
	"TESTS_COUNT" => 0,
	"ERROR_MESSAGE" => "",
	"NAV_SRTING" => "",
	"NAV_RESULT" => null,
);

$arNavParams = array();
if ((int) $arParams["TESTS_PER_PAGE"] > 0)
{
	$arNavParams['nPageSize'] = (int) $arParams["TESTS_PER_PAGE"];
	$arNavParams['bDescPageNumbering'] = false;
}


if(empty($arParams["FILTER_NAME"]) || !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["FILTER_NAME"]))
{
	$arFilter = array(
		"COURSE_ID"         => $arParams["COURSE_ID"],
		"ACTIVE"            => "Y",
		"CHECK_PERMISSIONS" => $arParams["CHECK_PERMISSIONS"]
	);
}
else
{
	$arFilter = $GLOBALS[$arParams["FILTER_NAME"]];
	if ( ! is_array($arFilter) )
		$arFilter = array();

	$arWhiteList = array(
		'ID', 'SORT', 'ATTEMPT_LIMIT', 'TIME_LIMIT', 'NAME', 'DESCRIPTION',
		'APPROVED', 'INCLUDE_SELF_TEST', 'RANDOM_ANSWERS', 'RANDOM_QUESTIONS',
		'QUESTIONS_FROM', 'QUESTIONS_FROM_ID', 'PASSAGE_TYPE'
	);

	foreach (array_keys($arFilter) as $filterKey)
	{
		if ( ! in_array($filterKey, $arWhiteList, true) )
		{
			return false;
		}
	}
}

$arFilter['COURSE_ID']         = $arParams['COURSE_ID'];
$arFilter['ACTIVE']            = 'Y';
$arFilter['CHECK_PERMISSIONS'] = $arParams['CHECK_PERMISSIONS'];

$rsTest = CTest::GetList(
	array("SORT" => "ASC"),
	$arFilter,
	$arNavParams
);

$arResult["NAV_STRING"] = $rsTest->GetPageNavString(GetMessage("LEARNING_TESTS_NAV"));
$arResult["NAV_RESULT"] = $rsTest;

while($arTest = $rsTest->GetNext())
{
	// Resolve links "?COURSE_ID={SELF}". Don't relay on it, this behaviour
	// can be changed in future without any notifications.
	if (isset($arTest['DESCRIPTION']))
	{
		$arTest['DESCRIPTION'] = CLearnHelper::PatchLessonContentLinks(
			$arTest['DESCRIPTION'],
			$arParams['COURSE_ID']
		);
	}

	//Test URL
	$arTest["TEST_DETAIL_URL"] = CComponentEngine::MakePathFromTemplate(
		$arParams["TEST_DETAIL_TEMPLATE"],
		Array(
			"TEST_ID" => $arTest["ID"],
			"COURSE_ID" => $arTest["COURSE_ID"],
		)
	);

	//Unfinished attempt exists?
	$arTest["ATTEMPT"] = false;

	if ($USER->IsAuthorized())
	{
		$rsAttempt = CTestAttempt::GetList(
			Array(),
			Array(
				"TEST_ID" => $arTest["ID"],
				"STATUS" => "B",
				"STUDENT_ID" => intval($USER->GetID()),
				"CHECK_PERMISSIONS" => $arParams["CHECK_PERMISSIONS"]
			)
		);

		$arTest["ATTEMPT"] = $rsAttempt->GetNext();
	}

	if ($arTest["PREVIOUS_TEST_ID"] > 0 && $arTest["PREVIOUS_TEST_SCORE"] > 0)
	{
		$rsPrevTest = CTest::GetList(
			array(),
			array(
				"ID" => $arTest["PREVIOUS_TEST_ID"],
				'CHECK_PERMISSIONS' => 'N'
				)
			);
		if ($arPrevTest = $rsPrevTest->GetNext())
		{
			if ($parent = $this->GetParent())
			{
				$testUrlTemplate = CComponentEngine::MakePathFromTemplate($parent->arResult["URL_TEMPLATES"]["test"], Array("TEST_ID" => $arPrevTest["ID"],"COURSE_ID" => $arPrevTest["COURSE_ID"]));
				$arTest["PREVIOUS_TEST_LINK"] = "<a href=\"".$testUrlTemplate."\">".$arPrevTest["NAME"]."</a>";
			}
		}
	}

	$arResult["TESTS"][] = $arTest;
}

$arResult["TESTS_COUNT"] = count($arResult["TESTS"]);
if ($arResult["TESTS_COUNT"] <= 0)
	$arResult["ERROR_MESSAGE"] = GetMessage("LEARNING_BAD_TEST_LIST");

unset($rsTest);
unset($arTest);
unset($rsAttempt);


$this->IncludeComponentTemplate();
?>
