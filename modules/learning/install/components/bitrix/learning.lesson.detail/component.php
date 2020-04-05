<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if ( ! CModule::IncludeModule('learning') )
	return (false);

//Params
$arParams["COURSE_ID"] = (isset($arParams["COURSE_ID"]) && intval($arParams["COURSE_ID"]) > 0 ? intval($arParams["COURSE_ID"]) : intval($_REQUEST["COURSE_ID"]));
$arParams["LESSON_ID"] = (isset($arParams["LESSON_ID"]) && intval($arParams["LESSON_ID"]) > 0 ? intval($arParams["LESSON_ID"]) : intval($_REQUEST["LESSON_ID"]));
$arParams["SELF_TEST_TEMPLATE"] = (strlen($arParams["SELF_TEST_TEMPLATE"]) > 0 ? htmlspecialcharsbx($arParams["SELF_TEST_TEMPLATE"]) : "self.php?SELF_TEST_ID=#SELF_TEST_ID#");
$arParams["CHECK_PERMISSIONS"] = (isset($arParams["CHECK_PERMISSIONS"]) && $arParams["CHECK_PERMISSIONS"]=="N" ? "N" : "Y");

if ( ! ( isset($arParams['LESSON_PATH']) && strlen($arParams['LESSON_PATH']) ) )
{
	$arParams['LESSON_PATH'] = '';

	if (isset($_REQUEST['LESSON_PATH']) && strlen($_REQUEST['LESSON_PATH']))
		$arParams['LESSON_PATH'] = $_REQUEST['LESSON_PATH'];
}

$strUrlencodedLessonPath = '';
if (strlen($arParams['LESSON_PATH']) > 0)
	$strUrlencodedLessonPath = 'LESSON_PATH=' . $arParams['LESSON_PATH'];

$ratingTransistor = '';
if ($arParams['LESSON_ID'] > 0)
{
	$arRatingData = CRatings::GetRatingVoteResult('LEARN_LESSON', $arParams['LESSON_ID']);
	$ratingTransistor = serialize($arRatingData);
}

$delayed = false;
$courseLessonId = CCourse::CourseGetLinkedLesson($arParams['COURSE_ID']);
$arGroupsPeriods = CLearnAccessMacroses::getActiveLearningGroupsPeriod($courseLessonId, $USER->getId());
if ($arGroupsPeriods['IS_EXISTS'])
{
	$arResult['LEARNING_GROUP_ACTIVE_FROM'] = $arGroupsPeriods['ACTIVE_FROM'];
	$arResult['LEARNING_GROUP_ACTIVE_TO'] = $arGroupsPeriods['ACTIVE_TO'];

	$activeFromMap = CLearnAccessMacroses::getActiveLearningChaptersPeriod($courseLessonId, $USER->getId());
	if ($activeFromMap !== false)
		$arResult['LEARNING_GROUP_CHAPTERS_ACTIVE_FROM'] = $activeFromMap;

	$oPath = new CLearnPath();
	$oPath->ImportUrlencoded ($arParams['LESSON_PATH']);
	$arPath = $oPath->GetPathAsArray();
	if (count($arPath) >= 2)
	{
		$secondLevelLesson = $arPath[1];
		if (isset($arResult['LEARNING_GROUP_CHAPTERS_ACTIVE_FROM'][$secondLevelLesson]))
		{
			$activeFrom = $arResult['LEARNING_GROUP_CHAPTERS_ACTIVE_FROM'][$secondLevelLesson];

			if (time() < MakeTimeStamp($activeFrom))
			{
				$delayed = $activeFrom;
			}
		}
	}
}

$lastDirtyCacheTS = COption::GetOptionString(
	'learning', 
	CLearnCacheOfLessonTreeComponent::OPTION_TS, 
	time()
);

// was: if($this->StartResultCache(false, $USER->GetGroups()))
$additionalCacheID = CLearnAccess::GetAccessSymbolsHashForSiteUser() 
	. '|' . $ratingTransistor . '|' . $lastDirtyCacheTS . '|' . ($delayed === false ? 'ND' : 'D');
if ($this->StartResultCache(false,  $additionalCacheID))
{
	// Module
	if (!CModule::IncludeModule("learning"))
	{
		$this->AbortResultCache();
		ShowError(GetMessage("LEARNING_MODULE_NOT_FOUND"));
		return;
	}
	
	if ($arParams["CHECK_PERMISSIONS"] !== 'N')
	{
		try
		{
			$arPermissionsParams = array(
				'COURSE_ID' => $arParams['COURSE_ID'],
				'LESSON_ID' => $arParams['LESSON_ID']
				);

			$isAccessible = CLearnAccessMacroses::CanUserViewLessonAsPublic ($arPermissionsParams);
		}
		catch (Exception $e)
		{
			$isAccessible = false;	// access denied
		}

		if ( ! $isAccessible )
		{
			ShowError(GetMessage('LEARNING_COURSE_DENIED'));
			return;
		}
	}

	// Course
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
		$this->AbortResultCache();
		ShowError(GetMessage("LEARNING_COURSE_DENIED"));
		return;
	}

	// Resolve links "?COURSE_ID={SELF}". Don't relay on it, this behaviour 
	// can be changed in future without any notifications.
	if (isset($arCourse['DETAIL_TEXT']))
	{
		$arCourse['DETAIL_TEXT'] = CLearnHelper::PatchLessonContentLinks(
			$arCourse['DETAIL_TEXT'],
			$arParams['COURSE_ID']
		);
	}

	if (isset($arCourse['PREVIEW_TEXT']))
	{
		$arCourse['PREVIEW_TEXT'] = CLearnHelper::PatchLessonContentLinks(
			$arCourse['PREVIEW_TEXT'],
			$arParams['COURSE_ID']
		);
	}
	
	// Lesson
	$rsLesson = CLearnLesson::GetList(
		array(),
		array(
			'LESSON_ID'         => $arParams['LESSON_ID'],
			'ACTIVE'            => 'Y',
			'CHECK_PERMISSIONS' => 'N'
		)
	);

	if (!$arLesson = $rsLesson->GetNext())
	{
		$this->AbortResultCache();
		ShowError(GetMessage("LEARNING_LESSON_DENIED"));
		return;
	}

	// Images
	$arLesson["PREVIEW_PICTURE_ARRAY"] = CFile::GetFileArray($arLesson["PREVIEW_PICTURE"]);
	$arLesson["DETAIL_PICTURE_ARRAY"] = CFile::GetFileArray($arLesson["DETAIL_PICTURE"]);

	// Resolve links "?COURSE_ID={SELF}". Don't relay on it, this behaviour 
	// can be changed in future without any notifications.
	if (isset($arLesson['DETAIL_TEXT']))
	{
		$arLesson['DETAIL_TEXT'] = CLearnHelper::PatchLessonContentLinks(
			$arLesson['DETAIL_TEXT'],
			$arParams['COURSE_ID']
		);
	}

	if (isset($arLesson['PREVIEW_TEXT']))
	{
		$arLesson['PREVIEW_TEXT'] = CLearnHelper::PatchLessonContentLinks(
			$arLesson['PREVIEW_TEXT'],
			$arParams['COURSE_ID']
		);
	}

	// Self test page URL
	$arLesson["SELF_TEST_URL"] = CComponentEngine::MakePathFromTemplate(
		$arParams["SELF_TEST_TEMPLATE"],
		Array(
			"LESSON_ID" => $arParams["LESSON_ID"],
			"SELF_TEST_ID" => $arParams["LESSON_ID"],
			"COURSE_ID" => $arParams["COURSE_ID"],
		)
	);

	//Self test exists?
	$rsQuestion = CLQuestion::GetList(
		Array(),
		Array(
			"LESSON_ID" => $arParams["LESSON_ID"],
			"ACTIVE" => "Y",
			"SELF" => "Y",
		)
	);

	$arLesson["SELF_TEST_EXISTS"] = (bool)($rsQuestion->Fetch());
	$urlInfo = parse_url($arLesson["LAUNCH"]);
	$path = $_SERVER["DOCUMENT_ROOT"].$urlInfo["path"];
	if ($arLesson["DETAIL_TEXT_TYPE"] == "file" && !file_exists($path))
	{
		$arLesson["LAUNCH"] = "";
	}

	if ($delayed === false)
	{
		$arResult = array(
			'DELAYED' => $delayed,
			"COURSE"  => $arCourse,
			"LESSON"  => $arLesson
		);
	}
	else
	{
		$arResult = array(
			'DELAYED' => $delayed,
			"COURSE"  => $arCourse,
			'LESSON' => null,
			"DELAYED_LESSON" => $arLesson
		);
	}

	global $CACHE_MANAGER;
	$CACHE_MANAGER->RegisterTag('LEARN_COURSE_'.$arCourse["ID"]);
	$CACHE_MANAGER->RegisterTag('LEARN_LESSON_'.$arLesson["ID"]);
	
	unset($arLesson);
	unset($rsLesson);
	unset($arCourse);
	unset($rsCourse);
	unset($rsQuestion);

	$APPLICATION->AddHeadScript('/bitrix/js/learning/scorm.js');
	$this->IncludeComponentTemplate();
}

//Set Title
$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y" );
if ($arParams["SET_TITLE"] == "Y")
	$APPLICATION->SetTitle($arResult["LESSON"]["NAME"]);

$bCanUserEdit   = CLearnAccessMacroses::CanUserEditLesson(array('lesson_id' => $arParams['LESSON_ID'])) || $USER->IsAdmin();
$bCanUserRemove = CLearnAccessMacroses::CanUserRemoveLesson(array('lesson_id' => $arParams['LESSON_ID'])) || $USER->IsAdmin();

if ($bCanUserEdit || $bCanUserRemove)
{
	$arAreaButtons = array();
	$deleteReturnUrl = "";
	if ($bCanUserRemove)
	{
		if ($parent = $this->GetParent())
		{
			$parentLessonId = 0;
			$lessonPath = "";
			if (strlen($arParams["LESSON_PATH"]) > 0)
			{
				$path = new CLearnPath();
				$path->ImportUrlencoded($arParams["LESSON_PATH"]);
				$path->PopBottom();
				$lessonPath = $path->ExportUrlencoded();
				$lessonId = $path->PopBottom();

				$edgesToParents = CLearnLesson::ListImmediateParents($arParams["LESSON_ID"]);
				foreach ($edgesToParents as $arEdgeToParent)
				{
					if ( (int) $arEdgeToParent['PARENT_LESSON'] === (int) $lessonId )
					{
						$parentLessonId = $lessonId;
						break;
					}
				}
			}

			if ($parentLessonId)
			{
				$deleteReturnUrl = CComponentEngine::MakePathFromTemplate($parent->arResult["FOLDER"].$parent->arResult["URL_TEMPLATES"]["chapter.detail"], Array("CHAPTER_ID" => "0".$parentLessonId,"COURSE_ID" => $arParams['COURSE_ID']));
				$deleteReturnUrl .= strpos($deleteReturnUrl, "?") !== false ? "&" : "?";
				$deleteReturnUrl .= "LESSON_PATH=".$lessonPath;
			}
			else
			{
				$deleteReturnUrl = CComponentEngine::MakePathFromTemplate($parent->arResult["FOLDER"].$parent->arResult["URL_TEMPLATES"]["course.detail"], Array("COURSE_ID" => $arParams['COURSE_ID']));
			}
		}

		$arAreaButtons[] = array(
				"TEXT" => GetMessage("LEARNING_COURSES_LESSON_EDIT"),
				"TITLE" => GetMessage("LEARNING_COURSES_LESSON_EDIT"),
				"URL" => 'javascript:'.$APPLICATION->GetPopupLink(
					array(
						"URL" => "/bitrix/admin/learn_unilesson_edit.php?LESSON_ID=" . $arParams["LESSON_ID"]
							. '&' . $strUrlencodedLessonPath
							. "&lang=" . LANGUAGE_ID . "&bxpublic=Y&from_module=learning",
						"PARAMS" => array(
							"width" => 700, 'height' => 500, 'resize' => true,
						),
					)
				),
				"ICON" => "bx-context-toolbar-edit-icon",
				"ID" => "bx-context-toolbar-edit-lesson",
			);
	}

	if ($bCanUserEdit)
	{
		$arAreaButtons[] = array(
				"TEXT" => GetMessage("LEARNING_COURSES_LESSON_DELETE"),
				"TITLE" => GetMessage("LEARNING_COURSES_LESSON_DELETE"),
				"URL" => "javascript:if(confirm('".GetMessage("LEARNING_COURSES_LESSON_DELETE_CONF")."'))jsUtils.Redirect([], '".CUtil::JSEscape("/bitrix/admin/learn_unilesson_admin.php?ID=".$arParams["LESSON_ID"]."&action=delete&lang=".LANGUAGE_ID."&".bitrix_sessid_get()."&COURSE_ID=".$arParams["COURSE_ID"]).(strlen($deleteReturnUrl) ? "&return_url=".urlencode($deleteReturnUrl) : "")."')",
				"ICON" => "bx-context-toolbar-delete-icon",
				"ID" => "bx-context-toolbar-delete-lesson",
			);
	}

	$this->AddIncludeAreaIcons($arAreaButtons);
}



