import {Grid} from './grid.js';
import {MembersPopup} from './memberspopup.js';
import {ScrumMembersPopup} from './scrum-members-popup.js';
import {Actions} from './actions.js';
import {Tag} from './tag.js';
import {Filter} from './filter.js';
import {Pin} from './pin.js';

import './css/members.css';
import './css/role.css';
import './css/avatar.css';
import './css/percent.css';
import './css/counter.css';

class Controller
{
	static repo = new Map();

	static getById(id)
	{
		return Controller.repo.get(id);
	}

	constructor(options)
	{
		this.gridInstance = new Grid(options);
		this.membersPopup = new MembersPopup(options);
		this.scrumMembersPopup = new ScrumMembersPopup(options);

		Controller.repo.set(options.id, this);
	}

	getMembersPopup(): MembersPopup
	{
		return this.membersPopup;
	}

	getScrumMembersPopup(): MembersPopup
	{
		return this.scrumMembersPopup;
	}

	getInstance(): Grid
	{
		return this.gridInstance;
	}

	getGrid()
	{
		return this.getInstance().getGrid();
	}

}

export {
	Controller,
	Actions as ActionController,
	Tag as TagController,
	Filter as Filter,
	Pin as PinManager,
};
