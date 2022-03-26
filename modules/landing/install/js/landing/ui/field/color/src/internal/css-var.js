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

		if (matches[3])
		{
			const cssVarWithOpacity = '--primary-opacity-0_';
			const cssVarWithOpacity0 = '--primary-opacity-0';
			if (matches[3].startsWith(cssVarWithOpacity0) && !matches[3].startsWith(cssVarWithOpacity))
			{
				cssVar.opacity = 0;
			}
			if (matches[3].startsWith(cssVarWithOpacity))
			{
				let newOpacity = matches[3].substr(cssVarWithOpacity.length);
				if (newOpacity.length === 1 && newOpacity !== 0)
				{
					newOpacity = newOpacity / 10;
				}
				if (newOpacity.length === 2)
				{
					newOpacity = newOpacity / 100;
				}
				cssVar.opacity = newOpacity;
			}
		}
		if(matches[5])
		{
			cssVar.opacity = +parseFloat(matches[5].replace('_', '.')).toFixed(1);
		}

		return cssVar;
	}

	return null;
}
