<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Landing\Manager;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
\CJSCore::init(['landing_master', 'action_dialog']);
?>

<div class="landing-form-site-name-block" id="ui-editable-domain">
	<?if (Manager::isB24()):
		$allowedDomains = array(
			'b24' => array(
				'postfix' => $arResult['POSTFIX'],
				'title' => 'B24 domain'
			),
			'own' => array(
				'postfix' => '',
				'title' => 'Own domain'
			)
		);
		?>
		<input type="hidden" name="<?= $arParams['FIELD_NAME'];?>_ID" id="<?= $arParams['FIELD_ID'];?>_id" value="<?= $arParams['DOMAIN_ID'];?>" />
		<input type="hidden" name="<?= $arParams['FIELD_NAME'];?>" id="<?= $arParams['FIELD_ID'];?>" value="<?= $arResult['DOMAIN_NAME'];?>" />
		<span class="landing-form-site-name-wrap">
			<span class="landing-form-site-name-label" id="<?= $arParams['FIELD_ID'];?>_title"><?= $arResult['DOMAIN_NAME'];?></span>
			<span class="ui-title-input-btn  ui-domain-input-btn-js ui-editing-pen"></span>
		</span>
		<div id="ui-editable-domain-content" class="ui-editable-domain-content" style="display: none;">
			<?
			$wasSelected = false;
			$counter = 0;
			$rndName = \randString();
			foreach ($allowedDomains as $domainCode => $domainItem):
				$counter++;
				$selected = false;
				$domainNameLocal = $arResult['DOMAIN_NAME'];
				if ($domainItem['postfix'] && (mb_substr($domainNameLocal, -1 * mb_strlen($domainItem['postfix'])) == $domainItem['postfix']))
				{
					$wasSelected = $selected = true;
					$domainNameLocal = mb_substr($domainNameLocal, 0, -1 * mb_strlen($domainItem['postfix']));
				}
				if ($domainCode == 'own' && !$wasSelected)
				{
					$wasSelected = $selected = true;
				}
				?>
				<?if ($domainCode != 'own'):?>
				<div class="ui-control-wrap landing-popup-control-wrap">
					<input type="radio" name="DOMAIN_NAME_<?= $rndName;?>" <?
						?>id="landing-domain-name-<?= $counter;?>" <?
						?>value="<?= $domainItem['postfix'];?>" <?
						?>class="ui-radio ui-postfix" <?
						?>data-input-id="<?= $arParams['FIELD_ID'];?>_<?= $domainCode;?>" />
					<div class="landing-form-domainname-wrap">
						<label class="ui-form-control-label" for="landing-domain-name-<?= $counter;?>"><?= Loc::getMessage('LANDING_TPL_DOMAIN_NAME_'.mb_strtoupper($domainCode));?></label>
						<input type="text" id="<?= $arParams['FIELD_ID'];?>_<?= $domainCode;?>" value="<?= $selected ? $domainNameLocal : '';?>" class="ui-input ui-domainname ui-domainname-subdomain" data-postfix="<?= $domainItem['postfix'];?>" />
						<span class="landing-site-name-postfix"><?= $domainItem['postfix'];?></span>
						<div class="landing-site-name-status" id="landing-site-name-status-subdomain"></div>
					</div>
				</div>
				<?elseif ($domainCode == 'own'):?>
				<div class="ui-control-wrap landing-popup-control-wrap">
					<input type="radio" name="DOMAIN_NAME_<?= $rndName;?>" <?
						?>id="landing-domain-name-<?= $counter;?>" <?
						?>value="<?= $domainItem['postfix'];?>" <?
						?>class="ui-radio ui-postfix" <?
						?>data-input-id="<?= $arParams['FIELD_ID'];?>_<?= $domainCode;?>" />
					<div class="landing-form-domainname-wrap">
						<label class="ui-form-control-label" for="landing-domain-name-<?= $counter;?>"><?= Loc::getMessage('LANDING_TPL_DOMAIN_NAME_'.mb_strtoupper($domainCode));?></label>
						<input type="text" id="<?= $arParams['FIELD_ID'];?>_<?= $domainCode;?>" maxlength="64" value="<?= $selected ? $domainNameLocal : '';?>" class="ui-input ui-domainname" data-postfix="" />
						<div class="landing-site-name-status" id="landing-site-name-status-domain"></div>
					</div>
				</div>
				<div class="landing-alert landing-alert-info">
					<p class="landing-alert-paragraph">
						<?= Loc::getMessage('LANDING_TPL_DOMAIN_OWN_DOMAIN_ANY_INSTRUCT');?>
					</p>
					<table class="landing-alert-table">
						<tr class="landing-alert-table-header">
							<td>
								<span class="landing-alert-header-text"><?= Loc::getMessage('LANDING_TPL_DOMAIN_OWN_DOMAIN_DNS_1');?></span>
							</td>
							<td>
								<span class="landing-alert-header-text"><?= Loc::getMessage('LANDING_TPL_DOMAIN_OWN_DOMAIN_DNS_2');?></span>
							</td>
							<td>
								<span class="landing-alert-header-text"><?= Loc::getMessage('LANDING_TPL_DOMAIN_OWN_DOMAIN_DNS_3');?></span>
							</td>
						</tr>
						<tr class="landing-alert-table-content">
							<td id="landing-form-domain-name-text">
								<?= $arResult['DOMAIN_NAME_ORIGINAL'] ? $arResult['DOMAIN_NAME_ORIGINAL'] : 'landing.mydomain';?>
							</td>
							<td>CNAME</td>
							<td>lb<?= $arResult['POSTFIX'];?>.</td>
						</tr>
						<tr class="landing-alert-table-content">
							<td id="landing-form-domain-any-name-text">
								<?= $arResult['DOMAIN_NAME_ORIGINAL'] ? $arResult['DOMAIN_NAME_ORIGINAL'] : 'landing.mydomain.ru';?>
							</td>
							<td>A</td>
							<td><?= $arResult['IP_FOR_DNS'];?></td>
						</tr>
					</table>
				</div>
				<div class="landing-alert landing-alert-warning">
					<p class="landing-alert-paragraph">
						<i style="display: none;">
							<span id="landing-form-domain-any-name-textAAA" class="landing-form-domain-name-text"></span>.
							IN A
						</i>
					</p>
					<p class="landing-alert-paragraph">
						<strong><?= Loc::getMessage('LANDING_TPL_DOMAIN_ATTENTION');?></strong>
						<?= Loc::getMessage('LANDING_TPL_DOMAIN_OWN_DOMAIN_AAAA');?>
					</p>
					<?if ($helpUrl = \Bitrix\Landing\Help::getHelpUrl('DOMAIN_EDIT')):?>
						<p class="landing-alert-paragraph">
							<a class="landing-alert-more" href="<?= $helpUrl;?>" target="_blank"><?= Loc::getMessage('LANDING_TPL_DOMAIN_OWN_DOMAIN_HELP');?></a>
						</p>
					<?endif;?>
				</div>
				<?endif;?>
			<?endforeach;?>
		</div>
	<?else:?>
		<select name="fields[DOMAIN_ID]" class="ui-select">
			<?foreach ($arResult['DOMAINS'] as $item):?>
				<option value="<?= $item['ID']?>"<?if ($item['ID'] == $arParams['DOMAIN_ID']){?> selected="selected"<?}?>>
					<?= \htmlspecialcharsbx($item['DOMAIN']);?>
				</option>
			<?endforeach;?>
		</select>
	<?endif;?>
</div>

<?if (Manager::isB24()):?>
<script type="text/javascript">
	BX.ready(function(){
		new BX.Landing.DomainNamePopup({
			fieldId: '<?= \CUtil::jsEscape($arParams['FIELD_ID']);?>',
			messages: {
				title: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_DOMAIN_POPUP'));?>',
				errorEmpty:'<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_DOMAIN_ERROR_EMPTY'));?>'
			}
		});
	});
</script>
<?endif;?>