<?php

declare(strict_types=1);

namespace Bitrix\Ui\Public\Enum\IconSet;

enum Editor: string
{
	case BOLD = 'bold';
	case ITALIC = 'italic';
	case UNDERLINE = 'underline';
	case STRIKETHROUGH = 'strikethrough';
	case TEXT_COLOR = 'text-color';
	case REMOVE_FORMATTING = 'remove-formatting';
	case FONT_SIZE = 'font-size';
	case NUMBERED_LIST = 'numbered-list';
	case BULLETED_LIST = 'bulleted-list';
	case LEFT_ALIGN = 'left-align';
	case TEXT_AMOUNT = 'text-amount';
	case INCERT_IMAGE = 'incert-image';
	case INSERT_EMOJI = 'insert-emoji';
	case INSERT_SPOILER = 'insert-spoiler';
	case REMOVE_FONTSIZE = 'remove-fontsize';
	case VIEWMODE_WYSIWYG = 'viewmode-wysiwyg';
	case VIEWMODE_CODE = 'viewmode-code';
	case VIEWMODE_SPLIT_HOR = 'viewmode-split-hor';
	case VIEWMODE_SPLIT_VER = 'viewmode-split-ver';
	case UNDO = 'undo';
	case REDO = 'redo';
	case HEADER = 'header';
	case ERASER = 'eraser';
	case RULER_AND_PENCIL = 'ruler-and-pencil';
	case PAINT_BUCKET = 'paint-bucket';
	case SERVICE = 'service';
	case TEXT_CHECK = 'text-check';
	case PAINT_BUCKET_FORMATTING = 'paint-bucket-formatting';
	case NEW_FILE = 'new-file';
	case SETTINGS_5 = 'settings-5';
	case ANCHOR = 'anchor';
	case SUPERSCRIPT = 'superscript';
	case SUBSCRIPT = 'subscript';
	case HR = 'hr';
	case SPECIAL_CHARACTERS = 'special-characters';
	case CHECK_GRAMMAR = 'check-grammar';
	case BREAKS = 'breaks';
	case PRINT = 'print';
	case UNION = 'union';
	case MENTION = 'mention';
	case ADD_TAG = 'add-tag';
	case ENCLOSE_TEXT_IN_CODE_TAG = 'enclose-text-in-code-tag';
	case TABLE_EDITOR = 'table-editor';
	case BB_CODE_MODE = 'bb-code-mode';
	case FULL_SCREEN = 'full-screen';
	case CENTER_ALIGN = 'center-align';
	case RIGHT_LIGN = 'right-align';
	case JUSTIFY = 'justify';
	case DECREASE_INDENT = 'decrease-indent';
	case INCREASE_INDENT = 'increase-indent';
	case PARAGRAPH_BACKGROUND_COLOUR = 'paragraph-background-colour';
	case FORMATTING = 'formatting';
	case INSERT_VIDEO = 'insert-video';
	case SPEED_0_5 = 'speed-0-5';
	case SPEED_0_7 = 'speed-0-7';
	case SPEED_1_0 = 'speed-1-0';
	case SPEED_1_2 = 'speed-1-2';
	case SPEED_1_5 = 'speed-1-5';
	case SPEED_1_7 = 'speed-1-7';
	case SPEED_2_0 = 'speed-2-0';
	case MAKE_LONGER = 'make-longer';
	case MAKE_SHORTER = 'make-shorter';
}
