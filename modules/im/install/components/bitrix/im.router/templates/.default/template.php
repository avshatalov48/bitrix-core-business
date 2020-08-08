<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();
?>
<div class="bx-messenger-box-hello-wrap">
	<div class="bx-messenger-box-hello"><?=GetMessage('IM_MESSENGER_EMPTY_PAGE');?></div>
</div>
<script type="text/javascript">
	BX.addCustomEvent('onImInitBefore', function(im){
		im.fullScreen = true;
	});
	<?if (!isset($_GET['IM_SETTINGS']) && !isset($_GET['IM_HISTORY']) && !isset($_GET['IM_NOTIFY'])):?>
	BX.addCustomEvent('onImInit', function(im){
		im.openMessenger();
	});
	<?endif;?>
</script>
