<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

IncludeModuleLangFile(__FILE__);
Bitrix\Main\Loader::includeModule('abtest');

$MOD_RIGHT = $APPLICATION->getGroupRight('abtest');
if ($MOD_RIGHT < 'W')
	$APPLICATION->authForm(getMessage('ACCESS_DENIED'));

$ID = intval($ID);

$abtest = Bitrix\ABTest\ABTestTable::getById($ID)->fetch();
if (empty($abtest))
	$ID = 0;

$arSites = array();
$dbSites = Bitrix\Main\SiteTable::getList(array('order' => array('DEF' => 'DESC', 'SORT' => 'ASC')));
while ($arSite = $dbSites->fetch())
	$arSites[$arSite['LID']] = $arSite;

$arTemplates = array();
$dbTemplates = CSiteTemplate::getList(array('ID' => 'ASC'), array('TYPE' => ''), array('ID', 'NAME'));
while ($arTemplate = $dbTemplates->fetch())
	$arTemplates[$arTemplate['ID']] = $arTemplate;

$arEstDays = array();
foreach (Bitrix\ABTest\AdminHelper::getSiteCapacity(array_keys($arSites)) as $lid => $value)
	$arEstDays[$lid] = $value['est'];


if ($REQUEST_METHOD == "POST" && ($save <> '' || $apply <> '') && check_bitrix_sessid())
{
	$arFields = array(
		'SITE_ID'  => $SITE,
		'NAME'     => $NAME,
		'DESCR'    => $DESCR,
		'DURATION' => intval($DURATION) < 0 ? -1 : intval($DURATION),
		'PORTION'  => intval($PORTION),
	);

	if ($ID > 0)
	{
		$arFields['TEST_DATA'] = $abtest['TEST_DATA'];
		$arFields['TEST_DATA']['list'] = array();
	}

	if (empty($arFields['SITE_ID']))
		$message = new CAdminMessage(array('MESSAGE' => getMessage('ABTEST_EMPTY_SITE')));
	else if (!is_set($arSites, $arFields['SITE_ID']))
		$message = new CAdminMessage(array('MESSAGE' => str_replace('#VALUE#', htmlspecialcharsbx($arFields['SITE_ID']), getMessage('ABTEST_UNKNOWN_SITE'))));

	if ($arFields['PORTION'] < 1 || $arFields['PORTION'] > 100)
		$message = new CAdminMessage(array('MESSAGE' => getMessage('ABTEST_PORTION_ERROR'), 'DETAILS' => getMessage('ABTEST_PORTION_HINT')));

	$errors = array();

	if (!empty($TEST_DATA['type']) && is_array($TEST_DATA['type']))
	{
		foreach ($TEST_DATA['type'] as $k => $type)
		{
			if (!in_array($type, array('template', 'page')))
				$errors[] = str_replace(array('#ID#', '#VALUE#'), array(intval($k)+1, htmlspecialcharsbx($type)), getMessage('ABTEST_UNKNOWN_TEST_TYPE'));

			if (empty($TEST_DATA['old_value'][$k]) || empty($TEST_DATA['new_value'][$k]))
			{
				$errors[] = str_replace('#ID#', intval($k)+1, getMessage(
					empty($TEST_DATA['old_value'][$k]) && empty($TEST_DATA['new_value'][$k])
						? 'ABTEST_EMPTY_TEST_VALUES' : 'ABTEST_EMPTY_TEST_VALUE'
				));
			}

			if (!empty($TEST_DATA['old_value'][$k]) || !empty($TEST_DATA['new_value'][$k]))
			{
				$docRoot = rtrim(Bitrix\Main\SiteTable::getDocumentRoot($arFields['SITE_ID']), '/');

				switch ($type)
				{
					case 'template':
						if (!empty($TEST_DATA['old_value'][$k]) && !is_set($arTemplates, $TEST_DATA['old_value'][$k]))
						{
							$errors[] = str_replace(
								array('#ID#', '#VALUE#'), array(intval($k)+1, htmlspecialcharsbx($TEST_DATA['old_value'][$k])),
								getMessage('ABTEST_UNKNOWN_TEST_TEMPLATE')
							);
						}
						if (!empty($TEST_DATA['new_value'][$k]) && !is_set($arTemplates, $TEST_DATA['new_value'][$k]))
						{
							$errors[] = str_replace(
								array('#ID#', '#VALUE#'), array(intval($k)+1, htmlspecialcharsbx($TEST_DATA['new_value'][$k])),
								getMessage('ABTEST_UNKNOWN_TEST_TEMPLATE')
							);
						}
						break;
					case 'page':
						if (!empty($TEST_DATA['old_value'][$k]))
						{
							$file = new Bitrix\Main\IO\File($docRoot.$TEST_DATA['old_value'][$k]);
							if (!$file->isExists())
							{
								$errors[] = str_replace(
									array('#ID#', '#VALUE#'), array(intval($k)+1, htmlspecialcharsbx($TEST_DATA['old_value'][$k])),
									getMessage('ABTEST_UNKNOWN_TEST_PAGE')
								);
							}
						}
						if (!empty($TEST_DATA['new_value'][$k]))
						{
							$file = new Bitrix\Main\IO\File($docRoot.$TEST_DATA['new_value'][$k]);
							if (!$file->isExists())
							{
								$errors[] = str_replace(
									array('#ID#', '#VALUE#'), array(intval($k)+1, htmlspecialcharsbx($TEST_DATA['new_value'][$k])),
									getMessage('ABTEST_UNKNOWN_TEST_PAGE')
								);
							}
						}
						break;
				}
			}

			$arFields['TEST_DATA']['list'][] = array(
				'type'      => $type,
				'old_value' => $TEST_DATA['old_value'][$k],
				'new_value' => $TEST_DATA['new_value'][$k],
			);
		}
	}
	else
	{
		$errors[] = getMessage('ABTEST_EMPTY_TEST_DATA');
	}

	if (!empty($errors))
		$message = new CAdminMessage(array('MESSAGE' => getMessage('ABTEST_TEST_DATA_ERROR'), 'DETAILS' => join('<br>', $errors)));

	if (empty($message))
	{
		$arFields['ENABLED'] = 'Y';

		if ($ID > 0)
		{
			$result = Bitrix\ABTest\ABTestTable::update($ID, $arFields);

			if ($result->isSuccess() && $abtest['ACTIVE'] == 'Y')
				Bitrix\ABTest\Helper::clearCache($arFields['SITE_ID']);
		}
		else
		{
			$arFields['ACTIVE'] = 'N';

			$result = Bitrix\ABTest\ABTestTable::add($arFields);
			$ID = $result->isSuccess() ? $result->getId() : 0;
		}

		if (!$result->isSuccess())
		{
			unset($arFields['ENABLED']);

			$message = new CAdminMessage(array(
				'MESSAGE' => getMessage('ABTEST_SAVE_ERROR'),
				'DETAILS' => join('<br>', $result->getErrorMessages())
			));
		}
		else
		{
			if ($save <> '')
				LocalRedirect('abtest_admin.php?lang='.LANG);
			else
				LocalRedirect($APPLICATION->getCurPage().'?lang='.LANG.'&ID='.$ID);
		}
	}

	if ($ID > 0)
		$abtest = array_merge($abtest, $arFields);
	else
		$abtest = $arFields;
}


if ($ID > 0)
{
	$APPLICATION->SetTitle(empty($abtest['NAME'])
		? str_replace('#ID#', $ID, getMessage('ABTEST_EDIT_TITLE1'))
		: str_replace('#NAME#', $abtest['NAME'], getMessage('ABTEST_EDIT_TITLE2'))
	);
}
else
{
	$APPLICATION->SetTitle(getMessage('ABTEST_ADD_TITLE'));
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$aMenu = array(
	array(
		"ICON" => "btn_list",
		"TEXT" => getMessage('ABTEST_GOTO_LIST'),
		"LINK" => "abtest_admin.php?lang=".LANG
	)
);

if ($ID > 0)
{
	$aMenu[] = array("SEPARATOR" => "Y");
	$aMenu[] = array(
		"ICON" => "btn_new",
		"TEXT" => getMessage('ABTEST_GOTO_ADD'),
		"LINK" => "abtest_edit.php?lang=".LANG
	);

	//if ($MOD_RIGHT == "W")
	{
		$aMenu[] = array(
			"ICON" => "btn_delete",
			"TEXT" => getMessage('ABTEST_DELETE'),
			"LINK" => "javascript:if(confirm('".CUtil::JSEscape(getMessage('ABTEST_DELETE_CONFIRM'))."')) window.location='abtest_admin.php?action=delete&ID=".$ID."&lang=".LANG."&".bitrix_sessid_get()."';",
		);
	}
}

$context = new CAdminContextMenu($aMenu);
$context->Show();


$aTabs = array(
	array('DIV' => 'edit1', 'TAB' => getMessage('ABTEST_TAB_NAME'), 'TITLE' => getMessage('ABTEST_TAB_TITLE')),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs, false);


if ($message) echo $message->Show();

?>

<form method="POST" action="<?=$APPLICATION->GetCurPage(); ?>?lang=<?=LANG; ?>&amp;ID=<?=$ID; ?>" name="form1" enctype="multipart/form-data">
<?=bitrix_sessid_post(); ?>

<? $tabControl->Begin(); ?>
<? $tabControl->BeginNextTab(); ?>

<?

if (empty($abtest))
{
	$lid      = current(array_keys($arSites));
	$duration = -1;
	$portion  = 30;
}
else
{
	$lid      = $abtest['SITE_ID'];
	$duration = $abtest['DURATION'];
	$portion  = $abtest['PORTION'];
}

?>

<tr class="adm-detail-required-field">
	<td style="width: 40%; "><?=getMessage('ABTEST_SITE_FIELD'); ?>:</td>
	<td style="width: 60%; ">
		<select id="site_id" name="SITE" onchange="ABTestParams.Site.handle(this); " style="width: 200px; ">
			<? if (!empty($abtest) && empty($abtest['SITE_ID'])) : ?>
			<option selected></option>
			<? endif; ?>
			<? $siteDefined = false; ?>
			<? foreach ($arSites as $value => $site) : ?>
			<option value="<?=htmlspecialcharsbx($value); ?>"<? if ($lid == $value && ($siteDefined = true)) echo ' selected'; ?>>
			<?=htmlspecialcharsbx($site['NAME']); ?> (<?=htmlspecialcharsbx($value); ?>)
			</option>
			<? endforeach; ?>
			<? if (!empty($abtest['SITE_ID']) && !$siteDefined) : ?>
			<option value="<?=htmlspecialcharsbx($abtest['SITE_ID']); ?>" selected>* <?=htmlspecialcharsbx($abtest['SITE_ID']); ?></option>
			<? endif; ?>
		</select>
	</td>
</tr>

<tr>
	<td><?=getMessage('ABTEST_NAME_FIELD'); ?>:</td>
	<td><input type="text" name="NAME" style="width: 340px; " maxlength="255" value="<? if (!empty($abtest)) echo htmlspecialcharsbx($abtest['NAME']); ?>"></td>
</tr>

<tr>
	<td class="adm-detail-valign-top"><?=getMessage('ABTEST_DESCR_FIELD'); ?>:</td>
	<td><textarea name="DESCR" cols="80" rows="4"><? if (!empty($abtest)) echo htmlspecialcharsbx($abtest['DESCR']); ?></textarea></td>
</tr>

<?

$durations = array(
	1  => getMessage('ABTEST_DURATION_OPTION_1'),
	3  => getMessage('ABTEST_DURATION_OPTION_3'),
	5  => getMessage('ABTEST_DURATION_OPTION_5'),
	7  => getMessage('ABTEST_DURATION_OPTION_7'),
	14 => getMessage('ABTEST_DURATION_OPTION_14'),
	30 => getMessage('ABTEST_DURATION_OPTION_30'),
	0  => getMessage('ABTEST_DURATION_OPTION_0'),
);

?>

<tr class="adm-detail-required-field">
	<td><?=getMessage('ABTEST_DURATION_FIELD'); ?><span class="required" style="font-weight: normal; "><sup>1</sup></span>:</td>
	<td>
		<select name="DURATION" style="width: 200px; ">
			<? $durationDefined = false; ?>
			<option id="duration_auto" value="-1"<? if ($duration == -1 && ($durationDefined = true)) echo ' selected'; ?>>
			<? $value = (empty($arEstDays[$lid]) || $portion < 1 || $portion > 100)
				? getMessage('ABTEST_DURATION_OPTION_NA') : ceil(100 * $arEstDays[$lid] / $portion); ?>
			<?=str_replace('#NUM#', $value, getMessage('ABTEST_DURATION_OPTION_A')); ?>
			</option>
			<? foreach ($durations as $value => $title) : ?>
			<option value="<?=intval($value); ?>"<? if ($duration == $value && ($durationDefined = true)) echo ' selected'; ?>><?=htmlspecialcharsbx($title); ?></option>
			<? endforeach; ?>
			<? if (!empty($abtest) && !$durationDefined) : ?>
			<option value="<?=intval($duration); ?>" selected>* <?=str_replace('#NUM#', intval($duration), getMessage('ABTEST_DURATION_OPTION_C')); ?></option>
			<? endif; ?>
		</select>
	</td>
</tr>

<tr class="adm-detail-required-field">
	<td><?=getMessage('ABTEST_PORTION_FIELD'); ?>:</td>
	<td>
		<select id="portion" name="PORTION" onchange="ABTestParams.Portion.handle(this); " style="width: 200px; ">
			<? $portionDefined = false; ?>
			<? foreach (array(10, 20, 30, 50, 100) as $value) : ?>
			<option value="<?=$value; ?>"<? if ($portion == $value && ($portionDefined = true)) echo ' selected'; ?>><?=$value; ?>%</option>
			<? endforeach; ?>
			<? if (!empty($abtest) && !$portionDefined) : ?>
			<option value="<?=intval($portion); ?>" selected>* <?=intval($portion); ?>%</option>
			<? endif; ?>
		</select>
	</td>
</tr>

<tr class="heading">
	<td align="center" colspan="2"><?=getMessage('ABTEST_TEST_DATA'); ?></td>
</tr>

<?

$test_form_msg = array(
	'template' => array(
		'title'   => getMessage('ABTEST_TEST_TEMPLATE_TITLE'),
		'title_a' => getMessage('ABTEST_TEST_TEMPLATE_TITLE_A'),
		'title_b' => getMessage('ABTEST_TEST_TEMPLATE_TITLE_B'),
	),
	'page' => array(
		'title'   => getMessage('ABTEST_TEST_PAGE_TITLE'),
		'title_a' => getMessage('ABTEST_TEST_PAGE_TITLE_A'),
		'title_b' => getMessage('ABTEST_TEST_PAGE_TITLE_B'),
	)
);

?>

<tr>
	<td colspan="2">
		<div id="abtest_list" class="adm-ab-edit-list">
		<? if (!empty($abtest['TEST_DATA']['list']) && is_array($abtest['TEST_DATA']['list'])) : ?>
		<? if ($ID > 0 && empty($message)) :
			$check_url = '';
			if (!empty($arSites[$abtest['SITE_ID']]['SERVER_NAME']))
				$check_url = 'http://' . $arSites[$abtest['SITE_ID']]['SERVER_NAME'];
		endif; ?>
		<? foreach ($abtest['TEST_DATA']['list'] as $k => $item) : ?>

			<? if ($ID > 0 && empty($message)) :
				switch ($item['type']) :
					case 'template':
						$check_uri = $check_url . $arSites[$abtest['SITE_ID']]['DIR'] . '?abtest_mode=' . $ID;
						break;
					case 'page':
						$check_uri = $check_url . $item['old_value'] . '?abtest_mode=' . $ID;
						break;
				endswitch;
			endif; ?>

			<table class="internal test-item" style="width: 100%; margin-bottom: 10px; ">
				<tr class="heading">
					<td colspan="2" style="text-align: left !important; ">
						<div style="float: right; cursor: pointer; " onclick="BX.remove(BX.findParent(this, {'tag': 'table', 'class': 'test-item'}));">x</div>
						<?=str_replace('#TYPE#', $test_form_msg[$item['type']]['title'], getMessage('ABTEST_TEST_TITLE')); ?>
					</td>
				</tr>
				<tr>
					<td>
						<input type="hidden" name="TEST_DATA[type][]" value="<?=htmlspecialcharsbx($item['type']); ?>">
						<table style="width: 400px; float: right; ">
							<tr>
								<td style="width: 20px; background: #498ec5; text-shadow: none; color: #ffffff !important; font-weight: bold; text-align: center; padding: 10px !important;">A</td>
								<td style="background: rgb(203, 213, 220); font-weight: bold; "><?=$test_form_msg[$item['type']]['title_a']; ?></td>
							</tr>
							<tr>
								<td colspan="2" style="background: rgb(236, 239, 241); padding-left: 41px !important; ">
									<? switch ($item['type']) :
										case 'template': ?>
											<select class="value-input old-value-input" name="TEST_DATA[old_value][]" onchange="ABTestList.Item.handle(this); " data-value="<?=htmlspecialcharsbx($item['old_value']); ?>" style="width: 320px; ">
												<option></option>
												<? $oldvalueDefined = false; ?>
												<? foreach ($arTemplates as $tmpl_id => $tmpl) : ?>
												<option value="<?=htmlspecialcharsbx($tmpl_id); ?>"<? if ($item['old_value'] == $tmpl_id && ($oldvalueDefined = true)) echo ' selected'; ?>><?=htmlspecialcharsbx($tmpl['NAME']); ?> (<?=htmlspecialcharsbx($tmpl_id); ?>)</option>
												<? endforeach; ?>
												<? if ($item['old_value'] && !$oldvalueDefined) : ?>
												<option value="<?=htmlspecialcharsbx($item['old_value']); ?>" selected>* <?=htmlspecialcharsbx($item['old_value']); ?></option>
												<? endif; ?>
											</select>
										<? break;
										case 'page': ?>
											<input class="value-input old-value-input" type="text" onchange="ABTestList.Item.handle(this); " oninput="ABTestList.Item.handle(this); " name="TEST_DATA[old_value][]" data-value="<?=htmlspecialcharsbx($item['old_value']); ?>" value="<?=htmlspecialcharsbx($item['old_value']); ?>" style="width: 230px; ">
											<input type="button" value="..." onclick="ABTestList.Item.select(this); " title="<?=getMessage('ABTEST_TEST_SELECT_PAGE'); ?>">
											<input class="copy-value-btn" type="button" onclick="ABTestList.Item.copy(this); " value="&gt;" title="<?=getMessage('ABTEST_TEST_COPY_PAGE'); ?>"<? if (empty($item['old_value'])) echo ' disabled'; ?>>
										<? break;
									endswitch; ?>
									<? if ($ID > 0 && $abtest['ENABLED'] == 'Y' && empty($message)) : ?>
									<br><br><input class="preview-btn" type="button" value="<?=getMessage('ABTEST_TEST_CHECK'); ?>" data-href="<?=htmlspecialcharsbx($check_uri); ?>|A" onclick="window.open(this.getAttribute('data-href')); ">
									<? endif; ?>
								</td>
							</tr>
						</table>
					</td>
					<td>
						<table style="width: 400px; ">
							<tr>
								<td style="width: 20px; background: rgb(255, 118, 36); text-shadow: none; color: #ffffff !important; font-weight: bold; text-align: center; padding: 10px !important;">B</td>
								<td style="background: rgb(203, 213, 220); font-weight: bold; "><?=$test_form_msg[$item['type']]['title_b']; ?></td>
							</tr>
							<tr>
								<td colspan="2" style="background: rgb(236, 239, 241); padding-left: 41px !important; ">
									<? switch ($item['type']) :
										case 'template': ?>
											<select class="value-input new-value-input" name="TEST_DATA[new_value][]" onchange="ABTestList.Item.handle(this); " data-value="<?=htmlspecialcharsbx($item['new_value']); ?>" style="width: 320px; ">
												<? $newvalueDefined = false; ?>
												<option></option>
												<? foreach ($arTemplates as $tmpl_id => $tmpl) : ?>
												<option value="<?=htmlspecialcharsbx($tmpl_id); ?>"<? if ($item['new_value'] == $tmpl_id && ($newvalueDefined = true)) echo ' selected'; ?>><?=htmlspecialcharsbx($tmpl['NAME']); ?> (<?=htmlspecialcharsbx($tmpl_id); ?>)</option>
												<? endforeach; ?>
												<? if ($item['new_value'] && !$newvalueDefined) : ?>
												<option value="<?=htmlspecialcharsbx($item['new_value']); ?>" selected>* <?=htmlspecialcharsbx($item['new_value']); ?></option>
												<? endif; ?>
											</select>
										<? break;
										case 'page': ?>
											<input class="value-input new-value-input" type="text" onchange="ABTestList.Item.handle(this); " oninput="ABTestList.Item.handle(this); " name="TEST_DATA[new_value][]" data-value="<?=htmlspecialcharsbx($item['new_value']); ?>" value="<?=htmlspecialcharsbx($item['new_value']); ?>" style="width: 230px; ">
											<input type="button" onclick="ABTestList.Item.select(this); " value="..." title="<?=getMessage('ABTEST_TEST_SELECT_PAGE'); ?>">
											<span class="edit-value-btn adm-btn<? if (empty($item['new_value'])) echo ' adm-btn-disabled'; ?>" onclick="ABTestList.Item.edit(this); " title="<?=getMessage('ABTEST_TEST_EDIT_PAGE'); ?>">&nbsp;</span>
										<? break;
									endswitch; ?>
									<? if ($ID > 0 && $abtest['ENABLED'] == 'Y' && empty($message)) : ?>
									<br><br><input class="preview-btn" type="button" value="<?=getMessage('ABTEST_TEST_CHECK'); ?>" data-href="<?=htmlspecialcharsbx($check_uri); ?>|B" onclick="window.open(this.getAttribute('data-href')); ">
									<? endif; ?>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>

		<? endforeach; ?>
		<? endif; ?>
		</div>
	</td>
</tr>

<? if (!$ID || in_array($abtest['ENABLED'], array('T', 'Y'))) : ?>
<tr>
	<td colspan="2">
		<a id="new_test_button" href="#" hidefocus="true" class="adm-btn adm-btn-add adm-btn-save adm-btn-menu" title="<?=getMessage('ABTEST_TEST_ADD'); ?>"><?=getMessage('ABTEST_TEST_ADD'); ?></a>
	</td>
</tr>
<? endif; ?>

<? $tabControl->EndTab(); ?>
<? $tabControl->Buttons(array('disabled' => $ID > 0 && !in_array($abtest['ENABLED'], array('T', 'Y')), 'back_url' => 'abtest_admin.php?lang='.LANG)); ?>
<? if ($ID > 0 && $abtest['ACTIVE'] == 'Y') : ?>
<span style="margin-left: 25px; color: #e70000; text-decoration: underline; "><?=getMessage('ABTEST_TEST_EDIT_WARNING'); ?></span>
<? endif; ?>
<? $tabControl->End(); ?>

</form>

<table id="abtest_sample_template" class="internal test-item" style="width: 100%; display: none; margin-bottom: 10px; ">
	<tr class="heading">
		<td colspan="2" style="text-align: left !important; ">
			<div style="float: right; cursor: pointer; " onclick="BX.remove(BX.findParent(this, {'tag': 'table', 'class': 'test-item'}));">x</div>
			<?=str_replace('#TYPE#', $test_form_msg['template']['title'], getMessage('ABTEST_TEST_TITLE')); ?>
		</td>
	</tr>
	<tr>
		<td>
			<input type="hidden" name="TEST_DATA[type][]" value="template">
			<table style="width: 400px; float: right; ">
				<tr>
					<td style="width: 20px; background: #498ec5; text-shadow: none; color: #ffffff !important; font-weight: bold; text-align: center; padding: 10px !important;">A</td>
					<td style="background: rgb(203, 213, 220); font-weight: bold; "><?=$test_form_msg['template']['title_a']; ?></td>
				</tr>
				<tr>
					<td colspan="2" style="background: rgb(236, 239, 241); padding-left: 41px !important; ">
						<select name="TEST_DATA[old_value][]" style="width: 320px; ">
							<option></option>
							<? foreach ($arTemplates as $tmpl_id => $tmpl) : ?>
							<option value="<?=htmlspecialcharsbx($tmpl_id); ?>"><?=htmlspecialcharsbx($tmpl['NAME']); ?> (<?=htmlspecialcharsbx($tmpl_id); ?>)</option>
							<? endforeach; ?>
						</select>
					</td>
				</tr>
			</table>
		</td>
		<td>
			<table style="width: 400px; ">
				<tr>
					<td style="width: 20px; background: rgb(255, 118, 36); text-shadow: none; color: #ffffff !important; font-weight: bold; text-align: center; padding: 10px !important;">B</td>
					<td style="background: rgb(203, 213, 220); font-weight: bold; "><?=$test_form_msg['template']['title_b']; ?></td>
				</tr>
				<tr>
					<td colspan="2" style="background: rgb(236, 239, 241); padding-left: 41px !important; ">
						<select name="TEST_DATA[new_value][]" style="width: 320px; ">
							<option></option>
							<? foreach ($arTemplates as $tmpl_id => $tmpl) : ?>
							<option value="<?=htmlspecialcharsbx($tmpl_id); ?>"><?=htmlspecialcharsbx($tmpl['NAME']); ?> (<?=htmlspecialcharsbx($tmpl_id); ?>)</option>
							<? endforeach; ?>
						</select>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<table id="abtest_sample_page" class="internal test-item" style="width: 100%; display: none; margin-bottom: 10px; ">
	<tr class="heading">
		<td colspan="2" style="text-align: left !important; ">
			<div style="float: right; cursor: pointer; " onclick="BX.remove(BX.findParent(this, {'tag': 'table', 'class': 'test-item'}));">x</div>
			<?=str_replace('#TYPE#', $test_form_msg['page']['title'], getMessage('ABTEST_TEST_TITLE')); ?>
		</td>
	</tr>
	<tr>
		<td>
			<input type="hidden" name="TEST_DATA[type][]" value="page">
			<table style="width: 400px; float: right; ">
				<tr>
					<td style="width: 20px; background: #498ec5; text-shadow: none; color: #ffffff !important; font-weight: bold; text-align: center; padding: 10px !important;">A</td>
					<td style="background: rgb(203, 213, 220); font-weight: bold; "><?=$test_form_msg['page']['title_a']; ?></td>
				</tr>
				<tr>
					<td colspan="2" style="background: rgb(236, 239, 241); padding-left: 41px !important; ">
						<input class="value-input old-value-input" type="text" onchange="ABTestList.Item.handle(this); " oninput="ABTestList.Item.handle(this); " name="TEST_DATA[old_value][]" style="width: 230px; ">
						<input type="button" onclick="ABTestList.Item.select(this); " value="..." title="<?=getMessage('ABTEST_TEST_SELECT_PAGE'); ?>">
						<input class="copy-value-btn" type="button" onclick="ABTestList.Item.copy(this); " value="&gt;" title="<?=getMessage('ABTEST_TEST_COPY_PAGE'); ?>" disabled>
					</td>
				</tr>
			</table>
		</td>
		<td>
			<table style="width: 400px; ">
				<tr>
					<td style="width: 20px; background: rgb(255, 118, 36); text-shadow: none; color: #ffffff !important; font-weight: bold; text-align: center; padding: 10px !important;">B</td>
					<td style="background: rgb(203, 213, 220); font-weight: bold; "><?=$test_form_msg['page']['title_b']; ?></td>
				</tr>
				<tr>
					<td colspan="2" style="background: rgb(236, 239, 241); padding-left: 41px !important; ">
						<input class="value-input new-value-input" type="text" onchange="ABTestList.Item.handle(this); " oninput="ABTestList.Item.handle(this); " name="TEST_DATA[new_value][]" style="width: 230px; ">
						<input type="button" onclick="ABTestList.Item.select(this); " value="..." title="<?=getMessage('ABTEST_TEST_SELECT_PAGE'); ?>">
						<span class="edit-value-btn adm-btn adm-btn-disabled" onclick="ABTestList.Item.edit(this); " title="<?=getMessage('ABTEST_TEST_EDIT_PAGE'); ?>">&nbsp;</span>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>

<div class="adm-info-message-wrap">
	<div class="adm-info-message">
		<span class="required"><sup>1</sup></span>
		<?=getMessage('ABTEST_DURATION_AUTO_HINT'); ?><br></br>
		<?=getMessage('ABTEST_MATH_POWER_HINT'); ?>
	</div>
</div>

<? CAdminFileDialog::ShowScript(array(
	'event'         => 'openFileDialog',
	'arResultDest'  => array('FUNCTION_NAME' => 'fileDialogCallback'),
	'arPath'        => array('SITE' => $siteDefined ? $abtest['SITE_ID'] : '', 'PATH' => '/'),
	'select'        => 'F',
	'operation'     => 'O',
	'fileFilter'    => 'php',
	'allowAllFiles' => true,
	'saveConfig'    => true
)); ?>

<script>

	var initialSite = '<?=CUtil::jsEscape($abtest['SITE_ID']); ?>';
	var siteDirs = <?=CUtil::phpToJSObject(array_map(function($site) {
		return $site['DIR'];
	}, $arSites)); ?>;

	var estDays = <?=CUtil::phpToJSObject($arEstDays); ?>;

	var fileDialogTarget = null;

	var fileDialogCallback = function(filename, path)
	{
		fileDialogTarget.value = (path+'/'+filename).replace(/\/+/, '/');
		fileDialogTarget.style.color = '';
		ABTestList.Item.handle(fileDialogTarget, true);

		fileDialogTarget = null;
	}

	var ABTestParams = {
		Site: {
			handle: function()
			{
				var inputs = BX.findChildrenByClassName(BX('abtest_list'), 'value-input', true);

				for (var i in inputs)
					ABTestList.Item.check(inputs[i], true);

				ABTestParams.Duration.updateAuto();
			}
		},
		Duration: {
			updateAuto: function()
			{
				BX.html(BX('duration_auto'), (function() {
					var portion = parseInt(BX('portion').value);
					var est = parseFloat(estDays[BX('site_id').value]);
					var days = !est || portion < 1 || portion > 100
						? '<?=CUtil::jsEscape(getMessage('ABTEST_DURATION_OPTION_NA')); ?>'
						: Math.ceil(100 * est / portion);
					return '<?=CUtil::jsEscape(getMessage('ABTEST_DURATION_OPTION_A')); ?>'.replace('#NUM#', days);
				})());
			}
		},
		Portion: {
			handle: function()
			{
				ABTestParams.Duration.updateAuto();
			}
		}
	};

	var ABTestList = {
		add: function(type)
		{
			var sample = BX('abtest_sample_'+type);
			var new_test = sample.cloneNode(true);

			new_test.removeAttribute('id');
			new_test.style.display = '';

			BX('abtest_list').appendChild(new_test);
		},
		Item: {
			handle: function(input, skipCheck)
			{
				if (!skipCheck)
					ABTestList.Item.check(input);

				ABTestList.Item.toggleCopy(input);
				ABTestList.Item.toggleEdit(input);
				ABTestList.Item.togglePreview(input);
			},
			toggleCopy: function(input)
			{
				if (BX.hasClass(input, 'old-value-input'))
				{
					var btn = BX.findChild(BX.findParent(input, {'class': 'test-item'}), {'class': 'copy-value-btn'}, true);

					if (btn)
						btn.disabled = !input.value;
				}
			},
			toggleEdit: function(input)
			{
				if (BX.hasClass(input, 'new-value-input'))
				{
					var btn = BX.findChild(BX.findParent(input, {'class': 'test-item'}), {'class': 'edit-value-btn'}, true);

					if (btn)
					{
						if (input.value)
							BX.removeClass(btn, 'adm-btn-disabled');
						else
							BX.addClass(btn, 'adm-btn-disabled');
					}
				}
			},
			togglePreview: function(input)
			{
				if (BX.hasClass(input, 'value-input'))
				{
					var item = BX.findParent(input, {'class': 'test-item'});

					var old_value = BX.findChild(item, {'class': 'old-value-input'}, true);
					var new_value = BX.findChild(item, {'class': 'new-value-input'}, true);

					var old_btn = BX.findChild(old_value.parentNode, {'class': 'preview-btn'}, true);
					var new_btn = BX.findChild(new_value.parentNode, {'class': 'preview-btn'}, true);

					var old_btn_disabled = old_value.value != old_value.getAttribute('data-value') || BX('site_id').value != initialSite;
					var new_btn_disabled = new_value.value != new_value.getAttribute('data-value') || old_btn_disabled;

					if (old_btn)
						old_btn.disabled = old_btn_disabled;
					if (new_btn)
						new_btn.disabled = new_btn_disabled;
				}
			},
			check: function(input, force)
			{
				if (input.nodeName.toLowerCase() != 'input')
				{
					ABTestList.Item.handle(input, true);
					return;
				}

				if (force || input.value != input.chkValue)
				{
					input.chkTimeout = clearTimeout(input.chkTimeout);
					if (typeof input.chkAjax == 'object')
					{
						input.chkAjax.abort();
						input.chkAjax = false;
					}

					input.style.color = '';
					if (input.value)
					{
						input.chkValue = input.value;
						input.chkTimeout = setTimeout(function() {
							input.style.color = '#808080';
							input.chkAjax = BX.ajax({
								method: 'POST',
								url: '/bitrix/admin/abtest_ajax.php?action=check&type=page',
								data: {
									site: BX('site_id').value,
									value: input.value
								},
								dataType: 'json',
								onsuccess: function(json)
								{
									if (json.result != 'error')
									{
										if (input.value != json.result)
										{
											input.value = json.result;
											input.chkValue = input.value;
										}

										input.style.color = '';
										ABTestList.Item.handle(input, true);
									}
									else
									{
										//alert(json.error);
										input.style.color = '#f00000';
									}
								}
							});
						}, force ? 0 : 500);
					}
				}
			},
			select: function(btn)
			{
				var path = null;

				fileDialogTarget = BX.findChild(btn.parentNode, {'class': 'value-input'}, false);

				// @TODO: define path
				path = '/';
				var params = {
					site: BX('site_id').value,
					path: typeof siteDirs[BX('site_id').value] != 'undefined' ? siteDirs[BX('site_id').value] : '/'
				};

				openFileDialog(true, params);
			},
			copy: function(btn)
			{
				var item = BX.findParent(btn, {'class': 'test-item'});

				var old_value = BX.findChild(item, {'class': 'old-value-input'}, true);
				var new_value = BX.findChild(item, {'class': 'new-value-input'}, true);

				if (old_value.style.color != '')
				{
					alert('<?=CUtil::JSEscape(getMessage('ABTEST_UNKNOWN_PAGE')); ?>');
					return;
				}

				btn.disabled = true;
				BX.ajax({
					method: 'POST',
					url: '/bitrix/admin/abtest_ajax.php?action=copy&type=page',
					data: '<?=bitrix_sessid_get(); ?>&site='+encodeURIComponent(BX('site_id').value)+'&source='+encodeURIComponent(old_value.value),
					dataType: 'json',
					onsuccess: function(json)
					{
						if (json.result != 'error')
						{
							new_value.value = json.result;
							ABTestList.Item.handle(new_value, true);
						}
						else
						{
							alert(json.error);
						}

						ABTestList.Item.toggleCopy(old_value);
					},
					onfailure: function()
					{
						alert('<?=CUtil::jsEscape(getMessage('ABTEST_AJAX_ERROR')); ?>');

						ABTestList.Item.toggleCopy(old_value);
					}
				});
			},
			edit: function(btn)
			{
				if (BX.hasClass(btn, 'adm-btn-disabled'))
					return false;

				var value = BX.findChild(btn.parentNode, {'class': 'value-input'}, false);

				if (value.style.color != '')
				{
					alert('<?=CUtil::JSEscape(getMessage('ABTEST_UNKNOWN_PAGE')); ?>');
					return;
				}

				window.open('/bitrix/admin/fileman_html_edit.php?path='+encodeURIComponent(value.value)+'&lang=<?=LANG; ?>');
			}
		}
	};

	BX('new_test_button').onclick = function()
	{
		this.blur();

		BX.adminShowMenu(this, [
			{'TEXT': '<?=CUtil::JSEscape($test_form_msg['template']['title']); ?>', 'ONCLICK': 'ABTestList.add(\'template\');'},
			{'TEXT': '<?=CUtil::JSEscape($test_form_msg['page']['title']); ?>', 'ONCLICK': 'ABTestList.add(\'page\');'}
		], {active_class: 'adm-btn-save-active'});

		return false;
	}

</script>


<?

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");
