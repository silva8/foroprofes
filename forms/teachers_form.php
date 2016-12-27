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
 *
*
* @package    local
* @subpackage foroprofes
* @copyright  2016 Cristobal Silva (cristobal.isilvap@gmail.com)
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/
defined("MOODLE_INTERNAL") || die();
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . "/config.php");
require_once("$CFG->libdir/formslib.php");

class foroprofes_teachers_form extends moodleform {
	public function definition(){
		global $DB;
		$sqlteachers = "SELECT DISTINCT u.id, CONCAT (u.firstname, ' ', u.lastname)AS name
					FROM {user} u
					INNER JOIN {role_assignments} ra ON (ra.userid = u.id)
					INNER JOIN {context} ct ON (ct.id = ra.contextid)
					INNER JOIN {role} r ON (r.id = ra.roleid AND r.shortname IN ('teacher', 'editingteacher'))";
		$teachers = $DB->get_records_sql($sqlteachers);
		$arrayteachers = array();
		$arrayteachers["no"] = "Selecciona un profesor";
		foreach ($teachers as $teacher){
			$arrayteachers[$teacher->id] = $teacher->name;
		}
		$mform = $this->_form;
		$mform->addElement("select", "teacher", "Profesores", $arrayteachers);
		$this->add_action_buttons(true, "Enviar");
	}
	public function validation($data, $files) {
		$errors = array();
		$teacher = $data["teacher"];
		if($teacher == "no"){
			$errors["teacher"] = "Profesor invalido";
		}
	
		return $errors;
	}
}