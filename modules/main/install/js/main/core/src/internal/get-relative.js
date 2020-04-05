import Type from '../lib/type';
import Dom from '../lib/dom';

export default function getRelative(node)
{
	if (Type.isDomNode(node))
	{
		if (
			Dom.style(node, 'position') === 'relative'
			|| Dom.style(node, 'position') === 'absolute'
		)
		{
			return node;
		}

		return getRelative(node.parentElement);
	}

	return null;
}