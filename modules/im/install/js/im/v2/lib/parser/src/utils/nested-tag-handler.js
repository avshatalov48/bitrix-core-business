export const NestedTagHandler = {
	putReplacement: [],
	sendReplacement: [],
	codeReplacement: [],

	clean()
	{
		this.putReplacement = [];
		this.sendReplacement = [];
		this.codeReplacement = [];
	},

	cutPutTag(text: string): string
	{
		return text.replaceAll(/\[put(?:=(.+?))?](.+?)?\[\/put]/gi, (whole) => {
			const id = this.putReplacement.length;
			this.putReplacement.push(whole);

			return `####REPLACEMENT_PUT_${id}####`;
		});
	},

	recoverPutTag(text: string): string
	{
		this.putReplacement.forEach((value, index) => {
			text = text.replace(`####REPLACEMENT_PUT_${index}####`, value);
		});

		return text;
	},

	cutSendTag(text: string): string
	{
		text = text.replaceAll(/\[send(?:=(.+?))?](.+?)?\[\/send]/gi, (whole) => {
			const id = this.sendReplacement.length;
			this.sendReplacement.push(whole);

			return `####REPLACEMENT_SEND_${id}####`;
		});

		return text;
	},

	recoverSendTag(text: string): string
	{
		this.sendReplacement.forEach((value, index) => {
			const placeholder = `####REPLACEMENT_SEND_${index}####`;
			text = text.split(placeholder).join(value);
		});

		return text;
	},

	cutCodeTag(text: string): string
	{
		text = text.replaceAll(/\[code](<br \/>)?(.*?)\[\/code]/gis, (whole) => {
			const id = this.codeReplacement.length;
			this.codeReplacement.push(whole);

			return `####REPLACEMENT_CODE_${id}####`;
		});

		return text;
	},

	recoverCodeTag(text: string): string
	{
		this.codeReplacement.forEach((value, index) => {
			text = text.replace(`####REPLACEMENT_CODE_${index}####`, value);
		});

		this.sendReplacement.forEach((value, index) => {
			text = text.replaceAll(`####REPLACEMENT_SEND_${index}####`, value);
		});

		return text;
	},

	recoverRecursionTag(text: string): string
	{
		if (this.sendReplacement.length > 0)
		{
			this.sendReplacement.forEach((value, index) => {
				text = text.replaceAll(`####REPLACEMENT_SEND_${index}####`, value);
			});
		}

		text = text.split('####REPLACEMENT_SP_').join('####REPLACEMENT_PUT_');

		if (this.putReplacement.length > 0)
		{
			do
			{
				this.putReplacement.forEach((value, index) => {
					text = text.replace(`####REPLACEMENT_PUT_${index}####`, value);
				});
			}
			while (text.includes('####REPLACEMENT_PUT_'));
		}

		return text;
	},
};
