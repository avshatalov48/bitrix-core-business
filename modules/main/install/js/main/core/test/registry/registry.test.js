import { Registry } from '../../src/lib/event/registry';

describe('Registry', () => {
	it('Should be exported as function', () => {
		assert(typeof Registry === 'function');
	});

	it('Should implement public interface', () => {
		const registry = new Registry();

		assert(typeof registry.set === 'function');
		assert(typeof registry.get === 'function');
		assert(typeof registry.delete === 'function');
	});

	describe('set', () => {
		it('Should set entry if passed correct parameters', () => {
			const registry = new Registry();
			const element = document.createElement('div');
			const event = 'test:event';
			const listener = () => {};

			registry.set(element, event, listener);

			const result = registry.get(element);

			assert(result[event].size === 1);
			assert(result[event].has(listener));
		});
	});

	describe('get', () => {
		it('Should return entry if exists', () => {
			const registry = new Registry();
			const element = document.createElement('div');
			const event = 'test:event';
			const listener = () => {};

			registry.set(element, event, listener);

			const result = registry.get(element);

			assert(result[event].size === 1);
			assert(result[event].has(listener));
		});
	});
	
	describe('delete', () => {
		it('Should delete specified listener', () => {
			const registry = new Registry();
			const element = document.createElement('div');
			const event = 'test:event';
			const listener = () => {};
			const listener2 = () => {};
			const listener3 = () => {};

			registry.set(element, event, listener);
			registry.set(element, event, listener2);
			registry.set(element, event, listener3);
			registry.delete(element, event, listener);

			const result = registry.get(element);

			assert(result[event].size === 2);
			assert(result[event].has(listener) === false);
			assert(result[event].has(listener2) === true);
			assert(result[event].has(listener3) === true);
		});

		it('Should delete all event listeners', () => {
			const registry = new Registry();
			const element = document.createElement('div');
			const event = 'test:event';
			const listener = () => {};
			const listener2 = () => {};
			const listener3 = () => {};

			registry.set(element, event, listener);
			registry.set(element, event, listener2);
			registry.set(element, event, listener3);

			registry.delete(element, event);

			const result = registry.get(element);

			assert(result[event].size === 0);
		});
	});
});