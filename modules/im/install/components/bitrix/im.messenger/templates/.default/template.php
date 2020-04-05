<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>
<div id="bx-notifier-panel" class="bx-notifier-panel">
	<span class="bx-notifier-panel-left"></span><span class="bx-notifier-panel-center"><span class="bx-notifier-drag">
	</span><span class="bx-notifier-indicators"><a href="javascript:void(0)" class="bx-notifier-indicator bx-notifier-call" title="<?=GetMessage('IM_MESSENGER_OPEN_CALL')?>"><span class="bx-notifier-indicator-text"></span><span class="bx-notifier-indicator-icon"></span><span class="bx-notifier-indicator-count"></span>
		</a><a href="javascript:void(0)" class="bx-notifier-indicator bx-notifier-message" title="<?=GetMessage('IM_MESSENGER_OPEN_MESSENGER_2');?>"><span class="bx-notifier-indicator-text"></span><span class="bx-notifier-indicator-icon"></span><span class="bx-notifier-indicator-count"></span>
		</a><a href="javascript:void(0)" class="bx-notifier-indicator bx-notifier-notify" title="<?=GetMessage('IM_MESSENGER_OPEN_NOTIFY');?>"><span class="bx-notifier-indicator-text"></span><span class="bx-notifier-indicator-icon"></span><span class="bx-notifier-indicator-count"></span>
		</a><a class="bx-notifier-indicator bx-notifier-mail" href="#mail" title="<?=GetMessage('IM_MESSENGER_OPEN_EMAIL');?>" target="_blank"><span class="bx-notifier-indicator-icon"></span><span class="bx-notifier-indicator-count"></span>
		</a><a class="bx-notifier-indicator bx-notifier-network" href="#network" title="<?=GetMessage('IM_MESSENGER_OPEN_NETWORK');?>" target="_blank"><span class="bx-notifier-indicator-icon"></span><span class="bx-notifier-indicator-count"></span>
		</a></span>
	</span><span class="bx-notifier-panel-right"></span>
</div>
<script type="text/javascript">
<?=CIMMessenger::GetTemplateJS(Array(), $arResult)?>
</script>