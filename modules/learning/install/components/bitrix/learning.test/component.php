<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
require_once($_SERVER["DOCUMENT_ROOT"].$componentPath."/functions.php");

//Authorized?
if (!$USER->IsAuthorized())
{
	$APPLICATION->AuthForm(GetMessage("LEARNING_NO_AUTHORIZE"), false, false, "N", false);
	return;
}

//Module
if (!CModule::IncludeModule("learning"))
{
	ShowError(GetMessage("LEARNING_MODULE_NOT_FOUND"));
	return;
}


$errors = array();
//Params
$arParams["PAGE_WINDOW"] = (isset($arParams["PAGE_WINDOW"]) && intval($arParams["PAGE_WINDOW"]) > 0 ? intval($arParams["PAGE_WINDOW"]) : "10");
$arParams["SHOW_TIME_LIMIT"] = (isset($arParams["SHOW_TIME_LIMIT"]) && $arParams["SHOW_TIME_LIMIT"] == "N" ? "N" : "Y");
$arParams["GRADEBOOK_TEMPLATE"] = ($arParams["GRADEBOOK_TEMPLATE"] <> '' ? htmlspecialcharsbx($arParams["GRADEBOOK_TEMPLATE"]) : "");
//$arParams["CHECK_PERMISSIONS"] = (isset($arParams["CHECK_PERMISSIONS"]) && $arParams["CHECK_PERMISSIONS"]=="N" ? "N" : "Y");
$arParams["TEST_ID"] = (isset($arParams["TEST_ID"]) && intval($arParams["TEST_ID"]) > 0 ? intval($arParams["TEST_ID"]) : intval($_REQUEST["TEST_ID"]));
$arParams["COURSE_ID"] = (isset($arParams["COURSE_ID"]) && intval($arParams["COURSE_ID"]) > 0 ? intval($arParams["COURSE_ID"]) : intval($_REQUEST["COURSE_ID"]));

if ($arParams["PAGE_NUMBER_VARIABLE"] == '' || !preg_match("#^[A-Za-z_][A-Za-z01-9_]*$#", $arParams["PAGE_NUMBER_VARIABLE"]))
	$arParams["PAGE_NUMBER_VARIABLE"] = "PAGE";

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
	ShowError(GetMessage("LEARNING_TEST_DENIED"));
	return;
}

//Test
$rsTest = CTest::GetList(
	Array(),
	Array(
		"ID" => $arParams["TEST_ID"],
		"ACTIVE" => "Y",
		"COURSE_ID" => $arParams["COURSE_ID"],
		//"CHECK_PERMISSIONS" => $arParams["CHECK_PERMISSIONS"]
	)
);



if (!$arTest = $rsTest->GetNext())
{
	ShowError(GetMessage("LEARNING_TEST_DENIED"));
	return;
}
else
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

	if ($arTest["APPROVED"] == "Y")
	{
		$arTest["CURRENT_INDICATION_PERCENT"] = ($arTest["CURRENT_INDICATION"] & 1) ? "Y" : "N";
		$arTest["CURRENT_INDICATION_MARK"] = ($arTest["CURRENT_INDICATION"] & 2) >> 1 ? "Y" : "N";
		$arTest["FINAL_INDICATION_CORRECT_COUNT"] = ($arTest["FINAL_INDICATION"] & 1) ? "Y" : "N";
		$arTest["FINAL_INDICATION_SCORE"] = ($arTest["FINAL_INDICATION"] & 2) >> 1 ? "Y" : "N";
		$arTest["FINAL_INDICATION_MARK"] = ($arTest["FINAL_INDICATION"] & 4) >> 2 ? "Y" : "N";
		$arTest["FINAL_INDICATION_MESSAGE"] = ($arTest["FINAL_INDICATION"] & 8) >> 3 ? "Y" : "N";
	}
	else
	{
		$arTest["CURRENT_INDICATION_PERCENT"] = "N";
		$arTest["CURRENT_INDICATION_MARK"] = "N";
		$arTest["FINAL_INDICATION_CORRECT_COUNT"] = "N";
		$arTest["FINAL_INDICATION_SCORE"] = "N";
		$arTest["FINAL_INDICATION_MARK"] = "N";
		$arTest["FINAL_INDICATION_MESSAGE"] = "N";
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
}

if ($USER->GetID())
{
	$arTest["ATTEMPT_LIMIT"] += CGradeBook::GetExtraAttempts($USER->GetID(), $arParams["TEST_ID"]);
}
$oAccess = CLearnAccess::GetInstance($USER->GetID());
$isRelativelyHighAccessLevel = $oAccess->IsBaseAccess(
	CLearnAccess::OP_LESSON_CREATE
	| CLearnAccess::OP_LESSON_READ
	| CLearnAccess::OP_LESSON_WRITE
	| CLearnAccess::OP_LESSON_REMOVE);
$bCheckPerm = (!$isRelativelyHighAccessLevel && !$USER->IsAdmin());
if ($bCheckPerm
	&& $arTest["PREVIOUS_TEST_ID"] > 0
	&& $arTest["PREVIOUS_TEST_SCORE"] > 0
	&& !CTest::isPrevPassed($arTest["PREVIOUS_TEST_ID"], $arTest["PREVIOUS_TEST_SCORE"]))
{
	if ($arTest["PREVIOUS_TEST_LINK"])
	{
		$errors[] = str_replace("#TEST_LINK#", "\"".$arTest["PREVIOUS_TEST_LINK"]."\"", GetMessage("LEARNING_TEST_DENIED_PREVIOUS"));
	}
}

//Session variables
$userID = $USER->GetID() ? $USER->GetID() : 0;
$sessAttemptID =& $_SESSION["LEARN_".$arParams["TEST_ID"]."_ATTEMPT_ID_".$userID];
$sessAttemptFinished =& $_SESSION["LEARN_".$arParams["TEST_ID"]."_FINISHED_".$userID];
$sessAttemptError =& $_SESSION["LEARN_".$arParams["TEST_ID"]."_ERROR_".$userID];
$sessAttempt =& $_SESSION["LEARN_".$arParams["TEST_ID"]."_COMPLETED_".$userID];
$sessIncorrectMessage =& $_SESSION["LEARN_".$arParams["TEST_ID"]."_INCORRECT_MESSAGE_".$userID];

//Page url template
$currentPage = GetPagePath(false, false);
$queryString= htmlspecialcharsbx(DeleteParam(array($arParams["PAGE_NUMBER_VARIABLE"], "SEF_APPLICATION_CUR_PAGE_URL")));
$pageTemplate = (
	$queryString == "" ?
	$currentPage."?".$arParams["PAGE_NUMBER_VARIABLE"]."=#PAGE_ID#" :
	$currentPage."?".$queryString."&amp;".$arParams["PAGE_NUMBER_VARIABLE"]."=#PAGE_ID#"
);

//arResult
$arResult = Array(
	"TEST" => $arTest,
	"QUESTION" => Array(),
	"QBAR" => Array(),
	"NAV" => Array(
		"PAGE_COUNT" => 0, //pages count
		"PAGE_NUMBER" => 1, //current page id
		"NEXT_QUESTION" => 0, //next question page id
		"PREV_QUESTION" => 0, //previous question page id
		"FIRST_NOANSWER" => 0,
		"NEXT_NOANSWER" => 0,
		"PREV_NOANSWER" => 0,
		"START_PAGE" => 0,
		"END_PAGE" => 0,
	),
	"PAGE_TEMPLATE" => $pageTemplate,
	"GRADEBOOK_URL" => CComponentEngine::MakePathFromTemplate(
		$arParams["GRADEBOOK_TEMPLATE"],
		[
			"FOR_TEST_ID" => $arParams["TEST_ID"],
			"TEST_ID" => $arParams["TEST_ID"],
			"COURSE_ID" => $arTest["COURSE_ID"]
		]
	),
	"PREVIOUS_PAGE" => "",
	"NEXT_PAGE" => "",
	"ACTION_PAGE" => "",
	"REDIRECT_PAGE" => "",
	"TEST_FINISHED" => $sessAttemptFinished,
	"ERROR_MESSAGE" => $sessAttemptError,
	"INCORRECT_QUESTION" => $sessIncorrectMessage,
	"ATTEMPT" => $sessAttempt,
	"SECONDS_TO_END" => 0,
	"SECONDS_TO_END_STRING" => 0,
	"ACCESS_ERRORS" => $errors,
);

if (!sizeof($errors))
{
	//Action form page
	if ($_SERVER['REDIRECT_STATUS'] == '404' || isset($_REQUEST["SEF_APPLICATION_CUR_PAGE_URL"]))
		$arResult["ACTION_PAGE"] = POST_FORM_ACTION_URI;
	else
		$arResult["ACTION_PAGE"] = $currentPage.($queryString == "" ? "" : "?".$queryString);

	//Page number
	if (array_key_exists($arParams["PAGE_NUMBER_VARIABLE"], $_REQUEST) && intval($_REQUEST[$arParams["PAGE_NUMBER_VARIABLE"]]) > 1)
		$arResult["NAV"]["PAGE_NUMBER"] = intval($_REQUEST[$arParams["PAGE_NUMBER_VARIABLE"]]);

	//Redirect page
	if (!empty($_REQUEST["back_page"]) && check_bitrix_sessid())
		$arResult["REDIRECT_PAGE"] = $_REQUEST["back_page"];
	else
		$arResult["REDIRECT_PAGE"] = str_replace(
			"#PAGE_ID#",
			(array_key_exists($arParams["PAGE_NUMBER_VARIABLE"], $_REQUEST) ? $arResult["NAV"]["PAGE_NUMBER"]+1 : $arResult["NAV"]["PAGE_NUMBER"]),
			$arResult["PAGE_TEMPLATE"]
		);

	$sessAttemptError = null;
	$sessAttemptFinished = null;
	$sessAttempt = null;

	//Title
	$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y" );
	if ($arParams["SET_TITLE"] == "Y")
		$APPLICATION->SetTitle($arResult["TEST"]["NAME"]);

	//Actions
	$bTestCreate = ($_SERVER["REQUEST_METHOD"]=="POST" && !isset($sessAttemptID) && isset($_POST["next"]));
	$bPostAnswer = ($_SERVER["REQUEST_METHOD"]=="POST" && isset($sessAttemptID) && $_POST["ANSWERED"] == "Y");

	$arResult["COMPLETE_PERCENT"] = 0;
	$arResult["CURRENT_MARK"] = "";
	if (isset($sessAttemptID) && intval($sessAttemptID) > 0)
	{
		$arResult["COMPLETE_PERCENT"] = CTestResult::GetPercent(@$sessAttemptID);
		if ($arResult["COMPLETE_PERCENT"])
		{
			$arResult["CURRENT_MARK"] = CLTestMark::GetByPercent($arTest["ID"], $arResult["COMPLETE_PERCENT"]);
		}
	}

	if ($bTestCreate)
	{
		$sessIncorrectMessage = null;

		//If old attempt exists?
		if ($arAttempt = _AttemptExists($arParams["TEST_ID"]))
		{
			$sessAttemptID = $arAttempt["ID"];
			if ($arAttempt["STATUS"] == "N")
			{
				$arFields = array(
					"~DATE_START" => CDatabase::CurrentTimeFunction(),
					"DATE_END" => false,
					"STATUS" => "B"
				);

				$ta = new CTestAttempt;

				$res = $ta->Update($arAttempt["ID"], $arFields);
			}
			LocalRedirect($arResult["REDIRECT_PAGE"]);
		}

		//Check attempt limit
		if ($arTest["ATTEMPT_LIMIT"] > 0 && $arTest["ATTEMPT_LIMIT"] <= CTestAttempt::GetCount($arParams["TEST_ID"], $USER->GetID()))
		{
			$sessAttemptError = GetMessage("LEARNING_LIMIT_ERROR");
			LocalRedirect($arResult["REDIRECT_PAGE"]);
		}

		//Check min time between attempts
		if ($arTest["MIN_TIME_BETWEEN_ATTEMPTS"] > 0)
		{
			CTimeZone::Disable();
			$rsPrevAttempt = CTestAttempt::GetList(array("DATE_END" => "DESC"), array("TEST_ID" => $arParams["TEST_ID"], "STUDENT_ID" => $USER->GetID()));
			CTimeZone::Enable();

			if ($arPrevAttempt = $rsPrevAttempt->GetNext())
			{
				$prevTime = strtotime($arPrevAttempt["DATE_END"]);
				$timeDiff = floor((time() - strtotime($arPrevAttempt["DATE_END"])) / 60); //time difference in minutes
				if ($timeDiff < $arTest["MIN_TIME_BETWEEN_ATTEMPTS"])
				{
					$nextAttemptAfter = $arTest["MIN_TIME_BETWEEN_ATTEMPTS"] - $timeDiff;
					$nextAttemptAfterD = floor($nextAttemptAfter / (60 * 24));
					$nextAttemptAfterH = floor(($nextAttemptAfter - $nextAttemptAfterD * 60 * 24) / 60);
					$nextAttemptAfterM = $nextAttemptAfter - $nextAttemptAfterD * 60 * 24 - $nextAttemptAfterH * 60;
					$sessAttemptError = GetMessage("LEARNING_TEST_TIME_INTERVAL_ERROR")." ".($nextAttemptAfterD > 0 ? $nextAttemptAfterD. " ".GetMessage("LEARNING_TEST_TIME_INTERVAL_ERROR_D")." " : "").($nextAttemptAfterH > 0 ? $nextAttemptAfterH. " ".GetMessage("LEARNING_TEST_TIME_INTERVAL_ERROR_H")." " : "").$nextAttemptAfterM. " ".GetMessage("LEARNING_TEST_TIME_INTERVAL_ERROR_M")."";
					LocalRedirect($arResult["REDIRECT_PAGE"]);
				}
			}
		}

		//Add new attempt
		$rsAttempt = new CTestAttempt();
		$attemptID = $rsAttempt->Add(Array("TEST_ID" => $arParams["TEST_ID"], "STUDENT_ID" => $USER->GetID(), "STATUS" => "B"));
		if(!$attemptID)
		{
			$sessAttemptError = ( ($ex = $APPLICATION->GetException()) ? $ex->GetString() : GetMessage("LEARNING_ATTEMPT_CREATE_ERROR"));
			LocalRedirect($arResult["REDIRECT_PAGE"]);
		}

		//Create test questions
		$success = CTestAttempt::CreateAttemptQuestions($attemptID);
		if(!$success)
		{
			$sessAttemptError = ( ($ex = $APPLICATION->GetException()) ? $ex->GetString() : GetMessage("LEARNING_ATTEMPT_CREATE_ERROR"));
			CTestAttempt::Delete($attemptID);
			LocalRedirect($arResult["REDIRECT_PAGE"]);
		}

		$sessAttemptID = $attemptID;
		LocalRedirect($arResult["REDIRECT_PAGE"]);
	}
	elseif ($bPostAnswer)
	{
		$sessIncorrectMessage = null;

		//Check attempt from session
		if (!$arAttempt = _AttemptExists($arParams["TEST_ID"], $sessAttemptID))
		{
			$sessAttemptID = null;
			$sessAttemptError = GetMessage("LEARNING_ATTEMPT_NOT_FOUND_ERROR") . ': ' . $sessAttemptID;
			LocalRedirect($arResult["REDIRECT_PAGE"]);
		}

		//Check test result
		$testResultID = intval($_REQUEST["TEST_RESULT"]);
		$arFields = Array("ID" => $testResultID, "ATTEMPT_ID" => $sessAttemptID, 'CHECK_PERMISSIONS' => 'N');

		if ($arTest["PASSAGE_TYPE"] < 2)
			$arFields["ANSWERED"] = "N";

		$rsTestResult = CTestResult::GetList(array(),$arFields);
		if(!$arTestResult = $rsTestResult->GetNext())
		{
			$sessAttemptID = null;
			$sessAttemptError = GetMessage("LEARNING_RESPONSE_SAVE_ERROR");
			LocalRedirect($arResult["REDIRECT_PAGE"]);
		}

		//Save User answer
		if ($arTest["TIME_LIMIT"] == 0 || $arTest["TIME_LIMIT"]*60 >= time()-MakeTimeStamp($arAttempt["DATE_START"]))//Check time limit
		{
			if ($arTest["PASSAGE_TYPE"] == 0 || array_key_exists("answer", $_REQUEST))
			{
				$result = CTestResult::AddResponse($testResultID, $_REQUEST["answer"]);
				if(!$result)
				{
					$sessAttemptID = null;
					$sessAttemptError = ( ($ex = $APPLICATION->GetException()) ? $ex->GetString() : GetMessage("LEARNING_RESPONSE_SAVE_ERROR"));
					LocalRedirect($arResult["REDIRECT_PAGE"]);
				}
				else
				{
					$rsQuestion = CLQuestion::GetByID($arTestResult["QUESTION_ID"]);
					if ($arQuestion = $rsQuestion->GetNext())
					{
						// Resolve links "?COURSE_ID={SELF}". Don't relay on it, this behaviour
						// can be changed in future without any notifications.
						if (isset($arQuestion['DESCRIPTION']))
						{
							$arQuestion['DESCRIPTION'] = CLearnHelper::PatchLessonContentLinks(
								$arQuestion['DESCRIPTION'],
								$arParams['COURSE_ID']
							);
						}

						if ($arQuestion["QUESTION_TYPE"] != "T")
						{
							if ($arTest["SHOW_ERRORS"] == "Y" && $result["CORRECT"] == "N" && $result["ANSWERED"] == "Y" && $arQuestion["INCORRECT_MESSAGE"])
							{
								$sessIncorrectMessage = $arQuestion;
								if ($arTest["NEXT_QUESTION_ON_ERROR"] == "N" && $arTest["PASSAGE_TYPE"] == 2)
								{
									$arResult["REDIRECT_PAGE"] = str_replace(
										"#PAGE_ID#",
										(array_key_exists($arParams["PAGE_NUMBER_VARIABLE"], $_REQUEST) ? $arResult["NAV"]["PAGE_NUMBER"]-1 : 1),
										$arResult["PAGE_TEMPLATE"]
									);
								}
							}
						}
						elseif ($arQuestion["EMAIL_ANSWER"] == "Y") //Send message to a teacher
						{
							$rsLesson = CLearnLesson::GetList(array(), array(
								"LESSON_ID" => $arQuestion['LESSON_ID'],
								"CHECK_PERMISSIONS" => "N"
							));

							if ($arLesson = $rsLesson->GetNext())
							{
								$rsTeacher = CUser::GetByID($arLesson["CREATED_BY"]);
								if ($arTeacher = $rsTeacher->GetNext())
								{
									$courseName = "";
									$rsCourse = CCourse::GetByID($arTest["COURSE_ID"]);
									if ($arCourse = $rsCourse->GetNext())
										$courseName = $arCourse["NAME"];

									$arEventFields = array(
										"ID" => $testResultID,
										"ATTEMPT_ID" => $sessAttemptID,
										"TEST_NAME" => $arTest["NAME"],
										"COURSE_NAME" => $courseName,
										"USER" => "(".$USER->GetLogin().")".($USER->GetFullName() <> '' ? " ".$USER->GetFullName() : ""),
										"DATE" => date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL")), time()),
										"QUESTION_TEXT" => $arQuestion["NAME"],
										"ANSWER_TEXT" => $_REQUEST["answer"],
										"EMAIL_FROM" => COption::GetOptionString("main","email_from", "nobody@nobody.com"),
										"EMAIL_TO" => $arTeacher["EMAIL"],
										"MESSAGE_TITLE" => GetMessage("LEARNING_NEW_TEXT_ANSWER"),
									);
									$arrSITE =  LANG;
									CEvent::Send("NEW_LEARNING_TEXT_ANSWER", $arrSITE, $arEventFields, "N");
								}
							}
						}
					}
				}
			}
		}

		//If it was the last question, finish the attempt
		if ($arTest["PASSAGE_TYPE"] < 2)
		{
			$arProgress = CTestResult::GetProgress($sessAttemptID);
			if($arProgress["TODO"]==0)
			{
				$rsTestAttempt = new CTestAttempt;
				$rsTestAttempt->AttemptFinished($sessAttemptID);
				$rsAtt = CTestAttempt::GetByID((int) $sessAttemptID);
				if (($arAtt = $rsAtt->GetNext()) && $arTest["APPROVED"] == "Y")
				{
					$arAtt["CORRECT_COUNT"] = CTestResult::GetCorrectCount($arAtt["ID"]);
					$sessAttempt = $arAtt;
				}
				$sessAttemptID = null;
				$sessAttemptFinished = true;
			}
			elseif ($arTest["INCORRECT_CONTROL"] == 'Y' && CTestAttempt::IsTestFailed($sessAttemptID, $arTest["COMPLETED_SCORE"]))//If unable to complete
			{
				$rsAttempt = new CTestAttempt;
				$rsAttempt->AttemptFinished($sessAttemptID);
				$rsAtt = CTestAttempt::GetByID((int) $sessAttemptID);
				if (($arAtt = $rsAtt->GetNext()) && $arTest["APPROVED"] == "Y")
				{
					$arAtt["CORRECT_COUNT"] = CTestResult::GetCorrectCount($arAtt["ID"]);
					$sessAttempt = $arAtt;
				}
				$sessAttemptID = null;
				$sessAttemptFinished = true;
				LocalRedirect($arResult["REDIRECT_PAGE"]);
			}
		}

		//User wants to finish
		if (($_REQUEST["finish"] <> '') && $sessAttemptID)
		{
			$rsAttempt = new CTestAttempt;
			$rsAttempt->AttemptFinished($sessAttemptID);
			$rsAtt = CTestAttempt::GetByID((int) $sessAttemptID);
			if (($arAtt = $rsAtt->GetNext()) && $arTest["APPROVED"] == "Y")
			{
				$arAtt["CORRECT_COUNT"] = CTestResult::GetCorrectCount($arAtt["ID"]);
				$sessAttempt = $arAtt;
			}
			$sessAttemptID = null;
			$sessAttemptFinished = true;
			LocalRedirect($arResult["REDIRECT_PAGE"]);
		}

		LocalRedirect($arResult["REDIRECT_PAGE"]);
	}
	elseif (isset($sessAttemptID))
	{
		//Check attempt from session
		if (!$arAttempt = _AttemptExists($arParams["TEST_ID"], $sessAttemptID))
		{
			$sessAttemptID = null;
			$sessAttemptError = GetMessage("LEARNING_ATTEMPT_NOT_FOUND_ERROR") . ': ' . $sessAttemptID;
			LocalRedirect($arResult["REDIRECT_PAGE"]);
		}

		//Check time limit
		if ($arTest["TIME_LIMIT"] > 0 && $arTest["TIME_LIMIT"]*60 < time()-MakeTimeStamp($arAttempt["DATE_START"]))
		{
			$rsTestAttempt = new CTestAttempt;
			$rsTestAttempt->AttemptFinished($sessAttemptID);
			$rsAtt = CTestAttempt::GetByID((int) $sessAttemptID);
			if (($arAtt = $rsAtt->GetNext()) && $arTest["APPROVED"] == "Y")
			{
				$arAtt["CORRECT_COUNT"] = CTestResult::GetCorrectCount($arAtt["ID"]);
				$sessAttempt = $arAtt;
			}
			$sessAttemptID = null;
			$sessAttemptFinished = true;
			$sessAttemptError = GetMessage("LEARNING_TIME_LIMIT");
			LocalRedirect($arResult["REDIRECT_PAGE"]);
		}
		elseif($arTest["TIME_LIMIT"]>0)
		{
			$arResult["SECONDS_TO_END"] = $arTest["TIME_LIMIT"]*60 - (time()-MakeTimeStamp($arAttempt["DATE_START"]));
			$arResult["SECONDS_TO_END_STRING"] = _TimeToStringFormat($arResult["SECONDS_TO_END"]);
		}

		//If there are no questions, finish the attempt
		if ($arTest["PASSAGE_TYPE"] < 2)
		{
			$arProgress = CTestResult::GetProgress($sessAttemptID);
			if($arProgress["TODO"]==0)
			{
				$rsTestAttempt = new CTestAttempt;
				$rsTestAttempt->AttemptFinished($sessAttemptID);
				$rsAtt = CTestAttempt::GetByID((int) $sessAttemptID);
				if (($arAtt = $rsAtt->GetNext()) && $arTest["APPROVED"] == "Y")
				{
					$arAtt["CORRECT_COUNT"] = CTestResult::GetCorrectCount($arAtt["ID"]);
					$sessAttempt = $arAtt;
				}
				$sessAttemptID = null;
				$sessAttemptFinished = true;
				LocalRedirect($arResult["REDIRECT_PAGE"]);
			}
		}

		//Get questions
		$rsTestResult = CTestResult::GetList(Array("ID"=>"ASC"), Array("ATTEMPT_ID" => $sessAttemptID, 'CHECK_PERMISSIONS' => 'N'));
		$rsTestResult->NavStart(10000);
		$arResult["NAV"]["PAGE_COUNT"] = $rsTestResult->SelectedRowsCount();

		//If no questions
		if ($arResult["NAV"]["PAGE_COUNT"] <= 0)
		{
			$rsTestAttempt = new CTestAttempt;
			$rsTestAttempt->AttemptFinished($sessAttemptID);
			$sessAttemptID = null;
			$sessAttemptFinished = true;
			LocalRedirect($arResult["REDIRECT_PAGE"]);
		}

		if ($arResult["NAV"]["PAGE_NUMBER"] > $arResult["NAV"]["PAGE_COUNT"])
		{
			$arResult["NAV"]["PAGE_NUMBER"] = 1;
			$arResult["REDIRECT_PAGE"] = str_replace("#PAGE_ID#", $arResult["NAV"]["PAGE_NUMBER"] + 1, $arResult["PAGE_TEMPLATE"]);
		}

		$questionPageIndex = 1;
		while ($arAttemptQuestion = $rsTestResult->GetNext())
		{
			if (!$arResult["NAV"]["FIRST_NOANSWER"] && $arAttemptQuestion["ANSWERED"] == "N")
				$arResult["NAV"]["FIRST_NOANSWER"] = $questionPageIndex;

			$inaccessible = (
				($arTest["PASSAGE_TYPE"] < 2  && $arAttemptQuestion["ANSWERED"] == "Y") ||
				($arTest["PASSAGE_TYPE"] == 0 && $arAttemptQuestion["ANSWERED"] == "N")
			);

			if ($arResult["NAV"]["FIRST_NOANSWER"] == $questionPageIndex )
				$inaccessible = false;

			if (!$inaccessible)
			{
				if ($questionPageIndex < $arResult["NAV"]["PAGE_NUMBER"])
					$arResult["NAV"]["PREV_QUESTION"] = $questionPageIndex;
				elseif (!$arResult["NAV"]["NEXT_QUESTION"] && $questionPageIndex > $arResult["NAV"]["PAGE_NUMBER"])
					$arResult["NAV"]["NEXT_QUESTION"] = $questionPageIndex;

				if ($arAttemptQuestion["ANSWERED"] == "N")
				{
					if (!$arResult["NAV"]["NEXT_NOANSWER"] && $questionPageIndex > $arResult["NAV"]["PAGE_NUMBER"])
						$arResult["NAV"]["NEXT_NOANSWER"] = $questionPageIndex;
					elseif ($questionPageIndex < $arResult["NAV"]["PAGE_NUMBER"])
						$arResult["NAV"]["PREV_NOANSWER"] = $questionPageIndex;
				}
			}

			$arResult["QBAR"][$questionPageIndex] = Array(
				"ID" => $arAttemptQuestion["ID"],
				"URL" => str_replace("#PAGE_ID#", $questionPageIndex, $arResult["PAGE_TEMPLATE"]),
				"ANSWERED" => $arAttemptQuestion["ANSWERED"],
				"QUESTION_ID" => $arAttemptQuestion["QUESTION_ID"],
				"RESPONSE" => explode(",",$arAttemptQuestion["RESPONSE"]),
				"INACCESSIBLE" => $inaccessible,
			);

			$questionPageIndex++;
		}

		//Pages
		if ($arResult["NAV"]["PREV_QUESTION"])
			$arResult["PREVIOUS_PAGE"] = str_replace("#PAGE_ID#", $arResult["NAV"]["PREV_QUESTION"], $arResult["PAGE_TEMPLATE"]);
		if ($arResult["NAV"]["NEXT_QUESTION"])
			$arResult["NEXT_PAGE"] = str_replace("#PAGE_ID#", $arResult["NAV"]["NEXT_QUESTION"], $arResult["PAGE_TEMPLATE"]);



		//$arResult["ACTION_PAGE"] = str_replace("#PAGE_ID#", $arResult["NAV"]["PAGE_NUMBER"] + 1, $arResult["PAGE_TEMPLATE"]);

		if (!empty($arResult["QBAR"]) && array_key_exists($arResult["NAV"]["PAGE_NUMBER"], $arResult["QBAR"]))
		{
			//If user get inaccessible question
			if ($arResult["QBAR"][$arResult["NAV"]["PAGE_NUMBER"]]["INACCESSIBLE"])
			{
				if ($arResult["NAV"]["NEXT_QUESTION"] || $arResult["NAV"]["PREV_QUESTION"] || $arResult["NAV"]["FIRST_NOANSWER"])
				{
					$page = (
						$arResult["NAV"]["NEXT_QUESTION"] ?
							$arResult["NAV"]["NEXT_QUESTION"] :
							(
								$arResult["NAV"]["FIRST_NOANSWER"] ?
								$arResult["NAV"]["FIRST_NOANSWER"] :
								$arResult["NAV"]["PREV_QUESTION"]
							)
					);

					LocalRedirect(
						str_replace("#PAGE_ID#", $page, $arResult["PAGE_TEMPLATE"])
					);
				}
			}

			if ($arResult["NAV"]["PAGE_NUMBER"] > floor($arParams["PAGE_WINDOW"]/2) + 1 && $arResult["NAV"]["PAGE_COUNT"] > $arParams["PAGE_WINDOW"])
				$arResult["NAV"]["START_PAGE"] = $arResult["NAV"]["PAGE_NUMBER"] - floor($arParams["PAGE_WINDOW"]/2);
			else
				$arResult["NAV"]["START_PAGE"] = 1;

			if (
				($arResult["NAV"]["PAGE_NUMBER"] <= $arResult["NAV"]["PAGE_COUNT"] - floor($arParams["PAGE_WINDOW"]/2) )
				&&($arResult["NAV"]["START_PAGE"] + $arParams["PAGE_WINDOW"]-1 <= $arResult["NAV"]["PAGE_COUNT"])
			)
			{

					$arResult["NAV"]["END_PAGE"] = $arResult["NAV"]["START_PAGE"] + $arParams["PAGE_WINDOW"] - 1;
			}
			else
			{
				$arResult["NAV"]["END_PAGE"] = $arResult["NAV"]["PAGE_COUNT"];
				if ( ($arResult["NAV"]["END_PAGE"] - $arParams["PAGE_WINDOW"] + 1) >= 1)
					$arResult["NAV"]["START_PAGE"] = $arResult["NAV"]["END_PAGE"] - $arParams["PAGE_WINDOW"] + 1;
			}


			$rsQuestion = CLQuestion::GetList(
				array(),
				array(
					"ID" => $arResult["QBAR"][$arResult["NAV"]["PAGE_NUMBER"]]["QUESTION_ID"],
					)
				);
			$arResult["QUESTION"] = $rsQuestion->GetNext();

			// Resolve links "?COURSE_ID={SELF}". Don't relay on it, this behaviour
			// can be changed in future without any notifications.
			if (isset($arResult["QUESTION"]['DESCRIPTION']))
			{
				$arResult["QUESTION"]['DESCRIPTION'] = CLearnHelper::PatchLessonContentLinks(
					$arResult["QUESTION"]['DESCRIPTION'],
					$arParams['COURSE_ID']
				);
			}

			$arResult["QUESTION"]["FILE"] = CFile::GetFileArray($arResult["QUESTION"]["FILE_ID"]);

			//Answers
			$arResult["QUESTION"]["ANSWERS"] = Array();

			$arSort = (
				$arTest["RANDOM_ANSWERS"] == "Y" || $arResult["QUESTION"]["QUESTION_TYPE"] == "R" ?
				Array("RAND" => "RAND", "SORT" => "ASC") :
				Array("SORT" => "ASC")
			);
			$rsAnswer = CLAnswer::GetList($arSort, Array("QUESTION_ID" => $arResult["QUESTION"]["ID"]));
			while($arAnswer = $rsAnswer->GetNext())
			{
				$arResult["QUESTION"]["ANSWERS"][] = $arAnswer;
			}
		}
	}

	$arResult["SAFE_REDIRECT_PAGE"] = htmlspecialcharsbx($arResult["REDIRECT_PAGE"]);
}
$linkedLessonId = CCourse::CourseGetLinkedLesson($arResult['TEST']['COURSE_ID']);
$bCanEdit = ($linkedLessonId !== false)
	&& (CLearnAccessMacroses::CanUserEditLesson(array('lesson_id' => $linkedLessonId)) || $USER->IsAdmin());
if ($bCanEdit)
{
	$deleteReturnUrl = "";
	if ($parent = $this->GetParent())
	{
		$deleteReturnUrl = CComponentEngine::MakePathFromTemplate($parent->arResult["URL_TEMPLATES"]["test.list"], Array("COURSE_ID" => $arResult["TEST"]["COURSE_ID"]));
	}

	$arAreaButtons = array(
		array(
			"TEXT" => GetMessage("LEARNING_COURSES_TEST_EDIT"),
			"TITLE" => GetMessage("LEARNING_COURSES_TEST_EDIT"),
			"URL" => 'javascript:'.$APPLICATION->GetPopupLink(
				array(
					"URL" => "/bitrix/admin/learn_test_edit.php?lang=".LANGUAGE_ID."&ID=".$arResult["TEST"]["ID"]."&COURSE_ID=".$arResult["TEST"]["COURSE_ID"]."&bxpublic=Y&from_module=learning",
					"PARAMS" => array(
						"width" => 700, 'height' => 500, 'resize' => false,
					),
				)
			),
			"ICON" => "bx-context-toolbar-edit-icon",
			"ID" => "bx-context-toolbar-edit-test",
		),

		array(
			"TEXT" => GetMessage("LEARNING_COURSES_TEST_DELETE"),
			"TITLE" => GetMessage("LEARNING_COURSES_TEST_DELETE"),
			"URL" => "javascript:if(confirm('".GetMessage("LEARNING_COURSES_TEST_DELETE_CONF")."'))jsUtils.Redirect([], '".CUtil::JSEscape("/bitrix/admin/learn_test_admin.php?ID=".$arParams["TEST_ID"]."&action=delete&lang=".LANGUAGE_ID."&".bitrix_sessid_get()."&COURSE_ID=".$arParams["COURSE_ID"]).($deleteReturnUrl <> ''? "&return_url=".urlencode($deleteReturnUrl) : "")."')",
			"ICON" => "bx-context-toolbar-delete-icon",
			"ID" => "bx-context-toolbar-delete-test",
		),

		array(
			"SEPARATOR" => "Y"
		),
	);

	if ($arResult["QUESTION"]["ID"])
	{
		array_unshift($arAreaButtons, array(
			"TEXT" => GetMessage("LEARNING_COURSES_QUESTION_EDIT"),
			"TITLE" => GetMessage("LEARNING_COURSES_QUESTION_EDIT"),
			"URL" => 'javascript:'.$APPLICATION->GetPopupLink(
				array(
					"URL" => "/bitrix/admin/learn_question_edit.php?lang=".LANGUAGE_ID."&ID=".$arResult["QUESTION"]["ID"]."&COURSE_ID=".$arResult["TEST"]["COURSE_ID"]."&LESSON_ID=".$arResult["QUESTION"]["LESSON_ID"]."&bxpublic=Y&from_module=learning",
					"PARAMS" => array(
						"width" => 700, 'height' => 500, 'resize' => false,
					),
				)
			),
			"ICON" => "bx-context-toolbar-edit-icon",
			"ID" => "bx-context-toolbar-edit-question",
		));
	}


	$this->AddIncludeAreaIcons($arAreaButtons);
}
$this->IncludeComponentTemplate();
?>
