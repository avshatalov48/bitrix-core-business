<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?if(!empty($arResult["CALENDAR"]))
{
?>
<table border="0" cellspacing="0" cellpadding="0" class="blog-calendar">
<tr>
	<td class="blog-calendar-lt"></td>
	<td class="blog-calendar-t"></td>
	<td class="blog-calendar-rt"></td>
</tr>
<tr>
	<td class="blog-calendar-l"></td>
	<td align="center">
		<table class="blog-calendar-table">
			<tr>
				<td width="0%" align="left"><?
					if ($arResult["urlToPrevYear"] <> ''):
						?><a title="<?=GetMessage("BLOG_BLOG_CLNDR_P_M")?>" href="<?=$arResult["urlToPrevYear"]?>">&laquo;</a>&nbsp;&nbsp;<?
					else:
						?><span class="blogCalDisable">&laquo;&nbsp;&nbsp;</span><?
					endif;
				?></td>
				<td width="0%" align="center"><b><?= GetMessage("BLOG_BLOG_CLNDR_M_".$arResult["CurrentMonth"])." ".$arResult["CurrentYear"]?></b></td>
				<td width="0%" align="right"><?
					if ($arResult["urlToNextYear"] <> ''):
						?>&nbsp;&nbsp;<a title="<?=GetMessage("BLOG_BLOG_CLNDR_N_M")?>" href="<?=$arResult["urlToNextYear"]?>">&raquo;</a><?
					else:
						?><span class="blogCalDisable">&nbsp;&nbsp;&raquo;</span><?
					endif;
				?></td>
			</tr>
		</table>
		<div class="blog-calendar-line"></div><br />
		<table border="0" cellspacing="0" cellpadding="2" class="blog-calendar-table">
			<tr>
				<th><?=GetMessage("BLOG_BLOG_CLNDR_D_1")?></th>
				<th><?=GetMessage("BLOG_BLOG_CLNDR_D_2")?></th>
				<th><?=GetMessage("BLOG_BLOG_CLNDR_D_3")?></th>
				<th><?=GetMessage("BLOG_BLOG_CLNDR_D_4")?></th>
				<th><?=GetMessage("BLOG_BLOG_CLNDR_D_5")?></th>
				<th><?=GetMessage("BLOG_BLOG_CLNDR_D_6")?></th>
				<th><?=GetMessage("BLOG_BLOG_CLNDR_D_7")?></th>
			</tr>

			<?
			foreach($arResult["CALENDAR"] as $k=>$v)
			{
				if($k!=0)
				{
					?>
					<tr><td colspan="7"><div class="blog-calendar-line"></div></td></tr>
					<?
				}
				?>
				<tr>
				<?
				foreach($v as $vv)
				{
					
					$class = "";
					switch($vv["type"])
					{
						case "selected": $class = "blogCalSelected"; break;
						case "today": $class = "blogCalToday"; break;
						case "weekend": $class = "blogCalWeekend"; break;
					}
					?>
					<td align="center" class="<?=$class?>" onMouseOver="this.className='blogCalHighlight'" onMouseOut="this.className='<?=$class?>'">
						<?
						if($vv["link"] <> '')
						{
							?>
							<a href="<?=$vv["link"]?>"><?=$vv["day"]?></a>
							<?
						}
						else
							echo $vv["day"];
					?>
					</td>
					<?
				}
				?>
				</tr>
				<?
			}
			?>
		</table>
	</td>
	<td class="blog-calendar-r"></td>
</tr>
<tr>
	<td class="blog-calendar-lb"></td>
	<td class="blog-calendar-b"></td>
	<td class="blog-calendar-rb"></td>
</tr>
</table>
<?}?>