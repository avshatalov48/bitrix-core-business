

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
		const url = new URL(window.location.href);
		if (url.searchParams.get('transition') === 'true')
		{
			url.searchParams.delete('transition');
			window.history.replaceState({}, '', url.toString());
		}
		else
		{
			BX.removeClass(document.body, "landing-page-transition");
		}
		document.addEventListener('DOMContentLoaded', function() {
			setTimeout(() => {
				BX.removeClass(document.body, "landing-page-transition");
			}, 300);
		});
	}
}