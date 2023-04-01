export type ValueScheme = {
	template: string,
	inherited: 'Y' | 'N',
	lowercase: 'Y' | 'N',
	transliterate: 'Y' | 'N',
	whitespaceCharacter: string,
	isExistedAttributes: boolean,
	clearCache: 'Y' | 'N',
	hint: 'string',
}