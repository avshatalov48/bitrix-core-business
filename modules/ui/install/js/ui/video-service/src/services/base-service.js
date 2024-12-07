import { Type } from 'main.core';

export interface ServiceStaticMembers {
	matchByUrl(url: string): boolean;
	getDomains(): string[];
}

export interface ServiceInterface {}
export type ServiceClassConstructor = () => ServiceInterface;
export type ServiceConstructor = ServiceClassConstructor & ServiceStaticMembers;

export class BaseService implements ServiceStaticMembers
{
	#url: string = null;

	constructor(url: string)
	{
		this.#url = url;
	}

	static matchByUrl(url: string): boolean
	{
		return false;
	}

	static getDomains(): string[]
	{
		return [];
	}

	getId(): string | null
	{
		return null;
	}

	getMatcher(): RegExp
	{
		return /^$/;
	}

	getMatcherReplacement(): string | Function | null
	{
		return null;
	}

	getEmbeddedUrl(): string
	{
		const replacement = this.getMatcherReplacement();
		if (Type.isStringFilled(replacement) || Type.isFunction(replacement))
		{
			return this.getUrl().replace(this.getMatcher(), replacement);
		}

		return '';
	}

	getUrl(): string
	{
		return this.#url;
	}
}
