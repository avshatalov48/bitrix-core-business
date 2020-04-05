<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$bDemo = (CTicket::IsDemo()) ? "Y" : "N";
$bAdmin = (CTicket::IsAdmin()) ? "Y" : "N";
$bSupportTeam = (CTicket::IsSupportTeam()) ? "Y" : "N";
$bADS = $bDemo == 'Y' || $bAdmin == 'Y' || $bSupportTeam == 'Y';

if(count($arResult["TICKETS"]) <= 0)
	echo GetMessage("G_TICKETS_LIST_EMPTY");
$bFirst = true;

if (array_key_exists("TICKETS", $arResult) && is_array($arResult["TICKETS"])):

	foreach ($arResult["TICKETS"] as $arTicket):

		if (!$bFirst)
		{
			?><div class="support-ticket-line"></div><?
		}
		?>
		<span class="sonet-forum-post-date"><small><?=GetMessage("G_TICKETS_TIMESTAMP_X")?>: <?=$arTicket["TIMESTAMP_X"]?></small><br /></span>
		<table class="support-ticket-lamp"><tr><td><div class="support-lamp-<?=str_replace("_","-",$arTicket["LAMP"])?>" title="<?=GetMessage("G_TICKETS_".strtoupper($arTicket["LAMP"]).($bADS ? "_ALT_SUP" : "_ALT"))?>"></div></td><td><b><a href="<?=$arTicket["TICKET_EDIT_URL"]?>"><? echo $arTicket["TITLE"]; ?></a></b></td></tr></table>

		<span class="support-ticket-info">
			<?=GetMessage("G_TICKETS_MESSAGES")?>:&nbsp;<?=$arTicket["MESSAGES"]?><br />
			<? if (strlen($arTicket["STATUS_NAME"]) > 0):?>
				<?=GetMessage("G_TICKETS_STATUS")?>:&nbsp;<?=$arTicket["STATUS_NAME"]?><br />
			<? endif; ?>
			<? if (strlen($arTicket["RESPONSIBLE_NAME"]) > 0):?>
				<?=GetMessage("G_TICKETS_RESPONSIBLE")?>:&nbsp;<?=$arTicket["RESPONSIBLE_NAME"]?><br />
			<? endif; ?>
		</span>
		<?
		$bFirst = false;

	endforeach;
endif;
?>