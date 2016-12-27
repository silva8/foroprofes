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
* @package    local
* @subpackage foroprofes
* @copyright  2016	Cristobal Silva (cristobal.isilvap@gmail.com)
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/
require_once(dirname(__FILE__) . "/../../config.php");
require_once ($CFG->dirroot . "/local/foroprofes/forms/newmessage_form.php");
require_once ($CFG->dirroot . "/local/foroprofes/forms/teachers_form.php");
require_once ($CFG->dirroot . "/local/foroprofes/forms/response_form.php");
require_once ($CFG->dirroot . "/local/foroprofes/forms/editresponse_form.php");
global $PAGE, $CFG, $DB, $USER;
require_login();
//Some optional variables
$action = optional_param("action", "selectteacher", PARAM_TEXT);
$responseid = optional_param("responseid", 0 , PARAM_INT);
$messageid = optional_param("messageid", 0 , PARAM_INT);

//Construction of the site in moodle format
$url = new moodle_url("/local/foroprofes/index.php");
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_pagelayout("standard");
$title = "Foroprofes";
$PAGE->set_title($title);
$PAGE->set_heading($title);
$date = new DateTime();
//It is checked if the user is a teacher in at least one course
$isteacher = false;
$sqlisteacher = "SELECT DISTINCT u.id
			FROM {user} u
			INNER JOIN {role_assignments} ra ON (ra.userid = u.id)
			INNER JOIN {context} ct ON (ct.id = ra.contextid)
			INNER JOIN {role} r ON (r.id = ra.roleid AND r.shortname IN ('teacher', 'editingteacher'))
			WHERE u.id = ?";
$teacherquery = $DB->get_records_sql($sqlisteacher, array($USER->id));
if(count($teacherquery)!=0){
	$isteacher = true;
	$teacherid = $USER->id;
	if($action != "responsequestion" && $action != "editresponse" && $action != "deleteresponse")
		$action = "viewmessages";
}
//Only students can see teachers form
if($action == "selectteacher"){
	$mform = new foroprofes_teachers_form();
	if ($mform->is_cancelled()) {
		$gohome = new moodle_url("/my");
		redirect($gohome);
	}
	else if ($data = $mform->get_data()) {
		$teacherid = $data->teacher;
		$action = "viewmessages";
	}
}
//Only teachers can see response form
if($action == "responsequestion"){
	$sqlmessage = "SELECT fr.id as responseid, fm.message as message
				FROM {foroprofes_messages} fm
				INNER JOIN {foroprofes_responses} fr ON (fm.id = fr.idmessage)
				WHERE fm.id = ?";
	$message = $DB->get_record_sql($sqlmessage, array($messageid));
	$mform = new foroprofes_response_form(null, array("responseid" => $message->responseid));
	if ($mform->is_cancelled()) {
		$gotomessages = new moodle_url("/local/foroprofes/index.php");
		redirect($gotomessages);
	}
	else if ($data = $mform->get_data()) {
		$newresponse = new stdClass();
		$newresponse->id = $data->responseid;
		$newresponse->response = $data->response;
		$newresponse->status = 2;	//Status equal two means that the message has been answered
		$newresponse->timecreated = $date->getTimestamp();
		$newresponse->timemodified = $date->getTimestamp();
		$updateresponse = $DB->update_record("foroprofes_responses", $newresponse);
		$action = "viewmessages";
	}
}
//It is cheked if the user who is going to delete a message is the teacher for which the message is
if($action == "deleteresponse"){
	$teacher = $DB->get_record("foroprofes_responses", array("id"=>$responseid));
	if($USER->id == $teacher->idteacher){
		$m = new stdClass();
		$m->id = $responseid;
		$m->status = 3;		//Status equal three means that the message has been deleted
		$deletemessage = $DB->update_record("foroprofes_responses", $m);
	}
	$action = "viewmessages";
}

if($action == "editresponse"){
	$sqlmessage = "SELECT fm.message as message, fr.response as response
				FROM {foroprofes_messages} fm
				INNER JOIN {foroprofes_responses} fr ON (fm.id = fr.idmessage)
				WHERE fr.id = ?";
	$message = $DB->get_record_sql($sqlmessage, array($responseid));
	$mform = new foroprofes_editresponse_form(null, array("responseid" => $responseid, "response"=>$message->response));
	if ($mform->is_cancelled()) {
		$gotomessages = new moodle_url("/local/foroprofes/index.php");
		redirect($gotomessages);
	}
	else if ($data = $mform->get_data()) {
		$responseid = $data->responseid;
		$response = $data->response;
	
		$editresponse = new stdClass();
		$editresponse->id = $responseid;
		$editresponse->response = $response;
		$editresponse->timemodified = $date->getTimestamp();
		$updateresponse = $DB->update_record("foroprofes_responses", $editresponse);
		$action = "viewmessages";
	}
}

if($action == "viewmessages"){
	if(!$isteacher){
		$mform = new foroprofes_newmessage_form(null, array("teacherid" => $teacherid));
		if ($mform->is_cancelled()) {
			$goteachersform = new moodle_url("/local/foroprofes/index.php");
			redirect($goteachersform);
		}
		else if ($data = $mform->get_data()) {
			$teacherid = $data->teacherid;
			$message = $data->message;
			$userid = $USER->id;
			$action = $data->action;
			
			$newmessage = new stdClass();
			$newmessage->idstudent = $userid;
			$newmessage->message = $message;
			$newmessage->timecreated = $date->getTimestamp();
			$newmessage->timemodified = $date->getTimestamp();
			$lastid = $DB->insert_record("foroprofes_messages", $newmessage, true);
			
			$newresponse = new stdClass();
			$newresponse->idmessage = $lastid;
			$newresponse->idteacher = $teacherid;
			$newresponse->status = 1;		//Status equal one means that the message hasn't been answered yet
			$insertresponse = $DB->insert_record("foroprofes_responses", $newresponse, false);
		}
	}
	$sqlmessages = "SELECT fm.id as messageid, fr.id as responseid, fm.message as message, fr.response as response, fr.idteacher as teacherid, fr.status as status, CONCAT (u.firstname,' ', u.lastname)AS name
					FROM {foroprofes_messages} fm, {foroprofes_responses} fr, {user} u
					WHERE fm.id = fr.idmessage AND fr.idteacher = ? AND fm.idstudent = u.id AND status != 3";
	$messages = $DB->get_records_sql($sqlmessages, array($teacherid));
	$messagestable = new html_table();
	if(count($messages)>0){
		$messagestable->head = array("Alumno", "Mensaje","Respuesta");
		foreach($messages as $message){
			//If the user is a teacher and the message is not answered yet
			if($isteacher && empty($message->response)){
				$responseurl = new moodle_url("/local/foroprofes/index.php", array(
						"action"=>"responsequestion",
						"messageid"=> $message->messageid
				));
				$messagestable->data[] = array(
						$message->name,
						$message->message,
						html_writer::nonempty_tag("div", $OUTPUT->single_button($responseurl, "Responder")),
						"",
						""
				);
			}
			//If the user is a teacher and the message has been already answered
			else if($isteacher && !empty($message->response)){
				$editicon = new pix_icon("i/edit", "edit");
				$editresponseurl = new moodle_url("/local/foroprofes/index.php", array(
						"action"=>"editresponse",
						"responseid"=>$message->responseid
				));
				$editresponse = $OUTPUT->action_icon(
						$editresponseurl,
						$editicon
						);
				$deleteicon = new pix_icon("t/delete", "delete");
				$deleteresponseurl = new moodle_url("/local/foroprofes/index.php", array(
						"action"=>"deleteresponse",
						"responseid"=>$message->responseid
				));
				$deleteresponse = $OUTPUT->action_icon(
						$deleteresponseurl,
						$deleteicon,
						new confirm_action("¿Seguro que deseas borrarlo?")
						);
				$messagestable->data[] = array(
						$message->name,
						$message->message,
						$message->response,
						$editresponse,
						$deleteresponse
				);
			}
			//If the user is a student
			else{
				$messagestable->data[] = array(
						$message->name,
						$message->message,
						$message->response
				);
			}
		}
	}
	//If there aren't messages
	else{
		$messagestable->head = array(
				"No hay mensajes que mostrar"
		);
	}
}


echo $OUTPUT->header();


if($action == "selectteacher"){
	$mform->display();
}
if($action == "viewmessages"){
	echo html_writer::table($messagestable);
	if(!$isteacher)
		$mform->display();
}
if($action == "responsequestion"){
	echo $message->message;
	$mform->display();
}
if($action == "editresponse"){
	echo $message->message;
	$mform->display();
}

echo $OUTPUT->footer();

?>


