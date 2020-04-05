<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<table class="learn-work-table">
	<tr>
		<td class="learn-left-data" valign="top">
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
		</td>
		<td class="learn-right-data" valign="top">
			<?$APPLICATION->IncludeComponent("bitrix:learning.search", ".default", array(
					"PAGE_RESULT_COUNT" => "10",
					"DISPLAY_TOP_PAGER" => "Y",
					"DISPLAY_BOTTOM_PAGER" => "Y",
					"COURSE_ID"	=> $arParams["COURSE_ID"],
				),
				$component
			);?>
		</td>
	</tr>
</table>

