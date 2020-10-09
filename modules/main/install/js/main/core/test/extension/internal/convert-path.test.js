import convertPath from '../../../src/lib/extension/internal/convert-path';

describe('main.core/extension/internal/convert-path', () => {
	it('Should convert path with dot notation', () => {
		assert.deepStrictEqual(
			convertPath(
				'param1.param11.param111',
			),
			['param1', 'param11', 'param111'],
		);

		assert.deepStrictEqual(
			convertPath(
				'PARAM_1.PARAM_11.PARAM_111',
			),
			['PARAM_1', 'PARAM_11', 'PARAM_111'],
		);

		assert.deepStrictEqual(
			convertPath(
				'Param-1.Param-2.Param-3',
			),
			['Param-1', 'Param-2', 'Param-3'],
		);
	});

	it('Should convert path with bracket notation', () => {
		assert.deepStrictEqual(
			convertPath(
				'param1["param2"]["param3"][0][1]',
			),
			['param1', 'param2', 'param3', '0', '1'],
		);
	});

	it('Should convert combined path notation', () => {
		assert.deepStrictEqual(
			convertPath(
				'param1.param2[0].param3[0][1].param4',
			),
			['param1', 'param2', '0', 'param3', '0', '1', 'param4'],
		);
	});
});