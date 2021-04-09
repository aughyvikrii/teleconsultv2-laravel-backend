<?php

namespace App\Exports;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class PatientReport implements FromView {
    public $append = [];

    public function __construct($data) {
        $this->append = $data;
    }

    public function view(): View {
        return view('report.xls.patient', $this->append);
    }
}