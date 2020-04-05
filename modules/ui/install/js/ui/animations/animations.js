(function() {

var BX = window.BX;

BX.namespace('BX.UI');

if (!!BX.UI.Animations)
{
	return;
}

BX.UI.Animations = {

	expand: function(params)
	{
		if (!BX(params.moreButtonNode))
		{
			return;
		}

		var
			type = (BX.type.isNotEmptyString(params.type) ? params.type : 'post'),
			classBlock = (BX.type.isNotEmptyString(params.classBlock) ? params.classBlock : 'feed-post-text-block'),
			classOuter = (BX.type.isNotEmptyString(params.classOuter) ? params.classOuter : 'feed-post-text-block-inner'),
			classInner = (BX.type.isNotEmptyString(params.classInner) ? params.classInner : 'feed-post-text-block-inner-inner'),
			heightLimit = (typeof params.heightLimit != 'undefined' && parseInt(params.heightLimit) > 0 ? parseInt(params.heightLimit) : 300);

		var tmpNode = BX.findParent(BX(params.moreButtonNode), {
			tag: 'div',
			className: classBlock
		});

		if (!tmpNode)
		{
			return;
		}

		var el = BX.findChild(tmpNode, {
			tag: 'div',
			className: classOuter
		}, true);
		var el2 = BX.findChild(tmpNode, {
			tag: 'div',
			className: classInner
		}, true);

		if (!el || !el2)
		{
			return;
		}

		var
			fxStart = heightLimit,
			fxFinish = parseInt(el2.offsetHeight),
			start1 = { height: fxStart },
			finish1 = { height: fxFinish };

		if (!!params.moreButtonNode)
		{
			BX.remove(params.moreButtonNode);
		}

		var time = (fxFinish - fxStart) / (2000 - fxStart);
		time = (time < 0.3 ? 0.3 : (time > 0.8 ? 0.8 : time));

		el.style.maxHeight = start1.height+'px';
		el.style.overflow = 'hidden';

		el.style.maxHeight = start1.height+'px';
		el.style.overflow = 'hidden';

		(new BX["easing"]({
			duration : time*1000,
			start : start1,
			finish : finish1,
			transition : BX.easing.makeEaseOut(BX.easing.transitions.quart),
			step : function(state){
				el.style.maxHeight = state.height + "px";
				el.style.opacity = state.opacity / 100;
			},
			complete : function(){
				el.style.cssText = '';
				el.style.maxHeight = 'none';

				BX.LazyLoad.showImages(true);

				if (BX.type.isFunction(params.callback))
				{
					params.callback(el);
				}
			}
		})).animate();

		return true;
	},

	onPlayerPlay: function(playerNode)
	{
		var classes = {
			post: {
				block: 'feed-post-text-block',
				outer: 'feed-post-text-block-inner',
				inner: 'feed-post-text-block-inner-inner'
			},
			comment: {
				block: 'feed-com-block',
				outer: 'feed-com-text-inner',
				inner: 'feed-com-text-inner-inner'
			},
			more: 'feed-post-text-more'
		};
		var contentNode = BX.findParent(playerNode, {
			className: classes.post.block
		});

		var type = null;

		if (contentNode)
		{
			type = 'post';
		}
		else
		{
			contentNode = BX.findParent(playerNode, {
				className: classes.comment.block
			});

			if (contentNode)
			{
				type = 'comment';
			}
		}

		if (!contentNode)
		{
			return;
		}

		var moreButtonNode = BX.findChild(contentNode, {
			className: classes.more
		}, true);

		if (moreButtonNode)
		{
			BX.UI.Animations.expand({
				moreButtonNode: moreButtonNode,
				type: type,
				classBlock: classes[type].block,
				classOuter: classes[type].outer,
				classInner: classes[type].inner,
				heightLimit: (type == 'comment' ? 200 : 300)
			});
		}
	}
};

BX.ready(function () {
	BX.addCustomEvent('Player:onPlay', function(player)
	{
		var node = player.getElement();
		if (node)
		{
			BX.UI.Animations.onPlayerPlay(node);
		}
	});
});

})();