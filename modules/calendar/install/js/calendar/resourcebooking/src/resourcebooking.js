// @flow
'use strict';
import { Type, Loc, Dom, Event, Tag, Browser, Text, Runtime } from 'main.core';
import {LiveFieldController} from './livefieldcontroller.js';
import {BookingUtil} from "./bookingutil";
import {FieldViewControllerEdit} from "./fieldviewcontrolleredit";
import {FieldViewControllerPreview} from "./fieldviewcontrollerpreview";
import {SelectInput} from "./controls/selectinput";
import "main.date";
import './css/resourcebooking.css';
import {PopupManager, MenuManager} from 'main.popup';

import {BaseEvent, EventEmitter } from 'main.core.events';
let CoreDate = window.BX && BX.Main && BX.Main.Date ? BX.Main.Date : null;
export {CoreDate};
export {Type, Loc, Dom, Event, Tag, Browser, Text, Runtime, PopupManager, MenuManager};
export {BaseEvent, EventEmitter};
export {BookingUtil, FieldViewControllerEdit, FieldViewControllerPreview, SelectInput};

export class Resourcebooking
{
	static getLiveField(params)
	{
		if (!params.wrap || !Type.isDomNode(params.wrap))
		{
			throw new Error('The argument "params.wrap" must be a DOM node');
		}
		if (Type.isNull(CoreDate))
		{
			throw new Error('The error occured during Date extention loading');
		}

		let liveFieldController = new LiveFieldController(params);
		liveFieldController.init();
		return liveFieldController;
	}

	static getPreviewField(params)
	{
	}
}
