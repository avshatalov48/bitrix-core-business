import { type LexicalNode, type TextNode } from 'ui.lexical.core';
import type { BBCodeNode } from 'ui.bbcode.model';

export type BBCodeImportConversion = Record<
	BBCodeTag,
	(node: BBCodeNode) => BBCodeConversion | null,
>;

export type BBCodeTag = string;

export type BBCodeImportMap = Map<BBCodeTag, Array<(node: BBCodeNode) => BBCodeConversion | null>>;

export type BBCodeConversion = {
	conversion: BBCodeConversionFn,
	priority: number,
};

export type BBCodeConversionFn = (node: BBCodeNode) => BBCodeConversionOutput | null;

export type BBCodeConversionOutput = {
	after?: (childLexicalNodes: Array<LexicalNode>) => Array<LexicalNode>,
	forChild?: BBCodeChildConversion,
	node: null | LexicalNode | Array<LexicalNode>,
};

export type BBCodeChildConversion = (
	lexicalNode: LexicalNode, parentLexicalNode: LexicalNode | null | undefined
) => LexicalNode | null | undefined;

export type BBCodeExportOutput = {
	after?: (generatedNode: ?BBCodeNode) => ?BBCodeNode,
	node?: BBCodeNode | null,
};

export type BBCodeExportConversion = Record<BBCodeExportType, BBCodeExportFn>;
export type BBCodeExportFn = BBCodeExportNodeFn | BBCodeExportFormatFn;
export type BBCodeExportNodeFn = (lexicalNode: LexicalNode) => BBCodeExportOutput | null;
export type BBCodeExportFormatFn = (lexicalNode: TextNode, node: BBCodeNode) => BBCodeNode | null;
export type BBCodeExportMap = Map<BBCodeExportType, BBCodeExportFn>;
export type BBCodeExportType = BBCodeExportNodeType | BBCodeExportFormat;
export type BBCodeExportNodeType = string;
export type BBCodeExportFormat = 'text:bold' | 'text:italic' | 'text:strikethrough' | 'text:underline';
