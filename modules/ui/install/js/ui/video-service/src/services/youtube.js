import { BaseService } from './base-service';

const YOUTUBE_MATCHER = /^((?:https?:)?\/\/)?((?:www|m)\.)?(youtube(-nocookie)?\.com|youtu\.be)(\/(?:[\w-]+\?v=|embed\/|shorts\/|live\/|v\/)?)(?<id>[\w-]+)(\S+)?$/;
const YOUTUBE_EMBEDDED = 'https://www.youtube-nocookie.com/embed/$<id>';

export class Youtube extends BaseService
{
	static matchByUrl(url: string): boolean
	{
		return YOUTUBE_MATCHER.test(url);
	}

	static getDomains(): string[]
	{
		return [
			'youtube.com',
			'youtu.be',
			'youtube-nocookie.com',
			'www.youtube-nocookie.com',
		];
	}

	getId(): string
	{
		return 'youtube';
	}

	getMatcher(): RegExp
	{
		return YOUTUBE_MATCHER;
	}

	getMatcherReplacement(): string | null
	{
		return YOUTUBE_EMBEDDED;
	}
}
