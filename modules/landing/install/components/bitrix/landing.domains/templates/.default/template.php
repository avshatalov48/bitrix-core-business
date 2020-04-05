<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

$APPLICATION->setTitle(Loc::getMessage('LANDING_TPL_TITLE'));

if ($arResult['ERRORS'])
{
	\showError(implode("\n", $arResult['ERRORS']));
}

if ($arResult['FATAL'])
{
	return;
}
?>

<table class="bx-interface-grid" style="min-width: 100%;">
	<tr class="bx-grid-head">
		<td><?= Loc::getMessage('LANDING_TPL_COL_ACTIVE')?></td>
		<td><?= Loc::getMessage('LANDING_TPL_COL_DOMAIN')?></td>
		<td><?= Loc::getMessage('LANDING_TPL_COL_CREATED')?></td>
		<td><?= Loc::getMessage('LANDING_TPL_COL_MODIFIED')?></td>
		<td></td>
	</tr>
	<?foreach ($arResult['DOMAINS'] as $item):
		$urlEdit = str_replace('#domain_edit#', $item['ID'], $arParams['PAGE_URL_DOMAIN_EDIT']);
		$uriDelete = new \Bitrix\Main\Web\Uri($urlEdit);
		$uriDelete->addParams(array(
			'fields' => array(
				'delete' => 'Y'
			),
			'sessid' => bitrix_sessid()
		));
		?>
	<tr valign="top">
		<td>
			<?= Loc::getMessage('LANDING_TPL_COL_ACT_' . $item['ACTIVE'])?>
		</td>
		<td>
			<a href="//<?= \htmlspecialcharsbx($item['DOMAIN'])?>" target="_bllank"><?= \htmlspecialcharsbx($item['DOMAIN'])?></a><br/>
		</td>
		<td>
			<small>
				<?= \htmlspecialcharsbx($item['CREATED_BY_NAME'] . ' ' . $item['CREATED_BY_LAST_NAME'])?><br/>
				<?= \htmlspecialcharsbx($item['DATE_CREATE'])?>
			</small>
		</td>
		<td>
			<small>
				<?= \htmlspecialcharsbx($item['MODIFIED_BY_NAME'] . ' ' . $item['MODIFIED_BY_LAST_NAME'])?><br/>
				<?= \htmlspecialcharsbx($item['DATE_MODIFY'])?>
			</small>
		</td>
		<td>
			<a href="<?= $urlEdit?>"><?= Loc::getMessage('LANDING_TPL_ACTION_EDIT')?></a><br/>
			<a href="<?= $uriDelete->getUri()?>"><?= Loc::getMessage('LANDING_TPL_ACTION_DELETE')?></a>
		</td>
	</tr>
	<?endforeach;?>
</table>

<br/>
<a href="<?= str_replace('#domain_edit#', 0, $arParams['PAGE_URL_DOMAIN_EDIT']);?>"><?= Loc::getMessage('LANDING_TPL_ACTION_ADD')?></a>