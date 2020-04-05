<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

/**
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $component
 * @global CMain $APPLICATION
 * @global CUser $USER
 */
?>
<?if ($arResult["MESSAGE"]):?>
	<?ShowError($arResult["MESSAGE"]);?>
<?else:?>
	<div class="code-p-wrap" id="recovery-codes-container">
		<div class="code-p-title"><?=GetMessage("SEC_CODES")?></div>
		<div class="code-p-block">
			<div class="code-p-text">
				<?=GetMessage("SEC_INFO")?>
			</div>
			<div class="code-p-list">
				<div class="code-p-list-r">
					<?=GetMessage("SEC_NEW_CODES")?>
					<br/>
					<span class="webform-small-button webform-small-button-accept" data-role="regenerate-button"><?=GetMessage("SEC_GENERATE")?></span>
				</div>
				<ul class="code-p-list-l">
					<li style="display:none" data-role="code-template" data-autoclear="yes" data-used-class="code-p-list-item-deactivate" class="code-p-list-item">
						<div data-code-template-role="code" class="code-p-list-code">#CODE#</div>
						<div data-code-template-role="using-date" data-visible-on-used="yes" class="code-p-list-code-date">#DATE#</div>
					</li>
				</ul>
			</div>
			<div class="code-p-tip">
				<?=GetMessage("SEC_SHORT_NOTICE")?>
			</div>
		</div>
		<a href="<?=$APPLICATION->GetCurPageParam('action=print&ncc=1')?>" class="webform-button webform-button-blue" target="_blank"><?=toUpper(GetMessage("SEC_PRINT"))?></a>
		<a href="<?=$APPLICATION->GetCurPageParam('action=download&ncc=1')?>" class="webform-button"><?=GetMessage("SEC_SAVE")?></a>
	</div>
	<?
	$jsCodes = array();
	foreach($arResult['CODES'] as $code)
	{
		$jsCodes[] = array(
			'VALUE' => $code['VALUE'],
			'USED' => $code['USED'],
			'USING_DATE' => strval($code['USING_DATE'])
		);
	}
	?>
	<script>
		BX.ready(function createOtp()
		{
			var recoveryCodes = new BX.Security.UserRecoveryCodes();
			recoveryCodes.drawRecoveryCodes(<?=\Bitrix\Main\Web\Json::encode($jsCodes)?>);
		});
	</script>
<?endif?>
