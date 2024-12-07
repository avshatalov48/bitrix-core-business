<?
//**************************** MEDIALIB ACTIONS *************************************
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
CModule::IncludeModule("fileman");

if (!CMedialib::CanDoOperation('medialib_view_collection', 0))
	die();

echo '<div style="display:none">BX_ML_LOAD_OK</div>';
if (!check_bitrix_sessid())
	die('<!--BX_ML_DUBLICATE_ACTION_REQUEST'.bitrix_sessid().'-->');

$action = isset($_GET['action']) ? $_GET['action'] : false;

if ($action == 'start')
{
	CMedialib::Start(array(
		'site' => isset($_REQUEST['site']) ? $_REQUEST['site'] : false,
		'lang' => isset($_REQUEST['lang']) ? $_REQUEST['lang'] : 'en',
		'types' => ($_POST['types'] ?? null)
	));
}
elseif ($action == 'get_items')
{
	CMedialib::GetItems(array(
		'site' => isset($_REQUEST['site']) ? $_REQUEST['site'] : false,
		'lang' => isset($_REQUEST['lang']) ? $_REQUEST['lang'] : 'en',
		'collectionId' => intval($_POST['col_id'])
	));
}
elseif ($action == 'edit_collection')
{
	$parent = intval($_POST['parent']);
	$id = CMedialib::EditCollection(array(
		'id' => intval(($_POST['id'] ?? null)),
		'name' => rawurldecode($_POST['name']),
		'desc' => rawurldecode($_POST['desc']),
		'keywords' => rawurldecode($_POST['keywords']),
		'parent' =>$parent,
		'site' => ($_GET['site'] ?? null),
		'type' => $_POST['type']
	));

	?><script>window.bx_req_res = {
		id: <?echo $id === false ? 'false' : $id;?>,
		access: {
			new_col: '<?= CMedialib::CanDoOperation('medialib_new_collection', $parent)?>',
			edit: '<?= CMedialib::CanDoOperation('medialib_edit_collection', $parent)?>',
			del: '<?= CMedialib::CanDoOperation('medialib_del_collection', $parent)?>',
			new_item: '<?= CMedialib::CanDoOperation('medialib_new_item', $parent)?>',
			edit_item: '<?= CMedialib::CanDoOperation('medialib_edit_item', $parent)?>',
			del_item: '<?= CMedialib::CanDoOperation('medialib_del_item', $parent)?>',
			access: '<?= CMedialib::CanDoOperation('medialib_access', $parent)?>'
		}
	}
	</script><?
}
elseif ($action == 'del_collection')
{
	$res = CMedialib::DelCollection(intval(($_POST['id'] ?? null)), $_POST['child_cols'] ?? null);
	?><script>window.bx_req_res = <?= ($res ? 'true' : 'false')?>;</script><?
}
elseif ($action == 'edit_item')
{
	CMedialib::EditItem(array(
		'lang' => isset($_REQUEST['lang']) ? $_REQUEST['lang'] : 'en',
		'site' => isset($_REQUEST['site']) ? $_REQUEST['site'] : false,
		'id' => intval(($_REQUEST['id'] ?? null)),
		'file' => isset($_FILES["load_file"]) ? $_FILES["load_file"] : false,
		'path' => isset($_POST["item_path"]) ? $_POST["item_path"] : '',
		'path_site' => isset($_POST["item_path_site"]) ? $_POST["item_path_site"] : '',
		'source_type' => isset($_POST["source_type"]) ? $_POST["source_type"] : '',
		'name' => isset($_POST["item_name"]) ? $APPLICATION->UnJSEscape($_POST["item_name"]) : '',
		'desc' => isset($_POST["item_desc"]) ? $APPLICATION->UnJSEscape($_POST["item_desc"]) : '',
		'keywords' => isset($_POST["item_keywords"]) ? $APPLICATION->UnJSEscape($_POST["item_keywords"]) : '',
		'item_collections' => $_POST["item_collections"]
	));
}
elseif ($action == 'del_item')
{
	$res = CMedialib::DelItem(intval(($_POST['id'] ?? null)), $_POST['mode'] == 'current', intval($_POST['col_id']));
	?><script>window.bx_req_res = <?= ($res ? 'true' : 'false')?>;</script><?
}
elseif ($action == 'save_settings')
{
	CMedialib::SaveUserSettings(array(
		'width' => intval($_POST['width']),
		'height' => intval($_POST['height']),
		'coll_id' => intval($_POST['coll_id'])
	));
}
elseif ($action == 'upload_form')
{
	CMedialib::ShowUploadForm(array(
		'lang' => isset($_REQUEST['lang']) ? $_REQUEST['lang'] : 'en',
		'site' => isset($_REQUEST['site']) ? $_REQUEST['site'] : false
	));
}
elseif ($action == 'get_item_coll_list')
{
	CMedialib::GetItemCollectionList(array('ID' => intval(($_POST['id'] ?? null))));
}
elseif ($action == 'multi_del')
{
	CMedialib::MultiActionDelete(array(
		'Items' => $_POST['items'] ?? null,
		'Cols' => $_POST['cols'] ?? null,
	));
}
elseif ($action == 'search')
{
	CMedialib::SearchItems(array(
		'query' => $APPLICATION->UnJSEscape($_POST['q']),
		'types' => ($_POST['types'] ?? null)
	));
}
elseif ($action == 'get_item_view')
{
	CMedialib::GetItemViewHTML(intval(($_POST['id'] ?? null)));
}
elseif ($action == 'change_col_type')
{
	CMedialib::ChangeColType(array(
		'col' => intval($_POST['col']),
		'type' => intval($_POST['type']),
		'parent' => intval($_POST['parent']),
		'childCols' => $_POST['children'] ?? [],
	));
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>
