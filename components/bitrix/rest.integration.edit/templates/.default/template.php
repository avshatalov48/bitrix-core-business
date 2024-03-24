<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Rest\Marketplace\Url;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;

Loc::loadMessages(__FILE__);

\Bitrix\Main\UI\Extension::load('ui.design-tokens');
?>
<form method="post" id="rest-integration-form">
	<div class="integration" id="rest-integration-form-block">
		<?=bitrix_sessid_post()?>
		<input id="integration-save-mode" type="hidden" name="MODE" value="SAVE">
		<div class="integration-start-container">
			<div class=" integration-row-padding-right">
				<div id="integrationEditTitle" style="display: none;">
					<input type="text" name="TITLE" value="<?=htmlspecialcharsbx($arResult['TITLE'])?>">
				</div>
				<input name="ID" type="hidden" value="<?=$arResult['ID']?>">
				<div class="integration-description-text">
					<p><?=($arResult['DESCRIPTION_FULL']) ? : $arResult['DESCRIPTION'];?></p>
				</div>
				<?php foreach ($arResult['ERROR_MESSAGE'] as $error):?>
					<?php if ($error):?>
						<div class="ui-alert ui-alert-danger">
							<span class="ui-alert-message"><?=$error?></span>
						</div>
					<?php endif?>
				<?php endforeach?>
				<div id="rest-integration-form-error"></div>
				<? if (in_array('INCOMING', $arResult['BLOCK_LIST'])): ?>
					<div class="integration-row">
						<div class="integration-row-input-title">
							<?=Loc::getMessage('REST_INTEGRATION_EDIT_LABEL_WEBHOOK_URL')?>
						</div>
						<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
							<input
								type="text"
								id="webhookURL"
								name="WEBHOOK_URL"
								class="ui-ctl-element"
								value="<?=htmlspecialcharsbx($arResult['PASSWORD_DATA_URL'])?>"
								readonly="readonly">
						</div>
						<span id="integrationGenerateWebhook" class="integration-action-btn ui-btn ui-btn-primary">
							<?=Loc::getMessage('REST_INTEGRATION_EDIT_BTN_GENERATE_WEBHOOK')?>
						</span>
					</div>
				<? endif; ?>
			</div>
		</div>
		<?
		$i = 0;
		foreach ($arResult['BLOCK_LIST'] as $block):
			$i++;
			?>
		<div class="integration-container">
			<div class="integration-row">
				<? if ($i == 1): ?>
					<div class="integration-row-container-title">
						<?=Loc::getMessage('REST_INTEGRATION_EDIT_BLOCK_TITLE_' . $block)?>
					</div>
				<? endif; ?>
				<? switch ($block):
					case 'BOT': ?>
						<div class="integration-row integration-row-padding-right integration-row-no-margin">
							<div class="integration-tab-container integration-no-padding">
								<div class="integration-row integration-row-no-margin">
									<div class="integration-row-input-title">
										<?=Loc::getMessage('REST_INTEGRATION_EDIT_BOT_NAME_TITLE')?>*
									</div>
									<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
										<input
											type="text"
											name="BOT_NAME"
											class="ui-ctl-element"
											value="<?=htmlspecialcharsbx($arResult['BOT_ACCOUNT_NAME'])?>"
											required>
									</div>
								</div>
								<div class="integration-row integration-row-no-margin">
									<div class="integration-row-input-title">
										<?=Loc::getMessage('REST_INTEGRATION_EDIT_BOT_HANDLER_URL_TITLE')?>*
									</div>
									<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
										<input
											type="url"
											name="BOT_HANDLER_URL"
											class="ui-ctl-element integration-required"
											placeholder="https://example.com/handler.php"
											value="<?=htmlspecialcharsbx($arResult['BOT_HANDLER_URL'])?>"
											required>
									</div>
								</div>
								<? if ($arResult['BOT_ID'] > 0): ?>
									<div class="integration-row integration-row-no-margin">
										<div class="integration-row-input-title">
											<?=Loc::getMessage('REST_INTEGRATION_EDIT_BOT_CODE_TITLE')?>
										</div>
										<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
											<input
												type="text"
												name="BOT_CODE"
												class="ui-ctl-element"
												value="<?=htmlspecialcharsbx($arResult['BOT_DATA_CODE'])?>"
												readonly="readonly">
										</div>
									</div>
									<div class="integration-row integration-row-no-margin">
										<div class="integration-row-input-title">
											<?=Loc::getMessage('REST_INTEGRATION_EDIT_BOT_CLIENT_ID_TITLE')?>
										</div>
										<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
											<input
												type="text"
												name="BOT_CLIENT_ID"
												class="ui-ctl-element"
												value="<?=htmlspecialcharsbx($arResult['BOT_DATA_APP_ID'])?>"
												readonly="readonly">
										</div>
									</div>
								<? endif; ?>
								<div class="integration-row integration-row-no-margin">
									<div class="integration-row-input-title">
										<?=Loc::getMessage('REST_INTEGRATION_EDIT_BOT_TYPE_TITLE')?>*
									</div>
									<? $APPLICATION->IncludeComponent(
										"bitrix:rest.integration.selector",
										"",
										array(
											'ACTION' => 'BotType',
											'LIST' => ($arResult['BOT_DATA_TYPE']) ? $arResult['BOT_DATA_TYPE'] : 'B',
											'INPUT_NAME' => 'BOT_TYPE',
											'READONLY' => false,
											'MULTIPLE' => false,
											'CAN_REMOVE_TILES' => false,
											'CAN_EDIT' => true
										),
										false
									); ?>
								</div>
								<? if (!empty($arResult[$block . '_DOWNLOAD_EXAMPLE_URL'])): ?>
									<div class="integration-row">
										<a
											href="<?=$arResult[$block . '_DOWNLOAD_EXAMPLE_URL']?>"
											class="integration-row-container-link integration-example-url"
										>
											<?=Loc::getMessage( 'REST_INTEGRATION_EDIT_TAB_' . $block . '_DOWNLOAD_EXAMPLE_BTN')?>
										</a>
									</div>
								<? elseif (!empty($arResult[$block . '_DOWNLOAD_EXAMPLE_TYPE'])): ?>
									<div class="integration-row">
										<a
											href="<?=$arResult['URI_EXAMPLE_DOWNLOAD'] . $arResult[$block . '_DOWNLOAD_EXAMPLE_TYPE']?>"
											class="integration-row-container-link integration-example-url integration-post-open"
										>
											<?=Loc::getMessage('REST_INTEGRATION_EDIT_TAB_' . $block . '_DOWNLOAD_EXAMPLE_BTN')?>
										</a>
									</div>
								<? endif; ?>
								<? if (isset($arResult['DESCRIPTION_BOT']) && $arResult['DESCRIPTION_BOT']): ?>
									<div class="integration-row integration-row-padding-right">
										<? if (!empty($arResult['DESCRIPTION_BOT']['TITLE'])): ?>
											<div class="integration-row-container-title-text">
												<?=$arResult['DESCRIPTION_BOT']['TITLE']?>
											</div>
										<? endif; ?>
										<? if (!empty($arResult['DESCRIPTION_BOT']['DESCRIPTION'])): ?>
											<div class="integration-row-container-description-text">
												<?=$arResult['DESCRIPTION_BOT']['DESCRIPTION']?>
											</div>
										<? endif; ?>
									</div>
								<? endif; ?>
								<div class="integration-row integration-row-no-margin integration-no-padding">
									<span id="integrationSaveBot" class="integration-action-btn ui-btn ui-btn-primary">
										<?=Loc::getMessage(
											'REST_INTEGRATION_EDIT_BOT_'
											. (($arResult['BOT_ID'] > 0) ? 'UPDATE' : 'CREATE')
											. '_BTN'
										)?>
									</span>
								</div>
							</div>
						</div>
						<? break; ?>
					<? case 'INCOMING': ?>
						<? if ($i != 1): ?>
							<div class="integration-row-container-title">
								<?=Loc::getMessage('REST_INTEGRATION_EDIT_BLOCK_TITLE_' . $block)?>
							</div>
						<? endif; ?>
						<? foreach ($arResult['QUERY'] as $data):?>
							<div class="integration-row integration-row-no-margin">
								<div class="integration-row-input-title">
									<?=Loc::getMessage('REST_INTEGRATION_EDIT_GENERATOR_LABEL_SELECTOR_REST_METHOD')?>
								</div>
								<div class="integration-webhook-method-api-select ui-ctl ui-ctl-textbox ui-ctl-w100">
									<?
									$APPLICATION->IncludeComponent(
										"bitrix:rest.integration.selector",
										"",
										array(
											'ACTION' => 'Method',
											'LIST' => $data['METHOD'],
											'ON_CHANGE' => 'BX.rest.integration.edit.makeCurlString();',
											'INPUT_NAME' => 'QUERY[' . $data['CODE'] . '][METHOD]',
											'INPUT_SCOPE_NAME' => 'SCOPE',
											'READONLY' => false,
											'MULTIPLE' => false,
											'CAN_REMOVE_TILES' => false,
											'CAN_EDIT' => true
										),
										false
									);
									?>
									<?php if (!isset($data['METHOD_URL_NEEDED']) || $data['METHOD_URL_NEEDED'] !== 'N'): ?>
										<div class="integration-method-href">
											<a
												href="" target="_blank" data-key="<?=$data['CODE']?>"
												class="integration-method-url integration-row-container-link"
											>
												<?=Loc::getMessage('REST_INTEGRATION_EDIT_GENERATOR_METHOD_URL_BTN')?>
											</a>
										</div>
									<? endif; ?>
									<? if (!empty($data['METHOD_DOWNLOAD_EXAMPLE_TYPE'])): ?>
										<div class="integration-method-href">
											<a
												data-key="<?=$data['CODE']?>"
												href="<?=$arResult['URI_EXAMPLE_DOWNLOAD'] . $data['METHOD_DOWNLOAD_EXAMPLE_TYPE']?>"
												target="_blank"
												class="integration-row-container-link integration-example-url integration-post-open"
											>
												<?=Loc::getMessage(
													'REST_INTEGRATION_EDIT_GENERATOR_METHOD_DOWNLOAD_EXAMPLE_BTN'
												)?>
											</a>
										</div>
									<? endif; ?>
								</div>
							</div>
							<? if (isset($data['DESCRIPTION_METHOD']) && $data['DESCRIPTION_METHOD']): ?>
								<div class="integration-row integration-row-padding-right">
									<? if (!empty($data['DESCRIPTION_METHOD']['TITLE'])): ?>
										<div class="integration-row-container-title-text">
											<?=$data['DESCRIPTION_METHOD']['TITLE']?>
										</div>
									<? endif; ?>
									<? if (!empty($data['DESCRIPTION_METHOD']['DESCRIPTION'])): ?>
										<div class="integration-row-container-description-text">
											<?=$data['DESCRIPTION_METHOD']['DESCRIPTION']?>
										</div>
									<? endif; ?>
								</div>
							<? endif; ?>
							<div class="integration-row-container-title-second integration-row-container-title-border">
								<?=$data['ITEMS_TITLE']?>
							</div>
							<div id="integration-webhook-param-<?=$data['CODE']?>"></div>
							<div class="integration-webhook-param-control">
								<span
									class="integration-webhook-param-control-item integration-webhook-param-control-item-add"
									id="integration-webhook-add-param-<?=$data['CODE']?>"
									data-key="<?=$data['CODE']?>"
								>
									<?=Loc::getMessage('REST_INTEGRATION_EDIT_GENERATOR_ADD_PARAM_BTN')?>
								</span>
								<? if (isset($data['QUERY_INFORMATION_URL']) && $data['QUERY_INFORMATION_URL']): ?>
									<a
										href="<?=$data['QUERY_INFORMATION_URL']?>" target="_blank"
										class="integration-webhook-param-control-item"
									>
										<?=Loc::getMessage( 'REST_INTEGRATION_EDIT_GENERATOR_QUERY_INFORMATION_URL_BTN')?>
									</a>
								<? endif; ?>
							</div>
							<div class="integration-row integration-row-padding-right">
								<div class="integration-row-input-title">
									<?=Loc::getMessage('REST_INTEGRATION_EDIT_GENERATOR_LABEL_GENERATED_URL')?>
								</div>
								<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
									<input
										type="text"
										data-key="<?=$data['CODE'];?>"
										class="integration-curl-uri ui-ctl-element integration-row-input-bold"
										value=""
										name="GENERATED_WEBHOOK[<?=$data['CODE']?>]"
										autocomplete="off"
									>
								</div>
								<span
									data-key="<?=$data['CODE']?>"
									class="integration-curl-uri-button integration-action-btn ui-btn ui-btn-primary"
								>
									<?=Loc::getMessage('REST_INTEGRATION_EDIT_GENERATOR_ACTION_DO_GENERATED_URL_BTN')?>
								</span>
							</div>
						<? endforeach; ?>
						<? break; ?>
					<? case 'OUTGOING': ?>
						<div
							class="integration-tab <?=($arResult[$block . '_NEEDED'] == 'Y' || $i === 1) ? ' integration-tab-auto-open' : ''?>"
							data-role="integration-tab"
						>
							<? if ($i != 1): ?>
								<div class="integration-tab-title">
									<label for="integration-tab-<?=$i?>">
										<input
											type="checkbox"
											name="<?=$block?>_NEEDED"
											value="Y"
											id="integration-tab-<?=$i?>"
											class="integration-tab-checkbox"
											<?=($arResult[$block . '_NEEDED'] == 'Y') ? ' selected' : ''?>
										>
										<span>
											<?=Loc::getMessage('REST_INTEGRATION_EDIT_BLOCK_TITLE_' . $block)?>
										</span>
									</label>
								</div>
							<? else: ?>
								<input type="hidden" name="<?=$block?>_NEEDED" value="Y">
							<? endif; ?>
							<div class="integration-tab-wrapper">
								<div class="integration-tab-container">
									<div class="integration-row-input-title">
										<?=Loc::getMessage('REST_INTEGRATION_EDIT_TAB_' . $block . '_URL')?>*
									</div>
									<div class="integration-row integration-row-padding-right integration-row-no-margin">
										<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
											<input
												type="url"
												name="<?=$block?>_HANDLER_URL"
												class="ui-ctl-element integration-required"
												placeholder="https://example.com/handler.php"
												value="<?=htmlspecialcharsbx($arResult[$block . '_HANDLER_URL'])?>"
											>
										</div>
									</div>
									<? if (!empty($arResult[$block . '_DOWNLOAD_EXAMPLE_URL'])): ?>
										<div class="integration-row">
											<a
												href="<?=$arResult[$block . '_DOWNLOAD_EXAMPLE_URL']?>"
												class="integration-row-container-link integration-example-url">
												<?=Loc::getMessage(
													'REST_INTEGRATION_EDIT_TAB_' . $block . '_DOWNLOAD_EXAMPLE_BTN'
												)?>
											</a>
										</div>
									<? elseif (!empty($arResult[$block . '_DOWNLOAD_EXAMPLE_TYPE'])): ?>
										<div class="integration-row">
											<a
												href="<?=$arResult['URI_EXAMPLE_DOWNLOAD'] . $arResult[$block . '_DOWNLOAD_EXAMPLE_TYPE']?>"
												target="_blank"
												class="integration-row-container-link integration-example-url integration-post-open"
											>
												<?=Loc::getMessage(
													'REST_INTEGRATION_EDIT_TAB_' . $block . '_DOWNLOAD_EXAMPLE_BTN'
												)?>
											</a>
										</div>
									<? endif; ?>
									<div class="integration-row integration-row-padding-right integration-row-no-margin">
										<div class="integration-row-input-title">
											<?=Loc::getMessage('REST_INTEGRATION_EDIT_TAB_APPLICATION_TOKEN')?>
										</div>
										<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
											<input
												type="text"
												name="APPLICATION_TOKEN"
												class="ui-ctl-element"
												value="<?=$arResult['APPLICATION_TOKEN']?>"
												readonly="readonly">
										</div>
									</div>

									<div class="integration-row integration-row-padding-right">
										<div class="integration-row-input-title">
											<?=Loc::getMessage('REST_INTEGRATION_EDIT_TAB_' . $block . '_TITLE')?>
										</div>
										<?
										$APPLICATION->IncludeComponent(
											"bitrix:rest.integration.selector",
											"",
											array(
												'ACTION' => 'Event',
												'LIST' => $arResult[$block . '_EVENTS'],
												'INPUT_NAME' => $block . '_EVENTS',
												'INPUT_SCOPE_NAME' => 'SCOPE',
												'READONLY' => false,
												'MULTIPLE' => true,
												'CAN_REMOVE_TILES' => true,
												'CAN_EDIT' => true
											),
											false
										);
										?>
									</div>
									<? if (isset($arResult['DESCRIPTION_' . $block]) && $arResult['DESCRIPTION_' . $block]): ?>
										<div class="integration-row integration-row-padding-right">
											<? if (!empty($arResult['DESCRIPTION_' . $block]['TITLE'])): ?>
												<div class="integration-row-container-title-text">
													<?=$arResult['DESCRIPTION_' . $block]['TITLE']?>
												</div>
											<? endif; ?>
											<? if (!empty($arResult['DESCRIPTION_' . $block]['DESCRIPTION'])): ?>
												<div class="integration-row-container-description-text">
													<?=$arResult['DESCRIPTION_' . $block]['DESCRIPTION']?>
												</div>
											<? endif; ?>
										</div>
									<? endif; ?>
								</div>
							</div>
						</div>
						<? break; ?>
					<? case 'APPLICATION': ?>
						<div
							class="integration-tab<?=($arResult[$block . '_NEEDED'] == 'Y' || $i === 1) ? ' integration-tab-auto-open' : ''?>"
							data-role="integration-tab"
						>
							<? if ($i != 1): ?>
								<div class="integration-tab-title">
									<label for="integration-tab-<?=$i?>">
										<input
											type="checkbox"
											name="<?=$block?>_NEEDED"
											value="Y"
											id="integration-tab-<?=$i?>"
											class="integration-tab-checkbox"
											<?=($arResult[$block . '_NEEDED'] == 'Y') ? ' selected' : ''?>>
										<span><?=Loc::getMessage('REST_INTEGRATION_EDIT_BLOCK_TITLE_' . $block)?></span>
									</label>
								</div>
							<? else: ?>
								<input type="hidden" name="<?=$block?>_NEEDED" value="Y">
							<? endif; ?>
							<div class="integration-tab-wrapper">
								<div class="integration-tab-container integration-no-padding">
									<? if ($arResult['ALLOW_ZIP_APPLICATION']): ?>
										<div id="applicationType">
											<label>
												<input
													type="radio"
													name="APPLICATION_MODE"
													value="<?=$arResult['APPLICATION_MODE']['SERVER'];?>"
													checked
												>
												<?=Loc::getMessage(
													"REST_INTEGRATION_EDIT_TAB_APPLICATION_MODE_SERVER_LABEL"
												)?>
											</label>
											<label>
												<input
													type="radio" name="APPLICATION_MODE"
													value="<?=$arResult['APPLICATION_MODE']['ZIP'];?>"
												>
												<?=Loc::getMessage(
													"REST_INTEGRATION_EDIT_TAB_APPLICATION_MODE_ZIP_LABEL"
												)?>
											</label>
										</div>
									<? endif; ?>
									<div id="applicationServer">
										<div class="integration-row  integration-row-no-margin">
											<div class="integration-row-input-title">
												<?=Loc::getMessage(
													'REST_INTEGRATION_EDIT_TAB_' . $block . '_URL_HANDLER'
												)?>*
											</div>
											<div
												class="integration-row integration-row-padding-right integration-row-no-margin">
												<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
													<input
														type="url"
														name="<?=$block?>_URL_HANDLER"
														class="ui-ctl-element integration-required"
														placeholder="https://example.com/handler.php"
														value="<?=htmlspecialcharsbx($arResult[$block . '_DATA_URL'])?>"
													>
												</div>
											</div>
										</div>
										<div class="integration-row  integration-row-no-margin">
											<div class="integration-row-input-title">
												<?=Loc::getMessage(
													'REST_INTEGRATION_EDIT_TAB_' . $block . '_URL_INSTALL'
												)?>
											</div>
											<div class="integration-row integration-row-padding-right integration-row-no-margin">
												<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
													<input
														type="url"
														name="<?=$block?>_URL_INSTALL"
														class="ui-ctl-element"
														placeholder="https://example.com/install.php"
														value="<?=htmlspecialcharsbx($arResult[$block . '_DATA_URL_INSTALL'])?>"
													>
												</div>
											</div>
										</div>
									</div>
									<? if ($arResult['ALLOW_ZIP_APPLICATION']): ?>
										<div id="applicationZip">
											<div class="integration-row  integration-row-no-margin">
												<label class="ui-ctl ui-ctl-file-btn">
													<input
														type="file"
														name="APP_ZIP"
														class="ui-ctl-element"
														accept=".zip"
													>
													<span class="ui-ctl-label-text">
														<?=Loc::getMessage(
															"REST_INTEGRATION_EDIT_TAB_APPLICATION_ZIP_INPUT_LABEL"
														)?>
													</span>
												</label>
											</div>
										</div>
									<? endif; ?>
									<? if ($arResult['APPLICATION_DATA_ID'] > 0): ?>
										<div class="integration-row integration-row-padding-right integration-row-no-margin">
											<div class="integration-row-input-title">
												<?=Loc::getMessage('REST_INTEGRATION_EDIT_TAB_APPLICATION_CLIENT_ID')?>
											</div>
											<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
												<input
													type="text"
													name="APPLICATION_DATA_CLIENT_ID"
													class="ui-ctl-element"
													value="<?=$arResult['APPLICATION_DATA_CLIENT_ID']?>"
													readonly="readonly">
											</div>
										</div>
										<div class="integration-row integration-row-padding-right integration-row-no-margin">
											<div class="integration-row-input-title">
												<?=Loc::getMessage('REST_INTEGRATION_EDIT_TAB_APPLICATION_SECRET_ID')?>
											</div>
											<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
												<input
													type="text"
													name="APPLICATION_DATA_CLIENT_SECRET"
													class="ui-ctl-element"
													value="<?=$arResult['APPLICATION_DATA_CLIENT_SECRET']?>"
													readonly="readonly">
											</div>
										</div>
									<? endif;?>
									<? if (!empty($arResult[$block . '_DOWNLOAD_EXAMPLE_URL'])): ?>
										<div class="integration-row">
											<a
												href="<?=$arResult[$block . '_DOWNLOAD_EXAMPLE_URL']?>"
												class="integration-row-container-link integration-example-url"
											>
												<?=Loc::getMessage(
													'REST_INTEGRATION_EDIT_TAB_' . $block . '_DOWNLOAD_EXAMPLE_BTN'
												)?>
											</a>
										</div>
									<? elseif (!empty($arResult[$block . '_DOWNLOAD_EXAMPLE_TYPE'])): ?>
										<div class="integration-row">
											<a
												href="<?=$arResult['URI_EXAMPLE_DOWNLOAD'] . $arResult[$block . '_DOWNLOAD_EXAMPLE_TYPE']?>"
												class="integration-row-container-link integration-example-url integration-post-open"
											>
												<?=Loc::getMessage(
													'REST_INTEGRATION_EDIT_TAB_' . $block . '_DOWNLOAD_EXAMPLE_BTN'
												)?>
											</a>
										</div>
									<? endif; ?>
									<div class="integration-row integration-row-padding-right integration-row-no-margin">
										<label class="ui-ctl ui-ctl-checkbox ui-ctl-w100">
											<input
												type="checkbox"
												name="<?=$block?>_ONLY_API"
												class="ui-ctl-element"
												value="Y"
												<?=($arResult[$block . '_ONLY_API'] !== 'N') ? ' checked' : ''?>>
											<span class="ui-ctl-label-text integration-row-text-data">
												<?=Loc::getMessage('REST_INTEGRATION_EDIT_TAB_APPLICATION_ONLY_API')?>
											</span>
										</label>
									</div>
									<div
										id="applicationLang"
										class="integration-row integration-row-padding-right integration-row-no-margin"
									>
										<label class="ui-ctl ui-ctl-checkbox ui-ctl-w100">
											<input
												type="checkbox"
												name="<?=$block?>_MOBILE"
												class="ui-ctl-element"
												value="Y"
												<?=($arResult[$block . '_DATA_MOBILE'] === 'Y') ? 'checked' : ''?>
											>
											<span class="ui-ctl-label-text integration-row-text-data">
												<?=Loc::getMessage('REST_INTEGRATION_EDIT_TAB_APPLICATION_MOBILE')?>
											</span>
										</label>
										<?php
										$needShowMoreBtn = false;
										foreach ($arResult['LANG_LIST_AVAILABLE'] as $lid => $lang):
											$value = $arResult['APPLICATION_LANG_DATA'][$lid] ?? null;
											$required = in_array($lid, $arResult['LANG_LIST'], true);
											$hidden = empty($value) && !$required;
											if ($hidden)
											{
												$needShowMoreBtn = true;
											}
											?>
											<div class="integration-application-lang-block<?=$hidden ? ' hidden' : ''?>">
												<div class="integration-row-input-title">
													<?=Loc::getMessage(
														'REST_INTEGRATION_EDIT_TAB_APPLICATION_MENU_NAME'
													)?> <?=$lang?> (<?=$lid?>)<?=$required ? ' *' : ''?>
												</div>
												<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
													<input
														type="text"
														name="APPLICATION_LANG_NAME[<?=$lid?>]"
														class="ui-ctl-element<?=$required ? ' integration-required-lang' : ''?>"
														value="<?=htmlspecialcharsbx($value);?>"
													>
												</div>
											</div>
										<?php endforeach; ?>
										<?php if ($needShowMoreBtn):?>
											<span
												data-id="applicationLang"
												data-selector="integration-application-lang-block"
												data-required="integration-required-lang"
												class="integration-webhook-param-control-item integration-btn-dropdown integration-action-dropdown"
											><?=Loc::getMessage('REST_INTEGRATION_EDIT_SHOW_MORE_BTN')?></span>
										<?php endif;?>
									</div>
									<? if ($arResult['DESCRIPTION_' . $block]): ?>
										<div class="integration-row integration-row-padding-right">
											<? if (!empty($arResult['DESCRIPTION_' . $block]['TITLE'])): ?>
												<div class="integration-row-container-title-text">
													<?=$arResult['DESCRIPTION_' . $block]['TITLE']?>
												</div>
											<? endif; ?>
											<? if (!empty($arResult['DESCRIPTION_' . $block]['DESCRIPTION'])): ?>
												<div class="integration-row-container-description-text">
													<?=$arResult['DESCRIPTION_' . $block]['DESCRIPTION']?>
												</div>
											<? endif; ?>
										</div>
									<? endif; ?>
									<? if ($arResult['APPLICATION_DATA_ID'] > 0):?>
										<div class="integration-row">
											<? if ($arResult['APPLICATION_ONLY_API'] === 'N'): ?>
												<a
													href="<?=Url::getApplicationUrl($arResult['APPLICATION_DATA_ID'])?>"
													class="ui-btn ui-btn-primary"
												>
													<?=Loc::getMessage('REST_INTEGRATION_EDIT_APPLICATION_OPEN')?>
												</a>
											<? endif; ?>
											<? if (!empty($arResult[$block . '_DATA_URL_INSTALL'])): ?>
												<span
													onclick="BX.rest.Marketplace.reinstall('<?=$arResult['APPLICATION_DATA_ID']?>')"
													class="ui-btn ui-btn-primary"
												>
													<?=Loc::getMessage('REST_INTEGRATION_EDIT_APPLICATION_REINSTALL')?>
												</span>
											<? endif;?>
										</div>
									<? endif; ?>
								</div>
							</div>
						</div>
						<? break; ?>
					<? case 'WIDGET': ?>
						<div
							class="integration-tab <?=($arResult['WIDGET_NEEDED'] == 'Y') ? 'integration-tab-auto-open' : ''?>"
							data-role="integration-tab"
						>
							<? if ($i != 1): ?>
								<div class="integration-tab-title">
									<label for="integration-tab-<?=$i?>">
										<input
											type="checkbox"
											name="<?=$block?>_NEEDED"
											value="Y"
											id="integration-tab-<?=$i?>"
											class="integration-tab-checkbox"
											<?=($arResult[$block . '_NEEDED'] == 'Y') ? ' selected' : ''?>>
										<span>
											<?=Loc::getMessage('REST_INTEGRATION_EDIT_BLOCK_TITLE_' . $block)?>
										</span>
									</label>
								</div>
							<? else: ?>
								<input type="hidden" name="<?=$block?>_NEEDED" value="Y">
							<? endif; ?>
							<div class="integration-tab-wrapper">
								<div class="integration-tab-container">
									<div
										id="widgetLang"
										class="integration-row integration-row-padding-right integration-row-no-margin"
									>
										<?php
										$needShowMoreBtn = false;
										foreach ($arResult['LANG_LIST_AVAILABLE'] as $lid => $lang):
											$value = $arResult['WIDGET_LANG_LIST'][$lid]['TITLE'] ?? null;
											$required = in_array($lid, $arResult['LANG_LIST'], true);
											$hidden = empty($value) && !$required;
											if ($hidden)
											{
												$needShowMoreBtn = true;
											}
											?>
											<div class="integration-widget-lang-block<?=$hidden ? ' hidden' : ''?>">
												<div class="integration-row-input-title">
													<?=Loc::getMessage(
														'REST_INTEGRATION_EDIT_TAB_WIDGET_TITLE_NAME'
													)?> <?=$lang?> (<?=$lid?>)<?=$required ? ' *' : ''?>
												</div>
												<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
													<input
														type="text"
														name="WIDGET_LANG_LIST[<?=$lid?>][TITLE]"
														class="ui-ctl-element<?=$required ? ' integration-required-lang' : ''?>"
														value="<?=htmlspecialcharsbx($value);?>"
													>
												</div>
											</div>
										<?php endforeach; ?>
										<?php if ($needShowMoreBtn):?>
											<span
												data-id="widgetLang"
												data-selector="integration-widget-lang-block"
												data-required="integration-required-lang"
												class="integration-webhook-param-control-item integration-btn-dropdown integration-action-dropdown"
											>
												<?=Loc::getMessage('REST_INTEGRATION_EDIT_SHOW_MORE_BTN')?>
											</span>
										<?php endif;?>
									</div>
									<div class="integration-row-input-title">
										<?=Loc::getMessage('REST_INTEGRATION_EDIT_TAB_WIDGET_HANDLER_URL_TITLE')?>*
									</div>
									<div class="integration-row integration-row-padding-right integration-row-no-margin">
										<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
											<input
												type="url"
												name="WIDGET_HANDLER_URL"
												class="ui-ctl-element integration-required integration-required"
												placeholder="https://example.com/handler.php"
												value="<?=($arResult['WIDGET_HANDLER_URL']) ? htmlspecialcharsbx($arResult['WIDGET_HANDLER_URL']) : ''?>"
											>
										</div>
									</div>
									<? if (!empty($arResult[$block . '_DOWNLOAD_EXAMPLE_URL'])): ?>
										<div class="integration-row">
											<a
												href="<?=$arResult[$block . '_DOWNLOAD_EXAMPLE_URL']?>"
												class="integration-row-container-link integration-example-url"
											>
												<?=Loc::getMessage(
													'REST_INTEGRATION_EDIT_TAB_' . $block . '_DOWNLOAD_EXAMPLE_BTN'
												)?>
											</a>
										</div>
									<? elseif (!empty($arResult[$block . '_DOWNLOAD_EXAMPLE_TYPE'])): ?>
										<div class="integration-row">
											<a
												href="<?=$arResult['URI_EXAMPLE_DOWNLOAD'] .
												$arResult[$block . '_DOWNLOAD_EXAMPLE_TYPE']?>"
												class="integration-row-container-link integration-example-url integration-post-open"
											>
												<?=Loc::getMessage(
													'REST_INTEGRATION_EDIT_TAB_' . $block . '_DOWNLOAD_EXAMPLE_BTN'
												)?>
											</a>
										</div>
									<? endif; ?>
									<? if (isset($arResult['DESCRIPTION_' . $block]) && $arResult['DESCRIPTION_' . $block]): ?>
										<div class="integration-row integration-row-padding-right">
											<? if (!empty($arResult['DESCRIPTION_' . $block]['TITLE'])): ?>
												<div
													class="integration-row-container-title-text">
													<?=$arResult['DESCRIPTION_' . $block]['TITLE']?>
												</div>
											<? endif; ?>
											<? if (!empty($arResult['DESCRIPTION_' . $block]['DESCRIPTION'])): ?>
												<div class="integration-row-container-description-text">
													<?=$arResult['DESCRIPTION_' . $block]['DESCRIPTION']?>
												</div>
											<? endif; ?>
										</div>
									<? endif; ?>
									<div class="integration-row integration-row-padding-right">
										<div class="integration-row-input-title">
											<?=Loc::getMessage('REST_INTEGRATION_EDIT_TAB_WIDGET_LIST_TITLE')?>
										</div>
										<?
										$APPLICATION->IncludeComponent(
											"bitrix:rest.integration.selector",
											"",
											array(
												'ACTION' => 'Placement',
												'LIST' => $arResult['WIDGET_LIST'],
												'INPUT_NAME' => 'WIDGET_LIST',
												'INPUT_SCOPE_NAME' => 'SCOPE',
												'READONLY' => false,
												'MULTIPLE' => true,
												'CAN_REMOVE_TILES' => true,
												'CAN_EDIT' => true
											),
											false
										);
										?>

									</div>
								</div>
							</div>
						</div>
						<? break; ?>
				<? endswitch; ?>
			</div>
		</div>
		<? endforeach; ?>

		<? if ($arResult['SCOPE_NEEDED'] == 'Y'): ?>
			<div class="integration-container">
				<div class="integration-row">
					<div class="integration-tab-title integration-cursor-default">
						<label class="integration-tab-title-clear">
							<?=Loc::getMessage('REST_INTEGRATION_EDIT_BLOCK_TITLE_SCOPE')?>
						</label>
					</div>
					<div class="integration-row-padding-right">
						<?
						$APPLICATION->IncludeComponent(
							"bitrix:rest.integration.selector",
							"",
							array(
								'ACTION' => 'Scope',
								'LIST' => $arResult['SCOPE'],
								'INPUT_NAME' => 'SCOPE',
								'READONLY' => false,
								'MULTIPLE' => true,
								'CAN_REMOVE_TILES' => true,
								'CAN_EDIT' => true
							),
							false
						);
						?>
					</div>
					<? if (isset($arResult['DESCRIPTION_SCOPE']) && $arResult['DESCRIPTION_SCOPE']): ?>
						<div class="integration-row integration-row-padding-right">
							<? if (!empty($arResult['DESCRIPTION_SCOPE']['TITLE'])): ?>
								<div class="integration-row-container-title-text">
									<?=$arResult['DESCRIPTION_SCOPE']['TITLE']?>
								</div>
							<? endif; ?>
							<? if (!empty($arResult['DESCRIPTION_SCOPE']['DESCRIPTION'])): ?>
								<div class="integration-row-container-description-text">
									<?=$arResult['DESCRIPTION_SCOPE']['DESCRIPTION']?>
								</div>
							<? endif; ?>
						</div>
					<? endif; ?>
				</div>
			</div>
		<? endif; ?>
	</div>
</form>
<?
$actionBtn = [];
if (isset($arResult['READ_ONLY']) && $arResult['READ_ONLY'] === 'Y')
{
	if ($arResult['QUERY_NEEDED'] != 'D')
	{
		$actionBtn[] = [
			'TYPE' => 'save',
			'NAME' => 'gen_save',
			'CAPTION' => Loc::getMessage('REST_INTEGRATION_EDIT_BTN_SAVE_AND_GENERATE_WEBHOOK'),
			'ONCLICK' => 'BX.rest.integration.edit.actionSaveRegenBtnClick()'
		];
	}
}
else
{
	$actionBtn[] = [
		'TYPE' => 'save',
		'ONCLICK' => 'BX.rest.integration.edit.actionSaveBtnClick()'
	];
}
$actionBtn[] = 'close';

?>
<? $APPLICATION->IncludeComponent(
	'bitrix:ui.button.panel',
	'',
	array(
		'BUTTONS' => $actionBtn
	)
); ?>
<script>
	BX.message(<?=Json::encode(
		[
			'REST_INTEGRATION_EDIT_CONFIRM_POPUP_DESCRIPTION' => Loc::getMessage(
				"REST_INTEGRATION_EDIT_CONFIRM_POPUP_DESCRIPTION"
			),
			'REST_INTEGRATION_EDIT_CONFIRM_POPUP_TITLE' => Loc::getMessage(
				"REST_INTEGRATION_EDIT_CONFIRM_POPUP_TITLE"
			),
			'REST_INTEGRATION_EDIT_CONFIRM_POPUP_BTN_CONTINUE' => Loc::getMessage(
				"REST_INTEGRATION_EDIT_CONFIRM_POPUP_BTN_CONTINUE"
			),
			'REST_INTEGRATION_EDIT_TAB_APPLICATION_ZIP_INPUT_LABEL' => Loc::getMessage(
				"REST_INTEGRATION_EDIT_TAB_APPLICATION_ZIP_INPUT_LABEL"
			),
			'REST_INTEGRATION_EDIT_TAB_APPLICATION_ZIP_NO_FILE' => Loc::getMessage(
				"REST_INTEGRATION_EDIT_TAB_APPLICATION_ZIP_NO_FILE"
			),
			'REST_INTEGRATION_EDIT_CLOSE_SLIDER_CLOSE_TITLE' => Loc::getMessage("REST_INTEGRATION_EDIT_CLOSE_SLIDER_CLOSE_TITLE"),
			'REST_INTEGRATION_EDIT_CLOSE_SLIDER_CLOSE' => Loc::getMessage("REST_INTEGRATION_EDIT_CLOSE_SLIDER_CLOSE"),
			'REST_INTEGRATION_EDIT_CLOSE_SLIDER_YES' => Loc::getMessage("REST_INTEGRATION_EDIT_CLOSE_SLIDER_YES"),
			'REST_INTEGRATION_EDIT_CLOSE_SLIDER_CANCEL' => Loc::getMessage("REST_INTEGRATION_EDIT_CLOSE_SLIDER_CANCEL"),
		]
	);?>);
	var restIntegrationEditComponent = <?=Json::encode(
		[
			'signetParameters' => $this->getComponent()->getSignedParameters(),
			'queryProps' => ($arResult['QUERY_NEEDED'] != 'D' && !empty($arResult['QUERY'])) ? $arResult['QUERY'] : '',
			'pathIframe' => $arParams['PATH_TO_IFRAME'],
			"needGridOpen" => $arParams['NEED_GRID_OPEN'],
			'pathToGrid' => $arParams['PATH_TO_GRID'],
			'uriToMethodInfo' => $arResult['URI_METHOD_INFO'],
			'integrationCode' => $arParams['ELEMENT_CODE'],
			'needConfirmCloseSliderWithDelete' => $arResult['IS_NEW_OPEN']
		]
	);?>;
</script>