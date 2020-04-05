<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>


<div id="webrtc-demo-placeholder"></div>

<script type="text/javascript">
	YourCompanyPrefix.webrtc = new YourCompanyPrefix.webrtc({
		'placeholder': BX('webrtc-demo-placeholder'),
		'signalingLink': '<?=$arResult['signalingLink']?>'
	});
	YourCompanyPrefix.webrtc.drawInterface();
</script>
