<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>
<script>
	BXDesktopSystem.LogInfo("Not authorized");
	BXDesktopSystem.Login({ AutoLogin: false });
</script>
<? die(); ?>