export const ParserRecursionPrevention = {

	_startTagReplacement: [],
	_putReplacement: [],
	_sendReplacement: [],
	_codeReplacement: [],

	clean()
	{
		this._startTagReplacement = [];
		this._putReplacement = [];
		this._sendReplacement = [];
		this._codeReplacement = [];
	},

	cutStartTag(text): string
	{
		text = text.replace(/\[(.+?)]/gi, (tag) => {
			if (tag.startsWith('/'))
			{
				return tag;
			}
			const id = this._startTagReplacement.length;
			this._startTagReplacement.push(tag);
			return '####REPLACEMENT_TAG_'+id+'####';
		});

		return text;
	},

	recoverStartTag(text): string
	{
		this._startTagReplacement.forEach((tag, index) => {
			text = text.replace('####REPLACEMENT_TAG_'+index+'####', tag);
		});

		return text;
	},

	cutPutTag(text): string
	{
		text = text.replace(/\[PUT(?:=(.+?))?](.+?)?\[\/PUT]/gi, (whole) => {
			const id = this._putReplacement.length;
			this._putReplacement.push(whole);
			return '####REPLACEMENT_PUT_'+id+'####';
		});

		return text;
	},

	recoverPutTag(text): string
	{
		this._putReplacement.forEach((value, index) => {
			text = text.replace('####REPLACEMENT_PUT_'+index+'####', value);
		});

		return text;
	},

	cutSendTag(text): string
	{
		text = text.replace(/\[SEND(?:=(.+?))?](.+?)?\[\/SEND]/gi, (whole) => {
			const id = this._sendReplacement.length;
			this._sendReplacement.push(whole);
			return '####REPLACEMENT_SEND_'+id+'####';
		});

		return text;
	},

	recoverSendTag(text): string
	{
		this._sendReplacement.forEach((value, index) => {
			text = text.replace('####REPLACEMENT_SEND_'+index+'####', value);
		});

		return text;
	},

	cutCodeTag(text): string
	{
		text = text.replace(/\[CODE](<br \/>)?(.*?)\[\/CODE]/sig, (whole) => {
			const id = this._codeReplacement.length;
			this._codeReplacement.push(whole);
			return '####REPLACEMENT_CODE_'+id+'####';
		});

		return text;
	},

	recoverCodeTag(text): string
	{
		this._codeReplacement.forEach((value, index) => {
			text = text.replace('####REPLACEMENT_CODE_'+index+'####', value)
		});

		if (this._sendReplacement.length > 0)
		{
			do
			{
				this._sendReplacement.forEach((value, index) => {
					text = text.replace('####REPLACEMENT_SEND_'+index+'####', value);
				});
			}
			while (text.includes('####REPLACEMENT_SEND_'));
		}

		return text;
	},

	recoverRecursionTag(text): string
	{
		if (this._sendReplacement.length > 0)
		{
			do
			{
				this._sendReplacement.forEach((value, index) => {
					text = text.replace('####REPLACEMENT_SEND_'+index+'####', value);
				});
			}
			while (text.includes('####REPLACEMENT_SEND_'));
		}

		text = text.split('####REPLACEMENT_SP_').join('####REPLACEMENT_PUT_');

		if (this._putReplacement.length > 0)
		{
			do
			{
				this._putReplacement.forEach((value, index) => {
					text = text.replace('####REPLACEMENT_PUT_'+index+'####', value);
				});
			}
			while (text.includes('####REPLACEMENT_PUT_'));
		}

		return text;
	},
}

