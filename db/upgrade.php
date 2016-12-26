<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 * This file keeps track of upgrades to the evaluaciones block
*
* Sometimes, changes between versions involve alterations to database structures
* and other major things that may break installations.
*
* The upgrade function in this file will attempt to perform all the necessary
* actions to upgrade your older installation to the current version.
*
* If there's something it cannot do itself, it will tell you what you need to do.
*
* The commands in here will all be database-neutral, using the methods of
* database_manager class
*
* Please do not forget to use upgrade_set_timeout()
* before any action that may take longer time to finish.
*
* @since 2.0
* @package blocks
* @copyright 2016 Matías Queirolo
* @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/
/**
 *
* @param int $oldversion
* @param object $block
*/
function xmldb_local_foroprofes_upgrade($oldversion) {
	
	global $CFG, $DB;
	$dbman = $DB->get_manager();
	
	if ($oldversion < 2016121902) {
	
		// Define table foroprofes_messages to be created.
		$table = new xmldb_table('foroprofes_messages');
	
		// Adding fields to table foroprofes_messages.
		$table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
		$table->add_field('idstudent', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
		$table->add_field('message', XMLDB_TYPE_CHAR, '1000', null, XMLDB_NOTNULL, null, null);
		$table->add_field('timecreated', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
		$table->add_field('timemodified', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
	
		// Adding keys to table foroprofes_messages.
		$table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
		$table->add_key('idstudent', XMLDB_KEY_FOREIGN, array('idstudent'), 'mdl_user', array('id'));
	
		// Conditionally launch create table for foroprofes_messages.
		if (!$dbman->table_exists($table)) {
			$dbman->create_table($table);
		}
	
		// Foroprofes savepoint reached.
		upgrade_plugin_savepoint(true, 2016121902, 'local', 'foroprofes');
	}
	
	if ($oldversion < 2016121903) {
	
		// Define table foroprofes_responses to be created.
		$table = new xmldb_table('foroprofes_responses');
	
		// Adding fields to table foroprofes_responses.
		$table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
		$table->add_field('idmessage', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
		$table->add_field('idteacher', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
		$table->add_field('timecreated', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
		$table->add_field('timemodified', XMLDB_TYPE_INTEGER, '20', null, null, null, null);
		$table->add_field('status', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
		$table->add_field('response', XMLDB_TYPE_CHAR, '1000', null, null, null, null);
	
		// Adding keys to table foroprofes_responses.
		$table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
		$table->add_key('message', XMLDB_KEY_FOREIGN_UNIQUE, array('idmessage'), 'mdl_foroprofes_messages', array('id'));
		$table->add_key('teacher', XMLDB_KEY_FOREIGN, array('idteacher'), 'mdl_user', array('id'));
	
		// Conditionally launch create table for foroprofes_responses.
		if (!$dbman->table_exists($table)) {
			$dbman->create_table($table);
		}
	
		// Foroprofes savepoint reached.
		upgrade_plugin_savepoint(true, 2016121903, 'local', 'foroprofes');
	}

	return true;
}