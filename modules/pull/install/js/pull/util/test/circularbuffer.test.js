import { CircularBuffer } from '../src/util';

describe('CircularBuffer', () => {
	describe('getAll returns elements in correct order', () => {
		const buf = new CircularBuffer(3);
		const letters = ['a', 'b', 'c', 'd', 'e'];

		letters.forEach((letter) => {
			buf.push(letter);
		});

		assert.deepEqual(buf.getAll(), ['c', 'd', 'e']);

		const singular = new CircularBuffer(1);

		letters.forEach((letter) => {
			singular.push(letter);
		});

		assert.deepEqual(singular.getAll(), ['e']);
	});
});
