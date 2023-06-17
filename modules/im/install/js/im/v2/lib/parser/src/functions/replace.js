export const ParserReplace = {
	decode(text, replace = []): string
	{
		if (replace.length === 0)
		{
			return text;
		}

		const replacedText = replace.reduce((replacedText, item) => {
			return this.replaceDate(replacedText, item);
		}, text);

		return replacedText;
	},

	replaceDate(text, item): string
	{
		const originalText = text.substring(item.start, item.end);
		if (originalText !== item.text)
		{
			return text;
		}

		const left = text.substring(0, item.start);
		const right = text.substring(item.end);

		return left + '[DATE='+item.value+']'+originalText+'[/DATE]' + right;
	}
}