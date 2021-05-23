<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if (!$this->__component->__parent || empty($this->__component->__parent->__name) || $this->__component->__parent->__name != "bitrix:blog"):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/themes/blue/style.css');
endif;
?>
<?
if(!empty($arResult["POST"]))
{
?>
	<ul>
	<li class="blog-best-posts">
		<h3 class='blog-sidebartitle'><?=GetMessage("BLOG_BLOG_FAVORITE")?></h3>
		<ul>
			<?
			foreach($arResult["POST"] as $arPost)
			{

				?>
				<li><a href="<?=$arPost["urlToPost"]?>"><?= $arPost["TITLE"];?></a></li>
				<?
			}
			?>
		</ul>
	</li>	
	</ul>
	<?
}
?>	