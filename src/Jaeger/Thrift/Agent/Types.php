<?php

namespace Jaeger\Thrift\Agent;

class Types {

    const TAG_TYPE_STRING = 0;
    const TAG_TYPE_DOUBLE = 1;
	const TAG_TYPE_BOOL = 2;
	const TAG_TYPE_LONG = 3;
	const TAG_TYPE_BINARY = 4;


    public static function stringToTagType($string){
        switch($string){
            case "STRING":
                return self::TAG_TYPE_STRING;
            case "DOUBLE":
                return self::TAG_TYPE_DOUBLE;
            case "BOOL":
                return self::TAG_TYPE_BOOL;
            case "LONG":
                return self::TAG_TYPE_LONG;
            case "BINARY":
                return self::TAG_TYPE_BINARY;
        }
        return "not a valid TagType string";
    }


    public static function tagTypeToString($tagType){
        switch($tagType){
            case self::TAG_TYPE_STRING:
                return "STRING";
            case self::TAG_TYPE_DOUBLE:
                return "DOUBLE";
            case self::TAG_TYPE_BOOL:
                return "BOOL";
            case self::TAG_TYPE_LONG:
                return "LONG";
            case self::TAG_TYPE_BINARY:
                return "BINARY";
        }
        return "UNSET";
    }
}