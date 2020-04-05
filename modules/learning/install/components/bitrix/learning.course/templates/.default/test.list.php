<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<table class="learn-work-table">
<tr>
	<td class="learn-left-data" valign="top">
	<?if (intval($arParams["COURSE_ID"]) > 0):?>
		<?$APPLICATION->IncludeComponent("bitrix:learning.course.tree", "", Array(
			"COURSE_ID"	=> $arParams["COURSE_ID"],
			"COURSE_DETAIL_TEMPLATE"	=> $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["course.detail"],
			"CHAPTER_DETAIL_TEMPLATE"	=> $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["chapter.detail"],
			"LESSON_DETAIL_TEMPLATE" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["lesson.detail"],
			"SELF_TEST_TEMPLATE"	=> $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["test.self"],
			"TESTS_LIST_TEMPLATE"	=> $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["test.list"],
			"TEST_DETAIL_TEMPLATE"	=> $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["test"],
			"CHECK_PERMISSIONS"	=> $arParams["CHECK_PERMISSIONS"],
			'LEARNING_GROUP_ACTIVE_FROM'          => $arResult['LEARNING_GROUP_ACTIVE_FROM'],
			'LEARNING_GROUP_ACTIVE_TO'            => $arResult['LEARNING_GROUP_ACTIVE_TO'],
			'LEARNING_GROUP_CHAPTERS_ACTIVE_FROM' => $arResult['LEARNING_GROUP_CHAPTERS_ACTIVE_FROM'],
			"SET_TITLE"	=> $arParams["SET_TITLE"]
			),
			$component
		);?>
	<?endif?>

	</td>

	<td class="learn-right-data" valign="top">

		<?$APPLICATION->IncludeComponent("bitrix:learning.test.list", "", Array(
			"COURSE_ID"	=>$arParams["COURSE_ID"],
			"TEST_DETAIL_TEMPLATE"	=> $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["test"],
			"CHECK_PERMISSIONS"	=> $arParams["CHECK_PERMISSIONS"],
			"SET_TITLE" => $arParams["SET_TITLE"],
			"TESTS_PER_PAGE"	=> $arParams["TESTS_PER_PAGE"],
			),
			$component
		);?>

	<?if (intval($arParams["COURSE_ID"]) > 0):?>
		<br /><br />
		<?$APPLICATION->IncludeComponent("bitrix:learning.course.tree", "navigation", Array(
			"COURSE_ID"	=> $arParams["COURSE_ID"],
			"COURSE_DETAIL_TEMPLATE"	=> $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["course.detail"],
			"CHAPTER_DETAIL_TEMPLATE"	=> $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["chapter.detail"],
			"LESSON_DETAIL_TEMPLATE" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["lesson.detail"],
			"SELF_TEST_TEMPLATE"	=> $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["test.self"],
			"TESTS_LIST_TEMPLATE"	=> $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["test.list"],
			"TEST_DETAIL_TEMPLATE"	=> $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["test"],
			"CHECK_PERMISSIONS"	=> $arParams["CHECK_PERMISSIONS"],
			'LEARNING_GROUP_ACTIVE_FROM'          => $arResult['LEARNING_GROUP_ACTIVE_FROM'],
			'LEARNING_GROUP_ACTIVE_TO'            => $arResult['LEARNING_GROUP_ACTIVE_TO'],
			'LEARNING_GROUP_CHAPTERS_ACTIVE_FROM' => $arResult['LEARNING_GROUP_CHAPTERS_ACTIVE_FROM'],
			"SET_TITLE" => "N",
			),
			$component
		);?>
	<?endif?>
	</td>

</tr>
</table>

