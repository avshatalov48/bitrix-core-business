import Type from '../../type';
import isVoidElement from './is-void-element';
import matchers from './matchers';

type TagResult = {
	type: 'tag' | 'comment',
	name?: string,
	svg?: boolean,
	attrs?: {[key: string]: any},
	children?: Array<TagResult>,
	voidElement: boolean,
	content?: string,
};

export default function parseTag(tag: string): TagResult
{
	const tagResult: TagResult = {
		type: 'tag',
		name: '',
		svg: false,
		attrs: {},
		children: [],
		voidElement: false,
	}

	if (tag.startsWith('<!--'))
	{
		const endIndex = tag.indexOf('-->');
		const openTagLength = '<!--'.length;
		return {
			type: 'comment',
			content: endIndex !== -1 ? tag.slice(openTagLength, endIndex) : '',
		};
	}

	const tagNameMatch = tag.match(matchers.tagName);
	if (Type.isArrayFilled(tagNameMatch))
	{
		const [, tagName] = tagNameMatch;
		tagResult.name = tagName;
		tagResult.svg = tagName === 'svg';
		tagResult.voidElement = isVoidElement(tagName) || tag.trim().endsWith('/>');
	}

	const reg = new RegExp(matchers.attributes);
	for (;;)
	{
		const result = reg.exec(tag);
		if (!Type.isNil(result))
		{
			// Attributes with double quotes
			const [, attrName, attrValue] = result;
			if (!Type.isNil(attrName))
			{
				tagResult.attrs[attrName] = Type.isStringFilled(attrValue) ? attrValue : '';
			}
			else
			{
				// Attributes with single quotes
				const [,,, attrName, attrValue] = result;
				if (!Type.isNil(attrName))
				{
					tagResult.attrs[attrName] = Type.isStringFilled(attrValue) ? attrValue : '';
				}
				else
				{
					// Attributes without value
					const [,,,,, attrName] = result;
					tagResult.attrs[attrName] = '';
				}
			}
		}
		else
		{
			break;
		}
	}

	return tagResult;
}