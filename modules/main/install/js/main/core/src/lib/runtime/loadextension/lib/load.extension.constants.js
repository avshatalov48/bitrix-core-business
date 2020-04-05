import type Extension from '../load.extension.entity';

export const initialized: {[name: string]: Extension} = {};
export const ajaxController: string = 'main.bitrix.main.controller.loadext.getextensions';
export const Status: {[key: string]: string} = {
	SCHEDULED: 'scheduled',
	LOAD: 'load',
	LOADED: 'loaded',
	ERROR: 'error',
};