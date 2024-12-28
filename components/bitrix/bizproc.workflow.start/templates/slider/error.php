<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

\Bitrix\Main\UI\Extension::load(['ui.design-tokens']);

/**
 * @var array $arResult
 * @var array $errors
 */
$errors = $arResult['errors'];
?>

<div class="bizproc__workflow-start_error">
	<div class="bizproc__workflow-start_error__title">
		<span class="bizproc__workflow-start_error__title-inner">
			<?= htmlspecialcharsbx(\Bitrix\Main\Localization\Loc::getMessage('BIZPROC__CMP_WORKFLOW_START_TMP_ERROR_TITLE')) ?>
		</span>
	</div>
	<?php if ($errors): ?>
		<div class="bizproc__workflow-start_error__wrapper">
			<div class="bizproc__workflow-start_error__wrapper-inner">
				<?php foreach ($errors as $error): ?>
					<p class="bizproc__workflow-start_error__text">
						<?= htmlspecialcharsbx($error['text']) ?>
					</p>
				<?php endforeach ?>
				<div class="bizproc__workflow-start_error__image"></div>
			</div>
		</div>
	<?php endif ?>
</div>
