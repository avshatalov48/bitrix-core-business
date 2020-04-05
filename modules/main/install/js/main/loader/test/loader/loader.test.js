import { Loader } from '../../src/loader';

describe('main.loader', () => {
	describe('BX.Loader', () => {
		it('Should be a function', () => {
			assert(typeof Loader === 'function');
		});

		it('Should works without options', () => {
			let loader;

			assert.doesNotThrow(() => {
				loader = new Loader();
			});

			assert(loader && loader instanceof Loader);
		});

		it('Should not append layout if loader not shown', () => {
			const target = document.createElement('div');

			void new Loader({ target });

			assert(target.lastElementChild === null);
		});

		it('Should implement public interface', () => {
			const loader = new Loader();

			assert(loader.layout && typeof loader.layout === 'object');
			assert(loader.circle && typeof loader.circle === 'object');
			assert(loader.createLayout() === loader.layout);
			assert(loader.show && typeof loader.show === 'function');
			assert(loader.hide && typeof loader.hide === 'function');
			assert(loader.isShown && typeof loader.isShown === 'function');
			assert(loader.setOptions && typeof loader.setOptions === 'function');
			assert(loader.destroy && typeof loader.destroy === 'function');
		});

		describe('BX.Loader#show', () => {
			it('Should be async', () => {
				const target = document.createElement('div');
				const loader = new Loader({ target });

				assert(typeof loader.show().then === 'function');
			});
		});
	});
});