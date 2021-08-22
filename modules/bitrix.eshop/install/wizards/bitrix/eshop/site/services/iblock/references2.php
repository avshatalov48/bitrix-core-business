<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

use Bitrix\Main\Loader;
use Bitrix\Main\Type\Collection;
use Bitrix\Highloadblock as HL;

global $USER_FIELD_MANAGER;

if (!Loader::includeModule("highloadblock"))
	return;

if (!WIZARD_INSTALL_DEMO_DATA)
	return;

$COLOR_ID = (int)$_SESSION["ESHOP_HBLOCK_COLOR_ID"];
unset($_SESSION["ESHOP_HBLOCK_COLOR_ID"]);

$BRAND_ID = (int)$_SESSION["ESHOP_HBLOCK_BRAND_ID"];
unset($_SESSION["ESHOP_HBLOCK_BRAND_ID"]);

//adding rows
WizardServices::IncludeServiceLang("references.php", LANGUAGE_ID);

if ($COLOR_ID > 0)
{
	$hldata = HL\HighloadBlockTable::getById($COLOR_ID)->fetch();
	if (is_array($hldata))
	{
		$hlentity = HL\HighloadBlockTable::compileEntity($hldata);

		$entity_data_class = $hlentity->getDataClass();

		$colors = [];
		$colors['PURPLE'] = [
			'XML_ID' => 'purple',
			'PATH' => 'references_files/iblock/0d3/0d3ef035d0cf3b821449b0174980a712.jpg',
			'FILE_NAME' => 'purple.jpg',
			'FILE_TYPE' => 'image/jpeg',
			'TITLE' => ''
		];
		$colors['BROWN'] = [
			'XML_ID' => 'brown',
			'PATH' => 'references_files/iblock/f5a/f5a37106cb59ba069cc511647988eb89.jpg',
			'FILE_NAME' => 'brown.jpg',
			'FILE_TYPE' => 'image/jpeg',
			'TITLE' => ''
		];
		$colors['SEE'] = [
			'XML_ID' => 'see',
			'PATH' => 'references_files/iblock/f01/f01f801e9da96ae5a7f26aae01255f38.jpg',
			'FILE_NAME' => 'see.jpg',
			'FILE_TYPE' => 'image/jpeg',
			'TITLE' => ''
		];
		$colors['BLUE'] = [
			'XML_ID' => 'blue',
			'PATH' => 'references_files/iblock/c1b/c1ba082577379bdc75246974a9f08c8b.jpg',
			'FILE_NAME' => 'blue.jpg',
			'FILE_TYPE' => 'image/jpeg',
			'TITLE' => ''
		];
		$colors['ORANGERED'] = [
			'XML_ID' => 'orangered',
			'PATH' => 'references_files/iblock/0ba/0ba3b7ecdef03a44b145e43aed0cca57.jpg',
			'FILE_NAME' => 'orangered.jpg',
			'FILE_TYPE' => 'image/jpeg',
			'TITLE' => ''
		];
		$colors['REDBLUE'] = [
			'XML_ID' => 'redblue',
			'PATH' => 'references_files/iblock/1ac/1ac0a26c5f47bd865a73da765484a2fa.jpg',
			'FILE_NAME' => 'redblue.jpg',
			'FILE_TYPE' => 'image/jpeg',
			'TITLE' => ''
		];
		$colors['RED'] = [
			'XML_ID' => 'red',
			'PATH' => 'references_files/iblock/0a7/0a7513671518b0f2ce5f7cf44a239a83.jpg',
			'FILE_NAME' => 'red.jpg',
			'FILE_TYPE' => 'image/jpeg',
			'TITLE' => ''
		];
		$colors['GREEN'] = [
			'XML_ID' => 'green',
			'PATH' => 'references_files/iblock/b1c/b1ced825c9803084eb4ea0a742b2342c.jpg',
			'FILE_NAME' => 'green.jpg',
			'FILE_TYPE' => 'image/jpeg',
			'TITLE' => ''
		];
		$colors['WHITE'] = [
			'XML_ID' => 'white',
			'PATH' => 'references_files/iblock/b0e/b0eeeaa3e7519e272b7b382e700cbbc3.jpg',
			'FILE_NAME' => 'white.jpg',
			'FILE_TYPE' => 'image/jpeg',
			'TITLE' => ''
		];
		$colors['BLACK'] = [
			'XML_ID' => 'black',
			'PATH' => 'references_files/iblock/d7b/d7bdba8aca8422e808fb3ad571a74c09.jpg',
			'FILE_NAME' => 'black.jpg',
			'FILE_TYPE' => 'image/jpeg',
			'TITLE' => ''
		];
		$colors['PINK'] = [
			'XML_ID' => 'pink',
			'PATH' => 'references_files/iblock/1b6/1b61761da0adce93518a3d613292043a.jpg',
			'FILE_NAME' => 'pink.jpg',
			'FILE_TYPE' => 'image/jpeg',
			'TITLE' => ''
		];
		$colors['AZURE'] = [
			'XML_ID' => 'azure',
			'PATH' => 'references_files/iblock/c2b/c2b274ad2820451d780ee7cf08d74bb3.jpg',
			'FILE_NAME' => 'azure.jpg',
			'FILE_TYPE' => 'image/jpeg',
			'TITLE' => ''
		];
		$colors['JEANS'] = [
			'XML_ID' => 'jeans',
			'PATH' => 'references_files/iblock/24b/24b082dc5e647a3a945bc9a5c0a200f0.jpg',
			'FILE_NAME' => 'jeans.jpg',
			'FILE_TYPE' => 'image/jpeg',
			'TITLE' => ''
		];
		$colors['FLOWERS'] = [
			'XML_ID' => 'flowers',
			'PATH' => 'references_files/iblock/64f/64f32941a654a1cbe2105febe7e77f33.jpg',
			'FILE_NAME' => 'flowers.jpg',
			'FILE_TYPE' => 'image/jpeg',
			'TITLE' => ''
		];
		$colors['DARKBLUE'] = [
			'XML_ID' => 'darkblue',
			'PATH' => 'references_files/iblock/84a/84afl562rq429820451d780ee7cf08d7.png',
			'FILE_NAME' => 'darkblue.png',
			'FILE_TYPE' => 'image/png',
			'TITLE' => ''
		];
		$colors['DARKGREEN'] = [
			'XML_ID' => 'darkgreen',
			'PATH' => 'references_files/iblock/87f/87f5d3ad34562rq429820451d780ee7c.png',
			'FILE_NAME' => 'darkgreen.png',
			'FILE_TYPE' => 'image/png',
			'TITLE' => ''
		];
		$colors['GREY'] = [
			'XML_ID' => 'grey',
			'PATH' => 'references_files/iblock/90c/90c274ad2820451d780ee7cf08d74bb3.png',
			'FILE_NAME' => 'grey.png',
			'FILE_TYPE' => 'image/png',
			'TITLE' => ''
		];
		$colors['YELLOW'] = [
			'XML_ID' => 'yellow',
			'PATH' => 'references_files/iblock/99a/99a082dc5e647a3a945bc9a5c0a200f0.png',
			'FILE_NAME' => 'yellow.png',
			'FILE_TYPE' => 'image/png',
			'TITLE' => ''
		];
		$colors['ORANGE'] = [
			'XML_ID' => 'orange',
			'PATH' => 'references_files/iblock/a0d/a0ddba8aca8422e808fb3ad571a74c09.png',
			'FILE_NAME' => 'orange.png',
			'FILE_TYPE' => 'image/png',
			'TITLE' => ''
		];

		foreach (array_keys($colors) as $index)
		{
			$colors[$index]['TITLE'] = GetMessage('WZD_REF_COLOR_'.$index);
		}

		Collection::sortByColumn($colors, ['TITLE' => SORT_ASC]);

		$picturePath = WIZARD_ABSOLUTE_PATH.'/site/services/iblock/';
		$sort = 0;
		foreach($colors as $row)
		{
			$sort+= 100;
			$data = [
				'UF_NAME' => $row['TITLE'],
				'UF_FILE' => [
					'name' => $row['FILE_NAME'],
					'type' => $row['FILE_TYPE'],
					'tmp_name' => $picturePath.$row['PATH']
				],
				'UF_SORT' => $sort,
				'UF_DEF' => '0',
				'UF_XML_ID' => $row['XML_ID']
			];
			$USER_FIELD_MANAGER->EditFormAddFields('HLBLOCK_'.$COLOR_ID, $data);
			$USER_FIELD_MANAGER->checkFields('HLBLOCK_'.$COLOR_ID, null, $data);

			$result = $entity_data_class::add($data);
		}
	}
}

if ($BRAND_ID > 0)
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