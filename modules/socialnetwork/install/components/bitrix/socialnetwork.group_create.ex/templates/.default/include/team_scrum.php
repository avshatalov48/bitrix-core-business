<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Security\Random;

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */
/** @var boolean $isScrumProject */

Loc::loadMessages(__FILE__);

?>
<div class="socialnetwork-group-create-ex__create--switch-scrum <?= ($isScrumProject ? '--scrum' : '') ?>" style="margin-bottom: 15px">
	<div class="socialnetwork-group-create-ex__text --s --margin-bottom"><?= Loc::getMessage('SONET_GCE_T_SCRUM_MASTER') ?></div>
	<div class="ui-ctl ui-ctl-textbox ui-ctl-w100" id="SCRUM_MASTER_CODE_container">
		<?php

		$selectorName = 'group_create_scrum_master_' . Random::getString(6);

		$APPLICATION->IncludeComponent(
			'bitrix:main.user.selector',
			'',
			[
				'ID' => $selectorName,
				'INPUT_NAME' => 'SCRUM_MASTER_CODE',
				'LIST' => (
					!empty($arResult['POST'])
					&& !empty($arResult['POST']['SCRUM_MASTER_ID'])
						? [ 'U' . $arResult['POST']['SCRUM_MASTER_ID'] ]
						: []
				),
				'USE_SYMBOLIC_ID' => true,
				'BUTTON_SELECT_CAPTION' => Loc::getMessage('SONET_GCE_T_CHANGE_SCRUM_MASTER'),
				'BUTTON_SELECT_CAPTION_MORE' => Loc::getMessage('SONET_GCE_T_CHANGE_SCRUM_MASTER_MORE'),
				'API_VERSION' => 3,
				'SELECTOR_OPTIONS' => [
					'userSearchArea' => ($arResult['bExtranetInstalled'] ? 'I' : false),
					'contextCode' => 'U',
					'context' => $arResult['destinationContextOwner'],
				]
			]
		);
		?>
	</div>
</div>
<?php
