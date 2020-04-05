<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();
?>
<div class="bx-auth-serv-icons">
<?foreach($arParams["~AUTH_SERVICES"] as $service):?>
	<a title="<?=htmlspecialcharsbx($service["NAME"])?>" href="javascript:void(0)" onclick="BxShowAuthFloat('<?=$service["ID"]?>', '<?=$arParams["SUFFIX"]?>')"><i class="bx-ss-icon <?=htmlspecialcharsbx($service["ICON"])?>"></i></a>
<?endforeach?>
</div>
