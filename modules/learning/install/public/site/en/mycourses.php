<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("");?>

<?$APPLICATION->IncludeComponent(
	"bitrix:learning.student.certificates",
	"",
	Array(
		"COURSE_DETAIL_TEMPLATE" => "course/index.php?COURSE_ID=#COURSE_ID#&INDEX=Y", 
		"TESTS_LIST_TEMPLATE" => "course/index.php?COURSE_ID=#COURSE_ID#&TEST_LIST=Y", 
		"SET_TITLE" => "Y" 
	)
);?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>