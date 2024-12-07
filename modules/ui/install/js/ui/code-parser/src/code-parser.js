import { mergeTokens } from './merge-tokens';
import { CommentTokenTypes, StringTokenTypes, TokenType, type CodeToken } from './token-types';

const WORD_REGEX = /[\p{L}\p{N}0-9_$]/u;
// const NON_WHITESPACE_REGEX = /[^\v\f \u00A0\u1680\u2000-\u200A\u2028\u2029\u202F\u205F\u3000\uFEFF]/;

const keywords: Set<string> = new Set([
	'abstract', 'alias', 'and', 'arguments', 'array', 'asm', 'assert', 'auto',
	'base', 'begin', 'bool', 'boolean', 'break', 'byte', 'case', 'catch',
	'char', 'checked', 'class', 'clone', 'compl', 'const', 'continue',
	'debugger', 'decimal', 'declare', 'def', 'default', 'defer', 'deinit', 'del', 'delegate',
	'delete', 'do', 'double', 'echo', 'elif', 'else', 'elseif', 'elsif', 'end',
	'ensure', 'enum', 'event', 'except', 'exec', 'explicit', 'export',
	'extends', 'extension', 'extern', 'fallthrough', 'false', 'final',
	'finally', 'fixed', 'float', 'for', 'foreach', 'friend', 'from', 'func',
	'function', 'global', 'goto', 'guard', 'if', 'implements', 'implicit',
	'import', 'include', 'include_once', 'init', 'inline', 'inout',
	'instanceof', 'int', 'interface', 'internal', 'is', 'lambda', 'let',
	'lock', 'long', 'module', 'mutable', 'namespace', 'NaN', 'native', 'new',
	'next', 'nil', 'none', 'not', 'null', 'object', 'operator', 'or', 'out',
	'override', 'package', 'params', 'pass', 'private', 'protected', 'protocol',
	'public', 'raise', 'readonly', 'redo', 'ref', 'register', 'repeat',
	'require', 'require_once', 'rescue', 'restrict', 'retry', 'return',
	'sbyte', 'sealed', 'self', 'short', 'signed', 'sizeof', 'static',
	'string', 'struct', 'subscript', 'super', 'switch', 'synchronized',
	'template', 'then', 'this', 'throw', 'throws', 'transient', 'true', 'try',
	'typealias', 'typedef', 'typeid', 'typename', 'typeof', 'unchecked',
	'undef', 'undefined', 'union', 'unless', 'unsigned', 'until', 'use',
	'using', 'var', 'virtual', 'void', 'volatile', 'wchar_t', 'when', 'where',
	'while', 'with', 'xor', 'yield',
]);

export class CodeParser
{
	// eslint-disable-next-line max-lines-per-function
	parse(text, merge = true): CodeToken[]
	{
		let currentPosition = 0;
		let nextChar = text[0];
		let currentChar = 1;
		let prevChar = null;
		let beforePrevChar = null;
		let tokenContent = '';
		let tokenType: string = null;
		let lastTokenType = null;
		let isMultiChar = null;
		const result = [];

		const shouldFinalizeToken = (): boolean => {
			if (!currentChar)
			{
				// end of content
				return true;
			}

			switch (tokenType)
			{
				// case TokenType.LINE_BREAK:
				// 	if (prevChar === '\r' && currentChar === '\n')
				// 	{
				// 		return false;
				// 	}
				//
				// 	return true;
				// case TokenType.TAB:
				// 	return true;
				case TokenType.WHITESPACE:
					return /\S/.test(currentChar);
				// return NON_WHITESPACE_REGEX.test(currentChar);
				case TokenType.OPERATOR:
				case TokenType.SEMICOLON:
				case TokenType.BRACKET:
				case TokenType.BRACE:
				case TokenType.PARENTHESES:
					return true;
				case TokenType.WORD:
					return !WORD_REGEX.test(currentChar);
				case TokenType.REGEX:
					return (prevChar === '/' || prevChar === '\n') && isMultiChar;
				case TokenType.STRING_DOUBLE:
					return prevChar === '"' && isMultiChar;
				case TokenType.STRING_SINGLE:
					return prevChar === '\'' && isMultiChar;
				case TokenType.STRING_TEMPLATE:
					return prevChar === '`' && isMultiChar;
				case TokenType.XML_COMMENT:
					return text[currentPosition - 4] + beforePrevChar + prevChar === '-->';
				case TokenType.COMMENT_MULTILINE:
					return beforePrevChar + prevChar === '*/';
				case TokenType.COMMENT_SLASH:
				case TokenType.COMMENT_HASH:
					return currentChar === '\n';
				default:
					return false;
			}
		};

		const getTokenType = (): string => {
			if (currentChar === '#')
			{
				return TokenType.COMMENT_HASH;
			}

			if (currentChar + nextChar === '//')
			{
				return TokenType.COMMENT_SLASH;
			}

			if (currentChar + nextChar === '/*')
			{
				return TokenType.COMMENT_MULTILINE;
			}

			if (currentChar + nextChar + text[currentPosition + 1] + text[currentPosition + 2] === '<!--')
			{
				return TokenType.XML_COMMENT;
			}

			if (currentChar === '`')
			{
				return TokenType.STRING_TEMPLATE;
			}

			if (currentChar === '\'')
			{
				return TokenType.STRING_SINGLE;
			}

			if (currentChar === '"')
			{
				return TokenType.STRING_DOUBLE;
			}

			if (
				currentChar === '/'
				&& [TokenType.WHITESPACE, TokenType.OPERATOR].includes(lastTokenType)
				&& prevChar !== '<'
			)
			{
				return TokenType.REGEX;
			}

			if (currentChar === '(' || currentChar === ')')
			{
				return TokenType.PARENTHESES;
			}

			if (currentChar === '[' || currentChar === ']')
			{
				return TokenType.BRACKET;
			}

			if (currentChar === '{' || currentChar === '}')
			{
				return TokenType.BRACE;
			}

			if (WORD_REGEX.test(currentChar))
			{
				return TokenType.WORD;
			}

			// if (currentChar === '\n' || (currentChar === '\r' && nextChar === '\n'))
			// {
			// 	return TokenType.LINE_BREAK;
			// }
			//
			// if (currentChar === '\t')
			// {
			// 	return TokenType.TAB;
			// }

			if (currentChar === ';')
			{
				return TokenType.SEMICOLON;
			}

			if (/[!&*+,./:;<=>?@\\|~-]/.test(currentChar))
			{
				return TokenType.OPERATOR;
			}

			return TokenType.WHITESPACE;
		};

		// eslint-disable-next-line no-cond-assign
		while ((prevChar = !CommentTokenTypes.has(tokenType) && prevChar === '\\' ? true : currentChar))
		{
			currentChar = nextChar;
			nextChar = text[++currentPosition];
			isMultiChar = tokenContent.length > 1;

			if (tokenType === null)
			{
				tokenType = getTokenType();
			}

			if (shouldFinalizeToken())
			{
				if (tokenContent)
				{
					result.push({
						content: tokenContent,
						type: this.#normalizeTokenType(tokenType, tokenContent),
					});
				}

				if (tokenType !== TokenType.WHITESPACE && !CommentTokenTypes.has(tokenType))
				{
					lastTokenType = tokenType;
				}

				tokenContent = '';
				tokenType = getTokenType();
			}

			tokenContent += currentChar;
			beforePrevChar = prevChar;
		}

		return merge ? mergeTokens(result) : result;
	}

	#normalizeTokenType(type: string, content: string): string
	{
		if (CommentTokenTypes.has(type))
		{
			return 'comment';
		}

		if (StringTokenTypes.has(type))
		{
			return 'string';
		}

		if (type === TokenType.WORD)
		{
			if (keywords.has(content))
			{
				return 'keyword';
			}

			if (/^\d+/.test(content))
			{
				return 'number';
			}
		}

		return type;
	}
}
