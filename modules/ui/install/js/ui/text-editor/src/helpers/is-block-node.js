import { $isDecoratorNode, $isElementNode } from 'ui.lexical.core';
import type { DecoratorNode, ElementNode, LexicalNode } from 'ui.lexical.core';

export function $isBlockNode(node: ElementNode | DecoratorNode | LexicalNode): boolean
{
	return ($isElementNode(node) || $isDecoratorNode(node)) && !node.isInline() && !node.isParentRequired();
}
