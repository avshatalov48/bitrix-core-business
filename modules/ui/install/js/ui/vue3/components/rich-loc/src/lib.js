import type { TemplateItem } from './types';

const escape = (str) => String(str).replaceAll(/[\\^$*+?.()|[\]{}]/g, '\\$&');

function getReplacementRegExp(placeholder: string): RegExp
{
	const closePlaceholder = `${placeholder.slice(0, 1)}/${placeholder.slice(1)}`;

	return new RegExp(`${escape(placeholder)}.*?${escape(closePlaceholder)}`, 'gmi');
}

export function getTemplateItems(text: string, placeholder: string | string[]): TemplateItem[]
{
	const items = (Array.isArray(placeholder) ? [...placeholder] : [placeholder])
		.flatMap((templatePlaceholder: string) => {
			return [...text.matchAll(getReplacementRegExp(templatePlaceholder))].map((exec) => ({
				index: exec.index,
				placeholder: templatePlaceholder,
				template: exec[0],
			}));
		});

	if (items.length > 1)
	{
		items.sort((a, b) => a.index - b.index);
	}

	return items;
}

export function unfoldTemplate(template: string, placeholder: string): string
{
	return template.slice(placeholder.length, template.length - placeholder.length - 1);
}
