import {Reflection, Runtime} from 'main.core';
import defaultOptions from './internal/default-options';
import type {EnvOptions} from './types/env.options.types';

const optionsKey = Symbol('options');

/**
 * @memberOf BX.Landing
 */
export class Env
{
	static instance = null;

	static getInstance(): Env
	{
		return Env.instance || Env.createInstance();
	}

	static createInstance(options: EnvOptions = {}): Env
	{
		Env.instance = new Env(options);

		const parentEnv = Reflection.getClass('parent.BX.Landing.Env');
		if (parentEnv)
		{
			parentEnv.instance = Env.instance;
		}

		return Env.instance;
	}

	constructor(options: EnvOptions = {})
	{
		this[optionsKey] = Object.seal(
			Runtime.merge(defaultOptions, options),
		);
	}

	getOptions(): EnvOptions
	{
		return {...this[optionsKey]};
	}

	setOptions(options: {[key: string]: any})
	{
		this[optionsKey] = Runtime.merge(this[optionsKey], options);
	}

	getType(): string
	{
		return this.getOptions().params.type;
	}

	setType(type: string): void
	{
		this.getOptions().params.type = type;
	}

	getSpecialType(): string
	{
		return this.getOptions().specialType;
	}

	getSiteId(): number
	{
		return this.getOptions().site_id || -1;
	}

	getLandingEditorUrl(options: {site?: number, landing: number} = {}): string
	{
		const envOptions = this.getOptions();
		const urlMask = envOptions.params.sef_url.landing_view;

		const siteId = options.site ? options.site : envOptions.site_id;

		return urlMask
			.replace('#site_show#', siteId)
			.replace('#landing_edit#', options.landing);
	}
}