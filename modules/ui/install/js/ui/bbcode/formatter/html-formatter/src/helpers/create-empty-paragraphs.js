import { BBCodeElementNode, BBCodeScheme } from 'ui.bbcode.model';

export function createEmptyParagraphs(scheme: BBCodeScheme, count: number = 1): Array<BBCodeElementNode>
{
	const result = [];
	for (let i = 0; i < count; i++)
	{
		result.push(scheme.createElement({ name: 'p' }));
	}

	return result;
}
