<?php

namespace App\Http\Controllers;

use App\Lab_checkup;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class Lab_checkup_Controller extends Controller {

    public function search() {
        return view('lab_checkup.search');
    }

    public function search_result(Request $req) {
        if (Auth::user()->status == 'Patient') {
            $patient_id = DB::table('patient')->where('user_id', Auth::user()->id)->value('id');
            $data['result'] = DB::table('lab_checkup')->where('patient_id', $patient_id)->where('date', date('Y-m-d', strtotime($req->input('date'))))->get();
        } else {
            $data['result'] = DB::table('lab_checkup')->where('date', date('Y-m-d', strtotime($req->input('date'))))->get();
        }

        $data['form'] = 'search';
        $data['date'] = $req->input('date');

        return view('lab_checkup.search-result', $data);
    }

    public function history() {
        if (Auth::user()->status == 'Patient') {
            $patient_id = DB::table('patient')->where('user_id', Auth::user()->id)->value('id');
            $data['result'] = DB::table('lab_checkup')->where('patient_id', $patient_id)->where('date', '<', date('Y-m-d'))->get();
        } else {
            $data['result'] = DB::table('lab_checkup')->where('date', '<', date('Y-m-d'))->get();
        }

        $data['form'] = '';

        return view('lab_checkup.search-result', $data);
    }

    public function future() {
        if (Auth::user()->status == 'Patient') {
            $patient_id = DB::table('patient')->where('user_id', Auth::user()->id)->value('id');
            $data['result'] = DB::table('lab_checkup')->where('patient_id', $patient_id)->where('date', '>=', date('Y-m-d'))->get();
        } else {
            $data['result'] = DB::table('lab_checkup')->where('date', '>=', date('Y-m-d'))->get();
        }
        $data['form'] = '';

        return view('lab_checkup.search-result', $data);
    }

    //Untuk mengisi pemeriksaan pasien dari lab
    public function add() {
        $data['checkup'] = DB::table('lab_checkup')->where('date', date('Y-m-d'))->where('result', NULL)->value('patient_id');
        $data['patient'] = DB::table('patient')->where('id', $data['checkup'])->get();

        return view('lab_checkup.add', $data);
    }

    public function add_submit(Request $req) {
        $lab_id = DB::table('lab')->where('user_id', Auth::user()->id)->value('id');
        $lab_checkup_id = DB::table('lab_checkup')->where('patient_id', $req->input('patient'))->where('date', date('Y-m-d'))->where('lab_id', $lab_id)->value('id');
        $checkup_id = DB::table('checkup')->where('patient_id', $req->input('patient'))->where('date', date('Y-m-d'))->value('id');
        if ($checkup_id == NULL) {
            $checkup_id = 0;
        }
        $account_id = DB::table('patient')->where('id', $req->input('patient'))->value('user_id');
        $lab = new Lab_checkup;

        /*
        $lab->lab_id = $lab_id;
        $lab->checkup_id = $checkup_id;
        $lab->patient_id = $req->input('patient');
        $lab->result = $req->input('result');
        $lab->notes = $req->input('note');
        $lab->save();
        */

        $lab->where('id', $lab_checkup_id)->update(
                array(
                    'result' => $req->input('result'),
                    'notes' => $req->input('note'),
                    'photo' => 'storage/assets/userfile/patient/' . $account_id . '/checkup/lab/' . $lab_checkup_id . '/result' . '.' . $req->file('photo')->extension(),
                )
        );

        $req->file('photo')->storeAs(('/public/assets/userfile/patient/' . $account_id . '/checkup/lab/' . $lab_checkup_id), 'result' . '.' . $req->file('photo')->extension());

        return redirect('lab/checkup/add');
    }

    public function detail($id) {
        $data['lab_checkup'] = DB::table('lab_checkup')->where('id', $id)->get();
        foreach ($data['lab_checkup'] as $r_pres) {
            $lab_id = $r_pres->lab_id;
            $checkup_id = $r_pres->checkup_id;
            $patient_id = $r_pres->patient_id;
        }
        if ($checkup_id == NULL || $checkup_id == 0) {
            $data['checkup'] = NULL;
            $data['doctor'] = NULL;
        } else {
            $doctor_id = DB::table('checkup')->where('id', $checkup_id)->value('doctor_id');
            $data['checkup'] = DB::table('checkup')->where('id', $checkup_id)->get();
            $data['doctor'] = DB::table('doctor')->where('id', $doctor_id)->get();
        }
        $data['lab'] = DB::table('lab')->where('id', $lab_id)->get();
        $data['patient'] = DB::table('patient')->where('id', $patient_id)->get();
        return view('lab_checkup.detail-lab', $data);
    }

}
