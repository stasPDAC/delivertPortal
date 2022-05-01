<?php
namespace OferExcelParser;

require_once "Utils.php";

use http\Exception;

class SheetImporter
{
    private $parsedRows;
    private $scheme;
    private $isValid;
    private static $groupingDict;
    private $targetGroupId;
    private $targetSiteId;
    private $splitMode;


    const VALID_STATUS = 1;

    private $validationStatus = 1;

    public $sheetValidationStatuses = [
        "SHEET_OK"  => 1,
        "SHEET_BAD_HEADER"  => 2,
        "SHEET_BAD_ROW"  => 3,
        "SHEET_EMPTY"  => 4,
    ];

    public function __construct($scheme, $groupId = 0, $siteId = 0, $splitMode = false){
        $this->parsedRows = array();
        $this->isValid = null;
        $this->scheme = $scheme;
        $this->targetGroupId = $groupId;
        $this->targetSiteId = $siteId;
        $this->splitMode = $splitMode;
        $this::$groupingDict = [];

    }

    public static function addGroupingValue($col, $value){

        if(isset(SheetImporter::$groupingDict[$col][$value])){
            return false;
        }
        SheetImporter::$groupingDict[$col][$value] = $value;
        return true;
    }

    public function parse($sheet){
        $this->isValid = null;
        $parsed = [];

        if(count($sheet) <= 1){
            $this->isValid = false;
            $this->validationStatus = $this->sheetValidationStatuses["SHEET_EMPTY"];
            return false;
        }
        else{
            //Check if header matches scheme header:
            $header = $sheet[0];
            $expectedHeaders = $this->scheme->getScheme();
            $expectedHeaders = array_column($expectedHeaders, "header");


            if(count($header) !== count($expectedHeaders) && array_diff($expectedHeaders, $header)){
                $this->isValid = false;
                $this->validationStatus = $this->sheetValidationStatuses["SHEET_BAD_HEADER"];
                return false;
            }
            unset($sheet[0]);
            foreach($sheet as $row){
                $r = new \OferExcelParser\ParsedRow($row, $this->scheme, $this->targetSiteId, $this->targetGroupId, $this->splitMode);
                $parsed[] = $r;
            }
        }
        $this->parsedRows = $parsed;
        return $this->parsedRows;
    }
    public function getValidationStatus(){
        return $this->validationStatus;
    }
    public function isValidSheet(){
        if(!is_null($this->isValid)){
            return $this->isValid;
        }
        foreach($this->parsedRows as $row){
            $isValidRow = $row->isValidRow();
            if($isValidRow !== true){
                $this->validationStatus = $this->sheetValidationStatuses["SHEET_BAD_ROW"];
                $this->isValid = false;
                return false;
            }
        }
        $this->validationStatus = $this->sheetValidationStatuses["SHEET_OK"];
        $this->isValid = true;
        return true;
    }

    public function getRows(){
        return $this->parsedRows;
    }

    public function insert(){
        if(!$this->isValidSheet()){
            return false;
        }

        switch($this->scheme->getType()){

        }
    }

    private function insertGroups($groups,$siteId, $groupId){
        $db = \DBController::getInstance();

        $insertGroupSql = "INSERT INTO tbl_groups 
                                    (
                                    `i_site_id`, `st_group_name`, `st_group_ref`, `i_group_max_slots`, `st_group_contact`, `bool_imported`,`date_group_created`,`date_period_start`,`date_period_end`, `date_group_updated`
                                    )
                            VALUES
                                   (
                                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                                   )";

        foreach($groups as $groupName => $groupData){
            $groupSlotLimit = $groupData['slotLimit'];
            $parkUsers = $groupData['parkingUsers'];
            $allowedCamList = $groupData['allowedCams'];
            $groupRef = "";
            $groupContact = "";

            //Init group's creation date and active range dates
            $groupCreated = date("Y-m-d H:i:s");
            $groupStart = date("Y-m-d") . " 00:00:00";
            $groupEnd = (date("Y") + 10) . "-" . date("m-d") . " 23:59:59";

            $stmt = $db->query($insertGroupSql, [$siteId, $groupName, $groupRef, $groupSlotLimit,$groupContact, 1, $groupCreated, $groupStart, $groupEnd, $groupCreated]);

            $lastInsertedGroupId = $db->lastId();

            if($lastInsertedGroupId){
                //Insert users in group:
                $this->insertParkUsers($parkUsers, $siteId, $lastInsertedGroupId);

                //Insert allowed cams in group:
                $sql = "INSERT INTO `tbl_group_cameras` (`i_group_id`, `i_camera_id`) values (?, ?)";
                foreach($allowedCamList as $cam){
                    $stmt = $db->query($sql, [$lastInsertedGroupId, $cam]);
                }
            }
            else {
                $error = $db->error();
                $error = $db->errorInfo();
                $errorString = $db->errorString();
                throw new \Exception('Failed to insert group. ' . $errorString . " " . $error);
            }
        }
    }
    private function insertParkUsers($parkUsers,$siteId, $groupId){
        $db = \DBController::getInstance();
        $insertParkUserSQL = "INSERT INTO tbl_parking_users 
                                    (
                                    `i_site_id`, `i_group_id`, `st_parkuser_nickname`, `st_parkuser_username`, `i_reserved_slots`, `bool_imported`, `st_parkuser_comment1`,`st_parkuser_comment2`, `st_slot_number`, `date_period_start`, `date_period_end`
                                    )
                            VALUES
                                   (
                                    ?, ?, ?, ?, ?, ?, ?,?, ?, ?, ?
                                   )";


        $insertLicensePlateSQL = "INSERT INTO tbl_media 
                                    (
                                    `i_parkuser_id`, `i_media_type`, `st_media_name`, `st_media_value`, `i_slot_count`, `bool_imported`
                                    )
                            VALUES
                                   (
                                    ?, ?, ?, ?, ?, ?
                                   )";

        //Iterate park_users, add each one and add their plate(s)
        foreach($parkUsers as $parkUser => $data){
            $parkUser = $data['name'];
            $plates = $data['plates'];
            $phone = $data['phone'];
            $parkingLotNum = $data['parkingLotNum'];
            $comment1 = $data['comment1'];
            $comment2 = $data['comment2'];

            $dateStart = $data['dateStart'];
            $dateEnd = $data['dateEnd'];


            //dd/mm/yyyy to Y-m-d H:i:s
            if(empty($dateStart)){
                $dateStart = null;
            }
            else{
                $dateStart .= " 00:00:00";
                $dateStart = $this->convertDate($dateStart);
            }

            if(empty($dateEnd)){
                $dateEnd = null;
            }
            else{
                $dateEnd .= " 23:59:59";
                $dateEnd = $this->convertDate($dateEnd);
            }

            $stmt = $db->query($insertParkUserSQL, [$siteId, $groupId, $parkUser, $phone, 0, 1,$comment1, $comment2, $parkingLotNum, $dateStart, $dateEnd]);

            $insertedId = $db->lastId();

            if($insertedId){
                foreach($plates as $plate){
                    $stmt = $db->query($insertLicensePlateSQL, [$insertedId, 1, "רכב", $plate, 1, 1]);
                }
            }
            else {
                $error = $db->error();
                $error = $db->errorInfo();
                $errorString = $db->errorString();
                throw new \Exception('Failed to insert park user. ' . $errorString . " " . $error);
            }
        }
    }
    //dd/mm/yyyy to Y-m-d H:i:s
    private function convertDate($date){
        $date = str_replace("/", "-", $date);
        $newDate = strtotime($date);
        return date("Y-m-d H:i:s", $newDate);
    }

    public function insertToSite($allowedCamList = []){
        if(!$this->isValidSheet()){
            return false;
        }
        $groupId = $this->targetGroupId;
        $siteId = $this->targetSiteId;

        if(is_array($allowedCamList)){
            $allowedCamList = array_slice($allowedCamList, 0, 6);
        }
        else{
            $allowedCamList = [];
        }


        //Check if current scheme matches this function - otherwise data would be incompatible.
        if($this->scheme->getType() != Scheme::SCHEME_TYPE_EXISTING_SITE){
            return false;
        }
        //Build list of groups, parking_users under them, and license plate under users.
        $newGroups = [];
        foreach($this->parsedRows as $row){
            $groupName =                $row->getRow()[0];
            $groupSlotLimit =           $row->getRow()[1];

            if(empty($groupSlotLimit) && !$groupSlotLimit && $groupSlotLimit !== '0'){
                $groupSlotLimit = '9999';
            }
            if(!isset($newGroups[$groupName])){
                $newGroups[$groupName]['slotLimit'] = $groupSlotLimit;
                $newGroups[$groupName]['parkingUsers'] = [];
                $newGroups[$groupName]['allowedCams'] = $allowedCamList;
            }

            if($this->splitMode){
                $plates = $row->getPlates();
                if(count($plates) > 1){
                    $parkUserArr = $this->extractParkUserDataFromRow($row);
                    $parkUser =         $parkUserArr['name'];
                    $parkUserArr['plates'] = [];
                    for($ii = 0;$ii < count($plates);$ii++){
                        $splitName = $parkUser . " רכב " . ($ii+1);
                        $parkUserArr['name'] = $splitName;
                        $parkUserArr['plates'][] = $plates[$ii];
                        $newGroups[$groupName]['parkingUsers'][] = $parkUserArr;
                    }
                    continue;
                }
            }
//            $parkUser =         $row->getRow()[2];
//            $phone =            $row->getRow()[3];
//            $parkingLotNum =    $row->getRow()[4];
//            $comment1 =         $row->getRow()[5];
//            $comment2 =         $row->getRow()[6];

            $parkUserArr = $this->extractParkUserDataFromRow($row);

//            $parkUserArr['name'] = $parkUser;
//            $parkUserArr['phone'] = $phone;
//            $parkUserArr['parkingLotNum'] = $parkingLotNum;
//            $parkUserArr['comment1'] = $comment1;
//            $parkUserArr['comment2'] = $comment2;
//            $parkUserArr['plates'] = $row->getPlates();

            $newGroups[$groupName]['parkingUsers'][] = $parkUserArr;
        }
//        if($_SERVER['REMOTE_ADDR'] == "62.219.131.74"){
//            echo "<pre>";
//            var_dump($this->splitMode);
//            var_dump($newGroups);
//            echo "</pre>";
//            exit;
//        }

        try{
            $this->insertGroups($newGroups, $siteId,$groupId);

            //TODO: update MPC attached to site.
            \Utils::setSyncRequired($siteId);
        }
        catch(\Exception $e){
            echo $e;
            exit;
        }
    }

    private function extractParkUserDataFromRow($row){
        $parkUser =         $row->getCellBySchemeId('NAME_1');
        $phone =            $row->getCellBySchemeId('PHONE');
        $parkingLotNum =    $row->getCellBySchemeId('LOT_NUM');
        $comment1 =         $row->getCellBySchemeId('REMARKS_1');
        $comment2 =         $row->getCellBySchemeId('REMARKS_2');
        $dateStart =        $row->getCellBySchemeId('DATE_START');
        $dateEnd =          $row->getCellBySchemeId('DATE_END');

        $parkUserArr = [];
        $parkUserArr['name'] = $parkUser;
        $parkUserArr['phone'] = $phone;
        $parkUserArr['parkingLotNum'] = $parkingLotNum;
        $parkUserArr['comment1'] = $comment1;
        $parkUserArr['comment2'] = $comment2;
        $parkUserArr['plates'] = $row->getPlates();
        $parkUserArr['dateStart'] = $dateStart;
        $parkUserArr['dateEnd'] = $dateEnd;
        return $parkUserArr;
    }

    /* Builds data to be inserted as parkusers in to specified group */
    /* and inserts the data using insertParkUsers() */
    public function insertToGroup(){
        if(!$this->isValidSheet()){
            return false;
        }

        $groupId = $this->targetGroupId;
        $siteId = $this->targetSiteId;

        //Check if current scheme matches this function - otherwise data would be incompatible.
        if($this->scheme->getType() != Scheme::SCHEME_TYPE_EXISTING_GROUP){
            return false;
        }

        //Build list of park_users and license plates under them
        $newParkUsers = [];
        foreach($this->parsedRows as $row){
            $keys = array_keys($newParkUsers);
//            $parkUser =         $row->getRow()[0];
//            $phone =            $row->getRow()[1];
//            $parkingLotNum =    $row->getRow()[2];
//            $comment1 =         $row->getRow()[3];
//            $comment2 =         $row->getRow()[4];
//            $dateStart =        $row->getRow()[4];
//            $dateEnd =          $row->getRow()[4];


//            $parkUser =         $row->getCellBySchemeId('NAME_1');
//            $phone =            $row->getCellBySchemeId('PHONE');
//            $parkingLotNum =    $row->getCellBySchemeId('LOT_NUM');
//            $comment1 =         $row->getCellBySchemeId('REMARKS_1');
//            $comment2 =         $row->getCellBySchemeId('REMARKS_2');
//            $dateStart =        $row->getCellBySchemeId('DATE_START');
//            $dateEnd =          $row->getCellBySchemeId('DATE_END');

//            $newParkUsers[$parkUser]['name'] = $parkUser;
//            $newParkUsers[$parkUser]['phone'] = $phone;
//            $newParkUsers[$parkUser]['parkingLotNum'] = $parkingLotNum;
//            $newParkUsers[$parkUser]['comment1'] = $comment1;
//            $newParkUsers[$parkUser]['comment2'] = $comment2;
//            $newParkUsers[$parkUser]['plates'] = $row->getPlates();
//            $newParkUsers[$parkUser]['dateStart'] = $dateStart;
//            $newParkUsers[$parkUser]['dateEnd'] = $dateEnd;

//            $parkUserArr = [];
//            $parkUserArr['name'] = $parkUser;
//            $parkUserArr['phone'] = $phone;
//            $parkUserArr['parkingLotNum'] = $parkingLotNum;
//            $parkUserArr['comment1'] = $comment1;
//            $parkUserArr['comment2'] = $comment2;
//            $parkUserArr['plates'] = $row->getPlates();
//            $parkUserArr['dateStart'] = $dateStart;
//            $parkUserArr['dateEnd'] = $dateEnd;

            $parkUserArr = $this->extractParkUserDataFromRow($row);
            $newParkUsers[] = $parkUserArr;
        }
        try{
//            if($_SERVER['REMOTE_ADDR'] == "62.219.114.229"){
//                printer($newParkUsers);
//                exit;
//            }
            $this->insertParkUsers($newParkUsers, $siteId,$groupId);
            //TODO: update MPC attached to site.
            \Utils::setSyncRequired($siteId);
        }
        catch(\Exception $e){
            echo $e;
            exit;
        }
    }
}