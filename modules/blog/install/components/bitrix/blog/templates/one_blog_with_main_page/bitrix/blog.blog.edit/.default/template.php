<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if(!empty($arResult["OK_MESSAGE"]))
{
	foreach($arResult["OK_MESSAGE"] as $v)
	{
		?>
		<span class='notetext'><?=$v?></span><br /><br />
		<?
	}
}
if(!empty($arResult["MESSAGE"]))
{
	foreach($arResult["MESSAGE"] as $v)
	{
		?>
		<?=$v?><br /><br />
		<?
	}
}
if(!empty($arResult["ERROR_MESSAGE"]))
{
	foreach($arResult["ERROR_MESSAGE"] as $v)
	{
		?>
		<span class='errortext'><?=$v?></span><br /><br />
		<?
	}
}
if(strlen($arResult["FATAL_ERROR"])>0)
{
	?>
	<span class='errortext'><?=$arResult["FATAL_ERROR"]?></span><br /><br />
	<?
}
elseif(count($arResult["POST"])>0)
{
	foreach($arResult["POST"] as $CurPost)
	{
		?>
		<table class="blog-table-post">
		<tr>
			<th nowrap width="100%">
				<table width="100%" cellspacing="2" cellpadding="0" border="0" class="blog-table-post-table">
				<tr>
					<td width="100%" align="left">
						<span class="blog-post-date"><?=$CurPost["DATE_PUBLISH_FORMATED"]?>
						<br /><b><?=$CurPost["TITLE"]?></b></span>
					</td>
					<?if(strLen($CurPost["urlToEdit"])>0):?>
						<td>
							<a href="<?=$CurPost["urlToEdit"]?>" class="blog-post-edit"></a>
						</td>
					<?endif;?>
					<?if(strLen($CurPost["urlToDelete"])>0):?>
						<td>
							<a href="javascript:if(confirm('<?=GetMessage("BLOG_MES_DELETE_POST_CONFIRM")?>')) window.location='<?=$CurPost["urlToDelete"]?>'" class="blog-post-delete"></a>
						</td>
					<?endif;?>
				</tr>
				</table>
			</th>
		</tr>
		<tr>
			<td>
				<?=$CurPost["TEXT_FORMATED"]?>
				<?if(!empty($CurPost["Category"]))
				{
					?>
					<table width="100%" cellspacing="0" cellpadding="0" border="0" class="blog-table-post-table">
					<tr>
						<td><div class="blog-line"></div></td>
					</tr>
					<tr>
						<td align="left"><?echo GetMessage("BLOG_BLOG_BLOG_CATEGORY");
						$i=0;
						foreach($CurPost["Category"] as $v)
						{
							if($i!=0)
								echo ",";
							?> <a href="<?=$v["urlToCategory"]?>"><?=$v["NAME"]?></a><?
							$i++;
						}?>
						</td>
					</tr>
					</table>
					<?
				}
				?>
			</td>
		</tr>
		</table>
		<br />
		<?
	}
}
else
	echo GetMessage("B_B_DRAFT_NO_MES");
?>	