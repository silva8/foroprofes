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
defined('MOODLE_INTERNAL') || die();
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once("$CFG->libdir/formslib.php");

class foroprofes_newmessage_form extends moodleform {
	public function definition() {
		global $DB, $CFG;

		$mform = $this->_form;
		$instance = $this->_customdata;
		$teacherid = $instance["teacherid"];

		$mform->addElement("text", "message", "Nuevo mensaje"); 		
		$mform->addElement("hidden", "teacherid", $teacherid);
		$mform->setType("teacherid", PARAM_INT);
		$mform->addElement("hidden", "action", "viewmessages");
		
		$this->add_action_buttons(true, "Enviar");
		
	}
	
	public function validation($data, $files) {
		$errors = array();
		$message = $data["message"];
		if(!isset($message) || empty($message)){
			$errors["message"] = "Mensaje invalido";
		}
		
		return $errors;
	}
}