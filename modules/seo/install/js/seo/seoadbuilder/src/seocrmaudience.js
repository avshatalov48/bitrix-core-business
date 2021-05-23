export class SeoCrmAudience
{
	static apply(applyBtn)
	{
		BX.SidePanel.Instance.close();
		BX.SidePanel.Instance.postMessage(
			window,
			'seo-crm-audience-configured',
			{
				segmentInclude: window.senderSegmentSelector.selectorInclude.selector.getTilesId() || [],
				segmentExclude: window.senderSegmentSelector.selectorExclude.selector.getTilesId() || []
			}
		);

		setTimeout(() => {
			applyBtn.classList.remove('ui-btn-wait')
		}, 200)
	}
}