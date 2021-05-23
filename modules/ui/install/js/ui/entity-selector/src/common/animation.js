import { Event, Type } from 'main.core';

export default class Animation
{
	static handleTransitionEnd(element: HTMLElement, propertyName: string | string[]): Promise
	{
		const properties = Type.isArray(propertyName) ? new Set(propertyName) : new Set([propertyName]);

		return new Promise(function(resolve) {
			const handler = (event: TransitionEvent) => {
				if (event.target !== element || !properties.has(event.propertyName))
				{
					return;
				}

				properties.delete(event.propertyName);
				if (properties.size === 0)
				{
					resolve(event);
					Event.unbind(element, 'transitionend', handler);
				}
			};

			Event.bind(element, 'transitionend', handler);
		});
	}

	static handleAnimationEnd(element: HTMLElement, animationName: string)
	{
		return new Promise(resolve => {
			const handler = (event) => {
				if (!animationName || (event.animationName === animationName))
				{
					resolve(event);
					Event.unbind(element, 'animationend', handler);
				}
			};

			Event.bind(element, 'animationend', handler);
		});
	}
}