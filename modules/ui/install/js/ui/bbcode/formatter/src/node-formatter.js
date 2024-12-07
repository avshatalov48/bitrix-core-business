import { Type } from 'main.core';
import {
	BBCodeNode,
	typeof BBCodeElementNode,
	typeof BBCodeRootNode,
	typeof BBCodeTextNode,
} from 'ui.bbcode.model';
import { type FormatterData, typeof Formatter, type FormatterElement } from './formatter';

export type ConvertCallbackOptions = {
	node: BBCodeElementNode | BBCodeRootNode | BBCodeTextNode,
	formatter: Formatter,
	data: FormatterData,
};

export type ValidateCallbackOptions = ConvertCallbackOptions & {};
export type BeforeConvertCallbackOptions = ConvertCallbackOptions & {};
export type ForChildCallbackOptions = ConvertCallbackOptions & {
	element: FormatterElement,
};
export type AfterCallbackOptions = ForChildCallbackOptions & {};
export type FormatterCallbackResult = Object | null;

export type NodeFormatterOptions = {
	name: string | Array<string>,
	convert: (ConvertCallbackOptions) => FormatterCallbackResult,
	validate?: (ValidateCallbackOptions) => boolean,
	before?: (BeforeConvertCallbackOptions) => BBCodeNode | null,
	after?: (AfterCallbackOptions) => FormatterCallbackResult,
	forChild?: (ForChildCallbackOptions) => FormatterCallbackResult,
	formatter?: Formatter,
};

type DefaultNodeConverterOptions = ConvertCallbackOptions | BeforeConvertCallbackOptions;
type DefaultElementConverterOptions = ForChildCallbackOptions | AfterCallbackOptions;

const nameSymbol: Symbol = Symbol('name');
const groupSymbol: Symbol = Symbol('group');
const validateSymbol: Symbol = Symbol('validate');
const beforeSymbol: Symbol = Symbol('before');
const convertSymbol: Symbol = Symbol('convert');
const forChildSymbol: Symbol = Symbol('forChild');
const afterSymbol: Symbol = Symbol('after');
const formatterSymbol: Symbol = Symbol('formatter');

const defaultValidator = () => true;
const defaultNodeConverter = ({ node }: DefaultNodeConverterOptions) => node;
const defaultElementConverter = ({ element }: DefaultElementConverterOptions) => element;

export class NodeFormatter
{
	[nameSymbol]: string = 'unknown';
	[groupSymbol]: ?Array<string> = null;
	[validateSymbol]: (ValidateCallbackOptions) => boolean;
	[beforeSymbol]: (BeforeConvertCallbackOptions) => FormatterCallbackResult = null;
	[convertSymbol]: (ConvertCallbackOptions) => FormatterCallbackResult = null;
	[forChildSymbol]: (ForChildCallbackOptions) => FormatterCallbackResult = null;
	[afterSymbol]: (AfterCallbackOptions) => FormatterCallbackResult = null;

	constructor(options: NodeFormatterOptions = {})
	{
		if (Type.isArray(options.name))
		{
			this[groupSymbol] = [...options.name];
		}
		else
		{
			this.setName(options.name);
		}

		if (!Type.isNil(options.formatter))
		{
			this.setFormatter(options.formatter);
		}

		this.setValidate(options.validate);
		this.setBefore(options.before);
		this.setConvert(options.convert);
		this.setForChild(options.forChild);
		this.setAfter(options.after);
	}

	setName(name: string)
	{
		if (!Type.isStringFilled(name))
		{
			throw new TypeError('Name is not a string');
		}

		this[nameSymbol] = name;
	}

	getName(): string
	{
		return this[nameSymbol];
	}

	setValidate(callback: (ValidateCallbackOptions) => boolean)
	{
		if (Type.isFunction(callback))
		{
			this[validateSymbol] = callback;
		}
		else
		{
			this[validateSymbol] = defaultValidator;
		}
	}

	validate(options: ValidateCallbackOptions): boolean
	{
		const result: ?boolean = this[validateSymbol](options);
		if (Type.isBoolean(result))
		{
			return result;
		}

		throw new TypeError(`Validate callback for "${this.getName()}" returned not boolean`);
	}

	setBefore(callback: (BeforeConvertCallbackOptions) => FormatterCallbackResult)
	{
		if (Type.isFunction(callback))
		{
			this[beforeSymbol] = callback;
		}
		else
		{
			this[beforeSymbol] = defaultNodeConverter;
		}
	}

	runBefore(options: BeforeConvertCallbackOptions): FormatterCallbackResult
	{
		return this[beforeSymbol](options);
	}

	setConvert(callback: (ConvertCallbackOptions) => FormatterCallbackResult)
	{
		if (!Type.isFunction(callback))
		{
			throw new TypeError('Convert is not a function');
		}

		this[convertSymbol] = callback;
	}

	runConvert(options: ConvertCallbackOptions): FormatterCallbackResult
	{
		return this[convertSymbol](options);
	}

	setForChild(callback: (ForChildCallbackOptions) => FormatterCallbackResult)
	{
		if (Type.isFunction(callback))
		{
			this[forChildSymbol] = callback;
		}
		else
		{
			this[forChildSymbol] = defaultElementConverter;
		}
	}

	runForChild(options: ForChildCallbackOptions): FormatterCallbackResult
	{
		return this[forChildSymbol](options);
	}

	setAfter(callback: (AfterCallbackOptions) => FormatterCallbackResult)
	{
		if (Type.isFunction(callback))
		{
			this[afterSymbol] = callback;
		}
		else
		{
			this[afterSymbol] = defaultElementConverter;
		}
	}

	runAfter(options: AfterCallbackOptions): FormatterCallbackResult
	{
		return this[afterSymbol](options);
	}

	setFormatter(formatter: Formatter)
	{
		this[formatterSymbol] = formatter;
	}

	getFormatter(): Formatter
	{
		return this[formatterSymbol];
	}
}
