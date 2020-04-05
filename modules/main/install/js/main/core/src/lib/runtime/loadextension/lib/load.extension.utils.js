import Type from '../../../type';

import Extension from '../load.extension.entity';
import {initialized, ajaxController} from './load.extension.constants';
import type {JsItem, Response} from '../type/load.extension.types';

export function makeIterable(value: any): Array<any>
{
	return Type.isArray(value) ? value : [value];
}

export function isInitialized(extension: string): boolean
{
	return extension in initialized;
}

export function getInitialized(extension: string): Extension
{
	return initialized[extension];
}

export function isAllInitialized(extensions: Array<string>): boolean
{
	return extensions.every(isInitialized);
}

export function loadExtensions(extensions: Array<Extension>): Promise<Array<Extension>>
{
	return Promise.all(extensions.map(item => item.load()));
}

export function mergeExports(exports: Array<Object>): {[key: string]: any}
{
	return exports.reduce((acc, currentExports) => {
		if (Type.isObject(currentExports))
		{
			return {...currentExports};
		}

		return currentExports;
	}, {});
}

export function inlineScripts(acc: Array<string>, item: JsItem)
{
	if (item.isInternal)
	{
		acc.push(item.JS);
	}

	return acc;
}

export function externalScripts(acc: Array<string>, item: JsItem)
{
	if (!item.isInternal)
	{
		acc.push(item.JS);
	}

	return acc;
}

export function externalStyles(acc: Array<string>, item: ?string)
{
	if (Type.isString(item) && item !== '')
	{
		acc.push(item);
	}

	return acc;
}

export function request(options: {extension: Array<string>}): Promise<any>
{
	return new Promise((resolve) => {
		// eslint-disable-next-line
		BX.ajax.runAction(ajaxController, {data: options}).then(resolve);
	});
}

export function prepareExtensions(response: Response)
{
	if (response.status !== 'success')
	{
		response.errors.map(console.warn);
		return [];
	}

	return response.data.map((item) => {
		const initializedExtension = (
			getInitialized(item.extension)
		);

		if (initializedExtension)
		{
			return initializedExtension;
		}

		initialized[item.extension] = new Extension(item);

		return initialized[item.extension];
	});
}

export function loadAll(items: Array<string>): Promise<void>
{
	const itemsList = makeIterable(items);

	if (!itemsList.length)
	{
		return Promise.resolve();
	}

	return new Promise((resolve) => {
		// eslint-disable-next-line
		BX.load(itemsList, resolve);
	});
}