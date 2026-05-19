<?php

/**
 * Interface iRadio
 * Defines the contract for GraduateThesis class.
 */
interface iRadio
{
    /**
     * Create/populate the object's properties from parsed data.
     *
     * @param string $work_name         Title of the thesis
     * @param string $work_text         Description/excerpt of the thesis
     * @param string $work_link         URL link to the thesis page
     * @param int    $identification_number  Unique identification number
     * @return void
     */
    public function create(string $work_name, string $work_text, string $work_link, int $identification_number): void;

    /**
     * Save all theses to the graduate_theses table in the thesis database.
     *
     * @return void
     */
    public function save(): void;

    /**
     * Read and return all theses from the graduate_theses table.
     *
     * @return array
     */
    public function read(): array;
}
