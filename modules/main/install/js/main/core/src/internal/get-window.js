import Type from '../lib/type';

export default function getWindow(element)
{
	if (Type.isElementNode(element))
	{
		return (
			element.ownerDocument.parentWindow
			|| element.ownerDocument.defaultView
			|| window
		);
	}

	if (Type.isDomNode(element))
	{
		return (
			element.parentWindow
			|| element.defaultView
			|| window
		);
	}

	return window;
}