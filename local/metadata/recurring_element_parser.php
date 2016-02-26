<?php

require_once 'lib.php';


/**
 *
 *  Should be used to save all data generated by a specific repeated element
 *
 *  Assumes that, for any new element, their primaryKey will not have a set value (is null)
 *
 *  NOTE: THE PRIMARY KEY CANNOT BE STORED AS ID IN THE FORM. Instead, should be $tableName.'_id'
 *       EG: coursesession_id
 *       Will be loaded into 'id' for each element
 *
 *  For an example, see session_form::save_data
 *
 */
class recurring_element_parser {
    /**
     * @param string $tableName should be the table 
     * @param string $repeatedHiddenName should be the name given to the repeating group. Stores the number of elements
     * @param array $allChangedAttributes array containing the ids of all elements that the user is able to edit.
     * @param array $convertedAttributes optional dictionary. Is to 
     *
     */
    function __construct($tableName, $repeatedHiddenName, $allChangedAttributes, $convertedAttributes=array()) {
        $this->tableName = $tableName;
        $this->repeatedHiddenName = $repeatedHiddenName;
        $this->allChangedAttributes = $allChangedAttributes;
        $this->primaryKey = $tableName.'_id';

        $this->convertedAttributes = $convertedAttributes;
    }

    /*
     * Will parse the data given to the constructor (from a form), and return tuples for elements from data
     *
     * @param object $data value returned by the related form. Must NOT be null
     *
     * @return array of dictionaries, where each element in the array corresponds to tuple to be added/updated to database
     *    The objects will be pairs of attribute->value, for each of the attributes that could be changed
     *
     */
    function getTuplesFromData($data) {
        $data = get_object_vars($data);
        $numElements = $data[$this->repeatedHiddenName];

        $sessions = array();
        for ($index = 0; $index < $numElements; $index += 1) {
            $session = array();

            foreach ($this->allChangedAttributes as $element) {
                
                // TODO: Refactor out ?
                if (array_key_exists($element, $data)) {
                    // If it is in as just $element, use that
                    $session[$element] = $data[$element][$index];
                    
                } else {
                    // Otherwise, access using $element[$index]
                    $session[$element] = $data[$element.'['.$index.']'];
                }
            }

            $session['id'] = $data[$this->primaryKey][$index];

            foreach($this->convertedAttributes as $element=>$func) {
                if ($session[$element] != null) {
                    $session[$element] = $func($session[$element]);
                }
            }

            $sessions[] = $session;
        }

        return $sessions;
    }


    function saveTuples($tuples) {
        global $DB;
        
        foreach ($tuples as $tuple) {
            // Two different cases for each tuple
            if (is_null($tuple['id'])) {
                unset($tuple['id']);
                $courseIdArray = array('courseid' => get_course_id());
                $inserted = array_merge($tuple, $courseIdArray);
                $DB->insert_record($this->tableName, $inserted, false);
                
            } else {
                // Already exists in the database
                $DB->update_record($this->tableName, $tuple);
                
            }
        }
    }
}



?>