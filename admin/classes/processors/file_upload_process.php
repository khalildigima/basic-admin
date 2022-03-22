<?php
require_once(__DIR__ . "/base_process.php");

class FileUploadProcess extends BaseProcess
{

    public function files($key)
    {
        // 1. verify

        $response = $this->verify_files($key);
        if (!$response) {
            return $this->response_message("failure",  $_SESSION["failure"]);
        }

        // 2. upload 
        $filenames = $this->upload_files($key);
        if (!$filenames) return $this->response_message("failure", "failed to upload files");
        // 3. save to database

        $arr = $this->response_message("success", "Data saved");
        $arr["files"] = $filenames;
        return $arr;
        //return $this->save_files_to_database($filenames);
    }

    function verify_files($key)
    {
        if (!isset($_FILES[$key])) {
            $_SESSION["failure"] = "You need to have image(s)";
            return false;
        }

        return true;

        $total = count($_FILES[$key]['name']);

        for ($i = 0; $i < $total; $i++) {
            $response = $this->verify_file($key, $i);
            if (!$response) {
                return $response;
            }
        }

        return true;
    }

    function verify_file($key, $index = -1, $extensions = null)
    {

        if ($index < 0) {
            $file_size = $_FILES[$key]['size'];
        } else {
            $file_size = $_FILES[$key]['size'][$index];
        }

        $allowed_file_size = 100 * 1024 * 1024; // 100 MB

        if ($file_size > $allowed_file_size) {
            $_SESSION["failure"] = "File size should be less than 100 MB";
            return false;
        }


        if ($index < 0)
            $ext_arr = explode('.', $_FILES[$key]['name']);
        else
            $ext_arr = explode('.', $_FILES[$key]['name'][$index]);

        $ext_raw = end($ext_arr);
        $file_ext = strtolower($ext_raw);

        if (!$extensions)
            $extensions = ["jpeg", "jpg", "png", "bmp", "gif", "pdf", "doc", "docx", "ppt", "pptx", "xls", "xlst", "txt", "webp", "tiff", "mp4"];

        /**
        if(in_array($file_ext, $extensions) === false)
        {
            $_SESSION["failure"] = "Invalid file. Only extension allowed: JPEG, JPG, PNG, BMP, GIF";
            return false;
        }
		/**/


        if ($file_size < 1) {
            $_SESSION["failure"] = "Invalid file";
            return false;
        }

        return true;
    }

    function upload_files($key)
    {
        if (!isset($_FILES[$key])) {
            $_SESSION["failure"] = "You need to have files";
            return false;
        }

        $total = count($_FILES[$key]['name']);

        $file_names = [];
        for ($i = 0; $i < $total; $i++) {
            $filename = $this->upload_file($key, $i);
            /*
            if(!$filename) 
            {
                $_SESSION["failure"] = "Failed to upload image";
                return false;
            }
			*/
            if ($filename) {
                array_push($file_names, $filename);
            }
        }

        return $file_names;
    }

    function upload_file($key, $index = -1, $target_dir = "")
    {

        if ($target_dir == "")
            $target_dir = __DIR__ . "/../../uploads/files/";

        $time = time();
        if ($index < 0)
            $target_filename = $time . '-' . basename($_FILES[$key]["name"]);
        else
            $target_filename = $time . '-' . basename($_FILES[$key]["name"][$index]);

        $target_file = $target_dir . $target_filename;

        if ($index < 0) {
            if (move_uploaded_file($_FILES[$key]["tmp_name"], $target_file)) {
                return $target_filename;
            }
        } else {
            if (move_uploaded_file($_FILES[$key]["tmp_name"][$index], $target_file)) {
                return $target_filename;
            }
        }

        return null;
    }

    function save_files_to_database($filenames, $user_id)
    {

        // $model = new Documents();
        // foreach($filenames as $file_name)
        // {
        //     $arr = [];
        //     $arr["user_id"] = $user_id;
        //     $arr["file"] = $file_name;
        //     $model->insert($arr);
        // }


        // return $this->response_message("success", "Uploaded images successfully");
    }
}