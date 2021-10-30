const matcher = /^(var\()?((--[\w\d-]*?)(-opacity_([\d_]+)?)?)\)?$/i;

export function isCssVar(css: string): boolean
{
	return !!css.trim().match(matcher);
}

type cssVar = {
	full: string,
	name: string,
	opacity: number,
};

export function parseCssVar(css: string): ?cssVar
{
	const matches = css.trim().match(matcher);
	if (!!matches)
	{
		const cssVar = {
			full: matches[2],
			name: matches[3],
		};

		if(matches[5])
		{
			cssVar.opacity = +parseFloat(matches[5].replace('_', '.')).toFixed(1);
		}

		return cssVar;
	}

	return null;
}
