<?
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002-2014 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/storeassist/include.php");

IncludeModuleLangFile(__FILE__);

if (!($APPLICATION->GetGroupRight("storeassist") >= "R"))
{
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

$docUrl = CStoreAssist::getDocumentationLink("storeassist_adaptive");

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/storeassist/prolog.php");

$APPLICATION->SetTitle(GetMessage("STOREAS_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<div class="adm-detail-content-wrap">
	<div class="adm-detail-content">
		<div class="adm-detail-title"><?=GetMessage("STOREAS_TITLE")?></div>
		<div class="adm-detail-content-item-block">
			<div class="adm-s-postww">
				<?=GetMessage("STOREAS_TEXT")?>
				<br/>
			</div>
		</div>
		<br/>
		<a href="/bitrix/admin/storeassist.php?lang=<?=LANGUAGE_ID?>" class="adm-detail-toolbar-btn"><span class="adm-detail-toolbar-btn-l"></span><span class="adm-detail-toolbar-btn-text"><?=GetMessage("STOREAS_WIZARD")?></span><span class="adm-detail-toolbar-btn-r"></span></a>
		&nbsp;
		<?if ($docUrl):?>
			<span class="adm-btn" onclick="BX.Storeassist.Admin.showDocumentation('<?=CUtil::JSEscape($docUrl)?>')"><?=GetMessage("STOREAS_DOC")?></span>
		<?endif?>
		<br/><br/>
	</div>
</div>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>