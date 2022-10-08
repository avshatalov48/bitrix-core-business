/* global assert */
import {Leaflet} from '../src/leaflet';

describe('Leaflet', () => {
	it('Should be a function', () => {
		assert(typeof Leaflet === 'function');
	});
});