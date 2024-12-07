<?php
$MESS["CLO_STORAGE_ESTIMATE_DUPLICATES"] = "Estimate size and number of duplicates";
$MESS["CLO_STORAGE_LIST_ACTIVATE"] = "Activate";
$MESS["CLO_STORAGE_LIST_ACTIVE"] = "Active";
$MESS["CLO_STORAGE_LIST_ADD"] = "Add";
$MESS["CLO_STORAGE_LIST_ADD_TITLE"] = "Adds a new cloud storage connection";
$MESS["CLO_STORAGE_LIST_BUCKET"] = "Bucket";
$MESS["CLO_STORAGE_LIST_CANNOT_DELETE"] = "Cannot delete connection: #error_msg#.";
$MESS["CLO_STORAGE_LIST_CONT_MOVE_FILES"] = "Continue moving files to cloud storage";
$MESS["CLO_STORAGE_LIST_COPY"] = "Copying data to kernel module.";
$MESS["CLO_STORAGE_LIST_DEACTIVATE"] = "Deactivate";
$MESS["CLO_STORAGE_LIST_DEACTIVATE_CONF"] = "Deactivate cloud storage connection?";
$MESS["CLO_STORAGE_LIST_DELETE"] = "Delete";
$MESS["CLO_STORAGE_LIST_DELETE_CONF"] = "Delete cloud storage connection?";
$MESS["CLO_STORAGE_LIST_DOWNLOAD_DONE"] = "Files has been downloaded from the storage.";
$MESS["CLO_STORAGE_LIST_DOWNLOAD_IN_PROGRESS"] = "Files are now being downloaded from the storage.";
$MESS["CLO_STORAGE_LIST_DOWNLOAD_PROGRESS"] = "
<b>#remain# (#bytes#)</b> remains.
";
$MESS["CLO_STORAGE_LIST_DUPLICATES_INFO"] = "
Number of duplicates: <b>#count#</b><br>
Total size:<b> #size#</b><br>
<a href=\"#list_link#\">View files</a><br>
<b>Attention!</b><br>
1. Not all duplicates are safe to delete. Only the duplicates registered in the database and managed by the system modules can be deleted safely.<br>
The duplicates in temporary or service folders (resize_cache, bizproc etc.) will not be deleted.<br>
2. To search for duplicates, the system compares file sizes and checksums. No file content analysis is performed.<br>
A situation is possible where two contentually different files with identical sizes and checksums are considered duplicates.
";
$MESS["CLO_STORAGE_LIST_DUPLICATES_RESULT"] = "Duplicate search results";
$MESS["CLO_STORAGE_LIST_EDIT"] = "Edit";
$MESS["CLO_STORAGE_LIST_FILE_COUNT"] = "Files";
$MESS["CLO_STORAGE_LIST_FILE_SIZE"] = "Size";
$MESS["CLO_STORAGE_LIST_ID"] = "ID";
$MESS["CLO_STORAGE_LIST_LISTING"] = "Getting cloud storage file list";
$MESS["CLO_STORAGE_LIST_MODE"] = "Mode";
$MESS["CLO_STORAGE_LIST_MOVE_DONE"] = "Files has been moved to the storage.";
$MESS["CLO_STORAGE_LIST_MOVE_FILE_ERROR"] = "Error moving the file to cloud storage.";
$MESS["CLO_STORAGE_LIST_MOVE_IN_PROGRESS"] = "Files are now being moved to the storage.";
$MESS["CLO_STORAGE_LIST_MOVE_LOCAL"] = "Download files from cloud storage";
$MESS["CLO_STORAGE_LIST_MOVE_LOCAL_CONF"] = "Are you sure you want to move the files from the cloud back to the server?";
$MESS["CLO_STORAGE_LIST_MOVE_PROGRESS"] = "
Total files processed: <b>#total#</b>.<br>
Moved files: <b>#moved# (#bytes#)</b>, skipped files: <b>#skiped#</b>.
";
$MESS["CLO_STORAGE_LIST_NOT_EMPTY"] = "there are currently files in the storage";
$MESS["CLO_STORAGE_LIST_READ_ONLY"] = "Read-only";
$MESS["CLO_STORAGE_LIST_READ_WRITE"] = "Read/Write";
$MESS["CLO_STORAGE_LIST_SERVICE"] = "Service";
$MESS["CLO_STORAGE_LIST_SORT"] = "Sorting";
$MESS["CLO_STORAGE_LIST_START_MOVE_FILES"] = "Upload files to cloud storage";
$MESS["CLO_STORAGE_LIST_STOP"] = "Stop";
$MESS["CLO_STORAGE_LIST_TITLE"] = "Cloud Storages";
$MESS["CLO_STORAGE_LIST_UNKNOWN_ERROR"] = "Unknown error [#CODE#].";