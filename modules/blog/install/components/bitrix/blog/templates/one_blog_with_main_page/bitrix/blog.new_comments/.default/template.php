<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
foreach($arResult as $arComment)
{
	if($arComment["FIRST"]!="Y")
	{
		?><div class="blog-line"></div><?
	}
	?>
	<span class="blog-author">
	<?
	if(strlen($arComment["urlToAuthor"])>0)
	{
		?>
		<a href="<?=$arComment["urlToAuthor"]?>" class="blog-user-grey"></a>&nbsp;<a href="<?=$arComment["urlToAuthor"]?>"><?=$arComment["AuthorName"]?></a>
		<?
	}
	elseif(strlen($arComment["urlToAuthor"])>0)
	{
		?>
		<a href="<?=$arComment["urlToAuthor"]?>" class="blog-user-grey"></a>&nbsp;<a href="<?=$arComment["urlToAuthor"]?>"><?=$arComment["AuthorName"]?></a>
		<?
	}
	else
	{
		?>
		<div class="blog-user-grey"></div>&nbsp;<?=$arComment["AuthorName"]?>
		<?
	}
	?>
	</span>
	<span class="blog-post-info">
		&nbsp;&nbsp;<a href="<?=$arComment["urlToComment"]?>" class="blog-clock" title="<?=GetMessage("BLOG_BLOG_M_DATE")?>"><?=$arComment["DATE_CREATE_FORMATED"]?></a>
	</span>
	
	<br clear="all"/>	
	<?
	if(strlen($arComment["TitleFormated"])>0) 
	{
		?>
		<span class="blog-post-date"><b><a href="<?=$arComment["urlToComment"]?>"><?
			echo $arComment["TitleFormated"];
		?></a></b></span><br /><?
	}
	else
	{
		?><a href="<?=$arComment["urlToComment"]?>"><?
	}
	?>
	<small><?=$arComment["TEXT_FORMATED"]?></small>
	<?
	if(strlen($arComment["TitleFormated"])>0) 
	{
		?></a><?
	}
	?>
	<br />

	<?
}
?>	
