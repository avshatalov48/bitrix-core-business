import { BaseService } from './base-service';

const INSTAGRAM_MATCHER = /(?:(?:https?:)?\/\/)?(?:www.)?(instagr\.am|instagram\.com)\/p\/(?<id>[\w-]+)\/?/;
const INSTAGRAM_EMBEDDED = 'https://instagram.com/p/$<id>/embed/captioned';

export class Instagram extends BaseService
{
	static matchByUrl(url: string): boolean
	{
		return INSTAGRAM_MATCHER.test(url);
	}

	static getDomains(): string[]
	{
		return [
			'www.instagram.com',
			'instagram.com',
			'instagr.am',
		];
	}

	getId(): string
	{
		return 'instagram';
	}

	getMatcher(): RegExp
	{
		return INSTAGRAM_MATCHER;
	}

	getMatcherReplacement(): string | null
	{
		return INSTAGRAM_EMBEDDED;
	}
}
