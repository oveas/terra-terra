<?php
/**
 * \file
 * This file defines the Hierarchical DataHandler class
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: class.datahandlerh.php,v 1.1 2011-05-13 16:39:19 oscar Exp $
 */

/**
 * \ingroup OWL_SO_LAYER
 * This class contains DB datasets for hierarchical tables
 * \brief The OWL Hierarchical Data object 
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version May 13, 2011 -- O van Eijk -- initial version
 */
class HDataHandler extends DataHandler
{
	/**
	 * Field name of the Left position indicator. Defaults to 'lft'
	 */
	private $left;

	/**
	 * Field name of the Right position indicator. Defaults to 'rgt'
	 */
	private $right;

	/**
	 * Class constructor.
	 * \param[in] $tablename Default table name for this dataset
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function __construct ($tablename = '')
	{
		_OWL::init();
		parent::__construct($tablename);
		$this->left = 'lft';
		$this->right = 'rgt';
	}

	/**
	 * Set or overwrite the fieldname of the left position indicator
	 * \param[in] $fieldname Name of the tablefield
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setLeft ($fieldname)
	{
		$this->left = $fieldname;
	}

	/**
	 * Set or overwrite the fieldname of the right position indicator
	 * \param[in] $fieldname Name of the tablefield
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setRight ($fieldname)
	{
		$this->right = $fieldname;
	}

	/**
	 * Get all items without children
	 * \return 2-D array with all records for all leaf nodes, or false on errors
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function getLeafNodes ()
	{
		$table = $this->owl_database->tablename($this->owl_tablename);
		$query = 'SELECT * '
				. "FROM $table "
				. "WHERE $this->right = $this->left + 1 "
				. "ORDER $this->left"
		;
		return ($this->readQuery($query, __LINE__));
	}

	/**
	 * Get the complete path of ancestors for a given node
	 * \param[in] $field Name of the field on which the field should be matched
	 * \param[in] $value Value of the field to match
	 * \return 2-D array with all records for the full path, or false on errors
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function getPathByChild ($field, $value)
	{
		$table = $this->owl_database->tablename($this->owl_tablename);
		$query = 'SELECT parent.* '
				. "FROM $table AS node "
				. ",    $table AS parent "
				. "WHERE node.$this->left BETWEEN parent.$this->left AND parent.$this->right "
				. "AND   node.$field = '$value' "
				. "ORDER BY parent.$this->left"
		;
		return ($this->readQuery($query, __LINE__));
	}

	/**
	 * Get the complete path of offspring for a given node
	 * \param[in] $field Name of the field on which the field should be matched
	 * \param[in] $value Value of the field to match
	 * \return 2-D array with all records for the full path, or false on errors
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function getPathByParent ($field, $value)
	{
		$table = $this->owl_database->tablename($this->owl_tablename);
		$query = 'SELECT node.* '
				. "FROM $table AS node "
				. ",    $table AS parent "
				. "WHERE node.$this->left BETWEEN parent.$this->left AND parent.$this->right "
				. "AND   parent.$field = '$value' "
				. "ORDER BY node.$this->left"
		;
		return ($this->readQuery($query, __LINE__));
	}
	
	/**
	 * Get the depth of all nodes or a single node.
	 * \param[in] $field The fieldname of which value that will be returned with the depth
	 * \param[in] $value An optional value to match if only the depth of 1 field is requested
	 * \return Array with all matched records holding the fieldvalue and the depth
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function getNodeDepth ($field, $value = null)
	{
		$table = $this->owl_database->tablename($this->owl_tablename);
		$query = "SELECT node.$field AS $field "
				. ",     (COUNT(parent.$field) - 1) AS depth"
				. "FROM $table AS node "
				. ",    $table AS parent "
				. "WHERE node.$this->left BETWEEN parent.$this->left AND parent.$this->right "
		;
		if ($value !== null) {
			$query .= "AND node.$field = '$value' ";
		}
		$query .= "GROUP BY node.$field "
				. "ORDER BY parent.$this->left"
		;
		return ($this->readQuery($query, __LINE__));
	}

	/**
	 * Retrieve all childnodes starting with a given parent
	 * \param[in] $field Name of the field on which the parent should be matched
	 * \param[in] $value Value of the parent's field
	 * \return 2-D array with all records for all direct children, or false on errors
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function getFullOffspring ($field, $value)
	{
		$table = $this->owl_database->tablename($this->owl_tablename);
		$query = 'SELECT node.* '
				. "FROM $table AS node "
				. ",    $table AS parent "
				. "WHERE node.$this->left BETWEEN parent.$this->left AND parent.$this->right "
				. "AND   parent.$field = '$value' "
				. "ORDER BY node.$this->left"
		;
		return ($this->readQuery($query, __LINE__));
	}

	/**
	 * Retrieve the direct chilren of a given parent
	 * \param[in] $field Name of the field on which the parent should be matched
	 * \param[in] $value Value of the parent's field
	 * \return Array with all matched records holding the fieldvalue and the depth (always 1, but required for the match)
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function getDirectChildren ($field, $value)
	{
		$table = $this->owl_database->tablename($this->owl_tablename);
		$query = "SELECT node.$field "
				. ",    (COUNT(parent.$field) - (dtree.depth + 1)) AS depth) "
				. "FROM $table AS node "
				. ",    $table AS parent "
				. ",    $table AS subparent "
				. '('
				. "     SELECT node.$field, (COUNT(parent.$field) - 1) AS depth "
				. "     FROM $table AS node "
				. "     ,    $table AS parent "
				. "     WHERE node.$this->left BETWEEN parent.$this->left AND parent.$this->right "
				. "     AND   node.$field = '$value' "
				. "     GROUP BY node.$field "
				. "     ORDER BY node.$this->left "
				. ') AS dtree '
				. "WHERE node.$this->left BETWEEN parent.$this->left AND parent.$this->right "
				. "AND   node.$this->left BETWEEN subparent.$this->left AND subparent.$this->right "
				. "AND   subparent.$field = dtree.$field "
				. "GROUP BY node.$field "
				. "HAVING depth = 1 "
				. "ORDER BY node.$this->left "
			;
		return ($this->readQuery($query, __LINE__));
	}

	/**
	 * Internal helper method to execute a read query
	 * \param[in] $query Query to execute
	 * \param[in] $line Line number from which this method is called
	 * \return Result of the query, or false on errors
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function readQuery ($query, $line)
	{
		if ($this->owl_database->read (DBHANDLE_DATA, $data, $query, $line, __FILE__) >= OWL_WARNING) {
			$this->setStatus(DATA_DBWARNING, array($this->owl_database->getLastWarning()));
			return false;
		}
		return ($data);
	}
}

/*
 * Register this class and all status codes
 */

Register::registerClass ('HDataHandler');

//Register::setSeverity (OWL_DEBUG);
//Register::setSeverity (OWL_INFO);
//Register::setSeverity (OWL_OK);
//Register::setSeverity (OWL_SUCCESS);
//Register::setSeverity (OWL_WARNING);
//Register::setSeverity (OWL_BUG);
//Register::setSeverity (OWL_ERROR);
//Register::setSeverity (OWL_FATAL);
//Register::setSeverity (OWL_CRITICAL);
