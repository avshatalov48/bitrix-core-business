import { $getSelection, $isNodeSelection, type LexicalNode } from 'ui.lexical.core';

export function getNodeInSelection(predicate: (node: LexicalNode) => boolean): LexicalNode | null
{
	const selection = $getSelection();
	if (!$isNodeSelection(selection))
	{
		return null;
	}

	const nodes = selection.getNodes();
	const node = nodes[0];

	return predicate(node) ? node : null;
}
