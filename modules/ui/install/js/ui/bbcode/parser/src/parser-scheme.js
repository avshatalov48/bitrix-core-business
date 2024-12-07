import { BBCodeScheme, BBCodeTagScheme, BBCodeNode } from 'ui.bbcode.model';

export class ParserScheme extends BBCodeScheme
{
	getTagScheme(tagName: string): BBCodeTagScheme
	{
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
