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

if ($arResult['shouldShowDebugger'] === true): ?>

	<script>
		BX.Event.ready(() => {

			const initializeDebugger = () => {
				BX.Runtime.loadExtension('bizproc.debugger').then(
					(exports) => {
						const {Manager} = exports;

						Manager.Instance.initializeDebugger({
							'session': <?= CUtil::PhpToJSObject($arResult['session']) ?>,
							'documentSigned': '<?= CUtil::JSEscape($arResult['documentSigned']) ?>',
						});
					}
				);
			};
			if (window.BX.frameCache)
			{
				if (window.BX.frameCache.frameDataInserted === true)
				{
					initializeDebugger();
				}
				else
				{
					BX.Event.EventEmitter.subscribe('onFrameDataProcessed', initializeDebugger);
				}
			}
			else
			{
				BX.Event.ready(initializeDebugger);
			}
		});
	</script>

<?php endif;

$frame->end();