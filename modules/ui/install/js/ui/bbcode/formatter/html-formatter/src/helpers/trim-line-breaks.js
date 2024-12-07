import { type BBCodeNode } from 'ui.bbcode.model';

export function trimLineBreaks(nodes: Array<BBCodeNode>): Array<BBCodeNode>
{
	const trimmedNodes = [...nodes];
	const firstNode: BBCodeNode = trimmedNodes[0];
	const lastNode: BBCodeNode = trimmedNodes[trimmedNodes.length - 1];
	if (isLineBreakNode(firstNode) || (isParagraphNode(firstNode) && firstNode.isEmpty()))
	{
		trimmedNodes.splice(0, 1);
	}

	if (isLineBreakNode(lastNode) || (isParagraphNode(lastNode) && lastNode.isEmpty()))
	{
		trimmedNodes.splice(-1, 1);
	}

	return trimmedNodes;
}

function isLineBreakNode(node: BBCodeNode): boolean
{
	return node && node.getScheme().isNewLine(node);
}

function isParagraphNode(node: BBCodeNode): boolean
{
	return node && node.getName() === 'p';
}
