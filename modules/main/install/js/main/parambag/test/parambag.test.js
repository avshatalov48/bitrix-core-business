import {ParamBag} from '../src/parambag';

describe('main/parambag', () => {
	it('Should be exported as function', () => {
		assert.ok(typeof ParamBag === 'function');
	});

	it('Should create from plain object', () => {
		const options = {
			param1: 1,
			param2: 'value2',
			param3: {param: 1, param2: 2},
			param4: [1, 2, 3, {test: 1}],
		};

		const params = new ParamBag(options);

		assert.deepEqual(params.getParam('param1'), options.param1);
		assert.deepEqual(params.getParam('param2'), options.param2);
		assert.deepEqual(params.getParam('param3'), options.param3);
		assert.deepEqual(params.getParam('param4'), options.param4);
	});

	it('Should create from array', () => {
		const options = [
			1,
			'value2',
			{param: 1, param2: 2},
			[1, 2, 3, {test: 1}],
		];

		const params = new ParamBag(options);

		assert.deepEqual(params.getParam('0'), options[0]);
		assert.deepEqual(params.getParam('1'), options[1]);
		assert.deepEqual(params.getParam('2'), options[2]);
		assert.deepEqual(params.getParam('3'), options[3]);
	});

	it('Should create without params', () => {
		assert.doesNotThrow(() => {
			void new ParamBag();
		});
	});

	it('Should ignore bad params', () => {
		assert.doesNotThrow(() => {
			void new ParamBag(null);
		});

		assert.doesNotThrow(() => {
			void new ParamBag('test');
		});

		assert.doesNotThrow(() => {
			void new ParamBag(() => true);
		});

		assert.doesNotThrow(() => {
			void new ParamBag(false);
		});

		assert.doesNotThrow(() => {
			void new ParamBag(1);
		});
	});
});