<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

$this->setFrameMode(true);

if (is_array($arResult["SOCSERV"]) && !empty($arResult["SOCSERV"]))
{
?>
<div class="bx-socialfooter">
	<div class="bx-socialfooter-flex">
		<?foreach($arResult["SOCSERV"] as $socserv):?>
		<a
			class="bx-socialfooter-item <?=htmlspecialcharsbx($socserv["CLASS"])?>"
			target="_blank"
			href="<?=htmlspecialcharsbx($socserv["LINK"])?>"
		>
			<span class="bx-socialfooter-icon"></span>
		</a>
		<?endforeach?>
	</div>
</div>
<?
}
?>