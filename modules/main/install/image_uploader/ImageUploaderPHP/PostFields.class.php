<?php
class PostFields {
  // package fields
  const packageIndex = 'PackageIndex';
  const packageCount = 'PackageCount';
  const packageGuid = 'PackageGuid';
  const packageFileCount = 'PackageFileCount';
  
  // source file fields
  const sourceName = 'SourceName_%d';
  const sourceSize = 'SourceSize_%d';
  const sourceCreatedDateTime = 'SourceCreatedDateTime_%d';
  const sourceLastModifiedDateTime = 'SourceLastModifiedDateTime_%d';
  const sourceCreatedDateTimeLocal = 'SourceCreatedDateTimeLocal_%d';
  const sourceLastModifiedDateTimeLocal = 'SourceLastModifiedDateTimeLocal_%d';
  const sourceWidth = 'SourceWidth_%d';
  const sourceHeight = 'SourceHeight_%d';
  const horizontalResolution = 'HorizontalResolution_%d';
  const verticalResolution = 'VerticalResolution_%d';
  
  // crop bounds
  const cropBounds = 'CropBounds_%d';
  
  // converted file fields
  const file = 'File%d_%d';
  const fileMode = 'File%dMode_%d';
  const fileName = 'File%dName_%d';
  const fileSize = 'File%dSize_%d';
  const fileWidth = 'File%dWidth_%d';
  const fileHeight = 'File%dHeight_%d';
  
  // other fields
  const angle = 'Angle_%d';
  const description = 'Description_%d';
  const tag = 'Tag_%d';
  
  // chunk fields
  const fileChunkCount = 'File%dChunkCount_%d';
  const fileChunkIndex = 'File%dChunkIndex_%d';
  
  // complete markers
  const requestComplete = 'RequestComplete';
  const packageComplete = 'PackageComplete';
  
}