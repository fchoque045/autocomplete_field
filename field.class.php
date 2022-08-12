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
 * Manual user enrolment UI.
 *
 * @package    enrol_courseacepted
 * @author     Fabian Choque (Promace Jujuy) 2022
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core_customfield\data;

class profile_field_autocomplete extends profile_field_base {

    /** @var array $options */
    public $options;

    /** @var int $datakey */
    public $datakey;

    /**
     * Constructor method.
     *
     * Pulls out the options for the menu from the database and sets the the corresponding key for the data if it exists.
     *
     * @param int $fieldid
     * @param int $userid
     * @param object $fielddata
     */
    public function __construct($fieldid = 0, $userid = 0, $fielddata = null) {
        // First call parent constructor.
        parent::__construct($fieldid, $userid, $fielddata);
        // Param 1 for menu type is the options.
        if (isset($this->field->param1)) {
            $options = explode("\n", $this->field->param1);
        } else {
            $options = array();
        }
        $this->options = array();
        if (!empty($this->field->required)) {
            $this->options[''] = get_string('choose').'...';
        }
        foreach ($options as $key => $option) {
            // Multilang formatting with filters.
            $this->options[$option] = format_string($option, true, ['context' => context_system::instance()]);
        }
        
        // Set the data key.
        if ($this->data !== null) {
            $key = $this->data;
            if (isset($this->options[$key]) || ($key = array_search($key, $this->options)) !== false) {
                $this->data = $key;
                $this->datakey = $key;
            }
        }
        
    }

    /**
     * Create the code snippet for this field instance
     * Overwrites the base class method
     * @param moodleform $mform Moodle form instance
     */
    public function edit_field_add($mform) {

        $options = array(
            'multiple' => false,
            'noselectionstring' => '',
        );
        $mform->addElement('autocomplete', $this->inputname, format_string($this->field->name), $this->options, $options);
        $mform->setType($this->inputname, PARAM_TEXT);
    }

    /**
     * The data from the form returns the key.
     *
     * This should be converted to the respective option string to be saved in database
     * Overwrites base class accessor method.
     *
     * @param mixed $data The key returned from the select input in the form
     * @param stdClass $datarecord The object that will be used to save the record
     * @return mixed Data or null
     */
    public function edit_save_data_preprocess($data, $datarecord) {
        return isset($this->options[$data]) ? $data : null;
    }
    
}


