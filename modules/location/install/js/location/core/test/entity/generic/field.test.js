import Field from "../../../src/entity/generic/field";

describe('Field', () => {

	it('Should be a function', () => {
		assert(typeof Field === 'function');
	});

	it('Should be constructed successfully', () => {
		let field = new Field({
			type: 222
		});

		assert.ok(field instanceof Field);
		assert.equal(field.type, 222);
	});

	it('Should rise exception in case construction without the type', () => {
		assert.throws(
			() => {
				let field = new Field();
			},
			Error,
			'Field type must be defined'
		);
	});
});