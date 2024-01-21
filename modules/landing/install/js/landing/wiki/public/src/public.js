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
	}
});