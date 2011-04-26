<?php
/*
 * This function demonstrates the use of the DataHandler::set() method to use SQL functions.
 * This example uses the following database table:
 *
 *	CREATE TABLE `exa_sqlfunc` (
 *	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
 *	`name` VARCHAR( 12 ) NOT NULL ,
 *	`expires` DATE ,
 *	`membership` INT NOT NULL ,
 *	INDEX ( `name` )
 *	)
 *
 * This implies the configuration should have the table prefix set to 'exa_'
 */
function useSqlFunctions($date)
{
	$dataset = new DataHandler();
	$dataset->set_tablename('sqlfunc');
	
	// Set the 'name' field
	$dataset->set(
		  'name'		// Fieldname to get
		, null			// We must read this value, so set it to null
		, null			// Use the default tablename (sqlfunc)
		, array(		// Array with functions for the fielname. All keys MUST have an array as value!
			  'groupby' => array()			// No arguments, so use an empty array
			, 'orderby' => array('desc')	// Ordering; desc here, can be empty (default to the SQL default: asc)
		)
	);

	// Set the 'membership' field
	$dataset->set(
		  'membership'	// Fieldname to get
		, null			// We will read this value, so his will be ignored
		, null			// Use the default tablename (sqlfunc)
		, array(		// Array with functions for the fielname. All keys MUST have an array as value!
			  'function' => array('count')		// Use the COUNT() function (from the db driver); no extra arguments
			, 'having' => array(DBMATCH_GT, 3)	// Make sure we only get results when the count is more than 3
			, 'name' => array('no_mbrships')	// Set the index name for the result array
		)
		, array(		// Array with functions for the value. All keys MUST have an array as value!
			  'match' => array(DBMATCH_NONE) //Don't match on this field, but use it in the SELECT list.
		)
	);

	// Set the 'expires' field; this is the field we make our select on
	$dataset->set(
		  'expires'		// Fieldname to select
		, $date			// Select using this value
		, null			// Use the default tablename (sqlfunc)
		, array(		// Array with functions for the fielname. All keys MUST have an array as value!
			  'function' => array('ifnull', 'NOW()')	// Call the ifnull function with 'NOW()' as the second argument.
						// ** NOTE**, to use an exact dat, this value must be quoted, e.g. "'2011-04-18'" !
		)
		, array(		// Array with functions for the value. All keys MUST have an array as value!
			  'match' => array(DBMATCH_GE) // Match with ">=", default is 'DBMATCH_EQ ('=')
		)
	);

	$dataset->prepare (); // Default prepare action is DATA_READ
	// The following query is prepared:
	// SELECT `exa_sqlfunc`.`name`, COUNT(`exa_sqlfunc`.`membership`) AS no_mbrships
	// FROM `exa_sqlfunc`
	// WHERE IFNULL(`exa_sqlfunc`.`expires`, NOW()) >= '(given date)'
	// GROUP BY `exa_sqlfunc`.`name`
	// HAVING COUNT(`exa_sqlfunc`.`membership`) > 3
	// ORDER BY `exa_sqlfunc`.`name` DESC

	$dataset->db ($_list, __LINE__, __FILE__); // Execute the query
	print_r($_list); // Show the results
}
?>