<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

$link = str_replace(
	array("#list_id#"),
	array($arResult["VARIABLES"]["list_id"]),
	$arResult["FOLDER"].$arResult["URL_TEMPLATES"]["bizproc_workflow_admin"]
);

CJSCore::Init(array('lists'));

$returnButton = new Bitrix\UI\Buttons\Button([
	'text' => Loc::getMessage('CT_BL_LIST_PROCESSES'),
	'color' => Bitrix\UI\Buttons\Color::LINK,
	'link' => $link,
	'icon' => Bitrix\UI\Buttons\Icon::BACK,
]);
$returnButton->setSize(Bitrix\UI\Buttons\Size::SMALL);
Bitrix\UI\Toolbar\Facade\Toolbar::addButton($returnButton);

?>
	<div style="background: #eef2f4; width: 600px; padding: 5px 20px;">
		<?php
		$APPLICATION->IncludeComponent('bitrix:bizproc.workflow.setconstants', '',
			array('ID' => $arResult['VARIABLES']['ID'], 'POPUP' => 'N'),
			$component,
			array("HIDE_ICONS" => "Y")
		);
		?>
	</div>
