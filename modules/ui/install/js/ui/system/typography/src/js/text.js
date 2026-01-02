import { createTypographyElement } from './utils';

import { TextOptions } from './types';

export class Text
{
	static render(text: string, options: TextOptions = {}): HTMLElement {
		return Text.#render(text, options);
	}

	static #render(text: string, options: TextOptions = {}): HTMLElement {
		const tag = options.tag || 'span';
		const size = options.size ?? 'md';

		return createTypographyElement(tag, text, 'ui-text', size, options);
	}
}
