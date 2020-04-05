import {Type, Event, Tag, Dom} from 'main.core';


export function show(element)
{
	if (!Type.isDomNode(element))
	{
		return Promise.reject(new Error('element is not Element'));
	}

	return new Promise((resolve) => {
		if (
			element.dataset.isShown === 'false'
			|| !element.dataset.isShown
		)
		{
			const handler = (event) => {
				if (event.animationName === 'showMainLoader')
				{
					Event.unbind(element, 'animationend', handler);
					resolve(event);
				}
			};

			Event.bind(element, 'animationend', handler);

			Tag.attrs(element)`
				data-is-shown: true;
			`;

			Tag.style(element)`
				display: null;
			`;

			Dom.removeClass(element, 'main-ui-hide');
			Dom.addClass(element, 'main-ui-show');
		}
	});
}

export function hide(element)
{
	if (!Type.isDomNode(element))
	{
		return Promise.reject(new Error('element is not Element'));
	}

	return new Promise((resolve) => {
		if (element.dataset.isShown === 'true')
		{
			const handler = function handler(event) {
				if (event.animationName === 'hideMainLoader')
				{
					Tag.style(element)`
						display: none;
					`;

					Event.unbind(element, 'animationend', handler);
					resolve(event);
				}
			};

			Event.bind(element, 'animationend', handler);

			Tag.attrs(element)`
				data-is-shown: false;
			`;

			Dom.removeClass(element, 'main-ui-show');
			Dom.addClass(element, 'main-ui-hide');
		}
	});
}