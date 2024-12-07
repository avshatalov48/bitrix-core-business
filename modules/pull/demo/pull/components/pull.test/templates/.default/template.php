<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>

<button onclick="sendRealtimeEvent();">Send Event</button>

<div id="pull-events-box"></div>

<script>
	function sendRealtimeEvent()
	{
		BX.ajax({
			url: '<?=$arResult['ajaxLink']?>',
			method: 'POST',
			data: {'SEND' : 'Y', 'sessid': BX.bitrix_sessid()}
		});
	}

	BX.ready(function(){
		BX.addCustomEvent("onPullEvent", function(module_id,command,params) {
			if (module_id == "test" && command == 'check')
			{
				console.log(module_id,command,params);
				BX('pull-events-box').innerHTML += params.TIME+'<br>';
			}
		});
		BX.PULL.extendWatch('PULL_TEST');
	});
</script>
