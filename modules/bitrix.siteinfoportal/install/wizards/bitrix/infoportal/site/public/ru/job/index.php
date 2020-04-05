<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Работа");
?>
<table> 
  <tbody>
    <tr> <td><?$APPLICATION->IncludeComponent("bitrix:catalog.section.list", "job", array(
	"IBLOCK_TYPE" => "job",
	"IBLOCK_ID" => "#VACANCY_IBLOCK_ID#",
	"SECTION_ID" => $_REQUEST["SECTION_ID"],
	"SECTION_CODE" => "",
	"COUNT_ELEMENTS" => "Y",
	"TOP_DEPTH" => "2",
	"SECTION_FIELDS" => array(
		0 => "",
		1 => "",
	),
	"SECTION_USER_FIELDS" => array(
		0 => "",
		1 => "",
	),
	"SECTION_URL" => "",
	"CACHE_TYPE" => "A",
	"CACHE_TIME" => "36000000",
	"CACHE_GROUPS" => "N",
	"ADD_SECTIONS_CHAIN" => "Y",
	"IBLOCK_TITLE_TEXT" => "Вакансии"
	),
	false
);?> </td> <td><?$APPLICATION->IncludeComponent("bitrix:catalog.section.list", "job", array(
	"IBLOCK_TYPE" => "job",
	"IBLOCK_ID" => "#RESUME_IBLOCK_ID#",
	"SECTION_ID" => $_REQUEST["SECTION_ID"],
	"SECTION_CODE" => "",
	"COUNT_ELEMENTS" => "Y",
	"TOP_DEPTH" => "2",
	"SECTION_FIELDS" => array(
		0 => "",
		1 => "",
	),
	"SECTION_USER_FIELDS" => array(
		0 => "",
		1 => "",
	),
	"SECTION_URL" => "",
	"CACHE_TYPE" => "A",
	"CACHE_TIME" => "36000000",
	"CACHE_GROUPS" => "N",
	"ADD_SECTIONS_CHAIN" => "Y",
	"IBLOCK_TITLE_TEXT" => "Резюме"
	),
	false
);?></td> </tr>
   </tbody>
</table>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>