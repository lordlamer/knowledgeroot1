FCKConfig.PluginsPath = FCKConfig.BasePath + '../../plugins/' ;
FCKConfig.Plugins.Add( 'krootlink', 'en,de,fr,ja,nl,pt-br' ) ;
FCKConfig.ToolbarSets["Default"] = [ ['Source','DocProps','-','Save','NewPage','Preview','-','Templates'], ['Cut','Copy','Paste','PasteText','PasteWord','-','Print','SpellCheck'], ['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'], ['Form','Checkbox','Radio','TextField','Textarea','Select','Button','ImageButton','HiddenField'], '/', ['Bold','Italic','Underline','StrikeThrough','-','Subscript','Superscript'], ['OrderedList','UnorderedList','-','Outdent','Indent','Blockquote'], ['JustifyLeft','JustifyCenter','JustifyRight','JustifyFull'], ['Link','Unlink','krootlink','Anchor'], ['Image','Flash','Table','Rule','Smiley','SpecialChar','PageBreak'], '/', ['Style','FontFormat','FontName','FontSize'], ['TextColor','BGColor'], ['FitWindow','ShowBlocks','-','About'] // No comma for the last row.
] ; 