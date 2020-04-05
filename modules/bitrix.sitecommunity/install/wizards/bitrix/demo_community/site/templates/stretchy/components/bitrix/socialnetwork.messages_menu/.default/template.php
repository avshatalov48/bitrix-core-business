<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$this->SetViewTarget("sidebar", 100);
?>
<div class="rounded-block">
	<div class="corner left-top"></div>
	<div class="corner right-top"></div>
	<div class="block-content">
		<div class="content-list user-sidebar">
			<div class="content-item">
				<div class="content-info">
					<div class="content-title"><?=GetMessage("SONET_UM_MY_MESSAGES")?></div>
				</div>
			</div>
		</div>
		<div class="hr"></div>
		<ul class="mdash-list">
			<li class="<?if ($arParams["PAGE_ID"] == "messages_users"):?>selected<?endif?>"><a href="<?=$arResult["Urls"]["MessagesUsers"]?>"><?=GetMessage("SONET_UM_MUSERS")?></a></li>
			<li class="<?if ($arParams["PAGE_ID"] == "messages_input"):?>selected<?endif?>"><a href="<?=$arResult["Urls"]["MessagesInput"]?>"><?=GetMessage("SONET_UM_INPUT")?></a></li>
			<li class="<?if ($arParams["PAGE_ID"] == "messages_output"):?>selected<?endif?>"><a href="<?=$arResult["Urls"]["MessagesOutput"]?>"><?=GetMessage("SONET_UM_OUTPUT")?></a></li>
			<li class="<?if ($arParams["PAGE_ID"] == "user_ban"):?>selected<?endif?>"><a href="<?=$arResult["Urls"]["UserBan"]?>"><?=GetMessage("SONET_UM_USER_BAN")?></a></li>
			<li class="<?if ($arParams["PAGE_ID"] == "log"):?>selected<?endif?>"><a href="<?=$arResult["Urls"]["Log"]?>"><?=GetMessage("SONET_UM_LOG")?></li>
			<li class="<?if ($arParams["PAGE_ID"] == "subscribe"):?>selected<?endif?>"><a href="<?=$arResult["Urls"]["Subscribe"]?>"><?=GetMessage("SONET_UM_SUBSCRIBE")?></a></li>
			<?if(strlen($arResult["Urls"]["BizProc"]) > 0):?>
				<li class="<?if ($arParams["PAGE_ID"] == "bizproc"):?>selected<?endif?>"><a href="<?=$arResult["Urls"]["BizProc"]?>"><?=GetMessage("SONET_UM_BIZPROC")?></a></li>
			<?endif;?>
		</ul>
	</div>
	<div class="corner left-bottom"></div>
	<div class="corner right-bottom"></div>
</div>
<?
$this->EndViewTarget();
?>