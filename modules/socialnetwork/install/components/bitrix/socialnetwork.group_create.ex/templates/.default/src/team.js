import {Type} from 'main.core';

import {WorkgroupForm} from './index';
import {InviteSelector} from './inviteselector';
import {TeamExtranetManager} from './teamextranet';
import {Util} from './util';

export class TeamManager
{
	constructor()
	{
		document.querySelectorAll('[data-employees-selector-id]').forEach((employeeSeectorNode) => {

			const selectorId = employeeSeectorNode.getAttribute('data-employees-selector-id');
			if (Type.isStringFilled(selectorId))
			{
				WorkgroupForm.getInstance().arUserSelector.push(selectorId);
				new InviteSelector({
					selectorId: selectorId,
				});
			}
		});

		new TeamExtranetManager();
	}
}
