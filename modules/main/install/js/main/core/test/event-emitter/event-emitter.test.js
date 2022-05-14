import { BaseError} from '../../src/core';
import BX from '../old/core/internal/bootstrap';
import { EventEmitter, BaseEvent} from 'main.core.events';

describe('EventEmitter', () => {
	it('Should be exported as function', () => {
		assert(typeof EventEmitter === 'function');
	});

	it('Should implement public interface', () => {
		const emitter = new EventEmitter();

		assert(typeof emitter.subscribe === 'function');
		assert(typeof emitter.subscribeOnce === 'function');
		assert(typeof emitter.emit === 'function');
		assert(typeof emitter.unsubscribe === 'function');
		assert(typeof emitter.getMaxListeners === 'function');
		assert(typeof emitter.setMaxListeners === 'function');
		assert(typeof emitter.getListeners === 'function');
		assert(typeof emitter.incrementMaxListeners === 'function');
		assert(typeof emitter.decrementMaxListeners === 'function');
	});

	describe('subscribe', () => {
		it('Should add event listener', () => {
			const emitter = new EventEmitter();
			const event = 'test:event';
			const listener1 = () => {};
			const listener2 = () => {};
			const listener3 = () => {};

			emitter.subscribe(event, listener1);
			emitter.subscribe(event, listener2);
			emitter.subscribe(event, listener3);

			assert.equal(emitter.getListeners(event).size, 3);
		});

		it('Should add unique listeners only', () => {

			const emitter = new EventEmitter();
			const event = 'test:event';
			const listener = () => {};
			const listener2 = () => {};
			const consoleError = sinon.spy(console, 'error');

			emitter.subscribe(event, listener);
			emitter.subscribe(event, listener);
			emitter.subscribe(event, listener);
			emitter.subscribe(event, listener);

			assert(consoleError.callCount === 3);

			emitter.subscribeOnce(event, listener);
			emitter.subscribeOnce(event, listener);
			emitter.subscribeOnce(event, listener);

			assert(consoleError.callCount === 6);

			emitter.subscribeOnce(event, listener2);
			emitter.subscribeOnce(event, listener2);
			emitter.subscribeOnce(event, listener2);
			emitter.subscribe(event, listener2);
			emitter.subscribe(event, listener2);

			assert(consoleError.callCount === 10);

			consoleError.restore();

			assert.equal(emitter.getListeners(event).size, 2);

			const obj = {};
			const once = sinon.stub();
			EventEmitter.subscribeOnce(obj, 'event:once', once);
			EventEmitter.emit(obj, 'event:once');
			EventEmitter.emit(obj, 'event:once');
			EventEmitter.emit(obj, 'event:once');

			assert.equal(once.callCount, 1);
		});
	});

	describe('unsubscribe', () => {
		it('Should remove specified event listener', () => {
			const emitter = new EventEmitter();
			const event = 'test:event';
			const listener1 = () => {};
			const listener2 = () => {};
			const listener3 = () => {};

			emitter.subscribe(event, listener1);
			emitter.subscribe(event, listener2);
			emitter.subscribe(event, listener3);

			emitter.unsubscribe(event, listener1);

			assert.equal(emitter.getListeners(event).size, 2);
			assert(emitter.getListeners(event).has(listener1) === false);
			assert(emitter.getListeners(event).has(listener2) === true);
			assert(emitter.getListeners(event).has(listener3) === true);
		});
	});

	describe('unsubscribeAll', () => {
		it('Should unsubscribe event listeners', () => {
			const emitter = new EventEmitter();
			const eventName = 'test:event';
			const listener1 = () => {};
			const listener2 = () => {};
			const listener3 = () => {};
			const listener4 = () => {};

			emitter.subscribe(eventName, listener1);
			emitter.subscribe(eventName, listener2);
			emitter.subscribe(eventName, listener3);
			emitter.subscribe(eventName + "2", listener4);

			assert.equal(emitter.getListeners(eventName).size, 3);

			emitter.unsubscribeAll(eventName);

			assert.equal(emitter.getListeners(eventName).size, 0);
		});

		it('Should unsubscribe all event listeners', () => {
			const emitter = new EventEmitter();
			const eventName = 'test:event';
			const eventName2 = 'test:event2';
			const listener1 = () => {};
			const listener2 = () => {};
			const listener3 = () => {};
			const listener4 = () => {};

			emitter.subscribe(eventName, listener1);
			emitter.subscribe(eventName, listener2);
			emitter.subscribe(eventName, listener3);

			emitter.subscribe(eventName2, listener1);
			emitter.subscribe(eventName2, listener2);
			emitter.subscribe(eventName2, listener3);
			emitter.subscribe(eventName2, listener4);

			assert.equal(emitter.getListeners(eventName).size, 3);
			assert.equal(emitter.getListeners(eventName2).size, 4);

			emitter.unsubscribeAll();

			assert.equal(emitter.getListeners(eventName).size, 0);
			assert.equal(emitter.getListeners(eventName2).size, 0);
		});

	});

	describe('emit', () => {
		it('Should call all event listeners', () => {
			const emitter = new EventEmitter();
			emitter.setEventNamespace('Test.Namespace');
			const event = 'test:event';
			const listener1 = sinon.stub();
			const listener2 = sinon.stub();
			const listener3 = sinon.stub();

			emitter.subscribe(event, listener1);
			emitter.subscribe(event, listener2);
			emitter.subscribe(event, listener3);

			emitter.emit(event);

			assert(listener1.calledOnce);
			assert(listener2.calledOnce);
			assert(listener3.calledOnce);
		});

		it('Should not call listener if was unsubscribe by a previous sibling listener', () => {
			const emitter = new EventEmitter();
			emitter.setEventNamespace('Test.Namespace');
			const eventName = 'event:sibling';

			let result = '';
			const listener1 = () => { result += "1"; };
			const listener2 = () => { result += "2"; emitter.unsubscribe(eventName, listener3)};
			const listener3 = () => { result += "3"; };

			emitter.subscribe(eventName, listener1);
			emitter.subscribe(eventName, listener2);
			emitter.subscribe(eventName, listener3);

			emitter.emit(eventName);

			assert.equal(result, '12');
		});

		it('Should execute listeners in a right sequence.', () => {
			let result = '';
			const listener1 = () => { result += "1"; };
			const listener2 = () => { result += "2"; };
			const listener3 = () => { result += "3"; };
			const listener4 = () => { result += "4"; };
			const listener5 = () => { result += "5"; };

			const emitter = new EventEmitter();
			emitter.setEventNamespace('Test.Namespace');
			const eventName = 'event:sequence';
			const globalEventName = `Test.Namespace:${eventName}`;

			emitter.subscribe(eventName, listener1);
			EventEmitter.subscribe(EventEmitter.GLOBAL_TARGET, globalEventName, listener2);
			emitter.subscribe(eventName, listener3);
			EventEmitter.subscribe(EventEmitter.GLOBAL_TARGET, globalEventName, listener4);
			emitter.subscribe(eventName, listener5);

			emitter.emit(eventName);

			assert.equal(result, '12345');
		});

		it('Should call event listeners after each emit call', () => {
			const emitter = new EventEmitter();
			emitter.setEventNamespace('Test.Namespace');
			const event = 'test:event';
			const listener1 = sinon.stub();
			const listener2 = sinon.stub();
			const listener3 = sinon.stub();

			emitter.subscribe(event, listener1);
			emitter.subscribe(event, listener2);
			emitter.subscribe(event, listener3);

			emitter.emit(event);

			assert(listener1.callCount === 1);
			assert(listener2.callCount === 1);
			assert(listener3.callCount === 1);

			emitter.emit(event);

			assert(listener1.callCount === 2);
			assert(listener2.callCount === 2);
			assert(listener3.callCount === 2);

			emitter.emit(event);
			emitter.emit(event);
			emitter.emit(event);

			assert(listener1.callCount === 5);
			assert(listener2.callCount === 5);
			assert(listener3.callCount === 5);
		});

		it('Should not call deleted listeners', () => {
			const emitter = new EventEmitter();
			emitter.setEventNamespace('Test.Namespace');
			const event = 'test:event';
			const listener1 = sinon.stub();
			const listener2 = sinon.stub();
			const listener3 = sinon.stub();

			emitter.subscribe(event, listener1);
			emitter.subscribe(event, listener2);
			emitter.subscribe(event, listener3);

			emitter.emit(event);

			assert(listener1.callCount === 1);
			assert(listener2.callCount === 1);
			assert(listener3.callCount === 1);

			emitter.unsubscribe(event, listener1);
			emitter.emit(event);

			assert(listener1.callCount === 1);
			assert(listener2.callCount === 2);
			assert(listener3.callCount === 2);
		});

		it('Should call listener with valid Event object anyway', async () => {
			const emitter = new EventEmitter();
			emitter.setEventNamespace('Test.Namespace');
			const eventName = "test:event";
			const globalEventName = `Test.Namespace:${eventName}`.toLowerCase();

			await new Promise((resolve) => {
				emitter.subscribe(eventName, (event) => {
					assert(event instanceof BaseEvent);
					assert(event.type === globalEventName);
					assert(event.hasOwnProperty("data"));
					assert(event.defaultPrevented === false);
					assert(event.immediatePropagationStopped === false);
					assert(typeof event.preventDefault === 'function');
					assert(typeof event.stopImmediatePropagation === 'function');
					assert(typeof event.isImmediatePropagationStopped === 'function');
					resolve();
				});
				emitter.emit(eventName);
			});
		});

		it('Should assign props to data if passed plain object', async () => {
			const emitter = new EventEmitter();
			emitter.setEventNamespace('Test.Namespace');
			const eventName = "Test:event";

			await new Promise((resolve) => {
				emitter.subscribe(eventName, (event) => {
					assert(event.data.test1 === 1);
					assert(event.data.test2 === 2);
					resolve();
				});
				emitter.emit(eventName, {test1: 1, test2: 2});
			});
		});

		it('Should add event value to data.event.value if passed not event object and not plain object', async () => {
			const emitter = new EventEmitter();
			emitter.setEventNamespace('Test.Namespace');
			const eventName = "Test:event";

			await new Promise((resolve) => {
				emitter.subscribe(eventName, (event) => {
					assert(Array.isArray(event.data));
					assert(event.data[0] === 1);
					assert(event.data[1] === 2);
					resolve();
				});
				emitter.emit(eventName, [1, 2]);
			});

			await new Promise((resolve) => {
				emitter.subscribe(`${eventName}2`, (event) => {
					assert(typeof event.data === 'string');
					assert(event.data === 'test');
					resolve();
				});
				emitter.emit(`${eventName}2`, 'test');
			});

			await new Promise((resolve) => {
				emitter.subscribe(`${eventName}3`, (event) => {
					assert(typeof event.data === 'boolean');
					assert(event.data === true);
					resolve();
				});
				emitter.emit(`${eventName}3`, true);
			});
		});

/*
		it('Should set event.isTrusted = true if event emitted with instance method', async () => {
			class Emitter extends EventEmitter {}
			const emitter = new Emitter();

			await new Promise((resolve) => {
				emitter.subscribe("test", (event) => {
					assert(event.isTrusted === true);
					resolve();
				});
				emitter.emit("test");
			});
		});

		it('Should set event.isTrusted = false if event emitted with static method', async () => {
			class Emitter extends EventEmitter {}
			const emitter = new Emitter();

			await new Promise((resolve) => {
				emitter.subscribe("test2", (event) => {
					assert(event.isTrusted === false);
					resolve();
				});
				EventEmitter.emit("test2");
			});

			await new Promise((resolve) => {
				emitter.subscribe("test3", (event) => {
					assert(event.isTrusted === false);
					resolve();
				});
				Emitter.emit("test3");
			});
		});
*/

		it('Should set defaultPrevented = true called .preventDefault() in listener', async () => {
			const emitter = new EventEmitter();
			emitter.setEventNamespace('Test.Namespace');

			emitter.subscribe('test4', (event) => {
				event.preventDefault();
			});

			const event = new BaseEvent();

			emitter.emit('test4', event);

			assert(event.isDefaultPrevented() === true);
			assert(event.defaultPrevented === true);
		});

		it('Should set thisArg for listeners', (done) => {

			const eventName = 'My:EventName';
			const obj = {};
			const thisArg = { a: 1 };

			BX.addCustomEvent(eventName, function() {
				assert.equal(this, thisArg);
			});

			EventEmitter.subscribe(eventName, function() {
				assert.equal(this, thisArg);
				done();
			});

			EventEmitter.emit(obj, eventName, {}, { thisArg });
		});
	});

	describe('emitAsync', () => {
		it('Should emit event and return promise', () => {
			const emitter = new EventEmitter();
			emitter.setEventNamespace('Test.Namespace');
			const resultPromise = emitter.emitAsync('test');

			assert.ok(resultPromise instanceof Promise);
		});

		it('Should resolve returned promise with values that returned from listeners', () => {
			const emitter = new EventEmitter();
			emitter.setEventNamespace('Test.Namespace');

			emitter.subscribe('test', () => {
				return 'result-1';
			});

			emitter.subscribe('test', () => {
				return true;
			});

			emitter.subscribe('test', () => {
				return 'test-result-3';
			});

			return emitter
				.emitAsync('test')
				.then((results) => {
					assert.ok(results[0] === 'result-1');
					assert.ok(results[1] === true);
					assert.ok(results[2] === 'test-result-3');
				});
		});

		it('Promise should be resolved, when resolved all promises returned from listeners', () => {
			const emitter = new EventEmitter();
			emitter.setEventNamespace('Test.Namespace');

			emitter.subscribe('test', () => {
				return new Promise((resolve) => {
					setTimeout(() => {
						resolve('value1');
					}, 500);
				});
			});

			emitter.subscribe('test', () => {
				return new Promise((resolve) => {
					setTimeout(() => {
						resolve('value2');
					}, 700);
				});
			});

			emitter.subscribe('test', () => {
				return new Promise((resolve) => {
					setTimeout(() => {
						resolve('value3');
					}, 900);
				});
			});

			return emitter
				.emitAsync('test')
				.then((results) => {
					assert.ok(results[0] === 'value1');
					assert.ok(results[1] === 'value2');
					assert.ok(results[2] === 'value3');
				});
		});

		it('Should reject returned promise if listener throw error', () => {
			const emitter = new EventEmitter();
			emitter.setEventNamespace('Test.Namespace');

			emitter.subscribe('test', () => {
				return Promise.reject(new Error());
			});

			emitter
				.emitAsync('test')
				.then(() => {})
				.catch((err) => {
					assert.ok(err instanceof Error);
				});
		});
	});

	describe('static emitAsync', () => {
		it('Should emit event and return promise', () => {
			const resultPromise = EventEmitter.emitAsync('test-event--1');
			assert.ok(resultPromise instanceof Promise);
		});

		it('Should resolve returned promise with values that returned from listeners', () => {
			const emitter = new EventEmitter();

			emitter.subscribe('test-event-1', () => {
				return 'result-1';
			});

			emitter.subscribe('test-event-1', () => {
				return true;
			});

			emitter.subscribe('test-event-1', () => {
				return 'test-result-3';
			});

			return EventEmitter
				.emitAsync(emitter, 'test-event-1')
				.then((results) => {
					assert.ok(results[0] === 'result-1');
					assert.ok(results[1] === true);
					assert.ok(results[2] === 'test-result-3');
				});
		});

		it('Promise should be resolved, when resolved all promises returned from listeners', () => {
			const emitter = new EventEmitter();

			emitter.subscribe('test-event-2', () => {
				return new Promise((resolve) => {
					setTimeout(() => {
						resolve('value1');
					}, 500);
				});
			});

			emitter.subscribe('test-event-2', () => {
				return new Promise((resolve) => {
					setTimeout(() => {
						resolve('value2');
					}, 700);
				});
			});

			emitter.subscribe('test-event-2', () => {
				return new Promise((resolve) => {
					setTimeout(() => {
						resolve('value3');
					}, 900);
				});
			});

			return EventEmitter
				.emitAsync(emitter, 'test-event-2')
				.then((results) => {
					assert.ok(results[0] === 'value1');
					assert.ok(results[1] === 'value2');
					assert.ok(results[2] === 'value3');
				});
		});

		it('Should reject returned promise if listener throw error', () => {
			const emitter = new EventEmitter();

			emitter.subscribe('test-event-3', () => {
				return Promise.reject(new Error());
			});

			return EventEmitter
				.emitAsync(emitter, 'test-event-3')
				.then(() => {})
				.catch((err) => {
					assert.ok(err instanceof Error);
				});
		});
	});

	describe('subscribeOnce', () => {
		it('Should call listener only once', () => {
			const emitter = new EventEmitter();
			emitter.setEventNamespace('Test.Namespace');
			const event = 'test:event';
			const listener = sinon.stub();

			emitter.subscribeOnce(event, listener);
			emitter.emit(event);
			emitter.emit(event);
			emitter.emit(event);
			emitter.emit(event);

			assert(listener.calledOnce);
		});

		it('Should add only unique listeners', () => {
			const emitter = new EventEmitter();
			emitter.setEventNamespace('Test.Namespace');
			const event = 'test:event';
			const listener = sinon.stub();

			emitter.subscribeOnce(event, listener);
			emitter.subscribeOnce(event, listener);
			emitter.subscribeOnce(event, listener);
			emitter.subscribeOnce(event, listener);

			emitter.emit(event);
			emitter.emit(event);
			emitter.emit(event);
			emitter.emit(event);

			assert(listener.calledOnce);
		});
	});

	describe('setMaxListeners', () => {
		it('Should set max allowed listeners count', () => {
			const emitter = new EventEmitter();
			emitter.setEventNamespace('Test.Namespace');
			const maxListenersCount = 3;

			emitter.setMaxListeners(maxListenersCount);
			emitter.setMaxListeners('onClose', 5);

			assert(emitter.getMaxListeners() === maxListenersCount);
			assert(emitter.getMaxListeners('onXXX') === maxListenersCount);
			assert(emitter.getMaxListeners('onClose') === 5);
		});

		it('Should set max listeners count for the event', () => {
			const emitter = new EventEmitter();
			emitter.setEventNamespace('Test.Namespace');
			const eventName = "MyEventMaxListeners";
			const maxListenersCount = 3;

			emitter.setMaxListeners(eventName, maxListenersCount);

			assert(emitter.getMaxListeners() === EventEmitter.DEFAULT_MAX_LISTENERS);
			assert(emitter.getMaxListeners(eventName) === maxListenersCount);

			assert(EventEmitter.getMaxListeners({}) === EventEmitter.DEFAULT_MAX_LISTENERS);
			assert(EventEmitter.getMaxListeners({}, eventName) === EventEmitter.DEFAULT_MAX_LISTENERS);
		});

		it('Should print warnings if the limit exceeded', (done) => {
			const obj = {};
			const eventName = "limit-subscribers";
			const eventName2 = "limit-subscribers2";
			const listener1 = sinon.stub();
			const listener2 = sinon.stub();
			const listener3 = sinon.stub();
			const listener4 = sinon.stub();

			EventEmitter.setMaxListeners(obj, eventName, 2);
			assert(EventEmitter.getMaxListeners(obj) === EventEmitter.DEFAULT_MAX_LISTENERS);
			assert(EventEmitter.getMaxListeners(obj, eventName) === 2);

			EventEmitter.subscribe(obj, eventName, listener1);
			EventEmitter.subscribe(obj, eventName, listener2);
			EventEmitter.subscribe(obj, eventName, listener3);
			EventEmitter.subscribe(obj, eventName, listener4);

			EventEmitter.emit(obj, eventName);

			EventEmitter.subscribe(obj, eventName2, listener1);
			EventEmitter.subscribe(obj, eventName2, listener2);
			EventEmitter.subscribe(obj, eventName2, listener3);
			EventEmitter.subscribe(obj, eventName2, listener4);

			EventEmitter.emit(obj, eventName2);

			setTimeout(function() {
				done();
			}, 1000);
		});

		it('Should sets max listeners for global target', () => {
			const obj = {};

			assert.equal(EventEmitter.getMaxListeners(), 25);
			assert.equal(EventEmitter.getMaxListeners(obj), EventEmitter.DEFAULT_MAX_LISTENERS);

			EventEmitter.setMaxListeners(55);
			EventEmitter.setMaxListeners('onMyClick', 77);

			assert.equal(EventEmitter.getMaxListeners(), 55);
			assert.equal(EventEmitter.getMaxListeners(obj), EventEmitter.DEFAULT_MAX_LISTENERS);
			assert.equal(EventEmitter.getMaxListeners('onMyClick'), 77);
			assert.equal(EventEmitter.getMaxListeners(obj, 'onMyClick'), EventEmitter.DEFAULT_MAX_LISTENERS);

			EventEmitter.setMaxListeners(obj, 88);
			EventEmitter.setMaxListeners(obj, 'onMyClick', 99);

			assert.equal(EventEmitter.getMaxListeners(), 55);
			assert.equal(EventEmitter.getMaxListeners('onMyClick'), 77);
			assert.equal(EventEmitter.getMaxListeners(obj), 88);
			assert.equal(EventEmitter.getMaxListeners(obj, 'onMyClick'), 99);
			assert.equal(EventEmitter.getMaxListeners(obj, 'onXXX'), 88);
		});
	});

	describe('incrementMaxListeners/decrementMaxListeners', () => {

		it('Should increment/decrement events for the global target', () => {

			const obj = {};
			const eventName = 'onMySpecialEvent';

			const defaultGlobalMaxListeners = EventEmitter.getMaxListeners();
			assert.equal(EventEmitter.getMaxListeners(), defaultGlobalMaxListeners);
			assert.equal(EventEmitter.getMaxListeners(eventName), defaultGlobalMaxListeners);
			assert.equal(EventEmitter.getMaxListeners(obj), EventEmitter.DEFAULT_MAX_LISTENERS);

			EventEmitter.incrementMaxListeners();
			EventEmitter.incrementMaxListeners();
			EventEmitter.incrementMaxListeners();
			EventEmitter.setMaxListeners(eventName, defaultGlobalMaxListeners);

			assert.equal(EventEmitter.getMaxListeners(), defaultGlobalMaxListeners + 3);
			assert.equal(EventEmitter.getMaxListeners(eventName), defaultGlobalMaxListeners);
			assert.equal(EventEmitter.getMaxListeners(obj), EventEmitter.DEFAULT_MAX_LISTENERS);

			EventEmitter.incrementMaxListeners();
			EventEmitter.incrementMaxListeners();
			EventEmitter.incrementMaxListeners(eventName);
			EventEmitter.incrementMaxListeners(eventName);

			assert.equal(EventEmitter.getMaxListeners(), defaultGlobalMaxListeners + 5);
			assert.equal(EventEmitter.getMaxListeners(eventName), defaultGlobalMaxListeners + 2);
			assert.equal(EventEmitter.getMaxListeners(obj), EventEmitter.DEFAULT_MAX_LISTENERS);

			EventEmitter.incrementMaxListeners(3);
			EventEmitter.incrementMaxListeners(eventName);
			EventEmitter.incrementMaxListeners(eventName, 4);

			assert.equal(EventEmitter.getMaxListeners(), defaultGlobalMaxListeners + 8);
			assert.equal(EventEmitter.getMaxListeners(eventName), defaultGlobalMaxListeners + 7);
			assert.equal(EventEmitter.getMaxListeners(obj), EventEmitter.DEFAULT_MAX_LISTENERS);

			EventEmitter.incrementMaxListeners(obj, eventName);
			EventEmitter.incrementMaxListeners(obj, eventName);
			EventEmitter.incrementMaxListeners(obj, eventName, 7);
			EventEmitter.incrementMaxListeners(obj);
			EventEmitter.incrementMaxListeners(obj);
			EventEmitter.incrementMaxListeners(obj, 3);

			assert.equal(EventEmitter.getMaxListeners(), defaultGlobalMaxListeners + 8);
			assert.equal(EventEmitter.getMaxListeners(eventName), defaultGlobalMaxListeners + 7);
			assert.equal(EventEmitter.getMaxListeners(obj), EventEmitter.DEFAULT_MAX_LISTENERS + 5);
			assert.equal(EventEmitter.getMaxListeners(obj, eventName), EventEmitter.DEFAULT_MAX_LISTENERS + 9);

			EventEmitter.decrementMaxListeners(obj, eventName);
			EventEmitter.decrementMaxListeners(obj, eventName, 7);
			EventEmitter.decrementMaxListeners(obj);
			EventEmitter.decrementMaxListeners(obj, 3);

			assert.equal(EventEmitter.getMaxListeners(obj, eventName), EventEmitter.DEFAULT_MAX_LISTENERS + 1);
			assert.equal(EventEmitter.getMaxListeners(obj), EventEmitter.DEFAULT_MAX_LISTENERS + 1);
			assert.equal(EventEmitter.getMaxListeners(), defaultGlobalMaxListeners + 8);
			assert.equal(EventEmitter.getMaxListeners(eventName), defaultGlobalMaxListeners + 7);

			EventEmitter.decrementMaxListeners(3);
			EventEmitter.decrementMaxListeners(eventName);
			EventEmitter.decrementMaxListeners(eventName, 4);

			assert.equal(EventEmitter.getMaxListeners(obj, eventName), EventEmitter.DEFAULT_MAX_LISTENERS + 1);
			assert.equal(EventEmitter.getMaxListeners(obj), EventEmitter.DEFAULT_MAX_LISTENERS + 1);
			assert.equal(EventEmitter.getMaxListeners(), defaultGlobalMaxListeners + 5);
			assert.equal(EventEmitter.getMaxListeners(eventName), defaultGlobalMaxListeners + 2);
		});

		it('Should increment events for an object target', () => {

			const emitter = new EventEmitter();
			const eventName = 'onMyEmitterEvent';

			assert.equal(emitter.getMaxListeners(), EventEmitter.DEFAULT_MAX_LISTENERS);
			assert.equal(emitter.getMaxListeners(eventName), EventEmitter.DEFAULT_MAX_LISTENERS);

			emitter.incrementMaxListeners();
			emitter.incrementMaxListeners();
			emitter.incrementMaxListeners(3);
			emitter.setMaxListeners(eventName, 30);

			assert.equal(emitter.getMaxListeners(), EventEmitter.DEFAULT_MAX_LISTENERS + 5);
			assert.equal(emitter.getMaxListeners(eventName), 30);

			emitter.incrementMaxListeners(eventName);
			emitter.incrementMaxListeners(eventName);
			emitter.incrementMaxListeners(eventName, 3);

			assert.equal(emitter.getMaxListeners(), EventEmitter.DEFAULT_MAX_LISTENERS + 5);
			assert.equal(emitter.getMaxListeners(eventName), 35);

			emitter.decrementMaxListeners();
			emitter.decrementMaxListeners(3);
			emitter.decrementMaxListeners(eventName);
			emitter.decrementMaxListeners(eventName, 2);

			assert.equal(emitter.getMaxListeners(), EventEmitter.DEFAULT_MAX_LISTENERS + 1);
			assert.equal(emitter.getMaxListeners(eventName), 32);
		});

	});

	describe('getMaxListeners', () => {
		it('Should return max listeners count for each event', () => {
			const emitter = new EventEmitter();
			const defaultMaxListenersCount = 10;

			assert(emitter.getMaxListeners() === defaultMaxListenersCount);
		});
	});

	describe('static', () => {
		it('Should implement public static interface', () => {
			assert(typeof EventEmitter.subscribe === 'function');
			assert(typeof EventEmitter.subscribeOnce === 'function');
			assert(typeof EventEmitter.emit === 'function');
			assert(typeof EventEmitter.unsubscribe === 'function');
			assert(typeof EventEmitter.getMaxListeners === 'function');
			assert(typeof EventEmitter.setMaxListeners === 'function');
			assert(typeof EventEmitter.getListeners === 'function');
		});

		it('Should add global event listener', () => {
			const emitter = new EventEmitter();
			emitter.setEventNamespace('Test.Namespace');
			const eventName = 'test:event';
			const listener = sinon.stub();

			EventEmitter.subscribe(emitter, eventName, listener);

			emitter.emit(eventName);

			assert(listener.callCount === 1);

			emitter.emit(eventName);
			emitter.emit(eventName);

			assert(listener.callCount === 3);
		});
	});

	describe('Old custom events', () => {
		it('Should implement public static interface', () => {
			assert(typeof BX.addCustomEvent === 'function');
			assert(typeof BX.onCustomEvent === 'function');
			assert(typeof BX.removeCustomEvent === 'function');
			assert(typeof BX.removeAllCustomEvents === 'function');
		});

		it('Should add an event listener', () => {

			const obj = {};
			const eventName = 'old:add-custom-event';
			const listener1 = sinon.stub();
			const listener2 = sinon.stub();
			const listener3 = sinon.stub();

			BX.addCustomEvent(obj, eventName, listener1);
			BX.addCustomEvent(obj, eventName, listener2);
			BX.addCustomEvent(obj, eventName, listener3);

			assert.equal(EventEmitter.getListeners(obj, eventName).size, 3);

			BX.onCustomEvent(obj, eventName);

			assert(listener1.calledOnce);
			assert(listener2.calledOnce);
			assert(listener3.calledOnce);
		});

		it('Should add global listeners', () => {

			const obj = {};
			const eventName = 'old:add-custom-event';
			const listener1 = sinon.stub();
			const listener2 = sinon.stub();
			const listener3 = sinon.stub();
			const listener4 = sinon.stub();

			BX.addCustomEvent(window, eventName, listener1);
			BX.addCustomEvent(eventName, listener2);
			BX.addCustomEvent(EventEmitter.GLOBAL_TARGET, eventName, listener3);
			BX.addCustomEvent(obj, eventName, listener4);

			assert.equal(EventEmitter.getListeners(EventEmitter.GLOBAL_TARGET, eventName).size, 3);
			assert.equal(EventEmitter.getListeners(eventName).size, 3);

			BX.onCustomEvent(window, eventName);

			assert(listener1.callCount === 1);
			assert(listener2.callCount === 1);
			assert(listener3.callCount === 1);

			BX.onCustomEvent(eventName);

			assert(listener1.callCount === 2);
			assert(listener2.callCount === 2);
			assert(listener3.callCount === 2);

			BX.onCustomEvent(obj, eventName);

			assert(listener1.callCount === 3);
			assert(listener2.callCount === 3);
			assert(listener3.callCount === 3);
			assert(listener4.callCount === 1);
		});

		it('Should invoke event listeners', () => {

			const obj = {};
			const eventName = 'test:event';
			const listener = sinon.stub();

			BX.addCustomEvent(obj, eventName, listener);

			BX.onCustomEvent(obj, eventName);

			assert(listener.callCount === 1);

			BX.onCustomEvent(obj, eventName);
			BX.onCustomEvent(obj, eventName);

			assert(listener.callCount === 3);
		});

		it('Should pass arguments', (done) => {

			const obj = {};
			const eventName = 'test:event';

			const listener = function(a, b, c) {

				assert.equal(a, 1);
				assert.equal(b, obj);
				assert.equal(c, "string");

				done();
			};

			BX.addCustomEvent(obj, eventName, listener);
			BX.onCustomEvent(obj, eventName, [1, obj, "string"]);
		});

		it('Should pass array-like arguments', (done) => {

			const obj = {};
			const eventName = 'test:onChanged';

			const listener = function(a, b, c) {

				assert.equal(a, 1);
				assert.equal(b, obj);
				assert.equal(c, "string");

				done();
			};

			function fireEvent()
			{
				BX.onCustomEvent(obj, eventName, arguments);
			}

			BX.addCustomEvent(obj, eventName, listener);
			fireEvent(1, obj, "string");
		});

		it('Should emit params for old handlers', (done) => {

			const emitter = new EventEmitter();
			emitter.setEventNamespace('TestNamespace');
			const eventName = 'onMyPopupClose';
			const globalEventName = `TestNamespace:${eventName}`;
			const listener = (a, b, c) => {

				assert.equal(a, 1);
				assert.equal(b, emitter);
				assert.equal(c, "string");

				done();
			};

			BX.addCustomEvent(emitter, globalEventName, listener);

			const event = new BaseEvent();
			event.setCompatData([1, emitter, "string"]);

			emitter.emit(eventName, event);
		});

		it('Should emit an event for new handlers', (done) => {

			const emitter = new EventEmitter();
			emitter.setEventNamespace('TestNamespace');
			const eventName = 'onMyPopupClose2';
			const globalEventName = `TestNamespace:${eventName}`;

			const listener = function(event) {
				assert.equal(event.getData(), 2);
				done();
			};

			BX.addCustomEvent(emitter, globalEventName, listener);

			emitter.emit(eventName, 2);
		});

		it('Should emit an event for new subscribers', (done) => {

			const obj = {};
			const eventName = 'test:event';

			EventEmitter.subscribe(obj, eventName, (event) => {

				const [num, instance, str] = event.getData();

				assert.equal(num, 1);
				assert.equal(instance, obj);
				assert.equal(str, "string");

				done();
			});

			BX.onCustomEvent(obj, eventName, [1, obj, "string"]);
		});
	});

	describe('StopImmediatePropagation', () => {
		it('Should stop invoke the rest listeners', () => {

			const emitter = new EventEmitter();
			emitter.setEventNamespace('Test.Namespace');
			const eventName = 'event:stop-propagation';
			const listener1 = sinon.stub();
			const listener2 = (event) => {
				event.stopImmediatePropagation();
			};
			const listener3 = sinon.stub();

			emitter.subscribe(eventName, listener1);
			emitter.subscribe(eventName, listener2);
			emitter.subscribe(eventName, listener3);

			emitter.emit(eventName);

			assert(listener1.callCount === 1);
			assert(listener3.callCount === 0);
		});
	});

	describe('Global Context', () => {
		it('Should add event listeners', () => {
			const eventName = 'event:global-context';
			const listener1 = () => {};
			const listener2 = () => {};
			const listener3 = () => {};

			EventEmitter.subscribe(eventName, listener1);
			EventEmitter.subscribe(eventName, listener2);
			EventEmitter.subscribe(eventName, listener3);

			assert.equal(EventEmitter.getListeners(eventName).size, 3);
			assert.equal(EventEmitter.getListeners(EventEmitter.GLOBAL_TARGET, eventName).size, 3);
		});

		it('Should remove specified event listener', () => {
			const eventName = 'event:global-context-unsubscribe';
			const listener1 = () => {};
			const listener2 = () => {};
			const listener3 = () => {};
			const listener4 = () => {};

			EventEmitter.subscribe(eventName, listener1);
			EventEmitter.subscribe(EventEmitter.GLOBAL_TARGET, eventName, listener2);
			EventEmitter.subscribe(eventName, listener3);
			EventEmitter.subscribe(EventEmitter.GLOBAL_TARGET, eventName, listener4);

			assert.equal(EventEmitter.getListeners(eventName).size, 4);
			assert.equal(EventEmitter.getListeners(EventEmitter.GLOBAL_TARGET, eventName).size, 4);

			EventEmitter.unsubscribe(eventName, listener1);
			EventEmitter.unsubscribe(EventEmitter.GLOBAL_TARGET, eventName, listener3);

			assert.equal(EventEmitter.getListeners(eventName).size, 2);
			assert.equal(EventEmitter.getListeners(EventEmitter.GLOBAL_TARGET, eventName).size, 2);

			assert(EventEmitter.getListeners(eventName).has(listener1) === false);
			assert(EventEmitter.getListeners(EventEmitter.GLOBAL_TARGET, eventName).has(listener1) === false);

			assert(EventEmitter.getListeners(eventName).has(listener2) === true);
			assert(EventEmitter.getListeners(EventEmitter.GLOBAL_TARGET, eventName).has(listener2) === true);

			assert(EventEmitter.getListeners(eventName).has(listener3) === false);
			assert(EventEmitter.getListeners(EventEmitter.GLOBAL_TARGET, eventName).has(listener3) === false);

			assert(EventEmitter.getListeners(eventName).has(listener4) === true);
			assert(EventEmitter.getListeners(EventEmitter.GLOBAL_TARGET, eventName).has(listener4) === true);
		});

		it('Should remove all event listeners', () => {
			const eventName = 'event:global-context-unsubscribe-all';
			const eventName2 = 'event:global-context-unsubscribe-all2';
			const listener1 = () => {};
			const listener2 = () => {};
			const listener3 = () => {};
			const listener4 = () => {};

			EventEmitter.subscribe(eventName, listener1);
			EventEmitter.subscribe(EventEmitter.GLOBAL_TARGET, eventName, listener2);
			EventEmitter.subscribe(eventName, listener3);
			EventEmitter.subscribe(EventEmitter.GLOBAL_TARGET, eventName, listener4);

			EventEmitter.subscribe(eventName2, listener1);
			EventEmitter.subscribe(EventEmitter.GLOBAL_TARGET, eventName2, listener2);
			EventEmitter.subscribe(eventName2, listener3);

			assert.equal(EventEmitter.getListeners(eventName).size, 4);
			assert.equal(EventEmitter.getListeners(EventEmitter.GLOBAL_TARGET, eventName).size, 4);

			assert.equal(EventEmitter.getListeners(eventName2).size, 3);
			assert.equal(EventEmitter.getListeners(EventEmitter.GLOBAL_TARGET, eventName2).size, 3);

			EventEmitter.unsubscribeAll(eventName);

			assert.equal(EventEmitter.getListeners(eventName).size, 0);
			assert.equal(EventEmitter.getListeners(EventEmitter.GLOBAL_TARGET, eventName).size, 0);

			assert.equal(EventEmitter.getListeners(eventName2).size, 3);
			assert.equal(EventEmitter.getListeners(EventEmitter.GLOBAL_TARGET, eventName2).size, 3);

			EventEmitter.unsubscribeAll(eventName2);

			assert.equal(EventEmitter.getListeners(eventName).size, 0);
			assert.equal(EventEmitter.getListeners(EventEmitter.GLOBAL_TARGET, eventName).size, 0);

			assert.equal(EventEmitter.getListeners(eventName2).size, 0);
			assert.equal(EventEmitter.getListeners(EventEmitter.GLOBAL_TARGET, eventName2).size, 0);
		});

		it('setMaxListeners', () => {

			EventEmitter.setMaxListeners(111);

			assert.equal(EventEmitter.getMaxListeners(), 111);
			assert.equal(EventEmitter.getMaxListeners(EventEmitter.GLOBAL_TARGET), 111);

			EventEmitter.setMaxListeners(EventEmitter.GLOBAL_TARGET, 222);

			assert.equal(EventEmitter.getMaxListeners(), 222);
			assert.equal(EventEmitter.getMaxListeners(EventEmitter.GLOBAL_TARGET), 222);
		});

		it('subscribeOnce', () => {

			const eventName = 'test:event';
			const listener = () => {};
			const listener2 = () => {};
			const listener3 = () => {};
			const listener4 = () => {};

			EventEmitter.subscribe(eventName, listener);
			EventEmitter.subscribe(eventName, listener);
			EventEmitter.subscribe(eventName, listener);
			EventEmitter.subscribeOnce(eventName, listener);
			EventEmitter.subscribeOnce(eventName, listener);
			EventEmitter.subscribeOnce(eventName, listener);

			EventEmitter.subscribeOnce(eventName, listener2);
			EventEmitter.subscribeOnce(eventName, listener2);
			EventEmitter.subscribeOnce(eventName, listener2);
			EventEmitter.subscribe(eventName, listener2);
			EventEmitter.subscribe(eventName, listener2);

			EventEmitter.subscribe(EventEmitter.GLOBAL_TARGET, eventName, listener3);
			EventEmitter.subscribe(EventEmitter.GLOBAL_TARGET, eventName, listener3);
			EventEmitter.subscribe(EventEmitter.GLOBAL_TARGET, eventName, listener3);
			EventEmitter.subscribeOnce(EventEmitter.GLOBAL_TARGET, eventName, listener3);
			EventEmitter.subscribeOnce(EventEmitter.GLOBAL_TARGET, eventName, listener3);
			EventEmitter.subscribeOnce(EventEmitter.GLOBAL_TARGET, eventName, listener3);

			EventEmitter.subscribeOnce(EventEmitter.GLOBAL_TARGET, eventName, listener4);
			EventEmitter.subscribeOnce(EventEmitter.GLOBAL_TARGET, eventName, listener4);
			EventEmitter.subscribeOnce(EventEmitter.GLOBAL_TARGET, eventName, listener4);
			EventEmitter.subscribe(EventEmitter.GLOBAL_TARGET, eventName, listener4);
			EventEmitter.subscribe(EventEmitter.GLOBAL_TARGET, eventName, listener4);

			assert.equal(EventEmitter.getListeners(eventName).size, 4);
			assert.equal(EventEmitter.getListeners(EventEmitter.GLOBAL_TARGET, eventName).size, 4);

		});

		it('emitSync', (done) => {

			const eventName = 'event:async';
			const listener1 = () => {
				return new Promise((resolve) => {
					setTimeout(() => {
						resolve('value1');
					}, 500);
				});
			};
			const listener2 = () => {
				return new Promise((resolve) => {
					setTimeout(() => {
						resolve('value2');
					}, 700);
				});
			};
			const listener3 = () => {
				return new Promise((resolve) => {
					setTimeout(() => {
						resolve('value3');
					}, 900);
				});
			};

			EventEmitter.subscribe(eventName, listener1);
			EventEmitter.subscribe(EventEmitter.GLOBAL_TARGET, eventName, listener2);
			EventEmitter.subscribe(eventName, listener3);

			EventEmitter
				.emitAsync(eventName)
				.then((results) => {
					assert.ok(results[0] === 'value1');
					assert.ok(results[1] === 'value2');
					assert.ok(results[2] === 'value3');

					done();
				});
		});
	});

	describe('Event Namespace', () => {

		it('Should subscribe on a short event name', () => {
			const emitter = new EventEmitter();
			emitter.setEventNamespace('MyCompany.MyModule.MyClass');
			const eventName = 'onOpen';

			const listener1 = sinon.stub();
			const listener2 = sinon.stub().callsFake(function(event) {
				assert.equal(event.getType(), 'MyCompany.MyModule.MyClass:onOpen'.toLowerCase());
			});

			const listener3 = sinon.stub();
			const listener4 = sinon.stub().callsFake(function(event) {
				assert.equal(event.getType(), 'MyCompany.MyModule.MyClass:onOpen'.toLowerCase());
			});

			emitter.subscribe(eventName, listener1);
			EventEmitter.subscribe('MyCompany.MyModule.MyClass:onOpen', listener2);
			EventEmitter.subscribe(emitter, 'onOpen', listener3);
			emitter.subscribe(eventName, listener4);

			emitter.emit(eventName);

			assert.equal(listener1.callCount, 1);
			assert.equal(listener2.callCount, 1);
			assert.equal(listener3.callCount, 1);
			assert.equal(listener4.callCount, 1);
		});

		it('Should subscribe on a full event name if a namespace is empty', () => {
			const emitter = new EventEmitter();
			const eventName = 'MyCompany.MyModule.MyClass:onOpen';

			const listener1 = sinon.stub();
			const listener2 = sinon.stub();
			const listener3 = sinon.stub();

			emitter.subscribe(eventName, listener1);
			EventEmitter.subscribe('MyCompany.MyModule.MyClass:onOpen', listener2);
			EventEmitter.subscribe(emitter, 'MyCompany.MyModule.MyClass:onOpen', listener3);

			const consoleWarn = sinon.spy(console, 'warn');

			emitter.emit(eventName);

			assert(consoleWarn.callCount === 1);
			consoleWarn.restore();

			assert(listener1.callCount === 1);
			assert(listener2.callCount === 1);
			assert(listener3.callCount === 1);
		});

		it('Should subscribe on a plain object with a full event name', () => {
			const obj = {};
			const eventName = 'MyCompany.MyModule.MyObject:onOpen';

			const listener1 = sinon.stub().callsFake(function(a, b, c) {
				assert(a === 1);
				assert(b === 'string');
				assert(c === obj);
				assert(this === obj);
			});

			const listener2 = sinon.stub().callsFake(function(event) {

				const { a, b, c } = event.getData();

				assert.equal(a, 2);
				assert.equal(b, 'string2');
				assert(c === obj);
				assert(event.getTarget() === obj);
				assert.equal(event.getType(), 'MyCompany.MyModule.MyObject:onOpen'.toLowerCase());
			});

			const listener3 = sinon.stub().callsFake(function(event) {

				const { a, b, c } = event.getData();

				assert.equal(a, 2);
				assert.equal(b, 'string2');
				assert(c === obj);
				assert(event.getTarget() === obj);
			});

			BX.addCustomEvent(obj, eventName, listener1);
			EventEmitter.subscribe('MyCompany.MyModule.MyObject:onOpen', listener2);
			EventEmitter.subscribe(obj, 'MyCompany.MyModule.MyObject:onOpen', listener3);

			EventEmitter.emit(
				obj,
				'MyCompany.MyModule.MyObject:onOpen',
				new BaseEvent({ compatData: [1, "string", obj], data: { a: 2, b: 'string2', c: obj } })
			);

			assert(listener1.callCount === 1);
			assert(listener2.callCount === 1);
			assert(listener3.callCount === 1);
		});
	});

	describe('Aliases', () => {

		EventEmitter.registerAliases({
			onPopupClose: { namespace: 'MyCompany.MyModule.MyPopup', eventName: 'onClose' },
			onPopupOpen: { namespace: 'MyCompany.MyModule.MyPopup', eventName: 'onOpen' },
			onPopupHide: { namespace: 'MyCompany.MyModule.MyPopup', eventName: 'onHide' },
			onPopupDestroy: { namespace: 'MyCompany.MyModule.MyPopup', eventName: 'onDestroy' },
		});

		class MyPopup extends EventEmitter
		{
			constructor()
			{
				super();
				this.setEventNamespace('MyCompany.MyModule.MyPopup');
			}

			show()
			{
				this.emit('onOpen');
			}

			close()
			{
				this.emit('onClose');
			}

			destroy()
			{
				this.emit('onDestroy');
			}
		}

		class MySlider extends EventEmitter
		{
			constructor()
			{
				super();
				this.setEventNamespace('MyCompany.MyModule.MySlider');
			}

			show()
			{
				this.emit('onOpen');
			}

			close()
			{
				this.emit('onClose');
			}
		}

		it('Should subscribe and unsubscribe old event names', () => {

			const onClose1 = sinon.stub();
			const onClose2 = sinon.stub();
			const onClose3Once = sinon.stub();
			const onClose4 = sinon.stub();
			const onClose5Once = sinon.stub();
			const onDestroy = sinon.stub();
			const onDestroyGlobal = sinon.stub();

			const onOpen1 = sinon.stub();
			const onOpen2 = sinon.stub();
			const onOpen3 = sinon.stub();

			const onHide1Once = sinon.stub();
			const onHide2 = sinon.stub();
			const onHide3 = sinon.stub();
			const onHide4 = sinon.stub();
			const onHide5 = sinon.stub();

			BX.addCustomEvent('onPopupClose', onClose1);
			EventEmitter.subscribe('onPopupClose', onClose2);
			EventEmitter.subscribeOnce('onPopupClose', onClose3Once);

			BX.addCustomEvent('onPopupOpen', onOpen1);
			EventEmitter.subscribe('MyCompany.MyModule.MyPopup:onOpen', onOpen2);

			EventEmitter.subscribeOnce('MyCompany.MyModule.MyPopup:onHide', onHide1Once);
			BX.addCustomEvent('onPopupHide', onHide2);

			assert.equal(EventEmitter.getListeners('onPopupClose').size, 3);
			assert.equal(EventEmitter.getListeners('onPopupOpen').size, 2);
			assert.equal(EventEmitter.getListeners('onPopupHide').size, 2);
			assert.equal(EventEmitter.getListeners('MyCompany.MyModule.MyPopup:onClose').size, 3);
			assert.equal(EventEmitter.getListeners('MyCompany.MyModule.MyPopup:onOpen').size, 2);
			assert.equal(EventEmitter.getListeners('MyCompany.MyModule.MyPopup:onHide').size, 2);

			const popup = new MyPopup();
			popup.subscribe('onClose', onClose4);
			popup.subscribeOnce('onClose', onClose5Once);
			popup.subscribe('onOpen', onOpen3);
			popup.subscribe('onHide', onHide3);
			popup.subscribe('onHide', onHide4);
			popup.subscribe('onHide', onHide5);

			BX.addCustomEvent('onPopupDestroy', onDestroyGlobal);
			BX.addCustomEvent(popup, 'onPopupDestroy', onDestroy);

			assert.equal(popup.getListeners('onClose').size, 2);
			assert.equal(popup.getListeners('onOpen').size, 1);
			assert.equal(popup.getListeners('onHide').size, 3);
			assert.equal(popup.getListeners('onDestroy').size, 1);

			assert.equal(EventEmitter.getListeners('onPopupClose').size, 3);
			assert.equal(EventEmitter.getListeners('onPopupOpen').size, 2);
			assert.equal(EventEmitter.getListeners('onPopupDestroy').size, 1);
			assert.equal(EventEmitter.getListeners('MyCompany.MyModule.MyPopup:onClose').size, 3);
			assert.equal(EventEmitter.getListeners('MyCompany.MyModule.MyPopup:onOpen').size, 2);
			assert.equal(EventEmitter.getListeners('MyCompany.MyModule.MyPopup:onHide').size, 2);
			assert.equal(EventEmitter.getListeners('MyCompany.MyModule.MyPopup:onDestroy').size, 1);

			popup.show();
			popup.close();

			assert.equal(onClose1.callCount, 1);
			assert.equal(onClose2.callCount, 1);
			assert.equal(onClose3Once.callCount, 1);
			assert.equal(onClose4.callCount, 1);
			assert.equal(onClose5Once.callCount, 1);
			assert.equal(onOpen1.callCount, 1);
			assert.equal(onOpen2.callCount, 1);
			assert.equal(onOpen3.callCount, 1);
			assert.equal(onHide1Once.callCount, 0);
			assert.equal(onHide2.callCount, 0);
			assert.equal(onHide3.callCount, 0);
			assert.equal(onDestroy.callCount, 0);
			assert.equal(onDestroyGlobal.callCount, 0);

			popup.show();
			popup.close();
			popup.destroy();

			assert.equal(onClose1.callCount, 2);
			assert.equal(onClose2.callCount, 2);
			assert.equal(onClose3Once.callCount, 1);
			assert.equal(onClose4.callCount, 2);
			assert.equal(onClose5Once.callCount, 1);
			assert.equal(onOpen1.callCount, 2);
			assert.equal(onOpen2.callCount, 2);
			assert.equal(onOpen3.callCount, 2);
			assert.equal(onHide1Once.callCount, 0);
			assert.equal(onHide2.callCount, 0);
			assert.equal(onHide3.callCount, 0);
			assert.equal(onDestroy.callCount, 1);
			assert.equal(onDestroyGlobal.callCount, 1);

			BX.onCustomEvent('onPopupClose');
			BX.onCustomEvent(popup, 'onPopupClose');
			BX.onCustomEvent('MyCompany.MyModule.MyPopup:onClose');
			BX.onCustomEvent(popup, 'onPopupClose');
			BX.onCustomEvent('MyCompany.MyModule.MyPopup:onOpen');
			BX.onCustomEvent(popup, 'onPopupOpen');

			BX.onCustomEvent('onPopupDestroy');
			BX.onCustomEvent(popup, 'onPopupDestroy');
			BX.onCustomEvent('MyCompany.MyModule.MyPopup:onDestroy');

			assert.equal(onClose1.callCount, 6);
			assert.equal(onClose2.callCount, 6);
			assert.equal(onClose3Once.callCount, 1);
			assert.equal(onClose4.callCount, 4);
			assert.equal(onClose5Once.callCount, 1);
			assert.equal(onOpen1.callCount, 4);
			assert.equal(onOpen2.callCount, 4);
			assert.equal(onOpen3.callCount, 3);
			assert.equal(onHide1Once.callCount, 0);
			assert.equal(onHide2.callCount, 0);
			assert.equal(onHide3.callCount, 0);

			assert.equal(onDestroy.callCount, 2);
			assert.equal(onDestroyGlobal.callCount, 4);

			EventEmitter.emit('onPopupClose');
			EventEmitter.emit(popup, 'onClose');
			EventEmitter.emit('MyCompany.MyModule.MyPopup:onOpen');

			EventEmitter.emit('onPopupDestroy');
			EventEmitter.emit(popup, 'onDestroy');
			EventEmitter.emit('MyCompany.MyModule.MyPopup:onDestroy');

			assert.equal(onClose1.callCount, 8);
			assert.equal(onClose2.callCount, 8);
			assert.equal(onClose3Once.callCount, 1);
			assert.equal(onClose4.callCount, 5);
			assert.equal(onClose5Once.callCount, 1);
			assert.equal(onOpen1.callCount, 5);
			assert.equal(onOpen2.callCount, 5);
			assert.equal(onOpen3.callCount, 3);
			assert.equal(onHide1Once.callCount, 0);
			assert.equal(onHide2.callCount, 0);
			assert.equal(onHide3.callCount, 0);

			assert.equal(onDestroy.callCount, 3);
			assert.equal(onDestroyGlobal.callCount, 7);

			assert.equal(popup.getListeners('onClose').size, 1);
			assert.equal(popup.getListeners('onDestroy').size, 1);
			assert.equal(EventEmitter.getListeners('onPopupClose').size, 2);
			assert.equal(EventEmitter.getListeners('MyCompany.MyModule.MyPopup:onHide').size, 2);

			BX.removeCustomEvent('onPopupClose', onClose1);
			BX.removeCustomEvent('onPopupOpen', onOpen1);
			BX.removeCustomEvent('onPopupHide', onHide1Once);

			BX.removeCustomEvent(popup, 'onPopupDestroy', onDestroy);

			assert.equal(popup.getListeners('onClose').size, 1);
			assert.equal(popup.getListeners('onOpen').size, 1);
			assert.equal(popup.getListeners('onHide').size, 3);
			assert.equal(EventEmitter.getListeners('onPopupClose').size, 1);
			assert.equal(EventEmitter.getListeners('onPopupOpen').size, 1);
			assert.equal(EventEmitter.getListeners('MyCompany.MyModule.MyPopup:onClose').size, 1);
			assert.equal(EventEmitter.getListeners('MyCompany.MyModule.MyPopup:onOpen').size, 1);
			assert.equal(EventEmitter.getListeners('MyCompany.MyModule.MyPopup:onHide').size, 1);

			assert.equal(popup.getListeners('onDestroy').size, 0);
			assert.equal(EventEmitter.getListeners('onPopupDestroy').size, 1);
			BX.removeCustomEvent('onPopupDestroy', onDestroyGlobal);
			assert.equal(popup.getListeners('onDestroy').size, 0);
			assert.equal(EventEmitter.getListeners('onPopupDestroy').size, 0);

			EventEmitter.unsubscribe('onPopupClose', onClose2);
			EventEmitter.unsubscribe('MyCompany.MyModule.MyPopup:onClose', onClose3Once);
			EventEmitter.unsubscribe('MyCompany.MyModule.MyPopup:onOpen', onOpen2);
			EventEmitter.unsubscribe('MyCompany.MyModule.MyPopup:onHide', onHide2);

			assert.equal(popup.getListeners('onClose').size, 1);
			assert.equal(popup.getListeners('onOpen').size, 1);
			assert.equal(popup.getListeners('onHide').size, 3);

			assert.equal(EventEmitter.getListeners('onPopupClose').size, 0);
			assert.equal(EventEmitter.getListeners('onPopupOpen').size, 0);
			assert.equal(EventEmitter.getListeners('MyCompany.MyModule.MyPopup:onClose').size, 0);
			assert.equal(EventEmitter.getListeners('MyCompany.MyModule.MyPopup:onOpen').size, 0);
			assert.equal(EventEmitter.getListeners('MyCompany.MyModule.MyPopup:onHide').size, 0);

			popup.unsubscribe('onClose', onClose4);
			popup.unsubscribe('onClose', onClose5Once);
			popup.unsubscribe('onOpen', onOpen3);

			assert.equal(popup.getListeners('onClose').size, 0);
			assert.equal(popup.getListeners('onOpen').size, 0);
			assert.equal(popup.getListeners('onHide').size, 3);

			popup.unsubscribeAll('onHide');

			assert.equal(popup.getListeners('onHide').size, 0);
		});

		it('Should unsubscribe all event names', () => {
			const listener1 = sinon.stub();
			const listener2 = sinon.stub();
			const listener3 = sinon.stub();
			const listener4 = sinon.stub();
			const listener5 = sinon.stub();
			const listener6 = sinon.stub();
			const listener7 = sinon.stub();
			const listener8 = sinon.stub();
			const listenerOnce1 = sinon.stub();
			const listenerOnce2 = sinon.stub();
			const listenerOnce3 = sinon.stub();

			BX.addCustomEvent('onPopupClose', listener1);
			BX.addCustomEvent('onPopupOpen', listener2);
			BX.addCustomEvent('onPopupHide', listener7);

			EventEmitter.subscribe('onPopupClose', listener3);
			EventEmitter.subscribeOnce('onPopupClose', listenerOnce1);
			EventEmitter.subscribe('MyCompany.MyModule.MyPopup:onOpen', listener4);
			EventEmitter.subscribeOnce('MyCompany.MyModule.MyPopup:onHide', listenerOnce3);

			const popup = new MyPopup();
			popup.subscribe('onClose', listener5);
			popup.subscribeOnce('onClose', listenerOnce2);
			popup.subscribe('onOpen', listener6);
			popup.subscribe('onHide', listener8);

			assert.equal(EventEmitter.getListeners('onPopupClose').size, 3);
			assert.equal(EventEmitter.getListeners('MyCompany.MyModule.MyPopup:onClose').size, 3);
			assert.equal(EventEmitter.getListeners('onPopupOpen').size, 2);
			assert.equal(EventEmitter.getListeners('MyCompany.MyModule.MyPopup:onOpen').size, 2);
			assert.equal(EventEmitter.getListeners('onPopupHide').size, 2);
			assert.equal(EventEmitter.getListeners('MyCompany.MyModule.MyPopup:onHide').size, 2);

			assert.equal(popup.getListeners('onClose').size, 2);
			assert.equal(popup.getListeners('onOpen').size, 1);
			assert.equal(popup.getListeners('onHide').size, 1);

			popup.unsubscribeAll('onClose');

			assert.equal(popup.getListeners('onClose').size, 0);
			assert.equal(popup.getListeners('onOpen').size, 1);
			assert.equal(popup.getListeners('onHide').size, 1);
			assert.equal(EventEmitter.getListeners('onPopupClose').size, 3);
			assert.equal(EventEmitter.getListeners('MyCompany.MyModule.MyPopup:onClose').size, 3);
			assert.equal(EventEmitter.getListeners('onPopupOpen').size, 2);
			assert.equal(EventEmitter.getListeners('MyCompany.MyModule.MyPopup:onOpen').size, 2);
			assert.equal(EventEmitter.getListeners('onPopupHide').size, 2);
			assert.equal(EventEmitter.getListeners('MyCompany.MyModule.MyPopup:onHide').size, 2);

			EventEmitter.unsubscribeAll('MyCompany.MyModule.MyPopup:onClose');

			assert.equal(popup.getListeners('onClose').size, 0);
			assert.equal(popup.getListeners('onOpen').size, 1);
			assert.equal(popup.getListeners('onHide').size, 1);
			assert.equal(EventEmitter.getListeners('onPopupClose').size, 0);
			assert.equal(EventEmitter.getListeners('MyCompany.MyModule.MyPopup:onClose').size, 0);
			assert.equal(EventEmitter.getListeners('onPopupOpen').size, 2);
			assert.equal(EventEmitter.getListeners('MyCompany.MyModule.MyPopup:onOpen').size, 2);
			assert.equal(EventEmitter.getListeners('onPopupHide').size, 2);
			assert.equal(EventEmitter.getListeners('MyCompany.MyModule.MyPopup:onHide').size, 2);

			EventEmitter.unsubscribeAll('onPopupHide');

			assert.equal(popup.getListeners('onClose').size, 0);
			assert.equal(popup.getListeners('onOpen').size, 1);
			assert.equal(popup.getListeners('onHide').size, 1);
			assert.equal(EventEmitter.getListeners('onPopupClose').size, 0);
			assert.equal(EventEmitter.getListeners('MyCompany.MyModule.MyPopup:onClose').size, 0);
			assert.equal(EventEmitter.getListeners('onPopupOpen').size, 2);
			assert.equal(EventEmitter.getListeners('MyCompany.MyModule.MyPopup:onOpen').size, 2);
			assert.equal(EventEmitter.getListeners('onPopupHide').size, 0);
			assert.equal(EventEmitter.getListeners('MyCompany.MyModule.MyPopup:onHide').size, 0);

			popup.unsubscribeAll();
			EventEmitter.unsubscribeAll('onPopupOpen');

			assert.equal(popup.getListeners('onClose').size, 0);
			assert.equal(popup.getListeners('onOpen').size, 0);
			assert.equal(popup.getListeners('onHide').size, 0);
			assert.equal(EventEmitter.getListeners('onPopupClose').size, 0);
			assert.equal(EventEmitter.getListeners('MyCompany.MyModule.MyPopup:onClose').size, 0);
			assert.equal(EventEmitter.getListeners('onPopupOpen').size, 0);
			assert.equal(EventEmitter.getListeners('MyCompany.MyModule.MyPopup:onOpen').size, 0);
			assert.equal(EventEmitter.getListeners('onPopupHide').size, 0);
			assert.equal(EventEmitter.getListeners('MyCompany.MyModule.MyPopup:onHide').size, 0);
		});

		it('Should rebuild event map after an alias registration', () => {
			const listener1 = sinon.stub();
			const listener2 = sinon.stub();
			const listener3 = sinon.stub();
			const listener4 = sinon.stub();
			const listener5 = sinon.stub();
			const listener6 = sinon.stub();
			const listener7 = sinon.stub();
			const listener8 = sinon.stub();
			const listener9 = sinon.stub();
			const listener10 = sinon.stub();
			const listener11 = sinon.stub();
			const listener12 = sinon.stub();

			BX.addCustomEvent('onSliderClose', listener1);
			BX.addCustomEvent('onSliderOpen', listener2);
			BX.addCustomEvent('onSliderHide', listener3);

			EventEmitter.setMaxListeners('onSliderOpen', 33);
			EventEmitter.setMaxListeners('MyCompany.MyModule.MySlider:onOpen', 66);
			EventEmitter.setMaxListeners('onSliderClose', 99);
			EventEmitter.setMaxListeners('MyCompany.MyModule.MySlider:onHide', 10);

			EventEmitter.subscribe('onSliderClose', listener4);
			EventEmitter.subscribeOnce('onSliderClose', listener5);
			EventEmitter.subscribe('MyCompany.MyModule.MySlider:onClose', listener11);
			EventEmitter.subscribe('MyCompany.MyModule.MySlider:onClose', listener12);
			EventEmitter.subscribe('MyCompany.MyModule.MySlider:onOpen', listener6);
			EventEmitter.subscribe('MyCompany.MyModule.MySlider:onOpen', listener11);

			assert.equal(EventEmitter.getListeners('onSliderClose').size, 3);
			assert.equal(EventEmitter.getListeners('MyCompany.MyModule.MySlider:onClose').size, 2);
			assert.equal(EventEmitter.getListeners('onSliderOpen').size, 1);
			assert.equal(EventEmitter.getListeners('MyCompany.MyModule.MySlider:onOpen').size, 2);
			assert.equal(EventEmitter.getListeners('onSliderHide').size, 1);
			assert.equal(EventEmitter.getListeners('MyCompany.MyModule.MySlider:onHide').size, 0);


			const globalTargetMaxListeners = EventEmitter.getMaxListeners();
			assert.equal(EventEmitter.getMaxListeners('onSliderOpen'), 33);
			assert.equal(EventEmitter.getMaxListeners('MyCompany.MyModule.MySlider:onOpen'), 66);
			assert.equal(EventEmitter.getMaxListeners('onSliderClose'), 99);
			assert.equal(EventEmitter.getMaxListeners('MyCompany.MyModule.MySlider:onClose'), globalTargetMaxListeners);
			assert.equal(EventEmitter.getMaxListeners('MyCompany.MyModule.MySlider:onHide'), 10);
			assert.equal(EventEmitter.getMaxListeners('onSliderHide'), globalTargetMaxListeners);

			EventEmitter.registerAliases({
				onSliderClose: { namespace: 'MyCompany.MyModule.MySlider', eventName: 'onClose' },
				onSliderOpen: { namespace: 'MyCompany.MyModule.MySlider', eventName: 'onOpen' },
				onSliderHide: { namespace: 'MyCompany.MyModule.MySlider', eventName: 'onHide' },
			});

			EventEmitter.subscribeOnce('MyCompany.MyModule.MySlider:onHide', listener7);

			assert.equal(EventEmitter.getListeners('onSliderClose').size, 5);
			assert.equal(EventEmitter.getListeners('MyCompany.MyModule.MySlider:onClose').size, 5);
			assert.equal(EventEmitter.getListeners('onSliderOpen').size, 3);
			assert.equal(EventEmitter.getListeners('MyCompany.MyModule.MySlider:onOpen').size, 3);
			assert.equal(EventEmitter.getListeners('onSliderHide').size, 2);
			assert.equal(EventEmitter.getListeners('MyCompany.MyModule.MySlider:onHide').size, 2);

			assert.equal(EventEmitter.getMaxListeners('onSliderOpen'), 66);
			assert.equal(EventEmitter.getMaxListeners('MyCompany.MyModule.MySlider:onOpen'), 66);
			assert.equal(EventEmitter.getMaxListeners('onSliderClose'), 99);
			assert.equal(EventEmitter.getMaxListeners('MyCompany.MyModule.MySlider:onHide'), 10);

			const slider = new MySlider();
			slider.subscribe('onClose', listener8);
			slider.subscribe('onClose', listener12);
			slider.subscribeOnce('onClose', listener9);
			slider.subscribe('onOpen', listener10);

			slider.show();
			slider.close();
			slider.show();
			slider.close();
			slider.emit('onHide');
			slider.emit('onHide');

			assert.equal(listener1.callCount, 2);
			assert.equal(listener2.callCount, 2);
			assert.equal(listener3.callCount, 2);
			assert.equal(listener4.callCount, 2);
			assert.equal(listener5.callCount, 1);
			assert.equal(listener6.callCount, 2);
			assert.equal(listener7.callCount, 1);
			assert.equal(listener8.callCount, 2);
			assert.equal(listener9.callCount, 1);
			assert.equal(listener10.callCount, 2);
			assert.equal(listener11.callCount, 4);
			assert.equal(listener12.callCount, 4);
		});

		class MyNewClass extends EventEmitter
		{
			constructor(options)
			{
				super();
				this.setEventNamespace('MyModule.MyNewClass');
				this.subscribeFromOptions(options.events);
			}
		}

		const aliases = {
			onOldPopupClose: { namespace: 'MyModule.MyNewPopup', eventName: 'onClose' },
			onOldPopupOpen: { namespace: 'MyModule.MyNewPopup', eventName: 'onOpen' },
		};

		EventEmitter.registerAliases(aliases);

		class MyNewPopup extends EventEmitter
		{
			constructor(options)
			{
				super();
				this.setEventNamespace('MyModule.MyNewPopup');
				this.subscribeFromOptions(options.events, aliases);
			}
		}

		class MyOldSlider extends EventEmitter
		{
			constructor(options)
			{
				super();
				this.setEventNamespace('MyModule.MyOldSlider');
				this.subscribeFromOptions(options.events, null, true);
			}
		}

		it('Should subscribe from options', () => {

			const listener1 = sinon.stub();
			const listener2 = sinon.stub();
			const listener3 = sinon.stub();
			const listener4 = sinon.stub();
			const listener5 = sinon.stub().callsFake((a, b, c) => {
				assert.equal(a, 1);
				assert.equal(b, 2);
				assert.equal(c, 3);
			});
			const listener6 = sinon.stub().callsFake((a, b, c) => {
				assert.equal(a, 1);
				assert.equal(b, 2);
				assert.equal(c, 3);
			});
			const listener7 = sinon.stub().callsFake((event) => {
				assert.equal(event.getData(), 100);

			});
			const listener8 = sinon.stub().callsFake((event) => {
				assert.equal(event.getData(), 100);
			});

			const newClass = new MyNewClass({
				events: {
					onOpen: listener1,
					onClose: listener2,
				}
			});

			assert.equal(newClass.getListeners('onOpen').size, 1);
			assert.equal(newClass.getListeners('onClose').size, 1);

			newClass.emit('onClose');
			newClass.emit('onClose');
			newClass.emit('onOpen');

			assert.equal(listener1.callCount, 1);
			assert.equal(listener2.callCount, 2);

			const oldSlider = new MyOldSlider({
				events: {
					onOpen: listener3,
					onClose: listener4,
				}
			});

			assert.equal(oldSlider.getListeners('onOpen').size, 1);
			assert.equal(oldSlider.getListeners('onClose').size, 1);

			oldSlider.emit('onClose');
			oldSlider.emit('onOpen');
			assert.equal(listener3.callCount, 1);
			assert.equal(listener4.callCount, 1);

			const newPopup = new MyNewPopup({
				events: {
					onOldPopupClose: listener5,
					onOldPopupOpen: listener6,
					onClose: listener7,
					onOpen: listener8,
				}
			});

			assert.equal(newPopup.getListeners('onOpen').size, 2);
			assert.equal(newPopup.getListeners('onClose').size, 2);

			const event = new BaseEvent({ compatData: [1,2,3], data: 100 });
			newPopup.emit('onClose', event);
			newPopup.emit('onClose', event);
			newPopup.emit('onClose', event);
			newPopup.emit('onOpen', event);
			newPopup.emit('onOpen', event);

			assert.equal(listener5.callCount, 3);
			assert.equal(listener6.callCount, 2);
			assert.equal(listener7.callCount, 3);
			assert.equal(listener8.callCount, 2);

		});
	});

	describe('Event errors', () => {
		it('Should add errors', () => {

			const listener1 = (event) => {
				event.setError(new BaseError('There is an error 1.', 'my-error-1'));
			};

			const listener2 = (event) => {
				event.setError(new BaseError('There is an error 2.', 'my-error-2', { code: 123 }));
			};

			const listener3 = (event) => {
				event.preventDefault();
			};

			const emitter = new EventEmitter();
			emitter.setEventNamespace('Test.Namespace');

			emitter.subscribe('onClose', listener1);
			emitter.subscribe('onClose', listener2);
			emitter.subscribe('onOpen', listener2);
			emitter.subscribe('onHide', listener3);

			const event1 = new BaseEvent();
			const event2 = new BaseEvent();
			const event3 = new BaseEvent();

			emitter.emit('onClose', event1);
			emitter.emit('onOpen', event2);
			emitter.emit('onHide', event3);

			assert.equal(event1.getErrors().length, 2);
			assert.equal(event2.getErrors().length, 1);
			assert.equal(event3.getErrors().length, 0);

			assert.equal(event1.getErrors()[0].getMessage(), 'There is an error 1.');
			assert.equal(event1.getErrors()[0].getCode(), 'my-error-1');
			assert.equal(event1.getErrors()[0].getCustomData(), null);

			assert.equal(event1.getErrors()[1].getMessage(), 'There is an error 2.');
			assert.equal(event1.getErrors()[1].getCode(), 'my-error-2');
			assert.equal(event1.getErrors()[1].getCustomData().code, 123);

			assert.equal(event2.getErrors()[0].getMessage(), 'There is an error 2.');
			assert.equal(event2.getErrors()[0].getCode(), 'my-error-2');
			assert.equal(event2.getErrors()[0].getCustomData().code, 123);
		});
	});

	describe('Emitter Composition', () => {

		class CatalogBaseComponent
		{
			constructor()
			{
				this.a = 123;
				this.emitter = new EventEmitter(this, 'BX.Catalog.Component');
			}

			getEmitter()
			{
				return this.emitter;
			}

			show()
			{
				this.emitter.emit('onOpen');
			}

			close()
			{
				this.emitter.emit('onClose', { b: 234 });
			}

			hide()
			{
				this.emitter.emit('onHide', { c: "12345" });
			}
		}

		it('Should set a namespace via constructor', () => {
			const component = new CatalogBaseComponent();

			assert.equal(component.getEmitter().getEventNamespace(), 'BX.Catalog.Component');
		});

		it('Should emit an event with an entity target', () => {

			const component = new CatalogBaseComponent();

			const listener1 = sinon.stub().callsFake(function(event: typeof(BaseEvent)) {
				assert.equal(event.getTarget(), component);
				assert.equal(event.getTarget().a, 123);
				assert.equal(event.getType(), 'BX.Catalog.Component:onOpen'.toLowerCase());
			});

			const listener2 = sinon.stub().callsFake(function(event: typeof(BaseEvent)) {

				const openEventName = 'BX.Catalog.Component:onOpen'.toLowerCase();
				const closeEventName = 'BX.Catalog.Component:onClose'.toLowerCase();

				assert.equal(event.getTarget(), component);
				assert.equal(event.getTarget().a, 123);
				assert([openEventName, closeEventName].includes(event.getType()));
			});

			const listener3 = sinon.stub().callsFake(function(event: typeof(BaseEvent)) {
				assert.equal(event.getTarget(), component);
				assert.equal(event.getType(), 'BX.Catalog.Component:onOpen'.toLowerCase());
			});

			const listener4 = sinon.stub().callsFake(function(event: typeof(BaseEvent)) {
				assert.equal(event.getTarget(), component);
				assert.equal(event.getData().c, "12345");
				assert.equal(event.getType(), 'BX.Catalog.Component:onHide'.toLowerCase());
			});

			const listener5 = sinon.stub().callsFake(function(event: typeof(BaseEvent)) {
				assert.equal(event.getTarget(), component);
				assert.equal(event.getType(), 'BX.Catalog.Component:onClose'.toLowerCase());
			});

			component.getEmitter().subscribe('onOpen', listener1);
			component.getEmitter().subscribe('onOpen', listener2);
			component.getEmitter().subscribe('onClose', listener2);

			EventEmitter.subscribe('BX.Catalog.Component:onOpen', listener3);
			EventEmitter.subscribe('BX.Catalog.Component:onHide', listener4);

			EventEmitter.subscribe(component.getEmitter(), 'onClose', listener5);

			component.show();
			component.close();
			component.hide();
			component.hide();

			assert.equal(listener1.callCount, 1);
			assert.equal(listener2.callCount, 2);
			assert.equal(listener3.callCount, 1);
			assert.equal(listener4.callCount, 2);
			assert.equal(listener5.callCount, 1);
		});

	});

	describe('Make an observable object', () => {

		class Parent
		{
			constructor()
			{
				this.parentProperty = 1;
			}

			doMethod()
			{
				return "123";
			}
		}

		/**
		 * @mixes EventEmitter
		 */
		class Child extends Parent
		{
			constructor()
			{
				super();

				EventEmitter.makeObservable(this, 'Module.Child');
				this.childProperty = 2;
			}

			show()
			{
				this.emit('onShow');
			}

			close()
			{
				this.emit('onClose');
			}

			hide()
			{
				this.emit('onHide');
			}

			doMethod()
			{
				return super.doMethod() + "456";
			}
		}

		it('Should implement public interface', () => {
			const child = new Child();

			assert(typeof child.subscribe === 'function');
			assert(typeof child.subscribeOnce === 'function');
			assert(typeof child.emit === 'function');
			assert(typeof child.unsubscribe === 'function');
			assert(typeof child.getMaxListeners === 'function');
			assert(typeof child.setMaxListeners === 'function');
			assert(typeof child.getListeners === 'function');
			assert(typeof child.incrementMaxListeners === 'function');
			assert(typeof child.decrementMaxListeners === 'function');
		});

		it('Should implement public interface (plain object)', () => {
			const obj = { a: 23, b: "eeee"};
			EventEmitter.makeObservable(obj, 'Module.MyObject');

			assert(typeof obj.subscribe === 'function');
			assert(typeof obj.subscribeOnce === 'function');
			assert(typeof obj.emit === 'function');
			assert(typeof obj.unsubscribe === 'function');
			assert(typeof obj.getMaxListeners === 'function');
			assert(typeof obj.setMaxListeners === 'function');
			assert(typeof obj.getListeners === 'function');
			assert(typeof obj.incrementMaxListeners === 'function');
			assert(typeof obj.decrementMaxListeners === 'function');
		});

		it('Should emit and subscribe', () => {

			const listener1 = sinon.stub();
			const listener2 = sinon.stub();
			const listener3 = sinon.stub();
			const listener4 = sinon.stub();

			const child = new Child();
			child.subscribe('onClose', listener1);
			child.subscribe('onShow', listener2);

			EventEmitter.subscribe('Module.Child:onHide', listener3);
			EventEmitter.subscribe(child, 'onHide', listener4);

			child.show();
			child.close();
			child.close();
			child.hide();

			assert.equal(listener1.callCount, 2);
			assert.equal(listener2.callCount, 1);
			assert.equal(listener3.callCount, 1);
			assert.equal(listener4.callCount, 1);
			assert.equal(child.childProperty, 2);
			assert.equal(child.parentProperty, 1);

			assert.equal(child.doMethod(), "123456");
		});


		it('Should make a plain object observable', () => {

			/**
			 * @mixes EventEmitter
			 */
			const obj = {
				a: 5,
				getValue: function() {
					return this.a;
				},

				 show: function() {
					this.emit('onShow');
				 },
				 close: function() {
					this.emit('onClose');
				 },
				 hide: function() {
					this.emit('onHide');
				 }
			};

			EventEmitter.makeObservable(obj, 'Module.MyObject');

			const listener1 = sinon.stub();
			const listener2 = sinon.stub();
			const listener3 = sinon.stub();
			const listener4 = sinon.stub();

			obj.subscribe('onClose', listener1);
			obj.subscribe('onShow', listener2);

			EventEmitter.subscribe('Module.MyObject:onHide', listener3);
			EventEmitter.subscribe(obj, 'onHide', listener4);

			obj.show();
			obj.close();
			obj.close();
			obj.hide();

			assert.equal(listener1.callCount, 2);
			assert.equal(listener2.callCount, 1);
			assert.equal(listener3.callCount, 1);
			assert.equal(listener4.callCount, 1);
			assert.equal(obj.getValue(), 5);
		});

	});

	describe('Full class name listeners', () => {

		it('Should subscribe/unsubscribe string listeners', () => {

			BX.namespace('BX.MyModule.MyClass');
			BX.MyModule.MyClass.handler = sinon.stub();
			BX.MyModule.MyClass.handler2 = sinon.stub();
			BX.MyModule.MyClass.handlerOnce = sinon.stub();

			const emitter = new EventEmitter();
			const event = 'test:event';
			const event2 = 'test:event2';
			const event3 = 'test:event3';
			const handler = 'BX.MyModule.MyClass.handler';
			const handler2 = 'BX.MyModule.MyClass.handler2';
			const handlerOnce = 'BX.MyModule.MyClass.handlerOnce';

			emitter.subscribe(event, handler);
			emitter.subscribe(event, handler2);
			emitter.subscribe(event2, handler2);
			emitter.subscribeOnce(event3, handlerOnce);

			assert.equal(emitter.getListeners(event).size, 2);
			assert.equal(emitter.getListeners(event2).size, 1);
			assert.equal(emitter.getListeners(event3).size, 1);
			emitter.emit(event);
			emitter.emit(event2);
			emitter.emit(event3);

			assert.equal(BX.MyModule.MyClass.handler.callCount, 1);
			assert.equal(BX.MyModule.MyClass.handler2.callCount, 2);
			assert.equal(BX.MyModule.MyClass.handlerOnce.callCount, 1);

			emitter.unsubscribe(event, handler2);
			assert.equal(emitter.getListeners(event).size, 1);
			assert.equal(emitter.getListeners(event2).size, 1);
			assert.equal(emitter.getListeners(event3).size, 0);

			emitter.emit(event);
			emitter.emit(event2);
			emitter.emit(event3);

			assert.equal(BX.MyModule.MyClass.handler.callCount, 2);
			assert.equal(BX.MyModule.MyClass.handler2.callCount, 3);
			assert.equal(BX.MyModule.MyClass.handlerOnce.callCount, 1);

			emitter.unsubscribe(event, handler);
			emitter.unsubscribe(event2, handler2);

			assert.equal(emitter.getListeners(event).size, 0);
			assert.equal(emitter.getListeners(event2).size, 0);
			assert.equal(emitter.getListeners(event3).size, 0);
		});


		it('Should subscribe from options', () => {
			BX.namespace('BX.MyModule.MySuperClass');
			BX.MyModule.MySuperClass.handleOpen = sinon.stub();
			BX.MyModule.MySuperClass.handleClose = sinon.stub();

			class MySuperClass extends EventEmitter
			{
				constructor(options)
				{
					super();
					this.setEventNamespace('MyCompany.MyModule.MySuperClass');
					this.subscribeFromOptions(options.events);
				}

				open()
				{
					this.emit('onOpen');
				}

				close()
				{
					this.emit('onClose');
				}
			}

			const obj = new MySuperClass({
				events: {
					'onOpen': 'BX.MyModule.MySuperClass.handleOpen',
					'onClose': 'BX.MyModule.MySuperClass.handleClose',
				}
			});

			assert.equal(BX.MyModule.MySuperClass.handleOpen.callCount, 0);
			assert.equal(BX.MyModule.MySuperClass.handleOpen.callCount, 0);

			obj.open();
			obj.close();

			assert.equal(BX.MyModule.MySuperClass.handleOpen.callCount, 1);
			assert.equal(BX.MyModule.MySuperClass.handleClose.callCount, 1);

			obj.open();
			obj.open();
			obj.close();

			assert.equal(BX.MyModule.MySuperClass.handleOpen.callCount, 3);
			assert.equal(BX.MyModule.MySuperClass.handleClose.callCount, 2);
		});
	});
});