<?php
/**
 * \file
 * This file defines the Mail class
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: class.mail.php,v 1.3 2011-09-26 10:50:18 oscar Exp $
 */

/**
 * \ingroup OWL_BO_LAYER
 * Define an email for sending.
 * \brief Mail class
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version May 11, 2011 -- O van Eijk -- initial version
 */
class Mail extends _OWL
{
	/**
	 * Mail driver object
	 */
	private $driver;

	/**
	 * Array defining the mail. This is an indexed array; see MailDriver::mailSend() for the layout
	 */
	private $mail;

	/**
	 * Class constructor; loads the maildriver
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function __construct ()
	{
		_OWL::init();
		$_driver = ConfigHandler::get('mail', 'driver');
		if (OWLloader::getDriver($_driver, 'mail') === true) {
			$this->driver = new $_driver;
		} else {
			$this->setStatus(MAIL_NODRIVER, array($_driver));
		}
		$this->mail = array();
		// Identify the mailer
		$this->addHeaders(array('X-Mailer' => 'OWL-PHP - Oveas Web Library for PHP v' . OWL_VERSION));
	}

	/**
	 * Check and set the Sender address as it appears in the header. It no from address is
	 * set yet, this method will also set the same address for the from SMTP command by calling setFrom()
	 * \param[in] $addr A (displayable) email address
	 * \return Boolean; true on success. False indicates an invalid mail address
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setSender ($addr)
	{
		if (($_addr = verifyMailAddress($addr)) === '') {
			$this->setStatus(MAIL_IVMAILADDR, array($addr));
			return (false);
		} else {
			$this->mail['sender'] = $addr;
			$this->setFrom($_addr);
			return (true);
		}
	}

	/**
	 * Check and set the from address
	 * \param[in] $addr A (displayable) email address
	 * \return Boolean; true on success. False indicates an invalid mail address
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setFrom ($addr)
	{
		if (($_addr = verifyMailAddress($addr)) === '') {
			$this->setStatus(MAIL_IVMAILADDR, array($addr));
			return (false);
		} else {
			$this->mail['from'] = $_addr;
			return (true);
		}
	}

	/**
	 * Set the mail subject
	 * \param[in] $subject Subject line
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setSubject ($subject)
	{
		$this->mail['subject'] = $subject;
	}

	/**
	 * Set the maildate, if it should not be the actual timestamp
	 * \param[in] $date Date, or null to use the actual
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setDate ($date = null)
	{	// Format: Date: Fri, 20 May 2011 18:05:06 +0200
		// TODO write this function.... what checks do we need to make??
	}

	/**
	 * Set the body for the email
	 * \param[in] $text Mail text
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setBody ($text)
	{
		$this->mail['body'] = $text;
	}

	/**
	 * Add text to the mailbody
	 * \param[in] $text Mail text
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function addToBody ($text)
	{
		$this->mail['body'] .= $text;
	}

	/**
	 * Check the given mail address  and add it to the recipients and the To- list
	 * \param[in] $addr A (displayable) email address
	 * \return Boolean; true on success. False indicates an invalid mail address
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function addTo ($addr)
	{
		if (!array_key_exists('to', $this->mail)) {
			$this->mail['to'] = array();
		}
		if (($_addr = verifyMailAddress($addr)) === '') {
			$this->setStatus(MAIL_IVMAILADDR, array($addr));
			return (false);
		} else {
			$this->addRecipient($_addr);
			$this->mail['to'][] = $addr;
			return (true);
		}
	}

	/**
	 * Check the given mail address  and add it to the recipients and the CC- list
	 * \param[in] $addr A (displayable) email address
	 * \return Boolean; true on success. False indicates an invalid mail address
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function addCc ($addr)
	{
		if (!array_key_exists('cc', $this->mail)) {
			$this->mail['cc'] = array();
		}
		if (($_addr = verifyMailAddress($addr)) === '') {
			$this->setStatus(MAIL_IVMAILADDR, array($addr));
			return (false);
		} else {
			$this->addRecipient($_addr);
			$this->mail['cc'][] = $addr;
			return (true);
		}
	}

	/**
	 * Check the given mail address  and add it to the recipients list
	 * \param[in] $addr A (displayable) email address
	 * \return Boolean; true on success. False indicates an invalid mail address
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function addBcc ($addr)
	{
		if (($_addr = verifyMailAddress($addr)) === '') {
			$this->setStatus(MAIL_IVMAILADDR, array($addr));
			return (false);
		} else {
			$this->addRecipient($_addr);
			return (true);
		}
	}

	/**
	 * Add an email address to the list of recipients. This list is for use by the SMTP
	 * driver only and will not appear in the headers
	 * \param[in] $addr A validated email address
	 * \return Boolean; true on success. False indicates an invalid mail address
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function addRecipient ($addr)
	{
		if (!array_key_exists('recipients', $this->mail)) {
			$this->mail['recipients'] = array();
		}
		$this->mail['recipients'][] = $addr;
	}

	/**
	 * Add one or more headers to this mail. Headers that already exist will be overwritten.
	 * \param[in] $headers Indexed array with the header info in the format (header => value)
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function addHeaders (array $headers)
	{
		if (!array_key_exists('headers', $this->mail)) {
			$this->mail['headers'] = array();
		}
		foreach ($headers as $hdr => $val) {
			$this->mail['headers'][$hdr] = $val;
		}
	}

	/**
	 * Get previously set data from the email
	 * \param[in] $item One of the items as specified in MailDriver::mailSend()
	 * \return Item value, or null if it does not exist
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function get ($item)
	{
		if (!array_key_exists($item, $this->mail)) {
			return (null);
		} else {
			return ($this->mail[$item]);
		}
	}

	/**
	 * Call the maildriver's mailSend() method and check the result.
	 * \return Object severity
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function send()
	{
		if ($this->driver->mailSend($this->mail) === false) {
			$_err = $this->driver->getLastWarning();
			$this->setStatus(MAIL_SENDERR, array($this->mail['subject'], $_err));
		} else {
			$this->setStatus(MAIL_SEND, array($this->mail['subject'], implode(',', $this->mail['recipients'])));
		}
		return ($this->severity);
	}

	/**
	 * Create a displayable email address based on name and mail address info.
	 * Refer to MailDriver::mailSend() for an explanation what I mean with 'displayable'.
	 * \param[in] $address Email address
	 * \param[in] $name Indexed array with either 1 key ('name') holding the full name,
	 * or one or more seperate keys for first name ('fname'), middle name ('mname') and
	 * last name ('lname') that will be checked and combined
	 * \return Displayable mail address in the format "Personal name <email@example.org>"
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function makeDisplayableAddress ($address, array $name)
	{
		$_addr = '';
		if (array_key_exists('name', $name)) {
			return ($name['name'] . ' <' . $address . '>');
		} else {
			$_addr = '';
			if (array_key_exists('fname', $name) && $name['fname']) {
				$_addr .= $name['fname'] . ' ';
			}
			if (array_key_exists('mname', $name) && $name['mname']) {
				$_addr .= $name['mname'] . ' ';
			}
			if (array_key_exists('lname', $name) && $name['lname']) {
				$_addr .= $name['lname'] . ' ';
			}
			$_addr .= '<' . $address . '>';
			return ($_addr);
		}
	}
}

/*
 * Register this class and all status codes
 */
Register::registerClass ('Mail');

//Register::setSeverity (OWL_DEBUG);

//Register::setSeverity (OWL_INFO);
//Register::setSeverity (OWL_OK);
Register::setSeverity (OWL_SUCCESS);
Register::registerCode ('MAIL_SEND');

Register::setSeverity (OWL_WARNING);
Register::registerCode ('MAIL_IVMAILADDR');
Register::registerCode ('MAIL_SENDERR');

//Register::setSeverity (OWL_BUG);

Register::setSeverity (OWL_ERROR);
Register::registerCode ('MAIL_NODRIVER');

//Register::setSeverity (OWL_FATAL);
//Register::setSeverity (OWL_CRITICAL);
