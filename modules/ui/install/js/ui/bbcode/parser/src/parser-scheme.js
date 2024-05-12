import { BBCodeScheme, BBCodeTagScheme, BBCodeNode } from 'ui.bbcode.model';
import type { BBCodeContentNode } from 'ui.bbcode.model';

export class ParserScheme extends BBCodeScheme
{
	getTagScheme(tagName: string): BBCodeTagScheme
	{
		if (tagName === 'code')
		{
			return new BBCodeTagScheme({
				name: 'code',
				convertChild: (child: BBCodeContentNode, scheme: BBCodeScheme): BBCodeContentNode => {
					if (['#linebreak', '#tab', '#text'].includes(child.getName()))
					{
						return child;
					}

					return scheme.createText(child.toString());
				},
			});
		}

		return new BBCodeTagScheme({
			name: 'any',
		});
	}

	isAllowedTag(tagName: string): boolean
	{
		return true;
	}

	isChildAllowed(parent: string | BBCodeNode, child: string | BBCodeNode): boolean
	{
		return true;
	}
}
