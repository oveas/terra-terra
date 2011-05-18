<?php
/**
 * \file
 * This file defines the Hierarchical DataHandler class
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: class.datahandlerh.php,v 1.4 2011-05-18 12:03:48 oscar Exp $
 */

/**
 * \defgroup HDATA_FollowXLink Follow crosslinks
 * These flags if and to what level crosslinks should be followed when full trees are retrieved
 * @{
 */
//! Do not follow crosslinks; only retrieve the direct offspring
define ('HDATA_XLINK_FOLLOW_NO',		0);

//! Retrieve trees for crosslinks that link directly to the maintree
define ('HDATA_XLINK_FOLLOW_ONCE',		1);

//! Follow all crosslinks and crosslinks of crosslinks.
//! \warning This might cause many queries resulting and a huge amount of data on large tables!
//! \warning This might cause loops!
define ('HDATA_XLINK_FOLLOW_UNLIMITED',	99);

//! @}


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
	 * Name of the crosslink field to allow multiple parents
	 */
	private $xlink;

	/**
	 * Name if the primary key field, required for closslinks
	 */
	private $xlinkID;

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
		$this->xlink = null;
		$this->xlinkID = null;
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
	 * Enable crosslinks allowing items in the hierarchy to be reachable via 2 parents
	 * \param[in] $fieldname Name of the field holding the additional parent
	 * \return True on success, false when the ID field has not been defined yet
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function enableCrossLink($fieldname)
	{
		if ($this->xlinkID === null) {
			$this->setStatus(HDATA_NOXLINKID);
			return (false);
		}
		$this->xlink = $fieldname;
		return (true);
	}

	/**
	 * Set the name of the primary key field. This is required for crosslinks
	 * \param[in] $fieldname Name of the field
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setPrimaryKey ($fieldname)
	{
		$this->xlinkID = $fieldname;
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
	 * Get the record for a single node
	 * \param[in] $field Name of the field on which the value should be matched, must be a primary key or unique indexed field
	 * \param[in] $value Value of the field to match
	 * \return Array with all fields for this node, or false on errors
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function getNode ($field, $value)
	{
		$table = $this->owl_database->tablename($this->owl_tablename);
		$query = 'SELECT * '
				. "FROM $table "
				. "WHERE $field = '$value' "
		;
		if (($_node = $this->readQuery($query, __LINE__)) === false) {
			return (false);
		} else {
			return (count($_node) > 0 ? $_node[0] : $_node);
		}
	}

	/**
	 * Get the complete path of ancestors for a given node
	 * \param[in] $field Name of the field on which the field should be matched, must be a primary key or unique indexed field
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
	 * \param[in] $field Name of the field on which the field should be matched, must be a primary key or unique indexed field
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
	 * \param[in] $field The fieldname of which value that will be returned with the depth, must be a primary key or unique indexed field
	 * \param[in] $value An optional value to match if only the depth of 1 field is requested
	 * \return Array with all matched records including the depth
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function getNodeDepth ($field, $value = null)
	{
		$table = $this->owl_database->tablename($this->owl_tablename);
		$query = "SELECT node.* "
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
	 * \param[in] $field Name of the field on which the parent should be matched, must be a primary key or unique indexed field
	 * \param[in] $value Value of the parent's field
	 * \param[in] $follow When crosslinks are enabled, this identifies to what level crosslinks should be followed.
	 * \warning User yje $follow parameter with care! Right now, only 3 options are implemented. HDATA_XLINK_FOLLOW_NO is the default value,
	 * but checks are made only for HDATA_XLINK_FOLLOW_NO and HDATA_XLINK_FOLLOW_ONCE. Any other value
	 * will be interpreted as HDATA_XLINK_FOLLOW_UNLIMITED!
	 * See \ref HDATA_FollowXLink for more info!
	 * \return 2-D array with all records for all direct children, or false on errors
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function getFullOffspring ($field, $value, $follow = HDATA_XLINK_FOLLOW_NO)
	{
		$table = $this->owl_database->tablename($this->owl_tablename);
		$query = 'SELECT node.* '
				. "FROM $table AS node "
				. ",    $table AS parent "
				. "WHERE node.$this->left BETWEEN parent.$this->left AND parent.$this->right "
				. "AND   parent.$field = '$value' "
				. "ORDER BY node.$this->left "
		;
		if (($data = $this->readQuery($query, __LINE__)) === false) {
			return (false);
		}
		if ($this->xlink !== null && $follow !== HDATA_XLINK_FOLLOW_NO && count($data) > 0) {
			if ($follow === HDATA_XLINK_FOLLOW_ONCE) {
				$follow = HDATA_XLINK_FOLLOW_NO;
			}
			foreach ($data as $node) {
				if ($this->owl_database->read(DBHANDLE_DATA, $idList
							, "SELECT $this->xlinkID FROM $table WHERE $this->xlink = '" . $node[$this->xlinkID] . "' "
							, __LINE__, __FILE__)  >= OWL_WARNING) {
					$this->setStatus(DATA_DBWARNING, array($this->owl_database->getLastWarning()));
					return (false);
				}
				if ($this->dbStatus() === DBHANDLE_NODATA) {
					continue;
				}
				foreach ($idList as $xlinkNode) {
					// Call myself recursively
					$xlinkTree = $this->getFullOffspring($field, $xlinkNode[$field], $follow);
					if ($xlinkTree === false) {
						return (false);
					}
					$data = array_merge($data, $xlinkTree);
				}
			}
		}
		return ($data);
	}

	/**
	 * Retrieve the direct chilren of a given parent
	 * \param[in] $field Name of the field on which the parent should be matched, must be a primary key or unique indexed field
	 * \param[in] $value Value of the parent's field
	 * \param[in] $xlink True (default) of crosslinked childnodes should be included
	 * \return Array with all matched records including the depth (always 1, but required for the match)
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function getDirectChildren ($field, $value, $xlink = true)
	{
		$table = $this->owl_database->tablename($this->owl_tablename);
		$query = "SELECT node.* "
				. ",    (COUNT(parent.$field) - (dtree.depth + 1)) AS depth "
				. "FROM $table AS node "
				. ",    $table AS parent "
				. ",    $table AS subparent "
				. ', ('
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
		if ($this->xlink !== null && $xlink === true) {
			if ($this->owl_database->read(DBHANDLE_SINGLEFIELD, $id 
						, "SELECT $this->xlinkID FROM $table WHERE $field = '$value' "
						, __LINE__, __FILE__)  >= OWL_WARNING) {
				$this->setStatus(DATA_DBWARNING, array($this->owl_database->getLastWarning()));
				return (false);
			}
			$query .= 'UNION '
					. "SELECT node.$field "
					. ',      0 AS depth ' // TODO Do we need the depth here??
					. "FROM $table AS node "
					. "WHERE node.$this->xlink = $id "
			;
		}
		return ($this->readQuery($query, __LINE__));
	}

	/**
	 * Get all nodes from the given depth, optionally from a given tree
	 * \param[in] $depth Depth level, where 0 is the toplevel
	 * \param[in] $field Fieldname of a unique indexed field used to identify records.
	 * \param[in] $value Value for fieldname identifying the top node for a tree. When this is
	 * null (default), all nodes with the same depth will be returned. If depth eqs 0, the value is ignored.
	 * \note Crosslinks, when enabled, are not followed in this method
	 * \return Array with all matched records including the given depth (required for the match)
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function getNodesByDepth($depth, $field, $value = null)
	{
		$table = $this->owl_database->tablename($this->owl_tablename);
		$query = "SELECT node.* "
				. ",     COUNT(ancestor.$field) - 1 AS ancestors "
				. "FROM $table AS node "
				. ",    $table AS ancestor "
		;
		if ($value !== null && $depth > 0) {
			$query .= ", $table AS parents ";
		}
		$query.= "WHERE node.$this->left BETWEEN ancestor.lft AND ancestor.$this->right ";
		if ($value !== null && $depth > 0) {
				$query .= "AND node.$this->left  BETWEEN parents.lft AND parents.$this->right "
						. "AND parents.$field = '$value' "
				;
		}
		$query .= "GROUP BY node.$field "
				. "HAVING ancestors = $depth "
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
			return (false);
		}
		return ($data);
	}

	/**
	 * Insert a new node
	 * \param[in] $data Indexed array with the data, in the format (field => value, ...) where all
	 * table fields must be provided that are not allowed to be null. The fields for left and right
	 * will be ignored, if the primary key is an auto increment, this must not be given in the array.
	 * \param[in] $parent Parent under which the new node will be added. This must be an indexed array
	 * with the keys 'field' and 'value' identifying the parent node, where field is the name of a unique
	 * indexed tablefield and value the value for the requested parent.
	 * To insert a new node at toplevel, leave out the value, but 'field' is always required!
	 * \param[in] $position Position at which the new node will be inserted, where '0' results as
	 * an insert as the leftmost childnode, and any negative value (default) or a value greater than
	 * the current number of children will result in an insert as the rightmost child.
	 * \note During the insert operation, left and right values are changed and might be conflicting at a certain point.
	 * Technically that is no problem during a transaction, but some databases will fail when the left and right values
	 * are indexed unique, so these fields should be indexed nut <em>not</em> UNIQUE!
	 * \return True on success, false on errors
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function insertNode (array $data, array $parent, $position = -1)
	{
		$newLeft = $this->_getNewLeft($parent, $position);

		$table = $this->owl_database->tablename($this->owl_tablename);
		$this->owl_database->startTransaction('insertNode');
		$this->owl_database->lockTable($table, DBDRIVER_LOCKTYPE_WRITE);
		
		$_stat = true;
		$_stat = $this->writeQuery("UPDATE $table "
							. "SET $this->right = $this->right + 2 "
							. "WHERE $this->right >= $newLeft"
							, __LINE__);
		if ($_stat !== false) {
			$_stat = $this->writeQuery("UPDATE $table "
								. "SET $this->left = $this->left + 2 "
								. "WHERE $this->left >= $newLeft"
								, __LINE__);
		}

		// Add the left and right values
		$data[$this->left] = $newLeft;
		$data[$this->right] = $newLeft + 1;

		if ($_stat !== false) {
			$_fields = array();
			$_values = array();
			foreach ($data as $_f => $_v) {
				$_fields[] = $_f;
				$_values[] = "'$_v'";
			}
			$_stat = $this->writeQuery("INSERT INTO $table ("
								. implode(',', $_fields)
								. ') VALUES ('
								. implode(',', $_values)
								. ')'
								, __LINE__);
		}
		$this->owl_database->unlockTable($table);
		if ($_stat === false) {
			$this->owl_database->rollbackTransaction('insertNode');
			return (false);
		} else {
			$this->owl_database->commitTransaction('insertNode');
			return (true);
		}
	}

	/**
	 * Get the new left position for a node being moved or inserted
	 * \param[in] $parent Parent under which the (new) node will be added.
	 * \param[in] $position Position at which the new node will be inserted.
	 * \return The value for the left position
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function _getNewLeft (array $parent, $position = -1)
	{
		if (!array_key_exists('value', $parent)) {
			$topLevel = true;
		} else {
			$topLevel = false;
		}
		if (!array_key_exists('field', $parent) || ($topLevel === false && !array_key_exists('value', $parent))) {
			$this->setStatus(HDATA_IVNODESPEC);
			return (false);
		}
		if ($topLevel) {
			$childNodes = $this->getNodesByDepth(0, $parent['field']);
		} else {
			$childNodes = $this->getDirectChildren($parent['field'], $parent['value'], false);
		}
		if (count($childNodes) == 0) {
			if ($topLevel) {
				// It seems this is our very first record
				$newLeft = 1;
			} else {
				// First child, start with parents left value
				$parentNode = $this->getNode($parent['field'], $parent['value']);
				$newLeft = $parentNode[$this->left] + 1;
			}
		} else {
			if ($position > count($childNodes) || $position < 0) {
				// Insert as the rightmost
				$newLeft = $childNodes[count($childNodes)-1][$this->right] + 1;
			} else {
				// Insert left of the node currently at this position
				$newLeft = $childNodes[$position][$this->left];
			}
		}
		return ($newLeft);
	}

	/**
	 * Add a crosslink so a given node, adding (or overwriting) a second parent.
	 * \param[in] $node Identification if the node to change. This must be an indexed array
	 * with the keys 'field' and 'value' identifying the node, where field is the name of a unique
	 * indexed tablefield and value the value for the requested node.
	 * \param[in] $parent Identification of the additional parent. This must be an indexed array
	 * with the keys 'field' and 'value' identifying the parent node, where field is the name of a unique
	 * indexed tablefield and value the value for the requested parent.
	 * \return True on success, false on errors
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function addParent (array $node, array $parent)
	{
		if ($this->xlink === null) {
			$this->setStatus(HDATA_XLINKDISA);
			return (false);
		}
		if (!array_key_exists('field', $node) || !array_key_exists('value', $node)
			|| !array_key_exists('field', $parent) || !array_key_exists('value', $parent)) {
			$this->setStatus(HDATA_IVNODESPEC);
			return (false);
		}
		$table = $this->owl_database->tablename($this->owl_tablename);
		$parentNode = $this->getNode($parent['field'], $parent['value']);
		return ($this->writeQuery("UPDATE $table "
							. "SET $this->xlink = " . $parentNode[$this->xlinkID] . ' '
							. 'WHERE ' . $node['field'] . " = '" . $node['value'] . "'"
							, __LINE__));
	}

	/**
	 * Delete a single node from the table. If the node has children, they will be added to the deleted
	 * nodes parent (so if the deleted node is a toplevel node, the children will be toplevel).
	 * All exising cross references will be removed (nullified).
	 * \param[in] $field Name of the field on which the value should be matched, must be a primary key or unique indexed field
	 * \param[in] $value Value of the field to match
	 * \return True on success
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function removeNode ($field, $value)
	{
		$table = $this->owl_database->tablename($this->owl_tablename);
		$node = $this->getNode($field, $value);
		$this->owl_database->startTransaction('insertNode');
		$this->owl_database->lockTable($table, DBDRIVER_LOCKTYPE_WRITE);
		$_stat = $this->writeQuery("DELETE FROM $table "
								. "WHERE $this->left = " . $node[$this->left] . ' '
								, __LINE__);
		if (($node[$this->right] - $node[$this->left]) > 2) {
			// Not a leafnode, add the children to the parent
			if ($_stat !== false) {
				$_stat = $this->writeQuery("UPDATE $table "
										. "SET $this->right = $this->right - 1 "
										. "WHERE $this->right BETWEEN " . $node[$this->left] . ' AND ' . $node[$this->right] . ' '
										, __LINE__);
			}
			if ($_stat !== false) {
				$_stat = $this->writeQuery("UPDATE $table "
										. "SET $this->left = $this->left - 1 "
										. "WHERE $this->left BETWEEN " . $node[$this->left] . ' AND ' . $node[$this->right] . ' '
										, __LINE__);
			}
		}
		if ($_stat !== false) {
			$_stat = $this->writeQuery("UPDATE $table "
									. "SET $this->right = $this->right - 2 "
									. "WHERE $this->right > " . $node[$this->right] . ' '
									, __LINE__);
		}
		if ($_stat !== false) {
			$_stat = $this->writeQuery("UPDATE $table "
									. "SET $this->left = $this->left - 2 "
									. "WHERE $this->left > " . $node[$this->right] . ' '
									, __LINE__);
		}
		if ($_stat !== false && $this->xlink !== null) {
			// Remove crosslinks
			$_stat = $this->writeQuery("UPDATE $table "
									. "SET $this->xlink = NULL "
									. "WHERE $this->xlink > " . $node[$this->xlinkID] . ' '
									, __LINE__);
		}

		$this->owl_database->unlockTable($table);
		if ($_stat === false) {
			$this->owl_database->rollbackTransaction('insertNode');
			return (false);
		} else {
			$this->owl_database->commitTransaction('insertNode');
			return (true);
		}
	}
	
	/**
	 * Delete a complete tree, removeing all cross references to from outside the tree to any of the nodes being deleted.
	 * All exising cross references will be removed (nullified).
	 * \warning This deletes the given node and all values under it! Use with care!
	 * \param[in] $field Name of the field on which the value should be matched, must be a primary key or unique indexed field
	 * \param[in] $value Value of the field identifying the toplevel of the tree
	 * \return True on success
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function removeTree ($field, $value)
	{
		$table = $this->owl_database->tablename($this->owl_tablename);
		$node = $this->getNode($field, $value);
		$this->owl_database->startTransaction('insertNode');
		$_stat = true;
		if ($this->xlink !== null) {
			// First, set all crosslinks to this tree to NULL.
			// TODO This must be done before locking the table, since we can't lock for read and write yet. Fix the locking mechanism first!

			// Use an extra select; the same table cannot be an update target and used in subselects
			$_ids = $this->readQuery("SELECT $this->xlinkID "
							. "FROM $table "
							. "WHERE $this->left BETWEEN " . $node[$this->left] . ' AND ' . $node[$this->right] . ' '
							, __LINE__);
			$_idList = array();
			foreach ($_ids as $_id) {
				$_idList[] = $_id[$this->xlinkID];
			}
			$_stat = $this->writeQuery("UPDATE $table "
									. "SET $this->xlink = NULL "
									. "WHERE $this->xlink IN (" . implode(',',$_idList) . ') '
									, __LINE__);
		}
		$this->owl_database->lockTable($table, DBDRIVER_LOCKTYPE_WRITE);
		if ($_stat !== false) {
			$_stat = $this->writeQuery("DELETE FROM $table "
								. "WHERE $this->left BETWEEN " . $node[$this->left] . ' AND ' . $node[$this->right] . ' '
								, __LINE__);
		}
		$width = $node[$this->right] - $node[$this->right] + 1;
		if ($_stat !== false) {
			$_stat = $this->writeQuery("UPDATE $table "
									. "SET $this->right = $this->right - $width "
									. "WHERE $this->right > " . $node[$this->right] . ' '
									, __LINE__);
		}
		if ($_stat !== false) {
			$_stat = $this->writeQuery("UPDATE $table "
									. "SET $this->left = $this->left - $width "
									. "WHERE $this->left > " . $node[$this->right] . ' '
									, __LINE__);
		}
		$this->owl_database->unlockTable($table);
		if ($_stat === false) {
			$this->owl_database->rollbackTransaction('insertNode');
			return (false);
		} else {
			$this->owl_database->commitTransaction('insertNode');
			return (true);
		}
	}

	/**
	 * Move a node or a complete tree to a new parent.
	 * \param[in] $field Name of the field on which the value should be matched, must be a primary key or unique indexed field
	 * \param[in] $value Value of the field to match to identifying the node
	 * \param[in] $newParent Parent to which the node will be moved. This must be an indexed array
	 * with the keys 'field' and 'value' identifying the parent node, where field is the name of a unique
	 * indexed tablefield and value the value for the requested parent.
	 * \param[in] $position Position at which the node will be inserted, where '0' results as
	 * an insert as the leftmost childnode, and any negative value (default) or a value greater than
	 * the current number of children will result in an postision as the rightmost child.
	 * \note To keep a complete tree together while being moved, the left and right values are
	 * temporarily set to a negative value. Therefore, this method will not work when the left and right
	 * values are defined as UNSIGNED!
	 * \return True on success
	 * \todo Is the tree being moved contains crosslinks to the new parent already, they should be deleted
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function moveNode ($field, $value, array $newParent, $position = -1)
	{
		if (!array_key_exists('field', $newParent) || !array_key_exists('value', $newParent)) {
			$this->setStatus(HDATA_IVNODESPEC);
			return (false);
		}
		$table = $this->owl_database->tablename($this->owl_tablename);
		$node = $this->getNode($field, $value);
		$this->owl_database->startTransaction('insertNode');
		$this->owl_database->lockTable($table, DBDRIVER_LOCKTYPE_WRITE); 
		$_stat = true;
		
		if ($_stat !== false) {
			$_stat = $this->writeQuery("UPDATE $table "
									. "SET $this->left = 0 "
									. ",   $this->right = 0 "
									. "WHERE $this->left = " . $node[$this->left] . ' '
									, __LINE__);
		}
		if ($_stat !== false) {
			// Set all left and right values in the tree being moved to their negative values
			$_stat = $this->writeQuery("UPDATE $table "
									. "SET $this->left = 0 - $this->left "
									. ",   $this->right = 0 - $this->right "
									. "WHERE $this->left BETWEEN " . $node[$this->left] . ' AND ' . $node[$this->right] . ' '
									, __LINE__);
		}

		// Get the width of the tree being moved
		$_width = ($node[$this->right] - $node[$this->left] + 1);

		if ($_stat !== false) {
			// Now the original truee is parked, shrink the total tree by decreasing everything
			// right from the original object (in 2 steps to make sure the direct parents right- only
			// is decreased!)
			$_stat = $this->writeQuery("UPDATE $table "
									. "SET $this->right = $this->right - $_width "
									. "WHERE $this->right > " . $node[$this->right] . ' '
									, __LINE__);
		}
		if ($_stat !== false) {
			$_stat = $this->writeQuery("UPDATE $table "
									. "SET $this->left = $this->left - $_width "
									. "WHERE $this->left > " . $node[$this->right] . ' '
									, __LINE__);
		}

		// Now set the new left value. This must be retrieved *after* the shrink above to
		// make sure we have the new leftvalue.
		// Then calculate how far the tree is actually moved

		// TODO Since _getNewLeft() reads from the table using aliases, we can't access the table
		// using the active lock (at least in MySQL), so we temporarily release the lock now
		$this->owl_database->unlockTable($table); 
		$_newLeft = $this->_getNewLeft($newParent, $position);
		$this->owl_database->lockTable($table, DBDRIVER_LOCKTYPE_WRITE); 
		$_newRight = $_newLeft + $_width - 1;
		$_move = $node[$this->left] - $_newLeft;

		if ($_stat !== false) {
			// Make room for the tree at the new position
			$_stat = $this->writeQuery("UPDATE $table "
									. "SET $this->right = $this->right + $_width "
									. "WHERE $this->right >= $_newLeft "
									, __LINE__);
		}
		if ($_stat !== false) {
			$_stat = $this->writeQuery("UPDATE $table "
									. "SET $this->left = $this->left + $_width "
									. "WHERE $this->left >= $_newLeft "
									, __LINE__);
		}

		if ($_stat !== false) {
			// Ok, now put the root node of this tree back in place
			$_stat = $this->writeQuery("UPDATE $table "
									. "SET $this->left = $_newLeft "
									. ",   $this->right = $_newRight "
									. "WHERE $this->left = 0 "
									. "AND   $this->right = 0 "
									, __LINE__);
		}
		
		if ($_stat !== false) {
			// And finally move the tree to the correct position
			$_stat = $this->writeQuery("UPDATE $table "
									. "SET $this->left = ABS($this->left) - $_move "
									. ",   $this->right = ABS($this->right) - $_move "
									. "WHERE $this->left < 0 "
									, __LINE__);
		}

		$this->owl_database->unlockTable($table);
		if ($_stat === false) {
			$this->owl_database->rollbackTransaction('insertNode');
			return (false);
		} else {
			$this->owl_database->commitTransaction('insertNode');
			return (true);
		}
	}

	/**
	 * Internal helper method to execute a write query
	 * \param[in] $query Query to execute
	 * \param[in] $line Line number from which this method is called
	 * \return Number of lines written, false on errors
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function writeQuery ($query, $line)
	{
		$this->owl_database->setQuery($query);
		if ($this->owl_database->write ($rowCount, $line, __FILE__) >= OWL_WARNING) {
			$this->setStatus(DATA_DBWARNING, array($this->owl_database->getLastWarning()));
			return (false);
		}
		return ($rowCount);
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
Register::setSeverity (OWL_WARNING);
Register::registerCode ('HDATA_IVNODESPEC');

Register::setSeverity (OWL_BUG);
Register::registerCode ('HDATA_NOXLINKID');
Register::registerCode ('HDATA_XLINKDISA');

//Register::setSeverity (OWL_ERROR);
//Register::setSeverity (OWL_FATAL);
//Register::setSeverity (OWL_CRITICAL);
