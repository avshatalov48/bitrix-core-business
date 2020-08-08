<?

use Bitrix\Main\Context;

\Bitrix\Main\UI\Extension::load([
	'im_page',
]);

$dialogId = (string)Context::getCurrent()->getRequest()->getQuery('dialogId');
$useVideo = (string)Context::getCurrent()->getRequest()->getQuery('useVideo');
$action = (string)Context::getCurrent()->getRequest()->getQuery('action');

?>
	<script>
		BX.ready(function()
		{
			var action = '<?= $arResult['ACTION']?>';
			window.callController = new BX.Call.Controller({listenIncoming: false});

			var callFabric = BX.Call.Engine.getInstance().__getCallFabric('Plain');

			window.currentCall = callFabric.createCall({
				id: <?= $arResult['CALL']['ID'] ?>,
				instanceId: BX.Call.Engine.getInstance().getUuidv4(),
				direction: action == 'startCall' ?  BX.Call.Direction.Outgoing : BX.Call.Direction.Incoming,
				users: [1, 474], //
				videoEnabled: true, //
				enableMicAutoParameters: true,
				associatedEntity: '474',
				events: {
					onDestroy: function ()
					{
						console.log("call destroyed");
					}
				},
				debug: true
			});

			console.trace(currentCall);


			BX.Call.Engine.getInstance().calls[currentCall.id] = currentCall;

			if (action == 'startCall')
			{
				callController.startCall_internal(currentCall);
			}
			else
			{
				callController.answerCall_internal(currentCall, true);
			}
		})

	</script>
<?

