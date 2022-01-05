<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */
/** @var string $templateFolder */
/** @var boolean $isProject */
/** @var boolean $isScrumProject */

Loc::loadMessages(__FILE__);

if (
	empty($arResult['TAB'])
	|| $arResult['TAB'] === 'edit'
)
{
	?>
	<div class="socialnetwork-group-create-ex__content-title socialnetwork-group-create-ex__create--switch-nonproject <?= ($isProject ? '--project' : '') ?> <?= ($isScrumProject ? '--scrum' : '') ?>"><?= htmlspecialcharsEx(Loc::getMessage('SONET_GCE_T_SLIDE_FIELDS_TITLE')) ?></div>
	<div class="socialnetwork-group-create-ex__content-title socialnetwork-group-create-ex__create--switch-project socialnetwork-group-create-ex__create--switch-nonscrum <?= ($isScrumProject ? '--scrum' : '') ?> <?= ($isProject ? '--project' : '') ?>"><?= htmlspecialcharsEx(Loc::getMessage('SONET_GCE_T_SLIDE_FIELDS_TITLE_PROJECT')) ?></div>
	<div class="socialnetwork-group-create-ex__content-title socialnetwork-group-create-ex__create--switch-scrum <?= ($isScrumProject ? '--scrum' : '') ?> <?= ($isProject ? '--project' : '') ?>"><?= htmlspecialcharsEx(Loc::getMessage('SONET_GCE_T_SLIDE_FIELDS_TITLE_SCRUM')) ?></div>
	<div class="socialnetwork-group-create-ex__content-wrapper">
		<?php
		require($_SERVER['DOCUMENT_ROOT'] . $templateFolder . '/include/mainfields.php');
		?>
		<div class="socialnetwork-group-create-ex__content-line"></div>
		<div class="socialnetwork-group-create-ex__content-block --space-between --space-bottom" style="max-width: 600px">
			<?php
			require($_SERVER['DOCUMENT_ROOT'] . $templateFolder . '/include/themepicker.php');
			require($_SERVER['DOCUMENT_ROOT'] . $templateFolder . '/include/image.php');
			?>
		</div>
		<div class="socialnetwork-group-create-ex__content-block --space-bottom">
			<div class="ui-ctl ui-ctl-file-link">
				<div class="ui-ctl-label-text" data-role="socialnetwork-group-create-ex__expandable" for="sonet_group_create_settings_expandable"><?= Loc::getMessage('SONET_GCE_T_SLIDE_FIELDS_SETTINGS_SWITCHER') ?></div>
			</div>
		</div>
		<div class="socialnetwork-group-create-ex__content-block">
			<div id="sonet_group_create_settings_expandable" class="socialnetwork-group-create-ex__content-expandable">
				<div class="socialnetwork-group-create-ex__content-expandable--wrapper">
					<div class="socialnetwork-group-create-ex__content-title socialnetwork-group-create-ex__create--switch-nonproject <?= ($isProject ? '--project' : '') ?> <?= ($isScrumProject ? '--scrum' : '') ?>"><?= htmlspecialcharsEx(Loc::getMessage('SONET_GCE_T_PARAMETERS_TITLE')) ?></div>
					<div class="socialnetwork-group-create-ex__content-title socialnetwork-group-create-ex__create--switch-project socialnetwork-group-create-ex__create--switch-nonscrum <?= ($isScrumProject ? '--scrum' : '') ?> <?= ($isProject ? '--project' : '') ?>"><?= htmlspecialcharsEx(Loc::getMessage('SONET_GCE_T_PARAMETERS_TITLE_PROJECT')) ?></div>
					<div class="socialnetwork-group-create-ex__content-title socialnetwork-group-create-ex__create--switch-scrum <?= ($isScrumProject ? '--scrum' : '') ?> <?= ($isProject ? '--project' : '') ?>"><?= htmlspecialcharsEx(Loc::getMessage('SONET_GCE_T_PARAMETERS_TITLE_SCRUM')) ?></div>
					<div class="socialnetwork-group-create-ex__content-block --space-bottom--xl">
						<?php

						require($_SERVER['DOCUMENT_ROOT'] . $templateFolder . '/include/project_params.php');
						require($_SERVER['DOCUMENT_ROOT'] . $templateFolder . '/include/scrum.php');
						require($_SERVER['DOCUMENT_ROOT'] . $templateFolder . '/include/subject.php');
						require($_SERVER['DOCUMENT_ROOT'] . $templateFolder . '/include/invite_perms.php');
						require($_SERVER['DOCUMENT_ROOT'] . $templateFolder . '/include/spam_perms.php');
						require($_SERVER['DOCUMENT_ROOT'] . $templateFolder . '/include/tags.php');

						?>
					</div>
					<?php

					require($_SERVER['DOCUMENT_ROOT'] . $templateFolder . '/include/features.php');
					require($_SERVER['DOCUMENT_ROOT'] . $templateFolder . '/include/options.php');

					?>
				</div>
			</div>
		</div>
		<div class="socialnetwork-group-create-ex__content-block">
			<?php

			require($_SERVER['DOCUMENT_ROOT'] . $templateFolder . '/include/uf.php');

			?>
		</div>
	</div>
	<?php
}

