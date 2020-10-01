<?php
/**
 * \file
 * This file defines the TT class for rights maintenantce
 */

/**
 * \ingroup TT_TTADMIN
 * Rights maintenance class.
 * \brief Rights maintenance
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Nov 29, 2011 -- O van Eijk -- initial version
 */
class Rightsmaint extends _TT
{
	/**
	 * Array with the info for this rightbit as it appears in the database
	 */
	private $rights_data;

	/**
	 * Datahandler  for this object
	 */
	private $dataset;

	/**
	 * Object constructor
	 * \param[in] $aid Application ID this rightbit belongs to
	 * \param[in] $rid Rights ID for which the object must be created, of 0 for a new right
	 */
	public function __construct($aid = 0, $rid = 0)
	{
		parent::init(__FILE__, __LINE__);
		$this->dataset = new DataHandler();
		if (ConfigHandler::get ('database', 'tttables', true)) {
			$this->dataset->setPrefix(ConfigHandler::get ('database', 'ttprefix'));
		}
		$this->dataset->setTablename('rights');

		if ($rid == 0) {
			$this->rights_data = array();
		} else {
			$this->rights_data = $this->getData($aid, $rid);
		}
	}


	/**
	 * Read the data for this rightbit from the database and store it in an indexed array
	 * \param[in] $aid Application ID
	 * \param[in] Rights ID
	 * \return Array with all info
	 */
	private function getData ($aid, $rid)
	{
		$this->dataset->set('aid', $aid);
		$this->dataset->set('rid', $rid);
		$this->dataset->prepare();
		$this->dataset->db($_data, __LINE__, __FILE__);
		$_data = $_data[0]; // shift up
		$_data['aid'] = $aid;
		$_data['rid'] = $rid;
		$this->dataset->reset(DATA_RESET_DATA & DATA_RESET_META);
		return $_data;
	}

	/**
	 * Return a rights item, or the default value if it does not exist.
	 * \param[in] $item The item of which the value should be returned
	 * \param[in] $default Default value it the item does not exist (default is null)
	 * \return Value
	 */
	public function get($item, $default = null)
	{
		return (
			(array_key_exists($item, $this->rights_data))
				? $this->rights_data[$item]
				: $default
		);
	}

	/**
	 * Add or edit a right. The data is taken from the form
	 */
	public function editRight ()
	{
		$_form = TT::factory('FormHandler');
		$_new = (($_rid = $_form->get('rid')) == 0);
		$this->dataset->set ('aid', $_form->get('aid'));
		if ($_new) {
			$this->dataset->set (
				 'rid'
				,null
				,null
				,array(
					 'function' => array('max')
					,'name' => array('rid')
				)
				,array('match' => array(DBMATCH_NONE))
			);
			$this->dataset->prepare();
			$this->dataset->db($_high, __LINE__, __FILE__);
			$this->dataset->reset(DATA_RESET_PREPARE | DATA_RESET_DATA);
			$this->dataset->set ('aid', $_form->get('aid'));
			$this->dataset->set('rid', $_high[0]['rid'] + 1);
		}
		$this->dataset->set ('name', $_form->get('rgt'));
		$this->dataset->set ('description', $_form->get('descr'));
		if ($_new) {
			$this->dataset->prepare(DATA_WRITE);
		} else {
			$this->dataset->set('rid', $_rid);
			$this->dataset->setKey('rid');
			$this->dataset->setKey('aid');
			$this->dataset->prepare(DATA_UPDATE);
		}
		$this->dataset->db($_dummy, __LINE__, __FILE__);
	}
}
