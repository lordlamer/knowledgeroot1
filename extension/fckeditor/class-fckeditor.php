<?php
/**
 * FCKEditor Class
 * This class will include the fckeditor to knowledgeroot
 * @author Frank Habermann
 * @package Knowledgeroot
 * @version $Id:
 */
class fckeditor extends rte {
	var $InstanceName = "content";
	var $BasePath = "";
	var $Width = "600";
	var $Height = "450";
	var $ToolbarSet = "Default";
	var $Value = "";
	var $Config = array();
	var $Skin = "default";
	var $editor = null;

	function main() {
		// use this class for rte
		$this->CLASS['rte'] =& $this;

		$this->editor = new FCKEditor($this->InstanceName);

		$sBasePath = $_SERVER['PHP_SELF'] ;
		$sBasePath = substr( $sBasePath, 0, strpos( $sBasePath, "index.php" ) );
		if($sBasePath == "" || $sBasePath == "/") {
			$sBasePath = "./extension/fckeditor/fckeditor/";
		} else {
			$sBasePath = $sBasePath . "/extension/fckeditor/fckeditor/";
		}

		// set default config
		$this->Config['SkinPath'] = "skins/" . $this->Skin . "/";
		$this->Config['CustomConfigurationsPath'] = substr( $_SERVER['PHP_SELF'], 0, strpos( $_SERVER['PHP_SELF'], "index.php" ) )."extension/fckeditor/fckconfig.js";
		$this->Config['AutoDetectLanguage'] = true;
		$this->Config['DefaultLanguage'] = 'en';
		$this->ToolbarSet = 'Default';

		$this->BasePath = $sBasePath . "editor/";
	}

	function show($text="") {
		$this->Value = $text;
		return $this->CreateHtml();
	}

	function CreateHtml()
	{
		$HtmlValue = htmlspecialchars( $this->Value ) ;

		$Html = '' ;

		if ( !isset( $_GET ) ) {
			global $HTTP_GET_VARS ;
			$_GET = $HTTP_GET_VARS ;
		}

		if ( $this->IsCompatible() )
		{
			if ( isset( $_GET['fcksource'] ) && $_GET['fcksource'] == "true" )
				$File = 'fckeditor.original.html' ;
			else
				$File = 'fckeditor.html' ;

			$Link = "{$this->BasePath}{$File}?InstanceName={$this->InstanceName}" ;

			if ( $this->ToolbarSet != '' )
				$Link .= "&amp;Toolbar={$this->ToolbarSet}" ;

			// Render the linked hidden field.
			$Html .= "<input type=\"hidden\" id=\"{$this->InstanceName}\" name=\"{$this->InstanceName}\" value=\"{$HtmlValue}\" style=\"display:none\" />" ;

			// Render the configurations hidden field.
			$Html .= "<input type=\"hidden\" id=\"{$this->InstanceName}___Config\" value=\"" . $this->GetConfigFieldString() . "\" style=\"display:none\" />" ;

			// Render the editor IFRAME.
			$Html .= "<iframe id=\"{$this->InstanceName}___Frame\" src=\"{$Link}\" width=\"{$this->Width}\" height=\"{$this->Height}\" frameborder=\"0\" scrolling=\"no\"></iframe>" ;
		}
		else
		{
			if ( strpos( $this->Width, '%' ) === false )
				$WidthCSS = $this->Width . 'px' ;
			else
				$WidthCSS = $this->Width ;

			if ( strpos( $this->Height, '%' ) === false )
				$HeightCSS = $this->Height . 'px' ;
			else
				$HeightCSS = $this->Height ;

			$Html .= "<textarea name=\"{$this->InstanceName}\" rows=\"4\" cols=\"40\" style=\"width: {$WidthCSS}; height: {$HeightCSS}\">{$HtmlValue}</textarea>" ;
		}

		return $Html ;
	}

	function IsCompatible()
	{
		if ( isset( $_SERVER ) ) {
			$sAgent = $_SERVER['HTTP_USER_AGENT'] ;
		}
		else {
			global $HTTP_SERVER_VARS ;
			if ( isset( $HTTP_SERVER_VARS ) ) {
				$sAgent = $HTTP_SERVER_VARS['HTTP_USER_AGENT'] ;
			}
			else {
				global $HTTP_USER_AGENT ;
				$sAgent = $HTTP_USER_AGENT ;
			}
		}

		if ( strpos($sAgent, 'MSIE') !== false && strpos($sAgent, 'mac') === false && strpos($sAgent, 'Opera') === false )
		{
			$iVersion = (float)substr($sAgent, strpos($sAgent, 'MSIE') + 5, 3) ;
			return ($iVersion >= 5.5) ;
		}
		else if ( strpos($sAgent, 'Gecko/') !== false )
		{
			$iVersion = (int)substr($sAgent, strpos($sAgent, 'Gecko/') + 6, 8) ;
			return ($iVersion >= 20030210) ;
		}
		else if ( strpos($sAgent, 'Opera/') !== false )
		{
			$fVersion = (float)substr($sAgent, strpos($sAgent, 'Opera/') + 6, 4) ;
			return ($fVersion >= 9.5) ;
		}
		else if ( preg_match( "|AppleWebKit/(\d+)|i", $sAgent, $matches ) )
		{
			$iVersion = $matches[1] ;
			return ( $matches[1] >= 522 ) ;
		}
		else
			return false ;
	}

	function GetConfigFieldString()
	{
		$sParams = '' ;
		$bFirst = true ;

		foreach ( $this->Config as $sKey => $sValue )
		{
			if ( $bFirst == false )
				$sParams .= '&amp;' ;
			else
				$bFirst = false ;

			if ( $sValue === true )
				$sParams .= $this->EncodeConfig( $sKey ) . '=true' ;
			else if ( $sValue === false )
				$sParams .= $this->EncodeConfig( $sKey ) . '=false' ;
			else
				$sParams .= $this->EncodeConfig( $sKey ) . '=' . $this->EncodeConfig( $sValue ) ;
		}

		return $sParams ;
	}

	function EncodeConfig( $valueToEncode )
	{
		$chars = array(
			'&' => '%26',
			'=' => '%3D',
			'"' => '%22' ) ;

		return strtr( $valueToEncode,  $chars ) ;
	}
}

?>
