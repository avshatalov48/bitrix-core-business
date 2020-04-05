<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<script>

//	it is scripts from old-version editor. Them work with new editor too and not was overwriting.

function showComment(key, error, userName, userEmail, needData)
{
	<?
	if($arResult["use_captcha"]===true)
	{
		?>
		BX.ajax.getCaptcha(function(data) {
			BX("captcha_word").value = "";
			BX("captcha_code").value = data["captcha_sid"];
			BX("captcha").src = '/bitrix/tools/captcha.php?captcha_code=' + data["captcha_sid"];
			BX("captcha").style.display = "";
		});
		<?
	}
	?>
	subject = '';
	comment = '';

	if(needData == "Y")
	{
		subject = window["title"+key];
		comment = window["text"+key];
	}
	
	var pFormCont = BX('form_c_del');
	BX('form_comment_' + key).appendChild(pFormCont); // Move form
	pFormCont.style.display = "block";

	document.form_comment.parentId.value = key;
	document.form_comment.edit_id.value = '';
	document.form_comment.act.value = 'add';
	document.form_comment.post.value = '<?=GetMessageJS("B_B_MS_SEND")?>';
	document.form_comment.action = document.form_comment.action + "#" + key;

	if(error == "Y")
	{
		if(comment.length > 0)
		{
			comment = comment.replace(/\/</gi, '<');
			comment = comment.replace(/\/>/gi, '>');
		}
		if(userName.length > 0)
		{
			userName = userName.replace(/\/</gi, '<');
			userName = userName.replace(/\/>/gi, '>');
			document.form_comment.user_name.value = userName;
		}
		if(userEmail.length > 0)
		{
			userEmail = userEmail.replace(/\/</gi, '<');
			userEmail = userEmail.replace(/\/>/gi, '>');
			document.form_comment.user_email.value = userEmail;
		}
		if(subject && subject.length>0 && document.form_comment.subject)
		{
			subject = subject.replace(/\/</gi, '<');
			subject = subject.replace(/\/>/gi, '>');
			document.form_comment.subject.value = subject;
		}
	}

	files = BX('form_comment')["UF_BLOG_COMMENT_DOC[]"];
	if(files !== null && typeof files != 'undefined')
	{
		if(!files.length)
		{
			BX.remove(files);
		}
		else
		{
			for(i = 0; i < files.length; i++)
				BX.remove(BX(files[i]));
		}
	}
	filesForm = BX.findChild(BX('blog-comment-user-fields-UF_BLOG_COMMENT_DOC'), {'className': 'file-placeholder-tbody' }, true, false);
	if(filesForm !== null && typeof filesForm != 'undefined')
		BX.cleanNode(filesForm, false);

	filesForm = BX.findChild(BX('blog-comment-user-fields-UF_BLOG_COMMENT_DOC'), {'className': 'feed-add-photo-block' }, true, true);
	if(filesForm !== null && typeof filesForm != 'undefined')

	{
		for(i = 0; i < filesForm.length; i++)
		{
			if(BX(filesForm[i]).parentNode.id != 'file-image-template')
				BX.remove(BX(filesForm[i]));
		}
	}

	filesForm = BX.findChild(BX('blog-comment-user-fields-UF_BLOG_COMMENT_DOC'), {'className': 'file-selectdialog' }, true, false);
	if(filesForm !== null && typeof filesForm != 'undefined')
	{
		BX.hide(BX.findChild(BX('blog-comment-user-fields-UF_BLOG_COMMENT_DOC'), {'className': 'file-selectdialog' }, true, false));
		BX.show(BX('blog-upload-file'));
	}

	onLightEditorShow(comment);
	return false;
}


function hideShowComment(url, id)
{
	var bcn = BX('blg-comment-'+id);
	BX.showWait(bcn);
	bcn.id = 'blg-comment-'+id+'old';
	BX('err_comment_'+id).innerHTML = '';

	BX.ajax.get(url, function(data) {
		var obNew = BX.processHTML(data, true);
		scripts = obNew.SCRIPT;
		BX.ajax.processScripts(scripts, true);
		var nc = BX('new_comment_'+id);
		var bc = BX('blg-comment-'+id+'old');
		nc.style.display = "none";
		nc.innerHTML = data;
		
		if(BX('blg-comment-'+id))
		{
			bc.innerHTML = BX('blg-comment-'+id).innerHTML;
		}
		else
		{
			BX('err_comment_'+id).innerHTML = nc.innerHTML;
		}
		BX('blg-comment-'+id+'old').id = 'blg-comment-'+id;

		BX.closeWait();
	});

	return false;
}

function deleteComment(url, id)
{
	BX.showWait(BX('blg-comment-'+id));

	BX.ajax.get(url, function(data) {
		var obNew = BX.processHTML(data, true);
		scripts = obNew.SCRIPT;
		BX.ajax.processScripts(scripts, true);

		var nc = BX('new_comment_'+id);
		nc.style.display = "none";
		nc.innerHTML = data;

		if(BX('blg-com-err'))
		{
			BX('err_comment_'+id).innerHTML = nc.innerHTML;
		}
		else
		{
			BX('blg-comment-'+id).innerHTML = nc.innerHTML;
		}
		nc.innerHTML = '';
		
		BX.closeWait();
	});

	return false;
}
<?if($arResult["NEED_NAV"] == "Y"):?>
function bcNavAjax(page, th)
{
//	get url from attrubute or just from href
	var href = th.getAttribute('data-bx-href');
	if(href == null || href == 'undefined')
		href = th.href;
	BX.showWait(th);
	BX.ajax({
		'method': 'POST',
		'url': href,
		'dataType': 'html',
		'processData': false,
		onsuccess: BX.proxy(function (data) {
			var answer = BX.create('div',{'html':data});
			var newPage = BX.findChild(answer, {"attribute" : {"id": 'blog-comment-page'}}, true);
			BX("blog-comment-page").innerHTML = newPage.innerHTML;

//			marking page numbers
			BX.removeClass(BX.findChild(BX('blog-comment-nav-t'), {"className": 'blog-comment-nav-item-sel'}, true), 'blog-comment-nav-item-sel');
			BX.removeClass(BX.findChild(BX('blog-comment-nav-b'), {"className": 'blog-comment-nav-item-sel'}, true), 'blog-comment-nav-item-sel');
			BX.addClass(BX('blog-comment-nav-t'+page), 'blog-comment-nav-item-sel');
			BX.addClass(BX('blog-comment-nav-b'+page), 'blog-comment-nav-item-sel');
			
			BX.closeWait();
		}),
		onfailure: function(){
			BX.closeWait();
			return false;
		}
	});
	
	return false;
}

function bcNav(page, th)
{
	BX.showWait(th);
	setTimeout(function() {
		for(i = 1; i <= <?=$arResult["PAGE_COUNT"]?>; i++)
		{
			if(i == page)
			{
				BX.addClass(BX('blog-comment-nav-t'+i), 'blog-comment-nav-item-sel');
				BX.addClass(BX('blog-comment-nav-b'+i), 'blog-comment-nav-item-sel');
				BX('blog-comment-page-'+i).style.display = "";
			}
			else
			{
				BX.removeClass(BX('blog-comment-nav-t'+i), 'blog-comment-nav-item-sel');
				BX.removeClass(BX('blog-comment-nav-b'+i), 'blog-comment-nav-item-sel');
				BX('blog-comment-page-'+i).style.display = "none";
			}
		}
		BX.closeWait();
	}, 300);
	return false;
}
<?endif;?>

</script>