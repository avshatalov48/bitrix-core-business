import { Event, Dom } from 'main.core';

const borderWidth = 2;

export const Resize = {
	mounted(el: HTMLElement): void
	{
		Event.bind(el, 'input', () => {
			Dom.style(el, 'height', 0);
			Dom.style(el, 'height', `${el.scrollHeight + borderWidth}px`);
		});
	},
};
