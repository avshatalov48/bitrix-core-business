

export class PageTransition
{
	/**
	 * Constructor.
	 */
	constructor()
	{
		this.init();
	}

	init()
	{
		const referrer = document.referrer;
		if (referrer !== '')
		{
			let isSameHost = false;
			let isDifferentPath = false;
			let isIframeDisabled = false;
			const previousUrl = new URL(referrer);
			if (previousUrl)
			{
				isSameHost = window.location.host === previousUrl.hostname;
				isDifferentPath = window.location.pathname !== previousUrl.pathname;
				isIframeDisabled = previousUrl.searchParams.get('IFRAME') !== 'Y';
			}
			if (!isIframeDisabled || !isSameHost || !isDifferentPath)
			{
				BX.removeClass(document.body, 'landing-page-transition');
			}
		}
		else
		{
			BX.removeClass(document.body, 'landing-page-transition');
		}

		document.addEventListener('DOMContentLoaded', function() {
			setTimeout(() => {
				BX.removeClass(document.body, "landing-page-transition");
			}, 300);
		});
	}
}