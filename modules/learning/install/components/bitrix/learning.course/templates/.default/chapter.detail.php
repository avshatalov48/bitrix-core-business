<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<table class="learn-work-table">
<tr>
	<td class="learn-left-data" valign="top">

	<?if (isset($arParams["COURSE_ID"]) && intval($arParams["COURSE_ID"]) > 0):?>

		<?$APPLICATION->IncludeComponent("bitrix:learning.course.tree", "", Array(
			"COURSE_ID"	=> $arParams["COURSE_ID"],
			"COURSE_DETAIL_TEMPLATE"	=> $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["course.detail"] ?? '',
			"CHAPTER_DETAIL_TEMPLATE"	=> $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["chapter.detail"] ?? '',
			"LESSON_DETAIL_TEMPLATE" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["lesson.detail"] ?? '',
			"SELF_TEST_TEMPLATE"	=> $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["test.self"] ?? '',
			"TESTS_LIST_TEMPLATE"	=> $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["test.list"] ?? '',
			"TEST_DETAIL_TEMPLATE"	=> $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["test"] ?? '',
			"CHECK_PERMISSIONS"	=> $arParams["CHECK_PERMISSIONS"] ?? null,
			"SET_TITLE"	=> $arParams["SET_TITLE"] ?? '',
			'LEARNING_GROUP_ACTIVE_FROM'          => $arResult['LEARNING_GROUP_ACTIVE_FROM'] ?? null,
			'LEARNING_GROUP_ACTIVE_TO'            => $arResult['LEARNING_GROUP_ACTIVE_TO'] ?? null,
			'LEARNING_GROUP_CHAPTERS_ACTIVE_FROM' => $arResult['LEARNING_GROUP_CHAPTERS_ACTIVE_FROM'] ?? null,
			),
			$component
		);?>

	<?endif?>

	</td>

	<td class="learn-right-data" valign="top">

		<?$APPLICATION->IncludeComponent("bitrix:learning.chapter.detail", "", Array(
			"CHAPTER_ID"	=> $arResult["VARIABLES"]["CHAPTER_ID"] ?? 0,
			"COURSE_ID"	=> $arParams["COURSE_ID"] ?? 0,
			"CHAPTER_DETAIL_TEMPLATE"	=> $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["chapter.detail"] ?? '',
			"LESSON_DETAIL_TEMPLATE"	=>$arResult["FOLDER"].$arResult["URL_TEMPLATES"]["lesson.detail"] ?? '',
			"SELF_TEST_TEMPLATE"	=> $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["test.self"] ?? '',
			"CHECK_PERMISSIONS" => $arParams["CHECK_PERMISSIONS"] ?? null,
			"SET_TITLE" => $arParams["SET_TITLE"] ?? '',
			"CACHE_TYPE" => $arParams["CACHE_TYPE"] ?? null,
			"CACHE_TIME" => $arParams["CACHE_TIME"] ?? null,
			"PATH_TO_USER_PROFILE" => $arParams["PATH_TO_USER_PROFILE"] ?? '',
			),
			$component
		);?>

	<?if (intval($arParams["COURSE_ID"]) > 0):?>
		<br /><br />
		<?$APPLICATION->IncludeComponent("bitrix:learning.course.tree", "navigation", Array(
			"COURSE_ID"	=> $arParams["COURSE_ID"] ?? 0,
			"COURSE_DETAIL_TEMPLATE"	=> $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["course.detail"] ?? '',
			"CHAPTER_DETAIL_TEMPLATE"	=> $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["chapter.detail"] ?? '',
			"LESSON_DETAIL_TEMPLATE" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["lesson.detail"] ?? '',
			"SELF_TEST_TEMPLATE"	=> $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["test.self"] ?? '',
			"TESTS_LIST_TEMPLATE"	=> $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["test.list"] ?? '',
			"TEST_DETAIL_TEMPLATE"	=> $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["test"] ?? '',
			'LEARNING_GROUP_ACTIVE_FROM'          => $arResult['LEARNING_GROUP_ACTIVE_FROM'] ?? null,
			'LEARNING_GROUP_ACTIVE_TO'            => $arResult['LEARNING_GROUP_ACTIVE_TO'] ?? null,
			'LEARNING_GROUP_CHAPTERS_ACTIVE_FROM' => $arResult['LEARNING_GROUP_CHAPTERS_ACTIVE_FROM'] ?? null,
			"CHECK_PERMISSIONS"	=> $arParams["CHECK_PERMISSIONS"] ?? null,
			"SET_TITLE" => "N",
			),
			$component
		);?>

	<?endif?>

	</td>

</tr>
</table>
