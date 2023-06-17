export const ParserSlashCommand = {

	decode(text): string
	{
		if (text.startsWith('/me'))
		{
			return `[i]${text.substr(4)}[/i]`;
		}

		if (text.startsWith('/loud'))
		{
			return `[size=20]${text.substr(6)}[/size]`;
		}

		return text;
	},

	purify(text): string
	{
		if (text.startsWith('/me'))
		{
			return  text.substr(4);
		}

		if (text.startsWith('/loud'))
		{
			return  text.substr(6);
		}

		return text;
	}
}