export default function getNodeClass(type)
{
	if (type === 'link')
	{
		return BX.Landing.Node.Link;
	}

	if (type === 'img')
	{
		return BX.Landing.Node.Img;
	}

	if (type === 'icon')
	{
		return BX.Landing.Node.Icon;
	}

	if (type === 'embed')
	{
		return BX.Landing.Node.Embed;
	}

	if (type === 'map')
	{
		return BX.Landing.Node.Map;
	}

	if (type === 'component')
	{
		return BX.Landing.Node.Component;
	}

	return BX.Landing.Node.Text;
}