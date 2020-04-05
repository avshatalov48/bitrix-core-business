import Type from '../type';
import Dom from '../dom';
import Event from '../event';

type renderResult = HTMLDocument | HTMLElement | Element;

const handlers = new Map();
const children = new Map();
const getUid = (() => {
	let incremental = 0;
	return () => {
		incremental += 1;
		return incremental;
	};
})();

function bindAll(element, handlersMap: Map)
{
	handlersMap.forEach((handler, key) => {
		const currentElement = element.querySelector(`[${key}]`);

		if (currentElement)
		{
			currentElement.removeAttribute(key);
			const event = key.replace(/-(.*)/, '');
			Event.bind(currentElement, event, handler);
			handlers.delete(key);
		}
	});
}

function replaceChild(element, childrenMap: Map)
{
	childrenMap.forEach((item, id) => {
		const currentElement = element.getElementById(id);

		if (currentElement)
		{
			Dom.replace(currentElement, item);
			children.delete(id);
		}
	});
}

export default function render(sections: string[], ...substitutions: string[]): renderResult
{
	const eventAttrRe = /[ |\t]on(\w+)="$/;
	const uselessSymbolsRe = /[\r\n\t]/g;

	const html = substitutions
		.reduce((acc, item, index) => {
			let preparedAcc = acc;

			// Process event handlers
			const matches = acc.match(eventAttrRe);

			if (matches && Type.isFunction(item))
			{
				const eventName = matches[1].replace(/=['|"]/, '');
				const attrName = `${eventName}-${getUid()}`;
				const attribute = `${attrName}="`;
				preparedAcc = preparedAcc.replace(eventAttrRe, ` ${attribute}`);
				handlers.set(attrName, item);
				preparedAcc += (
					sections[index + 1]
						.replace(uselessSymbolsRe, ' ')
						.replace(/  +/g, ' ')
				);
				return preparedAcc;
			}

			// Process element
			if (Type.isDomNode(item))
			{
				const childKey = `tmp___${getUid()}`;
				children.set(childKey, item);
				preparedAcc += `<span id="${childKey}"> </span>`;
				preparedAcc += sections[index + 1];
				return preparedAcc;
			}

			// Process array
			if (Type.isArray(item))
			{
				[...item].forEach((currentElement) => {
					if (Type.isDomNode(currentElement))
					{
						const childKey = `tmp___${getUid()}`;
						children.set(childKey, currentElement);
						preparedAcc += `<span id="${childKey}"> </span>`;
					}
				});

				preparedAcc += sections[index + 1];

				return preparedAcc;
			}

			return preparedAcc + item + sections[index + 1];
		}, sections[0]);

	const lowercaseHtml = html.trim().toLowerCase();
	if (
		lowercaseHtml.startsWith('<!doctype')
		|| lowercaseHtml.startsWith('<html')
	)
	{
		const doc = document.implementation.createHTMLDocument('');
		doc.documentElement.innerHTML = html;
		replaceChild(doc, children);
		bindAll(doc, handlers);
		handlers.clear();
		return doc;
	}

	const parser = new DOMParser();
	const parsedDocument = parser.parseFromString(html, 'text/html');

	replaceChild(parsedDocument, children);
	bindAll(parsedDocument, handlers);

	if (
		parsedDocument.head.children.length
		&& parsedDocument.body.children.length
	)
	{
		return parsedDocument;
	}

	if (parsedDocument.body.children.length === 1)
	{
		const [el] = parsedDocument.body.children;
		Dom.remove(el);
		return el;
	}

	if (parsedDocument.body.children.length > 1)
	{
		return [...parsedDocument.body.children].map((item) => {
			Dom.remove(item);
			return item;
		});
	}

	if (parsedDocument.body.children.length === 0)
	{
		if (parsedDocument.head.children.length === 1)
		{
			const [el] = parsedDocument.head.children;
			Dom.remove(el);
			return el;
		}

		if (parsedDocument.head.children.length > 1)
		{
			return [...parsedDocument.head.children].map((item) => {
				Dom.remove(item);
				return item;
			});
		}
	}

	return false;
}