import { $isNodeSelection } from 'ui.lexical.core';
import type { BaseSelection } from 'ui.lexical.core';

export function printNodeSelection(selection: BaseSelection): string
{
	if (!$isNodeSelection(selection))
	{
		return '';
	}

	return `: node\n  └ [${[...selection._nodes].join(', ')}]`;
}
