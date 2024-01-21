<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var $APPLICATION \CMain
 * @var array $arResult
 * @var array $arParams
 */

use Bitrix\Main\Component\ParameterSigner;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Web\Json;

Extension::load('ui.buttons');

$messages = Loc::loadLanguageFile(__DIR__ . '/users.php');
?>

<div class="sn-spaces__toolbar-space_basic">
	<div class="sn-spaces__toolbar-space_left-content">
		<div id="sn-spaces-toolbar-users-invite-btn"></div>
		<div
			class="sn-spaces__toolbar_filter-container ui-ctl ui-ctl-textbox ui-ctl-wa ui-ctl-after-icon ui-ctl-round ui-ctl-transp-white-borderless"
			id="sn-spaces__toolbar_filter-container"
		>
		<?php
			$APPLICATION->IncludeComponent(
				'bitrix:main.ui.filter',
				'',
				[
					'GRID_ID' => $arResult['GRID_ID'],
					'FILTER_ID' => $arResult['FILTER_ID'],
					'FILTER' => $arResult['FILTER'],
					'FILTER_PRESETS' => $arResult['FILTER_PRESETS'],
					'RESET_TO_DEFAULT_MODE' => true,
					'ENABLE_LIVE_SEARCH' => true,
					'ENABLE_LABEL' => true,
					'LAZY_LOAD' => [
						'CONTROLLER' => [
							'getList' => 'socialnetwork.filter.usertogroup.getlist',
							'getField' => 'socialnetwork.filter.usertogroup.getfield',
							'componentName' => 'socialnetwork.group.user.list',
							'signedParameters' => ParameterSigner::signParameters(
								'socialnetwork.group.user.list',
								[]
							),
						]
					],
					'CONFIG' => [
						'AUTOFOCUS' => false,
					],
				]
			);
		?>
		</div>
	</div>
</div>

<script>
	BX.ready(function() {
		BX.message(<?= Json::encode($messages) ?>);

		const usersToolbar = new BX.Socialnetwork.Spaces.UsersToolbar({
			pathToInvite: '<?= CUtil::JSescape($arResult['pathToInvite']) ?>',
		});

		usersToolbar.renderInviteBtnTo(document.getElementById('sn-spaces-toolbar-users-invite-btn'));
	});
</script>
