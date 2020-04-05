<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

\Bitrix\Main\Page\Asset::getInstance()->addCss('/bitrix/js/crm/css/crm.css');
if(\CCrmSipHelper::isEnabled())
	\Bitrix\Main\Page\Asset::getInstance()->addJs('/bitrix/js/crm/common.js');

$publicMode = isset($arParams["PUBLIC_MODE"]) && $arParams["PUBLIC_MODE"] === true;
?><table cellpadding="0" cellspacing="0" class="field_crm"><?
	$_suf = rand(1, 100);
	foreach ($arResult["VALUE"] as $entityType => $arEntity):
		?><tr><?
		if($arParams['PREFIX']):
			?><td class="field_crm_entity_type">
			<?=GetMessage('CRM_ENTITY_TYPE_'.$entityType)?>:
			</td><?
		endif;
		?><td class="field_crm_entity"><?

		$first = true;
		foreach ($arEntity as $entityId => $entity)
		{
			echo !$first ? ', ': '';

			if ($publicMode)
			{
				?><?=htmlspecialcharsbx($entity['ENTITY_TITLE'])?><?
			}
			else
			{
				?><a href="<?=htmlspecialcharsbx($entity['ENTITY_LINK'])?>" target="_blank"
					 id="balloon_<?=$entityType."_".$entityId."_".$_suf?>"><?=htmlspecialcharsbx($entity['ENTITY_TITLE'])?></a><?
			}

			$first = false;
		};

		?></td>
		</tr><?
	endforeach;
	?></table>

<?if(\CCrmSipHelper::isEnabled()):?>
<script type="text/javascript">
	BX.ready(
		function()
		{
			if(typeof(window["BXIM"]) === "undefined" || typeof(BX.CrmSipManager) === "undefined")
			{
				return;
			}

			if(typeof(BX.CrmSipManager.messages) === "undefined")
			{
				BX.CrmSipManager.messages =
				{
					"unknownRecipient": "<?= GetMessageJS('CRM_SIP_MGR_UNKNOWN_RECIPIENT')?>",
					"makeCall": "<?= GetMessageJS('CRM_SIP_MGR_MAKE_CALL')?>"
				};
			}

			var sipMgr = BX.CrmSipManager.getCurrent();
			sipMgr.setServiceUrl(
				"CRM_<?=CUtil::JSEscape(CCrmOwnerType::LeadName)?>",
				"/bitrix/components/bitrix/crm.lead.show/ajax.php?<?=bitrix_sessid_get()?>"
			);

			sipMgr.setServiceUrl(
				"CRM_<?=CUtil::JSEscape(CCrmOwnerType::ContactName)?>",
				"/bitrix/components/bitrix/crm.contact.show/ajax.php?<?=bitrix_sessid_get()?>"
			);

			sipMgr.setServiceUrl(
				"CRM_<?=CUtil::JSEscape(CCrmOwnerType::CompanyName)?>",
				"/bitrix/components/bitrix/crm.company.show/ajax.php?<?=bitrix_sessid_get()?>"
			);
		}
	);
</script>
<? endif ?>

<? if (!$publicMode):?>
	<?CJSCore::Init('tooltip');?>
	<script type="text/javascript">
		<?foreach ($arResult["VALUE"] as $entityType => $arEntity):?>
		<?foreach ($arEntity as $entityId => $entity):?>
		BX.tooltip(<?=$entityId?>, "balloon_<?=$entityType?>_<?=$entityId?>_<?=$_suf?>", "/bitrix/components/bitrix/crm.<?=strtolower($entityType)?>.show/card.ajax.php", "crm_balloon<?=($entityType == 'LEAD' || $entityType == 'DEAL'? '_no_photo': '_'.strtolower($entityType))?>", true);
		<?endforeach;?>
		<?endforeach;?>
	</script>
<? endif ?>