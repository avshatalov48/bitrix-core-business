import { Dom } from 'main.core';

export const isElementVisible = (element: HTMLElement) => {
	let current = element;

	while (current)
	{
		const style = window.getComputedStyle(current);

		if (style.display === 'none'
			|| style.visibility === 'hidden'
			|| style.opacity === '0'
		)
		{
			return false;
		}

		current = current.parentElement;
	}

	const rect = Dom.getPosition(element);

	return rect?.width > 1 && rect?.height > 1;
};
