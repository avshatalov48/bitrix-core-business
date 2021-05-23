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

	//Course
	$rsCourse = CCourse::GetList(Array(),
		Array(
			"ID" => $arParams["COURSE_ID"],
			"ACTIVE" => "Y",
			"ACTIVE_DATE" => "Y",
			"SITE_ID" => LANG,
			"CHECK_PERMISSIONS" => 'N'
		)
	);

	if(!$arCourse = $rsCourse->GetNext())
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

	//arResult
	$arResult = Array(
		"COURSE" => $arCourse,
		"CONTENTS" => Array(),
	);

	$rsContent = CCourse::GetCourseContent($arParams["COURSE_ID"], Array("DETAIL_TEXT", "DETAIL_TEXT_TYPE", "DETAIL_PICTURE", "PREVIEW_PICTURE"));

	while ($arContent = $rsContent->GetNext())
	{
		$arContent["DETAIL_PICTURE_ARRAY"] = CFile::GetFileArray($arContent["DETAIL_PICTURE"]);
		$arContent["PREVIEW_PICTURE_ARRAY"] = CFile::GetFileArray($arContent["PREVIEW_PICTURE"]);

		// Resolve links "?COURSE_ID={SELF}". Don't relay on it, this behaviour 
		// can be changed in future without any nitifications.
		if (isset($arContent['DETAIL_TEXT']))
		{
			$arContent['DETAIL_TEXT'] = CLearnHelper::PatchLessonContentLinks(
				$arContent['DETAIL_TEXT'],
				$arParams["COURSE_ID"]
			);
		}

		if (isset($arContent['PREVIEW_TEXT']))
		{
			$arContent['PREVIEW_TEXT'] = CLearnHelper::PatchLessonContentLinks(
				$arContent['PREVIEW_TEXT'],
				$arParams["COURSE_ID"]
			);
		}

		$arResult["CONTENTS"][] = $arContent;
	}

	unset($rsContent);
	unset($arContent);

	$APPLICATION->AddHeadScript('/bitrix/js/learning/scorm.js');
	$this->IncludeComponentTemplate();
}

//Set Title
$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y" );
if ($arParams["SET_TITLE"] == "Y")
	$APPLICATION->SetTitle($arResult["COURSE"]["NAME"]);
?>