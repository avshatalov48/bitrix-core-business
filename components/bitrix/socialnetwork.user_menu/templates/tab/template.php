<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="sonet-user-menu-tabdiv">
<ul class="sonet-user-menu-tabs">
<li><a href="<?=$arResult["Urls"]["Main"]?>" class="m1<?if ($arParams["PAGE_ID"] == "user"):?> selected<?endif?>"><b class="p1"></b><b class="p2"><?=GetMessage("SONET_UM_GENERAL")?></b><b class="p3"></b></a></li>
<?if ($arResult["CurrentUserPerms"]["Operations"]["viewfriends"]):?>
	<li><a href="<?=$arResult["Urls"]["Friends"]?>" class="m2<?if ($arParams["PAGE_ID"] == "user_friends"):?> selected<?endif?>"><b class="p1"></b><b class="p2"><?=GetMessage("SONET_UM_FRIENDS")?></b><b class="p3"></b></a></li>
<?endif;?>
<?if ($arResult["CurrentUserPerms"]["Operations"]["viewgroups"]):?>
	<li><a href="<?=$arResult["Urls"]["Groups"]?>" class="m3<?if ($arParams["PAGE_ID"] == "user_groups"):?> selected<?endif?>"><b class="p1"></b><b class="p2"><?=GetMessage("SONET_UM_GROUPS")?></b><b class="p3"></b></a></li>
<?endif;?>
<?if ($arResult["CurrentUserPerms"]["IsCurrentUser"]):?>
	<li><a href="<?=$arResult["Urls"]["MessagesInput"]?>" class="m3"><b class="p1"></b><b class="p2"><?=GetMessage("SONET_UM_MESSAGES")?></b><b class="p3"></b></a></li>
<?endif;?>
<li><a href="" class="m4"><b class="p1"></b><b class="p2"><?=GetMessage("SONET_UM_PHOTO")?></b><b class="p3"></b></a></li>
<li><a href="" class="m5"><b class="p1"></b><b class="p2"><?=GetMessage("SONET_UM_FORUM")?></b><b class="p3"></b></a></li>
</ul>
</div>
<div class="sonet-user-menu-menu-clear-left"></div>
