<?php
/**
 * This Class inherits from the PHPMailer class
 *
 * @package Knowledgeroot
 * @author Frank Habermann
 * @version $Id: class-email-notification.php 1200 2011-12-28 20:56:58Z lordlamer $
 */
class knowledgeroot_notification {
	var $CLASS = array();
	var $config = null;

	/**
	 * start mailnotification
	 */
	function knowledgeroot_notification(&$CLASS) {
		$this->CLASS =& $CLASS;
		$this->config = $this->CLASS['config']->email;
	}

	/**
	 * send email notification
	 * id show the id of the modified element
	 * @param integer $pageid id of page
	 * @param string $elementType type of element
	 * @param string $action action
	 * @param string $name name of element
	 * @param integer $id id of element
	 * @param string $extratext some optional extra text
	 */
	function send_email_notification($pageid, $elementType, $action, $name = "", $id = "", $extratext = "", $action_str = "") {
		if($this->config->notification) {
			// get system locale
			$locale = $this->CLASS['config']->base->locale;

			// get pagetitle
			$pageTitle = $this->CLASS['path']->getTreePageTitle($pageid);

			// make text mail
			// get path
			$path = $this->CLASS['path']->getPath($pageid, $this->CLASS['kr_header']->get_base_url());

			// get action string
			if($action_str == "") {
				switch($elementType) {
					case "page":
						switch($action) {
							case "created":
								$action_str = sprintf($this->CLASS['translate']->_('Page "%s" was created',$locale), $name);
								break;

							case "edited":
								$action_str = sprintf($this->CLASS['translate']->_('Page "%s" was edited',$locale), $name);
								break;

							case "moved":
								$action_str = sprintf($this->CLASS['translate']->_('Page "%s" was moved',$locale), $name);
								break;

							case "deleted":
								$action_str = sprintf($this->CLASS['translate']->_('Page "%s" was deleted',$locale), $name);
								break;
						}
						break;

					case "content":
						switch($action) {
							case "created":
								$action_str = sprintf($this->CLASS['translate']->_('Content "%s" was created',$locale), $name);
								break;

							case "edited":
								$action_str = sprintf($this->CLASS['translate']->_('Content "%s" was edited',$locale), $name);
								break;

							case "moved":
								$action_str = sprintf($this->CLASS['translate']->_('Content "%s" was moved',$locale), $name);
								break;

							case "deleted":
								$action_str = sprintf($this->CLASS['translate']->_('Content "%s" was deleted',$locale), $name);
								break;
						}

						break;

					case "file":
						switch($action) {
							case "created":
								$action_str = sprintf($this->CLASS['translate']->_('File "%s" was created',$locale), $name);
								break;

							case "deleted":
								$action_str = sprintf($this->CLASS['translate']->_('File "%s" was deleted',$locale), $name);
								break;
						}

						break;
				}
			}

			// set emailsubject
			$subject = $this->CLASS['translate']->_('Knowledgeroot notification', $locale) . ' (' . $action_str . ')';

			$pageId = null;
			$linkToPage = null;

			if($id != "" && $elementType == "content") {
				$pageId = $this->CLASS['knowledgeroot']->getPageIdFromContentId($id);
				$linkToPage = $this->CLASS['config']->base->base_url . "index.php?id=" . $pageId . "&oc=".$id."#" . $id;
			}

			$mailBody = '
<html>
<head><meta http-equiv="content-type" content="text/html; " /><style type="text/css"><!--
#msg dl { border: 1px #006 solid; background: #577; padding: 6px; color: #fff; }
#msg dt { float: left; width: 6em; font-weight: bold; }
#msg dt:after { content:\':\';}
#msg dl, #msg dt, #msg ul, #msg li, #header, #footer { font-family: verdana,arial,helvetica,sans-serif; font-size: 10pt;  }
#msg dl a { font-weight: bold ; color:#fc3;}
#msg dl a:hover  { color:#ff0; }
	--></style>
	<title>' . $this->CLASS['translate']->_('Knowledgeroot notification', $locale) . '</title>
</head>
<body>
	<div id="msg">
	<dl>
	<dt>' . $this->CLASS['translate']->_('Path', $locale) . '</dt> <dd>' . $path . '</dd>
	<dt>' . $this->CLASS['translate']->_('Action', $locale) . '</dt> <dd> ' . $action_str . ($id != "" ? " (ID: " . $id . ")<br />\n" : "") . '</dd>
	<dt>' . $this->CLASS['translate']->_('author', $locale) . '</dt> <dd>' . $_SESSION['user'] . '</dd>
	<dt>' . $this->CLASS['translate']->_('date', $locale) . '</dt> <dd>' . date("D, j M Y H:i:s") . '</dd>
	'.(($id != "" && $elementType == "content") ? '<dt>' . $this->CLASS['translate']->_('link', $locale) . '</dt> <dd><a href="' . $linkToPage . '">' . $linkToPage . '</a></dd>' : '') .'
	</dl>
	</div>';

			if ($extratext != "") {
				$mailBody .= $extratext;
			}

			$mailBody .= '</div>
</body>
</html>';

			$bodyHtml = $mailBody;

			// html part for email
			// get path
			$path = $this->CLASS['path']->getTextPath($pageid, $this->CLASS['kr_header']->get_base_url());

			$mailBody = $this->CLASS['translate']->_('Path', $locale) . ': ' . $path."\n";
			$mailBody .= $this->CLASS['translate']->_('Action', $locale) . ': ' . $action_str . ($id != "" ? " (ID: " . $id . ")\n" : "\n");
			$mailBody .= $this->CLASS['translate']->_('Author', $locale). ': ' . $_SESSION['user']."\n";
			$mailBody .= $this->CLASS['translate']->_('Date', $locale) . ': ' . date("D, j M Y H:i:s")."\n";
			if($id != "" && $elementType == "content") $mailBody .= $this->CLASS['translate']->_('link', $locale) . ': ' . $linkToPage . "\n";

			if ($extratext != "") {
				$mailBody .= "\n" . $this->CLASS['translate']->_('Detail of Changes', $locale) . ": \n\n" . $extratext . "\n";
			}

			$bodyText = $mailBody;

			// send email
			if (!$this->sendEmail($this->config, $subject, $bodyText, $bodyHtml)) {
				$this->CLASS['kr_header']->addwarning($this->CLASS['translate']->_('Unable to send email notification'));
			}
		}
	}

	/**
	 * send email
	 *
	 * @param mixed $config - Zend_Config Object of email configuration
	 * @param string $subject - subject of email
	 * @param string $body - body of email
	 * @param return bool
	 */
	function sendEmail($config, $subject, $bodyText, $bodyHtml = null) {
		try {
			$transport = null;

			if($config->host != '') {
				$smtpConfig = array();
				if($config->auth != '') {
					$smtpConfig['auth'] = $config->auth;
					$smtpConfig['username'] = $config->username;
					$smtpConfig['password'] = $config->password;
				}

				if($config->port != '') {
					$smtpConfig['port'] = $config->port;
				}

				if($config->ssl != '') {
					$smtpConfig['ssl'] = $config->ssl;
				}

				$transport = new Zend_Mail_Transport_Smtp($config->host, $smtpConfig);
			}

			$mail = new Zend_Mail();

			$mail->addHeader('X-MailGenerator', 'Knowledgeroot');

			$mail->setBodyText($bodyText);
			if($bodyHtml != null) $mail->setBodyHtml($bodyHtml);
			$mail->setFrom($config->from, $config->from_name);

			// addTo
			foreach(explode(",", $config->to) as $value) {
				if(trim($value) != "") {
					$mail->addTo($value);
				}
			}

			$mail->setSubject($config->subject_prefix . $subject);

			$mail->send($transport);

			return true;
		} catch(Exception $e) {
			$this->CLASS['error']->log('Could not send email: ' . $e->getMessage());
			return false;
		}
	}
}
?>
