<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

IncludeTemplateLangFile(__FILE__);

return array(
	// First letter
	"bxe-first-letter" => array('tag' => 'p', "title" => GetMessage("STYLES_LETTER")),

	// Quote
	"bxe-quote" => array('tag' => 'blockquote', "title" => GetMessage("STYLES_BXE_QUOTE"), "section" => 'quote'),
	"blockquote" => array('tag' => 'blockquote', "title" => GetMessage("STYLES_BLOCKQUOTE"), "section" => 'quote'),
	"lead" => array('tag' => 'p', "title" => GetMessage("STYLES_LEAD"), "section" => 'quote'),

	// Text
	"bg-primary" => array('tag' => 'span', "title" => GetMessage("STYLES_BLUE"), "html" => '<span style="background: #60aadb;color: #fff; padding: 4px;">'.GetMessage("STYLES_BLUE_BG").'</span>', "section" => 'text'),
	"bg-success" => array('tag' => 'span', "title" => GetMessage("STYLES_GREEN"), "html" => '<span style="background: #64ba4e;color: #fff; padding: 4px;">'.GetMessage("STYLES_GREEN_BG").'</span>', "section" => 'text'),
	"bg-info" => array('tag' => 'span', "title" => GetMessage("STYLES_AZURE"), "html" => '<span style="background: #9bc6dd;color: #fff; padding: 4px;">'.GetMessage("STYLES_AZURE_BG").'</span>', "section" => 'text'),
	"bg-warning" => array('tag' => 'span', "title" => GetMessage("STYLES_YELLOW"), "html" => '<span style="background: #fec139;color: #fff; padding: 4px;">'.GetMessage("STYLES_YELLOW_BG").'</span>', "section" => 'text'),
	"bg-danger" => array('tag' => 'span', "title" => GetMessage("STYLES_PINK"), "html" => '<span style="background: #f2dede;color: #000; padding: 4px;">'.GetMessage("STYLES_PINK_BG").'</span>', "section" => 'text'),

	// Block
	"alert alert-success" => array("title" => GetMessage("STYLES_BLOCK_GREEN"), 'tag' => 'DIV', "section" => 'block', "html" => '<div style="background-color:#dff0d8;border: 1px solid #d6e9c6;color:#3c763d;padding:5px;border-radius:3px; min-width: 170px;">'.GetMessage("STYLES_BLOCK_GREEN").'</div>'),
	"alert alert-info" => array("title" => GetMessage("STYLES_BLOCK_BLUE"), 'tag' => 'DIV', "section" => 'block', "html" => '<div style="background-color:#d9edf7;border:1px solid #bce8f1;color:#31708f;padding:5px;border-radius:3px;min-width: 170px;">'.GetMessage("STYLES_BLOCK_BLUE").'</div>'),
	"alert alert-warning" => array("title" => GetMessage("STYLES_BLOCK_YELLOW"), 'tag' => 'DIV', "section" => 'block', "html" => '<div style="background-color:#fcf8e3;border:1px solid #faebcc;color:#8a6d3b; padding:5px; border-radius:3px; min-width: 170px;">'.GetMessage("STYLES_BLOCK_YELLOW").'</div>'),
	"alert alert-danger" => array("title" => GetMessage("STYLES_BLOCK_RED"), 'tag' => 'DIV', "section" => 'block', "html" => '<div style="background-color:#f2dede;border:1px solid #ebccd1;color:#a94442; padding:5px;  border-radius:3px; min-width: 170px;">'.GetMessage("STYLES_BLOCK_RED").'</div>'),
	"alert alert-note" => array("title" => GetMessage("STYLES_BLOCK_GRAY"), 'tag' => 'DIV', "section" => 'block', "html" => '<div style="background-color:#f2f2f3;border: 1px solid #f2f2f3;padding:5px;  border-radius:3px; min-width: 170px;">'.GetMessage("STYLES_BLOCK_GRAY").'</div>'),
	"alert alert-sys" => array("title" => GetMessage("STYLES_BLOCK_BROWN"), 'tag' => 'DIV', "section" => 'block', "html" => '<div style="background-color:#f5f2ec;border:1px solid #f5f2ec;color:#81641f; padding:5px;  border-radius:3px; min-width: 170px;">'.GetMessage("STYLES_BLOCK_BROWN").'</div>'),

	// Block with icon
	"alert bxe-icon alert-success" => array("title" => GetMessage("STYLES_BLOCK_ICON_GREEN"), 'tag' => 'DIV', "section" => 'block_icon', "html" => '<div style="background-color:#dff0d8;border: 1px solid #d6e9c6;color:#3c763d;padding:5px;border-radius:3px; min-width: 170px;">'.GetMessage("STYLES_BLOCK_ICON_GREEN").'</div>'),
	"alert bxe-icon alert-info" => array("title" => GetMessage("STYLES_BLOCK_ICON_BLUE"), 'tag' => 'DIV', "section" => 'block_icon', "html" => '<div style="background-color:#d9edf7;border:1px solid #bce8f1;color:#31708f;padding:5px;border-radius:3px; min-width: 170px;">'.GetMessage("STYLES_BLOCK_ICON_BLUE").'</div>'),
	"alert bxe-icon alert-warning" => array("title" => GetMessage("STYLES_BLOCK_ICON_YELLOW"), 'tag' => 'DIV', "section" => 'block_icon', "html" => '<div style="background-color:#fcf8e3;border:1px solid #faebcc;color:#8a6d3b;padding:5px;  border-radius:3px; min-width: 170px;">'.GetMessage("STYLES_BLOCK_ICON_YELLOW").'</div>'),
	"alert bxe-icon alert-danger" => array("title" => GetMessage("STYLES_BLOCK_ICON_RED"), 'tag' => 'DIV', "section" => 'block_icon', "html" => '<div style="background-color:#f2dede;border:1px solid #ebccd1;color:#a94442;padding:5px;  border-radius:3px; min-width: 170px;">'.GetMessage("STYLES_BLOCK_ICON_RED").'</div>'),
	"alert bxe-icon alert-note" => array("title" => GetMessage("STYLES_BLOCK_ICON_GRAY"), 'tag' => 'DIV', "section" => 'block_icon', "html" => '<div style="background-color:#f2f2f3;border: 1px solid #f2f2f3;padding:5px;  border-radius:3px; min-width: 170px;">'.GetMessage("STYLES_BLOCK_ICON_GRAY").'</div>'),
	"alert bxe-icon alert-sys" => array("title" => GetMessage("STYLES_BLOCK_ICON_BROWN"), 'tag' => 'DIV', "section" => 'block_icon', "html" => '<div style="background-color:#f5f2ec;border:1px solid #f5f2ec;color:#81641f;padding:5px;  border-radius:3px; min-width: 170px;">'.GetMessage("STYLES_BLOCK_ICON_BROWN").'</div>'),

	// Lists
	"ul" => array("title" => GetMessage("STYLES_STANDART"), "html" => '<UL style="margin: 0; padding:0 0 0 10px;"><LI>'.GetMessage("STYLES_STANDART_LIST").'</LI></UL>', "section" => 'list', 'tag' => 'UL'),
	"bxe-list bxe-lis-blue~~fa fa-check-square" => array('section'=>'list', 'tag' => 'UL', "title" => GetMessage("STYLES_LIST_CHECK_SQUARE"), "html" => '<ul  style="margin: 0; padding:0; list-style: none;"><li><i class="fa fa-check-square" style="color: #0083d1;margin-right: 6px"></i>'.GetMessage("STYLES_LIST_CHECK_SQUARE").'</li><ul>'),
	"bxe-list bxe-lis-blue~~fa fa-check" => array('section'=>'list', 'tag' => 'UL', "title" => GetMessage("STYLES_LIST_CHECK"), "html" => '<ul  style="margin: 0; padding:0; list-style: none;"><li><i class="fa fa-check" style="color: #0083d1;margin-right: 6px"></i>'.GetMessage("STYLES_LIST_CHECK").'</li><ul>'),
	"bxe-list bxe-lis-blue~~fa fa-minus" => array('section'=>'list', 'tag' => 'UL', "title" => GetMessage("STYLES_LIST_MINUS"), "html" => '<ul  style="margin: 0; padding:0; list-style: none;"><li><i class="fa fa-minus" style="color: #0083d1;margin-right: 6px"></i>'.GetMessage("STYLES_LIST_MINUS").'</li><ul>'),
	"bxe-list bxe-lis-blue~~fa fa-map-marker" => array('section'=>'list', 'tag' => 'UL', "title" => GetMessage("STYLES_LIST_MAP_MARKER"), "html" => '<ul  style="margin: 0; padding:0; list-style: none;"><li><i class="fa fa-map-marker" style="color: #0083d1;margin-right: 6px"></i>'.GetMessage("STYLES_LIST_MAP_MARKER").'</li><ul>'),
	"bxe-list bxe-lis-blue~~fa fa-rocket" => array('section'=>'list', 'tag' => 'UL', "title" => GetMessage("STYLES_LIST_ROCKET"), "html" => '<ul  style="margin: 0; padding:0; list-style: none;"><li><i class="fa fa-rocket" style="color: #0083d1;margin-right: 6px"></i>'.GetMessage("STYLES_LIST_ROCKET").'</li><ul>'),
	"bxe-list bxe-lis-blue~~fa fa-paw" => array('section'=>'list', 'tag' => 'UL', "title" => GetMessage("STYLES_LIST_PAW"), "html" => '<ul  style="margin: 0; padding:0; list-style: none;"><li><i class="fa fa-paw" style="color: #0083d1;margin-right: 6px"></i>'.GetMessage("STYLES_LIST_PAW").'</li><ul>'),
	"bxe-list bxe-lis-blue~~fa fa-paper-plane" => array('section'=>'list', 'tag' => 'UL', "title" => GetMessage("STYLES_LIST_PLANE"), "html" => '<ul  style="margin: 0; padding:0; list-style: none;"><li><i class="fa fa-paper-plane" style="color: #0083d1;margin-right: 6px"></i>'.GetMessage("STYLES_LIST_PLANE").'</li><ul>'),
	"bxe-list bxe-lis-blue~~fa fa-certificate" => array('section'=>'list', 'tag' => 'UL', "title" => GetMessage("STYLES_LIST_CERTIFICATE"), "html" => '<ul  style="margin: 0; padding:0; list-style: none;"><li><i class="fa fa-certificate" style="color: #0083d1;margin-right: 6px"></i>'.GetMessage("STYLES_LIST_CERTIFICATE").'</li><ul>'),
	"bxe-list bxe-lis-blue~~fa fa-flag" => array('section'=>'list', 'tag' => 'UL', "title" => GetMessage("STYLES_LIST_FLAG"), "html" => '<ul  style="margin: 0; padding:0; list-style: none;"><li><i class="fa fa-flag" style="color: #0083d1;margin-right: 6px"></i>'.GetMessage("STYLES_LIST_FLAG").'</li><ul>'),
	"bxe-list bxe-lis-blue~~fa fa-bomb" => array('section'=>'list', 'tag' => 'UL', "title" => GetMessage("STYLES_LIST_BOMB"), "html" => '<ul  style="margin: 0; padding:0; list-style: none;"><li><i class="fa fa-bomb" style="color: #0083d1;margin-right: 6px"></i>'.GetMessage("STYLES_LIST_BOMB").'</li><ul>'),
	"bxe-list bxe-lis-blue~~fa fa-close" => array('section'=>'list', 'tag' => 'UL', "title" => GetMessage("STYLES_LIST_CLOSE"), "html" => '<ul  style="margin: 0; padding:0; list-style: none;"><li><i class="fa fa-close" style="color: #0083d1;margin-right: 6px"></i>'.GetMessage("STYLES_LIST_CLOSE").'</li><ul>'),
	"bxe-list bxe-lis-blue~~fa fa-star" => array('section'=>'list', 'tag' => 'UL', "title" => GetMessage("STYLES_LIST_STAR"), "html" => '<ul  style="margin: 0; padding:0; list-style: none;"><li><i class="fa fa-star" style="color: #0083d1;margin-right: 6px"></i>'.GetMessage("STYLES_LIST_STAR").'</li><ul>'),
	"bxe-list bxe-lis-blue~~fa fa-times-circle" => array('section'=>'list', 'tag' => 'UL', "title" => GetMessage("STYLES_LIST_TIMES_CIRCLE"), "html" => '<ul  style="margin: 0; padding:0; list-style: none;"><li><i class="fa fa-times-circle" style="color: #0083d1;margin-right: 6px"></i>'.GetMessage("STYLES_LIST_TIMES_CIRCLE").'</li><ul>'),
	"bxe-list bxe-lis-blue~~fa fa-caret-square-o-right" => array('section'=>'list', 'tag' => 'UL', "title" => GetMessage("STYLES_LIST_VIDEO"), "html" => '<ul  style="margin: 0; padding:0; list-style: none;"><li><i class="fa fa-caret-square-o-right" style="color: #0083d1;margin-right: 6px"></i>'.GetMessage("STYLES_LIST_VIDEO").'</li><ul>'),
	"bxe-list bxe-lis-blue~~fa fa-heart" => array('section'=>'list', 'tag' => 'UL', "title" => GetMessage("STYLES_LIST_HEART"), "html" => '<ul  style="margin: 0; padding:0; list-style: none;"><li><i class="fa fa-heart" style="color: #0083d1;margin-right: 6px"></i>'.GetMessage("STYLES_LIST_HEART").'</li><ul>'),
);
