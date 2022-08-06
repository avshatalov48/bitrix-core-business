<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var CBitrixComponentTemplate $this
 * @var array $arResult
 */

$frame = $this->createFrame()->begin('');

if ($arResult['shouldShowDebugger']): ?>

	<script>
		BX.ready(() => {
			BX.Runtime.loadExtension('bizproc.debugger').then(
				(exports) => {
					const {Manager} = exports;

					Manager.Instance.initializeDebugger({
						'session': <?= CUtil::PhpToJSObject($arResult['session']) ?>,
						'documentSigned': '<?= CUtil::JSEscape($arResult['documentSigned']) ?>',
					});
				}
			);
		});
	</script>

<?php endif;

$frame->end();