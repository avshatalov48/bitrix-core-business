import { Type } from 'main.core';

export const FOCUSABLE_ELEMENTS_SELECTOR: string = [
	'button:not([disabled])',
	'[tabindex]:not([tabindex="-1"]):not([disabled])',
].join(', ');

function isElementFocused(element: HTMLElement): boolean
{
	return element.ownerDocument.activeElement === element;
}

export function getFocusableBoundaryElements(element: HTMLElement, matcher: Function = null): HTMLElement[]
{
	const matcherFn = Type.isFunction(matcher) ? matcher : () => true;

	const elements: HTMLElement[] = (
		[...element.querySelectorAll(FOCUSABLE_ELEMENTS_SELECTOR)].filter((el: HTMLElement): boolean => {
			return el.tabIndex !== -1 && matcherFn(el);
		})
	);

	if (elements.length === 0)
	{
		return [];
	}

	if (elements.length === 1)
	{
		return [elements[0], elements[0]];
	}

	let next = elements.at(0);
	let prev = elements.at(-1);
	for (const [index, currentElement] of elements.entries())
	{
		if (isElementFocused(currentElement))
		{
			prev = index > 0 ? elements[index - 1] : elements.at(-1);
			next = Type.isUndefined(elements[index + 1]) ? elements.at(0) : elements[index + 1];

			break;
		}
	}

	return [prev, next];
}
