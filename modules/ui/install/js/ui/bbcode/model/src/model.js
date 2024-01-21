import {
	Node,
	type NodeOptions,
	type ContentNode,
	type ParentNode,
	type SerializedNode,
	type SpecialCharNode,
} from './nodes/node';
import { RootNode, type RootNodeOptions } from './nodes/root-node';
import { ElementNode } from './nodes/element-node';
import { FragmentNode } from './nodes/fragment-node';
import { NewLineNode } from './nodes/new-line-node';
import { TabNode } from './nodes/tab-node';
import { TextNode } from './nodes/text-node';
import { ModelFactory } from './factory/factory';
import { Tag } from './reference/tag';
import { Text } from './reference/text';
import { BBCodeScheme, type BBCodeSchemeOptions } from './scheme/scheme';

export type {
	NodeOptions,
	ContentNode,
	ParentNode,
	SerializedNode,
	RootNodeOptions,
	SpecialCharNode,
	BBCodeSchemeOptions,
};

export {
	Node,
	RootNode,
	ElementNode,
	FragmentNode,
	NewLineNode,
	TabNode,
	TextNode,
	ModelFactory,
	Tag,
	Text,
	BBCodeScheme,
};
