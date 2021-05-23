BX.ready(function(){
	 var idea_active_menu= {
		tag:'a.bx-idea-active-menu',

		_node:BX('bx-idea-left-menu'),

		_tagname:function(){
			var name_patern=/^([a-z]{1,6})(\.|#)(\S+)/;
			var tag_n=name_patern.exec(this.tag)[1];
			return tag_n;
		},
		_classname:function(){
			var name_patern=/^([a-z]{1,6})(\.|#)(\S+)/;
			var class_n=name_patern.exec(this.tag)[3];
			return class_n;
		},

		active_tag:function(){
			var act_tag = BX.findChildren(this._node,{tagName:this._tagname(), className:this._classname()},true);
			if(act_tag)
				return act_tag[act_tag.length-1];
			else
				return false;
		},

		wrap: function(){
			var span = document.createElement('span');
			var span_corn = document.createElement('span');
			BX.addClass(span,'bx-idea-active-menu-item');
			//BX.addClass(span_corn,'bx-idea-menu-corner');

			if(!span.style.borderRadius && BX.browser.IsDoctype())span_corn.style.left='-9px';

			if(!BX.browser.IsDoctype() && BX.browser.IsIE()){
			span_corn.style.right='-12px';
			}

			span.style.borderRadius='3px 4px 4px 3px';
			var act_tag_wrap = this.active_tag();
			if(act_tag_wrap)
			{
				act_tag_wrap.parentNode.replaceChild(act_tag_wrap.parentNode.insertBefore(span, act_tag_wrap),act_tag_wrap);
				span.appendChild(act_tag_wrap);
				span.appendChild(span_corn);

				var TopParent = act_tag_wrap.parentNode.parentNode.parentNode.parentNode;
				if(BX.hasClass(TopParent, 'bx-idea-left-menu-li') && !BX.hasClass(TopParent, 'bx-idea-left-menu-open'))
					BX.addClass(TopParent, 'bx-idea-left-menu-open');

				var corn_height=(span.offsetHeight-3)/2;
				span_corn.style.borderBottomWidth = corn_height+'px';
				span_corn.style.borderTopWidth = corn_height+'px';
			}
		}
	};

	idea_active_menu.wrap();

	/*var menuLI = BX.findChildren(BX('bx-idea-left-menu'), {tagName:'li', className:'bx-idea-left-menu-li'}, true);
	if(menuLI)
	{
		for(var i=0; i<menuLI.length; i++)
	{
		var IsSubMenuExists = BX.findChildren(menuLI[i], {tagName:'ul', className:'bx-idea-left-menu_2'}, true);
		if(IsSubMenuExists)
				{
					var firstParentLink = BX.findChildren(menuLI[i], {tagName:'a', className:'bx-idea-left-menu-link'}, true);
					if(firstParentLink)
						firstParentLink[0].onclick = function()
						{
							BX.hasClass(this.parentNode, 'bx-idea-left-menu-open')?BX.removeClass(this.parentNode, 'bx-idea-left-menu-open'):BX.addClass(this.parentNode, 'bx-idea-left-menu-open');
						}
				}
		else
		{
				menuLI[i].onclick=function()
			{
					BX.hasClass(this, 'bx-idea-left-menu-open')?BX.removeClass(this, 'bx-idea-left-menu-open'):BX.addClass(this, 'bx-idea-left-menu-open');
				}
		}
		}
	}   */
});