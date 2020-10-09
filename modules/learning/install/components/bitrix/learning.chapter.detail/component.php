<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if ( ! CModule::IncludeModule('learning') )
	return (false);

//Params
$arParams["COURSE_ID"] = (isset($arParams["COURSE_ID"]) && intval($arParams["COURSE_ID"]) > 0 ? intval($arParams["COURSE_ID"]) : intval($_REQUEST["COURSE_ID"]));
$arParams["SELF_TEST_TEMPLATE"] = ($arParams["SELF_TEST_TEMPLATE"] <> '' ? htmlspecialcharsbx($arParams["SELF_TEST_TEMPLATE"]) : "self.php?SELF_TEST_ID=#SELF_TEST_ID#");

$CHAPTER_ID = ((isset($arParams["CHAPTER_ID"]) && intval($arParams["CHAPTER_ID"]) > 0) ? $arParams["CHAPTER_ID"] : $_REQUEST["CHAPTER_ID"]);
if (CLearnPath::IsUrlencodedPath($CHAPTER_ID))
{
	$path = new CLearnPath();
	$path->ImportUrlencoded($CHAPTER_ID);
	$arParams['CHAPTER_ID'] = (int) $path->GetBottom();
}
elseif (mb_substr($CHAPTER_ID, 0, 1) === '0')
{
	$arParams['CHAPTER_ID'] = (int)mb_substr($CHAPTER_ID, 1);
}
else
{
	$arParams['CHAPTER_ID'] = (int) CLearnLesson::LessonIdByChapterId ($CHAPTER_ID);
}

if ( ! (isset($arParams['LESSON_PATH']) && mb_strlen($arParams['LESSON_PATH']) ) )
{
	$arParams['LESSON_PATH'] = '';

	if (isset($_REQUEST['LESSON_PATH']) && mb_strlen($_REQUEST['LESSON_PATH']))
		$arParams['LESSON_PATH'] = $_REQUEST['LESSON_PATH'];
}

$strUrlencodedLessonPath = '';
if ($arParams['LESSON_PATH'] <> '')
	$strUrlencodedLessonPath = 'LESSON_PATH=' . $arParams['LESSON_PATH'];



$arParams["CHAPTER_DETAIL_TEMPLATE"] = ($arParams["CHAPTER_DETAIL_TEMPLATE"] <> '' ? htmlspecialcharsbx($arParams["CHAPTER_DETAIL_TEMPLATE"]): "chapter.php?CHAPTER_ID=#CHAPTER_ID#");
$arParams["LESSON_DETAIL_TEMPLATE"] = ($arParams["LESSON_DETAIL_TEMPLATE"] <> '' ? htmlspecialcharsbx($arParams["LESSON_DETAIL_TEMPLATE"]) : "lesson.php?LESSON_ID=#LESSON_ID#");
$arParams["CHECK_PERMISSIONS"] = (isset($arParams["CHECK_PERMISSIONS"]) && $arParams["CHECK_PERMISSIONS"]=="N" ? "N" : "Y");

if ($arParams["CHECK_PERMISSIONS"] !== 'N')
{
	try
	{
		$arPermissionsParams = array(
			'COURSE_ID' => $arParams['COURSE_ID'],
			'LESSON_ID' => $arParams['CHAPTER_ID']
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
		exit();
	}
}

$ratingTransistor = '';
if ($arParams['CHAPTER_ID'] > 0)
{
	$arRatingData = CRatings::GetRatingVoteResult('LEARN_LESSON', $arParams['CHAPTER_ID']);
	$ratingTransistor = serialize($arRatingData);
}

$lastDirtyCacheTS = COption::GetOptionString(
	'learning', 
	CLearnCacheOfLessonTreeComponent::OPTION_TS, 
	time()
);

// was: if($this->StartResultCache(false, $USER->GetGroups()))
$additionalCacheID = CLearnAccess::GetAccessSymbolsHashForSiteUser() 
	. '|' . $ratingTransistor . '|' . $lastDirtyCacheTS;
if ($this->StartResultCache(false,  $additionalCacheID))
{
	//Module
	if (!CModule::IncludeModule("learning"))
	{
		$this->AbortResultCache();
		ShowError(GetMessage("LEARNING_MODULE_NOT_FOUND"));
		return;
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

	// Resolve links "?COURSE_ID={SELF}". Don't relay on it, this behaviour 
	// can be changed in future without any notifications.
	if (isset($arCourse['DETAIL_TEXT']))
	{
		$arCourse['DETAIL_TEXT'] = CLearnHelper::PatchLessonContentLinks(
			$arCourse['DETAIL_TEXT'],
			$arCourse['ID']
		);
	}

	if (isset($arCourse['PREVIEW_TEXT']))
	{
		$arCourse['PREVIEW_TEXT'] = CLearnHelper::PatchLessonContentLinks(
			$arCourse['PREVIEW_TEXT'],
			$arCourse['ID']
		);
	}

	$rsChapter = false;
	//Chapter
	if (isset($arParams["CHAPTER_ID"]))
	{
		$rsChapter = CLearnLesson::GetList(
			Array(),
			Array(
				"LESSON_ID" => $arParams["CHAPTER_ID"],
				//"WAS_CHAPTER_ID" => $arParams["CHAPTER_ID"],
				"ACTIVE" => "Y",
				"CHECK_PERMISSIONS" => 'N'
			)
		);
	}

	if (($rsChapter === false) || (!$arChapter = $rsChapter->GetNext()))
	{
		$this->AbortResultCache();
		ShowError(GetMessage("LEARNING_CHAPTER_DENIED"));
		return;
	}

	//Images
	$arChapter["PREVIEW_PICTURE_ARRAY"] = CFile::GetFileArray($arChapter["PREVIEW_PICTURE"]);
	$arChapter["DETAIL_PICTURE_ARRAY"] = CFile::GetFileArray($arChapter["DETAIL_PICTURE"]);

	// Resolve links "?COURSE_ID={SELF}". Don't relay on it, this behaviour 
	// can be changed in future without any notifications.
	if (isset($arChapter['DETAIL_TEXT']))
	{
		$arChapter['DETAIL_TEXT'] = CLearnHelper::PatchLessonContentLinks(
			$arChapter['DETAIL_TEXT'],
			$arParams['COURSE_ID']
		);
	}

	if (isset($arChapter['PREVIEW_TEXT']))
	{
		$arChapter['PREVIEW_TEXT'] = CLearnHelper::PatchLessonContentLinks(
			$arChapter['PREVIEW_TEXT'],
			$arParams['COURSE_ID']
		);
	}

	// Self test page URL
	$arChapter["SELF_TEST_URL"] = CComponentEngine::MakePathFromTemplate(
		$arParams["SELF_TEST_TEMPLATE"],
		Array(
			"LESSON_ID" => $arParams["CHAPTER_ID"],
			"SELF_TEST_ID" => $arParams["CHAPTER_ID"],
			"COURSE_ID" => $arParams["COURSE_ID"],
		)
	);

	//Self test exists?
	$rsQuestion = CLQuestion::GetList(
		Array(),
		Array(
			"LESSON_ID" => $arParams["CHAPTER_ID"],
			"ACTIVE" => "Y",
			"SELF" => "Y",
		)
	);

	$arChapter["SELF_TEST_EXISTS"] = (bool)($rsQuestion->Fetch());

	$arResult = Array(
		"COURSE" => $arCourse,
		"CHAPTER" => $arChapter,
		"CONTENTS" => Array()
	);


	//Included chapters and lessons
	$rsContent = CCourse::GetCourseContent($arParams["COURSE_ID"], Array());
	$foundChapter = false;
	while ($arContent = $rsContent->GetNext())
	{
		if ($foundChapter)
		{
			if ($arContent["DEPTH_LEVEL"] <= $baseDepthLevel)
				break;

			$arContent["DEPTH_LEVEL"] -= $baseDepthLevel;

			if ($arContent["TYPE"] == "CH")
				$arContent["URL"] = CComponentEngine::MakePathFromTemplate(
					$arParams["CHAPTER_DETAIL_TEMPLATE"],
					Array(
						"CHAPTER_ID" => '0' . $arContent["ID"],
						"COURSE_ID" => $arParams["COURSE_ID"]
					)
				);
			else
				$arContent["URL"] = CComponentEngine::MakePathFromTemplate(
					$arParams["LESSON_DETAIL_TEMPLATE"],
					Array(
						"LESSON_ID" => $arContent["ID"],
						"COURSE_ID" => $arParams["COURSE_ID"]
					)
				);

			$arResult["CONTENTS"][] = $arContent;
		}

		if ($arContent["ID"]==$arParams["CHAPTER_ID"] && $arContent["TYPE"]=="CH")
		{
			$foundChapter = true;
			$baseDepthLevel = $arContent["DEPTH_LEVEL"];
		}
	}
	
	global $CACHE_MANAGER;
	$CACHE_MANAGER->RegisterTag('LEARN_COURSE_'.$arCourse["ID"]);
	$CACHE_MANAGER->RegisterTag('LEARN_CHAPTER_'.$arChapter["ID"]);
	
	unset($rsContent, $arContent, 
		$rsCourse, $arCourse, 
		$rsChapter, $arChapter);

	$this->IncludeComponentTemplate();
}

//Set Title
$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y" );
if ($arParams["SET_TITLE"] == "Y")
	$APPLICATION->SetTitle($arResult["CHAPTER"]["NAME"]);

$bCanEdit = CLearnAccessMacroses::CanUserEditLesson(array('lesson_id' => $arParams['CHAPTER_ID'])) || $USER->IsAdmin();
if ($bCanEdit)
{
	$deleteReturnUrl = "";
	if ($parent = $this->GetParent())
	{
		$parentLessonId = 0;
		$lessonPath = "";
		if ($arParams["LESSON_PATH"] <> '')
		{
			$path = new CLearnPath();
			$path->ImportUrlencoded($arParams["LESSON_PATH"]);
			$path->PopBottom();
			$lessonPath = $path->ExportUrlencoded();
			$lessonId = $path->PopBottom();

			$edgesToParents = CLearnLesson::ListImmediateParents($arParams['CHAPTER_ID']);
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
			$deleteReturnUrl = CComponentEngine::MakePathFromTemplate($parent->arResult["FOLDER"].$parent->arResult["URL_TEMPLATES"]["chapter.detail"], Array("CHAPTER_ID" => "0".$parentLessonId, "COURSE_ID" => $arParams["COURSE_ID"]));
			$deleteReturnUrl .= mb_strpos($deleteReturnUrl, "?") !== false ? "&" : "?";
			$deleteReturnUrl .= "LESSON_PATH=".$lessonPath;
		}
		else
		{
			$deleteReturnUrl = CComponentEngine::MakePathFromTemplate($parent->arResult["FOLDER"].$parent->arResult["URL_TEMPLATES"]["course.detail"], Array("COURSE_ID" => $arParams["COURSE_ID"]));
		}
	}

	$arAreaButtons = array(
		array(
			"TEXT" => GetMessage("LEARNING_COURSES_CHAPTER_EDIT"),
			"TITLE" => GetMessage("LEARNING_COURSES_CHAPTER_EDIT"),
			"URL" => 'javascript:'.$APPLICATION->GetPopupLink(
				array(
					"URL" => "/bitrix/admin/learn_unilesson_edit.php"
						. "?LESSON_ID=" . $arParams["CHAPTER_ID"]
						. '&' . $strUrlencodedLessonPath
						. "&lang=" . LANGUAGE_ID 
						. "&COURSE_ID=" . $arParams["COURSE_ID"]
						. "&bxpublic=Y&from_module=learning",
					"PARAMS" => array(
						"width" => 700, 'height' => 500, 'resize' => false,
					),
				)
			),
			"ICON" => "bx-context-toolbar-edit-icon",
			"ID" => "bx-context-toolbar-edit-chapter",
		),

		array(
			"TEXT" => GetMessage("LEARNING_COURSES_CHAPTER_DELETE"),
			"TITLE" => GetMessage("LEARNING_COURSES_CHAPTER_DELETE"),
			"URL" => "javascript:if(confirm('".GetMessage("LEARNING_COURSES_CHAPTER_DELETE_CONF")."'))jsUtils.Redirect([], '".CUtil::JSEscape("/bitrix/admin/learn_unilesson_admin.php?ID=" . $arParams["CHAPTER_ID"] . "&action=delete&lang=".LANGUAGE_ID."&".bitrix_sessid_get()."&COURSE_ID=".$arParams["COURSE_ID"]).($deleteReturnUrl <> ''? "&return_url=".urlencode($deleteReturnUrl) : "")."')",
			"ICON" => "bx-context-toolbar-delete-icon",
			"ID" => "bx-context-toolbar-delete-chapter",
		),
	);

	$this->AddIncludeAreaIcons($arAreaButtons);
}
?>