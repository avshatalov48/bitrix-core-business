import {Dom, Type} from 'main.core'

export function createSVG(elementName, config)
{
	let element = document.createElementNS('http://www.w3.org/2000/svg', elementName);

	if ("attrNS" in config && Type.isObject(config.attrNS))
	{
		for (let key in config.attrNS)
		{
			if (config.attrNS.hasOwnProperty(key))
			{
				element.setAttributeNS(null, key, config.attrNS[key]);
			}
		}
	}

	Dom.adjust(element, config);
	return element;
}