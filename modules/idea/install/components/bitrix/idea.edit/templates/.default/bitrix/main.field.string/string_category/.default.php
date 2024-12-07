<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Text\HtmlFilter;

$categoryList = [];
if(
	CModule::IncludeModule('iblock')
	&& CModule::IncludeModule('idea')
	&& CIdeaManagment::getInstance()->Idea()->getCategoryListID() > 0
)
{
	$categoryList = CIdeaManagment::getInstance()->Idea()->getCategoryList();
}

if(
	$arParams['userField']['VALUE'] == ''
	&& array_key_exists('idea', $_REQUEST)
	&& $_REQUEST['idea'] <> ''
)
{
	$arParams['userField']['VALUE'] = HtmlFilter::encode($_REQUEST['idea']);
}
?>
<select name="<?= $arParams['userField']['FIELD_NAME'] ?>">
	<?php
	foreach($categoryList as $opt)
	{
		?>
		<option
			value="<?= mb_strtoupper($opt['CODE']) ?>"
			<?= ((mb_strtoupper($arParams['userField']['VALUE']) == mb_strtoupper($opt['CODE'])) ? ' selected' : '') ?>
		>
			<?php
			print str_repeat('&bull; ', $opt['DEPTH_LEVEL'] - 1);
			print $opt['NAME'];
			?>
		</option>
		<?php
	}
	?>
</select>