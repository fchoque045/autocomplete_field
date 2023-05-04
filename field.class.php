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
        
        /// Set the data key
        if ($this->data !== null) {
            $this->data = str_replace("\r", '', $this->data);
            $this->datatmp = explode("\n", $this->data);
            foreach ($this->datatmp as $key => $value) {
                $this->datakey[] = array_search($value, $this->options);
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
            'multiple' => true,
            'noselectionstring' => '',
        );
        $mform->addElement('autocomplete', $this->inputname, format_string($this->field->name), $this->options, $options);
        $mform->setType($this->inputname, PARAM_TEXT);
    }

    /**
     * When passing the user object to the form class for the edit profile page
     * we should load the key for the saved data
     * Overwrites the base class method.
     *
     * @param   object   user object
     */
    public function edit_load_user_data($user)
    {
        $user->{$this->inputname} = $this->datakey;
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
        $string = '';
        if (is_array($data)) {
            foreach ($data as $key) {
                if (isset($this->options[$key])) {
                    $string .= $this->options[$key]."\r\n";
                }
            }

            return substr($string, 0, -2);
        }

        return isset($this->options[$data]) ? $this->options[$data] : null;
        // return isset($this->options[$data]) ? $data : null;
    }
    
}