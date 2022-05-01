<?php

namespace OferExcelParser;

class ParsedRow
{
    const MAX_STRING_LEN = 128;
    const MAX_NAME_LEN = 32;
    const VALID_STATUS = 1;


    private $isValid;
    public $scheme;
    public $row;
    public $raw;
    private $validation = null;
    private $siteId = null;
    private $groupId = null;
    private $range_pairs;
    private $validationFlags;

    const FLAG_SPLIT = 1;


    /*Validation status enums (validation result return codes) */
    public $validationStatuses = [
        "VALIDATION_OK"  => 1,
        "VALIDATION_REQUIRED"  => 2,
        "VALIDATION_ILLEGAL_CHARS"  => 3, 
        "VALIDATION_TOO_LONG"  => 4,
        "VALIDATION_INVALID_LP" => 5,
        "INTEGRITY_ALREADY_EXISTS" => 6,
        "VALIDATION_DUPLICATE_NO_GROUPING_ALLOWED" => 7,
        "VALIDATION_INVALID_PHONE_NUMBER" => 8,
        "VALIDATION_INVALID_DATE_FORMAT" => 9,
        "VALIDATION_DATE_IMPOSSIBLE" => 10,
        "VALIDATION_INVALID_DATE_RANGE" => 11,
        "VALIDATION_INVALID_DATE_START_RANGE" => 12
    ];

    /* User-friendly error messages, should map to $validationStatuses */
    public $validationErrorsHebrew = [
        1 => "תקין",
        2 => "נתון הכרחי",
        3 => "תווים לא חוקיים",
        4 => "נתון ארוך מידי",
        5 => "מספר לוחית לא חוקי",
        6 => "הנתון כבר קיים במערכת",
        7 => "לא ניתן לייבא את אותו התא יותר מפעם אחת",
        8 => "מספר פלאפון לא חוקי",
        9 => "פורמט תאריך לא חוקי (רצוי dd/mm/yyyy)",
        10 => "תאריך לא אפשרי",
        11 => "טווח תאריכים לא חוקי (תאריך סיום צריך להיות אחרי תאריך התחלה)",
        12 => "תאריך ההתחלה לא תקין",
    ];

    /**
     * @return mixed
     */
    public function getRow()
    {
        return $this->row;
    }
    public function getCellBySchemeId($id){
        $s = $this->scheme->getScheme();
        $ids = array_column($s, "id");
        $idx = array_search($id, $ids);
        if($idx !== false){
            return $this->row[$idx];
        }
        return false;
    }
    public function getRaw()
    {
        return $this->raw;
    }

    public function getPlates()
    {
        //iterate scheme and get licenese plate that are not emtpy:
        $idx = 0;
        $scheme = $this->scheme->getScheme();
        $lps = [];
        foreach($scheme as $s){
            if($s['type'] == "LICENSE_PLATE"){
                if(!empty($this->row[$idx])){
                    $lps[] = $this->row[$idx];
                }
            }
            $idx++;
        }
        return $lps;
    }

    public function __construct($data, $scheme = null,$siteId = null, $groupId = null, $splitMode = false){
        $this->validation = array();
        $this->isValid = true;
        $this->scheme = $scheme;
        $this->row = $data;
        $this->raw = $data;
        $this->groupId = $groupId;
        $this->siteId = $siteId;
        $this->validationFlags = array();
        $this->range_pairs = [];

        if($splitMode == true){
            $this->validationFlags[] = self::FLAG_SPLIT;
        }

        $this->runValidation();
        $this->runDataIntegrityCheck();
        if($this->isValid){
            $this->runRangeValidation();
        }
    }

    public function addValidationFlag($flag){
        if(!in_array($flag, $this->validationFlags)){
            $this->validationFlags[] = $flag;
        }
    }

    /*
     * data integrity checks
     * 1. Duplicate data (ie. license plate)
     */

    public function runDataIntegrityCheck(){
        $ii = -1;
        foreach($this->row as $idx => $cell){
            $ii++;

            if($this->validation[$idx] != $this::VALID_STATUS){
                continue;
            }

            $res = $this::VALID_STATUS;

            $integrityFunc = $this->scheme->getScheme()[$idx]["duplication_check_func"];

            if($integrityFunc){
                $val = $cell;
                $res = call_user_func_array([$this, $integrityFunc],[$val, $this->siteId]);
            }


            if($res != $this::VALID_STATUS){
                $this->isValid = false;
            }
            $this->validation[$idx] = $res;
        }
    }

    private function groupDuplicationCheck($val, $siteId){
        $db = \DBController::getInstance();
        $sql = "SELECT tbl_groups.id 
                FROM tbl_groups 
                WHERE tbl_groups.i_site_id = ? AND tbl_groups.st_group_name = ?";

        $stmt = $db->query($sql, [$siteId, $val]);
        $res = $stmt->results(true);

        if($res){
            return $this->validationStatuses["INTEGRITY_ALREADY_EXISTS"];
        }
        return $this->validationStatuses["VALIDATION_OK"];
    }

    private function mediaDuplicationCheck($val, $siteId){
        $db = \DBController::getInstance();
        $sql = "SELECT tbl_media.* 
                FROM tbl_media 
                LEFT JOIN tbl_parking_users ON tbl_media.i_parkuser_id = tbl_parking_users.id
                WHERE tbl_parking_users.i_site_id = ? AND tbl_media.st_media_value = ?";

        $stmt = $db->query($sql, [$siteId, $val]);
        $res = $stmt->results(true);

        if($res){
            return $this->validationStatuses["INTEGRITY_ALREADY_EXISTS"];
        }
        return $this->validationStatuses["VALIDATION_OK"];
    }

    private function parkingUserDuplicationCheck($val, $siteId){
        $db = \DBController::getInstance();

        if(in_array(self::FLAG_SPLIT, $this->validationFlags)){
            $platesCount = count($this->getPlates());
            if($platesCount > 1){
                return $this->splitParkingUserDuplicationCheck($val, $siteId,$platesCount);
            }
        }

        $sql = "SELECT tbl_parking_users.id 
                FROM tbl_parking_users 
                WHERE tbl_parking_users.i_site_id = ? AND tbl_parking_users.i_group_id = ? AND tbl_parking_users.st_parkuser_nickname = ?";

        $stmt = $db->query($sql, [$siteId,$this->groupId, $val]);
        $res = $stmt->results(true);

        if($res){
            return $this->validationStatuses["INTEGRITY_ALREADY_EXISTS"];
        }
        return $this->validationStatuses["VALIDATION_OK"];
    }

    private function splitParkingUserDuplicationCheck($val, $siteId, $count){
        $db = \DBController::getInstance();

        $valsToSearch = [];
        //TODO: put split keyword somewhere global
        for($ii = 0;$ii < $count;$ii++){
            $valsToSearch[] = $val . " רכב " . ($ii+1);
        }

        $sql = "SELECT tbl_parking_users.id 
                FROM tbl_parking_users 
                WHERE tbl_parking_users.i_site_id = ? AND tbl_parking_users.i_group_id = ? AND ( FALSE";

        foreach($valsToSearch as $v){
            $sql .= " OR tbl_parking_users.st_parkuser_nickname = ? ";
        }

        $sql .= ")";

        $params = array_merge([$siteId, $this->groupId], $valsToSearch);
        $stmt = $db->query($sql, $params);
        $res = $stmt->results(true);

        if($res){
            return $this->validationStatuses["INTEGRITY_ALREADY_EXISTS"];
        }
        return $this->validationStatuses["VALIDATION_OK"];
    }




    /* Checks in DB if the supplied value already exists in $table under $column.*/
    /* $filterSiteId tells whether the check should be done only in this site id */
    /* $filterGroupId tells whether the check should be done only in this group id */

    /* deprecated !*/
    public function duplicationCheck($val, $table, $column, $filterSiteId = false, $filterGroupId = false){
        $sql = "SELECT " . $column . " FROM " . $table . " LEFT JOIN `tbl_parking_users` ON `tbl_parking_users`.`id` = `tbl_media`.`i_parkuser_id` WHERE `" . $column . "` = ?";
        $params = [$val];


        if(!is_null($this->group_id) && $filterGroupId){
            $sql .= " AND `i_group_id` = ?";
            $params[] = $this->group_id;
        }

        if(!is_null($this->siteId) && $filterSiteId){
            $sql .= " AND `i_site_id` = ?";
            $params[] = $this->siteId;
        }

        $db = \DBController::getInstance();
        $stmt = $db->query($sql, $params);
        $res = $stmt->results(true);
        if($res){
            return $this->validationStatuses["INTEGRITY_ALREADY_EXISTS"];
        }
        return $this->validationStatuses["VALIDATION_OK"];
    }


    /* Gets validation status for cell at column $idx in this row. */
    public function getCellValidationStatus($idx){
        if(in_array($idx, array_keys($this->validation))){
            return $this->validation[$idx];
        }
        return null;
    }

    /* Returns the hebrew error text based on validation status */
    public function getHebrew($idx){
        if(in_array($idx, array_keys($this->validationErrorsHebrew))){
            return $this->validationErrorsHebrew[$idx];
        }
        return null;
    }

    public function isValidRow(){
        return $this->isValid;
    }

    /* Performs validation on whole row, results are appended to $this->validation. */
    public function runValidation(){
        $ii = -1;
        //todo: check if scheme col count matches row data.

        /* Iterate each cell in this row, validate it and assign validation result to $this->validation. */
        foreach($this->row as $idx => &$cell){
            $ii++;

            /* Get cell properties from scheme */
            $type =             $this->scheme->getScheme()[$idx]["type"];               //Type of validation run on this cell.
            $required =         $this->scheme->getScheme()[$idx]["req"];                //Whether this cell is required, ie. must not be empty
            $allow_grouping =   $this->scheme->getScheme()[$idx]["allow_grouping"];     //Whether duplicate values are allowed for this cell, across this sheet.

            /* Validation based on type */
            switch($type){
                case "NAME":
                    $res = $this->validateName($cell, $required);
                    break;
                case "LICENSE_PLATE":
                    $cell = str_replace("-", "", $cell);
                    $res = $this->validateLicensePlate($cell, $required);
                    break;
                case "PHONE":
                    $cell = str_replace([" ", "-", "(", ")", "."], "", $cell);

                    if(mb_substr($cell, 0, strlen("9725+")) == "9725+"){
                        $cell = "05" . mb_substr($cell, strlen("9725+"));
                    }

                    if(mb_substr($cell, 0, strlen("9725")) == "9725"){
                        $cell = "05" . mb_substr($cell, strlen("9725"));
                    }

                    if(mb_strlen($cell) == 9 && mb_substr($cell, 0 , 1) == "5"){
                        $cell = "0" . $cell;
                    }

                    $res = $this->validatePhoneNumber($cell, $required);
                    break;
                case "DATE_RANGE":
                    $range_name = $this->scheme->getScheme()[$idx]["range_pair"];
                    $range_type = $this->scheme->getScheme()[$idx]["range_type"];
                    $this->range_pairs[$range_name][$range_type] = $idx;
                case "DATE":
                    $res = $this->validateDate($cell, $required);
                    break;
                case "STRING":
                default:
                    $res = $this->validateString($cell, $required);
                    break;
            }

            /* Empty cells in excel are parsed as NULL by PhpOffice, changing cell value to empty string for database insertion */
            if(!$required){
                if(is_null($cell)){
                    $cell = "";
                }
            }

            /* Validation based on duplicate values for this column, across this sheet. */
            if(!$allow_grouping){
                if(!empty($cell)){
                    //Treat cells of type license plate as same column
                    if($type == "LICENSE_PLATE"){
                        $col = "LICENSE_PLATE";
                    }
                    else{
                        $col = $ii;
                    }
                    //Use a basic dict to check whether this cell's value is found in another cell in the same column.
                    $isNewVal = SheetImporter::addGroupingValue($col, $cell);
                    if(!$isNewVal){
                        $res = $this->validationStatuses['VALIDATION_DUPLICATE_NO_GROUPING_ALLOWED'];
                    }
                }
            }

            //Update whole row's validation status.
            if($res != $this::VALID_STATUS){
                $this->isValid = false;
            }

            //Set the resulting validation for this cell.
            $this->validation[$idx] = $res;
        }
    }


    private function runRangeValidation(){
        foreach($this->range_pairs as $range_name => $pair){
            if(isset($pair['start']) && isset($pair['end'])){
                $start = $this->row[$pair['start']];
                $end = $this->row[$pair['end']];

                //$res_start = $this->validateDateRangeStart($start);
                $res_start = $this->validationStatuses["VALIDATION_OK"];

                $this->validation[$pair['start']] = $res_start;
                $res = $this->validateDateRange($start, $end);
                $this->validation[$pair['end']] = $res;

                if($res != $this::VALID_STATUS || $res_start != $this::VALID_STATUS){
                    $this->isValid = false;
                }
            }
        }
    }
    /* Checks whether this cell is empty */
    private function validateRequired($val){
        if(empty($val) || is_null($val)){
            return false;
        }
        return true;
    }

    /* Basic char validation */
    private function basicValidation($val){
        //$pattern = '/[<>\/~`\!@#\$%\^&\*\(\)_\-\+=\{\}\[\]\|;:"\<\>,\.\?\\\]/';
        $pattern = '/[<>\/~`\!@#\$%\^\*_\+=\{\}\[\]\|;:\<\>\?\\\]/';
        if(preg_match($pattern, $val)){
            return false;
        }
        return true;
    }

    /* String validation */
    private function validateString($val,$required){
        if($required){
            if(!$this->validateRequired($val)){
                return $this->validationStatuses["VALIDATION_REQUIRED"];
            }
        }

        if(mb_strlen($val) > ParsedRow::MAX_STRING_LEN){
            return $this->validationStatuses["VALIDATION_TOO_LONG"];
        }

        if(!$this->basicValidation($val)){
            return $this->validationStatuses["VALIDATION_ILLEGAL_CHARS"];
        }
        return $this->validationStatuses["VALIDATION_OK"];
    }

    /* Name validation */
    private function validateName($val,$required){
        if($required){
            if(!$this->validateRequired($val)){
                return $this->validationStatuses["VALIDATION_REQUIRED"];
            }
        }
        if(mb_strlen($val) > ParsedRow::MAX_NAME_LEN){
            return $this->validationStatuses["VALIDATION_TOO_LONG"];
        }

        if(!$this->basicValidation($val)){
            return $this->validationStatuses["VALIDATION_ILLEGAL_CHARS"];
        }
        return $this->validationStatuses["VALIDATION_OK"];
    }

    /* LP validation */
    private function validateLicensePlate($lp, $required){
        $empty = !$this->validateRequired($lp);

        if($required && $empty){
            return $this->validationStatuses["VALIDATION_REQUIRED"];
        }
        elseif(!$required && $empty) {
            return $this->validationStatuses["VALIDATION_OK"];
        }


        if(!is_numeric($lp)){
            return $this->validationStatuses["VALIDATION_INVALID_LP"];
        }

        $len = mb_strlen($lp);
        if($len < 6 || $len > 8){
            return $this->validationStatuses["VALIDATION_INVALID_LP"];
        }

        if(!$this->basicValidation($lp)){
            return $this->validationStatuses["VALIDATION_ILLEGAL_CHARS"];
        }

        return $this->validationStatuses["VALIDATION_OK"];
    }

    /* Phone number validation*/
    /**
     * @param $phone
     * @param $required
     * @return int
     */
    private function validatePhoneNumber($phone, $required): int
    {
        $empty = !$this->validateRequired($phone);

        if($required && $empty){
            return $this->validationStatuses["VALIDATION_REQUIRED"];
        }
        elseif(!$required && $empty) {
            return $this->validationStatuses["VALIDATION_OK"];
        }

        if(!is_numeric($phone)){
            return $this->validationStatuses["VALIDATION_ILLEGAL_CHARS"];
        }

        $len = mb_strlen($phone);
        if($len != 10){
            return $this->validationStatuses["VALIDATION_INVALID_PHONE_NUMBER"];
        }

        if(mb_substr($phone, 0, strlen("05")) != "05"){
            return $this->validationStatuses["VALIDATION_INVALID_PHONE_NUMBER"];
        }

        return $this->validationStatuses["VALIDATION_OK"];
    }
    /*
     * Validates string as dd/mm/yyyy
     */
    /**
     * @param $date - date to validate as dd/mm/yyyy
     * @param $required - is required input field flag.
     * @return int
     */
    private function validateDate($date, $required): int
    {
        $empty = !$this->validateRequired($date);
        if($required && $empty){
            if(!$this->validateRequired($date)){
                return $this->validationStatuses["VALIDATION_REQUIRED"];
            }
        }
        elseif(!$required && $empty) {
            return $this->validationStatuses["VALIDATION_OK"];
        }

        $matches = array();
        $pattern = '/^([0-9]{1,2})\\/([0-9]{1,2})\\/([0-9]{4})$/';
        if (!preg_match($pattern, $date, $matches)) return $this->validationStatuses["VALIDATION_INVALID_DATE_FORMAT"];
        if (!checkdate($matches[2], $matches[1], $matches[3])) return $this->validationStatuses["VALIDATION_DATE_IMPOSSIBLE"];
        return $this->validationStatuses["VALIDATION_OK"];
    }
    private function validateDateRangeStart($dateStart){
        $dateStart = str_replace("/", "-", $dateStart);
        $dateStart = strtotime($dateStart);
        if($dateStart < strtotime(date("Y-m-d 00:00:00"))){
            return $this->validationStatuses["VALIDATION_INVALID_DATE_START_RANGE"];
        }
        return $this->validationStatuses["VALIDATION_OK"];
    }
    private function validateDateRange($dateStart, $dateEnd){

        //convert to time:
        $dateStart = str_replace("/", "-", $dateStart);
        $dateStart = strtotime($dateStart);

        $dateEnd = str_replace("/", "-", $dateEnd);
        $dateEnd = strtotime($dateEnd);

        $d = $dateEnd - $dateStart;

        if($d < 0){
            return $this->validationStatuses["VALIDATION_INVALID_DATE_RANGE"];
        }
        return $this->validationStatuses["VALIDATION_OK"];
    }
}