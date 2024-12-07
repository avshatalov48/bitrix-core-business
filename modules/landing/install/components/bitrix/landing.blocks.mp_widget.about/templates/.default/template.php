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

<div class="landing-widget-about" id="<?= $id ?>">
	<div class="landing-widget-view-main">
		<div class="landing-widget-about-top">
			<div class="landing-widget-about-title"><?= \htmlspecialcharsbx($arResult['TITLE']) ?></div>
			<div class="landing-widget-about-info">
				<?= \htmlspecialcharsbx($arResult['TEXT']) ?>
			</div>
		</div>
		<div class="landing-widget-about-bottom">
			<div class="landing-widget-about-bottom-inner">
				<div class="landing-widget-about-person">
					<div class="landing-widget-about-person-icon-box">
						<?php if ($personalPhotoSrc !== ''): ?>
						<a
							<?= $bossLinkHrefAttr ?>
							style="background-image: url('<?= \htmlspecialcharsbx($personalPhotoSrc) ?>');"
							class="landing-widget-about-person-icon"
						></a>
						<?php else: ?>
						<div class="landing-widget-about-person-icon-default"></div>
						<?php endif; ?>
					</div>
					<div class="landing-widget-about-person-inner">
						<a class="landing-widget-about-person-name" <?= $bossLinkHrefAttr ?>>
							<?= \htmlspecialcharsbx($arResult['BOSS']['FULL_NAME']) ?>
						</a>
						<div class="landing-widget-about-person-department">
							<?= \htmlspecialcharsbx($arResult['BOSS']['WORK_POSITION']) ?? '' ?>
						</div>
					</div>
				</div>
				<div class="landing-widget-about-bottom-wrap">
					<div class="landing-widget-about-card-wrap">
						<?php
						foreach ($arResult['CARDS'] as $card): ?>
							<div class="landing-widget-about-card">
								<div class="<?= $card['icon'] ?> landing-widget-about-card-icon"></div>
								<div class="landing-widget-about-card-inner">
									<div class="landing-widget-about-card-title"><?= $card['title'] ?></div>
									<div class="landing-widget-about-card-text"><?= $card['text'] ?></div>
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
		<div class="landing-widget-about-title"><?= \htmlspecialcharsbx($arResult['TITLE']) ?></div>
		<div class="landing-widget-about-person">
			<div class="landing-widget-about-person-icon-box">
				<?php if ($personalSidebarPhotoSrc !== ''): ?>
					<a
						<?= $bossLinkHrefAttr ?>
						style="background-image: url('<?= \htmlspecialcharsbx($personalSidebarPhotoSrc) ?>');"
						class="landing-widget-about-person-icon"
					></a>
				<?php else: ?>
					<div class="landing-widget-about-person-icon-default"></div>
				<?php endif; ?>
			</div>
			<div class="landing-widget-about-person-inner">
				<a class="landing-widget-about-person-name" <?= $bossLinkHrefAttr ?>>
					<?= \htmlspecialcharsbx($arResult['BOSS']['FULL_NAME']) ?>
				</a>
				<div class="landing-widget-about-person-department">
					<?= \htmlspecialcharsbx($arResult['BOSS']['WORK_POSITION']) ?? '' ?>
				</div>
			</div>
		</div>
		<div class="landing-widget-about-info">
			<?= \htmlspecialcharsbx($arResult['TEXT']) ?>
		</div>
		<div class="landing-widget-about-bottom">
			<div class="landing-widget-about-bottom-inner">
				<div class="landing-widget-about-bottom-wrap">
					<div class="landing-widget-about-card-wrap">
						<?php
						foreach ($arResult['CARDS'] as $card): ?>
							<div class="landing-widget-about-card">
								<div class="<?= $card['icon'] ?> landing-widget-about-card-icon"></div>
								<div class="landing-widget-about-card-inner">
									<div class="landing-widget-about-card-title"><?= $card['title'] ?></div>
									<div class="landing-widget-about-card-text"><?= $card['text'] ?></div>
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
				new BX.Landing.Widget.About(widgetElement);
			}
		}
	});
</script>
