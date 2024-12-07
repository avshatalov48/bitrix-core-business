import {
	$isElementNode,
	type ElementNode,
	type LexicalNode,
} from 'ui.lexical.core';

import { SYMBOLS } from './constants';

export function visitTree(
	currentNode: ElementNode,
	visitor: (node: LexicalNode, indentArr: Array<string>) => void,
	indent: Array<string> = [],
): void
{
	const childNodes = currentNode.getChildren();
	const childNodesLength = childNodes.length;

	childNodes.forEach((childNode, i) => {
		visitor(
			childNode,
			indent.concat(
				i === childNodesLength - 1
					? SYMBOLS.isLastChild
					: SYMBOLS.hasNextSibling,
			),
		);

		if ($isElementNode(childNode))
		{
			visitTree(
				childNode,
				visitor,
				indent.concat(
					i === childNodesLength - 1
						? SYMBOLS.ancestorIsLastChild
						: SYMBOLS.ancestorHasNextSibling,
				),
			);
		}
	});
}
