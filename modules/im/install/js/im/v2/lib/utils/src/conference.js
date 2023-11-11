import { Type } from 'main.core';
export const ConferenceUtil = {
	isValidUrl(url: string): boolean
	{
		return /^(https|http):\/\/(.*)\/video\/([\d.a-z-]+)/i.test(url);
	},

	isValidCode(code: string): boolean
	{
		return /^([\d.a-z-]+)$/i.test(code);
	},

	isCurrentPortal(url: string): boolean
	{
		if (!Type.isStringFilled(url))
		{
			return false;
		}

		const result = url.match(/^(https|http):\/\/(.*)\/video\/([\d.a-z-]+)/i);
		if (!result)
		{
			return false;
		}

		const host = result[2];

		return host.includes(location.host);
	},

	getCodeFromUrl(url: string): ?string
	{
		if (!Type.isStringFilled(url))
		{
			return null;
		}

		const result = url.match(/^(https|http):\/\/(.*)\/video\/([\d.a-z-]+)/i);
		if (!result)
		{
			return null;
		}

		const code = result[3];
		if (!Type.isStringFilled(code))
		{
			return null;
		}

		return code;
	},

	getUrlByCode(code: string): ?string
	{
		if (!this.isValidCode(code))
		{
			return null;
		}

		const origin = location.origin.replace('http://', 'https://');

		return `${origin}/video/${code}`;
	},

	getCodeByOptions(options: { code?: string, link?: string } = {}): ?string
	{
		if (Type.isStringFilled(options.link) && this.isValidUrl(options.link))
		{
			return this.getCodeFromUrl(options.link);
		}

		if (Type.isStringFilled(options.code) && this.isValidCode(options.code))
		{
			return options.code;
		}

		return null;
	},

	getWindowNameByCode(code): ?string
	{
		if (!this.isValidCode(code))
		{
			return null;
		}

		return `im-conference-${code}`;
	},
};
