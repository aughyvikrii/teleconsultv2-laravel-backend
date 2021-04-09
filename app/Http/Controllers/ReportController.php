<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Models\{Appointment, LogReport, Soap, Branch, Department, Person, Specialist};
use App\Exports\{FinanceReport, AppointmentReport, DoctorReport, PatientReport, PrintReport};

use App\Http\Controllers\{AppointmentController, DoctorController, PatientController, BranchController, DepartmentController, SpecialistController};

use DB, PDF, Excel;

class ReportController extends Controller
{
    public $type = [
        'full', 'register', 'soap', 'pharmacy', 'radiology', 'laboratory'
    ];

    public $appointment = null;
    public $save_log = false;

    public function Print(Request $request) {
        $type = $request->input('type');
        $appointment_id = $request->input('appointment_id');

        if(!in_array($type, $this->type)) {
            return response()->json([
                'status' => false,
                'message' => "Tipe laporan #{$type} tidak dikenali"
            ]);
        }

        $appointment = Appointment::joinFullInfo()
            ->selectRaw('
                row_to_json(appointments) as appointment_json,
                row_to_json(patient) as patient_json,
                row_to_json(doctor) as doctor_json,
                row_to_json(schedules) as schedule_json,
                row_to_json(departments) as department_json,
                row_to_json(branches) as branch_json,
                row_to_json(bills) as bill_json,
                id_date(patient.birth_date) as id_birth_date,
                id_age(patient.birth_date) as age
            ')
            ->doctorUID()
            ->where('appointments.aid', $appointment_id)
            ->first();

        if(!$appointment) {
            return response()->json([
                'status' => false,
                'message' => "Perjanjian dengan id #{$appointment_id} tidak ditemukan"
            ]);
        }

        $this->appointment = $appointment;

        return $this->$type($request);
    }

    public function register(Request $request) {
        $type = $request->input('type');

        $appointment_id = $request->input('appointment_id');

        $appointment = $this->appointment;

        $uniq = uniqid();

        if($this->save_log) {
            LogReport::create([
                'aid' =>  $appointment_id,
                'uniq' => $uniq,
                'type' => 'register',
                'create_id' => auth()->user()->uid,
                'created_at' =>  date('Y-m-d H:i:s')
            ]);
        }

        $pdf = PDF::loadView('report.pdf.'. $type, [
            'data' => $appointment,
            'id' => $uniq
        ]);

        $pdf->setOptions([
            'defaultPaperSize' => 'a4',
            'defaultFont' => 'Times New Roman'
        ]);

        return $pdf->stream('Laporan_Pendaftaran_'. @$appointment->appointment_json['aid'] . '_'. $uniq . '.pdf');
    }

    public function soap(Request $request) {
        $type = $request->input('type');

        $appointment_id = $request->input('appointment_id');

        $appointment = $this->appointment;

        $soap = Soap::JoinFullInfoJson()
                ->where('soaps.aid', $appointment_id)
                ->first();

        $uniq = uniqid();

        if($this->save_log) {
            LogReport::create([
                'aid' =>  $appointment_id,
                'uniq' => $uniq,
                'type' => 'soap',
                'create_id' => auth()->user()->uid,
                'created_at' =>  date('Y-m-d H:i:s')
            ]);
        }

        $pdf = PDF::loadView('report.pdf.'. $type, [
            'register' => $appointment,
            'soap' => $soap,
            'id' => $uniq
        ]);

        $pdf->setOptions([
            'defaultPaperSize' => 'a4',
            'defaultFont' => 'Times New Roman'
        ]);

        return $pdf->stream('Laporan_Soap_'. @$appointment->appointment_json['aid'] . '_'. $uniq . '.pdf');
    }

    public function finance(Request $request) {
        $patient = Appointment::joinFullInfo()->selectRaw('appointments.aid appointment_id, bills.uniq as bill_uniq, bills.paid_on, id_date(bills.paid_on) as id_paid_on, patient.pid as patient_id, patient.full_name as patient_name, appointments.consul_date, ftime(appointments.consul_time) as consul_time, doctor.pid as doctor_id, doctor.display_name as doctor_name, departments.deid as department_id, departments.name as department, branches.bid as branch_id, branches.name as branch,specialists.sid as specialist_id, specialists.title as specialist, specialists.alt_name as specialist_alt_name, bills.amount, bills.status, appointments.created_at, id_date(appointments.created_at) as id_created_at');

        $start_date = $request->query('start_date');
        $patient->when($start_date, function($query) use ($start_date) {
            $query->whereRaw("to_char(bills.paid_on, 'YYYY-MM-DD') >= ?", [$start_date]);
        });

        $end_date = $request->query('end_date');
        $patient->when($end_date, function($query) use ($end_date) {
            $query->whereRaw("to_char(bills.paid_on, 'YYYY-MM-DD') <= ?", [$end_date]);
        });

        $branch_id = $request->query('branch_id');
        $branch_ids = $branch_id ? explode(",", $branch_id) : [];

        $patient->when($branch_ids, function($query) use ($branch_ids){
            $query->whereIn('branches.bid', $branch_ids);
        });

        $department_id = $request->query('department_id');
        $department_ids = $department_id ? explode(",", $department_id) : [];

        $patient->when($department_ids, function($query) use ($department_ids){
            $query->whereIn('departments.deid', $department_ids);
        });

        $specialist_id = $request->query('specialist_id');
        $specialist_ids = $specialist_id ? explode(",", $specialist_id) : [];

        $patient->when($specialist_ids, function($query) use ($specialist_ids){
            $query->whereIn('specialists.sid', $specialist_ids);
        });

        $doctor_id = $request->query('doctor_id');
        $doctor_ids = $doctor_id ? explode(",", $doctor_id) : [];

        $patient->when($doctor_ids, function($query) use ($doctor_ids){
            $query->whereIn('doctor.pid', $doctor_ids);
        });

        $patient_id = $request->query('patient_id');
        $patient_ids = $patient_id ? explode(",", $patient_id) : [];

        $patient->when($patient_ids, function($query) use ($patient_ids){
            $query->whereIn('patient.pid', $patient_ids);
        });

        $appointment_id = $request->query('appointment_id');
        $appointment_ids = $appointment_id ? explode(",", $appointment_id) : [];

        $patient->when($appointment_ids, function($query) use ($appointment_ids){
            $query->whereIn('appointments.aid', $appointment_ids);
        });

        $status = $request->query('status');
        $statuses = $status ? explode(",", $status) : [];

        $patient->when($statuses, function($query) use ($statuses){
            $query->whereIn('bills.status', $statuses);
        });
        
        if($request->query('paginate')=='true') $list = $patient->paginate($request->query('data_per_page', 10));
        else $list = $patient->get();

        return response()->json([
            'status'=> true,
            'data' => $list
        ]);
    }

    public function print_finance (Request $request) {
        $list = $this->finance($request)->original;

        $paginate = $request->query('paginate');

        if($paginate=='true') $data = $list['data']->items();
        else $data = $list['data'];

        $type = $request->query('print_type', 'pdf');

        $uniq = uniqid();

        if($start_date = $request->query('start_date')) $start_date = \Carbon\Carbon::parse($start_date)->translatedFormat('d F Y');
        else $start_date = '-';

        $filter_date = $start_date . ' s/d ';

        if($end_date = $request->query('end_date')) $end_date = \Carbon\Carbon::parse($end_date)->translatedFormat('d F Y');
        else $end_date = '-';

        $filter_date .= $end_date;

        $branch = $department = $specialist = $doctor = $patient = $status = $page = '-';
        if($branch_id = $request->query('branch_id')) {
            $branch_ids = explode(",", $branch_id);
            $branches = Branch::select('name')->whereIn('bid', $branch_ids)->get();
            $branch = '';
            foreach($branches as $item) {
                $branch .= $item->name . PHP_EOL;
            }
        }

        if($department_id = $request->query('department_id')) {
            $department_ids = explode(",", $department_id);
            $departments = Department::select('name')->whereIn('deid', $department_ids)->get();
            $department = '';
            foreach($departments as $item) {
                $department .= $item->name . PHP_EOL;
            }
        }

        if($specialist_id = $request->query('specialist_id')) {
            $specialist_ids = explode(",", $specialist_id);
            $specialists = Specialist::select('alt_name')->whereIn('sid', $specialist_ids)->get();
            $specialist = '';
            foreach($specialists as $item) {
                $specialist .= $item->alt_name . PHP_EOL;
            }
        }

        if($doctor_id = $request->query('doctor_id')) {
            $doctor_ids = explode(",", $doctor_id);
            $doctors = Person::select('display_name')->whereIn('pid', $doctor_ids)->get();
            $doctor = '';
            foreach($doctors as $item) {
                $doctor .= $item->display_name . PHP_EOL;
            }
        }

        if($patient_id = $request->query('patient_id')) {
            $patient_ids = explode(",", $patient_id);
            $patients = Person::select('full_name')->whereIn('pid', $patient_ids)->get();
            $patient = '';
            foreach($patients as $item) {
                $patient .= $item->full_name . PHP_EOL;
            }
        }

        $status = payment_status($request->query('status'));

        if(!$paginate) $page = '1/1';
        else $page = $list['data']->currentPage() . '/' . $list['data']->lastPage(0);
        
        $filter = [
            'date' => $filter_date,
            'branch' => $branch,
            'department' => $department,
            'specialist' => $specialist,
            'doctor' => $doctor,
            'patient' => $patient,
            'status' => $status,
            'page' => $page
        ];

        $append = [
            'items' => $data,
            'id' => $uniq,
            'filter' => $filter
        ];

        if($type === 'pdf') {
            $pdf = PDF::loadView('report.pdf.finance', $append);
    
            $pdf->setOptions([
                'defaultPaperSize' => 'a4',
                'defaultFont' => 'Times New Roman'
            ]);
    
            return $pdf->stream('Laporan_Keuangan_'. $uniq . '.pdf');
        } else {
            return Excel::download(new FinanceReport($append), 'Laporan_Keuangan_'. $uniq . '.xlsx');
            // return (new FinanceReport($append))->download('Laporan_Keuangan_'. $uniq . '.xlsx');
        }
    }

    public function print_appointment(Request $request) {
        $cont = new AppointmentController;
        $list = $cont->List($request)->original;

        $paginate = $request->query('paginate');

        if($paginate=='true') $data = $list['data']->items();
        else $data = $list['data'];

        $type = $request->query('print_type', 'pdf');

        $uniq = uniqid();

        if($start_date = $request->query('start_date')) $start_date = \Carbon\Carbon::parse($start_date)->translatedFormat('d F Y');
        else $start_date = '-';

        $filter_date = $start_date . ' s/d ';

        if($end_date = $request->query('end_date')) $end_date = \Carbon\Carbon::parse($end_date)->translatedFormat('d F Y');
        else $end_date = '-';

        $filter_date .= $end_date;

        $branch = $department = $specialist = $doctor = $patient = $status = $page = '-';
        if($branch_id = $request->query('branch_id')) {
            $branch_ids = explode(",", $branch_id);
            $branches = Branch::select('name')->whereIn('bid', $branch_ids)->get();
            $branch = '';
            foreach($branches as $item) {
                $branch .= $item->name . PHP_EOL;
            }
        }

        if($department_id = $request->query('department_id')) {
            $department_ids = explode(",", $department_id);
            $departments = Department::select('name')->whereIn('deid', $department_ids)->get();
            $department = '';
            foreach($departments as $item) {
                $department .= $item->name . PHP_EOL;
            }
        }

        if($specialist_id = $request->query('specialist_id')) {
            $specialist_ids = explode(",", $specialist_id);
            $specialists = Specialist::select('alt_name')->whereIn('sid', $specialist_ids)->get();
            $specialist = '';
            foreach($specialists as $item) {
                $specialist .= $item->alt_name . PHP_EOL;
            }
        }

        if($doctor_id = $request->query('doctor_id')) {
            $doctor_ids = explode(",", $doctor_id);
            $doctors = Person::select('display_name')->whereIn('pid', $doctor_ids)->get();
            $doctor = '';
            foreach($doctors as $item) {
                $doctor .= $item->display_name . PHP_EOL;
            }
        }

        if($patient_id = $request->query('patient_id')) {
            $patient_ids = explode(",", $patient_id);
            $patients = Person::select('full_name')->whereIn('pid', $patient_ids)->get();
            $patient = '';
            foreach($patients as $item) {
                $patient .= $item->full_name . PHP_EOL;
            }
        }

        $status = appointment_status($request->query('status'));

        if(!$paginate) $page = '1/1';
        else $page = $list['data']->currentPage() . '/' . $list['data']->lastPage(0);
        
        $filter = [
            'date' => $filter_date,
            'branch' => $branch,
            'department' => $department,
            'specialist' => $specialist,
            'doctor' => $doctor,
            'patient' => $patient,
            'status' => $status,
            'page' => $page
        ];

        $append = [
            'items' => $data,
            'id' => $uniq,
            'filter' => $filter
        ];

        if($type === 'pdf') {
            $pdf = PDF::loadView('report.pdf.appointment', $append);
    
            $pdf->setOptions([
                'defaultPaperSize' => 'a4',
                'defaultFont' => 'Times New Roman'
            ]);
    
            return $pdf->stream('Laporan_Perjanjian_'. $uniq . '.pdf');
        } else {
            return Excel::download(new AppointmentReport($append), 'Laporan_Perjanjian_'. $uniq . '.xlsx');
        }
    }

    public function print_doctor(Request $request) {
        $cont = new DoctorController;
        $list = $cont->List($request, true)->original;

        $paginate = $request->query('paginate');

        if($paginate=='true') $data = $list['data']->items();
        else $data = $list['data'];

        $type = $request->query('print_type', 'pdf');

        $uniq = uniqid();
        $specialist = '';

        $name = $request->name;
        $email = $request->email;
        $phone_number = $request->phone_number;

        if($specialist_id = $request->query('specialist_id')) {
            $specialist_ids = explode(",", $specialist_id);
            $specialists = Specialist::select('alt_name')->whereIn('sid', $specialist_ids)->get();
            foreach($specialists as $item) {
                $specialist .= $item->alt_name . PHP_EOL;
            }
        }

        if(!$paginate) $page = '1/1';
        else $page = $list['data']->currentPage() . '/' . $list['data']->lastPage(0);
        
        $filter = [
            'name' => $name,
            'email' => $email,
            'phone_number' => $phone_number,
            'specialist' => $specialist,
            'page' => $page
        ];

        $append = [
            'items' => $data,
            'id' => $uniq,
            'filter' => $filter
        ];

        if($type === 'pdf') {
            $pdf = PDF::loadView('report.pdf.doctor', $append);
    
            $pdf->setOptions([
                'defaultPaperSize' => 'a4',
                'defaultFont' => 'Times New Roman'
            ]);
    
            return $pdf->stream('Daftar_Dokter_'. $uniq . '.pdf');
        } else {
            return Excel::download(new DoctorReport($append), 'Daftar_Dokter_'. $uniq . '.xlsx');
        }
    }

    public function print_patient(Request $request) {
        $cont = new PatientController;
        $list = $cont->List($request, true)->original;

        $paginate = $request->query('paginate');

        if($paginate=='true') $data = $list['data']->items();
        else $data = $list['data'];

        $type = $request->query('print_type', 'pdf');

        $uniq = uniqid();
        $specialist = '';

        $name = $request->name;
        $email = $request->email;
        $phone_number = $request->phone_number;

        if(!$paginate) $page = '1/1';
        else $page = $list['data']->currentPage() . '/' . $list['data']->lastPage(0);
        
        $filter = [
            'name' => $name,
            'email' => $email,
            'phone_number' => $phone_number,
            'page' => $page
        ];

        $append = [
            'items' => $data,
            'id' => $uniq,
            'filter' => $filter
        ];

        if($type === 'pdf') {
            $pdf = PDF::loadView('report.pdf.patient', $append);
    
            $pdf->setOptions([
                'defaultPaperSize' => 'a4',
                'defaultFont' => 'Times New Roman'
            ]);
    
            return $pdf->stream('Daftar_Pasien_'. $uniq . '.pdf');
        } else {
            return Excel::download(new PatientReport($append), 'Daftar_Pasien_'. $uniq . '.xlsx');
        }
    }

    public function print_branch(Request $request) {
        $cont = new BranchController;
        $list = $cont->List($request, true)->original;

        $paginate = $request->query('paginate');

        if($paginate=='true') $data = $list['data']->items();
        else $data = $list['data'];

        $type = $request->query('print_type', 'pdf');

        $uniq = uniqid();

        if(!$paginate) $page = '1/1';
        else $page = $list['data']->currentPage() . '/' . $list['data']->lastPage(0);
        
        $filter = [
            'page' => $page
        ];

        $append = [
            'items' => $data,
            'id' => $uniq,
            'filter' => $filter
        ];
        if($type === 'pdf') {
            $pdf = PDF::loadView('report.pdf.branch', $append);
    
            $pdf->setOptions([
                'defaultPaperSize' => 'a4',
                'defaultFont' => 'Times New Roman'
            ]);
    
            return $pdf->stream('Daftar_Cabang_'. $uniq . '.pdf');
        } else {
            return Excel::download(new PrintReport('branch', $append), 'Daftar_Cabang_'. $uniq . '.xlsx');
        }
    }

    public function print_department(Request $request) {
        $cont = new DepartmentController;
        $list = $cont->List($request, true)->original;

        $paginate = $request->query('paginate');

        if($paginate=='true') $data = $list['data']->items();
        else $data = $list['data'];

        $type = $request->query('print_type', 'pdf');

        $uniq = uniqid();

        if(!$paginate) $page = '1/1';
        else $page = $list['data']->currentPage() . '/' . $list['data']->lastPage(0);
        
        $filter = [
            'page' => $page
        ];

        $append = [
            'items' => $data,
            'id' => $uniq,
            'filter' => $filter
        ];
        if($type === 'pdf') {
            $pdf = PDF::loadView('report.pdf.department', $append);
    
            $pdf->setOptions([
                'defaultPaperSize' => 'a4',
                'defaultFont' => 'Times New Roman'
            ]);
    
            return $pdf->stream('Daftar_Departemen_'. $uniq . '.pdf');
        } else {
            return Excel::download(new PrintReport('department', $append), 'Daftar_Departemen_'. $uniq . '.xlsx');
        }
    }

    public function print_specialist(Request $request) {
        $cont = new SpecialistController;
        $list = $cont->List($request, true)->original;

        $paginate = $request->query('paginate');

        if($paginate=='true') $data = $list['data']->items();
        else $data = $list['data'];

        $type = $request->query('print_type', 'pdf');

        $uniq = uniqid();

        if(!$paginate) $page = '1/1';
        else $page = $list['data']->currentPage() . '/' . $list['data']->lastPage(0);
        
        $filter = [
            'page' => $page
        ];

        $append = [
            'items' => $data,
            'id' => $uniq,
            'filter' => $filter
        ];

        if($type === 'pdf') {
            $pdf = PDF::loadView('report.pdf.specialist', $append);
    
            $pdf->setOptions([
                'defaultPaperSize' => 'a4',
                'defaultFont' => 'Times New Roman'
            ]);
    
            return $pdf->stream('Daftar_Spesialis_'. $uniq . '.pdf');
        } else {
            return Excel::download(new PrintReport('specialist', $append), 'Daftar_Spesialis_'. $uniq . '.xlsx');
        }
    }
}
