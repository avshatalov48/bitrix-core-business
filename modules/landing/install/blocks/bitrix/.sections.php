<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(
	\Bitrix\Landing\Manager::getDocRoot() .
	'/bitrix/modules/landing/blocks/.sections.php'
);


return array(
	'last' => Loc::getMessage('LD_BLOCK_SECTION_LAST'),
	'cover' => Loc::getMessage('LD_BLOCK_SECTION_COVER'),
	
	'text' => Loc::getMessage('LD_BLOCK_SECTION_TEXT_NEW'),
	'text_image' => Loc::getMessage('LD_BLOCK_SECTION_TEXT_IMAGE'),
	'image' => Loc::getMessage('LD_BLOCK_SECTION_IMAGE_NEW'),
	'video' => Loc::getMessage('LD_BLOCK_SECTION_VIDEO'),
	
	'title' => Loc::getMessage('LD_BLOCK_SECTION_TITLE'),
	'columns' => Loc::getMessage('LD_BLOCK_SECTION_COLUMNS'),
	
	'tiles' => Loc::getMessage('LD_BLOCK_SECTION_TILES_NEW2'),
	'countdowns' => Loc::getMessage('LD_BLOCK_SECTION_COUNTDOWNS'),
	
	'separator' => Loc::getMessage('LD_BLOCK_SECTION_SEPARATOR'),
	
	'menu' => Loc::getMessage('LD_BLOCK_SECTION_MENU_NEW'),
	'footer' => Loc::getMessage('LD_BLOCK_SECTION_FOOTER'),
	
	'forms' => Loc::getMessage('LD_BLOCK_SECTION_FORMS'),
	'schedule' => Loc::getMessage('LD_BLOCK_SECTION_SCHEDULE'),
	
	'store' => Loc::getMessage('LD_BLOCK_SECTION_STORE_NEW'),
	
	'team' => Loc::getMessage('LD_BLOCK_SECTION_TEAM'),
	'feedback' => Loc::getMessage('LD_BLOCK_SECTION_FEEDBACK'),
	'steps' => Loc::getMessage('LD_BLOCK_SECTION_STEPS'),
	'tariffs' => Loc::getMessage('LD_BLOCK_SECTION_TARIFFS'),
	'partners' => Loc::getMessage('LD_BLOCK_SECTION_PARTNERS'),
	'about' => Loc::getMessage('LD_BLOCK_SECTION_ABOUT'),
	'contacts' => Loc::getMessage('LD_BLOCK_SECTION_CONTACTS'),
	'social' => Loc::getMessage('LD_BLOCK_SECTION_SOCIAL'),
	
	'other' => Loc::getMessage('LD_BLOCK_SECTION_OTHER'),
);