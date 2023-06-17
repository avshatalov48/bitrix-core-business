

export class Loc
{
	static messages: {[type: string]: string};

	static loadMessages(messages: {[type: string]: string})
	{
		Loc.messages = messages;
	}

	static getMessage(code: string): string
	{
		return Loc.messages[code];
	}
}
