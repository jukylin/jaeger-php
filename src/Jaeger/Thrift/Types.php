<?php
/*
 * Copyright (c) 2019, The Jaeger Authors
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this file except
 * in compliance with the License. You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under the License
 * is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express
 * or implied. See the License for the specific language governing permissions and limitations under
 * the License.
 */

namespace Jaeger\Thrift;

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