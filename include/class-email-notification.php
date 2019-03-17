<?php
/**
 * class to send emails for knowledgeroot
 *
 * @package Knowledgeroot
 * @author Frank Habermann
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
     *
     * @param integer $pageid id of page
     * @param string $elementType type of element
     * @param string $action action
     * @param string $name name of element
     * @param string $id id of element
     * @param string $extratext some optional extra text
     * @param string $action_str self defined action string
     */
	function send_email_notification($pageid, $elementType, $action, $name = "", $id = "", $extratext = "", $action_str = "") {
	    // check if notification is enabled
		if($this->config->notification) {
			// get system locale
			$locale = $this->CLASS['config']->base->locale;

			// get email action only if it was not given
            if($action_str == "") {
                $action_str = $this->getEmailActionText($elementType, $action, $locale, $name);
            }

			// set emailsubject
			$subject = $this->CLASS['translate']->_('Knowledgeroot notification', $locale) . ' (' . $action_str . ')';

			$pageId = null;
			$linkToPage = null;

			if($id != "" && $elementType == "content") {
				$pageId = $this->CLASS['knowledgeroot']->getPageIdFromContentId($id);
				$linkToPage = $this->CLASS['config']->base->base_url . "index.php?id=" . $pageId . "&oc=".$id."#" . $id;
			}

            // get path as html
            $pathHtml = $this->CLASS['path']->getEmailPath($pageid, $this->CLASS['kr_header']->get_base_url());

            // get html mail body
			$bodyHtml = $this->getMailBodyHtml($id, $action_str, $pathHtml, $linkToPage, $elementType, $locale, $extratext);

			// get path as text
			$pathText = $this->CLASS['path']->getTextPath($pageid, $this->CLASS['kr_header']->get_base_url());

			// get body as text for email
			$bodyText = $this->getMailBodyText($id, $action_str, $pathText, $linkToPage, $elementType, $locale, $extratext);

			// send email
			if (!$this->sendEmail($this->config, $subject, $bodyText, $bodyHtml)) {
				$this->CLASS['kr_header']->addwarning($this->CLASS['translate']->_('Unable to send email notification'));
			}
		}
	}

    /**
     * get action text for email
     *
     * @param $elementType
     * @param $action
     * @param $locale
     * @param $name
     * @return string
     */
	private function getEmailActionText($elementType, $action, $locale, $name) {
        // get action string
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

        return $action_str;
    }

    /**
     * get mail body as text
     *
     * @param $id
     * @param $action_str
     * @param $path
     * @param $linkToPage
     * @param $elementType
     * @param $locale
     * @param $extratext
     * @return string
     */
	function getMailBodyText($id, $action_str, $path, $linkToPage, $elementType, $locale, $extratext) {
        $mailBody = $this->CLASS['translate']->_('Path', $locale) . ': ' . $path."\n";
        $mailBody .= $this->CLASS['translate']->_('Action', $locale) . ': ' . $action_str . ($id != "" ? " (ID: " . $id . ")\n" : "\n");
        $mailBody .= $this->CLASS['translate']->_('Author', $locale). ': ' . $_SESSION['user']."\n";
        $mailBody .= $this->CLASS['translate']->_('Date', $locale) . ': ' . date("D, j M Y H:i:s")."\n";
        if($id != "" && $elementType == "content") $mailBody .= $this->CLASS['translate']->_('link', $locale) . ': ' . $linkToPage . "\n";

        if ($extratext != "") {
            $mailBody .= "\n" . $this->CLASS['translate']->_('Detail of Changes', $locale) . ": \n\n" . $extratext . "\n";
        }

        return $mailBody;
    }

    /**
     * get mail body as html
     *
     * @param $id
     * @param $action_str
     * @param $path
     * @param $linkToPage
     * @param $elementType
     * @param $locale
     * @param $extratext
     * @return string
     */
    function getMailBodyHtml($id, $action_str, $path, $linkToPage, $elementType, $locale, $extratext) {
        $mailBody = '
<!doctype html>
<html>
<head><meta http-equiv="content-type" content="text/html; " /><style type="text/css"><!--
#msg dl { border: 1px #006 solid; background: #577; padding: 6px; color: #fff; }
#msg dt { float: left; width: 6em; font-weight: bold; }
#msg dt:after { content:\':\';}
#msg dl, #msg dt, #msg ul, #msg li, #header, #footer { font-family: verdana,arial,helvetica,sans-serif; font-size: 10pt;  }
#msg dl a { font-weight: bold ; color:#fc3;}
#msg dl a:hover  { color:#ff0; }
	--></style>
	<!-- <link rel="stylesheet" href="https://github.com/lordlamer/knowledgeroot1/blob/1.1dev/system/themes/green/green.css" crossorigin="anonymous"> -->
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

        return $mailBody;
    }

    /**
     * send email
     *
     * @param mixed $config - Zend_Config Object of email configuration
     * @param string $subject - subject of email
     * @param string $body - body of email
     * @param return bool
     * @return bool
     */
	function sendEmail($config, $subject, $bodyText, $bodyHtml = null) {
		try {
		    // get mail transport
            $transport = $this->getMailTransport($config);

			$mail = new Zend_Mail();

			// set mail generator to knowledgeroot :D
			$mail->addHeader('X-MailGenerator', 'Knowledgeroot');

			// set subject
            $mail->setSubject($config->subject_prefix . $subject);

            // set from
            $mail->setFrom($config->from, $config->from_name);

            // addTo
            foreach(explode(",", $config->to) as $value) {
                if(trim($value) != "") {
                    $mail->addTo($value);
                }
            }

			// set body parts
			$mail->setBodyText($bodyText);
			if($bodyHtml != null) $mail->setBodyHtml($bodyHtml);

			// send email
			$mail->send($transport);

			return true;
		} catch(Exception $e) {
			$this->CLASS['error']->log('Could not send email: ' . $e->getMessage());
			return false;
		}
	}

    /**
     * get email transport object
     *
     * @param $config
     * @return Zend_Mail_Transport_Smtp|null
     */
	function getMailTransport($config) {
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

        return $transport;
    }
}
