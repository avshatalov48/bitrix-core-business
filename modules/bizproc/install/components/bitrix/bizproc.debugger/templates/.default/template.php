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

			if (BX.Type.isUndefined(window.frameCacheVars))
			{
				BX.Event.ready(initializeDebugger);
			}
			else
			{
				const isCompositeReady = (
					BX.frameCache?.frameDataInserted === true || !BX.Type.isUndefined(window.frameRequestFail)
				);
				if (isCompositeReady)
				{
					initializeDebugger();
				}
				else
				{
					BX.Event.EventEmitter.subscribe('onFrameDataProcessed', initializeDebugger);
					BX.Event.EventEmitter.subscribe('onFrameDataRequestFail', initializeDebugger);
				}
			}
		});
	</script>

<?php endif;

$frame->end();
