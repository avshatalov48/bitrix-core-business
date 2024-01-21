import { Type } from 'main.core';
import { typeof Node } from '../nodes/node';
import { childFilters } from './child-filters/child-filters';
import { childConverters } from './child-converters/child-converters';

export type ChildFilter = (Node) => boolean;
export type ChildConverter = (Node) => Node | null;
export type AllowedCase = 'lowerCase' | 'upperCase';

export type BBCodeSchemeOptions = {
	allowedTags?: Array<string>,
	childFilters?: { [tagName: string]: ChildFilter },
	childConverters?: { [tagName: string]: ChildConverter },
	propagateUnresolvedNodes?: boolean,
	tagCase?: AllowedCase,
	newLineBeforeBlockOpeningTag?: boolean,
	newLineAfterBlockOpeningTag?: boolean,
	newLineBeforeBlockClosingTag?: boolean,
	newLineAfterBlockClosingTag?: boolean,
	newLineAfterListItem?: boolean,
};

export class BBCodeScheme
{
	static LOWER_CASE: string = 'lowerCase';
	static UPPER_CASE: string = 'upperCase';

	static allowedCases: Set<string> = new Set([
		BBCodeScheme.LOWER_CASE,
		BBCodeScheme.UPPER_CASE,
	]);

	/** @private */
	childFilters: Map<string, ChildFilter> = new Map();

	/** @private */
	childConverters: Map<string, ChildConverter> = new Map();

	/** @private */
	allowedTags: Set<string> = new Set();

	/** @private */
	propagateUnresolvedNodes: boolean = true;

	/** @private */
	tagCase: AllowedCase = BBCodeScheme.LOWER_CASE;

	/** @private */
	allowNewLineBeforeBlockOpeningTag: boolean = true;

	/** @private */
	allowNewLineAfterBlockOpeningTag: boolean = true;

	/** @private */
	allowNewLineBeforeBlockClosingTag: boolean = true;

	/** @private */
	allowNewLineAfterBlockClosingTag: boolean = true;

	/** @private */
	allowNewLineAfterListItem: boolean = true;

	constructor(options: BBCodeSchemeOptions = {})
	{
		this.setChildFilters(childFilters);
		this.setChildConverters(childConverters);

		if (Type.isPlainObject(options))
		{
			this.setAllowedTags(options.allowedTags);
			this.setChildFilters(options.childFilters);
			this.setChildConverters(options.childConverters);
			this.setPropagateUnresolvedNodes(options.propagateUnresolvedNodes);
			this.setTagCase(options.tagCase);
			this.setAllowNewLineBeforeBlockOpeningTag(options.newLineBeforeBlockOpeningTag);
			this.setAllowNewLineAfterBlockOpeningTag(options.newLineAfterBlockOpeningTag);
			this.setAllowNewLineBeforeBlockClosingTag(options.newLineBeforeBlockClosingTag);
			this.setAllowNewLineAfterBlockClosingTag(options.newLineAfterBlockClosingTag);
			this.setAllowNewLineAfterListItem(options.newLineAfterListItem);
		}
	}

	setAllowedTags(allowedTags: Array<string>)
	{
		if (Type.isArray(allowedTags))
		{
			this.allowedTags = new Set(allowedTags);
		}
	}

	addAllowedTag(tag)
	{
		if (Type.isStringFilled(tag))
		{
			this.getAllowedTags().add(tag);
		}
	}

	getAllowedTags(): Set<string>
	{
		return this.allowedTags;
	}

	getChildFilters(): Map<string, ChildFilter>
	{
		return this.childFilters;
	}

	getChildFilter(tagName: string): ChildFilter
	{
		return this.getChildFilters().get(tagName);
	}

	setChildFilters(filters: { [tagName: string]: ChildFilter })
	{
		if (Type.isPlainObject(filters))
		{
			const childFiltersMap = this.getChildFilters();
			Object.entries(filters).forEach(([tagName, filter]) => {
				childFiltersMap.set(tagName, filter);
			});
		}
	}

	getChildConverters(): Map<string, ChildConverter>
	{
		return this.childConverters;
	}

	getChildConverter(tagName: string): ChildConverter
	{
		return this.getChildConverters().get(tagName);
	}

	setChildConverters(converters: { [tagName: string]: ChildConverter })
	{
		if (Type.isPlainObject(converters))
		{
			const convertersMap = this.getChildConverters();
			Object.entries(converters).forEach(([tagName, converter]) => {
				convertersMap.set(tagName, converter);
			});
		}
	}

	setPropagateUnresolvedNodes(value: boolean)
	{
		if (Type.isBoolean(value))
		{
			this.propagateUnresolvedNodes = value;
		}
	}

	isPropagateUnresolvedNodes(): boolean
	{
		return this.propagateUnresolvedNodes;
	}

	setTagCase(tagCase: AllowedCase)
	{
		if (BBCodeScheme.allowedCases.has(tagCase))
		{
			this.tagCase = tagCase;
		}
	}

	getTagCase(): AllowedCase
	{
		return this.tagCase;
	}

	setAllowNewLineBeforeBlockOpeningTag(value: boolean)
	{
		if (Type.isBoolean(value))
		{
			this.allowNewLineBeforeBlockOpeningTag = value;
		}
	}

	isAllowNewLineBeforeBlockOpeningTag(): boolean
	{
		return this.allowNewLineBeforeBlockOpeningTag;
	}

	setAllowNewLineAfterBlockOpeningTag(value: boolean)
	{
		if (Type.isBoolean(value))
		{
			this.allowNewLineAfterBlockOpeningTag = value;
		}
	}

	isAllowNewLineAfterBlockOpeningTag(): boolean
	{
		return this.allowNewLineAfterBlockOpeningTag;
	}

	setAllowNewLineBeforeBlockClosingTag(value: boolean)
	{
		if (Type.isBoolean(value))
		{
			this.allowNewLineBeforeBlockClosingTag = value;
		}
	}

	isAllowNewLineBeforeBlockClosingTag(): boolean
	{
		return this.allowNewLineBeforeBlockClosingTag;
	}

	setAllowNewLineAfterBlockClosingTag(value: boolean)
	{
		if (Type.isBoolean(value))
		{
			this.allowNewLineAfterBlockClosingTag = value;
		}
	}

	isAllowNewLineAfterBlockClosingTag(): boolean
	{
		return this.allowNewLineAfterBlockClosingTag;
	}

	setAllowNewLineAfterListItem(value: boolean)
	{
		if (Type.isBoolean(value))
		{
			this.allowNewLineAfterListItem = value;
		}
	}

	isAllowNewLineAfterListItem(): boolean
	{
		return this.allowNewLineAfterListItem;
	}
}
