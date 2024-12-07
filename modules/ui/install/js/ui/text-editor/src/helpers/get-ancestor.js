import type { LexicalNode } from 'ui.lexical.core';

export function $getAncestor(node: LexicalNode, predicate: (ancestor: LexicalNode) => boolean): LexicalNode | null
{
	let parent: LexicalNode = node;
	while (parent !== null && parent.getParent() !== null && !predicate(parent))
	{
		parent = parent.getParentOrThrow();
	}

	return predicate(parent) ? parent : null;
}
