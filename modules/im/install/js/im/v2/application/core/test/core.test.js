import {Core} from "im.v2.application.core";
import {Controller} from "im.v2.controller";

describe('Core application', () => {
	it('should exist', () => {
		assert.equal(typeof Core, 'object');
		assert.equal(Core.controller instanceof Controller, true);
	});

	it ('should have ready function', () => {
		assert.equal(typeof Core.ready, 'function');
		let readyResult = Core.ready();
		assert.equal(readyResult instanceof Promise, true);
	});
});