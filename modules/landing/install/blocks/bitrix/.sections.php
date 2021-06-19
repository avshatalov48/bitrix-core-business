<?php
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
	'recommended' =>[
		'name' => Loc::getMessage('LD_BLOCK_SECTION_RECOMMENDED'),
		'type' => ['knowledge']
	],
	'cover' => Loc::getMessage('LD_BLOCK_SECTION_COVER'),
	
	'text' => Loc::getMessage('LD_BLOCK_SECTION_TEXT_NEW'),
	'text_image' => Loc::getMessage('LD_BLOCK_SECTION_TEXT_IMAGE'),
	'image' => Loc::getMessage('LD_BLOCK_SECTION_IMAGE_NEW'),
	'video' => Loc::getMessage('LD_BLOCK_SECTION_VIDEO'),
	
	'title' => Loc::getMessage('LD_BLOCK_SECTION_TITLE'),
	'columns' => Loc::getMessage('LD_BLOCK_SECTION_COLUMNS'),
	
	'tiles' => Loc::getMessage('LD_BLOCK_SECTION_TILES_NEW2'),
	'countdowns' => [
		'name' => Loc::getMessage('LD_BLOCK_SECTION_COUNTDOWNS'),
		'type' => ['page', 'store', 'smn']
	],
	
	'separator' => Loc::getMessage('LD_BLOCK_SECTION_TRANSITIONS_SEPARATORS'),
	
	'menu' => Loc::getMessage('LD_BLOCK_SECTION_MENU_NEW'),
	'sidebar' => Loc::getMessage('LD_BLOCK_SECTION_SIDEBAR'),
	'footer' => Loc::getMessage('LD_BLOCK_SECTION_FOOTER'),
	
	'forms' => Loc::getMessage('LD_BLOCK_SECTION_FORMS'),
	'news' => Loc::getMessage('LD_BLOCK_SECTION_NEWS'),
	'schedule' => Loc::getMessage('LD_BLOCK_SECTION_SCHEDULE'),
	
	'store' => Loc::getMessage('LD_BLOCK_SECTION_STORE_NEW'),
	
	'team' => Loc::getMessage('LD_BLOCK_SECTION_TEAM'),
	'feedback' => [
		'name' => Loc::getMessage('LD_BLOCK_SECTION_FEEDBACK'),
		'type' => ['page', 'store', 'smn']
	],
	'steps' => Loc::getMessage('LD_BLOCK_SECTION_STEPS'),
	'tariffs' => [
		'name' => Loc::getMessage('LD_BLOCK_SECTION_TARIFFS'),
		'type' => ['page', 'store', 'smn']
	],
	'partners' => [
		'name' => Loc::getMessage('LD_BLOCK_SECTION_PARTNERS'),
		'type' => ['page', 'store', 'smn']
	],
	'about' => [
		'name' => Loc::getMessage('LD_BLOCK_SECTION_ABOUT'),
		'type' => ['page', 'store', 'smn']
	],
	'contacts' => Loc::getMessage('LD_BLOCK_SECTION_CONTACTS'),
	'social' => Loc::getMessage('LD_BLOCK_SECTION_SOCIAL'),
	
	'other' => Loc::getMessage('LD_BLOCK_SECTION_OTHER'),
);