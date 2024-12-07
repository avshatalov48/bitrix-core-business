import { Type } from 'main.core';

// eslint-disable-next-line no-control-regex
const ATTRIBUTE_WHITESPACES = /[\u0000-\u0020\u00A0\u1680\u180E\u2000-\u2029\u205F\u3000]/g;
const SAFE_URL = /^(?:(?:https?|ftps?|mailto):|[^a-z]|[+.a-z-]+(?:[^+.:a-z-]|$))/i;

export function sanitizeUrl(url: string): string
{
	if (!Type.isStringFilled(url))
	{
		return '';
	}

	const normalizedUrl = url.replaceAll(ATTRIBUTE_WHITESPACES, '');

	return SAFE_URL.test(normalizedUrl) ? normalizedUrl : '';
}
