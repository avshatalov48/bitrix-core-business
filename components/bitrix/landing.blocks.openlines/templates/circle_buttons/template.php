<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

if (!empty($arParams['ERRORS']))
{
	if ($arParams['EDIT_MODE'] === 'Y') :?>
		<div class="g-landing-alert-v2">
			<?php foreach ($arParams['ERRORS'] as $error) : ?>
				<?php
				$title = $error['title'] ?: $error['text'];
				$text = $error['text'] ?: '';
				?>
				<div class="g-landing-alert-title"><?= $title ?></div>
				<div class="g-landing-alert-text"><?= $text ?></div>
				<?php if ($error['button']): ?>
					<?php
						$onclick = $error['button']['onclick']
							? ' onclick="'.$error['button']['onclick'].'" '
							: '';
					?>
					<a class="landing-trusted-link landing-required-link ui-btn g-mt-15"
						href="<?= $error['button']['href'] ?>"
						<?=$onclick?>
					>
						<?= $error['button']['text'] ?>
					</a>
				<?php endif ?>
			<?php endforeach; ?>
		</div>
	<?php endif;

	return;
}

foreach ($arParams['WIDGETS'] as $widget) :?>
	<?php if (is_array($widget['show']) && isset($widget['show']['url'])): ?>
		<?php if (is_array($widget['show']['url'])): ?>
			<?php if ($arParams['IS_MOBILE']): ?>
				<a class="<?= $widget['classList'] ?> openlines-style-circle g-pointer-events-none--edit-mode" target="_blank" href="<?= $widget['show']['url']['mobile'] ?>">
				</a>
			<?php else: ?>
				<div
					class="openlines-style-circle g-cursor-pointer g-pointer-events-none--edit-mode <?= $widget['classList'] ?>"
					target="_blank"
					onclick='<?= $widget['show']['js']['desktop'] ?>'
				>
				</div>
			<?php endif; ?>
		<?php else: ?>
			<a class="<?= $widget['classList'] ?> openlines-style-circle g-pointer-events-none--edit-mode" target="_blank" href="<?= $widget['show']['url'] ?>">
			</a>
		<?php endif; ?>
	<?php else: ?>
		<div
			class="openlines-style-circle g-cursor-pointer g-pointer-events-none--edit-mode <?= $widget['classList'] ?>"
			target="_blank"
			onclick="<?= $widget['show'] ?>"
		>
		</div>
	<?php endif; ?>
<?php endforeach;





