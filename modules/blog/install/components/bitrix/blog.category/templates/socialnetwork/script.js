function category_edit(id)
{
	if (id == 0)
		document.getElementById("category_name").value = '';
	else
		document.getElementById("category_name").value = document.getElementById("name_" + id).value;
	document.getElementById("category_id").value = id;
	show_form(1);
}

function category_del(id)
{
	if (document.getElementById("count_" + id).value == 0 || window.confirm(BX.message("BLOG_CONFIRM_DELETE")))
	{
		document.getElementById("category_id").value = id;
		document.getElementById("category_del").value = "Y";
		document.REPLIER.submit();
	}
}

function show_form(flag)
{
	if (flag==1)
	{
		document.getElementById("edit_form").style.display = 'block';
		document.getElementById("category_name").focus();
	}
	else
		document.getElementById("edit_form").style.display = 'none';
}

function submitForm()
{
	BX("REPLIER").submit();
}