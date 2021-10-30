export function regexpWoStartEnd(regexp: RegExp): RegExp
{
	return new RegExp(regexpToString(regexp));
}

export function regexpToString(regexp: RegExp): string
{
	return regexp.source.replace(/(^\^)|(\$$)/g, '');
}