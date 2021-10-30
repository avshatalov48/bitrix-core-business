<?php

use Bitrix\Main\Text\HtmlFilter;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

if (!empty($arResult['ERRORS']))
{
	if ($arParams['EDIT_MODE'] === 'Y') :?>
		<div class="g-landing-alert-v2">
			<?php foreach ($arResult['ERRORS'] as $error) : ?>
				<?php
				$title = $error['title'] ?: $error['text'];
				$text = $error['text'] ?: '';
				?>
				<div class="g-landing-alert-title"><?= $title ?></div>
				<div class="g-landing-alert-text"><?= $text ?></div>
				<?php if ($error['button']): ?>
					<a class="landing-trusted-link landing-required-link ui-btn g-mt-15"
						href="<?= $error['button']['href'] ?>"><?= $error['button']['text'] ?></a>
				<?php endif ?>
			<?php endforeach; ?>
		</div>
	<?php endif;

	return;
}

// todo: migrate to style.css

$alerts = '';
if (!empty($arResult['ALERTS']))
{
	$alerts = '<div class="crmcontacts-alert"><div class="g-landing-alert-v3">';
	foreach ($arResult['ALERTS'] as $alert)
	{
		$alerts .= $alert;
	}
	$alerts .= '</div></div>';
}

$arResult['CONTACTS']['phones'][0] = HtmlFilter::encode($arResult['CONTACTS']['phones'][0]);
$textAlign = $arParams['BUTTON_POSITION'] === 'right' ? 'text-left' : 'text-right';
$textBlock = <<<HTML
		<div class="crmcontacts-text-block {$textAlign} col-8">
			<h6 class="crmcontacts-text-title h6">
				{$arParams['TITLE']}
			</h6>
				<div class="crmcontacts-text-text">
				<a href="tel:{$arResult['CONTACTS']['phones'][0]}">
					{$arResult['CONTACTS']['phones'][0]}
				</a>
			</div>
		</div>
HTML;

$buttonAlign = $arParams['BUTTON_POSITION'] === 'right' ? 'text-right d-flex justify-content-end' : 'text-left';
$buttonBlock = <<<HTML
		<div class="crmcontacts-button-block {$buttonAlign} col-4">
			<a class="crmcontacts-button-button btn g-color-white g-rounded-50 g-btn-px-m g-btn-size-md g-theme-bitrix-btn-v6"
				href="tel:{$arResult['CONTACTS']['phones'][0]}">
				{$arParams['BUTTON_TITLE']}
			</a>
		</div>
HTML;
?>

<div class="<?= (($arParams['TEMPLATE_MODE'] === 'darkmode') ? 'bx-dark' : '') ?>">
	<div class="row g-flex-centered align-items-end">
		<?php
		if ($arParams['BUTTON_POSITION'] === 'right')
		{
			echo $textBlock, $buttonBlock;
		}
		else
		{
			echo $buttonBlock, $textBlock;
		}
		if ($arParams['EDIT_MODE'] === 'Y')
		{
			echo $alerts;
		}
		?>
	</div>
</div>