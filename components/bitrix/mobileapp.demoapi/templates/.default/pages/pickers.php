<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>
<button class="styled-button" onclick="BXMobileDemoApi.pickers.showDatePicker()"><i class="fa fa-calendar"></i> <?=GetMessage("MB_DEMO_PICK_DATE");?>
</button>
<button class="styled-button" onclick="BXMobileDemoApi.pickers.showTimePicker()"><i class="fa fa-calendar"></i> <?=GetMessage("MB_DEMO_PICK_TIME");?>
</button>
<button class="styled-button" onclick="BXMobileDemoApi.pickers.showDateTimePicker()"><i class="fa fa-calendar"></i> <?=GetMessage("MB_DEMO_PICK_DATE_TIME");?>
</button>
<button class="styled-button" onclick="BXMobileDemoApi.pickers.showDateTimePickerMinAndMax()"><i class="fa fa-calendar"></i> <?=GetMessage("MB_DEMO_PICK_DATE_TIME_LIMIT");?>
</button>
<button class="styled-button" onclick="BXMobileDemoApi.pickers.showSinglePicker()"><i class="fa fa-calendar"></i> <?=GetMessage("MB_DEMO_PICK_VALUE");?>
<button class="styled-button" onclick="BXMobileDemoApi.pickers.showMultiPicker()"><i class="fa fa-calendar"></i> <?=GetMessage("MB_DEMO_PICK_VALUE_MULTI");?>
</button>

