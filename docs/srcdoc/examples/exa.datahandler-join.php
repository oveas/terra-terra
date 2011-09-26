<?php
/*
 * This function demonstrates the use of the DataHandler::setJoin() method to use SQL joins.
 * This example uses the following database table (CREATE TABLE statements here are in MySQL format):
 *
 *	CREATE TABLE `exa_sections` (
 *	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
 *	`name` VARCHAR( 12 ) NOT NULL ,
 *	PRIMARY KEY ( `id` )
 *	);
 *
 *	CREATE TABLE `exa_items` (
 *	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
 *	`name` VARCHAR( 12 ) NOT NULL ,
 *	`value` VARCHAR( 12 ) ,
 *	`section_id` INT NOT NULL ,
 *	PRIMARY KEY ( `id` )
 *  CONSTRAINT `fk_section`
 *    FOREIGN KEY (`section_id` )
 *    REFERENCES `exa_sections` (`id` )
 *    ON DELETE RESTRICT
 *	);
 *
 * This implies the configuration should have the table prefix set to 'exa_'
 */
function useSqlJoins($id)
{
	$dataset = new DataHandler();
	$dataset->setTablename('items');

	// Values to read
	$dataset->set(
		  'name'					// Field to select
		, null						// Ignore the value
		, null						// Default table
		, array('name' => array('name'))	// Use an alias ("AS")
		, array('match' => array(DBMATCH_NONE))	// Don't use it in the WHERE clause
	);

	$dataset->set(					// Same for the value
		  'value'
		, null
		, null
		, array('name' => array('value'))
		, array('match' => array(DBMATCH_NONE))
	);

	$dataset->set(
		  'name'					// Select the section name
		, null
		, 'sections'				// This comes from the sections tables
		, array('name' => array('section'))
		, array('match' => array(DBMATCH_NONE))
	);

	// Searches
	$dataset->set('id', $id);		// Item ID to select

	// Joins
	$dataset->setJoin(				// Create an implicit inner join
		  'section_id'				// Match the section_id field
		, array('sections', 'id')	// Join with the id field in the sections table
	);
	$dataset->prepare (); // Default prepare action is DATA_READ
	// The following query is prepared (assuming the MySQL driver and using backticks):
	// SELECT `exa_items`.`name` AS name
	// ,      `exa_items`.`value` AS value
	// ,      `exa_sections`.`name` AS section
	// FROM   `exa_items`
	//,       `exa_sections`
	// WHERE  `exa_items`.`id` = $id
	// AND    ``exa_items`.`section_id` = `exa_sections`.`id`

	$dataset->db ($_list, __LINE__, __FILE__); // Execute the query
	print_r($_list); // Show the results
}
?>