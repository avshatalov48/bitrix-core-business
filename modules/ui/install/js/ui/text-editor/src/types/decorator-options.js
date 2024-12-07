import type { JsonObject } from 'main.core';
import DecoratorComponent from '../decorator-component';

export type DecoratorOptions = {
	componentClass: Class<DecoratorComponent>,
	options: JsonObject,
};
