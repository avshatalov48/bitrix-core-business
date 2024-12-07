import { type TableSelection } from 'ui.lexical.table';

export function printTableSelection(selection: TableSelection): string
{
	return `: table\n  └ { table: ${selection.tableKey}, anchorCell: ${selection.anchor.key}, focusCell: ${selection.focus.key} }`;
}
