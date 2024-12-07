import type { LexicalNode } from 'ui.lexical.core';
import { $isRootOrShadowRoot, type BaseSelection, type ElementNode } from 'ui.lexical.core';
import { $getAncestor } from './get-ancestor';
import { $isBlockNode } from './is-block-node';

export function $wrapNodes(selection: BaseSelection | null, createElement: () => ElementNode): ElementNode | null
{
	if (selection === null)
	{
		return null;
	}

	const anchor = selection.anchor;
	const anchorNode: ElementNode = anchor.getNode();
	const element: ElementNode = createElement();
	if ($isRootOrShadowRoot(anchorNode))
	{
		const firstChild = anchorNode.getFirstChild();
		if (firstChild)
		{
			firstChild.replace(element, true);
		}
		else
		{
			anchorNode.append(element);
		}

		return element;
	}

	const handled = new Set();
	const nodes: LexicalNode[] = selection.getNodes();
	const firstSelectedBlock = $getAncestor(selection.anchor.getNode(), $isBlockNode);
	if (firstSelectedBlock && !nodes.includes(firstSelectedBlock))
	{
		nodes.unshift(firstSelectedBlock);
	}

	handled.add(element.getKey());

	let firstNode = true;
	for (const node of nodes)
	{
		if (!$isBlockNode(node) || handled.has(node.getKey()))
		{
			continue;
		}

		const isParentHandled = $getAncestor(
			node.getParent(),
			(parentNode: LexicalNode): boolean => handled.has(parentNode.getKey()),
		);

		if (isParentHandled)
		{
			continue;
		}

		if (firstNode)
		{
			firstNode = false;
			node.replace(element);
			element.append(node);
		}
		else
		{
			element.append(node);
		}

		handled.add(node.getKey());
	}

	return element;
}
