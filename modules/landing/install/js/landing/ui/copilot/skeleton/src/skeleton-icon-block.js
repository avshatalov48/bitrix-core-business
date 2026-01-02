import { Dom } from 'main.core';
import { SkeletonBlock } from './skeleton-block';
import { wait } from './helpers/wait';

import './css/skeleton-icon-block.css';

export type SkeletonIconBlockOptions = {
	iconElement: HTMLElement;
};

export class SkeletonIconBlock extends SkeletonBlock
{
	#iconElement: HTMLElement | null = null;
	#iconFontawesomeClassNames: string = '';
	#iconStubClassNames: string[] = ['fa', 'fas', 'fa-stars'];

	constructor(options: SkeletonIconBlockOptions) {
		super(options);

		this.#iconElement = options.iconElement;
		this.#iconFontawesomeClassNames = this.#getFontawesomeClassNamesFromIcon(this.#iconElement);
	}

	async show(): void
	{
		await this.#replaceStubWithIcon();
	}

	hide(): void
	{
		this.#removeClassNames(this.#iconElement, this.#iconFontawesomeClassNames);
		this.#addClassNames(this.#iconElement, this.#iconStubClassNames);
	}

	async #replaceStubWithIcon(): void
	{
		Dom.style(this.#iconElement, {
			transition: '0.2s ease-in-out opacity',
			opacity: 0,
		});

		await wait(200);

		this.#removeClassNames(this.#iconElement, this.#iconStubClassNames);
		this.#addClassNames(this.#iconElement, this.#iconFontawesomeClassNames);

		Dom.style(this.#iconElement, {
			opacity: 1,
		});

		await wait(200);

		Dom.style(this.#iconElement, {
			transition: null,
			opacity: null,
		});
	}

	#getFontawesomeClassNamesFromIcon(iconElement: HTMLElement): ?string[]
	{
		return [...iconElement.classList].filter((classname: string) => classname.startsWith('fa'));
	}

	#addClassNames(element: HTMLElement, classNames: string[])
	{
		classNames.forEach((className: string) => {
			Dom.addClass(element, className);
		});
	}

	#removeClassNames(element: HTMLElement, classNames: string[])
	{
		classNames.forEach((className: string) => {
			Dom.removeClass(element, className);
		});
	}
}
