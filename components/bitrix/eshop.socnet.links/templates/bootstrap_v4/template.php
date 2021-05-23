<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

$this->setFrameMode(true);

if (is_array($arResult["SOCSERV"]) && !empty($arResult["SOCSERV"]))
{
?>
<div>
	<?foreach($arResult["SOCSERV"] as $socserv):?>
		<a class="bx-icon bx-icon-service-<?=htmlspecialcharsbx($socserv["CLASS"])?>" target="_blank" href="<?=htmlspecialcharsbx($socserv["LINK"])?>"><i></i></a>
	<?endforeach?>
</div>
<?
}
?>