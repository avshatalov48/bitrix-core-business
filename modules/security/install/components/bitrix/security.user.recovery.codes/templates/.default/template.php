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

\Bitrix\Main\UI\Extension::load(['ui.fonts.opensans', 'ui.buttons']);
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
					<br/>
					<span class="ui-btn ui-btn-md ui-btn-success" data-role="regenerate-button"><?=GetMessage("SEC_GENERATE")?></span>
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
		<?if (\Bitrix\Main\Context::getCurrent()->getRequest()->isPost()):?>
			<a href="javascript:void(0)" onclick="BX.Intranet.UserProfile.Security.showRecoveryCodesComponent('print')" class="ui-btn ui-btn-lg ui-btn-primary" target="_blank"><?=mb_strtoupper(GetMessage("SEC_PRINT"))?></a>
		<?else:?>
			<a href="<?=$APPLICATION->GetCurPageParam('codesAction=print&ncc=1')?>" class="ui-btn ui-btn-lg ui-btn-primary" target="_blank"><?=mb_strtoupper(GetMessage("SEC_PRINT"))?></a>
		<?endif?>
		<?
		if (isset($arParams["PATH_TO_CODES"]))
		{
			$pathToCodes = $arParams["PATH_TO_CODES"]."?codesAction=download&ncc=1";
		}
		else
		{
			$pathToCodes = $APPLICATION->GetCurPageParam('codesAction=download&ncc=1');
		}
		?>
		<a href="<?=$pathToCodes?>" target="_blank" class="ui-btn ui-btn-lg ui-btn-light-border"><?=GetMessage("SEC_SAVE")?></a>
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
			var recoveryCodes = new BX.Security.UserRecoveryCodes({
				signedParameters: '<?=$this->getComponent()->getSignedParameters()?>',
				componentName: '<?=$this->getComponent()->getName() ?>'
			});
			recoveryCodes.drawRecoveryCodes(<?=\Bitrix\Main\Web\Json::encode($jsCodes)?>);
		});
	</script>
<?endif?>
