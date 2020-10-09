<?if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\UI;

UI\Extension::load("ui.tooltip");

?><div id="wiki-post">
<?if($arResult['MESSAGE'] <> ''):
	?>
	<div class="wiki-notes">
		<div class="wiki-note-text">
			<?=$arResult['MESSAGE']?>
		</div>
	</div>
	<?
endif;?>
<?if(!empty($arResult['FATAL_MESSAGE'])):
	?>
	<div class="wiki-errors wiki-note-box wiki-note-error">
		<div class="wiki-error-text">
			<?=$arResult['FATAL_MESSAGE']?>
		</div>
	</div>
	<?
else:
	?>
	<div id="wiki-post-content">
	<?
	if (empty($arResult['HISTORY'])):
		ShowNote(GetMessage('WIKI_HISTORY_NOT_FIND'));
	else:
		if ($arResult['SOCNET']) :
			$APPLICATION->IncludeComponent('bitrix:main.user.link',
				'',
				array(
					"ID" => $arResult["USER_ID"],
					'AJAX_ONLY' => 'Y',
					'PATH_TO_SONET_USER_PROFILE' => str_replace('#user_id#', '#ID#', $arResult['PATH_TO_USER']),
					'PATH_TO_SONET_MESSAGES_CHAT' => $arResult['PATH_TO_SONET_MESSAGES_CHAT'],
					'NAME_TEMPLATE' => $arResult['NAME_TEMPLATE'],
					'SHOW_LOGIN' => $arResult['SHOW_LOGIN'],
					'PATH_TO_CONPANY_DEPARTMENT' => $arResult['PATH_TO_CONPANY_DEPARTMENT'],
					'PATH_TO_VIDEO_CALL' => $arResult['PATH_TO_VIDEO_CALL']
				),
				$component,
				array('HIDE_ICONS' => 'Y')
			);
		endif;

		$arHeaders = array(
			array('id' => 'LOGIN', 'name' => GetMessage('WIKI_COLUMN_AUTHOR'), 'sort' => false, 'default' => true),
			array('id' => 'DATE', 'name' => GetMessage('WIKI_COLUMN_DATE_CREATE'), 'sort' => false, 'default' => true),
			array('id' => 'MODIFY_COMMENT', 'name' => GetMessage('WIKI_MODIFY_COMMENT'), 'sort' => false, 'default' => true),
		);

		foreach($arResult['HISTORY'] as $sKey =>  $arHistory)
		{
			$arResult['HISTORY'][$sKey]['ANCHOR_ID'] = RandString(8);

			$_arData = array(
				'LOGIN' => !empty($arHistory['USER_LINK']) ? '<a href="'.$arHistory['USER_LINK'].'" id="anchor_'.$arResult['HISTORY'][$sKey]['ANCHOR_ID'].'" bx-tooltip-user-id="'.$arHistory['USER_ID'].'">'.$arHistory['USER_LOGIN'].'</a>' : $arHistory['USER_LOGIN'],
				'DATE' => $arHistory['MODIFIED'],
				'MODIFY_COMMENT' => $arHistory['MODIFY_COMMENT']
			);

			$arActions = array();
			$arActions[] =  array(
				'TITLE' => GetMessage('WIKI_VERSION_TITLE'),
				'TEXT' => GetMessage('WIKI_VERSION'),
				'ONCLICK' => "jsUtils.Redirect([], '".CUtil::JSEscape($arHistory['SHOW_LINK'])."');"
			);
			$arActions[] =  array(
				'TITLE' => GetMessage('WIKI_RECOVER_TITLE'),
				'TEXT' => GetMessage('WIKI_RECOVER'),
				'ONCLICK' => "jsUtils.Redirect([], '".CUtil::JSEscape($arHistory['CANCEL_LINK'])."');"
			);
			if (!empty($arHistory['PREV_LINK']) || !empty($arHistory['CUR_LINK']))
				$arActions[] =  array('SEPARATOR' => 'true');
			if (!empty($arHistory['PREV_LINK']))
			{
				$arActions[] =  array(
					'TITLE' => GetMessage('WIKI_PREV_VERSION_TITLE'),
					'TEXT' => GetMessage('WIKI_PREV_VERSION'),
					'ONCLICK' => "jsUtils.Redirect([], '".CUtil::JSEscape($arHistory['PREV_LINK'])."');"
				);
			}

			if (!empty($arHistory['CUR_LINK']))
			{
				$arActions[] =  array(
					'TITLE' => GetMessage('WIKI_CURR_VERSION_TITLE'),
					'TEXT' => GetMessage('WIKI_CURR_VERSION'),
					'ONCLICK' =>  "jsUtils.Redirect([], '".CUtil::JSEscape($arHistory['CUR_LINK'])."');"
				);
			}

			if (!empty($arHistory['DELETE_LINK']))
			{
				$arActions[] =  array('SEPARATOR' => 'true');
				$arActions[] =  array(
					'TITLE' => GetMessage('WIKI_DELETE_CURR_VERSION_TITLE'),
					'TEXT' => GetMessage('WIKI_DELETE_CURR_VERSION'),
					'ONCLICK' => "wiki_version_delete_grid('".CUtil::JSEscape(GetMessage('WIKI_DELETE_CURR_VERSION_TITLE'))."', '".CUtil::JSEscape(GetMessage('WIKI_DELETE_CURR_VERSION_DELETE_CONFIRM'))."', '".CUtil::JSEscape(GetMessage('WIKI_DELETE_CURR_VERSION_DELETE'))."', '".CUtil::JSEscape($arHistory['DELETE_LINK'])."')"
				);
			}
			$arResult["GRID_DATA"][] = array(
				'id' => $arHistory['ID'],
				'actions' => $arActions,
				'data' => $_arData,
				'editable' => 'N'
			);
		}

		$APPLICATION->IncludeComponent(
			'bitrix:main.interface.grid',
			'',
			array(
				'GRID_ID' => 'WIKI_HISTORY',
				'HEADERS' => $arHeaders,
				'SORT' => array($by => $order),
				'ROWS' => $arResult['GRID_DATA'],
				'FOOTER' => array(array('title' => GetMessage('WIKI_ALL'), 'value' => $arResult['DB_LIST']->SelectedRowsCount())),
				'EDITABLE' => 'Y',
				'ACTIONS' => array(
				'custom_html' => "
					<input type=\"hidden\" name=\"".$arResult['PAGE_VAR']."\" value=\"".$arResult['ELEMENT']['NAME']."\">
					<input type=\"hidden\" name=\"".$arResult['OPER_VAR']."\" value=\"history_diff\">
					<input type=\"submit\" name=\"compare\" value=\"".GetMessage('WIKI_DIFF_VERSION')."\" disabled/>"
				),
				'ACTION_ALL_ROWS' => false,
				'NAV_OBJECT' => $arResult['DB_LIST'],
				'AJAX_MODE' => 'N',
			),
			$component
		);
		?>
		<script type="text/javascript">

			function wiki_version_delete_grid(title, message, btnTitle, path)
			{
				var d;
				d = new BX.CDialog({
					title: title,
					head: '',
					content: message,
					resizable: false,
					draggable: true,
					height: 70,
					width: 300
				});

				var _BTN = [
					{
						title: btnTitle,
						id: 'crmOk',
						'action': function ()
						{
							window.location.href = path;
							BX.WindowManager.Get().Close();
						}
					},
					BX.CDialog.btnCancel
				];
				d.ClearButtons();
				d.SetButtons(_BTN);
				d.Show();
			}

			BX('WIKI_HISTORY_check_all').style.visibility = 'hidden';
			document.forms['form_WIKI_HISTORY'].action = '<?=$arResult['PATH_TO_HISTORY_DIFF']?>';
			var inp = document.forms['form_WIKI_HISTORY'].elements;
			for(var i = 0; i < inp.length; i++)
			{
				if (inp[i].type == 'submit' && inp[i].name == 'apply')
					inp[i].style.visibility = 'hidden';

				if (inp[i].type == 'checkbox' && inp[i].id.indexOf('ID_') == 0)
				{
					inp[i].title = '<?=CUTIL::JSEscape(GetMessage('WIKI_SELECT_DIFF'))?>';
					BX.bind(inp[i], 'click', function() {
						var j = 0;
						var i = 0;
						var inp = document.forms['form_WIKI_HISTORY'].elements;
						for(i = 0; i < inp.length; i++)
						{
							if (inp[i].type == 'checkbox' && inp[i].id.indexOf('ID_') == 0 && inp[i].checked)
								j++;
						}

						if ((j >= 2 && this.checked) || !this.checked)
						{
							for(i = 0; i < inp.length; i++)
							{
								if (inp[i].type == 'checkbox' && inp[i].id.indexOf('ID_') == 0 && !inp[i].checked)
									inp[i].disabled = this.checked;
							}
						}
						document.forms['form_WIKI_HISTORY'].elements['compare'].disabled = (j < 2);
					});
				}
			}
		</script>
	<? endif;?>
	</div>
<? endif;?>
</div>
