<?
use Bitrix\Main\Localization\Loc;
use Bitrix\MobileApp\Data\Lists;

define("NOT_CHECK_PERMISSIONS", true);
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
\Bitrix\Main\Loader::includeModule("mobileapp");
$list = new Lists();
Loc::loadLanguageFile(__FILE__);
$listType = $_REQUEST["listType"];

switch ($listType)
{
	case "recursive":
		$nextTableUrl = "/bitrix/components/bitrix/mobileapp.demoapi/templates/.default/data/list.php?listType=recursive";
		$list->addItems("elements", array(
			array(
				"ID" => 1,
				"IMAGE"=>"/bitrix/components/bitrix/mobileapp.demoapi/templates/.default/img/attach-2-icon.png",
				"TABLE_URL" => $nextTableUrl,
				"NAME" => GetMessage("MB_DEMO_OPEN_LIST_AGAIN"),
				"TAGS" => GetMessage("MB_DEMO_LIST_ELEMENT_DETAIL")
			)
		));
		$list->setCategoryName("elements", "elements");
		break;
	case "simple_alphabet":
		$list->addItems("elements", array(
			array(
				"ID" => 1,
				"IMAGE"=>"/bitrix/components/bitrix/mobileapp.demoapi/templates/.default/img/addition-icon.png",
				"NAME" => "A ".GetMessage("MB_DEMO_LIST_ELEMENT") . "1",
				"TAGS" => GetMessage("MB_DEMO_LIST_ELEMENT_DETAIL")
			),
			array(
				"ID" => 2,
				"IMAGE"=>"/bitrix/components/bitrix/mobileapp.demoapi/templates/.default/img/addition-icon.png",
				"NAME" => "B ".GetMessage("MB_DEMO_LIST_ELEMENT") . "2",
				"TAGS" => GetMessage("MB_DEMO_LIST_ELEMENT_DETAIL"),
				"URL" => "http://bitrix.ru"
			),
			array(
				"ID" => 3,
				"IMAGE"=>"/bitrix/components/bitrix/mobileapp.demoapi/templates/.default/img/addition-icon.png",
				"NAME" => "C ".GetMessage("MB_DEMO_LIST_ELEMENT") . "3",
				"TAGS" => GetMessage("MB_DEMO_LIST_ELEMENT_DETAIL"),
				"URL" => "http://www.digitalworkplace.ru"
			)
		));
		$list->setCategoryName("elements", "elements");
		break;
	case "simple":
		$list->addItems("elements", array(
			array(
				"ID" => 1,
				"IMAGE"=>"/bitrix/components/bitrix/mobileapp.demoapi/templates/.default/img/laboratory-icon.png",
				"NAME" => GetMessage("MB_DEMO_LIST_ELEMENT") . "1",
				"TAGS" => GetMessage("MB_DEMO_LIST_ELEMENT_DETAIL")
			),
			array(
				"ID" => 2,
				"IMAGE"=>"/bitrix/components/bitrix/mobileapp.demoapi/templates/.default/img/laboratory-icon.png",
				"NAME" => GetMessage("MB_DEMO_LIST_ELEMENT") . "2",
				"TAGS" => GetMessage("MB_DEMO_LIST_ELEMENT_DETAIL"),
				"URL" => "http://bitrix.ru"
			),
			array(
				"ID" => 3,
				"IMAGE"=>"/bitrix/components/bitrix/mobileapp.demoapi/templates/.default/img/laboratory-icon.png",
				"NAME" => GetMessage("MB_DEMO_LIST_ELEMENT") . "3",
				"TAGS" => GetMessage("MB_DEMO_LIST_ELEMENT_DETAIL"),
				"URL" => "http://www.digitalworkplace.ru"
			)
		));
		$list->setCategoryName("elements", "elements");
		break;
	case "sections":
		$sections = array("SECTION_ONE", "SECTION_TWO");
		$countInSection = 4;
		$globalCounter = 1;
		$sectionNumber = 1;
		foreach ($sections as $section)
		{
			for ($i = 0; $i < $countInSection; $i++)
			{
				$list->addItem("category", array(
					"ID" => $section . "_element_" . $i,
					"IMAGE"=>"/bitrix/components/bitrix/mobileapp.demoapi/templates/.default/img/".$sectionNumber.".png",
					"NAME" => GetMessage("MB_DEMO_LIST_ELEMENT") . $globalCounter,
					"TAGS" => GetMessage("MB_DEMO_LIST_ELEMENT_DETAIL"),
					"URL" => "http://bitrix.ru",
					"SECTION_ID" => $section,
				));
				$globalCounter++;
			}
			$list->addSection("category", $section, GetMessage("MB_DEMO_".$section));

			$sectionNumber++;
		}

		$list->setCategoryName("category", "category");

		break;
	default:

}

$list->showJSON();

die(); 