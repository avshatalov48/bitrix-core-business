import { type RangeSelection } from 'ui.lexical.core';
import { printFormatProperties } from './print-format-properties';

export function printRangeSelection(selection: RangeSelection): string
{
	let res = '';

	const formatText = printFormatProperties(selection);

	res += `: range ${formatText !== '' ? `{ ${formatText} }` : ''} ${
		selection.style !== '' ? `{ style: ${selection.style} } ` : ''
	}`;

	const anchor = selection.anchor;
	const focus = selection.focus;
	const anchorOffset = anchor.offset;
	const focusOffset = focus.offset;

	res += `\n  ├ anchor { key: ${anchor.key}, offset: ${
		anchorOffset === null ? 'null' : anchorOffset
	}, type: ${anchor.type} }`;
	res += `\n  └ focus { key: ${focus.key}, offset: ${
		focusOffset === null ? 'null' : focusOffset
	}, type: ${focus.type} }`;

	return res;
}
