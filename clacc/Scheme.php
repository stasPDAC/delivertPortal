<?php

namespace OferExcelParser;

class Scheme
{
    private $scheme;
    private $type;
    private $required;

    const SCHEME_TYPE_DEFAULT = 0;
    const SCHEME_TYPE_EXISTING_GROUP = 1;
    const SCHEME_TYPE_EXISTING_SITE = 2;

    const SCHEME_DEFAULT = [
        ["id" => "NAME_1", "allow_grouping" => false, "type" =>'NAME',            "req" => true,  "header" => "שם הדייר",     "duplication_check_func" => "parkingUserDuplicationCheck"],
        ["id" => "PHONE", "allow_grouping" => true,  "type" =>'PHONE',           "req" => false, "header" => "טלפון",        "duplication_check_func" => ""],
        ["id" => "LOT_NUM", "allow_grouping" => true,  "type" =>'STRING',          "req" => false, "header" => "מספר חניה",    "duplication_check_func" => ""],
        ["id" => "REMARKS_1", "allow_grouping" => true,  "type" =>'STRING',          "req" => false, "header" => "הערות 1",      "duplication_check_func" => ""],
        ["id" => "REMARKS_2", "allow_grouping" => true,  "type" =>'STRING',          "req" => false, "header" => "הערות 2",      "duplication_check_func" => ""],
        ["id" => "LP_1", "allow_grouping" => false, "type" =>'LICENSE_PLATE',   "req" => true,  "header" => "לוחית 1",      "duplication_check_func" => "mediaDuplicationCheck"],
        ["id" => "LP_2", "allow_grouping" => false, "type" =>'LICENSE_PLATE',   "req" => false, "header" => "לוחית 2",      "duplication_check_func" => "mediaDuplicationCheck"],
        ["id" => "LP_3", "allow_grouping" => false, "type" =>'LICENSE_PLATE',   "req" => false, "header" => "לוחית 3",      "duplication_check_func" => "mediaDuplicationCheck"],
        ["id" => "LP_4", "allow_grouping" => false, "type" =>'LICENSE_PLATE',   "req" => false, "header" => "לוחית 4",      "duplication_check_func" => "mediaDuplicationCheck"],
    ];

    const SCHEME_EXISTING_GROUP = [
        ["id" => "NAME_1", "allow_grouping" => true, "type" =>'NAME',              "req" => true,  "header" => "שם הדייר",     "duplication_check_func" => ""],
        ["id" => "PHONE", "allow_grouping" => true,  "type" =>'PHONE',              "req" => false, "header" => "טלפון",        "duplication_check_func" => ""],
        ["id" => "LOT_NUM", "allow_grouping" => true,  "type" =>'STRING',           "req" => false, "header" => "מספר חניה",    "duplication_check_func" => ""],
        ["id" => "DATE_START", "allow_grouping" => true,  "type" =>'DATE_RANGE',    "range_pair" => "pair_1", "range_type" => "start",            "req" => false, "header" => "תאריך תחילת מנוי",    "duplication_check_func" => ""],
        ["id" => "DATE_END", "allow_grouping" => true,  "type" =>'DATE_RANGE',      "range_pair" => "pair_1", "range_type" => "end",           "req" => false, "header" => "תאריך סיום מנוי",    "duplication_check_func" => ""],
        ["id" => "REMARKS_1", "allow_grouping" => true,  "type" =>'STRING',          "req" => false, "header" => "הערות 1",      "duplication_check_func" => ""],
        ["id" => "REMARKS_2", "allow_grouping" => true,  "type" =>'STRING',          "req" => false, "header" => "הערות 2",      "duplication_check_func" => ""],
        ["id" => "LP_1", "allow_grouping" => false, "type" =>'LICENSE_PLATE',       "req" => true,  "header" => "לוחית 1",      "duplication_check_func" => "mediaDuplicationCheck"],
        ["id" => "LP_2", "allow_grouping" => false, "type" =>'LICENSE_PLATE',       "req" => false, "header" => "לוחית 2",      "duplication_check_func" => "mediaDuplicationCheck"],
        ["id" => "LP_3", "allow_grouping" => false, "type" =>'LICENSE_PLATE',       "req" => false, "header" => "לוחית 3",      "duplication_check_func" => "mediaDuplicationCheck"],
        ["id" => "LP_4", "allow_grouping" => false, "type" =>'LICENSE_PLATE',       "req" => false, "header" => "לוחית 4",      "duplication_check_func" => "mediaDuplicationCheck"],
    ];


    const SCHEME_EXISTING_SITE = [
        ["id" => "GROUP_NAME", "allow_grouping" => true,  "type" =>'NAME',             "req" => true,  "header" => "שם החברה",     "duplication_check_func" => "groupDuplicationCheck"],
        ["id" => "GROUP_MAX_SLOTS", "allow_grouping" => true,  "type" =>'STRING',          "req" => false, "header" => "מגבלת כמות חניות",    "duplication_check_func" => ""],
        ["id" => "NAME_1", "allow_grouping" => true, "type" =>'NAME',            "req" => true,  "header" => "שם הדייר",     "duplication_check_func" => ""],
        ["id" => "PHONE", "allow_grouping" => true,  "type" =>'PHONE',           "req" => false, "header" => "טלפון",        "duplication_check_func" => ""],
        ["id" => "LOT_NUM", "allow_grouping" => true,  "type" =>'STRING',          "req" => false, "header" => "מספר חניה",    "duplication_check_func" => ""],
        ["id" => "REMARKS_1", "allow_grouping" => true,  "type" =>'STRING',          "req" => false, "header" => "הערות 1",      "duplication_check_func" => ""],
        ["id" => "REMARKS_2", "allow_grouping" => true,  "type" =>'STRING',          "req" => false, "header" => "הערות 2",      "duplication_check_func" => ""],
        ["id" => "LP_1", "allow_grouping" => false, "type" =>'LICENSE_PLATE',   "req" => true,  "header" => "לוחית 1",      "duplication_check_func" => "mediaDuplicationCheck"],
        ["id" => "LP_2", "allow_grouping" => false, "type" =>'LICENSE_PLATE',   "req" => false, "header" => "לוחית 2",      "duplication_check_func" => "mediaDuplicationCheck"],
        ["id" => "LP_3", "allow_grouping" => false, "type" =>'LICENSE_PLATE',   "req" => false, "header" => "לוחית 3",      "duplication_check_func" => "mediaDuplicationCheck"],
        ["id" => "LP_4", "allow_grouping" => false, "type" =>'LICENSE_PLATE',   "req" => false, "header" => "לוחית 4",      "duplication_check_func" => "mediaDuplicationCheck"],
    ];


//duplication_check_func
    const SCHEME_SWITCH = [
        Scheme::SCHEME_TYPE_DEFAULT => Scheme::SCHEME_DEFAULT,
        Scheme::SCHEME_TYPE_EXISTING_GROUP => Scheme::SCHEME_EXISTING_GROUP,
        Scheme::SCHEME_TYPE_EXISTING_SITE => Scheme::SCHEME_EXISTING_SITE,
    ];

    public function getType(){
        return $this->type;
    }

    public function getScheme(){
        return $this->scheme;
    }
    public function __construct($type, $headers = []){
        if( in_array($type, array_keys(Scheme::SCHEME_SWITCH))){
            $this->scheme = Scheme::SCHEME_SWITCH[$type];
            $this->type = $type;

            if(is_array($headers) && count($headers) === count($this->scheme)){
                $idx = 0;
                foreach($this->scheme as &$s){
                    $s["header"] = $headers[$idx++];
                }
            }
        }
    }
}