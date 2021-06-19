;(function() {
	'use strict';

	BX.namespace('BX');

	var isBlock = function(block)
	{
		return BX.type.isPlainObject(block) && ('block' in block);
	};

	var isString = function(string)
	{
		return BX.type.isNotEmptyString(string);
	};

	var isTag = function(tag)
	{
		return BX.type.isPlainObject(tag) && !('block' in tag && 'elem' in tag);
	};

	BX.render = function(item)
	{
		var element = null;

		if (isBlock(item) || isTag(item))
		{
			var tag = 'tag' in item ? item.tag : 'div';
			var className = item.block;
			var attrs = 'attrs' in item ? item.attrs : {};
			var events = 'events' in item ? item.events : {};
			var props = {};

			if ('props' in item && BX.type.isPlainObject(item.props))
			{
				props = item.props;
			}

			if ('mix' in item && BX.type.isArray(item.mix))
			{
				item.mix.push(className);
				props.className = item.mix.join(' ');
			}
			else if ('mix' in item && BX.type.isNotEmptyString(item.mix))
			{
				props.className = [className, item.mix].join(' ');
			}
			else
			{
				props.className = className;
			}

			if ('content' in item)
			{
				var children = [];
				var text = '';

				if (isBlock(item.content) || isTag(item.content))
				{
					if (item.content.block in BX.Main.ui.block)
					{
						item.content = BX.Main.ui.block[item.content.block](item.content);
					}

					children = [BX.render(item.content)];
				}

				if (isString(item.content))
				{
					text = (item.isHtmlContent) ? item.content : BX.util.htmlspecialchars(item.content);
				}

				if (BX.type.isArray(item.content))
				{
					children = BX.decl(item.content);
				}

				if (BX.type.isDomNode(item.content))
				{
					children = [item.content];
				}
			}

			element = BX.create(tag, {props: props, attrs: attrs, events: events, children: children, html: text});
		}
		else if (isString(item))
		{
			element = BX.util.htmlspecialchars(item);
		}
		else if (BX.type.isDomNode(item))
		{
			element = item;
		}

		return element;
	};


	BX.decl = function(decl)
	{
		var result = null;

		if (BX.type.isArray(decl))
		{
			result = decl.map(function(current) {
				if (isBlock(current) && current.block in BX.Main.ui.block)
				{
					current = BX.Main.ui.block[current.block](current);
				}

				return BX.render(current);
			});
		}
		else if (isBlock(decl))
		{
			if (decl.block in BX.Main.ui.block)
			{
				decl = BX.Main.ui.block[decl.block](decl);
			}

			result = BX.render(decl);
		}

		return result;
	};
})();