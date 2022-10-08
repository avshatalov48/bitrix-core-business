import FormatField from "../../../src/entity/format/formatfield";

describe('FormatField', () => {

	it('Should be a function', () => {
		assert(typeof FormatField === 'function');
	});

	it('Should be constructed successfully', () => {
		let field = new FormatField({
			type: 222,
			sort: 100,
			name: 'building',
			description: 'Bilding number'
		});

		assert.ok(field instanceof FormatField);
		assert.equal(field.type, 222);
		assert.equal(field.sort, 100);
		assert.equal(field.name, 'building');
	});

	describe('get / set', () => {
		it('Should return setted values', () => {
			let field = new FormatField({
				type: 222
			});

			field.sort = 200;
			assert.equal(field.sort, 200);

			field.name = 'building';
			assert.equal(field.name, 'building');
		});
	});
});