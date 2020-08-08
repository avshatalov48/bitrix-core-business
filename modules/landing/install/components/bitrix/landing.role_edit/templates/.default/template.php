<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Landing\Manager;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
Manager::setPageTitle(Loc::getMessage('LANDING_TPL_TITLE_EDIT'));
$this->addExternalCss('/bitrix/css/main/table/style.css');

if ($arResult['EXTENDED'])
{
	?>
	<form action="<?=POST_FORM_ACTION_URI;?>" method="post">
		<?= bitrix_sessid_post();?>
		<input type="hidden" name="action" value="mode"/>
		<p><?=Loc::getMessage('LANDING_TPL_EXTENDED_MODE');?></p>
		<button type="submit" class="ui-btn ui-btn-success" value="<?=Loc::getMessage(
			'LANDING_TPL_BUTTON_MODE_TO_ROLE'
		);?>">
			<?=Loc::getMessage('LANDING_TPL_BUTTON_MODE_TO_ROLE');?>
		</button>
	</form>
	<?
	return;
}

$context = \Bitrix\Main\Application::getInstance()->getContext();
$request = $context->getRequest();
$row = $arResult['ROLE'];

// show errors
if ($arResult['ERRORS'])
{
	?><div class="landing-message-label error"><?
	foreach ($arResult['ERRORS'] as $error)
	{
		echo $error . '<br/>';
	}
	?></div><?
}
if ($arResult['FATAL'])
{
	return;
}

// function for draw one tr (one site)
$drawTr = function($siteId, array $selectedId = [], $title = '') use($arResult)
{
	static $count = 0;

	$html = '';
	if ($count == 0)
	{
		$count = count($arResult['TASKS']);
	}

	foreach (array_values($arResult['TASKS']) as $i => $right)
	{
		$code = $right['NAME'];
		if ($code == $arResult['TASK_DENIED_CODE'])
		{
			continue;
		}
		$notSelected = !in_array($code, $selectedId) ? ' selected="selected"' : '';
		$right['TITLE'] = \htmlspecialcharsbx($right['TITLE']);
		$html .= '
			<tr class="tr-first landing-rightsblock-' . $siteId . (!$html ? ' landing-rightsblock-content' : '') . '">
				<td class="table-blue-td-name">
					' . (!$html ? '<a name="site' . $siteId . '"></a>' . $title : '') . '
				</td>
				<td class="table-blue-td-param">
					<label for="landing-operation-' . $siteId . '-' . $code . '">
						' .$right['TITLE'] . '
					</label>
				</td>
				<td class="table-blue-td-select table-blue-td-select-landing">
					<select class="table-blue-select" name="fields[RIGHTS][' . $siteId . '][]"' .
						' id="landing-operation-' . $siteId . '-' . $code . '">
						<option value="' . $code . '">' . Loc::getMessage('LANDING_TPL_RIGHT_ALLOW') . '</option>					
						<option value="" ' . $notSelected . '>' . Loc::getMessage('LANDING_TPL_RIGHT_DISALLOW') . '</option>
					</select>
				</td>
				<td class="table-blue-td-select-remove">
					' . (
						($i == $count-1 && $siteId > 0)
						? '<a href="javascript:void(0);" class="landing-rightsblock-remove bitrix24-metrika" data-metrika24="role_site_delete" data-id="' . $siteId . '">
								' . Loc::getMessage('LANDING_TPL_BUTTON_DEL_RIGHT') . '
							</a>'
						: ''
					) . '
				</td>
			</tr>';
	}

	return $html;
};

// add new site in selected
if ($request->get('site'))
{
	$newSite = $request->get('site');
	if (!isset($arResult['RIGHTS'][$newSite]))
	{
		$arResult['RIGHTS'][$newSite] = [];
	}
}

// default rights ???
if (!isset($arResult['RIGHTS'][0]))
{
	$arResult['RIGHTS'][0] = [];
}

// clear sites array
foreach ($arResult['SITES'] as &$site)
{
	$site = [
		'ID' => $site['ID'],
		'TITLE' => \htmlspecialcharsbx($site['TITLE']),
		'DELETED' => $site['DELETED']
	];
}
unset($site);
?>

<form action="<?= POST_FORM_ACTION_URI;?>" method="post" class="ui-form ui-form-gray-padding" id="landing-role-edit">
	<input type="hidden" name="fields[SAVE_FORM]" value="Y" />
	<input type="hidden" name="data[id]" value="<?= $arParams['ROLE_ID'];?>" />
	<?= bitrix_sessid_post();?>

	<div class="landing-form-role-title">
		<label class="landing-form-role-caption"><?= Loc::getMessage('LANDING_TPL_CAPTION');?>:</label>
		<input class="landing-form-role-input" type="text" name="fields[TITLE]" value="<?= $row['TITLE']['CURRENT'];?>" placeholder="<?= $row['TITLE']['TITLE'];?>" />
	</div>

	<table class="table-blue table-blue-landing-role" id="landing-role-rights-table">
		<tbody>
		<tr>
			<th class="table-blue-td-title">
				<?= Loc::getMessage('LANDING_TPL_RIGHT_ENTITY');?>
			</th>
			<th class="table-blue-td-title">
				<?= Loc::getMessage('LANDING_TPL_RIGHT_TITLE');?>
			</th>
			<th class="table-blue-td-title">
				<?= Loc::getMessage('LANDING_TPL_RIGHT_SELECT');?>
			</th>
			<th class="table-blue-td-title"></th>
		</tr>
		<?foreach ($arResult['ADDITIONAL'] as $code => $title):
			$notChecked = ! (
								!is_array($row['ADDITIONAL_RIGHTS']['CURRENT']) ||
					   			in_array($code, $row['ADDITIONAL_RIGHTS']['CURRENT'])
							);
			?>
			<tr class="tr-first">
				<td class="table-blue-td-name">
					<?= Loc::getMessage('LANDING_TPL_ADDITIONAL_ENTITY_'.mb_strtoupper($code));?>
				</td>
				<td class="table-blue-td-param">
					<label for="landing-operation-additional-<?= $code;?>">
						<?= Loc::getMessage('LANDING_TPL_ADDITIONAL_ACTION_'.mb_strtoupper($code));?>
					</label>
				</td>
				<td class="table-blue-td-select">
					<select class="table-blue-select" name="fields[ADDITIONAL][]" id="landing-operation-additional-<?= $code;?>">
						<option value="<?= $code;?>"><?= Loc::getMessage('LANDING_TPL_RIGHT_ALLOW');?></option>
						<option value=""<?= $notChecked ? ' selected="selected"' : '';?>><?= Loc::getMessage('LANDING_TPL_RIGHT_DISALLOW');?></option>
					</select>
				</td>
			</tr>
		<?endforeach;?>
		<?
		echo $drawTr(
			0,
			$arResult['RIGHTS'][0],
			$component->getMessageType('LANDING_TPL_RIGHT_DEFAULT_TITLE')
		);
		foreach ($arResult['RIGHTS'] as $siteId => $rights)
		{
			if (!isset($arResult['SITES'][$siteId]))
			{
				continue;
			}
			$site = $arResult['SITES'][$siteId];
			unset($arResult['SITES'][$siteId]);

			echo $drawTr($siteId, $rights, $site['TITLE']);
		}
		?>
		</tbody>
	</table>

	<?if ($arResult['SITES']):?>
	<div style="padding: 20px 0 20px 0;">
		<span class="landing-role-add bitrix24-metrika" <?
			?>data-metrika24="role_site_add" <?
			?>id="landing-role-add" <?
			?>onclick="showSiteMenu(
				this,
				<?= \CUtil::phpToJSObject($arResult['SITES']);?>,
				{
					LANDING_ALERT_CONTENT_RELOADED: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_ALERT_CONTENT_RELOADED'));?>'
				}
			)">
			<?= $component->getMessageType('LANDING_TPL_ADD_FOR_SITE');?>
		</span>
	</div>
	<?else:?>
		<div style="padding-top: 20px;"></div>
	<?endif;?>

	<div class="pinable-block">
		<div class="landing-form-footer-container">
			<button id="landing-rights-save" type="submit" class="ui-btn ui-btn-success bitrix24-metrika" data-metrika24="role_save" name="submit" value="<?= Loc::getMessage('LANDING_TPL_BUTTON_SAVE');?>">
				<?= Loc::getMessage('LANDING_TPL_BUTTON_SAVE');?>
			</button>
			<a class="ui-btn ui-btn-md ui-btn-link" href="<?= $arParams['PAGE_URL_ROLES'];?>">
				<?= Loc::getMessage('LANDING_TPL_BUTTON_CANCEL');?>
			</a>
		</div>
	</div>
</form>

