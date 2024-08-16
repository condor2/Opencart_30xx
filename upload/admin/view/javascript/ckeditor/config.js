/**
 * @license Copyright (c) 2003-2024, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';

	config.filebrowserWindowWidth = '800';
	config.filebrowserWindowHeight = '500';
	config.resize_enabled = true;

	config.htmlEncodeOutput = false;
	config.entities = false;
	config.allowedContent = true;
	config.extraPlugins = 'codemirror';
	config.codemirror_theme = 'monokai';
	config.versionCheck = false;
};
