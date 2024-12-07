export const TokenType: Object<string, string> = {
	WHITESPACE: 'whitespace',
	// LINE_BREAK: 'line-break',
	// TAB: 'tab',
	SEMICOLON: 'semicolon',
	OPERATOR: 'operator',
	BRACE: 'brace',
	BRACKET: 'bracket',
	PARENTHESES: 'parentheses',
	WORD: 'word',
	REGEX: 'regex',
	STRING_DOUBLE: 'string-double',
	STRING_SINGLE: 'string-single',
	STRING_TEMPLATE: 'string-template',
	XML_COMMENT: 'comment-xml',
	COMMENT_MULTILINE: 'comment-multiline',
	COMMENT_SLASH: 'comment-slash',
	COMMENT_HASH: 'comment-hash',
};

export const CommentTokenTypes: Set<string> = new Set([
	TokenType.XML_COMMENT,
	TokenType.COMMENT_MULTILINE,
	TokenType.COMMENT_SLASH,
	TokenType.COMMENT_HASH,
]);

export const StringTokenTypes: Set<string> = new Set([
	TokenType.STRING_SINGLE,
	TokenType.STRING_DOUBLE,
	TokenType.STRING_TEMPLATE,
]);

export type CodeToken = {
	type: string;
	content: string;
}
