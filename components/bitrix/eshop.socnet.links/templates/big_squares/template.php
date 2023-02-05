<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

$this->setFrameMode(true);

if (is_array($arResult["SOCSERV"]) && !empty($arResult["SOCSERV"]))
{
?>
<div class="bx-socialfooter">
	<div class="bx-socialfooter-flex">
		<?php foreach($arResult["SOCSERV"] as $socserv): ?>
		<a
			class="bx-socialfooter-item <?= htmlspecialcharsbx($socserv["CLASS"]) ?>"
			target="_blank"
			href="<?=htmlspecialcharsbx($socserv["LINK"])?>"
			<?= $arResult['FACEBOOK_CONVERSION_ENABLED'] ? "onclick=\"sendEventToFacebook('{$socserv['NAME']}')\"" : '' ?>
		><span class="bx-socialfooter-icon"></span></a>
		<?php endforeach ?>
	</div>
</div>
<?php if ($arResult['FACEBOOK_CONVERSION_ENABLED']): ?>
<script>
	function sendEventToFacebook(socialServiceName)
	{
		BX.ajax.runAction(
			'sale.facebookconversion.contact',
			{
				data: {
					contactBy: {
						type: 'socialNetwork',
						value: socialServiceName
					}
				}
			}
		);
	}
</script>
<?php endif ?>
<?php
}
?>