export class BBCodeEncoder
{
	encodeText(source: string): string
	{
		return String(source)
			.replaceAll('[', '&#91;')
			.replaceAll(']', '&#93;');
	}

	decodeText(source: string): string
	{
		return String(source)
			.replaceAll('&#91;', '[')
			.replaceAll('&#93;', ']');
	}

	encodeAttribute(source: string): string
	{
		return this.encodeText(source);
	}

	decodeAttribute(source: string): string
	{
		return this.decodeText(source);
	}
}
