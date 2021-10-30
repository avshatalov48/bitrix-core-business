import {matcherGradient} from './is-gradient-string';
import {regexpToString} from './regexp';

export const matcherBgImage = /url\(['"]?([^ '"]*)['"]?\)([\w \/]*)/i;

export default function isBgImageString(bgImage: string)
{
	if (!!bgImage.trim().match(matcherBgImage))
	{
		return true;
	}

	return !!bgImage.trim().match(getMatcherWithOverlay());
}

function getMatcherWithOverlay(): RegExp
{
	const matcherBgString = regexpToString(matcherBgImage);
	const matcherGradientString = regexpToString(matcherGradient);
	return new RegExp(`^${matcherGradientString},${matcherBgString}`);
}

