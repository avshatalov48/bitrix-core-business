export default function getNodeClass(type)
{
	if (type === 'link')
	{
		return BX.Landing.Block.Node.Link;
	}

	if (type === 'img')
	{
		return BX.Landing.Block.Node.Img;
	}

	if (type === 'icon')
	{
		return BX.Landing.Block.Node.Icon;
	}

	if (type === 'embed')
	{
		return BX.Landing.Block.Node.Embed;
	}

	if (type === 'map')
	{
		return BX.Landing.Block.Node.Map;
	}

	if (type === 'component')
	{
		return BX.Landing.Block.Node.Component;
	}

	return BX.Landing.Block.Node.Text;
}