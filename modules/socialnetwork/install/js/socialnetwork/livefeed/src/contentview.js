import { ajax, Type, Loc, Dom, Tag } from 'main.core';
import { Popup } from 'main.popup';

export class ContentView
{
	static registerAreaList()
	{
		var
			container = BX('log_internal_container'),
			fullContentArea = null;

		if (container)
		{
			var viewAreaList = BX.findChildren(container, {
				tag: 'div',
				className: 'feed-post-contentview'
			}, true);
			for (var i = 0, length = viewAreaList.length; i < length; i++)
			{
				if (viewAreaList[i].id.length > 0)
				{
					fullContentArea = BX.findChild(viewAreaList[i], {
						tag: 'div',
						className: 'feed-post-text-block-inner-inner'
					});
					BX.UserContentView.registerViewArea(viewAreaList[i].id, (fullContentArea ? fullContentArea : null));
				}
			}
		}
	}
}
