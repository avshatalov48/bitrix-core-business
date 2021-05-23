<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;
use Bitrix\Sender\Internals\PrettyDate;
use Bitrix\Sender\Message\ConfigurationOption as ConOpt;

/** @var CAllMain $APPLICATION */
/** @var array $arParams */
/** @var array $arResult */
$containerId = 'bx-sender-template-edit';
Bitrix\Main\UI\Extension::load(
	[
		'ui',
	]
);
CJSCore::Init(array('admin_interface'));
?>
<script type="text/javascript">
	BX.ready(function () {
		BX.Sender.Message.Editor.init(<?=Json::encode(array(
			'containerId' => $containerId,
			'actionUrl' => $arResult['ACTION_URL'],
			'isFrame' => $arParams['IFRAME'] == 'Y',
			'isSaved' => $arResult['IS_SAVED'],
			'prettyDateFormat' => PrettyDate::getDateFormat(),
			'mess' => array(
				'patternTitle' => Loc::getMessage('SENDER_TEMPLATES_EDIT_TMPL_PATTERN_TITLE'),
				'newTitle' => Loc::getMessage('SENDER_TEMPLATES_EDIT_TMPL_NEW_TITLE'),
			)
		))?>);
	});
</script>

<div id="<?=htmlspecialcharsbx($containerId)?>" class="sender-template-edit-wrap">

	<?
	$APPLICATION->IncludeComponent("bitrix:sender.ui.panel.title", "", array('LIST' => array(
		array('type' => 'buttons', 'list' => array(
			array('type' => 'feedback')
		)),
	)));
	?>

	<form method="post" action="<?=htmlspecialcharsbx($arResult['SUBMIT_FORM_URL'])?>">
		<?=bitrix_sessid_post()?>

		<div class="bx-sender-letter-field" style="<?=($arParams['IFRAME'] == 'Y' ? 'display: none;' : '')?>">
			<div class="bx-sender-caption">
				<?=Loc::getMessage('SENDER_TEMPLATES_EDIT_TMPL_FIELD_NAME')?>:
			</div>
			<div class="bx-sender-value">
				<input data-role="templates-title" type="text" name="NAME" value="<?=htmlspecialcharsbx($arResult['ROW']['NAME'])?>" class="bx-sender-form-control bx-sender-letter-field-input">
			</div>
		</div>


		<div data-bx-selector="" style="<?=(!$arResult['SHOW_TEMPLATE_SELECTOR'] ? 'display: none;' : '')?>">
			<div class="bx-sender-letter-field">
				<div class="bx-sender-caption"></div>
				<div class="bx-sender-value">
					<?
					if ($arResult['USE_TEMPLATES'])
					{
						$APPLICATION->IncludeComponent(
							"bitrix:sender.template.selector",
							"",
							array(
								"TYPE" => '',
								"CACHE_TIME" => "60",
								"CACHE_TYPE" => "N",
							)
						);
					}
					?>
				</div>
			</div>
		</div>
		<div data-bx-editor="" style="<?=($arResult['SHOW_TEMPLATE_SELECTOR'] ? 'display: none;' : '')?>">

			<!--
			<div class="bx-sender-letter-field">
				<div class="bx-sender-caption"></div>
				<div class="bx-sender-value">
					<a data-bx-change-btn="" href="javascript: void(0);">
						Choose base template
					</a>
				</div>
			</div>
			-->

			<div class="bx-sender-letter-field">
				<div class="bx-sender-value">
					<?
					switch ($arResult['ROW']['TYPE'])
					{
						case ConOpt::TYPE_MAIL_EDITOR:
						default:
							$APPLICATION->IncludeComponent(
								"bitrix:sender.mail.editor",
								"",
								array(
									"INPUT_NAME" => 'CONTENT',
									"VALUE" => $arResult['ROW']['CONTENT'],
									"TEMPLATE_TYPE" => '',
									"TEMPLATE_ID" => '',
									"CONTENT_URL" => $arResult['ROW']['CONTENT_URL'],
									"IS_TEMPLATE_MODE" => false,
									"IS_TRIGGER" => false,
								),
								null
							);
							break;
					}
					?>
				</div>
			</div>

		</div>

		<?
		$APPLICATION->IncludeComponent(
			"bitrix:sender.ui.button.panel",
			"",
			array(
				'SAVE' => $arParams['CAN_EDIT'] ? [] : null,
				'CANCEL' => array(
					'URL' => $arParams['PATH_TO_LIST']
				),
			),
			false
		);
		?>

	</form>

</div>