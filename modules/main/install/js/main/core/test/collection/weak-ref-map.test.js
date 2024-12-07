import WeakRefMap from '../../src/lib/collections/weak-ref-map';

describe('Weak Ref Map', () => {
	it('should behave like an ordinary map', () => {
		const map: WeakRefMap<string, Object> = new WeakRefMap();

		const objA = { a: 'aaa' };
		const objB = { b: 'bbb' };

		map.set('a', objA);
		map.set('b', objB);

		assert.equal(map.get('a'), objA);
		assert.equal(map.get('b'), objB);
		assert.equal(map.has('a'), true);
		assert.equal(map.has('b'), true);

		map.delete('a');
		assert.equal(map.get('a'), undefined);
		assert.equal(map.get('b'), objB);
		assert.equal(map.has('a'), false);
		assert.equal(map.has('b'), true);

		map.delete('b');
		assert.equal(map.get('a'), undefined);
		assert.equal(map.get('b'), undefined);
		assert.equal(map.has('a'), false);
		assert.equal(map.has('b'), false);

		map.set('a', objA);
		map.set('b', objB);
		assert.equal(map.get('a'), objA);
		assert.equal(map.get('b'), objB);
		map.clear();
		assert.equal(map.get('a'), undefined);
		assert.equal(map.get('b'), undefined);
		assert.equal(map.has('a'), false);
		assert.equal(map.has('b'), false);
	});

	it('should remove elements after GC', (done) => {
		const map: WeakRefMap<string, Object> = new WeakRefMap();

		let objA = { a: 'aaa' };
		let objB = { b: 'bbb' };

		map.set('a', objA);
		map.set('b', objB);

		assert.equal(map.get('a'), objA);
		assert.equal(map.get('b'), objB);
		assert.equal(map.has('a'), true);
		assert.equal(map.has('b'), true);

		objA = null;
		const tickA = () => {
			global.gc();

			assert.equal(map.get('a'), undefined);
			assert.equal(map.has('a'), false);

			assert.equal(map.get('b'), objB);
			assert.equal(map.has('b'), true);

			setTimeout(tickB, 500);
		}

		const tickB = () => {
			objB = null;
			global.gc();
			assert.equal(map.get('a'), undefined);
			assert.equal(map.get('b'), undefined);
			assert.equal(map.has('a'), false);
			assert.equal(map.has('b'), false);

			done();
		}

		setTimeout(tickA, 500);
	});

	it('should remove elements after GC2', (done) => {
		const map: WeakRefMap<string, Object> = new WeakRefMap();

		let objA = { a: 'a' };
		let objB = { b: 'b' };

		map.set('a', objA);
		map.set('b', objB);

		const tick = () => {
			if (objA !== null)
			{
				objA = null;
				global.gc();

				assert.equal(map.get('a'), undefined);
				assert.equal(map.get('b'), objB);

				return;
			}

			if (objB !== null)
			{
				objB = null;
				global.gc();

				assert.equal(map.get('b'), undefined);

				return;
			}

			assert.equal(map.get('a'), undefined);
			assert.equal(map.get('b'), undefined);

			clearInterval(timer);
			done();
		};

		const timer = setInterval(tick, 500);
	});
});
