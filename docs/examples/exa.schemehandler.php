<?php
/*
 * This example users class SchemeHandler to create the table exa_person in the database,
 * or alters the table conforming the description if already existing.
 * After executing of this example, this will be how the table looks like (assuming the
 * table prefix is set to 'exa_' in the configuration):
 * mysql> desc exa_person;
 * +---------+--------------------------------+------+-----+---------+----------------+
 * | Field   | Type                           | Null | Key | Default | Extra          |
 * +---------+--------------------------------+------+-----+---------+----------------+
 * | id      | int(11)                        | NO   | PRI | NULL    | auto_increment |
 * | name    | varchar(24)                    | NO   | MUL | NULL    |                |
 * | address | text                           | NO   | MUL | NULL    |                |
 * | phone   | varchar(16)                    | YES  |     | NULL    |                |
 * | country | enum('NL','BE','DE','FR','ES') | NO   |     | ES      |                |
 * +---------+--------------------------------+------+-----+---------+----------------+
 * 5 rows in set (0.00 sec)
 *
 */
// Het or instantiate the schemehandler
$_scheme = TT::factory('schemehandler');

// Define the table layout
$_table = array(
	 'id' => array (
			 'type' => 'INT'
			,'length' => 11
			,'auto_inc' => true
			,'null' => false
	)
	,'name' => array (
			 'type' => 'varchar'
			,'length' => 24
			,'auto_inc' => false
			,'null' => false
	)
	,'address' => array (
			 'type' => 'text'
			,'length' => 0
			,'null' => false
	)
	,'phone' => array (
			 'type' => 'varchar'
			,'length' => 16
			,'null' => true
	)
	,'country' => array (
			 'type' => 'enum'
			,'length' => 0
			,'auto_inc' => false
			,'options' => array('NL', 'BE', 'DE', 'FR', 'ES')
			,'default' => 'ES'
			,'null' => false
	)
);

// Define the table indexes
// Note the field 'id' is not specified here; since it
// is an autoincrement field, it will be primary key by default.
$_index = array (
	 'name' => array(
			 'columns' => array ('name')
			,'primary' => false
			,'unique' => false
			,'type' => null
	)
	,'address' => array(
			 'columns' => array ('address')
			,'primary' => false
			,'unique' => false
			,'type' => 'FULLTEXT'
	)
);

$_scheme->createScheme('person');	// Create the scheme in the SchemeHandler
$_scheme->defineScheme($_table);	// Define the columns
$_scheme->defineIndex($_index);		// Define the indexes
$_scheme->scheme();					// Check the table in the database and create or alter it
$_scheme->reset();					// Reset the schemehandler (clear the definitions)

// Now setup the same table in the schemehandler again, this time by reading from the database
$_scheme->tableDescription('person', $_data);
// Show the result
echo '<pre>'. print_r($_data, 1) . '</pre>';

/*
 * The page will show this datastructure:
 * Array
 * (
 *     [columns] => Array
 *         (
 *             [id] => Array
 *                 (
 *                     [type] => int
 *                     [length] => 11
 *                     [unsigned] =>
 *                     [zerofill] =>
 *                     [null] =>
 *                     [auto_inc] => 1
 *                     [default] =>
 *                     [comment] =>
 *                 )
 *
 *             [name] => Array
 *                 (
 *                     [type] => varchar
 *                     [length] => 24
 *                     [unsigned] =>
 *                     [zerofill] =>
 *                     [null] =>
 *                     [auto_inc] => 0
 *                     [default] =>
 *                     [comment] =>
 *                 )
 *
 *             [address] => Array
 *                 (
 *                     [type] => text
 *                     [null] =>
 *                     [auto_inc] => 0
 *                     [default] =>
 *                     [comment] =>
 *                 )
 *
 *             [phone] => Array
 *                 (
 *                     [type] => varchar
 *                     [length] => 16
 *                     [unsigned] =>
 *                     [zerofill] =>
 *                     [null] => 1
 *                     [auto_inc] => 0
 *                     [default] =>
 *                     [comment] =>
 *                 )
 *
 *             [country] => Array
 *                 (
 *                     [type] => enum
 *                     [null] =>
 *                     [auto_inc] => 0
 *                     [options] => Array
 *                         (
 *                             [0] => 'NL'
 *                             [1] => 'BE'
 *                             [2] => 'DE'
 *                             [3] => 'FR'
 *                             [4] => 'ES'
 *                         )
 *
 *                     [default] => ES
 *                     [comment] =>
 *                 )
 *
 *         )
 *
 *     [indexes] => Array
 *         (
 *             [PRIMARY] => Array
 *                 (
 *                     [columns] => Array
 *                         (
 *                             [1] => id
 *                         )
 *
 *                     [unique] => 1
 *                     [type] => BTREE
 *                     [comment] =>
 *                 )
 *
 *             [name] => Array
 *                 (
 *                     [columns] => Array
 *                         (
 *                             [1] => name
 *                         )
 *
 *                     [unique] =>
 *                     [type] => BTREE
 *                     [comment] =>
 *                 )
 *
 *             [address] => Array
 *                 (
 *                     [columns] => Array
 *                         (
 *                             [1] => address
 *                         )
 *
 *                     [unique] =>
 *                     [type] => FULLTEXT
 *                     [comment] =>
 *                 )
 *
 *         )
 *
 * )
 */
?>
