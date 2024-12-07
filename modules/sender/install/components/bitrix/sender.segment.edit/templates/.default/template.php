<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Web\Json;
use Bitrix\Sender\Internals\PrettyDate;
use Bitrix\Sender\Posting\SegmentDataBuilder;

/** @var CMain $APPLICATION */
/** @var array $arParams */
/** @var array $arResult */

Extension::load([
	"pull.client",
	'ui.notification',
	'ui',
	'ui.alerts',
]);
$containerId = 'bx-sender-segment-edit';
?>
<div id="<?=htmlspecialcharsbx($containerId)?>" class="bx-sender-segment-edit-wrapper">

	<?
	$APPLICATION->IncludeComponent("bitrix:sender.ui.panel.title", "", array('LIST' => array(
		array('type' => 'buttons', 'list' => array(
			array('type' => 'feedback')
		)),
	)));
	?>

	<form name="post_form" method="post" action="<?=htmlspecialcharsbx($arResult['SUBMIT_FORM_URL'])?>">
		<?=bitrix_sessid_post()?>

		<div class="bx-sender-letter-field" style="<?=(isset($arParams['IFRAME']) && $arParams['IFRAME'] == 'Y' ? 'display: none;' : '')?>">
			<div class="bx-sender-caption">
				<?=Loc::getMessage('SENDER_SEGMENT_EDIT_TMPL_FIELD_NAME')?>
			</div>
			<div class="bx-sender-value">
				<input data-role="segment-title" type="text" name="NAME" value="<?=htmlspecialcharsbx($arResult['ROW']['NAME'])?>" class="bx-sender-form-control bx-sender-letter-field-input">
			</div>
		</div>

		<?php if (!$arResult['PREPARED']): ?>
		<div class="ui-alert ui-alert-warning">
			<span class="ui-alert-message"><?php echo Loc::getMessage('SENDER_SEGMENT_NOT_PREPARED') ?></span>
		</div>
		<?php endif; ?>

		<div class="bx-sender-letter-field" style="<?=(!$arParams['CAN_EDIT'] || $arParams['ONLY_CONNECTOR_FILTERS'] ? 'display: none;' : '')?>">
			<div class="bx-sender-caption">

			</div>
			<div class="bx-sender-value">

				<div class="sender-group-address-counter">
					<?
					if(count($arResult['CONNECTOR']['AVAILABLE']) > 0):
						?>
						<a data-bx-button="" class="ui-btn ui-btn-primary ui-btn-dropdown"><?=Loc::getMessage('SENDER_SEGMENT_EDIT_TMPL_BTN_ADD')?></a>
						<?
					endif;
					?>

					<span class="sender-mailing-sprite sender-group-address-counter-img"></span>
					<span class="sender-box-list-item-caption-additional-less"><?=Loc::getMessage('SENDER_SEGMENT_EDIT_TMPL_SELECTED')?>:</span>
					<span data-bx-counter="" style="display: none;"><?
						echo intval($arResult['CONNECTOR']['EXISTED_ADDRESS_COUNT'])
					?></span><?
					?><span class="sender-box-list-item-caption-additional-less" style="<?=($arParams['DATA_TYPE_ID'] ? 'display: none;' : '')?>"><?
						?><span class=""></span>
						<span data-bx-count-info=""></span>
					</span>
				</div>

			</div>
		</div>




		<script type="text/template" id="connector-template-filter">
			<?
			ob_start();
			?><div data-bx-item="%CONNECTOR_NUM%"
				data-code="%CONNECTOR_MODULE_ID%_%CONNECTOR_CODE%"
				data-bx-item-filter="%CONNECTOR_FILTER_ID%"
				data-result-viewable="%CONNECTOR_IS_RESULT_VIEWABLE%"
				class="sender-box-connector"
			>
				<div class="sender-box-name">
					%CONNECTOR_NAME%
					<span class="sender-box-close" style="<?=($arParams['ONLY_CONNECTOR_FILTERS'] ? 'display: none;' : '')?>">
						<span data-bx-item-remove="" class="sender-mailing-sprite sender-box-list-item-caption-delete"></span>
					</span>
				</div>
				<div class="sender-box-filter">
					%CONNECTOR_FORM%
				</div>
				<div class="sender-box-desc">
					<span class="sender-box-list-item-caption-additional">
						<span class="sender-box-list-item-caption-additional-less"><?=Loc::getMessage('SENDER_SEGMENT_EDIT_TMPL_SELECTED')?>:</span>
						<span class="sender-segment-edit-count">
							<span data-bx-item-counter="">%CONNECTOR_COUNT%</span>
							<span style="<?=($arParams['DATA_TYPE_ID'] ? 'display: none;' : '')?>">
								<span data-bx-item-count-info="%CONNECTOR_COUNTER%" class=""></span>
							</span>
						</span>
						<span class="sender-segment-edit-loader">
							<svg class="sender-segment-edit-circular" viewBox="25 25 50 50">
								<circle class="sender-segment-edit-path" cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"></circle>
								<circle class="sender-segment-edit-inner-path" cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"></circle>
							</svg>
						</span>
					</span>
					<?if($arParams['CAN_VIEW_CONN_DATA']):?>
					<span data-bx-item-result-view=""
						class="ui-btn ui-btn-xs ui-btn-secondary ui-btn-no-caps"
						style="float: right; display: none;"
					>
						<?=Loc::getMessage('SENDER_SEGMENT_EDIT_TMPL_VIEW')?>
					</span>
					<?endif;?>
				</div>
				<input data-bx-item-filter="" type="hidden" name="CONNECTOR_SETTING[%CONNECTOR_MODULE_ID%][%CONNECTOR_CODE%][%CONNECTOR_NUM%]" value="%CONNECTOR_FILTER%">
			</div>
			<?
			$connectorFilterTemplate = ob_get_clean();
			echo $connectorFilterTemplate;
			?>
		</script>
		<script type="text/template" id="connector-template">
			<?
			ob_start();
			?><div data-bx-item="%CONNECTOR_NUM%" data-code="%CONNECTOR_MODULE_ID%_%CONNECTOR_CODE%" class="sender-box-connector sender-box-list-item-hidden">
				<div class="sender-box-name">
					%CONNECTOR_NAME%
					<span class="sender-box-close" style="<?=($arParams['ONLY_CONNECTOR_FILTERS'] ? 'display: none;' : '')?>">
						<span data-bx-item-remove="" class="sender-mailing-sprite sender-box-list-item-caption-delete"></span>
					</span>
				</div>
				<div data-bx-item-toggler="" class="sender-box-list-item-caption">
					<?=Loc::getMessage('SENDER_SEGMENT_EDIT_TMPL_SET_FILTER')?>
				</div>
				<div class="sender-box-desc">
					<span class="sender-box-list-item-caption-additional">
						<span class="sender-box-list-item-caption-additional-less"><?=Loc::getMessage('SENDER_SEGMENT_EDIT_TMPL_SELECTED')?>:</span>
						<span class="sender-segment-edit-count">
							<span data-bx-item-counter="">%CONNECTOR_COUNT%</span>
							<span style="<?=($arParams['DATA_TYPE_ID'] ? 'display: none;' : '')?>">
								<span data-bx-item-count-info="%CONNECTOR_COUNTER%" class=""></span>
							</span>
						</span>
						<span class="sender-segment-edit-loader">
							<svg class="sender-segment-edit-circular" viewBox="25 25 50 50">
								<circle class="sender-segment-edit-path" cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"></circle>
								<circle class="sender-segment-edit-inner-path" cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"></circle>
							</svg>
						</span>
					</span>
					<?if($arParams['CAN_VIEW_CONN_DATA']):?>
						<span data-bx-item-result-view=""
							class="ui-btn ui-btn-xs ui-btn-secondary ui-btn-no-caps"
							style="float: right; display: none;"
						>
						<?=Loc::getMessage('SENDER_SEGMENT_EDIT_TMPL_VIEW')?>
					</span>
					<?endif;?>
				</div>
				<div class="sender-box-list-item-block connector_form_container">
					<div class="sender-box-list-item-block-item">%CONNECTOR_FORM%</div>
					<div style="text-align: center;">
						<span data-bx-item-close="" class="ui-btn ui-btn-light-border">
							<?=Loc::getMessage('SENDER_SEGMENT_EDIT_TMPL_CLOSE')?>
						</span>
					</div>
				</div>
			</div>
			<?
			$connectorTemplate = ob_get_clean();
			echo $connectorTemplate;
			?>
		</script>

		<div class="sender-box-list">
			<div data-bx-list="" >
				<?php
				foreach($arResult['CONNECTOR']['EXISTED'] as $existedConnector)
				{
					if ($existedConnector['ID'] == 'sender_contact_list')
					{
						continue;
					}

					$replace = array();
					foreach ($existedConnector as $key => $value)
					{
						if (!is_array($value) && $key != 'FORM')
						{
							$value = htmlspecialcharsbx((string) $value);
						}
						$replace["%CONNECTOR_$key%"] = $value;
					}

					$subject = $existedConnector['IS_FILTER'] ? $connectorFilterTemplate : $connectorTemplate;

					$keys = array_keys($replace);
					$values = array_values($replace);
					$size = count($keys);
					for($counter = 0; $counter < $size; $counter++)
					{
						$key = $keys[$counter];
						$value = !is_array($values[$counter]) ? $values[$counter] : '';
						$subject = str_replace(
							$key,
							$value,
							$subject
						);
					}
					echo $subject;
				}
				?>
			</div>
			<div class="sender-box-informer">
				<div class="sender-box-informer-text"><?=Loc::getMessage('SENDER_SEGMENT_EDIT_TMPL_DYNAMIC_DESC')?></div>
				<div data-hint="<?=Loc::getMessage('SENDER_SEGMENT_EDIT_TMPL_DYNAMIC_HINT')?>"></div>
			</div>
		</div>
		<?if($arResult['CAN_ADD_PERSONAL_CONTACTS']):?>
		<div class="sender-box-list">
			<div class="sender-box-name"><?=Loc::getMessage('SENDER_SEGMENT_EDIT_TMPL_LIST1')?></div>
			<div class="sender-flex-control">
				<?
				$APPLICATION->IncludeComponent('bitrix:sender.ui.tile.selector', '', array(
					'ID' => 'sender-segment-contacts',
					'LIST' => $arResult['CONTACTS']['TILES'],
					'SHOW_BUTTON_ADD' => $arParams['CAN_EDIT'],
					'SHOW_BUTTON_SELECT' => $arParams['SHOW_CONTACT_SETS'],
					'BUTTON_SELECT_CAPTION' => Loc::getMessage('SENDER_SEGMENT_EDIT_BUTTON_SELECT_CONTACT'),
					'BUTTON_ADD_CAPTION' => Loc::getMessage('SENDER_SEGMENT_EDIT_TMPL_LOAD'),
					'READONLY' => !$arParams['CAN_EDIT'],
					'MULTIPLE' => false,
				));
				?>
				<input data-role="contact_list" type="hidden"
					name="CONNECTOR_SETTING[sender][contact_list][0]"
					value="<?=htmlspecialcharsbx($arResult['CONTACTS']['VALUE'])?>"
				>
			</div>
			<div class="sender-box-informer">
				<div class="sender-box-informer-text"><?=Loc::getMessage('SENDER_SEGMENT_EDIT_TMPL_LIST_DESC')?></div>
				<div data-hint="<?=Loc::getMessage('SENDER_SEGMENT_EDIT_TMPL_LIST_HINT1')?>"></div>
			</div>
		</div>
		<?endif;?>

		<?
		$APPLICATION->IncludeComponent(
			"bitrix:sender.ui.button.panel",
			"",
			array(
				'CHECKBOX' => $arParams['CAN_EDIT'] ?
					[
						'NAME' =>  'HIDDEN',
						'CAPTION' =>  $arResult['HIDDEN'] ?
							Loc::getMessage('SENDER_SEGMENT_EDIT_TMPL_BTN_HIDE_IN_LIST')
							:
							Loc::getMessage('SENDER_SEGMENT_EDIT_TMPL_BTN_HIDDEN'),
						'CHECKED' =>  $arResult['ROW']['HIDDEN'] === 'Y',
						'DISPLAY' =>  true,
						'HINT' =>  Loc::getMessage('SENDER_SEGMENT_EDIT_TMPL_BTN_HIDDEN_HINT'),
					]
					:
					null
				,
				'SAVE' => $arParams['CAN_EDIT'] ? [] : null,
				'CANCEL' => array(
					'URL' => $arParams['PATH_TO_LIST'],
				),
			),
			false
		);
		?>

	</form>

	<script>
		BX.ready(function () {

			BX.Loc.setMessage(
				'SENDER_SEGMENT_SEARCH_INFORMATION',
				'<?=Loc::getMessage("SENDER_SEGMENT_SEARCH_INFORMATION")?>'
			);

			window.bxConnectorManager = BX.Sender.Connector.Manager.init(<?=Json::encode(array(
				'groupId' => $arParams['ID'],
				'containerId' => $containerId,
				'actionUri' => $arResult['ACTION_URI'],
				'isFrame' => isset($arParams['IFRAME']) && $arParams['IFRAME'] == 'Y',
				'isSaved' => $arResult['IS_SAVED'],
				'canViewConnData' => $arParams['CAN_VIEW_CONN_DATA'],
				'onlyConnectorFilters' => $arParams['ONLY_CONNECTOR_FILTERS'],
				'showContactSets' => $arParams['SHOW_CONTACT_SETS'],
				'contactTileNameTemplate' => $arResult['CONTACTS']['TILE_NAME_TEMPLATE'],
				'pathToContactList' => $arParams['PATH_TO_CONTACT_LIST'],
				'pathToResult' => $arParams['PATH_TO_RESULT'],
				'pathToContactImport' => $arParams['PATH_TO_CONTACT_IMPORT'],
				'segmentTile' => $arResult['SEGMENT_TILE'],
				'availableConnectors' => array_values($arResult['CONNECTOR']['AVAILABLE']),
				'prettyDateFormat' => PrettyDate::getDateFormat(),
				'filterCounterTag' => SegmentDataBuilder::FILTER_COUNTER_TAG,
				'mess' => array(
					'patternTitle' => Loc::getMessage('SENDER_SEGMENT_EDIT_TMPL_PATTERN_TITLE1') ?: Loc::getMessage('SENDER_SEGMENT_EDIT_TMPL_PATTERN_TITLE'),
					'newTitle' => Loc::getMessage('SENDER_SEGMENT_EDIT_TMPL_NEW_TITLE'),
					'filterPlaceholder' => Loc::getMessage('SENDER_SEGMENT_EDIT_TMPL_FLT_PLACEHOLDER'),
					'filterPlaceholderCrmLead' => Loc::getMessage('SENDER_SEGMENT_EDIT_TMPL_FLT_PLACEHOLDER_CRM_LEAD'),
					'filterPlaceholderCrmClient' => Loc::getMessage('SENDER_SEGMENT_EDIT_TMPL_FLT_PLACEHOLDER_CRM_CLIENT'),
					'contactSearchTitle' => Loc::getMessage('SENDER_SEGMENT_EDIT_CONTACT_SEARCHER_TITLE'),
				)
			))?>);

		});
	</script>

	<?php if ($arResult['IS_NEW']): ?>
		<script>
			BX.ready(function() {
				setTimeout(function(){
					BX.UI.Notification.Center.notify({
						content: '<?=CUtil::JSEscape(htmlspecialcharsbx(Loc::getMessage('SENDER_SEGMENT_CREATED')))?>',
						position: 'top-right',
						autoHideDelay: 15000,
					});
				})
			}, 1000);
		</script>
	<?php endif; ?>
</div>