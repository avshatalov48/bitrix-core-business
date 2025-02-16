export function wrapTextInParagraph(text: string): string
{
	let result = '';
	const parts = text.split(/((?:\r?\n){2})/);

	for (const part of parts)
	{
		if (part === '\n\n' || part === '\r\n\r\n')
		{
			continue;
		}

		result += `<p>${part.replaceAll(/(\r?\n)/g, '<br>')}</p>`;
	}

	return result;
}
