<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var CBitrixComponentTemplate $this
 * @var array $arParams
 * @var array $arResult
 */

$this->setFrameMode(true);

if (!empty($arResult['SOCSERV']) && is_array($arResult['SOCSERV']))
{
	?>
	<div class="store-social-profiles">
		<?php
		foreach ($arResult['SOCSERV'] as $service)
		{
			?>
			<a class="bx-icon bx-icon-service-<?= htmlspecialcharsbx($service['CLASS']) ?>"
				target="_blank"
				href="<?= htmlspecialcharsbx($service['LINK']) ?>"
				<?= $arResult['FACEBOOK_CONVERSION_ENABLED'] ? "onclick=\"sendEventToFacebook('{$service['NAME']}')\"" : '' ?>
			><i></i></a>
			<?php
		}
		?>
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
