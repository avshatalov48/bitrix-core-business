import { BBCodeTagScheme } from '../../../src/scheme/node-schemes/tag-scheme';

describe('scheme/node-schemes/tag-scheme', () => {
	let tagScheme;

	beforeEach(() => {
		tagScheme = new BBCodeTagScheme({ name: 'p' });
	});

	it('should set the "void" property', () => {
		tagScheme.setVoid(true);
		assert.strictEqual(tagScheme.isVoid(), true);

		tagScheme.setVoid(false);
		assert.strictEqual(tagScheme.isVoid(), false);
	});

	it('should set the "childConverter" property', () => {
		const converter = (node) => node;
		tagScheme.setChildConverter(converter);
		assert.strictEqual(tagScheme.getChildConverter(), converter);

		tagScheme.setChildConverter(null);
		assert.strictEqual(tagScheme.getChildConverter(), null);
	});

	it('should set the "allowedChildren" property', () => {
		const allowedChildren = ['div', 'span'];
		tagScheme.setAllowedChildren(allowedChildren);
		assert.deepStrictEqual(tagScheme.getAllowedChildren(), allowedChildren);
	});

	it('should set canBeEmpty from constructor options', () => {
		const tagScheme = new BBCodeTagScheme({
			name: 'div',
			canBeEmpty: true,
		});

		assert.ok(tagScheme.canBeEmpty());

		const tagScheme2 = new BBCodeTagScheme({
			name: 'div',
			canBeEmpty: false,
		});

		assert.ok(tagScheme2.canBeEmpty() === false);
	});
});
