<?php

interface iRadio {
    public function create($work_name, $work_text, $work_link, $identification_number);
    public function save();
    public function read();
}
