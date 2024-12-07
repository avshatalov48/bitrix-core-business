import { BaseService } from './base-service';

const VK_MATCHER = /(?:(?:https?:)?\/\/)?(?:www.)?vk\.(com|ru)\/.*(video|clip)((?<oid>-?\d+)_(?<id>\d+))\/?/;
const VK_EMBEDDED = 'https://vk.com/video_ext.php?oid=$<oid>&id=$<id>&hd=2';

export class VK extends BaseService
{
	static matchByUrl(url: string): boolean
	{
		return VK_MATCHER.test(url);
	}

	static getDomains(): string[]
	{
		return [
			'vk.com',
			'vk.ru',
		];
	}

	getId(): string
	{
		return 'vk';
	}

	getDomains(): string[]
	{
		return [
			'vk.com',
		];
	}

	getMatcher(): RegExp
	{
		return VK_MATCHER;
	}

	getMatcherReplacement(): string | null
	{
		return VK_EMBEDDED;
	}
}
