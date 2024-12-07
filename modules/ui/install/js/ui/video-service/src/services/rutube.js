import { BaseService } from './base-service';

const RUTUBE_MATCHER = /(?:(?:https?:)?\/\/)?(?:www.)?rutube\.ru\/video\/(private\/)?(?<id>[\dA-Za-z]+)\/?/;
const RUTUBE_EMBEDDED = 'https://rutube.ru/play/embed/$<id>';

export class Rutube extends BaseService
{
	static matchByUrl(url: string): boolean
	{
		return RUTUBE_MATCHER.test(url);
	}

	static getDomains(): string[]
	{
		return [
			'rutube.ru',
			'www.rutube.ru',
		];
	}

	getId(): string
	{
		return 'rutube';
	}

	getMatcher(): RegExp
	{
		return RUTUBE_MATCHER;
	}

	getMatcherReplacement(): string | null
	{
		return RUTUBE_EMBEDDED;
	}
}
