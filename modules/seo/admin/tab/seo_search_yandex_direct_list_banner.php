<?php
/**
 * Bitrix vars
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global CDatabase $DB
 * @global CUserTypeManager $USER_FIELD_MANAGER
 * @global CCacheManager $CACHE_MANAGER
 * @global array $arLinks
 * @global int $ID
 */

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Text\Converter;
use \Bitrix\Seo\Adv;

Loc::loadMessages(dirname(__FILE__).'/../seo_adv.php');

$bAllowDelete = $ID > 0;

if(count($arLinks) > 0)
{
	foreach($arLinks as $link)
	{
		switch($link['LINK_TYPE'])
		{
			case Adv\LinkTable::TYPE_IBLOCK_ELEMENT:

				echo '<div><a href="/bitrix/admin/iblock_element_edit.php?IBLOCK_ID='.$link['ELEMENT_IBLOCK_ID'].'&type='.Converter::getHtmlConverter()->encode($link['ELEMENT_IBLOCK_TYPE_ID']).'&ID='.$link['LINK_ID'].'&lang='.LANGUAGE_ID.'&find_section_section='.$link['ELEMENT_IBLOCK_SECTION_ID'].'" style="display: inline-block; height: 20px; vertical-align: top; margin-top: 2px;">'.Converter::getHtmlConverter()->encode($link['ELEMENT_NAME']).'</a>'.($bAllowDelete ? '&nbsp;<span class="yandex-delete" onclick="deleteLink(\''.$link['LINK_ID'].'\', \''.$link['LINK_TYPE'].'\', this)"></span>' : '').'</div>';

				break;
		}
	}
}
else
{
	echo Loc::getMessage('MAIN_NO');
}

?>
<script>
	function deleteLink(linkId, linkType, el)
	{
		if(!el._loading)
		{
			el._loading = true;
			el.style.background = 'url("/bitrix/panel/main/images/waiter-small-white.gif") no-repeat scroll center center';

			BX.ajax.loadJSON('/bitrix/tools/seo_yandex_direct.php?action=link_delete&banner=<?=$ID?>&link='+linkId+'&link_type='+linkType+'&get_list_html=2&sessid='+BX.bitrix_sessid(), function(res)
			{
				BX('adv_link_list').innerHTML = res.list_html;
				BX.onCustomEvent("OnSeoYandexDirectLinksChange", [BX('adv_link_list')]);
			});
		}
	}
</script>