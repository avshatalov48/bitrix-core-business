import {Event} from 'main.core';

export default function onAnimationEnd(element: HTMLElement, animationName: string)
{
	return new Promise(((resolve) => {
		const onAnimationEndListener = (event) => {
			if (!animationName || (event.animationName === animationName))
			{
				resolve(event);
				Event.bind(element, 'animationend', onAnimationEndListener);
			}
		};

		Event.bind(element, 'animationend', onAnimationEndListener);
	}));
}