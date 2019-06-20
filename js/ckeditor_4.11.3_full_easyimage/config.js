/**
 * @license Copyright (c) 2003-2019, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
	//config.image_previewText = ' ';
	config.removePlugins = 'easyimage, cloudservices';
	//config.removePlugins = 'cloudservices'; //[CKEDITOR] Error code: cloudservices-no-token-url.
	config.extraPlugins = 'youtube';
	//config.extraPlugins = 'html5video,widget,widgetselection,clipboard,lineutils';
	config.extraPlugins = 'html5video';
	//config.extraPlugins = 'videodetector';
	
	config.extraPlugins = 'youtube,html5video,html5audio';
	
	
	/*
	$config['ResourceType'][] = Array(
      'name' => 'Images',
      'url' => $baseUrl . 'images',
      'directory' => $baseDir . 'images',
      'maxSize' => 0,
      'allowedExtensions' => 'bmp,gif,jpeg,jpg,png',
      'deniedExtensions' => '');
	*/
	
	
	//config.extraPlugins = 'html5audio';
	
	//config.extraPlugins = 'toolbar';
	//config.allowedContent = true;
	//config.allowedContent= 'iframe[*]' ;
	//config.youtube_related = true;
	//config.extraAllowedContent = 'iframe[*]';
	//config.toolbar = 'Basic';
	//config.removeButtons = 'Flash,IFrame';
	//config.toolbarLocation =  'bottom';
	//config.removePlugins = 'elementspath,resize';
	/*
    config.toolbarGroups = [
    { name: 'clipboard',   groups: [ 'clipboard', 'undo' ] },
    { name: 'editing',     groups: [ 'find', 'selection', 'spellchecker' ] },
    { name: 'links' },
    { name: 'insert' },
    //{ name: 'forms' },
    { name: 'tools' },
    { name: 'document',       groups: [ 'mode', 'document', 'doctools' ] },
    { name: 'others' },
    '/',
    { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
    { name: 'paragraph',   groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ] },
    { name: 'styles' },
    { name: 'colors' },
    //{ name: 'about' }
    ];	
    */
	/*
	config.toolbar = [
    { name: 'document', items: [ 'Source', '-', 'NewPage', 'Preview', '-', 'Templates' ] },
    { name: 'clipboard', items: [ 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo' ] },
    '/',
    { name: 'basicstyles', items: [ 'Bold', 'Italic' ] }
];
	*/
config.toolbar = 
  [
    ['Source','Cut','Copy','Paste','PasteText','PasteFromWord','-','Print', 'SpellChecker', 'Scayt'],
    ['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
    '/',
    ['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
    ['NumberedList','BulletedList','-','Outdent','Indent','Blockquote'],
    ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
    ['Link','Unlink','Anchor'],
    ['Image','Youtube','Html5video','Html5audio','Table','HorizontalRule','Smiley','SpecialChar','PageBreak'],
    '/',
    ['Styles','Format','Font','FontSize'],
    ['TextColor','BGColor'],
    ['Maximize', 'ShowBlocks']
  ];
  
};
