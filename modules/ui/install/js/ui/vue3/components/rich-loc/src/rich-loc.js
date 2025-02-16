import { Type } from 'main.core';
import { h } from 'ui.vue3';
import { getTemplateItems, unfoldTemplate } from './lib';
import type { TemplateItem } from './types';

function makeRichLocChildren(text: string, templateItems: TemplateItem[], context): Array<string | Object>
{
	const children = [];
	let index = 0;
	for (const item of templateItems)
	{
		if (item.index > index)
		{
			children.push(text.slice(index, item.index));
			index = item.index;
		}

		if (item.index === index)
		{
			const placeholder = item.placeholder;
			const slotName = placeholder.slice(1, -1);
			if (Type.isFunction(context.slots[slotName]))
			{
				children.push(context.slots[slotName]({
					text: unfoldTemplate(item.template, placeholder),
				}));
			}

			index += item.template.length;
		}
	}

	if (index < text.length)
	{
		children.push(text.slice(index));
	}

	return children;
}

function RichLoc(props: RichLocProps, context): Object
{
	const templateItems: TemplateItem[] = getTemplateItems(props.text, props.placeholder);
	const children = makeRichLocChildren(props.text, templateItems, context);

	return h(
		props.tag || 'div',
		{
			...context.attrs,
		},
		children,
	);
}

type RichLocProps = {
	text: string;
	placeholder: string | string[];
	tag: ?string | string[];
}

const richLocProps: Array<$Keys<RichLocProps>> = ['text', 'placeholder', 'tag'];

RichLoc.props = richLocProps;

export { RichLoc };
