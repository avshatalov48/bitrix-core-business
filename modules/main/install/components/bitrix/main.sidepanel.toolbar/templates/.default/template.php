<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var CBitrixComponentTemplate $this
 * @var array $arResult
 */
$frame = $this->createFrame()->begin('');
?>

<script>
	(function() {
		const toolbar = BX.SidePanel.Instance.createToolbar(
			<?= CUtil::PhpToJSObject($arResult['options'], false, false, true) ?>
		);
		const shouldShowSpotlight = <?= CUtil::PhpToJSObject($arResult['spotlight'], false, false, true) ?>;

		let spotlight = null;
		const showSpotlight = () => {
			const slider = BX.SidePanel.Instance.getTopSlider();
			if (slider && slider.getMinimizeLabel() !== null)
			{
				BX.Runtime.loadExtension(['spotlight', 'ui.tour']).then(() => {
					let guide = null;
					spotlight = new BX.SpotLight({
						id: 'sidepanel_toolbar',
						autoSave: true,
						color: '#3bc8f5',
						targetElement: slider.getMinimizeLabel().getIconContainer(),
						targetVertex: 'middle-center',
						events: {
							onTargetEnter: () => {
								if (guide)
								{
									return;
								}

								guide = new BX.UI.Tour.Guide({
									onEvents: true,
									steps: [{
											target: slider.getMinimizeLabel().getIconContainer(),
											text: '<?= CUtil::JSEscape($arResult['spotlightHint']) ?>',
											title: '<?= CUtil::JSEscape($arResult['spotlightTitle']) ?>',
									}],
								});

								guide.getPopup().setAutoHide(true);
								guide.getPopup().setOverlay(true);
								guide.getPopup().setWidth(400);
								guide.getPopup().subscribe('onClose', () => { spotlight.close(); });
								guide.getPopup().subscribe('onDestroy', () => { spotlight.close(); });
								guide.showNextStep();
								guide.getPopup().adjustPosition();
							}
						}
					});

					spotlight.show();
				});
			}
		};

		const hideSpotlight = () => {
			if (spotlight)
			{
				spotlight.close();
				BX.Event.EventEmitter.unsubscribe('SidePanel.Slider:onOpenComplete', showSpotlight);
				BX.Event.EventEmitter.unsubscribe('SidePanel.Slider:onClose', hideSpotlight);
			}
		};

		if (shouldShowSpotlight)
		{
			BX.Event.EventEmitter.subscribe('SidePanel.Slider:onOpenComplete', showSpotlight);
			BX.Event.EventEmitter.subscribe('SidePanel.Slider:onClose', hideSpotlight);
		}

		if (toolbar.getItems().length > 0)
		{
			toolbar.show();
			if (!toolbar.canShowOnTop())
			{
				toolbar.mute();
			}
		}
	})();
</script>

<?
$frame->end();
