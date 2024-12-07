import { Type } from 'main.core';
import { BBCodeFragmentNode, BBCodeNode, BBCodeScheme } from 'ui.bbcode.model';
import { BBCodeParser } from 'ui.bbcode.parser';
import { NodeFormatter, type BeforeConvertCallbackOptions } from './node-formatter';

export * from './node-formatter';

export type FormatterData = {
	[key: string]: any,
};

export type UnknownNodeCallbackOptions = {
	node: BBCodeNode,
	formatter: Formatter,
	data: FormatterData,
};

export type FormatterOptions = {
	formatters: Array<NodeFormatter>,
	onUnknown: (UnknownNodeCallbackOptions) => BBCodeNode | null,
};

export type FormatterFormatOptions = {
	source: BBCodeNode | string,
	data?: FormatterData,
};

export interface FormatterElement
{
	appendChild(): void
}

const formattersSymbol: Symbol = Symbol('formatters');
const onUnknownSymbol: Symbol = Symbol('onUnknown');
const dataSymbol: Symbol = Symbol('data');

/**
 * @memberOf BX.UI.BBCode
 */
export class Formatter
{
	[formattersSymbol]: Map<any, any> = new Map();
	[onUnknownSymbol]: (UnknownNodeCallbackOptions) => NodeFormatter | null = null;
	[dataSymbol]: FormatterData | null = null;

	constructor(options: FormatterOptions = {})
	{
		this.setNodeFormatters(options.formatters);
		if (Type.isNil(options.onUnknown))
		{
			this.setOnUnknown(this.getDefaultUnknownNodeCallback());
		}
		else
		{
			this.setOnUnknown(options.onUnknown);
		}
	}

	isElement(source): boolean
	{
		return Type.isObject(source) && Type.isFunction(source.appendChild);
	}

	static prepareSourceNode(source: BBCodeNode | string): BBCodeNode | null
	{
		if (source instanceof BBCodeNode)
		{
			return source;
		}

		if (Type.isString(source))
		{
			return (new BBCodeParser()).parse(source);
		}

		return null;
	}

	setData(data: FormatterData)
	{
		this[dataSymbol] = data;
	}

	getData(): FormatterData
	{
		return this[dataSymbol];
	}

	setNodeFormatters(formatters: Array<NodeFormatter>)
	{
		if (Type.isArrayFilled(formatters))
		{
			formatters.forEach((formatter: NodeFormatter) => {
				this.setNodeFormatter(formatter);
			});
		}
	}

	setNodeFormatter(formatter: NodeFormatter)
	{
		if (formatter instanceof NodeFormatter)
		{
			this[formattersSymbol].set(formatter.getName(), formatter);
		}
		else
		{
			throw new TypeError('formatter is not a NodeFormatter instance.');
		}
	}

	getDefaultUnknownNodeCallback(): (UnknownNodeCallbackOptions) => NodeFormatter | null
	{
		throw new TypeError('Must be implemented in subclass');
	}

	setOnUnknown(callback: (UnknownNodeCallbackOptions) => NodeFormatter | null)
	{
		if (Type.isFunction(callback))
		{
			this[onUnknownSymbol] = callback;
		}
		else
		{
			throw new TypeError('OnUnknown callback is not a function.');
		}
	}

	runOnUnknown(options: UnknownNodeCallbackOptions): NodeFormatter | null
	{
		const result: NodeFormatter | null = this[onUnknownSymbol](options);
		if (result instanceof NodeFormatter || Type.isNull(result))
		{
			return result;
		}

		throw new TypeError('OnUnknown callback returned not NodeFormatter instance or null.');
	}

	getNodeFormatter(node: BBCodeNode): NodeFormatter | null
	{
		const formatter: ?NodeFormatter = this[formattersSymbol].get(node.getName());
		if (formatter instanceof NodeFormatter)
		{
			return formatter;
		}

		return this.runOnUnknown({ node, formatter: this });
	}

	getNodeFormatters(): Array<NodeFormatter>
	{
		return this[formattersSymbol];
	}

	format(options: FormatterFormatOptions): DocumentFragment | HTMLElement | Text | null
	{
		if (!Type.isPlainObject(options))
		{
			throw new TypeError('options is not a object');
		}

		const { source, data = {} } = options;
		if (!Type.isUndefined(data) && !Type.isPlainObject(data))
		{
			throw new TypeError('options.data is not a object');
		}

		this.setData(data);

		const sourceNode: ?BBCodeNode = Formatter.prepareSourceNode(source);
		if (Type.isNull(sourceNode))
		{
			throw new TypeError('options.source is not a BBCodeNode or string');
		}

		const nodeFormatter: NodeFormatter = this.getNodeFormatter(sourceNode);
		const isValidNode: boolean = nodeFormatter.validate({
			node: sourceNode,
			formatter: this,
			data,
		});
		if (!isValidNode)
		{
			return null;
		}

		const preparedNode: ?BBCodeNode = nodeFormatter.runBefore({
			node: sourceNode,
			formatter: this,
			data,
		});
		if (Type.isNull(preparedNode))
		{
			return null;
		}

		const convertedElement: ?HTMLElement = nodeFormatter.runConvert({
			node: preparedNode,
			formatter: this,
			data,
		});
		if (Type.isNull(convertedElement))
		{
			return null;
		}

		preparedNode.getChildren().forEach((childNode: BBCodeNode) => {
			const childElement: ?HTMLElement = this.format({ source: childNode, data });
			if (childElement !== null)
			{
				const convertedChildElement: ?HTMLElement = nodeFormatter.runForChild({
					node: childNode,
					element: childElement,
					formatter: this,
					data,
				});

				if (convertedChildElement !== null && this.isElement(convertedElement))
				{
					convertedElement.appendChild(convertedChildElement);
				}
			}
		});

		return nodeFormatter.runAfter({
			node: preparedNode,
			element: convertedElement,
			formatter: this,
			data,
		});
	}
}
