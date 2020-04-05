import {Env} from '../../src/env';

describe('landing.env', () => {
	it('Should be exported Env function', () => {
		assert(typeof Env === 'function');
	});

	it('Should be a singleton', () => {
		const env1 = Env.getInstance();
		const env2 = Env.getInstance();

		assert.strictEqual(env1, env2);
	});

	describe('Env.getOptions', () => {
		it('Should return default options if instance created without options', () => {
			const options = Env.getInstance().getOptions();
			const defaultOptions = {params: {type: 'EXTERNAL'}};
			
			assert.deepEqual(options, defaultOptions);
		});

		it('Should return options with which instance was created', () => {
			const params = {test1: 1, test2: {name: 'test'}};
			const defaultOptions = {params: {type: 'EXTERNAL'}};

			Env.createInstance(params);

			const options = Env.getInstance().getOptions();
			const expected = {...defaultOptions, ...params};

			assert.deepEqual(options, expected);
		});
	});

	describe('Env.getType', () => {
		it('Should return EXTERNAL if instance created without options', () => {
			Env.createInstance();
			
			assert.strictEqual(Env.getInstance().getType(), 'EXTERNAL');
		});

		it('Should return type from passed options', () => {
			const options = {params: {type: 'TEST'}};
			Env.createInstance(options);

			assert.strictEqual(Env.getInstance().getType(), 'TEST');
		});
	});

	describe('Env.getSiteId', () => {
		it('Should return -1 if instance created without options', () => {
			Env.createInstance();

			assert.strictEqual(Env.getInstance().getSiteId(), -1);
		});

		it('Should return sited from passed options', () => {
			const options = {site_id: 10};
			Env.createInstance(options);

			assert.strictEqual(Env.getInstance().getSiteId(), 10);
		});
	});
});