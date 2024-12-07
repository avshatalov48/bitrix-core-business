import { BaseService } from './base-service';

const FACEBOOK_MATCHER = /^(?:(?:https?:)?\/\/)?(?:www.)?facebook\.com.*\/(videos?|watch)(\.php|\/|\?).+$/;

export class Facebook extends BaseService
{
	static matchByUrl(url: string): boolean
	{
		return FACEBOOK_MATCHER.test(url);
	}

	static getDomains(): string[]
	{
		return [
			'facebook.com',
			'www.facebook.com',
		];
	}

	getId(): string
	{
		return 'facebook';
	}

	getMatcher(): RegExp
	{
		return FACEBOOK_MATCHER;
	}

	getEmbeddedUrl(): string
	{
		const encodedUrl = encodeURIComponent(this.getUrl().replace(/\/$/, ''));

		return `https://www.facebook.com/plugins/video.php?href=${encodedUrl}`;
	}
}
