import { BBCodeElementNode, BBCodeNode, BBCodeScheme } from 'ui.bbcode.model';
import { createEmptyParagraphs } from './create-empty-paragraphs';
import { shouldWrapInParagraph } from './should-wrap-in-paragraph';

export function wrapTextNodes(nodes: Array<BBCodeNode>, scheme: BBCodeScheme): Array<BBCodeElementNode>
{
	const result = [];
	let currentParagraph = null;
	let lineBreaks = 0;

	for (const node of nodes)
	{
		if (scheme.isNewLine(node))
		{
			lineBreaks++;

			continue;
		}

		if (shouldWrapInParagraph(node))
		{
			if (currentParagraph === null || lineBreaks >= 2)
			{
				result.push(...createEmptyParagraphs(scheme, lineBreaks - 2));
				currentParagraph = scheme.createElement({ name: 'p' });
				result.push(currentParagraph);
			}
			else if (lineBreaks === 1)
			{
				currentParagraph.appendChild(scheme.createNewLine());
			}

			currentParagraph.appendChild(node);
		}
		else
		{
			if (lineBreaks > 2)
			{
				result.push(...createEmptyParagraphs(scheme, lineBreaks - 2));
			}

			result.push(node);
			currentParagraph = null;
		}

		lineBreaks = 0;
	}

	// to avoid a height collapsing for empty elements
	if (result.length === 0)
	{
		return [scheme.createElement({ name: 'p' })];
	}

	return result;
}
