export const ParserCommon = {

	decodeNewLine(text): string
	{
		text = text.replace(/\n/gi, '<br />');
		text = text.replace(/\[BR]/gi, '<br />');

		return text;
	},

	purifyNewLine(text, replaceSymbol = ' '): string
	{
		if (replaceSymbol !== "\n")
		{
			text = text.replace(/\n/gi, replaceSymbol);
		}
		text = text.replace(/\[BR]/gi, replaceSymbol);

		return text;
	},

	purifyBreakLine(text, replaceLetter = ' '): string
	{
		text = text.replace(/<br><br \/>/gi, '<br />');
		text = text.replace(/<br \/><br>/gi, '<br />');
		text = text.replace(/\[BR]/gi, '<br />');
		text = text.replace(/<br \/>/gi, replaceLetter);

		// text = text.replace(/<\/?[^>]+>/gi, '');

		return text;
	},

	decodeTabulation(text): string
	{
		text = text.replace(/( ){4}/gi, '\t');
		text = text.replace(/\t/gi, '&nbsp;&nbsp;&nbsp;&nbsp;');

		return text;
	},

	purifyTabulation(text): string
	{
		text = text.replace(/&nbsp;&nbsp;&nbsp;&nbsp;/gi, " ");

		return text;
	},

	purifyNbsp(text): string
	{
		text = text.replace(/&nbsp;/gi, " ");

		return text;
	},

	removeDuplicateTags(text): string
	{
		if (text.substr(-6) === '<br />')
		{
			text = text.substr(0, text.length - 6);
		}

		text = text.replace(/<br><br \/>/gi, '<br />');
		text = text.replace(/<br \/><br>/gi, '<br />');

		return text;
	},
}

