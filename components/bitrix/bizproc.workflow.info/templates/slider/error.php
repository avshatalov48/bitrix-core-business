<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var array $arResult */
?>

<div class="bizproc__workflow-info_error">
	<div class="bp-workflow-info__title">
		<span class="bp-workflow-info__title-inner"><?= htmlspecialcharsbx($arResult['pageTitle'] ?? '') ?></span>
	</div>
	<?php if (!empty($arResult['errors'])): ?>
		<div class="bizproc__workflow-info_error-wrapper">
			<div class="bizproc__workflow-info_error-inner">
				<?php foreach ($arResult['errors'] as $error): ?>
					<p class="bizproc__workflow-info_error-text"><?= htmlspecialcharsbx($error) ?></p>
				<?php endforeach ?>
				<div class="bizproc__workflow-info_error-img"></div>
			</div>
		</div>
	<?php endif ?>
</div>
