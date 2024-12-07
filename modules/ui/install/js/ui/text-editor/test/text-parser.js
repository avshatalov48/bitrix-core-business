import { SmileyParser } from '../src/plugins/smiley/smiley-parser';
import Smiley from '../src/plugins/smiley/smiley';

const smileys = [
	{
		name: 'С улыбкой',
		image: '/upload/main/smiles/2/bx_smile_smile.png',
		typing: ':)',
		width: 20,
		height: 20,
	},
	{
		name: 'С улыбкой',
		image: '/upload/main/smiles/2/bx_smile_smile.png',
		typing: ':-)',
		width: 20,
		height: 20,
	},
	{
		name: 'Шутливо',
		image: '/upload/main/smiles/2/bx_smile_wink.png',
		typing: ';)',
		width: 20,
		height: 20,
	},
	{
		name: 'Шутливо',
		image: '/upload/main/smiles/2/bx_smile_wink.png',
		typing: ';-)',
		width: 20,
		height: 20,
	},
	{
		name: 'Широкая улыбка',
		image: '/upload/main/smiles/2/bx_smile_biggrin.png',
		typing: ':D',
		width: 20,
		height: 20,
	},
	{
		name: 'Широкая улыбка',
		image: '/upload/main/smiles/2/bx_smile_biggrin.png',
		typing: ':-D',
		width: 20,
		height: 20,
	},
	{
		name: 'Здорово',
		image: '/upload/main/smiles/2/bx_smile_cool.png',
		typing: '8-)',
		width: 20,
		height: 20,
	},
	{
		name: 'Разочарование',
		image: '/upload/main/smiles/2/bx_smile_facepalm.png',
		typing: ':facepalm:',
		width: 20,
		height: 20,
	},
	{
		name: 'Поцелуй',
		image: '/upload/main/smiles/2/bx_smile_kiss.png',
		typing: ':{}',
		width: 20,
		height: 20,
	},
	{
		name: 'Поцелуй',
		image: '/upload/main/smiles/2/bx_smile_kiss.png',
		typing: ':-{}',
		width: 20,
		height: 20,
	},
	{
		name: 'Печально',
		image: '/upload/main/smiles/2/bx_smile_sad.png',
		typing: ':(',
		width: 20,
		height: 20,
	},
	{
		name: 'Печально',
		image: '/upload/main/smiles/2/bx_smile_sad.png',
		typing: ':-(',
		width: 20,
		height: 20,
	},
	{
		name: 'Скептически',
		image: '/upload/main/smiles/2/bx_smile_neutral.png',
		typing: ':|',
		width: 20,
		height: 20,
	},
	{
		name: 'Скептически',
		image: '/upload/main/smiles/2/bx_smile_neutral.png',
		typing: ':-|',
		width: 20,
		height: 20,
	},
	{
		name: 'Смущенный',
		image: '/upload/main/smiles/2/bx_smile_redface.png',
		typing: ':oops:',
		width: 20,
		height: 20,
	},
	{
		name: 'Очень грустно',
		image: '/upload/main/smiles/2/bx_smile_cry.png',
		typing: ':cry:',
		width: 20,
		height: 20,
	},
	{
		name: 'Очень грустно',
		image: '/upload/main/smiles/2/bx_smile_cry.png',
		typing: ':~(',
		width: 20,
		height: 20,
	},
	{
		name: 'Со злостью',
		image: '/upload/main/smiles/2/bx_smile_evil.png',
		typing: ':evil:',
		width: 20,
		height: 20,
	},
	{
		name: 'Со злостью',
		image: '/upload/main/smiles/2/bx_smile_evil.png',
		typing: '>:-<',
		width: 20,
		height: 20,
	},
	{
		name: 'Удивленно',
		image: '/upload/main/smiles/2/bx_smile_eek.png',
		typing: ':o',
		width: 20,
		height: 20,
	},
	{
		name: 'Удивленно',
		image: '/upload/main/smiles/2/bx_smile_eek.png',
		typing: ':-o',
		width: 20,
		height: 20,
	},
	{
		name: 'Удивленно',
		image: '/upload/main/smiles/2/bx_smile_eek.png',
		typing: ':shock:',
		width: 20,
		height: 20,
	},
	{
		name: 'Смущенно',
		image: '/upload/main/smiles/2/bx_smile_confuse.png',
		typing: ':/',
		width: 20,
		height: 20,
	},
	{
		name: 'Смущенно',
		image: '/upload/main/smiles/2/bx_smile_confuse.png',
		typing: ':-/',
		width: 20,
		height: 20,
	},
	{
		name: 'Идея',
		image: '/upload/main/smiles/2/bx_smile_idea.png',
		typing: ':idea:',
		width: 20,
		height: 20,
	},
	{
		name: 'Вопрос',
		image: '/upload/main/smiles/2/bx_smile_question.png',
		typing: ':?:',
		width: 20,
		height: 20,
	},
	{
		name: 'Восклицание',
		image: '/upload/main/smiles/2/bx_smile_exclaim.png',
		typing: ':!:',
		width: 20,
		height: 20,
	},
	{
		name: 'Нравится',
		image: '/upload/main/smiles/2/bx_smile_like.png',
		typing: ':like:',
		width: 20,
		height: 20,
	},
	{
		name: 'Bue.js',
		image: '/upload/main/smiles/3/bue.png',
		typing: ':bue:',
		width: 128,
		height: 128,
	},
	{
		name: 'Null',
		image: '/upload/main/smiles/3/null.png',
		typing: ':bitrix/null:',
		width: 128,
		height: 128,
	},
	{
		name: 'ORM',
		image: '/upload/main/smiles/3/orm.png',
		typing: ':bitrix/orm:',
		width: 128,
		height: 128,
	},
	{
		name: 'Taras',
		image: '/upload/main/smiles/3/Screenshot 2023-12-27 at 17.55.48.png',
		typing: ':taras:',
		width: 103,
		height: 34,
	},
	{
		name: 'Oleg',
		image: '/upload/main/smiles/3/Screenshot 2023-12-28 at 10.09.12.png',
		typing: ':oleg:',
		width: 91,
		height: 127,
	},
];

const smileyParser = new SmileyParser(smileys.map((smiley) => new Smiley(smiley)));

describe('Text Parser', () => {
	it('should parse smileys', () => {
		const tests = [
			[':)', [[0, 2]]],
			['Text:)', []],
			['Text :)', [[5, 7]]],
			[':)Text', []],
			[':) Text', [[0, 2]]],
			[':)Text:)', []],
			[':) Text :)', [[0, 2], [8, 10]]],
			['Text :) Text', [[5, 7]]],
			['Text.:) Text.:)', [[5, 7], [13, 15]]],
			[':):):):)', [[0, 2], [2, 4], [4, 6], [6, 8]]],
			[':):-):):-{}', [[0, 2], [2, 5], [5, 7], [7, 11]]],
			[':) :-) :) :-{}', [[0, 2], [3, 6], [7, 9], [10, 14]]],
			[':);):D8-)', [[0, 2], [2, 4], [4, 6], [6, 9]]],
			['Привет!:bitrix/null:', [[7, 20]]],
			['Привет!:bitrix/null::)', [[7, 20], [20, 22]]],
			['>:-<>:-<', [[0, 4], [4, 8]]],
			['8-):D8-):D8-)8-):D text :D', [[0, 3], [3, 5], [5, 8], [8, 10], [10, 13], [13, 16], [16, 18], [24, 26]]],
			['>:-<8-)>:-<', [[0, 4], [4, 7], [7, 11]]],
			[':oops:', [[0, 6]]],
			[':o', [[0, 2]]],
			[':o:oops:', [[0, 2], [2, 8]]],
			[':oops::o', [[0, 6], [6, 8]]],
			[':oops::o', [[0, 6], [6, 8]]],
			[':oleg::o:oleg::o text :):):o text :oleg:', [[0, 6], [6, 8], [8, 14], [14, 16], [22, 24], [24, 26], [26, 28], [34, 40]]],
		];

		tests.forEach((test) => {
			const [text, result] = test;
			const splits = smileyParser.parse(text);
			const expected = result.map((entry) => {
				return {
					start: entry[0],
					end: entry[1],
				};
			});

			assert.deepEqual(splits, expected);
		});
	});
});
