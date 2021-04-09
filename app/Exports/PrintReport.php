<?php

namespace App\Exports;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class PrintReport implements FromView {
    public $append = [];
    public $template = '';

    public function __construct($template, $data) {
        $this->template = $template;
        $this->append = $data;
    }

    public function view(): View {
        return view('report.xls.' . $this->template, $this->append);
    }
}