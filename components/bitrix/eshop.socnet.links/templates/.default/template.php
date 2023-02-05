<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

$this->setFrameMode(true);

if (is_array($arResult["SOCSERV"]) && !empty($arResult["SOCSERV"]))
{
?>
<div class="bx-socialsidebar">
	<div class="bx-block-title"><?= GetMessage("SS_TITLE") ?></div>
	<div class="bx-socialsidebar-group">
		<ul>
			<?php foreach($arResult["SOCSERV"] as $socserv): ?>
			<li>
				<a
					class="<?= htmlspecialcharsbx($socserv["CLASS"]) ?> bx-socialsidebar-icon"
					target="_blank"
					href="<?= htmlspecialcharsbx($socserv["LINK"]) ?>"
					<?= $arResult['FACEBOOK_CONVERSION_ENABLED'] ? "onclick=\"sendEventToFacebook('{$socserv['NAME']}')\"" : '' ?>
				></a>
			</li>
			<?php endforeach ?>
		</ul>
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
