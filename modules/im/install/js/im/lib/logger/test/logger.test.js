import 'im.test';

import {Logger} from "im.lib.logger";

let sandbox = null;
beforeEach(() => {
	Logger.disable();
	sandbox = sinon.createSandbox();
});

afterEach(() => {
	sandbox.restore();
});

describe('Lib:Logger', function() {
	it('should exist', function() {
		assert.equal(typeof Logger, 'object');
		assert.equal(Logger.hasOwnProperty('enabled'), true);
	});

	it('can be enabled', function() {
		assert.equal(Logger.enabled, false);
		Logger.enable();
		assert.equal(Logger.enabled, true);
	});

	it('can be disabled', function() {
		Logger.enable();
		assert.equal(Logger.enabled, true);
		Logger.disable();
		assert.equal(Logger.enabled, false);
	});

	describe('log', function() {
		it('should call console.log if enabled', function() {
			sandbox.spy(console, 'log');
			Logger.enable();
			Logger.log();
			assert.equal(console.log.calledOnce, true);
		});

		it('should do nothing if disabled', function() {
			sandbox.spy(console, 'log');
			Logger.log();
			assert.equal(console.log.notCalled, true);
		});
	});

	describe('info', function() {
		it('should call console.info if enabled', function() {
			sandbox.stub(console, 'info');
			Logger.enable();
			Logger.info();
			assert.equal(console.info.calledOnce, true);
		});

		it('should do nothing if disabled', function() {
			sandbox.spy(console, 'info');
			Logger.info();
			assert.equal(console.info.notCalled, true);
		});
	});

	describe('warn', function() {
		it('should call console.warn if enabled', function() {
			sandbox.stub(console, 'warn');
			Logger.enable();
			Logger.warn();
			assert.equal(console.warn.calledOnce, true);
		});

		it('should do nothing if disabled', function() {
			sandbox.stub(console, 'warn');
			Logger.warn();
			assert.equal(console.warn.notCalled, true);
		});
	});

	describe('error', function() {
		it('should call console.error in any state', function() {
			sandbox.stub(console, 'error');
			Logger.enable();
			Logger.error();
			assert.equal(console.error.calledOnce, true);
			Logger.disable();
			Logger.error();
			assert.equal(console.error.calledTwice, true);
		});
	});

	describe('trace', function() {
		it('should call console.trace in any state', function() {
			sandbox.stub(console, 'trace');
			Logger.enable();
			Logger.trace();
			assert.equal(console.trace.calledOnce, true);
			Logger.disable();
			Logger.trace();
			assert.equal(console.trace.calledTwice, true);
		});
	});
});