/*
 * FCKeditor - The text editor for internet
 * Copyright (C) 2003-2006 Frederico Caldeira Knabben
 *
 * Licensed under the terms of the GNU Lesser General Public License:
 * 		http://www.opensource.org/licenses/lgpl-license.php
 *
 * File Name: fckplugin.js
 * 	Plugin to insert "Knowledgeroot Link" in the editor.
 *
 * File Authors:
 * 		Michael Fields (mfields@slac.com)
 */

// Register the related command.
// RegisterCommand takes the following arguments: CommandName, DialogCommand
// FCKDialogCommand takes the following arguments: CommandName, Dialog Title, Path to HTML file, Width, Height
FCKCommands.RegisterCommand( 'krootlink', new FCKDialogCommand( 'krootlink', FCKLang.KrootLinkDlgTitle,
FCKPlugins.Items['krootlink'].Path + 'fck_krootlink.php', 380, 440 ) ) ;

// Create the toolbar button.
// FCKToolbarButton takes the following arguments: CommandName, Button Caption

var oKrootLinkItem = new FCKToolbarButton( 'krootlink', FCKLang.KrootLinkBtn ) ;
oKrootLinkItem.IconPath = FCKPlugins.Items['krootlink'].Path + 'krootlink.gif' ;
FCKToolbarItems.RegisterItem( 'krootlink', oKrootLinkItem ) ;

// The object used for all KrootLink operations.
var FCKKrootLink = new Object() ;

// Add a new KrootLink at the actual selection.
// This function will be called from the HTML file when the user clicks the OK button.
// This function receives the values from the Dialog

FCKKrootLink.Add = function( linkname, caption )
{
FCK.InsertHtml("<a href='"+linkname+"'>"+caption+"</a>") ;
}
