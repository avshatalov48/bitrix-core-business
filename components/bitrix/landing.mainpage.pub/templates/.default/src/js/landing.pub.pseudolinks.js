export class Pseudolinks
{
	/**
	 * Constructor.
	 */
	constructor()
	{
		const checkPageLoaded = setInterval(() => {
			if (document.readyState === 'complete')
			{
				this.initPseudoLinks();
				clearInterval(checkPageLoaded);
			}
		}, 500);
	}

	/**
	 * Click callback.
	 *
	 * @return {void}
	 */
	initPseudoLinks()
	{
		const pseudoLinks = [].slice.call(document.querySelectorAll('[data-pseudo-url*="{"]'));
		if (pseudoLinks.length > 0)
		{
			pseudoLinks.forEach((link) => {
				const linkOptionsJson = link.getAttribute('data-pseudo-url');
				const linkOptions = JSON.parse(linkOptionsJson);
				if (
					linkOptions.href
					&& linkOptions.enabled
					&& linkOptions.href.indexOf('/bitrix/services/main/ajax.php?action=landing.api.diskFile.download') !== 0
				)
				{
					if (linkOptions.target === '_self' || linkOptions.target === '_blank')
					{
						link.addEventListener('click', (event) => {
							event.preventDefault();
							let url = null;
							try
							{
								url = new URL(linkOptions.href);
							}
							catch (error)
							{
								console.error(error);
							}
							if (url)
							{
								const isSameHost = url.hostname === window.location.hostname;
								const isIframe = url.searchParams.get('IFRAME') === 'Y';

								if (isSameHost && !isIframe)
								{
									const isDifferentPath = url.pathname !== window.location.pathname;
									if (isDifferentPath)
									{
										BX.addClass(document.body, 'landing-page-transition');
										linkOptions.href = url.href;
										setTimeout(() => {
											this.openPseudoLinks(linkOptions, event);
										}, 400);
										setTimeout(() => {
											BX.removeClass(document.body, 'landing-page-transition');
										}, 3000);
									}
								}
								else
								{
									this.openPseudoLinks(linkOptions, event);
								}
							}
						});
					}
				}
			});
		}
	}

	openPseudoLinks(linkOptions, event)
	{
		if (linkOptions.href.indexOf('/bitrix/services/main/ajax.php?action=landing.api.diskFile.download') === 0)
		{
			return;
		}

		if (linkOptions.query)
		{
			linkOptions.href += (linkOptions.href.indexOf('?') === -1) ? '?' : '&';
			linkOptions.href += linkOptions.query;
		}

		if (this.isValidURL(linkOptions.href))
		{
			top.open(linkOptions.href, linkOptions.target);
		}
	}

	isValidURL(url)
	{
		try
		{
			new URL(url);

			return true;
		}
		catch
		{
			return false;
		}
	}
}
