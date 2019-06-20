/**
 * @license Copyright (c) 2003-2019, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

CKEDITOR.editorConfig = function( config ) {
  config.extraPlugins = 'youtube,html5video,html5audio,mediaembed';
  //config.extraPlugins = 'html5video,html5audio,mediaembed';
  //config.extraPlugins = 'youtube,html5video,html5audio';
  //config.extraPlugins = 'youtube,html5video,widget,widgetselection,clipboard,lineutils,html5audio,';
  //config.removeButtons = 'BrowseServer';
  config.removePlugins = 'iframe';
  config.allowedContent = true;
  //config.extraAllowedContent = 'p()[]{};div[id];iframe[];html[];script[]';
  
  
  config.toolbar = 
  [
    ['Source','Cut','Copy','Paste','PasteText','PasteFromWord','-','Print', 'SpellChecker', 'Scayt'],
    ['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
    '/',
    ['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
    ['NumberedList','BulletedList','-','Outdent','Indent','Blockquote'],
    ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
    ['Link','Unlink','Anchor'],
    ['Image','Youtube','Html5video','MediaEmbed','Html5audio','Flash','Table','HorizontalRule','Smiley','SpecialChar','PageBreak'],
	//['Image','Youtube','Html5video','Html5audio','Flash','MediaEmbed','Iframe','Table','embed','HorizontalRule','Smiley','SpecialChar','PageBreak'],
    //['Image','MediaEmbed','Html5video','Html5audio','Flash','Table','embed','HorizontalRule','Smiley','SpecialChar','PageBreak'],
	'/',
    ['Styles','Format','Font','FontSize'],
    ['TextColor','BGColor'],
    ['Maximize', 'ShowBlocks']
  ];
  
};
