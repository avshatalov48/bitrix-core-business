import { createTypographyElement } from './utils';

import { DisplayTitleOptions } from './types';

export class Headline
{
	static #getTag(size: string, customTag?: ?string): string
	{
		return customTag || 'div';
	}

	static #render(text: string, options: DisplayTitleOptions = {}): HTMLElement
	{
		const tag = Headline.#getTag(options.size, options.tag);

		return createTypographyElement(tag, text, 'ui-headline', options.size, options);
	}

	static render(text: string, options: DisplayTitleOptions = {}): HTMLElement
	{
		return Headline.#render(text, options);
	}
}
