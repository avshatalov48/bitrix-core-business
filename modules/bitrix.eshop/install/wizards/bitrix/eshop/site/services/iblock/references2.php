<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

if (!CModule::IncludeModule("highloadblock"))
	return;

if (!WIZARD_INSTALL_DEMO_DATA)
	return;

$COLOR_ID = $_SESSION["ESHOP_HBLOCK_COLOR_ID"];
unset($_SESSION["ESHOP_HBLOCK_COLOR_ID"]);

$BRAND_ID = $_SESSION["ESHOP_HBLOCK_BRAND_ID"];
unset($_SESSION["ESHOP_HBLOCK_BRAND_ID"]);

//adding rows
WizardServices::IncludeServiceLang("references.php", LANGUAGE_ID);

use Bitrix\Highloadblock as HL;
global $USER_FIELD_MANAGER;

if (intval($COLOR_ID) > 0)
{
	$hldata = HL\HighloadBlockTable::getById($COLOR_ID)->fetch();
	if (is_array($hldata))
	{
		$hlentity = HL\HighloadBlockTable::compileEntity($hldata);

		$entity_data_class = $hlentity->getDataClass();
		$arColors = array(
			"PURPLE" => "references_files/iblock/0d3/0d3ef035d0cf3b821449b0174980a712.jpg",
			"BROWN" => "references_files/iblock/f5a/f5a37106cb59ba069cc511647988eb89.jpg",
			"SEE" => "references_files/iblock/f01/f01f801e9da96ae5a7f26aae01255f38.jpg",
			"BLUE" => "references_files/iblock/c1b/c1ba082577379bdc75246974a9f08c8b.jpg",
			"ORANGERED" => "references_files/iblock/0ba/0ba3b7ecdef03a44b145e43aed0cca57.jpg",
			"REDBLUE" => "references_files/iblock/1ac/1ac0a26c5f47bd865a73da765484a2fa.jpg",
			"RED" => "references_files/iblock/0a7/0a7513671518b0f2ce5f7cf44a239a83.jpg",
			"GREEN" => "references_files/iblock/b1c/b1ced825c9803084eb4ea0a742b2342c.jpg",
			"WHITE" => "references_files/iblock/b0e/b0eeeaa3e7519e272b7b382e700cbbc3.jpg",
			"BLACK" => "references_files/iblock/d7b/d7bdba8aca8422e808fb3ad571a74c09.jpg",
			"PINK" => "references_files/iblock/1b6/1b61761da0adce93518a3d613292043a.jpg",
			"AZURE" => "references_files/iblock/c2b/c2b274ad2820451d780ee7cf08d74bb3.jpg",
			"JEANS" => "references_files/iblock/24b/24b082dc5e647a3a945bc9a5c0a200f0.jpg",
			"FLOWERS" => "references_files/iblock/64f/64f32941a654a1cbe2105febe7e77f33.jpg",
		);
		$sort = 0;
		foreach($arColors as $colorName=>$colorFile)
		{
			$sort+= 100;
			$arData = array(
				'UF_NAME' => GetMessage("WZD_REF_COLOR_".$colorName),
				'UF_FILE' =>
					array (
						'name' => ToLower($colorName).".jpg",
						'type' => 'image/jpeg',
						'tmp_name' => WIZARD_ABSOLUTE_PATH."/site/services/iblock/".$colorFile
					),
				'UF_SORT' => $sort,
				'UF_DEF' => ($sort > 100) ? "0" : "1",
				'UF_XML_ID' => ToLower($colorName)
			);
			$USER_FIELD_MANAGER->EditFormAddFields('HLBLOCK_'.$COLOR_ID, $arData);
			$USER_FIELD_MANAGER->checkFields('HLBLOCK_'.$COLOR_ID, null, $arData);

			$result = $entity_data_class::add($arData);
		}
	}
}

if (intval($BRAND_ID) > 0)
{
	$hldata = HL\HighloadBlockTable::getById($BRAND_ID)->fetch();
	if (is_array($hldata))
	{
		$hlentity = HL\HighloadBlockTable::compileEntity($hldata);

		$entity_data_class = $hlentity->getDataClass();
		$arBrands = array(
			"COMPANY1" => "brands_files/cm-01.png",
			"COMPANY2" => "brands_files/cm-02.png",
			"COMPANY3" => "brands_files/cm-03.png",
			"COMPANY4" => "brands_files/cm-04.png",
			"BRAND1" => "brands_files/bn-01.png",
			"BRAND2" => "brands_files/bn-02.png",
			"BRAND3" => "brands_files/bn-03.png",
		);
		$sort = 0;
		foreach($arBrands as $brandName=>$brandFile)
		{
			$sort+= 100;
			$arData = array(
				'UF_NAME' => GetMessage("WZD_REF_BRAND_".$brandName),
				'UF_FILE' =>
					array (
						'name' => ToLower($brandName).".png",
						'type' => 'image/png',
						'tmp_name' => WIZARD_ABSOLUTE_PATH."/site/services/iblock/".$brandFile
					),
				'UF_SORT' => $sort,
				'UF_DESCRIPTION' => GetMessage("WZD_REF_BRAND_DESCR_".$brandName),
				'UF_FULL_DESCRIPTION' => GetMessage("WZD_REF_BRAND_FULL_DESCR_".$brandName),
				'UF_XML_ID' => ToLower($brandName)
			);
			$USER_FIELD_MANAGER->EditFormAddFields('HLBLOCK_'.$BRAND_ID, $arData);
			$USER_FIELD_MANAGER->checkFields('HLBLOCK_'.$BRAND_ID, null, $arData);

			$result = $entity_data_class::add($arData);
		}
	}
}
?>