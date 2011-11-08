<?php
/*
 * This function demonstrates the use of the HDataHandler class
 * This example uses the following database table (CREATE TABLE statements here is in MySQL format):
 *
 * CREATE TABLE `exa_instruments` (
 * `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
 * ,`lval` INT NOT NULL
 * ,`rval` INT NOT NULL
 * ,`node` VARCHAR(45) NOT NULL
 * ,`xlink` INT UNSIGNED
 * ,PRIMARY KEY (`id`)
 * ,KEY `lval` (`lval`)
 * ,KEY `rval` (`rval`)
 * ,UNIQUE KEY `node` (`node`)
 * ,KEY `xlink` (`xlink`)
 * )
 *
 * This implies the configuration should have the table prefix set to 'exa_'
 *
 * When the table is completely filled, it will contain the following (unordered) hierarchical
 * structure:
 *
 *    Musical instruments
 *    |
 *    +-> Keyboards
 *    |   |
 *    |   +->Synthesizer
 *    |   |
 *    |   + (*)
 *    |
 *    +-> Strings
 *    |   |
 *    |   +-> Piano (*)
 *    |   |
 *    |   +-> Plucked
 *    |   |   |
 *    |   |   +-> Guitar
 *    |   |   |
 *    |   |   +-> Chapman stick
 *    |   |
 *    |   +-> Bowed
 *    |       |
 *    |       +-> Violin
 *    |       |
 *    |       +-> Cello
 *    |
 *    +-> Percussion
 *    |   |
 *    |   +-> Cymbal
 *    |   |
 *    |   +-> Tuned
 *    |       |
 *    |       +-> Timpani
 *    |
 *    +-> Wind
 *        |
 *        +-> Brass
 *        |   |
 *        |   +-> Horn
 *        |   |
 *        |   +-> Trumpet
 *        |
 *        +-> Wood
 *            |
 *            +-> Flute
 *            |
 *            +-> Single reed
 *            |   |
 *            |   +-> Clarinet
 *            |   |
 *            |   +-> Saxophone
 *            |
 *            +-> Double reed
 *                |
 *                +-> Oboe
 *                |
 *                +-> Bassoon
 */
function useHierarchicalData($date)
{
	$hData = new HDataHandler();
	$hData->setTablename('instruments');
	$hData->setLeft('lval');
	$hData->setRight('rval');

	// Insert the toplevel node
	$hData->insertNode(array('node' => 'Musical instruments'), array('field' => 'node'));

	// Create some categories
	$hData->insertNode(array('node' => 'Wind'), array('field' => 'node', 'value' => 'Musical instruments'));
	$hData->insertNode(array('node' => 'Strings'), array('field' => 'node', 'value' => 'Musical instruments'));
	$hData->insertNode(array('node' => 'Percussion'), array('field' => 'node', 'value' => 'Musical instruments'));

	// And subcategories
	$hData->insertNode(array('node' => 'Wood'), array('field' => 'node', 'value' => 'Wind'));
	$hData->insertNode(array('node' => 'Brass'), array('field' => 'node', 'value' => 'Wind'));

	$hData->insertNode(array('node' => 'Plucked'), array('field' => 'node', 'value' => 'Strings'));
	$hData->insertNode(array('node' => 'Bowed'), array('field' => 'node', 'value' => 'Strings'));

	// Insert some instruments per category
	$hData->insertNode(array('node' => 'Clarinet'), array('field' => 'node', 'value' => 'Wood'));
	$hData->insertNode(array('node' => 'Saxophone'), array('field' => 'node', 'value' => 'Wood'));
	$hData->insertNode(array('node' => 'Oboe'), array('field' => 'node', 'value' => 'Wood'));
	$hData->insertNode(array('node' => 'Bassoon'), array('field' => 'node', 'value' => 'Wood'));
	$hData->insertNode(array('node' => 'Flute'), array('field' => 'node', 'value' => 'Wood'));

	$hData->insertNode(array('node' => 'Trumpet'), array('field' => 'node', 'value' => 'Brass'));
	$hData->insertNode(array('node' => 'Horn'), array('field' => 'node', 'value' => 'Brass'));

	$hData->insertNode(array('node' => 'Violin'), array('field' => 'node', 'value' => 'Bowed'));
	$hData->insertNode(array('node' => 'Cello'), array('field' => 'node', 'value' => 'Bowed'));

	$hData->insertNode(array('node' => 'Guitar'), array('field' => 'node', 'value' => 'Plucked'));
	$hData->insertNode(array('node' => 'Chapman stick'), array('field' => 'node', 'value' => 'Plucked'));

	$hData->insertNode(array('node' => 'Piano'), array('field' => 'node', 'value' => 'Strings'));

	$hData->insertNode(array('node' => 'Cymbal'), array('field' => 'node', 'value' => 'Percussion'));

	// New subcategory and a child
	$hData->insertNode(array('node' => 'Tuned'), array('field' => 'node', 'value' => 'Percussion'));
	$hData->insertNode(array('node' => 'Timpani'), array('field' => 'node', 'value' => 'Tuned'));

	// Now create new subcategories under Wind - Wood and move some of the nodes in their new category
	$hData->insertNode(array('node' => 'Single reed'), array('field' => 'node', 'value' => 'Wood'));
	$hData->insertNode(array('node' => 'Double reed'), array('field' => 'node', 'value' => 'Wood'));

	$hData->moveNode('node', 'Clarinet', array('field' => 'node', 'value' => 'Single reed'));
	$hData->moveNode('node', 'Saxophone', array('field' => 'node', 'value' => 'Single reed'));
	$hData->moveNode('node', 'Oboe', array('field' => 'node', 'value' => 'Double reed'));
	$hData->moveNode('node', 'Bassoon', array('field' => 'node', 'value' => 'Double reed'));

	// We'll make a new category now (keyboards) and add Synthesizer as child.
	// Nex we're gonna enable crosslinks and set Keyboards as a second parent for Piano
	$hData->insertNode(array('node' => 'Keyboards'), array('field' => 'node', 'value' => 'Musical instruments'));
	$hData->insertNode(array('node' => 'Synthesizer'), array('field' => 'node', 'value' => 'Keyboards'));
	$hData->setPrimaryKey('id');		// Define the primary key on which the crosslink will be created
	$hData->enableCrossLink('xlink');	// Enable crosslinks using the 'xlink' table field
	$hData->addParent(
			 array('field' => 'node', 'value' => 'Piano')
			,array('field' => 'node', 'value' => 'Keyboards')
	);

	// At this point we have the structure that's given in the header of this file.

	$_keyboards = $hData->getDirectChildren('node', 'Keyboards');
	$_instruments = $hData->getFullOffspring ('node', 'Musical instruments', HDATA_XLINK_FOLLOW_UNLIMITED);

	// See at the end of this file for the output genererated by the following statements:
	print_r($_keyboards);
	print_r($_instruments);

	// Remove all wind and string instruments including subcategories
	$hData->removeTree('node', 'Wind');
	$hData->removeTree('node', 'String');
	$_clearedList = $hData->getFullOffspring ('node', 'Musical instruments', HDATA_XLINK_FOLLOW_UNLIMITED);
	print_r($_clearedList);


	/* The $_keyboards array now holds the Piano and the Synthesizer, while the Piano will also
	 * be found in the Strings category:
	 * Array
	 * (
	 *     [0] => Array
	 *         (
	 *             [id] => 20
	 *             [lval] => 23
	 *             [rval] => 24
	 *             [node] => Piano
	 *             [xlink] => 26
	 *             [depth] => 1
	 *         )
	 *
	 *     [1] => Array
	 *         (
	 *             [id] => 27
	 *             [lval] => 45
	 *             [rval] => 46
	 *             [node] => Synthesizer
	 *             [xlink] =>
	 *             [depth] => 1
	 *         )
	 *
	 * )
	 *
	 * This is how the $_instruments array looks like:
	 *  Array
	 *  (
	 *      [0] => Array
	 *          (
	 *              [id] => 1
	 *              [lval] => 1
	 *              [rval] => 80
	 *              [node] => Musical instruments
	 *              [xlink] =>
	 *          )
	 *
	 *      [1] => Array
	 *          (
	 *              [id] => 3
	 *              [lval] => 2
	 *              [rval] => 31
	 *              [node] => Strings
	 *              [xlink] =>
	 *          )
	 *
	 *      [2] => Array
	 *          (
	 *              [id] => 4
	 *              [lval] => 32
	 *              [rval] => 43
	 *              [node] => Percussion
	 *              [xlink] =>
	 *          )
	 *
	 *      [3] => Array
	 *          (
	 *              [id] => 34
	 *              [lval] => 63
	 *              [rval] => 64
	 *              [node] => Bassoon
	 *              [xlink] =>
	 *          )
	 *
	 *      [4] => Array
	 *          (
	 *              [id] => 33
	 *              [lval] => 61
	 *              [rval] => 62
	 *              [node] => Oboe
	 *              [xlink] =>
	 *          )
	 *
	 *      [5] => Array
	 *          (
	 *              [id] => 7
	 *              [lval] => 3
	 *              [rval] => 12
	 *              [node] => Plucked
	 *              [xlink] =>
	 *          )
	 *
	 *      [6] => Array
	 *          (
	 *              [id] => 8
	 *              [lval] => 13
	 *              [rval] => 22
	 *              [node] => Bowed
	 *              [xlink] =>
	 *          )
	 *
	 *      [7] => Array
	 *          (
	 *              [id] => 32
	 *              [lval] => 57
	 *              [rval] => 58
	 *              [node] => Saxophone
	 *              [xlink] =>
	 *          )
	 *
	 *      [8] => Array
	 *          (
	 *              [id] => 31
	 *              [lval] => 55
	 *              [rval] => 56
	 *              [node] => Clarinet
	 *              [xlink] =>
	 *          )
	 *
	 *      [9] => Array
	 *          (
	 *              [id] => 30
	 *              [lval] => 67
	 *              [rval] => 72
	 *              [node] => Brass
	 *              [xlink] =>
	 *          )
	 *
	 *      [10] => Array
	 *          (
	 *              [id] => 29
	 *              [lval] => 51
	 *              [rval] => 66
	 *              [node] => Wood
	 *              [xlink] =>
	 *          )
	 *
	 *      [11] => Array
	 *          (
	 *              [id] => 28
	 *              [lval] => 50
	 *              [rval] => 73
	 *              [node] => Wind
	 *              [xlink] =>
	 *          )
	 *
	 *      [12] => Array
	 *          (
	 *              [id] => 16
	 *              [lval] => 14
	 *              [rval] => 15
	 *              [node] => Violin
	 *              [xlink] =>
	 *          )
	 *
	 *      [13] => Array
	 *          (
	 *              [id] => 17
	 *              [lval] => 16
	 *              [rval] => 17
	 *              [node] => Cello
	 *              [xlink] =>
	 *          )
	 *
	 *      [14] => Array
	 *          (
	 *              [id] => 18
	 *              [lval] => 4
	 *              [rval] => 5
	 *              [node] => Guitar
	 *              [xlink] =>
	 *          )
	 *
	 *      [15] => Array
	 *          (
	 *              [id] => 19
	 *              [lval] => 6
	 *              [rval] => 7
	 *              [node] => Chapman stick
	 *              [xlink] =>
	 *          )
	 *
	 *      [16] => Array
	 *          (
	 *              [id] => 20
	 *              [lval] => 23
	 *              [rval] => 24
	 *              [node] => Piano
	 *              [xlink] => 26
	 *          )
	 *
	 *      [17] => Array
	 *          (
	 *              [id] => 21
	 *              [lval] => 33
	 *              [rval] => 34
	 *              [node] => Cymbal
	 *              [xlink] =>
	 *          )
	 *
	 *      [18] => Array
	 *          (
	 *              [id] => 35
	 *              [lval] => 52
	 *              [rval] => 53
	 *              [node] => Flute
	 *              [xlink] =>
	 *          )
	 *
	 *      [19] => Array
	 *          (
	 *              [id] => 23
	 *              [lval] => 35
	 *              [rval] => 36
	 *              [node] => Timpani
	 *              [xlink] =>
	 *          )
	 *
	 *      [20] => Array
	 *          (
	 *              [id] => 27
	 *              [lval] => 45
	 *              [rval] => 46
	 *              [node] => Synthesizer
	 *              [xlink] =>
	 *          )
	 *
	 *      [21] => Array
	 *          (
	 *              [id] => 26
	 *              [lval] => 44
	 *              [rval] => 49
	 *              [node] => Keyboards
	 *              [xlink] =>
	 *          )
	 *
	 *      [22] => Array
	 *          (
	 *              [id] => 36
	 *              [lval] => 68
	 *              [rval] => 69
	 *              [node] => Trumpet
	 *              [xlink] =>
	 *          )
	 *
	 *      [23] => Array
	 *          (
	 *              [id] => 37
	 *              [lval] => 70
	 *              [rval] => 71
	 *              [node] => Horn
	 *              [xlink] =>
	 *          )
	 *
	 *      [24] => Array
	 *          (
	 *              [id] => 38
	 *              [lval] => 37
	 *              [rval] => 40
	 *              [node] => Tuned
	 *              [xlink] =>
	 *          )
	 *
	 *      [25] => Array
	 *          (
	 *              [id] => 39
	 *              [lval] => 54
	 *              [rval] => 59
	 *              [node] => Single reed
	 *              [xlink] =>
	 *          )
	 *
	 *      [26] => Array
	 *          (
	 *              [id] => 40
	 *              [lval] => 60
	 *              [rval] => 65
	 *              [node] => Double reed
	 *              [xlink] =>
	 *          )
	 *
	 *      [27] => Array
	 *          (
	 *              [id] => 20
	 *              [lval] => 23
	 *              [rval] => 24
	 *              [node] => Piano
	 *              [xlink] => 26
	 *          )
	 *  )
	 *
	 *  The $_clearedList contrains:
	 *  Array
	 *  (
	 *      [0] => Array
	 *          (
	 *              [id] => 1
	 *              [lval] => 1
	 *              [rval] => 26
	 *              [node] => Musical instruments
	 *              [xlink] =>
	 *          )
	 *
	 *      [1] => Array
	 *          (
	 *              [id] => 4
	 *              [lval] => 2
	 *              [rval] => 13
	 *              [node] => Percussion
	 *              [xlink] =>
	 *          )
	 *
	 *      [2] => Array
	 *          (
	 *              [id] => 21
	 *              [lval] => 3
	 *              [rval] => 4
	 *              [node] => Cymbal
	 *              [xlink] =>
	 *          )
	 *
	 *      [3] => Array
	 *          (
	 *              [id] => 23
	 *              [lval] => 5
	 *              [rval] => 6
	 *              [node] => Timpani
	 *              [xlink] =>
	 *          )
	 *
	 *      [4] => Array
	 *          (
	 *              [id] => 27
	 *              [lval] => 15
	 *              [rval] => 16
	 *              [node] => Synthesizer
	 *              [xlink] =>
	 *          )
	 *
	 *      [5] => Array
	 *          (
	 *              [id] => 26
	 *              [lval] => 14
	 *              [rval] => 19
	 *              [node] => Keyboards
	 *              [xlink] =>
	 *          )
	 *
	 *      [6] => Array
	 *          (
	 *              [id] => 38
	 *              [lval] => 7
	 *              [rval] => 10
	 *              [node] => Tuned
	 *              [xlink] =>
	 *          )
	 *  )
	 */
}
?>