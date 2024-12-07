import type { LexicalNode } from 'ui.lexical.core';

export type SchemeValidationOptions = {
	nodes?: SchemeNodeValidation[],
	bbcodeMap?: Object<string, string>,
};

export type SchemeNodeValidation = {
	nodeClass: Class<LexicalNode>,
	validate?: (node: LexicalNode) => {},
};
