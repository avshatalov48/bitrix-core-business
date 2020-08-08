<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

\CJSCore::init('sidepanel');
?>

<script type="text/javascript">
	BX.ready(function()
	{
		BX.SidePanel.Instance.open(
			window.location.href
		);
	});
</script>