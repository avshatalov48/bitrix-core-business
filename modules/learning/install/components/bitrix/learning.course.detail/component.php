<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if ( ! CModule::IncludeModule('learning') )
	return (false);

//Params
$arParams["CHECK_PERMISSIONS"] = (isset($arParams["CHECK_PERMISSIONS"]) && $arParams["CHECK_PERMISSIONS"]=="N" ? "N" : "Y");
$arParams["COURSE_ID"] = (isset($arParams["COURSE_ID"]) && intval($arParams["COURSE_ID"]) > 0 ? intval($arParams["COURSE_ID"]) : intval($_REQUEST["COURSE_ID"]));

// was: if($this->StartResultCache(false, $USER->GetGroups()))
if ($this->StartResultCache(false,  CLearnAccess::GetAccessSymbolsHashForSiteUser()))
{
	//Module
	if (!CModule::IncludeModule("learning"))
	{
		$this->AbortResultCache();
		ShowError(GetMessage("LEARNING_MODULE_NOT_FOUND"));
		return;
	}

	if ($arParams['CHECK_PERMISSIONS'] !== 'N')
	{
		$isAccessible = false;
		$linkedLessonId = CCourse::CourseGetLinkedLesson ($arParams["COURSE_ID"]);

		if ($linkedLessonId != false)
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

	//Course
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

	//Images
	$arCourse["PREVIEW_PICTURE_ARRAY"] = CFile::GetFileArray($arCourse["PREVIEW_PICTURE"]);

	// Resolve links "?COURSE_ID={SELF}". Don't relay on it, this behaviour 
	// can be changed in future without any notifications.
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

	$arResult = Array(
		"COURSE" => $arCourse
	);

	unset($rsCourse);
	unset($arCourse);

	$this->IncludeComponentTemplate();
}

//Set Title
$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y" );
if ($arParams["SET_TITLE"] == "Y")
	$APPLICATION->SetTitle($arResult["COURSE"]["NAME"]);


$linkedLessonId = CCourse::CourseGetLinkedLesson($arParams['COURSE_ID']);
$bCanEdit = ($linkedLessonId !== false) 
	&& (CLearnAccessMacroses::CanUserEditLesson(array('lesson_id' => $linkedLessonId)) || $USER->IsAdmin());

if ($bCanEdit)
{
	$arAreaButtons = array(
		array(
			"TEXT" => GetMessage("LEARNING_COURSES_COURSE_EDIT"),
			"TITLE" => GetMessage("LEARNING_COURSES_COURSE_EDIT"),
			"URL" => 'javascript:'.$APPLICATION->GetPopupLink(
				array(
					"URL" => "/bitrix/admin/learn_course_edit.php?COURSE_ID=".$arParams["COURSE_ID"]."&lang=".LANGUAGE_ID."&bxpublic=Y&from_module=learning",
					"PARAMS" => array(
						"width" => 700, 'height' => 500, 'resize' => false,
					),
				)
			),
			"ICON" => "bx-context-toolbar-edit-icon",
			"ID" => "bx-context-toolbar-edit-course",
		),
	);

	$this->AddIncludeAreaIcons($arAreaButtons);
}
