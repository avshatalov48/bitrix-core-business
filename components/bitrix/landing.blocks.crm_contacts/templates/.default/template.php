<?php

use Bitrix\Main\Text\HtmlFilter;

/** @var array $arParams */
/** @var array $arResult */

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
					<a class="landing-trusted-link ui-btn g-mt-15"
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
	$alerts .= implode('', $arResult['ALERTS']);
	$alerts .= '</div></div>';
}

$arResult['CONTACTS']['PHONE'] = HtmlFilter::encode($arResult['CONTACTS']['PHONE']);
$textAlign = $arParams['BUTTON_POSITION'] === 'right' ? 'text-left' : 'text-right';
$titleBlock = $arParams['TITLE'] ? "<h6 class=\"crmcontacts-text-title h6\">{$arParams['TITLE']}</h6>" : '';
$textBlock = <<<HTML
	<div class="crmcontacts-text-block {$textAlign} col-8">
		{$titleBlock}
		<div class="crmcontacts-text-text">
			<a href="tel:{$arResult['CONTACTS']['PHONE']}">
				{$arResult['CONTACTS']['PHONE']}
			</a>
		</div>
	</div>
HTML;

$buttonAlign = $arParams['BUTTON_POSITION'] === 'right' ? 'text-right d-flex justify-content-end' : 'text-left';
$buttonClasses =
	$arParams['BUTTON_CLASSES']
	?: 'btn g-color-white g-rounded-50 g-btn-px-m g-btn-size-md g-theme-bitrix-btn-v6'
;
$buttonBlock = <<<HTML
	<div class="crmcontacts-button-block {$buttonAlign} col-4">
		<a class="crmcontacts-button-button {$buttonClasses}"
			href="tel:{$arResult['CONTACTS']['PHONE']}">
			{$arParams['BUTTON_TITLE']}
		</a>
	</div>
HTML;
?>

<?php
	$modeClass = '';
	if ($arParams['TEMPLATE_MODE'] === 'darkmode')
	{
		$modeClass = 'bx-dark';
	}
	if ($arParams['TEMPLATE_MODE'] === 'graymode')
	{
		$modeClass = 'bx-gray';
	}
?>

<div class="<?= $modeClass ?>">
	<div class="row g-flex-centered <?= $arParams['TITLE'] ? 'align-items-end' : 'align-items-center' ?>">
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