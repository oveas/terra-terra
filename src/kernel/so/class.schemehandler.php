<?php
/**
 * \file
 * This file defines the Scheme Handler class
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: class.schemehandler.php,v 1.6 2011-05-02 12:56:14 oscar Exp $
 */

/**
 * \ingroup OWL_SO_LAYER
 * Handler for all database schemes. This singleton class handles all updates to db tables
 * \brief Scheme handler 
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Oct 7, 2010 -- O van Eijk -- initial version for OWL
 * \note A port of this class has been created for VirtueMart
 * \todo This handler currently doesn't use the DbDriver driver class
 */
class SchemeHandler extends _OWL
{
	/**
	 * integer - Scheme Handle ID
	 */
	private $id;

	/**
	 * integer - Reference to the database class
	 */
	private $db;

	/**
	 * Array - table description
	 */
	private $scheme;

	/**
	 * Array - table name
	 */
	private $table = '';

	/**
	 * integer - self reference
	 */
	private static $instance;

	/**
	 * boolean - True when a scheme is filled with data and not yet used
	 */
	private $inuse = false;

	/**
	 * Class constructor
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function __construct ()
	{
		_OWL::init();
		$this->db = DbHandler::getInstance();
		$this->setStatus (OWL_STATUS_OK);
	}

	/**
	 * Class destructor
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function __destruct ()
	{
		if (parent::__destruct() === false) {
			return;
		}
	}

	/**
	 * Implementation of the __clone() function to prevent cloning of this singleton;
	 * it triggers a fatal (user)error
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function __clone ()
	{
		trigger_error('invalid object cloning');
	}

	/**
	 * Return a reference to my implementation. If necessary, create that implementation first.
	 * \return Severity level
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public static function getInstance()
	{
		if (!SchemeHandler::$instance instanceof self) {
			SchemeHandler::$instance = new self();
		}
		return SchemeHandler::$instance;
	}

	/**
	 * Set a new tablename
	 * \param[in] $_tblname Name of the table to create, check or modify
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function createScheme ($_tblname)
	{
		if ($this->inuse) {
			$this->setStatus (SCHEMEHANDLE_INUSE, $this->table);
			return ($this->severity);
		}
		self::reset();
		$this->table = $_tblname;
		$this->inuse = true;
	}

	/**
	 * Define the layout for a table
	 * \param[in] $_scheme Array holding the table description. This is a 2 dimensional array where the
	 * first level holds the fieldnames. The second array defines the attributes for each field:
	 * - type : String; the field-type (INT|TINYINT|VARCHAR|MEDIUMTEXT|TEXT|LONGTEXT|BLOB|LONGBLOB|ENUM|SET)
	 * - length : Integer; indicating the length for fieldtypes that use that (like INT and VARCHAR)
	 * - null : Boolean; when true the value can be NULL
	 * - auto-inc : Boolean; True for auto-increment values (will be set as primary key)
	 * - default : Mixed; default value
	 * - options : Array; for SET and ENUM types. the list of possible values
	 * - comment : String; field comment
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function defineScheme($_scheme)
	{
		if (!$this->inuse) {
			$this->setStatus (SCHEMEHANDLE_NOTUSE);
			return ($this->severity);
		}
		$this->scheme['columns'] = $_scheme;
		return $this->validateScheme();
	}

	/**
	 * Define the indexes for a table
	 * \param[in] $_index Array holding the index description. This is a 2 dimensional array where the
	 * first level holds the indexname. The second array defines the attributes for each index:
	 * - unique : Boolean; True for unique keys
	 * - primary : Boolean; True for the primary key
	 * - columns : Array; List with columnnames that will be indexed
	 * - type : String (optional); Index type, currenty only supports 'FULLTEXT'
	 * \return Severity level
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function defineIndex($_index)
	{
		if (!$this->inuse) {
			$this->setStatus (SCHEMEHANDLE_NOTUSE);
			return ($this->severity);
		}
		$_primary = false;
		foreach ($_index as $_name => $_descr) {
			if ($_descr['primary']) {
				if ($_primary) {
					$this->setStatus (SCHEMEHANDLE_DUPLPRKEY, $this->table);
					return false;
				}
				$_name = 'PRIMARY';
				$_primary = true;
			}
			unset ($_descr['primary']);
			$this->scheme['indexes'][$_name] = $_descr;
		}
		return $this->validateScheme();
	}

	/**
	 * If the table does not exist, or differs from the defined scheme, create of modify the table
	 * \param[in] $_drops True if existing fields should be dropped; default false.
	 * If existing fields should be converted to new fields, call with DbScheme::scheme(false) first,
	 * then do the conversions, next call DbScheme::scheme(true).
	 * \return Severity level
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	function scheme($_drops = false)
	{
		if (!$this->inuse) {
			$this->setStatus (SCHEMEHANDLE_NOTUSE);
			return ($this->severity);
		}
		$_return = $this->compare();
		if ($_return === true) {
			return true; // table exists and is equal
		} elseif ($_return === false) {
			$_stat = $this->createTable(); // table does not exist
		} else {
			$_stat = $this->alterTable($_return, $_drops); // differences found
		}
		return ($_stat === OWL_SUCCESS);
	}

	/**
	 * Modify 1 or more fields in an existing scheme definition
	 * \param[in] $_field Array holding 1 or more field descriptions
	 * \see defineScheme()
	 * \return Boolean; false if the table description contains errors
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function alterScheme($_field)
	{
		if (!$this->inuse) {
			$this->setStatus (SCHEMEHANDLE_NOTUSE);
			return ($this->severity);
		}
		foreach ($_field as $_fieldname => $_attributes) {
			$this->scheme['columns'][$_fieldname][$_attributes[0]] = $_attributes[1];
		}
		return $this->validateScheme();
	}

	/**
	 * Validate the defined scheme. Some values will be modified to make sure the SQL
	 * statements can be prepared and compare() won't find differences on case diffs
	 * \return boolean False if there is an error in the scheme definition, True if no errors were found
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function validateScheme()
	{
		$_counters = array(
			 'auto_inc' => 0
		);
		if (!array_key_exists('columns', $this->scheme) || count($this->scheme['columns']) == 0) {
			$this->setStatus (SCHEMEHANDLE_NOCOLS, $this->table);
			return false;
		}
		foreach ($this->scheme['columns'] as $_fld => $_desc) {
			$this->scheme['columns'][$_fld]['type'] = strtolower($_desc['type']);
			if (array_key_exists('auto_inc',$_desc) && $_desc['auto_inc'] == true) {
				$this->scheme['indexes']['PRIMARY'] = array(
							 'columns' => array ($_fld)
							,'primary' => true
							,'unique' => true
							,'type' => null
				);
				if ($_counters['auto_inc'] > 0) {
				$this->setStatus (SCHEMEHANDLE_MULAUTOINC, $this->table);
					return false;
				}
				$_counters['auto_inc']++;
			}
			if (array_key_exists('length',$_desc) && $_desc['length'] == 0) {
				unset ($this->scheme['columns'][$_fld]['length']);
			}
			if (array_key_exists('options',$_desc)) {
				for ($_idx = 0; $_idx < count($_desc['options']); $_idx++) {
					if (preg_match("/^'.*'$/", $_desc['options'][$_idx]) == 0) {
						$this->scheme['columns'][$_fld]['options'][$_idx] = "'" . $_desc['options'][$_idx] . "'";
					}
				}
			}
			
		}
		if (!array_key_exists('indexes', $this->scheme) || count($this->scheme['indexes']) == 0) {
			$this->setStatus (SCHEMEHANDLE_NOINDEX, $this->table);
			return true;
		}
		foreach ($this->scheme['indexes'] as $_idx => $_desc) {
			if (!array_key_exists('columns', $_desc)
				|| !is_array($_desc['columns'])
				|| count($_desc['columns']) == 0) {
					$this->setStatus (SCHEMEHANDLE_NOCOLIDX, $this->table, $_idx);
					return false;
			}
			foreach ($_desc['columns'] as $_fld) {
				if (!array_key_exists($_fld, $this->scheme['columns'])) {
					$this->setStatus (SCHEMEHANDLE_IVCOLIDX, $this->table, $_idx, $_fld);
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * Compare the scheme with an existing database table
	 * \return mixed True if there are no differences, False if the table does not exist, or an
	 * array with differences
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function compare ()
	{
		$_diffs = array();
		$_current = array();
		$this->tableDescription($this->table, $_current);
		if ($this->getStatus() === SCHEMEHANDLE_NOTABLE) {
			return false;
		}
		foreach ($this->scheme['columns'] as $_fld => $_descr) {
			if (!array_key_exists($_fld, $_current['columns'])) {
				$_diffs['add']['columns'][$_fld] = $_descr;
			} else {
				foreach ($_descr as $_item => $_value) {
					if (!array_key_exists($_item, $_current['columns'][$_fld])
							|| ($_value != $_current['columns'][$_fld][$_item])) {
						$_diffs['mod']['columns'][$_fld] = $_descr;
					}
				}
			}
		}
		foreach ($_current['columns'] as $_fld => $_descr) {
			if (!array_key_exists($_fld, $this->scheme['columns'])) {
				$_diffs['drop']['columns'][$_fld] = $_descr;
			}
		}
		if (count($_diffs) == 0) {
			return true;
		}
		return $_diffs;
	}

	/**
	 * Create the defined table
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function createTable()
	{
		$_qry = 'CREATE TABLE ' . $this->db->tablename($this->table) . '(';
		$_first = true;
		foreach ($this->scheme['columns'] as $_fld => $_desc) {
			if ($_first) {
				$_first = false;
			} else {
				$_qry .= ',';
			}
			$_qry .= ('`' . $_fld . '` ' . $_desc['type']
				. $this->_define_field($_desc));
		}
		foreach ($this->scheme['indexes'] as $_idx => $_desc) {
			if ($_idx == 'PRIMARY') {
				$_qry .= ',PRIMARY KEY ';
			} else {
				if ($_desc['unique']) {
					$_qry .= ',UNIQUE KEY ';
				} elseif ($_desc['type'] == 'FULLTEXT') {
					$_qry .= ',FULLTEXT KEY ';
				} else {
					$_qry .= ',KEY ';
				}
				$_qry .= "`$_idx` ";
			}
			$_qry .= ('(' . implode(',',$_desc['columns']) . ')');
		}
		$_qry .= ')';
		$this->db->setQuery($_qry);
		return ($this->db->write($_dummy, __LINE__, __FILE__));
	}

	/**
	 * Make changes to the table
	 * \param[in] $_diffs Changes to make
	 * \param[in] $_drops True if existing fields should be dropped
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function alterTable($_diffs, $_drops)
	{
		if ($_drops === true && array_key_exists('drop', $_diffs) && count($_diffs['drop']['columns']) > 0) {
			foreach ($_diffs['drop']['columns'] as $_fld => $_desc) {
				$this->db->setQuery('ALTER TABLE ' . $this->db->tablename($this->table) . ' DROP ' . $_fld);
				$this->db->write(false, __LINE__, __FILE__);
			}
		}
		if (array_key_exists('mod', $_diffs) && count($_diffs['mod']['columns']) > 0) {
			foreach ($_diffs['mod']['columns'] as $_fld => $_desc) {
				$_qry = 'ALTER TABLE ' . $this->db->tablename($this->table)
					. ' CHANGE `' . $_fld . '` `' .$_fld . '` ' . $_desc['type']
					. $this->_define_field($_desc);
				$this->db->setQuery($_qry);
				$this->db->write(false, __LINE__, __FILE__);
			}
		}
		if (array_key_exists('add', $_diffs) && count($_diffs['add']['columns']) > 0) {
			foreach ($_diffs['add']['columns'] as $_fld => $_desc) {
				$_qry = 'ALTER TABLE ' . $this->db->tablename($this->table)
					. ' ADD `' . $_fld . '` ' . $_desc['type']
					. $this->_define_field($_desc);
				$this->db->setQuery($_qry);
				$this->db->write(false, __LINE__, __FILE__);
			}
		}
		return OWL_SUCCESS; // TODO proper checking
	}

	/**
	 * Create the SQL code for a field definition
	 * \param[in] $_desc Indexed array with the field properties from scheme definition
	 * \return string SQL code
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function _define_field($_desc)
	{
		$_qry = '';
		if (array_key_exists('length', $_desc) && $_desc['length'] > 0) {
			$_qry .= ('(' . $_desc['length'] . ')');
		}
		if (array_key_exists('options', $_desc)) {
			$_qry .= ('(' . implode(',',$_desc['options']) . ')');
		}
		if (array_key_exists('unsigned', $_desc) && $_desc['unsigned']) {
			$_qry .= ' UNSIGNED';
		}
		if (array_key_exists('zerofill', $_desc) && $_desc['zerofill']) {
			$_qry .= ' ZEROFILL';
		}
		if (!array_key_exists('null', $_desc) || !$_desc['null']) {
			$_qry .= ' NOT NULL';
		}
		if (array_key_exists('auto_inc', $_desc) && $_desc['auto_inc']) {
			$_qry .= ' AUTO_INCREMENT';
		}
		if (array_key_exists('default', $_desc) && !empty($_desc['default'])) {
			$_qry .= (' DEFAULT \'' . $_desc['default'] . "'");
		}
		if (array_key_exists('comment', $_desc) && !empty($_desc['comment'])) {
			$_qry .= (' COMMENT \'' . $_desc['comment'] . "'");
		}
		return $_qry;
	}

	/**
	 * Get the columns for a given table
	 * \param[in] $_tablename The tablename
	 * \return Indexed array holding all fields =&gt; datatypes, or null on errors
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function getTableColumns($_tablename)
	{
		$_descr = array ();
		$_data  = array ();
		$_qry = 'SHOW FULL COLUMNS FROM ' . $this->db->tablename($_tablename);

		$this->db->read (DBHANDLE_DATA, $_data, $_qry, __LINE__, __FILE__);
		if ($this->db->getStatus() === 'DBHANDLE_NODATA') {
			// A table without fields.... not likely.... some bug somewhere
			$this->setStatus (SCHEMEHANDLE_EMPTYTABLE, $_tablename);
			return null;
		}
		foreach ($_data as $_record) {
			if (preg_match("/(.+)\((\d+,?\d*)\)\s?(unsigned)?\s?(zerofill)?/i", $_record['Type'], $_matches)) {
				$_descr[$_record['Field']]['type'] = $_matches[1];
				$_descr[$_record['Field']]['length'] = $_matches[2];
				$_descr[$_record['Field']]['unsigned'] = (@$_matches[3] == 'unsigned');
				$_descr[$_record['Field']]['zerofill'] = (@$_matches[4] == 'zerofill');
			} else {
				$_descr[$_record['Field']]['type'] = $_record['Type'];
			}
			$_descr[$_record['Field']]['null']     = ($_record['Null'] == 'YES');
			$_descr[$_record['Field']]['auto_inc'] = (preg_match("/auto_inc/i", $_record['Extra']));

			if (preg_match("/(enum|set)\((.+),?\)/i", $_record['Type'], $_matches)) {
				// Value list for ENUM and SET type
				$_descr[$_record['Field']]['type'] = $_matches[1];
				$_descr[$_record['Field']]['options']  = explode(',', $_matches[2]);
			}

			$_descr[$_record['Field']]['default'] = ($_record['Default'] == 'NULL') ? '' : $_record['Default'];
			$_descr[$_record['Field']]['comment'] = $_record['Comment'];
//			$_descr[$_record['Field']]['index'] = substr(0, 1, $_record['Key']); // P[RI], U[NI] or M[UL]
		}
		return $_descr;
	}

	/**
	 * Get the indexes for a given table
	 * \param[in] $_tablename The tablename
	 * \return Indexed array holding all fields =&gt; datatypes, or null on errors
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function getTableIndexes($_tablename)
	{
		$_descr = array ();
		$_data  = array ();
		$_qry = 'SHOW INDEXES FROM ' . $this->db->tablename($_tablename);

		$this->db->read (DBHANDLE_DATA, $_data, $_qry, __LINE__, __FILE__);
		if ($this->db->getStatus() === 'DBHANDLE_NODATA') {
			$this->setStatus (SCHEMEHANDLE_NOINDEX, $_tablename);
			return null;
		}
		foreach ($_data as $_record) {
			$_index[$_record['Key_name']]['columns'][$_record['Seq_in_index']] = $_record['Column_name'];
			$_index[$_record['Key_name']]['unique'] = (!$_record['Non_unique']);
			$_index[$_record['Key_name']]['type'] = $_record['Index_type'];
			$_index[$_record['Key_name']]['comment'] = $_record['Comment'];
		}
		return $_index;
	}

	/**
	 * Get a description of a database table
	 * \param[in] $tablename The tablename
	 * \param[out] $data Indexed array holding all fields =&gt; datatypes
	 * \return Severity level
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function tableDescription ($tablename, &$data)
	{
		if (!$this->db->tableExists($tablename)) {
			$data = array();
			$this->setStatus (SCHEMEHANDLE_NOTABLE);
			return ($this->severity);
		}
		$data['columns'] = $this->getTableColumns($tablename);
		$data['indexes'] = $this->getTableIndexes($tablename);
		return ($this->severity);
	}
	
	/**
	 * Reset the internal data structure
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function reset()
	{
		$this->scheme = array();
		$this->table = '';
		$this->inuse = false;
		parent::reset();
	}
}
/**
 * \example exa.schemehandler.php
 * This example shows how to create or alter a table using the SchemeHandler class
 * \author Oscar van Eijk, Oveas Functionality Provider
 */

/*
 * Register this class and all status codes
 */
Register::registerClass ('SchemeHandler');

//Register::setSeverity (OWL_DEBUG);

Register::setSeverity (OWL_INFO);
Register::registerCode ('SCHEMEHANDLE_NOTABLE');
Register::registerCode ('SCHEMEHANDLE_NOINDEX');

//Register::setSeverity (OWL_OK);
//Register::setSeverity (OWL_SUCCESS);

Register::setSeverity (OWL_WARNING);
Register::registerCode ('SCHEMEHANDLE_IVTABLE');
			
Register::setSeverity (OWL_BUG);
Register::registerCode ('SCHEMEHANDLE_INUSE');
Register::registerCode ('SCHEMEHANDLE_NOUSE');
Register::registerCode ('SCHEMEHANDLE_EMPTYTABLE');
					
Register::setSeverity (OWL_ERROR);
Register::registerCode ('SCHEMEHANDLE_DUPLPRKEY');
Register::registerCode ('SCHEMEHANDLE_MULAUTOINC');
Register::registerCode ('SCHEMEHANDLE_NOCOLIDX');
Register::registerCode ('SCHEMEHANDLE_IVCOLIDX');
Register::registerCode ('SCHEMEHANDLE_NOCOLS');

//Register::setSeverity (OWL_FATAL);
//Register::setSeverity (OWL_CRITICAL);
