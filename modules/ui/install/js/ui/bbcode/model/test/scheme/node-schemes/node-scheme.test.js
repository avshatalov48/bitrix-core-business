import { BBCodeNodeScheme } from '../../../src/scheme/node-schemes/node-scheme';

describe('scheme/node-schemes/node-scheme', () => {
	describe('NodeScheme.constructor', () => {
		it('should throw a TypeError if options is not an object', () => {
			assert.throws(() => {
				new BBCodeNodeScheme();
			}, TypeError);
		});

		it('should throw a TypeError if options.name is not specified', () => {
			assert.throws(() => {
				new BBCodeNodeScheme({});
			}, TypeError);
		});
	});

	describe('NodeScheme.setName', () => {
		it('should set the name property to a single string value', () => {
			const scheme = new BBCodeNodeScheme({ name: 'node' });
			assert.deepStrictEqual(scheme.getName(), ['node']);
		});

		it('should set the name property to an array of string values', () => {
			const scheme = new BBCodeNodeScheme({ name: ['node1', 'node2'] });
			assert.deepStrictEqual(scheme.getName(), ['node1', 'node2']);
		});
	});

	describe('NodeScheme.removeName', () => {
		it('should remove the specified names from the name property', () => {
			const scheme = new BBCodeNodeScheme({ name: ['node1', 'node2', 'node3'] });
			scheme.removeName('node2', 'node3');
			assert.deepStrictEqual(scheme.getName(), ['node1']);
		});
	});

	describe('NodeScheme.setStringifier', () => {
		it('should set the stringifier property to a function value', () => {
			const stringifier = (node) => node.toString();
			const scheme = new BBCodeNodeScheme({ name: 'node', stringify: stringifier });
			assert.strictEqual(scheme.getStringifier(), stringifier);
		});

		it('should set the stringifier property to null', () => {
			const scheme = new BBCodeNodeScheme({ name: 'node', stringify: null });
			assert.strictEqual(scheme.getStringifier(), null);
		});
	});

	describe('NodeScheme.setSerializer', () => {
		it('should set the serializer property to a function value', () => {
			const serializer = (node) => JSON.stringify(node);
			const scheme = new BBCodeNodeScheme({ name: 'node', serialize: serializer });
			assert.strictEqual(scheme.getSerializer(), serializer);
		});

		it('should set the serializer property to null', () => {
			const scheme = new BBCodeNodeScheme({ name: 'node', serialize: null });
			assert.strictEqual(scheme.getSerializer(), null);
		});
	});
});
