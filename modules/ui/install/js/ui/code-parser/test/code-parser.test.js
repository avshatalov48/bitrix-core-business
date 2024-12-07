import { CodeParser } from '../src/code-parser';
import { mergeTokens } from '../src/merge-tokens';

describe('Code Parser', () => {
	let codeParser = null;

	before(() => {
		codeParser = new CodeParser();
	});

	it('should parse a simple text', () => {
		const tokens = codeParser.parse('One One\nTwo\n\nThree\n\tFour Four Four Four', false);

		assert.equal(tokens.length, 15);
		assert.equal(tokens[0].content, 'One');
		assert.equal(tokens[0].type, 'word');
		assert.equal(tokens[1].content, ' ');
		assert.equal(tokens[1].type, 'whitespace');
		assert.equal(tokens[2].content, 'One');
		assert.equal(tokens[2].type, 'word');
		assert.equal(tokens[3].content, '\n');
		assert.equal(tokens[3].type, 'whitespace');
		assert.equal(tokens[4].content, 'Two');
		assert.equal(tokens[4].type, 'word');
		assert.equal(tokens[5].content, '\n\n');
		assert.equal(tokens[5].type, 'whitespace');
		assert.equal(tokens[6].content, 'Three');
		assert.equal(tokens[6].type, 'word');
		assert.equal(tokens[7].content, '\n\t');
		assert.equal(tokens[7].type, 'whitespace');
		assert.equal(tokens[8].content, 'Four');
		assert.equal(tokens[8].type, 'word');
		assert.equal(tokens[9].content, ' ');
		assert.equal(tokens[9].type, 'whitespace');
		assert.equal(tokens[10].content, 'Four');
		assert.equal(tokens[10].type, 'word');
		assert.equal(tokens[11].content, ' ');
		assert.equal(tokens[11].type, 'whitespace');
		assert.equal(tokens[12].content, 'Four');
		assert.equal(tokens[12].type, 'word');
		assert.equal(tokens[13].content, ' ');
		assert.equal(tokens[13].type, 'whitespace');
		assert.equal(tokens[14].content, 'Four');
		assert.equal(tokens[14].type, 'word');

		const mergedTokens = mergeTokens(tokens);
		assert.equal(mergedTokens.length, 1);
		assert.equal(mergedTokens[0].content, 'One One\nTwo\n\nThree\n\tFour Four Four Four');
	});

	it('should parse a cyrillic text', () => {
		const tokens = codeParser.parse('Один Один\nДва\n\nТри\n\tЧетыре Четыре Четыре Четыре\n', false);

		assert.equal(tokens.length, 16);
		assert.equal(tokens[0].content, 'Один');
		assert.equal(tokens[0].type, 'word');
		assert.equal(tokens[1].content, ' ');
		assert.equal(tokens[1].type, 'whitespace');
		assert.equal(tokens[2].content, 'Один');
		assert.equal(tokens[2].type, 'word');
		assert.equal(tokens[3].content, '\n');
		assert.equal(tokens[3].type, 'whitespace');
		assert.equal(tokens[4].content, 'Два');
		assert.equal(tokens[4].type, 'word');
		assert.equal(tokens[5].content, '\n\n');
		assert.equal(tokens[5].type, 'whitespace');
		assert.equal(tokens[6].content, 'Три');
		assert.equal(tokens[6].type, 'word');
		assert.equal(tokens[7].content, '\n\t');
		assert.equal(tokens[7].type, 'whitespace');
		assert.equal(tokens[8].content, 'Четыре');
		assert.equal(tokens[8].type, 'word');
		assert.equal(tokens[9].content, ' ');
		assert.equal(tokens[9].type, 'whitespace');
		assert.equal(tokens[10].content, 'Четыре');
		assert.equal(tokens[10].type, 'word');
		assert.equal(tokens[11].content, ' ');
		assert.equal(tokens[11].type, 'whitespace');
		assert.equal(tokens[12].content, 'Четыре');
		assert.equal(tokens[12].type, 'word');
		assert.equal(tokens[13].content, ' ');
		assert.equal(tokens[13].type, 'whitespace');
		assert.equal(tokens[14].content, 'Четыре');
		assert.equal(tokens[14].type, 'word');
		assert.equal(tokens[15].content, '\n');
		assert.equal(tokens[15].type, 'whitespace');

		const mergedTokens = mergeTokens(tokens);
		assert.equal(mergedTokens.length, 1);
		assert.equal(mergedTokens[0].content, 'Один Один\nДва\n\nТри\n\tЧетыре Четыре Четыре Четыре\n');
	});

	it('should parse windows line breaks', () => {
		const tokens = codeParser.parse('Один Один\r\nДва\r\n\r\nТри\r\n\tЧетыре\nЧетыре\n\t\tЧетыре Четыре\r\n', false);

		assert.equal(tokens.length, 16);
		assert.equal(tokens[0].content, 'Один');
		assert.equal(tokens[0].type, 'word');
		assert.equal(tokens[1].content, ' ');
		assert.equal(tokens[1].type, 'whitespace');
		assert.equal(tokens[2].content, 'Один');
		assert.equal(tokens[2].type, 'word');
		assert.equal(tokens[3].content, '\r\n');
		assert.equal(tokens[3].type, 'whitespace');
		assert.equal(tokens[4].content, 'Два');
		assert.equal(tokens[4].type, 'word');
		assert.equal(tokens[5].content, '\r\n\r\n');
		assert.equal(tokens[5].type, 'whitespace');
		assert.equal(tokens[6].content, 'Три');
		assert.equal(tokens[6].type, 'word');
		assert.equal(tokens[7].content, '\r\n\t');
		assert.equal(tokens[7].type, 'whitespace');
		assert.equal(tokens[8].content, 'Четыре');
		assert.equal(tokens[8].type, 'word');
		assert.equal(tokens[9].content, '\n');
		assert.equal(tokens[9].type, 'whitespace');
		assert.equal(tokens[10].content, 'Четыре');
		assert.equal(tokens[10].type, 'word');
		assert.equal(tokens[11].content, '\n\t\t');
		assert.equal(tokens[11].type, 'whitespace');
		assert.equal(tokens[12].content, 'Четыре');
		assert.equal(tokens[12].type, 'word');
		assert.equal(tokens[13].content, ' ');
		assert.equal(tokens[13].type, 'whitespace');
		assert.equal(tokens[14].content, 'Четыре');
		assert.equal(tokens[14].type, 'word');
		assert.equal(tokens[15].content, '\r\n');
		assert.equal(tokens[15].type, 'whitespace');
	});

	it('should parse a JavaScript code', () => {
		const tokens = codeParser.parse('var my_var = "a"; // [comment]\nif (a === true)\n{\tlet s = 123;\n\ts++;\n}\n', false);

		assert.equal(tokens.length, 41);
		assert.equal(tokens[0].content, 'var');
		assert.equal(tokens[0].type, 'keyword');
		assert.equal(tokens[1].content, ' ');
		assert.equal(tokens[1].type, 'whitespace');
		assert.equal(tokens[2].content, 'my_var');
		assert.equal(tokens[2].type, 'word');
		assert.equal(tokens[3].content, ' ');
		assert.equal(tokens[3].type, 'whitespace');
		assert.equal(tokens[4].content, '=');
		assert.equal(tokens[4].type, 'operator');
		assert.equal(tokens[5].content, ' ');
		assert.equal(tokens[5].type, 'whitespace');
		assert.equal(tokens[6].content, '"a"');
		assert.equal(tokens[6].type, 'string');
		assert.equal(tokens[7].content, ';');
		assert.equal(tokens[7].type, 'semicolon');
		assert.equal(tokens[8].content, ' ');
		assert.equal(tokens[8].type, 'whitespace');
		assert.equal(tokens[9].content, '// [comment]');
		assert.equal(tokens[9].type, 'comment');
		assert.equal(tokens[10].content, '\n');
		assert.equal(tokens[10].type, 'whitespace');
		assert.equal(tokens[11].content, 'if');
		assert.equal(tokens[11].type, 'keyword');
		assert.equal(tokens[12].content, ' ');
		assert.equal(tokens[12].type, 'whitespace');
		assert.equal(tokens[13].content, '(');
		assert.equal(tokens[13].type, 'parentheses');
		assert.equal(tokens[14].content, 'a');
		assert.equal(tokens[14].type, 'word');
		assert.equal(tokens[15].content, ' ');
		assert.equal(tokens[15].type, 'whitespace');
		assert.equal(tokens[16].content, '=');
		assert.equal(tokens[16].type, 'operator');
		assert.equal(tokens[17].content, '=');
		assert.equal(tokens[17].type, 'operator');
		assert.equal(tokens[18].content, '=');
		assert.equal(tokens[18].type, 'operator');
		assert.equal(tokens[19].content, ' ');
		assert.equal(tokens[19].type, 'whitespace');
		assert.equal(tokens[20].content, 'true');
		assert.equal(tokens[20].type, 'keyword');
		assert.equal(tokens[21].content, ')');
		assert.equal(tokens[21].type, 'parentheses');
		assert.equal(tokens[22].content, '\n');
		assert.equal(tokens[22].type, 'whitespace');
		assert.equal(tokens[23].content, '{');
		assert.equal(tokens[23].type, 'brace');
		assert.equal(tokens[24].content, '\t');
		assert.equal(tokens[24].type, 'whitespace');
		assert.equal(tokens[25].content, 'let');
		assert.equal(tokens[25].type, 'keyword');
		assert.equal(tokens[26].content, ' ');
		assert.equal(tokens[26].type, 'whitespace');
		assert.equal(tokens[27].content, 's');
		assert.equal(tokens[27].type, 'word');
		assert.equal(tokens[28].content, ' ');
		assert.equal(tokens[28].type, 'whitespace');
		assert.equal(tokens[29].content, '=');
		assert.equal(tokens[29].type, 'operator');
		assert.equal(tokens[30].content, ' ');
		assert.equal(tokens[30].type, 'whitespace');
		assert.equal(tokens[31].content, '123');
		assert.equal(tokens[31].type, 'number');
		assert.equal(tokens[32].content, ';');
		assert.equal(tokens[32].type, 'semicolon');
		assert.equal(tokens[33].content, '\n\t');
		assert.equal(tokens[33].type, 'whitespace');
		assert.equal(tokens[34].content, 's');
		assert.equal(tokens[34].type, 'word');
		assert.equal(tokens[35].content, '+');
		assert.equal(tokens[35].type, 'operator');
		assert.equal(tokens[36].content, '+');
		assert.equal(tokens[36].type, 'operator');
		assert.equal(tokens[37].content, ';');
		assert.equal(tokens[37].type, 'semicolon');
		assert.equal(tokens[38].content, '\n');
		assert.equal(tokens[38].type, 'whitespace');
		assert.equal(tokens[39].content, '}');
		assert.equal(tokens[39].type, 'brace');
		assert.equal(tokens[40].content, '\n');
		assert.equal(tokens[40].type, 'whitespace');

		const mergedTokens = mergeTokens(tokens);
		assert.equal(mergedTokens.length, 32);
		assert.equal(mergedTokens[1].type, 'word');
		assert.equal(mergedTokens[1].content, ' my_var ');
		assert.equal(mergedTokens[13].type, 'operator');
		assert.equal(mergedTokens[13].content, '===');
		assert.equal(mergedTokens[27].type, 'operator');
		assert.equal(mergedTokens[27].content, '++');
	});

	it('should parse a PHP code', () => {
		const tokens = codeParser.parse('$ar = array();\n$ar[] = 134;\n\nfor($i = 0; $i < $ar.length; $i++)', false);

		assert.equal(tokens.length, 40);
		assert.equal(tokens[0].content, '$ar');
		assert.equal(tokens[0].type, 'word');
		assert.equal(tokens[1].content, ' ');
		assert.equal(tokens[1].type, 'whitespace');
		assert.equal(tokens[2].content, '=');
		assert.equal(tokens[2].type, 'operator');
		assert.equal(tokens[3].content, ' ');
		assert.equal(tokens[3].type, 'whitespace');
		assert.equal(tokens[4].content, 'array');
		assert.equal(tokens[4].type, 'keyword');
		assert.equal(tokens[5].content, '(');
		assert.equal(tokens[5].type, 'parentheses');
		assert.equal(tokens[6].content, ')');
		assert.equal(tokens[6].type, 'parentheses');
		assert.equal(tokens[7].content, ';');
		assert.equal(tokens[7].type, 'semicolon');
		assert.equal(tokens[8].content, '\n');
		assert.equal(tokens[8].type, 'whitespace');
		assert.equal(tokens[9].content, '$ar');
		assert.equal(tokens[9].type, 'word');
		assert.equal(tokens[10].content, '[');
		assert.equal(tokens[10].type, 'bracket');
		assert.equal(tokens[11].content, ']');
		assert.equal(tokens[11].type, 'bracket');
		assert.equal(tokens[12].content, ' ');
		assert.equal(tokens[12].type, 'whitespace');
		assert.equal(tokens[13].content, '=');
		assert.equal(tokens[13].type, 'operator');
		assert.equal(tokens[14].content, ' ');
		assert.equal(tokens[14].type, 'whitespace');
		assert.equal(tokens[15].content, '134');
		assert.equal(tokens[15].type, 'number');
		assert.equal(tokens[16].content, ';');
		assert.equal(tokens[16].type, 'semicolon');
		assert.equal(tokens[17].content, '\n\n');
		assert.equal(tokens[17].type, 'whitespace');
		assert.equal(tokens[18].content, 'for');
		assert.equal(tokens[18].type, 'keyword');
		assert.equal(tokens[19].content, '(');
		assert.equal(tokens[19].type, 'parentheses');
		assert.equal(tokens[20].content, '$i');
		assert.equal(tokens[20].type, 'word');
		assert.equal(tokens[21].content, ' ');
		assert.equal(tokens[21].type, 'whitespace');
		assert.equal(tokens[22].content, '=');
		assert.equal(tokens[22].type, 'operator');
		assert.equal(tokens[23].content, ' ');
		assert.equal(tokens[23].type, 'whitespace');
		assert.equal(tokens[24].content, '0');
		assert.equal(tokens[24].type, 'number');
		assert.equal(tokens[25].content, ';');
		assert.equal(tokens[25].type, 'semicolon');
		assert.equal(tokens[26].content, ' ');
		assert.equal(tokens[26].type, 'whitespace');
		assert.equal(tokens[27].content, '$i');
		assert.equal(tokens[27].type, 'word');
		assert.equal(tokens[28].content, ' ');
		assert.equal(tokens[28].type, 'whitespace');
		assert.equal(tokens[29].content, '<');
		assert.equal(tokens[29].type, 'operator');
		assert.equal(tokens[30].content, ' ');
		assert.equal(tokens[30].type, 'whitespace');
		assert.equal(tokens[31].content, '$ar');
		assert.equal(tokens[31].type, 'word');
		assert.equal(tokens[32].content, '.');
		assert.equal(tokens[32].type, 'operator');
		assert.equal(tokens[33].content, 'length');
		assert.equal(tokens[33].type, 'word');
		assert.equal(tokens[34].content, ';');
		assert.equal(tokens[34].type, 'semicolon');
		assert.equal(tokens[35].content, ' ');
		assert.equal(tokens[35].type, 'whitespace');
		assert.equal(tokens[36].content, '$i');
		assert.equal(tokens[36].type, 'word');
		assert.equal(tokens[37].content, '+');
		assert.equal(tokens[37].type, 'operator');
		assert.equal(tokens[38].content, '+');
		assert.equal(tokens[38].type, 'operator');
		assert.equal(tokens[39].content, ')');
		assert.equal(tokens[39].type, 'parentheses');
	});

	it('should parse whitespaces', () => {
		let tokens = codeParser.parse('\n', false);

		assert.equal(tokens.length, 1);
		assert.equal(tokens[0].type, 'whitespace');
		assert.equal(tokens[0].content, '\n');

		tokens = codeParser.parse('\n\n', false);

		assert.equal(tokens.length, 1);
		assert.equal(tokens[0].type, 'whitespace');
		assert.equal(tokens[0].content, '\n\n');

		tokens = codeParser.parse(' \n\n', false);

		assert.equal(tokens.length, 1);
		assert.equal(tokens[0].type, 'whitespace');
		assert.equal(tokens[0].content, ' \n\n');

		tokens = codeParser.parse('  \n\n', false);

		assert.equal(tokens.length, 1);
		assert.equal(tokens[0].type, 'whitespace');
		assert.equal(tokens[0].content, '  \n\n');

		tokens = codeParser.parse('a  \n\n', false);

		assert.equal(tokens.length, 2);
		assert.equal(tokens[0].type, 'word');
		assert.equal(tokens[0].content, 'a');
		assert.equal(tokens[1].type, 'whitespace');
		assert.equal(tokens[1].content, '  \n\n');

		tokens = codeParser.parse('\n\n  \n\n', false);

		assert.equal(tokens.length, 1);
		assert.equal(tokens[0].type, 'whitespace');
		assert.equal(tokens[0].content, '\n\n  \n\n');

		tokens = codeParser.parse('\r\n', false);

		assert.equal(tokens.length, 1);
		assert.equal(tokens[0].type, 'whitespace');
		assert.equal(tokens[0].content, '\r\n');

		tokens = codeParser.parse('\r\n\r\n', false);

		assert.equal(tokens.length, 1);
		assert.equal(tokens[0].type, 'whitespace');
		assert.equal(tokens[0].content, '\r\n\r\n');

		tokens = codeParser.parse(' \r\n\r\n', false);
		assert.equal(tokens.length, 1);
		assert.equal(tokens[0].type, 'whitespace');
		assert.equal(tokens[0].content, ' \r\n\r\n');

		tokens = codeParser.parse('  \r\n\r\n', false);

		assert.equal(tokens.length, 1);
		assert.equal(tokens[0].type, 'whitespace');
		assert.equal(tokens[0].content, '  \r\n\r\n');
	});

	it('should parse tabs', () => {
		let tokens = codeParser.parse('\t', false);
		assert.equal(tokens.length, 1);
		assert.equal(tokens[0].type, 'whitespace');
		assert.equal(tokens[0].content, '\t');

		tokens = codeParser.parse('\t\t', false);
		assert.equal(tokens.length, 1);
		assert.equal(tokens[0].type, 'whitespace');
		assert.equal(tokens[0].content, '\t\t');

		tokens = codeParser.parse(' \t\t', false);
		assert.equal(tokens.length, 1);
		assert.equal(tokens[0].type, 'whitespace');
		assert.equal(tokens[0].content, ' \t\t');

		tokens = codeParser.parse('  \t\t', false);
		assert.equal(tokens.length, 1);
		assert.equal(tokens[0].type, 'whitespace');
		assert.equal(tokens[0].content, '  \t\t');

		tokens = codeParser.parse('a  \t\t', false);
		assert.equal(tokens.length, 2);
		assert.equal(tokens[0].type, 'word');
		assert.equal(tokens[0].content, 'a');
		assert.equal(tokens[1].type, 'whitespace');
		assert.equal(tokens[1].content, '  \t\t');

		tokens = codeParser.parse('\t\t  \t\t', false);
		assert.equal(tokens.length, 1);
		assert.equal(tokens[0].type, 'whitespace');
		assert.equal(tokens[0].content, '\t\t  \t\t');

		tokens = codeParser.parse('\n\t', false);
		assert.equal(tokens.length, 1);
		assert.equal(tokens[0].type, 'whitespace');
		assert.equal(tokens[0].content, '\n\t');

		tokens = codeParser.parse('\t\n', false);
		assert.equal(tokens.length, 1);
		assert.equal(tokens[0].type, 'whitespace');
		assert.equal(tokens[0].content, '\t\n');

		tokens = codeParser.parse(' \n\t', false);
		assert.equal(tokens.length, 1);
		assert.equal(tokens[0].type, 'whitespace');
		assert.equal(tokens[0].content, ' \n\t');

		tokens = codeParser.parse(' \t\n', false);
		assert.equal(tokens.length, 1);
		assert.equal(tokens[0].type, 'whitespace');
		assert.equal(tokens[0].content, ' \t\n');

		tokens = codeParser.parse('  \n\n', false);
		assert.equal(tokens.length, 1);
		assert.equal(tokens[0].type, 'whitespace');
		assert.equal(tokens[0].content, '  \n\n');

		tokens = codeParser.parse('a \t \n\n', false);
		assert.equal(tokens.length, 2);
		assert.equal(tokens[0].type, 'word');
		assert.equal(tokens[0].content, 'a');
		assert.equal(tokens[1].type, 'whitespace');
		assert.equal(tokens[1].content, ' \t \n\n');

		tokens = codeParser.parse('\n\n\t\n\n', false);
		assert.equal(tokens.length, 1);
		assert.equal(tokens[0].type, 'whitespace');
		assert.equal(tokens[0].content, '\n\n\t\n\n');

		tokens = codeParser.parse('\r\n\t', false);
		assert.equal(tokens.length, 1);
		assert.equal(tokens[0].type, 'whitespace');
		assert.equal(tokens[0].content, '\r\n\t');

		tokens = codeParser.parse(' \t\r\n', false);
		assert.equal(tokens.length, 1);
		assert.equal(tokens[0].type, 'whitespace');
		assert.equal(tokens[0].content, ' \t\r\n');
	});

	it('should parse simple line', () => {
		let tokens = codeParser.parse('//', false);
		assert.equal(tokens.length, 1);
		assert.equal(tokens[0].type, 'comment');
		assert.equal(tokens[0].content, '//');

		tokens = codeParser.parse('// comment', false);
		assert.equal(tokens.length, 1);
		assert.equal(tokens[0].type, 'comment');
		assert.equal(tokens[0].content, '// comment');

		// tokens = codeParser.parse('[ini]', false);
		// tokens = codeParser.parse('[', false);
		// tokens = codeParser.parse(']', false);
		// tokens = codeParser.parse('{', false);
		// tokens = codeParser.parse('}', false);
		// tokens = codeParser.parse('{brace}', false);
		// tokens = codeParser.parse('{brace};', false);
		// tokens = codeParser.parse(';', false);
		// tokens = codeParser.parse(':', false);
		// tokens = codeParser.parse('/* \n\n word */', false);
	});

	xit('should generate test', () => {
		const tokens = codeParser.parse('One One\nTwo\n\nThree\n\tFour Four Four Four', false);

		let result = `assert.equal(tokens.length, ${tokens.length});\n`;

		function addSlashes(str: string): string
		{
			return str
				.replaceAll(/\n/g, '\\n')
				.replaceAll(/\t/g, '\\t')
				.replaceAll(/\r/g, '\\r')
			;
		}

		for (const [key, token] of tokens.entries())
		{
			result += `assert.equal(tokens[${key}].content, '${addSlashes(token.content)}');\n`;
			result += `assert.equal(tokens[${key}].type, '${token.type}');\n`;
		}

		console.log(result);
	});
});
