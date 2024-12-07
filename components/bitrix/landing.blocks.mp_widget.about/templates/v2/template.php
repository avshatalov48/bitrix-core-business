<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var array $arParams */
/** @var array $arResult */

use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$id = 'widget-' . htmlspecialcharsbx(bin2hex(random_bytes(5)));
$personalPhotoSrc = $arResult['BOSS']['PERSONAL_PHOTO_SRC'] ?? '';
$personalSidebarPhotoSrc = $arResult['BOSS']['PERSONAL_PHOTO_SRC_SMALL'] ?? $arResult['BOSS']['PERSONAL_PHOTO_SRC'];
$bossLinkHrefAttr = '';
if (isset($arResult['BOSS']['LINK']))
{
	$bossLinkHrefAttr = 'href="' . $arResult['BOSS']['LINK'] . '"';
}
?>

<div class="landing-widget-about-v2" id="<?= $id ?>">
	<div class="landing-widget-view-main">
		<div class="landing-widget-about-v2-top">
			<div class="landing-widget-about-v2-title"><?= \htmlspecialcharsbx($arResult['TITLE']) ?></div>
		</div>
		<div class="landing-widget-about-v2-center">
			<div class="landing-widget-about-v2-person">
				<div class="landing-widget-about-v2-person-icon-box">
					<?php if ($personalPhotoSrc !== ''): ?>
						<a
							<?= $bossLinkHrefAttr ?>
							style="background-image: url('<?= \htmlspecialcharsbx($personalPhotoSrc) ?>');"
							class="landing-widget-about-v2-person-icon"
						></a>
					<?php else: ?>
						<div class="landing-widget-about-v2-person-icon-default"></div>
					<?php endif; ?>
				</div>
				<div class="landing-widget-about-v2-person-inner">
					<a class="landing-widget-about-v2-person-name" <?= $bossLinkHrefAttr ?>>
						<?= \htmlspecialcharsbx($arResult['BOSS']['FULL_NAME']) ?>
					</a>
					<div class="landing-widget-about-v2-person-department">
						<?= \htmlspecialcharsbx($arResult['BOSS']['WORK_POSITION']) ?? '' ?>
					</div>
				</div>
			</div>
			<div class="landing-widget-about-v2-info">
				<?= \htmlspecialcharsbx($arResult['TEXT']) ?>
			</div>
		</div>
		<div class="landing-widget-about-v2-bottom">
			<div class="landing-widget-about-v2-bottom-inner">
				<div class="landing-widget-about-v2-bottom-wrap">
					<div class="landing-widget-about-v2-card-wrap">
						<?php
						foreach ($arResult['CARDS'] as $card): ?>
							<div class="landing-widget-about-v2-card">
								<div class="<?= $card['icon'] ?> landing-widget-about-v2-card-icon"></div>
								<div class="landing-widget-about-v2-card-inner">
									<div class="landing-widget-about-v2-card-title"><?= $card['title'] ?></div>
									<div class="landing-widget-about-v2-card-text"><?= $card['text'] ?></div>
								</div>
							</div>
						<?php
						endforeach; ?>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="landing-widget-view-sidebar">
		<div class="landing-widget-about-v2-title"><?= \htmlspecialcharsbx($arResult['TITLE']) ?></div>
		<div class="landing-widget-about-v2-person">
			<div class="landing-widget-about-v2-person-icon-box">
				<?php if ($personalSidebarPhotoSrc !== ''): ?>
					<a
						<?= $bossLinkHrefAttr ?>
						style="background-image: url('<?= \htmlspecialcharsbx($personalSidebarPhotoSrc) ?>');"
						class="landing-widget-about-v2-person-icon"
					></a>
				<?php else: ?>
					<div class="landing-widget-about-v2-person-icon-default"></div>
				<?php endif; ?>
			</div>
			<div class="landing-widget-about-v2-person-inner">
				<a class="landing-widget-about-v2-person-name" <?= $bossLinkHrefAttr ?>>
					<?= \htmlspecialcharsbx($arResult['BOSS']['FULL_NAME']) ?>
				</a>
				<div class="landing-widget-about-v2-person-department">
					<?= \htmlspecialcharsbx($arResult['BOSS']['WORK_POSITION']) ?? '' ?>
				</div>
			</div>
		</div>
		<div class="landing-widget-about-v2-info">
			<?= \htmlspecialcharsbx($arResult['TEXT']) ?>
		</div>
		<div class="landing-widget-about-v2-bottom">
			<div class="landing-widget-about-v2-bottom-inner">
				<div class="landing-widget-about-v2-bottom-wrap">
					<div class="landing-widget-about-v2-card-wrap">
						<?php
						foreach ($arResult['CARDS'] as $card): ?>
							<div class="landing-widget-about-v2-card">
								<div class="<?= $card['icon'] ?> landing-widget-about-v2-card-icon"></div>
								<div class="landing-widget-about-v2-card-inner">
									<div class="landing-widget-about-v2-card-title"><?= $card['title'] ?></div>
									<div class="landing-widget-about-v2-card-text"><?= $card['text'] ?></div>
								</div>
							</div>
						<?php
						endforeach; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
	BX.ready(function()
	{
		const editModeElement = document.querySelector('main.landing-edit-mode');
		if (!editModeElement)
		{
			const widgetElement = document.querySelector('#<?= $id ?>');
			if (widgetElement)
			{
					new BX.Landing.Widget.AboutV2(widgetElement);
			}
		}
	});
</script>
