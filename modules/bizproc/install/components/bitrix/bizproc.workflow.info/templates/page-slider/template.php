<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var CAllMain $APPLICATION */
/** @var array $arResult */

\Bitrix\Main\UI\Extension::load([
	'sidepanel',
]);
?>

<script>
	BX.ready(() => {
		BX.SidePanel.Instance.open(
			window.location.href,
			{
				events: {
					onCloseComplete: () => {
						window.location.href = '/bizproc/userprocesses/';
					},
				},
				cacheable: false,
				loader: 'bizproc:workflow-info',
				width: (
					window.innerWidth < 1500
						? null
						: (1500 + Math.floor((window.innerWidth - 1500) / 3))
				),
			}
		);
	})
</script>
