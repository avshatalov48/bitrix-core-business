;(function(window) {
	if (window.JCIblockVoteStars)
		return;

	/** @param {{
			progressId: string,
			ratingId: string,
			starsId: string,
			ajaxUrl: string,
			checkVoteUrl: string,
			ajaxParams: {},
			siteId: string,
			voteData: {
				element: int,
				percent: int,
				count: int
			},
			readOnly: bool
		}} params
	 */
	window.JCIblockVoteStars = function (params)
	{
		this.progressObj = null;
		this.ratingObj = null;
		this.starsObj = null;

		this.progressId = '';
		this.ratingId = '';
		this.starsId = '';

		this.ajaxParams = {};
		this.siteId = '';

		this.voteData = {
			element: 0,
			percent: 0,
			count: 0
		};

		this.config = {
			readOnly:  false,
			alreadyVoted: true,
			request: false
		};

		if (BX.type.isPlainObject(params))
		{
			if (BX.type.isNotEmptyString(params.progressId))
				this.progressId = params.progressId;
			if (BX.type.isNotEmptyString(params.ratingId))
				this.ratingId = params.ratingId;
			if (BX.type.isNotEmptyString(params.starsId))
				this.starsId = params.starsId;
			if (BX.type.isNotEmptyString(params.ajaxUrl))
				this.ajaxUrl = params.ajaxUrl;
			if (BX.type.isNotEmptyString(params.checkVoteUrl))
				this.checkVoteUrl = params.checkVoteUrl;
			if (BX.type.isPlainObject(params.ajaxParams))
				this.ajaxParams = params.ajaxParams;
			if (BX.type.isNotEmptyString(params.siteId))
				this.siteId = params.siteId;
			if (BX.type.isPlainObject(params.voteData))
			{
				if (BX.type.isNumber(params.voteData.element))
					this.voteData.element = params.voteData.element;
				if (BX.type.isNumber(params.voteData.percent))
					this.voteData.percent = this.preparePercent(params.voteData.percent);
				if (BX.type.isNumber(params.voteData.count))
					this.voteData.count = params.voteData.count;
			}
			if (BX.type.isBoolean(params.readOnly))
				this.config.readOnly = params.readOnly;
		}

		BX.ready(BX.proxy(this.init, this));
	};

	window.JCIblockVoteStars.prototype.init = function()
	{
		if (BX.type.isNotEmptyString(this.progressId))
			this.progressObj = BX(this.progressId);

		if (BX.type.isNotEmptyString(this.ratingId))
			this.ratingObj = BX(this.ratingId);

		if (BX.type.isNotEmptyString(this.starsId))
			this.starsObj = BX(this.starsId);

		this.showProgress(this.voteData.percent);
		this.showVotes();

		this.checkVote();
	};

	window.JCIblockVoteStars.prototype.checkVote = function()
	{
		if (this.config.readOnly || this.voteData.element <= 0)
			return;

		BX.ajax({
			'timeout': 30,
			'method': 'POST',
			'dataType': 'json',
			'url': this.checkVoteUrl,
			'data': {
				sessid: BX.bitrix_sessid(),
				checkVote: 'Y',
				vote_id: this.voteData.element,
				site_id: this.siteId
			},
			'onsuccess': BX.proxy(this.checkVoteResult, this)
		});
	};

	window.JCIblockVoteStars.prototype.checkVoteResult = function(result)
	{
		if (BX.type.isPlainObject(result))
		{
			if (result.success)
				this.config.alreadyVoted = result.voted;
		}

		if (this.config.readOnly || this.config.alreadyVoted || this.voteData.element <= 0)
			return;

		if (BX.type.isElementNode(this.starsObj))
		{

			BX.bind(this.starsObj, 'mousemove', BX.proxy(this.handlerMouseMove, this));
			BX.bind(this.starsObj, 'mouseout', BX.proxy(this.handlerMouseOut, this));
			BX.bind(this.starsObj, 'click', BX.proxy(this.handlerClick, this));
		}
	};

	window.JCIblockVoteStars.prototype.destroy = function()
	{
		if (BX.type.isElementNode(this.progressObj))
			BX.unbindAll(this.progressObj);
		this.progressObj = null;

		if (BX.type.isElementNode(this.ratingObj))
			BX.unbindAll(this.ratingObj);
		this.ratingObj = null;

		if (BX.type.isElementNode(this.starsObj))
			BX.unbindAll(this.starsObj);
		this.starsObj = null;
	};

	window.JCIblockVoteStars.prototype.preparePercent = function(percent)
	{
		percent = parseInt(percent, 10);
		if (isNaN(percent))
			percent = 0;
		else if(percent > 100)
			percent = 100;
		else if(percent < 0)
			percent = 0;
		return percent;
	};

	window.JCIblockVoteStars.prototype.showProgress = function(percent)
	{
		if (!BX.type.isElementNode(this.progressObj))
			return;

		BX.style(this.progressObj, 'width', percent.toString() + '%');
	};

	window.JCIblockVoteStars.prototype.showVotes = function()
	{
		if (!BX.type.isElementNode(this.ratingObj))
			return;

		this.ratingObj.innerHTML = "( " + this.voteData.count + " )";
	};

	window.JCIblockVoteStars.prototype.handlerMouseMove = function(e)
	{
		var starsPos,
			newPercent;

		if (this.config.readOnly || this.config.alreadyVoted || this.config.request)
			return;

		e = e || window.event;

		if (!BX.type.isElementNode(this.starsObj))
			return;

		starsPos = BX.pos(this.starsObj);
		newPercent = ((e.pageX - starsPos.left)/starsPos.width)*5;
		this.showProgress(this.preparePercent(Math.ceil(newPercent)*20));
	};

	window.JCIblockVoteStars.prototype.handlerMouseOut = function()
	{
		if (this.config.readOnly || this.config.alreadyVoted || this.config.request)
			return;
		this.showProgress(this.voteData.percent);
	};

	window.JCIblockVoteStars.prototype.handlerClick = function(e)
	{
		var starsPos,
			newValue;

		if (this.config.readOnly || this.config.alreadyVoted || this.config.request)
			return;
		this.config.request = true;

		e = e || window.event;

		if (!BX.type.isElementNode(this.starsObj))
			return;

		starsPos = BX.pos(this.starsObj);
		newValue = parseInt(Math.ceil(((e.pageX - starsPos.left)/starsPos.width)*5), 10);
		if (isNaN(newValue))
			return;

		this.ajaxParams.rating = newValue - 1;
		this.ajaxParams.vote = 'Y';
		this.ajaxParams.vote_id = this.voteData.element;
		this.ajaxParams.sessid = BX.bitrix_sessid();
		this.ajaxParams.site_id = this.siteId;

		BX.ajax({
			timeout:   30,
			method:   'POST',
			dataType: 'json',
			url:       this.ajaxUrl,
			data:      this.ajaxParams,
			onsuccess: BX.proxy(this.clickResult, this)
		});
	};

	/** @param {{
			value: int,
			votes: int
		}} result
	 */
	window.JCIblockVoteStars.prototype.clickResult = function(result)
	{
		this.config.request = false;
		if (BX.type.isPlainObject(result))
		{
			this.config.alreadyVoted = true;
			this.voteData.percent = this.preparePercent((result.value)*20);
			this.voteData.count = result.votes;
			this.showProgress(this.voteData.percent)
			this.showVotes();
		}
	};
})(window);