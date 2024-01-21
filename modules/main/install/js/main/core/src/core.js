import './internal/bx';

import Type from './lib/type';
import Reflection from './lib/reflection';
import Text from './lib/text';
import Dom from './lib/dom';
import Browser from './lib/browser';
import Event from './lib/event';
import Http from './lib/http';
import Runtime from './lib/runtime';
import Loc from './lib/loc';
import Tag from './lib/tag';
import Uri from './lib/uri';
import Validation from './lib/validation';
import Cache from './lib/cache';
import BaseError from './lib/base-error';
import Extension from './lib/extension/extension';
import ZIndexManager from './lib/z-index/z-index-manager';
import Collections from './lib/collections';

export {
	Type,
	Reflection,
	Text,
	Dom,
	Browser,
	Event,
	Http,
	Runtime,
	Loc,
	Tag,
	Uri,
	Validation,
	Cache,
	BaseError,
	Extension,
	ZIndexManager,
	Collections,
};

export * from './core-compatibility';
export type * from './lib/types/index';

if (typeof global === 'object' && global.window && global.window.BX)
{
	Object.assign(global.window.BX, exports);
}
