<?php
/**
 * \file
 * This file defines the Scheme Handler class
 * \version $Id: class.schemehandler.php,v 1.4 2011-04-26 11:45:45 oscar Exp $
 */

/**
 * \ingroup OWL_SO_LAYER
 * Handler for all database schemes. This singleton class handles all updates to db tables
 * \brief Scheme handler 
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Oct 7, 2010 -- O van Eijk -- initial version for OWL
 * \todo This handler currently doesn't use the DbDriver driver class
 */
class SchemeHandler extends _OWL
{
	/**
	 * integer - Scheme Handle ID
	 * \private
	 */
	private $id;

	/**
	 * integer - Reference to the database class
	 * \private
	 */
	private $db;

	/**
	 * Array - table description
	 * \private
	 */
	private $scheme;

	/**
	 * Array - table name
	 * \private
	 */
	private $table = '';

	/**
	 * integer - self reference
	 * \private
	 * \static
	 */
	private static $instance;

	/**
	 * boolean - True when a scheme is filled with data and not yet used
	 * \private
	 */
	private $inuse = false;

	/**
	 * Class constructor
	 * \private
	 */
	private function __construct ()
	{
		_OWL::init();
		$this->db = DbHandler::get_instance();
		$this->set_status (OWL_STATUS_OK);
	}

	/**
	 * Class destructor
	 * \public
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
	 * \public
	 */
	public function __clone ()
	{
		trigger_error('invalid object cloning');
	}

	/**
	 * Return a reference to my implementation. If necessary, create that implementation first.
	 * \public
	 * \return Severity level
	 */
	public static function get_instance()
	{
		if (!SchemeHandler::$instance instanceof self) {
			SchemeHandler::$instance = new self();
		}
		return SchemeHandler::$instance;
	}

	/**
	 * Set a new tablename
	 * \param[in] $_tblname Name of the table to create, check or modify
	 */
	public function create_scheme ($_tblname)
	{
		if ($this->inuse) {
			$this->set_status (SCHEMEHANDLE_INUSE, $this->table);
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
	 */
	public function define_scheme($_scheme)
	{
		if (!$this->inuse) {
			$this->set_status (SCHEMEHANDLE_NOTUSE);
			return ($this->severity);
		}
		$this->scheme['columns'] = $_scheme;
		return $this->validate_scheme();
	}

	/**
	 * Define the indexes for a table
	 * \param[in] $_index Array holding the index description. This is a 2 dimensional array where the
	 * first level holds the indexname. The second array defines the attributes for each index:
	 * - unique : Boolean; True for unique keys
	 * - primary : Boolean; True for the primary key
	 * - columns : Array; List with columnnames that will be indexed
	 * - type : String (optional); Index type, currenty only supports 'FULLTEXT'
	 */
	public function define_index($_index)
	{
		if (!$this->inuse) {
			$this->set_status (SCHEMEHANDLE_NOTUSE);
			return ($this->severity);
		}
		$_primary = false;
		foreach ($_index as $_name => $_descr) {
			if ($_descr['primary']) {
				if ($_primary) {
					$this->set_status (SCHEMEHANDLE_DUPLPRKEY, $this->table);
					return false;
				}
				$_name = 'PRIMARY';
				$_primary = true;
			}
			unset ($_descr['primary']);
			$this->scheme['indexes'][$_name] = $_descr;
		}
		return $this->validate_scheme();
	}

	/**
	 * If the table does not exist, or differs from the defined scheme, create of modify the table
	 * \param[in] $_drops True if existing fields should be dropped; default false.
	 * If existing fields should be converted to new fields, call with DbScheme::scheme(false) first,
	 * then do the conversions, next call DbScheme::scheme(true).
	 * \return Severity level
	 */
	function scheme($_drops = false)
	{
		if (!$this->inuse) {
			$this->set_status (SCHEMEHANDLE_NOTUSE);
			return ($this->severity);
		}
		$_return = $this->compare();
		if ($_return === true) {
			return true; // table exists and is equal
		} elseif ($_return === false) {
			$_stat = $this->create_table(); // table does not exist
		} else {
			$_stat = $this->alter_table($_return, $_drops); // differences found
		}
		return ($_stat === OWL_SUCCESS);
	}

	/**
	 * Modify 1 or more fields in an existing scheme definition
	 * \param[in] $_field Array holding 1 or more field descriptions
	 * \see define_scheme()
	 * \private
	 * \return Boolean; false if the table description contains errors
	 */
	public function alter_scheme($_field)
	{
		if (!$this->inuse) {
			$this->set_status (SCHEMEHANDLE_NOTUSE);
			return ($this->severity);
		}
		foreach ($_field as $_fieldname => $_attributes) {
			$this->scheme['columns'][$_fieldname][$_attributes[0]] = $_attributes[1];
		}
		return $this->validate_scheme();
	}

	/**
	 * Validate the defined scheme. Some values will be modified to make sure the SQL
	 * statements can be prepared and compare() won't find differences on case diffs
	 * \private
	 * \return boolean False if there is an error in the scheme definition, True if no errors were found
	 */
	private function validate_scheme()
	{
		$_counters = array(
			 'auto_inc' => 0
		);
		if (!array_key_exists('columns', $this->scheme) || count($this->scheme['columns']) == 0) {
			$this->set_status (SCHEMEHANDLE_NOCOLS, $this->table);
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
				$this->set_status (SCHEMEHANDLE_MULAUTOINC, $this->table);
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
			$this->set_status (SCHEMEHANDLE_NOINDEX, $this->table);
			return true;
		}
		foreach ($this->scheme['indexes'] as $_idx => $_desc) {
			if (!array_key_exists('columns', $_desc)
				|| !is_array($_desc['columns'])
				|| count($_desc['columns']) == 0) {
					$this->set_status (SCHEMEHANDLE_NOCOLIDX, $this->table, $_idx);
					return false;
			}
			foreach ($_desc['columns'] as $_fld) {
				if (!array_key_exists($_fld, $this->scheme['columns'])) {
					$this->set_status (SCHEMEHANDLE_IVCOLIDX, $this->table, $_idx, $_fld);
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
	 */
	private function compare ()
	{
		$_diffs = array();
		$_current = array();
		$this->table_description($this->table, $_current);
		if ($this->get_status() === SCHEMEHANDLE_NOTABLE) {
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
	 * \private
	 */
	private function create_table()
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
		$this->db->set_query($_qry);
		return ($this->db->write($_dummy, __LINE__, __FILE__));
	}

	/**
	 * Make changes to the table
	 * \param[in] $_diffs Changes to make
	 * \param[in] $_drops True if existing fields should be dropped
	 * \private
	 */
	private function alter_table($_diffs, $_drops)
	{
		if ($_drops === true && array_key_exists('drop', $_diffs) && count($_diffs['drop']['columns']) > 0) {
			foreach ($_diffs['drop']['columns'] as $_fld => $_desc) {
				$this->db->set_query('ALTER TABLE ' . $this->db->tablename($this->table) . ' DROP ' . $_fld);
				$this->db->write(false, __LINE__, __FILE__);
			}
		}
		if (array_key_exists('mod', $_diffs) && count($_diffs['mod']['columns']) > 0) {
			foreach ($_diffs['mod']['columns'] as $_fld => $_desc) {
				$_qry = 'ALTER TABLE ' . $this->db->tablename($this->table)
					. ' CHANGE `' . $_fld . '` `' .$_fld . '` ' . $_desc['type']
					. $this->_define_field($_desc);
				$this->db->set_query($_qry);
				$this->db->write(false, __LINE__, __FILE__);
			}
		}
		if (array_key_exists('add', $_diffs) && count($_diffs['add']['columns']) > 0) {
			foreach ($_diffs['add']['columns'] as $_fld => $_desc) {
				$_qry = 'ALTER TABLE ' . $this->db->tablename($this->table)
					. ' ADD `' . $_fld . '` ' . $_desc['type']
					. $this->_define_field($_desc);
				$this->db->set_query($_qry);
				$this->db->write(false, __LINE__, __FILE__);
			}
		}
		return OWL_SUCCESS; // TODO proper checking
	}

	/**
	 * Create the SQL code for a field definition
	 * \param[in] $_desc Indexed array with the field properties from scheme definition
	 * \private
	 * \return string SQL code
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
	 * \private
	 * \param[in] $_tablename The tablename
	 * \return Indexed array holding all fields =&gt; datatypes, or null on errors
	 */
	private function get_table_columns($_tablename)
	{
		$_descr = array ();
		$_data  = array ();
		$_qry = 'SHOW FULL COLUMNS FROM ' . $this->db->tablename($_tablename);

		$this->db->read (DBHANDLE_DATA, $_data, $_qry, __LINE__, __FILE__);
		if ($this->db->get_status() === 'DBHANDLE_NODATA') {
			// A table without fields.... not likely.... some bug somewhere
			$this->set_status (SCHEMEHANDLE_EMPTYTABLE, $_tablename);
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
	 * \private
	 * \param[in] $_tablename The tablename
	 * \return Indexed array holding all fields =&gt; datatypes, or null on errors
	 */
	private function get_table_indexes($_tablename)
	{
		$_descr = array ();
		$_data  = array ();
		$_qry = 'SHOW INDEXES FROM ' . $this->db->tablename($_tablename);

		$this->db->read (DBHANDLE_DATA, $_data, $_qry, __LINE__, __FILE__);
		if ($this->db->get_status() === 'DBHANDLE_NODATA') {
			$this->set_status (SCHEMEHANDLE_NOINDEX, $_tablename);
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
	 */
	public function table_description ($tablename, &$data)
	{
		if (!$this->db->table_exists($tablename)) {
			$data = array();
			$this->set_status (SCHEMEHANDLE_NOTABLE);
			return ($this->severity);
		}
		$data['columns'] = $this->get_table_columns($tablename);
		$data['indexes'] = $this->get_table_indexes($tablename);
		return ($this->severity);
	}
	
	/**
	 * Reset the internal data structure
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
 */

/*
 * Register this class and all status codes
 */
Register::register_class ('SchemeHandler');

//Register::set_severity (OWL_DEBUG);

Register::set_severity (OWL_INFO);
Register::register_code ('SCHEMEHANDLE_NOTABLE');
Register::register_code ('SCHEMEHANDLE_NOINDEX');

//Register::set_severity (OWL_OK);
//Register::set_severity (OWL_SUCCESS);

Register::set_severity (OWL_WARNING);
Register::register_code ('SCHEMEHANDLE_IVTABLE');
			
Register::set_severity (OWL_BUG);
Register::register_code ('SCHEMEHANDLE_INUSE');
Register::register_code ('SCHEMEHANDLE_NOUSE');
Register::register_code ('SCHEMEHANDLE_EMPTYTABLE');
					
Register::set_severity (OWL_ERROR);
Register::register_code ('SCHEMEHANDLE_DUPLPRKEY');
Register::register_code ('SCHEMEHANDLE_MULAUTOINC');
Register::register_code ('SCHEMEHANDLE_NOCOLIDX');
Register::register_code ('SCHEMEHANDLE_IVCOLIDX');
Register::register_code ('SCHEMEHANDLE_NOCOLS');

//Register::set_severity (OWL_FATAL);
//Register::set_severity (OWL_CRITICAL);
