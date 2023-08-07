import bind from './event/bind';
import unbind from './event/unbind';
import unbindAll from './event/unbind-all';
import bindOnce from './event/bind-once';
import EventEmitter from './event/event-emitter';
import BaseEvent from './event/base-event';
import ready from './event/ready';

/**
 * @memberOf BX
 */
export default class Event
{
	static bind: bind = bind;
	static bindOnce: bindOnce = bindOnce;
	static unbind: unbind = unbind;
	static unbindAll: unbindAll = unbindAll;
	static ready: ready = ready;
	static EventEmitter: EventEmitter = EventEmitter;
	static BaseEvent: BaseEvent = BaseEvent;
}
