// @flow
'use strict';

import 'ui.tilegrid';
import 'ui.forms';
import { Dom, Loc, Tag, Type, Event} from 'main.core';
import { EventEmitter } from 'main.core.events';

export default class SyncStageUnit
{
	constructor(options)
	{
		this.name = options.name || '';
		this.title = options.title || '';
		this.doneStatus = false;
	}

	renderTo(outerWrapper)
	{
		if (Type.isElementNode(outerWrapper))
		{
			outerWrapper.appendChild(this.getContent());
		}
		
		EventEmitter.emit('BX.Calendar.Sync.Interface.SyncStageUnit:onRenderDone');
	}

	getContent()
	{
		this.contentNode = Tag.render`
			<div class="calendar-sync__content-block --space-bottom-xl">
				<div class="calendar-sync__content-text --icon-check --disabled">${this.title}</div>
			</div>
		`;
		return this.contentNode;
	}

	setDone()
	{
		this.doneStatus = true;
		Dom.removeClass(this.contentNode.querySelector('.--icon-check'), '--disabled');
	}

	setUndone()
	{
		this.doneStatus = false;
		Dom.addClass(this.contentNode.querySelector('.--icon-check'), '--disabled');
	}
}
