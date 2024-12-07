<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?

if (count($arResult) > 0)
{
	?>
	<ul>
	<?
	foreach ($arResult as $arPlan)
	{
		?>
		<li><b><?=$arPlan["NAME"]?></b><br />
		<?
		if ($arPlan["DESCRIPTION"] <> '')
		{
		?>
			<small><?=$arPlan["DESCRIPTION"]?></small><br />
		<?
		}
		?>
		<?=GetMessage("SPCAT1_TARIF")?>
		<?=$arPlan["BASE_RATE_FORMAT"] ?>
		<br />
		<?
		if ($arPlan["MIN_PLAN_VALUE"] > 0)
		{?>
			<?=$arPlan["MIN_PLAN_VALUE_FORMAT"]?>
			<br />
		<?
		}
		?>
		</li>
		<?
	}
	?>
	</ul>
	<?
}
else
{
	?>
	<? ShowError(GetMessage("SPCAT1_NO_PLANS"))?>
	<?
}
?>