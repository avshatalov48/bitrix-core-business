import { BBCodeNode } from 'ui.bbcode.model';

export function findParent(startingNode: BBCodeNode, findFn: (node: BBCodeNode) => boolean): BBCodeNode | null
{
	let curr: BBCodeNode | null = startingNode;
	while (curr !== null && curr.getType() !== BBCodeNode.ROOT_NODE)
	{
		if (findFn(curr))
		{
			return curr;
		}

		curr = curr.getParent();
	}

	return null;
}
