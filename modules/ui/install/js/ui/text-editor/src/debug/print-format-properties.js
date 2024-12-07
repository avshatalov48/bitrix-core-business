import { type RangeSelection, type TextNode } from 'ui.lexical.core';
import { FORMAT_PREDICATES } from './constants';

export function printFormatProperties(nodeOrSelection: TextNode | RangeSelection): string
{
	let str = FORMAT_PREDICATES.map((predicate) => predicate(nodeOrSelection))
		.filter(Boolean)
		.join(', ')
		.toLocaleLowerCase();

	if (str !== '')
	{
		str = `format: ${str}`;
	}

	return str;
}
