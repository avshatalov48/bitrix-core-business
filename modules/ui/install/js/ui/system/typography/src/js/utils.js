import { Dom, Type } from 'main.core';
import type { DisplayTitleOptions, TextOptions } from './types';

export const createTypographyElement = (
	tag: string,
	text: string,
	baseClass: string,
	size: string,
	options: DisplayTitleOptions | TextOptions = {},
): HTMLElement => {
	const element = Dom.create(tag, {
		text,
	});

	applyClasses(element, baseClass, size, options);

	return element;
};

export const applyClasses = (
	element: HTMLElement,
	baseClass: string,
	size: string,
	options: DisplayTitleOptions | TextOptions = {},
): void => {
	Dom.addClass(element, [baseClass, `--${size}`]);

	// Модификаторы
	if (options.accent)
	{
		Dom.addClass(element, '--accent');
	}

	if (options.align)
	{
		Dom.addClass(element, `--align-${options.align}`);
	}

	if (options.transform)
	{
		Dom.addClass(element, `--${options.transform}`);
	}

	if (options.wrap)
	{
		Dom.addClass(element, `--${options.wrap}`);
	}

	if (options.className)
	{
		addCustomClasses(element, options.className);
	}
};

export const addCustomClasses = (
	element: HTMLElement,
	className: string | Array<string> | { [key: string]: boolean },
): void => {
	if (Type.isString(className))
	{
		Dom.addClass(element, className);
	}
	else if (Type.isArray(className))
	{
		Dom.addClass(element, className);
	}
	else if (Type.isPlainObject(className))
	{
		Object.entries(className).forEach(([key, value]) => {
			if (value)
			{
				Dom.addClass(element, key);
			}
		});
	}
};
