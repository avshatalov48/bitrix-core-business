window["__logCommentsListRedefine"] = function(ENTITY_XML_ID, node_quote_id, author_id, logId)
{
	if (window["UC"] && !!window["UC"][ENTITY_XML_ID])
	{
		BX.addCustomEvent(window["UC"][ENTITY_XML_ID], "OnUCListWasBuilt", function(obj, data, container){
			if (BX(container) && container.hasChildNodes)
			{
				var node = container.firstChild, id;
				do {
					if (BX(node) && node["getAttribute"])
					{
						id = node.getAttribute("id").replace('record-' + ENTITY_XML_ID + '-', '').replace('-cover', '');
						BX.onCustomEvent(window, "OnUCAddEntitiesCorrespondence", [ENTITY_XML_ID + '-' + id, [logId, top["arLogCom" + logId + id]]]);
					}
				} while ((node = node.nextSibling))
			}
		});
	}
	if (!!window.mplCheckForQuote)
		BX.bind(BX(node_quote_id), "mouseup", function(e){ mplCheckForQuote(e, this, ENTITY_XML_ID, author_id) });

};

window["__logBuildRating"] = function(comm, commFormat, anchor_id) {
	var ratingNode = '';
		anchor_id = (!!anchor_id ? anchor_id : (Math.floor(Math.random()*100000) + 1));
	if ( BX.message("sonetLShowRating") == 'Y' &&
		!!comm["RATING_TYPE_ID"] > 0 && comm["RATING_ENTITY_ID"] > 0 &&
		(BX.message("sonetLRatingType") == "like" && !!window["RatingLike"] || BX.message("sonetLRatingType") == "standart_text" && !!window["Rating"]))
	{
		if (BX.message("sonetLRatingType") == "like")
		{
			var
				you_like_class = (comm["RATING_USER_VOTE_VALUE"] > 0) ? " bx-you-like" : "",
				you_like_text = (comm["RATING_USER_VOTE_VALUE"] > 0) ? BX.message("sonetLTextLikeN") : BX.message("sonetLTextLikeY"),
				vote_text = null;
			if (!!commFormat["ALLOW_VOTE"] &&
				!!commFormat["ALLOW_VOTE"]["RESULT"])
				vote_text = BX.create('span', {
					props: {
						'className': 'bx-ilike-text'
					},
					html: you_like_text
				});

			ratingNode = BX.create('span', {
				attrs : {
					id : 'sonet-rating-' + comm["RATING_TYPE_ID"] + '-' + comm["RATING_ENTITY_ID"] + '-' + anchor_id
				},
				props: {
					'className': 'sonet-log-comment-like rating_vote_text'
				},
				children: [
					BX.create('span', {
						props: {
							'className': 'ilike-light'
						},
						children: [
							BX.create('span', {
								props: {
									'id': 'bx-ilike-button-' + comm["RATING_TYPE_ID"] + '-' + comm["RATING_ENTITY_ID"] + '-' + anchor_id,
									'className': 'bx-ilike-button'
								},
								children: [
									BX.create('span', {
										props: {
											'className': 'bx-ilike-right-wrap' + you_like_class
										},
										children: [
											BX.create('span', {
												props: {
													'className': 'bx-ilike-right'
												},
												html: comm["RATING_TOTAL_POSITIVE_VOTES"]
											})
										]
									}),
									BX.create('span', {
										props: {
											'className': 'bx-ilike-left-wrap'
										},
										children: [
											vote_text
										]
									})
								]
							}),
							BX.create('span', {
								props: {
									'id': 'bx-ilike-popup-cont-' + comm["RATING_TYPE_ID"] + '-' + comm["RATING_ENTITY_ID"] + '-' + anchor_id,
									'className': 'bx-ilike-wrap-block'
								},
								style: {
									'display': 'none'
								},
								children: [
									BX.create('span', {
										props: {
											'className': 'bx-ilike-popup'
										},
										children: [
											BX.create('span', {
												props: {
													'className': 'bx-ilike-wait'
												}
											})
										]
									})
								]
							})
						]
					})
				]
			});
		}
		else if (BX.message("sonetLRatingType") == "standart_text")
		{
			ratingNode = BX.create('span', {
				attrs : {
					id : 'sonet-rating-' + comm["RATING_TYPE_ID"] + '-' + comm["RATING_ENTITY_ID"] + '-' + anchor_id
				},
				props: {
					'className': 'sonet-log-comment-like rating_vote_text'
				},
				children: [
					BX.create('span', {
						props: {
							'className': 'bx-rating' + (!commFormat["ALLOW_VOTE"]['RESULT'] ? ' bx-rating-disabled' : '') + (comm["RATING_USER_VOTE_VALUE"] != 0 ? ' bx-rating-active' : ''),
							'id': 'bx-rating-' + comm["RATING_TYPE_ID"] + '-' + comm["RATING_ENTITY_ID"] + '-' + anchor_id,
							'title': (!commFormat["ALLOW_VOTE"]['RESULT'] ? commFormat["ERROR_MSG"] : '')
						},
						children: [
							BX.create('span', {
								props: {
									'className': 'bx-rating-absolute'
								},
								children: [
									BX.create('span', {
										props: {
											'className': 'bx-rating-question'
										},
										html: (!commFormat["ALLOW_VOTE"]['RESULT'] ? BX.message("sonetLTextDenied") : BX.message("sonetLTextAvailable"))
									}),
									BX.create('span', {
										props: {
											'className': 'bx-rating-yes ' +  (comm["RATING_USER_VOTE_VALUE"] > 0 ? '  bx-rating-yes-active' : ''),
											'title': (comm["RATING_USER_VOTE_VALUE"] > 0 ? BX.message("sonetLTextCancel") : BX.message("sonetLTextPlus"))
										},
										children: [
											BX.create('a', {
												props: {
													'className': 'bx-rating-yes-count',
													'href': '#like'
												},
												html: ""+parseInt(comm["RATING_TOTAL_POSITIVE_VOTES"])
											}),
											BX.create('a', {
												props: {
													'className': 'bx-rating-yes-text',
													'href': '#like'
												},
												html: BX.message("sonetLTextRatingY")
											})
										]
									}),
									BX.create('span', {
										props: {
											'className': 'bx-rating-separator'
										},
										html: '/'
									}),
									BX.create('span', {
										props: {
											'className': 'bx-rating-no ' +  (comm["RATING_USER_VOTE_VALUE"] < 0 ? '  bx-rating-no-active' : ''),
											'title': (comm["RATING_USER_VOTE_VALUE"] < 0 ? BX.message("sonetLTextCancel") : BX.message("sonetLTextMinus"))
										},
										children: [
											BX.create('a', {
												props: {
													'className': 'bx-rating-no-count',
													'href': '#dislike'
												},
												html: ""+parseInt(comm["RATING_TOTAL_NEGATIVE_VOTES"])
											}),
											BX.create('a', {
												props: {
													'className': 'bx-rating-no-text',
													'href': '#dislike'
												},
												html: BX.message("sonetLTextRatingN")
											})
										]
									})
								]
							})
						]
					}),
					BX.create('span', {
						props: {
							'id': 'bx-rating-popup-cont-' + comm["RATING_TYPE_ID"] + '-' + comm["RATING_ENTITY_ID"] + '-' + anchor_id + '-plus'
						},
						style: {
							'display': 'none'
						},
						children: [
							BX.create('span', {
								props: {
									'className': 'bx-ilike-popup  bx-rating-popup'
								},
								children: [
									BX.create('span', {
										props: {
											'className': 'bx-ilike-wait'
										}
									})
								]
							})
						]
					}),
					BX.create('span', {
						props: {
							'id': 'bx-rating-popup-cont-' + comm["RATING_TYPE_ID"] + '-' + comm["RATING_ENTITY_ID"] + '-' + anchor_id + '-minus'
						},
						style: {
							'display': 'none'
						},
						children: [
							BX.create('span', {
								props: {
									'className': 'bx-ilike-popup  bx-rating-popup'
								},
								children: [
									BX.create('span', {
										props: {
											'className': 'bx-ilike-wait'
										}
									})
								]
							})
						]
					})
				]
			});
		}
	}
	if (!!ratingNode)
	{
		ratingNode = BX.create('span', { children : [ ratingNode ] } );
		ratingNode = ratingNode.innerHTML +
			'<script>window["#OBJ#"].Set("#ID#", "#RATING_TYPE_ID#", #RATING_ENTITY_ID#, "#ALLOW_VOTE#", BX.message("sonetLCurrentUserID"), #TEMPLATE#, "light", BX.message("sonetLPathToUser"));</script>'.
			replace("#OBJ#", (BX.message("sonetLRatingType") == "like" ? "RatingLike" : "Rating")).
			replace("#ID#", comm["RATING_TYPE_ID"] + '-' + comm["RATING_ENTITY_ID"] + '-' + anchor_id).
			replace("#RATING_TYPE_ID#", comm["RATING_TYPE_ID"]).
			replace("#RATING_ENTITY_ID#", comm["RATING_ENTITY_ID"]).
			replace("#ALLOW_VOTE#", (!!commFormat["ALLOW_VOTE"] && !!commFormat["ALLOW_VOTE"]['RESULT'] ? 'Y' : 'N')).
			replace("#TEMPLATE#", (BX.message("sonetLRatingType") == "like" ?
				'{LIKE_Y:BX.message("sonetLTextLikeN"),LIKE_N:BX.message("sonetLTextLikeY"),LIKE_D:BX.message("sonetLTextLikeD")}' :
				'{PLUS:BX.message("sonetLTextPlus"),MINUS:BX.message("sonetLTextMinus"),CANCEL:BX.message("sonetLTextCancel")}'));

	}
	return ratingNode;
};
window["__logShowCommentForm"] = function(xmlId)
{
	if (!!window["UC"][xmlId])
		window["UC"][xmlId].reply();
};

var waitTimeout = null;
var waitDiv = null;
var	waitPopup = null;
var waitTime = 500;

function __logShowHiddenDestination(log_id, created_by_id, bindElement)
{
	var sonetLXmlHttpSet6 = new XMLHttpRequest();

	sonetLXmlHttpSet6.open("POST", BX.message('sonetLESetPath'), true);
	sonetLXmlHttpSet6.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

	sonetLXmlHttpSet6.onreadystatechange = function()
	{
		if(sonetLXmlHttpSet6.readyState == 4)
		{
			if(sonetLXmlHttpSet6.status == 200)
			{
				var data = LBlock.DataParser(sonetLXmlHttpSet6.responseText);
				if (typeof(data) == "object")
				{
					if (data[0] == '*')
					{
						if (sonetLErrorDiv != null)
						{
							sonetLErrorDiv.style.display = "block";
							sonetLErrorDiv.innerHTML = sonetLXmlHttpSet6.responseText;
						}
						return;
					}
					sonetLXmlHttpSet6.abort();
					var arDestinations = data["arDestinations"];
					
					if (typeof (arDestinations) == "object")
					{
						if (BX(bindElement))
						{
							var cont = bindElement.parentNode;
							cont.removeChild(bindElement);
							var url = '';

							for (var i = 0; i < arDestinations.length; i++)
							{
								if (typeof (arDestinations[i]['TITLE']) != 'undefined' && arDestinations[i]['TITLE'].length > 0)
								{
									cont.appendChild(BX.create('SPAN', {
										html: ',&nbsp;'
									}));

									if (typeof (arDestinations[i]['CRM_PREFIX']) != 'undefined' && arDestinations[i]['CRM_PREFIX'].length > 0)
									{
										cont.appendChild(BX.create('SPAN', {
											props: {
												className: 'feed-add-post-destination-prefix'
											},
											html: arDestinations[i]['CRM_PREFIX'] + ':&nbsp;'
										}));
									}
								
									if (typeof (arDestinations[i]['URL']) != 'undefined' && arDestinations[i]['URL'].length > 0)
									{
										cont.appendChild(BX.create('A', {
											props: {
												className: 'feed-add-post-destination-new' + (typeof (arDestinations[i]['IS_EXTRANET']) != 'undefined' && arDestinations[i]['IS_EXTRANET'] == 'Y' ? ' feed-post-user-name-extranet' : ''),
												'href': arDestinations[i]['URL']
											},
											html: arDestinations[i]['TITLE']
										}));
									}
									else
									{
										cont.appendChild(BX.create('SPAN', {
											props: {
												className: 'feed-add-post-destination-new' + (typeof (arDestinations[i]['IS_EXTRANET']) != 'undefined' && arDestinations[i]['IS_EXTRANET'] == 'Y' ? ' feed-post-user-name-extranet' : '')
											},
											html: arDestinations[i]['TITLE']
										}));
									}
								}
							}

							if (
								data["iDestinationsHidden"] != 'undefined'
								&& parseInt(data["iDestinationsHidden"]) > 0
							)
							{
								data["iDestinationsHidden"] = parseInt(data["iDestinationsHidden"]);
								var suffix = (
									(data["iDestinationsHidden"] % 100) > 10
									&& (data["iDestinationsHidden"] % 100) < 20
										? 5
										: data["iDestinationsHidden"] % 10
								);

								cont.appendChild(BX.create('SPAN', {
									html: '&nbsp;' + BX.message('sonetLDestinationHidden' + suffix).replace("#COUNT#", data["iDestinationsHidden"])
								}));
							}
						}
					}
				}
			}
			else
			{
				// error!
			}
		}
	};

	sonetLXmlHttpSet6.send("r=" + Math.floor(Math.random() * 1000)
		+ "&" + BX.message('sonetLSessid')
		+ "&site=" + BX.util.urlencode(BX.message('SITE_ID'))
		+ "&nt=" + BX.util.urlencode(BX.message('sonetLNameTemplate'))
		+ "&log_id=" + encodeURIComponent(log_id)
		+ (created_by_id ? "&created_by_id=" + encodeURIComponent(created_by_id) : "")
		+ "&p_user=" + BX.util.urlencode(BX.message('sonetLPathToUser'))
		+ "&p_group=" + BX.util.urlencode(BX.message('sonetLPathToGroup'))
		+ "&p_dep=" + BX.util.urlencode(BX.message('sonetLPathToDepartment'))
		+ "&dlim=" + BX.util.urlencode(BX.message('sonetLDestinationLimit'))
		+ "&action=get_more_destination"
	);

}

function __logSetFollow(log_id)
{
	var strFollowOld = (BX("log_entry_follow_" + log_id, true).getAttribute("data-follow") == "Y" ? "Y" : "N");
	var strFollowNew = (strFollowOld == "Y" ? "N" : "Y");	

	if (BX("log_entry_follow_" + log_id, true))
	{
		BX.findChild(BX("log_entry_follow_" + log_id, true), { tagName: 'a' }).innerHTML = BX.message('sonetLFollow' + strFollowNew);
		BX("log_entry_follow_" + log_id, true).setAttribute("data-follow", strFollowNew);
	}
				
	BX.ajax({
		url: BX.message('sonetLSetPath'),
		method: 'POST',
		dataType: 'json',
		data: {
			"log_id": log_id,
			"action": "change_follow",
			"follow": strFollowNew,
			"sessid": BX.bitrix_sessid(),
			"site": BX.message('sonetLSiteId')
		},
		onsuccess: function(data) {
			if (
				data["SUCCESS"] != "Y"
				&& BX("log_entry_follow_" + log_id, true)
			)
			{
				BX.findChild(BX("log_entry_follow_" + log_id, true), { tagName: 'a' }).innerHTML = BX.message('sonetLFollow' + strFollowOld);
				BX("log_entry_follow_" + log_id, true).setAttribute("data-follow", strFollowOld);
			}
		},
		onfailure: function(data) {
			if (BX("log_entry_follow_" +log_id, true))
			{
				BX.findChild(BX("log_entry_follow_" + log_id, true), { tagName: 'a' }).innerHTML = BX.message('sonetLFollow' + strFollowOld);
				BX("log_entry_follow_" + log_id, true).setAttribute("data-follow", strFollowOld);
			}		
		}
	});
	return false;
}

function __logRefreshEntry(params)
{
	var entryNode = (params.node !== undefined ? BX(params.node) : false);
	var logId = (params.logId !== undefined ? parseInt(params.logId) : 0);

	if (
		!entryNode
		|| logId <= 0
		|| BX.message('sonetLEPath') === undefined
	)
	{
		return;
	}

	BX.ajax({
		url: BX.message('sonetLEPath').replace("#log_id#", logId),
		method: 'POST',
		dataType: 'json',
		data: {
			"log_id": logId,
			"action": "get_entry"
		},
		onsuccess: function(data) {
			if (data["ENTRY_HTML"] !== undefined)
			{
				BX.cleanNode(entryNode);
				entryNode.innerHTML = data["ENTRY_HTML"];
				var ob = BX.processHTML(entryNode.innerHTML, true);
				var scripts = ob.SCRIPT;
				BX.ajax.processScripts(scripts, true);
			}
		},
		onfailure: function(data) {}
	});
	return false;
}

window.__logEditComment = function(entityXmlId, key, postId)
{
	BX.ajax({
		url: BX.message('sonetLESetPath'),
		method: 'POST',
		dataType: 'json',
		data: {
			"comment_id": key,
			"post_id": postId,
			"site" : BX.message('sonetLSiteId'),
			"action": "get_comment_src",
			"sessid": BX.bitrix_sessid()
		},
		onsuccess: function(data) 
		{
			if (
				typeof data.message != 'undefined'
				&& typeof data.sourceId != 'undefined'
			)
			{
				var eventData = {
					messageBBCode : data.message,
					messageFields : { 
						arFiles : (
							typeof data["UF"] != 'undefined'
							&& typeof data["UF"]["UF_SONET_COM_FILE"] != 'undefined'
								? data["UF"]["UF_SONET_COM_FILE"]["VALUE"]
								: []
						)
					}
				};

				if (
					typeof data["UF"] != 'undefined'
					&& typeof data["UF"]["UF_SONET_COM_DOC"] != 'undefined'
					&& typeof data["UF"]["UF_SONET_COM_DOC"]["USER_TYPE_ID"] != 'undefined'

				)
				{
					if (data["UF"]["UF_SONET_COM_DOC"]["USER_TYPE_ID"] == "webdav_element")
					{
						eventData["messageFields"]["arDocs"] = data["UF"]["UF_SONET_COM_DOC"]["VALUE"];
					}
					else if (data["UF"]["UF_SONET_COM_DOC"]["USER_TYPE_ID"] == "disk_file")
					{
						eventData["messageFields"]["arDFiles"] = data["UF"]["UF_SONET_COM_DOC"]["VALUE"];
					}
				}

				window["UC"][window.SLEC.formKey]["entitiesCorrespondence"][entityXmlId+'-'+data.sourceId] = [postId, data.id];
				BX.onCustomEvent(window, 'OnUCAfterRecordEdit', [entityXmlId, data.sourceId, eventData, 'EDIT']);
			}
		},
		onfailure: function(data) {}
	});
};

(function(){
	BX.SocialnetworkLogEntry = {
	};

	BX.SocialnetworkLogEntry.registerViewAreaList = function(params)
	{
		if (
			typeof params == 'undefined'
			|| typeof params.containerId == 'undefined'
			|| typeof params.className == 'undefined'
		)
		{
			return;
		}

		if (BX(params.containerId))
		{
			var
				viewAreaList = BX.findChildren(BX(params.containerId), {'tag':'div', 'className': params.className}, true),
				fullContentArea = null;

			for (var i = 0, length = viewAreaList.length; i < length; i++)
			{
				if (viewAreaList[i].id.length > 0)
				{
					fullContentArea = null;
					if (BX.type.isNotEmptyString(params.fullContentClassName))
					{
						fullContentArea = BX.findChild(viewAreaList[i], {
							className: params.fullContentClassName
						});
					}

					BX.UserContentView.registerViewArea(viewAreaList[i].id, (fullContentArea ? fullContentArea : null));
				}
			}
		}
	};
})();

