import { BaseService } from './base-service';

const VIMEO_MATCHER = /^(?:(?:https?:)?\/\/)?(?:www.)?vimeo.com\/(.*\/)?(?<id>\d+)(.*)?/;
const VIMEO_EMBEDDED = 'https://player.vimeo.com/video/$<id>';

export class Vimeo extends BaseService
{
	static matchByUrl(url: string): boolean
	{
		return VIMEO_MATCHER.test(url);
	}

	static getDomains(): string[]
	{
		return [
			'vimeo.com',
			'player.vimeo.com',
		];
	}

	getId(): string
	{
		return 'vimeo';
	}

	getMatcher(): RegExp
	{
		return VIMEO_MATCHER;
	}

	getMatcherReplacement(): string | null
	{
		return VIMEO_EMBEDDED;
	}
}
