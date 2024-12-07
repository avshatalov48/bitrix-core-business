<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="wizard">

<form method="post"  action="<?=POST_FORM_ACTION_URI?>" name="wizard">
<input type=hidden name=LAST_SECTION_ID value="<?=$arResult['LAST_SECTION_ID']?>">
<input type=hidden name=CURRENT_STEP value="<?=$arResult['CURRENT_STEP']?>">

<?
if ($arResult['ERROR'])
	echo '<div><font class=wizard_errortext>'.$arResult['ERROR'].'</font></div>';
elseif($arResult['MESSAGE'])
	echo '<div><font class=wizard_oktext>'.$arResult['MESSAGE'].'</font></div>';

$arHelp = [];

?>
<table cellspacing=0 cellpadding=0 border=0 width=100%>
<tr>
	<td height=70 width=6 align=left valign=top><img src="<?=$templateFolder?>/images/top_left.gif"></td>
	<td background="<?=$templateFolder?>/images/top_fill.gif" style="background-repeat: repeat-x" align=center valign=middle width=60><img src="<?=$templateFolder?>/images/icon2.gif"><br><img src="/bitrix/images/1.gif" height=1 width=60></td>
	<td background="<?=$templateFolder?>/images/top_fill.gif" style="background-repeat: repeat-x" align=left valign=middle>
		<div class=wizard_title><?=GetMessage("WZ_TITLE")?></div>
		<?
		if ($arResult['TOP_MESSAGE'])
			echo "<div class=wizard_smalltext>".$arResult['TOP_MESSAGE']."</div>";
		?>
	</td>
	<td width=6 align=right valign=top><img src="<?=$templateFolder?>/images/top_right.gif"></td>
</tr>
<?
?>
<tr>
	<td style="border-left:1px solid #BEC0CF"><img src="/bitrix/images/1.gif" width=1 height=1></td>
	<td><img src="/bitrix/images/1.gif" height=40 width=4></td>
	<td valign=bottom><div class=wizard_step><?=$arResult['CURRENT_STEP_TEXT']?></div></td>
	<td style="border-right:1px solid #BEC0CF"><img src="/bitrix/images/1.gif" width=1 height=1></td>
</tr>
<tr>
	<td style="border-left:1px solid #BEC0CF"><img src="/bitrix/images/1.gif" width=1 height=1></td>
	<td colspan=2><hr size=1 style="background-color:#CCCCCC;height:1px;border: medium none;color:#CCCCCC;"></td>
	<td style="border-right:1px solid #BEC0CF"><img src="/bitrix/images/1.gif" width=1 height=1></td>
</tr>
<tr>
	<td style="border-left:1px solid #BEC0CF"><img src="/bitrix/images/1.gif" width=1 height=1></td>
	<td><img src="/bitrix/images/1.gif" width=1 height=1></td>
	<td align=center>
		<table cellspacing=0 cellpadding=8 border=0>
			<?
			if (count($arResult['SECTIONS']))
			{
				echo '<tr><td>';
				foreach($arResult['SECTIONS'] as $f)
				{
					$id = $f['ID'];
					echo "<div class=wizard_sections><input type=radio name=SECTION_ID value='$id' id='section_$id'> <label for='section_$id'><font class=text>".$f['NAME']."</font></label></div>";
				}
				echo '</td></tr>';
			}

			if (!empty($arResult['FIELDS']) && is_array($arResult['FIELDS']))
			{
				$i=0;
				foreach($arResult['FIELDS'] as $num=>$f)
				{
					if (trim($f['DETAIL_TEXT']))
					{
						$i++;
						$link = '&nbsp;<a href="#note'.$i.'"><sup>'.$i.'</sup></a>';
						$arHelp[$i] = $f['DETAIL_TEXT'];
					}
					else
						$link = '';


					$id = $f['FIELD_ID'];

					echo '<tr>
						<td valign=top align=left>';

					if ($f['FIELD_TYPE']=='text') // simple input field
						echo '<div class="wizard_field_name">' . $f['NAME'] . ':</div>' .
								'<input name="wizard['.$id.']" size=65 value="'.$f['FIELD_VALUE'].'">' .
									'<br><font class=smalltext>' . $f['PREVIEW_TEXT'] . $link . '</font>';
					elseif ($f['FIELD_TYPE']=='checkbox') // checkbox
						echo '<div class="wizard_field_name"><input type=checkbox value="'.GetMessage('WZ_YES').'" name="wizard['.$id.']" '.($f['FIELD_VALUE']?'checked':'').' id="'.$id.'">' .
							'<label for="'.$id.'"><b>' . $f['NAME'] . '</b></label></div>' .
								'<font class=smalltext>' . $f['PREVIEW_TEXT'] . $link . '</font>';
					elseif ($f['FIELD_TYPE']=='select') // select box
					{
						echo 	'<div class="wizard_field_name">' . $f['NAME'] . ':</div>' .
								'<select name="wizard['.$id.']">';

							foreach($f['FIELD_VALUES'] as $v)
								echo '<option value="'.$v.'" '.($f['FIELD_VALUE']==$v?'selected':'').'>'.$v.'</option>';

						echo ' </select>' .
								'<br><font class=smalltext>' . $f['PREVIEW_TEXT'] . $link . '</font>';
					}
					elseif ($f['FIELD_TYPE']=='radio') // radio box
					{
						echo 	'<div class="wizard_field_name">' . $f['NAME'] . ':</div>' .
								'<table cellspacing=2 cellpadding=0 border=0>';
							foreach($f['FIELD_VALUES'] as $k=>$v)
								echo '<tr><td align=left><input type=radio name="wizard['.$id.']" value="'.$v.'" '.($f['FIELD_VALUE']==$v?'checked':'').' id="'.$id.'_'.$k.'"><label for="'.$id.'_'.$k.'"> '.$v.'</label></td></tr>';
						echo	'</table><font class=smalltext>' . $f['PREVIEW_TEXT'] . $link . '</font>';
					}
					elseif ($f['FIELD_TYPE']=='multitext') // input options
					{
						echo 	'<div class="wizard_field_name">' . $f['NAME'] . ':</div>' .
								'<table cellspacing=2 cellpadding=0 border=0>';
							foreach($f['FIELD_VALUES'] as $k=>$v)
								echo '<tr><td align=right>'.$v.':</td><td><input name="wizard['.$id.']['.$k.']" value="'.($f['FIELD_VALUE'][$k]).'"></td></tr>';
						echo	'</table><font class=smalltext>' . $f['PREVIEW_TEXT'] . $link . '</font>';
					}
					else // textarea, default
						echo 	'<div class="wizard_field_name">' . $f['NAME'] . ':</div>' .
								'<textarea name="wizard['.$id.']" rows=10 cols=65>'.$f['FIELD_VALUE'].'</textarea>' .
									'<br><font class=smalltext>' . $f['PREVIEW_TEXT'] . $link . '</font>';

					echo '	</td>
						</tr>';

					unset($arResult['FIELDS'][$field_num]['FIELD_VALUE']);
				}
			}

?>
		</table>
	</td>
	<td style="border-right:1px solid #BEC0CF"><img src="/bitrix/images/1.gif" width=1 height=1></td>
</tr>
<tr>
	<td style="border-left:1px solid #BEC0CF"><img src="/bitrix/images/1.gif" width=1 height=1></td>
	<td><img src="/bitrix/images/1.gif" width=1 height=1></td>
	<td>
		<p align=right style="padding-right:15px;padding-top:15px">
		<? if (count($arResult['SECTIONS'])) { ?>
			<? if ($arResult['CURRENT_STEP']>1)
			{
				$img = $templateFolder.'/images/'.LANGUAGE_ID.'/button_back.gif';
				if (file_exists($_SERVER['DOCUMENT_ROOT'].$img))
					echo '<input type=image src="'.$img.'" name="back">';
				else
					echo '<input type=submit value="'.GetMessage('WZ_BTN_BACK').'" name="back">';
			}
			elseif ($arParams['BACK_URL'])
			{
				$img = $templateFolder.'/images/'.LANGUAGE_ID.'/button_back.gif';
				if (file_exists($_SERVER['DOCUMENT_ROOT'].$img))
					echo '<input type=image src="'.$img.'" onclick="javascript:window.location=\''.htmlspecialcharsbx(addslashes($arParams['BACK_URL'])).'\';return false;">';
				else
					echo '<input type=submit value="'.GetMessage('WZ_BTN_BACK').'" name="back">';
			}
		?>
			<img src="/bitrix/images/1.gif" width=1 height=1>
		<?
			$img = $templateFolder.'/images/'.LANGUAGE_ID.'/button_next.gif';
			if (file_exists($_SERVER['DOCUMENT_ROOT'].$img))
				echo '<input type=image src="'.$img.'" name="next">';
			else
				echo '<input type=submit value="'.GetMessage('WZ_BTN_NEXT').'" name="next">';
		?>
		<? } else { // Finish ?>
			<script>
			function Goto(url)
			{
				var wizard = document.forms.wizard;
				wizard.action=url;
				BX.submit(wizard);
			}
			</script>

			<?
			$img = $templateFolder.'/images/'.LANGUAGE_ID.'/button_back.gif';
			if (file_exists($_SERVER['DOCUMENT_ROOT'].$img))
				echo '<input type=image src="'.$img.'" name="back">';
			else
				echo '<input type=submit value="'.GetMessage('WZ_BTN_BACK').'" name="back">';
			?>
			<img src="/bitrix/images/1.gif" width=1 height=1>
			<?
			$img = $templateFolder.'/images/'.LANGUAGE_ID.'/button_done.gif';
			if (file_exists($_SERVER['DOCUMENT_ROOT'].$img))
				echo '<input type=image src="'.$img.'" name="wizard" onclick="Goto(\''.htmlspecialcharsbx(addslashes($arParams['NEXT_URL'])).'\')">';
			else
				echo '<input type=submit value="'.GetMessage('WZ_BTN_FINISH').'" name="wizard" onclick="Goto(\''.htmlspecialcharsbx(addslashes($arParams['NEXT_URL'])).'\')">';
			?>
		<? } ?>
		</p>
	</td>
	<td style="border-right:1px solid #BEC0CF"><img src="/bitrix/images/1.gif" width=1 height=1></td>
</tr>
<tr>
	<td height=6 width=6 align=left valign=bottom><img src="<?=$templateFolder?>/images/bottom_left.gif"></td>
	<td background="<?=$templateFolder?>/images/bottom_fill.gif"></td>
	<td background="<?=$templateFolder?>/images/bottom_fill.gif"></td>
	<td height=6 width=6 align=right valign=bottom><img src="<?=$templateFolder?>/images/bottom_right.gif"></td>
</tr>
</table>
<?
	if (!empty($arResult['HIDDEN']) && is_array($arResult['HIDDEN']))
	{
		foreach($arResult['HIDDEN'] as $k=>$v)
		{
			if (is_array($v))
				foreach($v as $k1=>$v1)
					echo '<input type=hidden name="wizard['.$k.']['.$k1.']" value="'.$v1.'">';
			else
				echo '<input type=hidden name="wizard['.$k.']" value="'.$v.'">';
		}
	}
?>
</form>
<?

// Help
if (!empty($arHelp))
{
?>
	<br>
	<table cellspacing=4 cellpadding=2 style="background-color:#FFFFEF;border:1px solid #d7d7be;" width="100%">
<?
	foreach($arHelp as $i=>$help)
		echo '<tr><td valign=top><font class=smalltext><b>'.$i.'.</b></font></td><td><font class=smalltext><a name="note'.$i.'"></a> ' . $help . '</font></td></tr>';
?>
	</table>
<?
}
?>
</div>
