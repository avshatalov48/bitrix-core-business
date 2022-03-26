export function fetchBlockImageNodes(block: BX.Landing.Block): Array<BX.Landing.Block.Node.Img>
{
	if (block)
	{
		return [...block.nodes.filter((node) => node.type === 'img')];
	}

	return [];
}