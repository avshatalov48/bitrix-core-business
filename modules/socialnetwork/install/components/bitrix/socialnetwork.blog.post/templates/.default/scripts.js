function showHiddenDestination(cont, el)
{
	BX.hide(el);
	BX('blog-destination-hidden-'+cont).style.display = 'inline';
}

function showBlogPost(id, source)
{
	var el = BX.findChild(BX('blg-post-' + id), {className: 'feed-post-text-block-inner'}, true, false);
	el2 = BX.findChild(BX('blg-post-' + id), {className: 'feed-post-text-block-inner-inner'}, true, false);
	BX.remove(source);

	if(el)
	{
		var fxStart = 300;
		var fxFinish = el2.offsetHeight;
		(new BX.fx({
			time: 1.0 * (fxFinish - fxStart) / (1200-fxStart),
			step: 0.05,
			type: 'linear',
			start: fxStart,
			finish: fxFinish,
			callback: BX.delegate(__blogExpandSetHeight, el),
			callback_complete: BX.delegate(function() {})
		})).start();								
	}
}

function __blogExpandSetHeight(height)
{
	this.style.maxHeight = height + 'px';
}