export default class ValueType
{
	static INTEGER = 'int';
	static STRING = 'string';

	static isValid(type: string): boolean
	{
		return type === ValueType.INTEGER || type === ValueType.STRING;
	}
}
