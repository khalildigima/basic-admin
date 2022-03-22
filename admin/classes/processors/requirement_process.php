<?php 
require_once(__DIR__ . "/base_process.php");

class RequirementProcess extends BaseProcess
{
    public function after_meeting($input_files_key) {
        
        $fileUploadProcess = new FileUploadProcess();
        $filenames = $fileUploadProcess->files($input_files_key);
        
        $model = new AfterMeeting();

        $count = 0;
        $files = [];
        if($filenames["status"] == SUCCESS) {
            $files = $filenames["files"];
            $count = count($files);
        }

        $arr = [];
        $arr["user_id"] = $_POST["user_id"];
        $arr["requirement_id"] = $_POST["requirement_id"];
        $arr["remark"] = $_POST["remark"];

        unset($_SESSION[SUCCESS]);
        unset($_SESSION[FAILURE]);
        if($count < 1) {
            if($model->insert($arr)) {
                $_SESSION[SUCCESS] = "Data saved";
                return;
            }
            else {
                $_SESSION[FAILURE] = $model->get_last_error();
                return;
            }
        }

        $error = null;
        for($i = 0; $i < $count; $i++) {
            $arr["file"] = $files[$i];
            if(!$model->insert($arr)) {
                $error = $model->get_last_error();
            }
        }

        if($error) {
            $_SESSION[FAILURE] = $error;
            return;
        }

        $_SESSION[SUCCESS] = "Data saved";
        return;
        
    }

    public function accept_reject_requirment($input_files_key) {
        unset($_SESSION[SUCCESS]);
        unset($_SESSION[FAILURE]);

        if(!isset($_POST["req_reject_accept"])) {
            $_SESSION[FAILURE] = "Please Select Yes/No";
            return;
        }

        if($_POST["req_reject_accept"] != "yes") {
            // requriement accepted
            return $this->accept_requirement();
        }

        // requirement rejected
        $this->reject_requirement($input_files_key);
    }

    private function accept_requirement() {
        $id = $_POST["requirement_id"];
        $model = new Requirements();

        $requirement = $model->get_by_id($id);
        if(!$requirement) {
            $_SESSION[FAILURE] = "Invalid requirement id . " . $model->get_last_error();
            return false;
        }
        
        $arr = [];
        $arr["requirement_accepted"] = 1;
        $arr["requirement_accepted_date"] = date("Y-m-d");
        $arr["req_status_id"] = PROCESS_REQ;

        if($model->update($id, $arr)) {
            $_SESSION[SUCCESS] = "Requirement status updated";

            $model = new RequirementReject();
            $model->delete_all_by("requirement_id", $_POST["requirement_id"]);
            return;
        }

        $_SESSION[FAILURE] = "Failed to accept requirement " . $model->get_last_error();
        
    }

    private function reject_requirement($input_files_key) {
        $id = $_POST["requirement_id"];
        $model = new Requirements();

        $requirement = $model->get_by_id($id);
        if(!$requirement) {
            $_SESSION[FAILURE] = "Invalid requirement id . " . $model->get_last_error();
            return false;
        }
        
        $arr = [];
        $arr["requirement_accepted"] = 0;
        $arr["candidates_interviewed"] = 0;
        $arr["candidates_selected"] = 0;
        $arr["serbian_embassy_notified"] = 0;
        $arr["accomodation_food_confirmed"] = 0;
        $arr["serbian_embassy_processing_application"] = 0;
        $arr["serbian_embassy_follow_up"] = 0;
        $arr["serbian_embassy_approved"] = 0;
        $arr["req_status_id"] = REQ_REJECT;

        if(!$model->update($id, $arr)) {
            $_SESSION[FAILURE] = "Failed to reject requirement " . $model->get_last_error();
            return;
        } 

        $fileUploadProcess = new FileUploadProcess();
        $filenames = $fileUploadProcess->files($input_files_key);
        
        $model = new RequirementReject();

        $count = 0;
        $files = [];
        if($filenames["status"] == SUCCESS) {
            $files = $filenames["files"];
            $count = count($files);
        }

        $arr = [];
        $arr["user_id"] = $_POST["user_id"];
        $arr["requirement_id"] = $_POST["requirement_id"];
        $arr["remark"] = $_POST["remark"];
        
        for($i = 0; $i < $count; $i++) {
            $arr["file"] = $files[$i];
            if(!$model->insert($arr)) {
                $error = $model->get_last_error();
            }
        }

        if($error) {
            $_SESSION[FAILURE] = $error;
            return;
        }

        $_SESSION[SUCCESS] = "Requirement Rejected";
        return;
    }

    public function candidates_interviewed() {
        
        unset($_SESSION[SUCCESS]);
        unset($_SESSION[FAILURE]);

        $id = $_POST["requirement_id"];
        $model = new Requirements();
        $requirement = $model->get_by_id($id);

        if(!$requirement) {
            $_SESSION[FAILURE] = "Invalid requirement id . " . $model->get_last_error();
            return false;
        }
        
        
        // candidates interviewed
        if(intval($_POST["compeleted_candidates_interview"]) === 1) {
            $arr = [];
            $arr["candidates_interviewed"] = 1;
            $arr["candidates_interviewed_date"] = date("Y-m-d");
            $arr["req_status_id"] = CANDIDATE_LIST;

            if($model->update($id, $arr)) {
                $_SESSION[SUCCESS] = "Requirement status updated";
                return;
            }

            
        } else {

            // candidates not interviewed
            $arr = [];
            $arr["candidates_interviewed"] = 0;
            $arr["candidates_selected"] = 0;
            $arr["serbian_embassy_notified"] = 0;
            $arr["accomodation_food_confirmed"] = 0;
            $arr["serbian_embassy_processing_application"] = 0;
            $arr["serbian_embassy_follow_up"] = 0;
            $arr["serbian_embassy_approved"] = 0;
            $arr["req_status_id"] = PROCESS_REQ;

            if($model->update($id, $arr)) {
                $_SESSION[SUCCESS] = "Requirement status updated";
                return;
            }
        }

        $_SESSION[FAILURE] = "Failed to update. " . $model->get_last_error();
        return;
    }

    public function candidates_selected() {
        
        unset($_SESSION[SUCCESS]);
        unset($_SESSION[FAILURE]);

        $id = $_POST["requirement_id"];
        $model = new Requirements();
        $requirement = $model->get_by_id($id);

        if(!$requirement) {
            $_SESSION[FAILURE] = "Invalid requirement id . " . $model->get_last_error();
            return false;
        }
        
        
        // candidates interviewed
        if(intval($_POST["candidates_selected"]) === 1) {
            $arr = [];
            $arr["candidates_selected"] = 1;
            $arr["candidates_selected_date"] = date("Y-m-d");
            $arr["req_status_id"] = CANDIDATE_SELECTED;

            if($model->update($id, $arr)) {
                $_SESSION[SUCCESS] = "Requirement status updated";
                return;
            }

            
        } else {

            // candidates not interviewed
            $arr = [];
            $arr["candidates_selected"] = 0;
            $arr["serbian_embassy_notified"] = 0;
            $arr["accomodation_food_confirmed"] = 0;
            $arr["serbian_embassy_processing_application"] = 0;
            $arr["serbian_embassy_follow_up"] = 0;
            $arr["serbian_embassy_approved"] = 0;
            $arr["req_status_id"] = CANDIDATE_LIST;

            if($model->update($id, $arr)) {
                $_SESSION[SUCCESS] = "Requirement status updated";
                return;
            }
        }

        $_SESSION[FAILURE] = "Failed to update. " . $model->get_last_error();
        return;
    }

    
    public function serbian_embassy_notified() {
        
        unset($_SESSION[SUCCESS]);
        unset($_SESSION[FAILURE]);

        $id = $_POST["requirement_id"];
        $model = new Requirements();
        $requirement = $model->get_by_id($id);

        if(!$requirement) {
            $_SESSION[FAILURE] = "Invalid requirement id . " . $model->get_last_error();
            return false;
        }
        
        
        // candidates interviewed
        if(intval($_POST["serbian_embassy_notified"]) === 1) {
            $arr = [];
            $arr["serbian_embassy_notified"] = 1;
            $arr["serbian_embassy_notified_date"] = date("Y-m-d");
            $arr["req_status_id"] = EMBASSY_NOTIFIED;

            if($model->update($id, $arr)) {
                $_SESSION[SUCCESS] = "Requirement status updated";
                return;
            }

            
        } else {

            // candidates not interviewed
            $arr = [];
            $arr["serbian_embassy_notified"] = 0;
            $arr["accomodation_food_confirmed"] = 0;
            $arr["serbian_embassy_processing_application"] = 0;
            $arr["serbian_embassy_follow_up"] = 0;
            $arr["serbian_embassy_approved"] = 0;
            $arr["req_status_id"] = CANDIDATE_SELECTED;

            if($model->update($id, $arr)) {
                $_SESSION[SUCCESS] = "Requirement status updated";
                return;
            }
        }

        $_SESSION[FAILURE] = "Failed to update. " . $model->get_last_error();
        return;
    }

    public function accomodation_food_confirmed() {
        
        unset($_SESSION[SUCCESS]);
        unset($_SESSION[FAILURE]);

        $id = $_POST["requirement_id"];
        $model = new Requirements();
        $requirement = $model->get_by_id($id);

        if(!$requirement) {
            $_SESSION[FAILURE] = "Invalid requirement id . " . $model->get_last_error();
            return false;
        }
        
        
        // candidates interviewed
        if(intval($_POST["accomodation_food_confirmed"]) === 1) {
            $arr = [];
            $arr["accomodation_food_confirmed"] = 1;
            $arr["accomodation_food_confirmed_date"] = date("Y-m-d");
            $arr["req_status_id"] = ACCOMMODATION;

            if($model->update($id, $arr)) {
                $_SESSION[SUCCESS] = "Requirement status updated";
                return;
            }

            
        } else {

            // candidates not interviewed
            $arr = [];
            $arr["accomodation_food_confirmed"] = 0;
            $arr["serbian_embassy_processing_application"] = 0;
            $arr["serbian_embassy_follow_up"] = 0;
            $arr["serbian_embassy_approved"] = 0;
            $arr["req_status_id"] = EMBASSY_NOTIFIED;

            if($model->update($id, $arr)) {
                $_SESSION[SUCCESS] = "Requirement status updated";
                return;
            }
        }

        $_SESSION[FAILURE] = "Failed to update. " . $model->get_last_error();
        return;
    }

    
    public function serbian_embassy_processing_application() {
        
        unset($_SESSION[SUCCESS]);
        unset($_SESSION[FAILURE]);

        $id = $_POST["requirement_id"];
        $model = new Requirements();
        $requirement = $model->get_by_id($id);

        if(!$requirement) {
            $_SESSION[FAILURE] = "Invalid requirement id . " . $model->get_last_error();
            return false;
        }
        
        
        // candidates interviewed
        if(intval($_POST["serbian_embassy_processing_application"]) === 1) {
            $arr = [];
            $arr["serbian_embassy_processing_application"] = 1;
            $arr["serbian_embassy_processing_application_date"] = date("Y-m-d");
            $arr["req_status_id"] = EMBASSY_PROCESSING;

            if($model->update($id, $arr)) {
                $_SESSION[SUCCESS] = "Requirement status updated";
                return;
            }

            
        } else {

            // candidates not interviewed
            $arr = [];
            $arr["serbian_embassy_processing_application"] = 0;
            $arr["serbian_embassy_follow_up"] = 0;
            $arr["serbian_embassy_approved"] = 0;
            $arr["req_status_id"] = ACCOMMODATION;

            if($model->update($id, $arr)) {
                $_SESSION[SUCCESS] = "Requirement status updated";
                return;
            }
        }

        $_SESSION[FAILURE] = "Failed to update. " . $model->get_last_error();
        return;
    }

    public function serbian_embassy_follow_up() {
        
        unset($_SESSION[SUCCESS]);
        unset($_SESSION[FAILURE]);

        $id = $_POST["requirement_id"];
        $model = new Requirements();
        $requirement = $model->get_by_id($id);

        if(!$requirement) {
            $_SESSION[FAILURE] = "Invalid requirement id . " . $model->get_last_error();
            return false;
        }
        
        
        // candidates interviewed
        if(intval($_POST["serbian_embassy_follow_up"]) === 1) {
            $arr = [];
            $arr["serbian_embassy_follow_up"] = 1;
            $arr["serbian_embassy_follow_up_date"] = date("Y-m-d");
            $arr["req_status_id"] = CLIENT_FOLLOW_UP ;

            if($model->update($id, $arr)) {
                $_SESSION[SUCCESS] = "Requirement status updated";
                return;
            }

            
        } else {

            // candidates not interviewed
            $arr = [];
            $arr["serbian_embassy_follow_up"] = 0;
            $arr["serbian_embassy_approved"] = 0;
            $arr["req_status_id"] = EMBASSY_PROCESSING;

            if($model->update($id, $arr)) {
                $_SESSION[SUCCESS] = "Requirement status updated";
                return;
            }
        }

        $_SESSION[FAILURE] = "Failed to update. " . $model->get_last_error();
        return;
    }

    public function serbian_embassy_approved() {
        
        unset($_SESSION[SUCCESS]);
        unset($_SESSION[FAILURE]);

        $id = $_POST["requirement_id"];
        $model = new Requirements();
        $requirement = $model->get_by_id($id);

        if(!$requirement) {
            $_SESSION[FAILURE] = "Invalid requirement id . " . $model->get_last_error();
            return false;
        }
        
        
        // candidates interviewed
        if(intval($_POST["serbian_embassy_approved"]) === 1) {
            $arr = [];
            $arr["serbian_embassy_approved"] = 1;
            $arr["serbian_embassy_approved_date"] = date("Y-m-d");
            $arr["req_status_id"] = EMBASSY_APPROVED;

            if($model->update($id, $arr)) {
                $_SESSION[SUCCESS] = "Requirement status updated";
                return;
            }

            
        } else {

            // candidates not interviewed
            $arr = [];
            $arr["serbian_embassy_approved"] = 0;
            $arr["req_status_id"] = CLIENT_FOLLOW_UP;

            if($model->update($id, $arr)) {
                $_SESSION[SUCCESS] = "Requirement status updated";
                return;
            }
        }

        $_SESSION[FAILURE] = "Failed to update. " . $model->get_last_error();
        return;
    }

}