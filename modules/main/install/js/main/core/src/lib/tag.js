import Type from './type';
import Dom from './dom';
import Loc from './loc';
import Text from './text';
import render from './tag/render';

function parseProps(sections: string[], ...substitutions: string[]): {[key: string]: string}
{
	return substitutions
		.reduce((acc, item, index) => {
			const nextSectionIndex = index + 1;

			if (!Type.isPlainObject(item) && !Type.isArray(item))
			{
				return acc + item + sections[nextSectionIndex];
			}

			return `${acc}__s${index}${sections[nextSectionIndex]}`;
		}, sections[0])
		.replace(/[\r\t]/gm, '')
		.split(';\n')
		.map(item => item.replace(/\n/, ''))
		.reduce((acc, item) => {
			if (item !== '')
			{
				const matches = item.match(/^[\w-. ]+:/);
				const splitted = item.split(/^[\w-. ]+:/);
				const key = matches[0].replace(':', '').trim();
				const value = splitted[1].trim();
				const substitutionPlaceholderExp = /^__s\d+/;

				if (substitutionPlaceholderExp.test(value))
				{
					acc[key] = substitutions[value.replace('__s', '')];
					return acc;
				}

				acc[key] = value;
			}

			return acc;
		}, {});
}

/**
 * @memberOf BX
 */
export default class Tag
{
	/**
	 * Encodes all substitutions
	 * @param sections
	 * @param substitutions
	 * @return {string}
	 */
	static safe(sections: string[], ...substitutions: string[])
	{
		return substitutions.reduce((acc, item, index) => (
			acc + Text.encode(item) + sections[index + 1]
		), sections[0]);
	}

	/**
	 * Decodes all substitutions
	 * @param sections
	 * @param substitutions
	 * @return {string}
	 */
	static unsafe(sections, ...substitutions)
	{
		return substitutions.reduce((acc, item, index) => (
			acc + Text.decode(item) + sections[index + 1]
		), sections[0]);
	}

	/**
	 * Adds styles to specified element
	 * @param {HTMLElement} element
	 * @return {Function}
	 */
	static style(element: HTMLElement): Function
	{
		if (!Type.isDomNode(element))
		{
			throw new Error('element is not HTMLElement');
		}

		return function styleTagHandler(...args) {
			Dom.style(element, parseProps(...args));
		};
	}

	/**
	 * Replace all messages identifiers to real messages
	 * @param sections
	 * @param substitutions
	 * @return {string}
	 */
	static message(sections: string[], ...substitutions: string[]): string
	{
		return substitutions.reduce((acc, item, index) => (
			acc + Loc.getMessage(item) + sections[index + 1]
		), sections[0]);
	}

	static render = render;

	/**
	 * Adds attributes to specified element
	 * @param element
	 * @return {Function}
	 */
	static attrs(element: HTMLElement)
	{
		if (!Type.isDomNode(element))
		{
			throw new Error('element is not HTMLElement');
		}

		return function attrsTagHandler(...args): string {
			Dom.attr(element, parseProps(...args));
		};
	}

	static attr = Tag.attrs;
}