import {Event, Type, Text, Dom} from 'main.core';
import {SliderHacks} from 'landing.sliderhacks';

Event.bind(document, 'click', (event: MouseEvent) => {
	if (Type.isDomNode(event.target))
	{
		const link = event.target.closest('a:not(.ui-btn):not([data-fancybox])');
		if (Type.isDomNode(link))
		{
			const isCurrentPageLink =
				Type.isStringFilled(link.href)
				&& link.hash !== ''
				&& link.pathname === document.location.pathname
				&& link.hostname === document.location.hostname;
			if (Type.isStringFilled(link.href) && link.target !== '_blank' && !isCurrentPageLink)
			{
				event.preventDefault();
				BX.Landing.Pub.TopPanel.pushHistory(link.href);
				void SliderHacks.reloadSlider(link.href);
			}
		}

		const pseudoLink = event.target.closest('[data-pseudo-url]');
		if (Type.isDomNode(pseudoLink))
		{
			const urlParams = Dom.attr(pseudoLink, 'data-pseudo-url');

			if (
				Text.toBoolean(urlParams.enabled)
				&& Type.isStringFilled(urlParams.href)
			)
			{
				if (urlParams.query)
				{
					urlParams.href += (urlParams.href.indexOf('?') === -1) ? '?' : '&';
					urlParams.href += urlParams.query;
				}
				if (urlParams.target === '_self')
				{
					event.stopImmediatePropagation();
					BX.Landing.Pub.TopPanel.pushHistory(urlParams.href);
					void SliderHacks.reloadSlider(urlParams.href);
				}
				else
				{
					top.open(urlParams.href, urlParams.target);
				}
			}
		}
	}
});