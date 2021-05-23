<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponentTemplate $this
 * @global CMain $APPLICATION
 */

use Bitrix\Main\Localization\Loc;

$c = \Bitrix\Main\Text\Converter::getHtmlConverter();

Loc::loadMessages($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/rest/scope.php');

if ($arResult["ERROR"]):
?>
<div class="content-edit-form-notice-error"><span class="content-edit-form-notice-text"><span class="content-edit-form-notice-icon"></span><?=$arResult["ERROR"]?></span></div>
<?php
endif;

if (empty($arResult["ERROR"]) && isset($_GET["success"])):
?>
<div class="content-edit-form-notice-successfully"><span class="content-edit-form-notice-text"><span class="content-edit-form-notice-icon"></span><?=Loc::getMessage("REST_HEVE_SUCCESS")?></span></div>
<?php
endif;
?>

<form method="post" action="<?=POST_FORM_ACTION_URI?>" enctype="multipart/form-data" name="bx_event_edit_form">
	<?=bitrix_sessid_post();?>
	<table id="content-edit-form-config" class="content-edit-form" cellspacing="0" cellpadding="0">

<?
if($arResult['INFO']['ID'] > 0):
?>

		<tr>
			<td class="content-edit-form-field-name content-edit-form-field-name-left" style="min-width: 300px;">
				<?=Loc::getMessage('REST_HEVE_APPLICATION_TOKEN')?><br />
				<span style="font-weight: normal;color:#AEA8A8"><?=Loc::getMessage("REST_HEVE_APPLICATION_TOKEN_DESC")?></span>
			</td>
			<td class="content-edit-form-field-text" style="min-width: 400px;">
				<input type="text" readonly="readonly" class="content-edit-form-field-input-text" value="<?=$c->encode($arResult['INFO']['APPLICATION_TOKEN'])?>" /><br />
				<input type="hidden" name="APPLICATION_TOKEN_REGEN" value="N" />
				<input type="checkbox" name="APPLICATION_TOKEN_REGEN" id="APPLICATION_TOKEN_REGEN" value="Y" /><label for="APPLICATION_TOKEN_REGEN"><?=Loc::getMessage('REST_HEVE_APPLICATION_TOKEN_REGEN')?></label><br />
			</td>
			<td class="content-edit-form-field-error"></td>
		</tr>

<?
endif;
?>


		<tr>
			<td class="content-edit-form-field-name content-edit-form-field-name-left" style="min-width: 300px;"><?=Loc::getMessage('REST_HEVE_EVENT_HANDLER')?></td>
			<td class="content-edit-form-field-text" style="min-width: 400px;">
				<input type="text" name="EVENT_HANDLER" class="content-edit-form-field-input-text" value="<?=$c->encode($arResult['INFO']['EVENT_HANDLER'])?>" /><br />
			</td>
			<td class="content-edit-form-field-error"></td>
		</tr>


		<tr>
			<td class="content-edit-form-field-name content-edit-form-field-name-left"><?=Loc::getMessage('REST_HEVE_TITLE')?></td>
			<td class="content-edit-form-field-text" >
				<input type="text" name="TITLE" class="content-edit-form-field-input-text" value="<?=$c->encode($arResult['INFO']['TITLE'])?>"/><br/>
			</td>
			<td class="content-edit-form-field-error"></td>
		</tr>

		<tr>
			<td class="content-edit-form-field-name content-edit-form-field-name-left"><?=Loc::getMessage('REST_HEVE_COMMENT')?></td>
			<td class="content-edit-form-field-textarea" >
				<textarea name="COMMENT" class="content-edit-form-field-input-textarea" style="max-width: 500px;"><?=$c->encode($arResult['INFO']['COMMENT'])?></textarea><br/>
			</td>
			<td class="content-edit-form-field-error"></td>
		</tr>


		<?php
if(is_array($arResult["EVENTS"])):
?>
		<tr>
			<td class="content-edit-form-field-name content-edit-form-field-name-left"><?=Loc::getMessage('REST_HEVE_EVENT_NAME')?><br/><span style="font-weight: normal;color:#AEA8A8"><?=Loc::getMessage("REST_HEVE_EVENT_NAME_DESC")?></span></td>
			<td class="content-edit-form-field-input" colspan="2">
				<table>
					<tr>
						<td valign="top">
<?php
	$hintHtml = '';
	foreach($arResult['EVENTS'] as $scope => $eventList)
	{
		$scopeHtml = '';
		foreach($eventList as $event)
		{
			$event = ToUpper($event);

			if(array_key_exists($event, $arResult['EVENTS_DESC']))
			{
				$eventTitle = $arResult['EVENTS_DESC'][$event]['name'] <> ''
					? $arResult['EVENTS_DESC'][$event]['name']
					: $arResult['EVENTS_DESC'][$event]['code'];
				$eventDescription = '<b>'.$eventTitle.'</b><p>'.$arResult['EVENTS_DESC'][$event]['descr'].'</p>';

				$selected = $event === $arResult['INFO']['EVENT_NAME'];

				$event = $c->encode($event);

				$hintHtml .= '<div id="hint_'.$event.'" style="display:'.($selected ? 'block' : 'none').'">'.$eventDescription.'</div>';

				$eventTitle = $c->encode($eventTitle).' <small>('.$event.')</small>';
				$scopeHtml .= '<input type="radio" name="EVENT_NAME" id="'.$event.'" value="'.$event.'" '.($selected ? ' checked="checked"' : '').' onclick="showEventHint(this.value)" /><label for="'.$event.'">'.$eventTitle.'</label><br />';
			}
		}

		if($scopeHtml != '')
		{
			$scopeName = $c->encode(Loc::getMessage("REST_SCOPE_".toUpper($scope)));
?>
				<b><?=$scopeName?></b>
				<div style="padding-left: 20px;"><?=$scopeHtml?></div>
<?
		}
	}
?>
						</td>
						<td style="width: 300px; padding-left: 40px;" id="event_hint" valign="top"><?=$hintHtml?></td>
					</tr>
				</table>
				<script>var selectedHint = '<?=CUtil::JSEscape($arResult['INFO']['EVENT_NAME'])?>'</script>
			</td>
		</tr>
<?php
endif;
?>
		<tr>
			<td></td>
			<td style="padding-top: 25px">
				<span onclick="BX.addClass(this, 'webform-button-wait webform-button-active');BX.submit(document.forms['bx_event_edit_form']);" class="webform-button webform-button-create"><?=GetMessage("REST_HEVE_SAVE")?></span>
			</td>
			<td class="content-edit-form-field-error"></td>
		</tr>
	</table>
</form>
<script>
	function showEventHint(e)
	{
		if(!!selectedHint)
		{
			BX.hide(BX('hint_' + selectedHint))
		}
		BX.show(BX('hint_' + e));
		selectedHint = e;
	}
</script>