import Type from '../../type';
import Dom from '../../dom';
import Text from '../../text';
import Event from '../../event';
import matchers from './matchers';

type RenderNodeOptions = {
	node: any,
	parentElement: HTMLElement,
	substitutions: Array<any>,
	refs: Array<Array<string, HTMLElement | HTMLTemplateElement | SVGElement>>,
};

const appendElement = (current: HTMLElement | HTMLTemplateElement, target: HTMLElement | HTMLTemplateElement) => {
	if (Type.isDomNode(current) && Type.isDomNode(target))
	{
		if (target.nodeName !== 'TEMPLATE')
		{
			Dom.append(current, target);
		}
		else
		{
			// eslint-disable-next-line bitrix-rules/no-native-dom-methods
			target.content.append(current);
		}
	}
};

export default function renderNode(options: RenderNodeOptions): HTMLDivElement | Array<HTMLDivElement>
{
	const {node, parentElement, substitutions, refs = []} = options;

	if (node.type === 'tag')
	{
		const element = (() => {
			if (node.svg)
			{
				return document.createElementNS('http://www.w3.org/2000/svg', node.name);
			}

			return document.createElement(node.name);
		})();

		if (Object.hasOwn(node.attrs, 'ref'))
		{
			refs.push([node.attrs.ref, element]);
			delete node.attrs.ref;
		}

		Object.entries(node.attrs).forEach(([key, value]) => {
			if (key.startsWith('on') && (new RegExp(matchers.placeholder)).test(value))
			{
				const substitution = substitutions[parseInt(value.replace(/{{uid|}}/, '')) - 1];
				if (Type.isFunction(substitution))
				{
					const bindFunctionName = key.endsWith('once') ? 'bindOnce' : 'bind';
					Event[bindFunctionName](
						element,
						key.replace(/^on|once$/g, ''),
						substitution,
					);
				}
				else
				{
					element.setAttribute(key, substitution);
				}
			}
			else
			{
				if ((new RegExp(matchers.placeholder)).test(value))
				{
					const preparedValue = value.split(/{{|}}/).reduce((acc, item) => {
						if (item.startsWith('uid'))
						{
							const substitution = substitutions[parseInt(item.replace('uid', '')) - 1];
							return `${acc}${substitution}`;
						}

						return `${acc}${item}`;
					}, '');

					element.setAttribute(key, preparedValue);
				}
				else
				{
					element.setAttribute(key, Text.decode(value));
				}
			}
		});

		node.children.forEach((childNode) => {
			const result = renderNode({
				node: childNode,
				parentElement: element,
				substitutions,
				refs,
			});

			if (Type.isArray(result))
			{
				result.forEach((subChildElement) => {
					appendElement(subChildElement, element);
				});
			}
			else
			{
				appendElement(result, element);
			}
		});

		return element;
	}

	if (node.type === 'comment')
	{
		return document.createComment(node.content);
	}

	if (node.type === 'text')
	{
		if (parentElement)
		{
			if (parentElement.nodeName !== 'TEMPLATE')
			{
				parentElement.insertAdjacentHTML('beforeend', node.content);
			}
			else
			{
				parentElement.content.append(node.content);
			}
			return;
		}

		return document.createTextNode(node.content);
	}

	if (node.type === 'placeholder')
	{
		return substitutions[node.uid - 1];
	}
};