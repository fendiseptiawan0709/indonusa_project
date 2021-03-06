<?php

namespace App\Http\Controllers;

use Redirect;
use Auth;
use App\Patient;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Mail\Account_Verification;
use Illuminate\Support\Facades\Mail;
use PHPMailer;

class Patient_Controller extends Controller {

    function auth() {
        if (Auth::guest()) {
            return redirect('login');
        } elseif (Auth::user()->status == 'Admin') {
            return redirect('admin');
        } elseif (Auth::user()->status == 'Doctor') {
            return redirect('doctor');
        } elseif (Auth::user()->status == 'Lab') {
            return redirect('lab');
        } elseif (Auth::user()->status == 'Pharmacy') {
            return redirect('pharmacy');
        } elseif (Auth::user()->status == 'Patient') {
            //Access granted
        }
    }

    //Untuk menampilkan dashboard pasien - Hanya untuk pasien
    public function home() {
        if (Auth::guest() == TRUE || Auth::user()->status !== 'Patient') {
            return $this->auth();
        }

        $data['patient_id'] = DB::table('patient')->where('user_id', Auth::user()->id)->value('id');

        return view('patient.home', $data);
    }

    //Untuk menampilkan form tambah pasien - Hanya untuk admin
    public function patient_add() {
        if (Auth::guest() == TRUE || Auth::user()->status !== 'Admin') {
            return $this->auth();
        }
        return view('patient.add');
    }

    function create_directory() {
        $b = '/storage/assets/userfile/abc';
        $a = Storage::makeDirectory($b);
        return var_dump($a) . $b;
    }

    //Untuk memproses form tambah pasien - Hanya untuk admin
    public function patient_add_submit(Request $request) {

        $this->validate($request, [
            'nik' => 'required|min:5|max:35|unique:patient',
            'email' => 'required|email|unique:users',
            'name' => 'required',
            'gender' => 'required',
            'birth_date' => 'required',
            'city' => 'required',
            'address' => 'required',
            'mobile' => 'required',
            'telephone' => 'required',
            'photo' => 'required|mimes:jpeg,png,jpg|max:512'
                ], [
            'nik.required' => 'NIK harus diisi',
            'nik.unique' => 'NIK pernah digunakan.',
            'email.unique' => 'Email pernah digunakan',
            'name.required' => 'Nama harap diisi',
            'gender.required' => 'Mohon isi data jenis kelamin Anda',
            'birth_date.required' => 'Kolom tanggal lahir harap diisi',
            'city.required' => 'Kota tinggal (domisili) harap diisi',
            'address.required' => 'Kolom alamat harap diisi',
            'mobile.required' => 'Kolom nomor ponsel harap diisi',
            'telephone.required' => 'Kolom telepon harap diisi',
            'photo.required' => 'Harap pilih foto untuk profil Anda'
        ]);

        $password = $this->generate_pass();
        //Insert into account table
        $account = new User;

        $account->email = $request->input('email');
        //Password will be added when user's first login.
        $account->password = password_hash($password, PASSWORD_DEFAULT);

        $account->name = $request->input('name');
        $account->city = $request->input('city');
        $account->address = $request->input('address');
        $account->mobile = $request->input('mobile');
        $account->telephone = $request->input('telephone');
        $account->remember_token = md5(date('Y-m-d H:i:s'));
        $account->status = 'Patient';
        $account->created_at = date('Y-m-d H:i:s');
        $account->updated_at = date('Y-m-d H:i:s');

        $account->save();
        //Insert into patient table
        //Formatting into database
        $birth_date = explode('/', $request->input('birth_date'));
        $birth_date_db = $birth_date[2] . '-' . $birth_date[0] . '-' . $birth_date[1];

        //Uploading photo profile
        //$this->validate($request, [ 'image' => 'required|image|mimes:jpeg,png,jpg|max:512' ]);
        $account_id = DB::table('users')->where('email', $request->input('email'))->value('id');
        $request->file('photo')->storeAs(('/public/assets/userfile/patient/' . $account_id . '/profile'), md5($request->input('email')) . '.' . $request->file('photo')->extension());

        $patient = new Patient(
                array(
            'nik' => $request->input('nik'),
            'name' => $request->input('name'),
            'gender' => $request->input('gender'),
            'birth_date' => $birth_date_db,
            'city' => $request->input('city'),
            'address' => $request->input('address'),
            'mobile' => $request->input('mobile'),
            'telephone' => $request->input('telephone'),
            'photo' => 'storage/assets/userfile/patient/' . $account_id . '/profile/' . md5($request->input('email')) . '.' . $request->file('photo')->extension()
                )
        );

        $account_patient = $account::where('email', $request->input('email'))->first();
        $account_patient->patient()->save($patient);

        $this->sendmail($request->input('name'), $request->input('email'), $password);

        return Redirect::back()->with('message', 'Data pasien ' . $request->input('name') . ' telah berhasil ditambahkan.');
    }

    public function generate_pass($length = 15) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function sendmail($name, $email, $password) {
        $mail = new PHPMailer;

        //Enable SMTP debugging.
        $mail->SMTPDebug = 3;
        //Set PHPMailer to use SMTP.
        $mail->isSMTP();
        //Set SMTP host name
        $mail->Host = "smtp.gmail.com";
        //Set this to true if SMTP host requires authentication to send email
        $mail->SMTPAuth = true;
        //Provide username and password
        $mail->Username = "fendi.septiawan0709@gmail.com";
        $mail->Password = "ciqxlyaimkwzoizi";
        //If SMTP requires TLS encryption then set it
        $mail->SMTPSecure = "tls";
        //Set TCP port to connect to
        $mail->Port = 587;

        $mail->From = "administrator@emr.com";
        $mail->FromName = "Electronic Medical Record - Aktivasi Akun";

        $mail->addAddress($email, $name);

        $mail->isHTML(true);

        $mail->Subject = "Subject Text";
        $mail->Body = "<div style='font-size: 14px'><p>Hai, " . $name . ".</p><p>Selamat datang di Electronic Medical Record (EMR).<p>Untuk dapat menggunakan akun Anda, silakan login dengan <br/>Email : " . $email . ".<br/>Password : " . $password . ".</p><p>Harap ganti password Anda segera setelah login.</p><p>Terima kasih.</p>";
        $mail->AltBody = "Hai, " . $name . ". Selamat datang di Electronic Medical Record (EMR). Untuk dapat menggunakan akun Anda, silakan login dengan email : " . $email . ". password : " . $password . ". Harap ganti password Anda segera setelah login. Terima kasih.";

        if (!$mail->send()) {
            echo "Mailer Error: " . $mail->ErrorInfo;
        } else {
            echo "Message has been sent successfully";
        }
    }

    public function sendmail_mailgun($name, $email, $pass) {
        $mail_param = new Account_Verification($name, $email, $pass);
        Mail::to($email)->send($mail_param);
    }

    //Untuk menampilkan profil pasien - Untuk Admin dan Pasien
    public function profile($id = NULL) {
        if (Auth::guest() == TRUE || (Auth::user()->status !== 'Patient' && Auth::user()->status !== 'Admin')) {
            return $this->auth();
        }

        if ($id == NULL) {
            $patient = DB::table('patient')->where('user_id', '=', Auth::user()->id)->get();
            $account = DB::table('users')->where('id', '=', Auth::user()->id)->get();
        } else {
            $patient = DB::table('patient')->where('id', '=', $id)->get();
            $patient_id = DB::table('patient')->where('id', '=', $id)->value('user_id');
            $account = DB::table('users')->where('id', '=', $patient_id)->get();
        }


        $data = array(
            'patient' => $patient,
            'account' => $account
        );

        return view('patient.profile', $data);
    }

    //Untuk menampilkan form profil pasien - Untuk Admin dan Pasien
    public function profile_edit($id = NULL) {
        if (Auth::guest() == TRUE || (Auth::user()->status !== 'Patient' && Auth::user()->status !== 'Admin')) {
            return $this->auth();
        }

        if ($id == NULL) {
            $patient = DB::table('patient')->where('user_id', '=', Auth::user()->id)->get();
            $account = DB::table('users')->where('id', '=', Auth::user()->id)->get();
            $data = array(
                'from' => 'Patient',
                'patient' => $patient,
                'account' => $account
            );
        } else {
            $patient = DB::table('patient')->where('id', '=', $id)->get();
            $user_id = DB::table('patient')->where('id', '=', $id)->value('user_id');
            $account = DB::table('users')->where('id', '=', $user_id)->get();
            $data = array(
                'from' => '',
                'patient' => $patient,
                'account' => $account
            );
        }

        return view('patient.edit-profile', $data);
    }

    //Untuk memproses form edit profil pasien - Untuk Admin dan Pasien
    public function profile_edit_submit(Request $request) {
        $patient = new Patient;

        $nik = $request->input('nik');

        $patient->where('nik', $nik)->update(
                array(
                    'name' => $request->input('name'),
                    'gender' => $request->input('gender'),
                    'birth_date' => date('Y-m-d', strtotime($request->input('birth_date'))),
                    'city' => $request->input('city'),
                    'address' => $request->input('address'),
                    'mobile' => $request->input('mobile'),
                    'telephone' => $request->input('telephone'),
                )
        );

        $user_id = DB::table('patient')->where('nik', $nik)->value('user_id');

        $user = new User;

        $user->where('id', $user_id)->update(array(
            'name' => $request->input('name')
        ));

        if ($request->input('from') == 'Patient') {
            return redirect('patient/profile')->with('message', 'Profil Anda telah berhasil diperbarui.');
        }

        return redirect('admin/patient/manage')->with('message', 'Data pasien ' . $request->input('name') . ' telah berhasil diperbarui.');
    }

    //Untuk menampilkan form edit password pasien - Hanya untuk Pasien
    public function password_edit() {
        if (Auth::guest() == TRUE || Auth::user()->status !== 'Patient') {
            return $this->auth();
        }

        return view('patient.edit-password');
    }

    //Untuk memproses form edit password pasien - Hanya untuk Pasien
    public function password_edit_submit(Request $req) {
        $email = $req->input('email');
        $cur_password = $req->input('password');
        $get_old_pass = DB::table('users')->where('email', $email)->value('password');
        $check = password_verify($cur_password, $get_old_pass);
        if ($check == TRUE) {
            $new_password = $req->input('password_new');
            $ver_new_password = $req->input('password_new_verify');
            if ($new_password == $ver_new_password) {
                $admin = new User;
                $admin->where('email', $email)->update(array('password' => password_hash($new_password, PASSWORD_DEFAULT)));
                return redirect('patient/profile')->with('message', 'Password Anda telah diperbarui.');
            } else {
                return Redirect::back()->with('message', 'Password Anda tidak sesuai.');
            }
        } else {
            return Redirect::back()->with('message', 'Password Anda tidak sesuai.');
        }

        return redirect('patient/profile')->with('message', 'Password Anda berhasil diperbarui.');
    }

    //Untuk memanajemen pasien - Hanya untuk Admin
    public function manage() {
        if (Auth::guest() == TRUE || Auth::user()->status !== 'Admin') {
            return $this->auth();
        }

        $patient = DB::table('users')->join('patient', 'users.id', '=', 'patient.user_id')->where('users.is_enabled', '=', '1')->get();

        $data = array(
            'patient' => $patient
        );
        return view('patient.manage', $data);
    }

    //Untuk menghapus data pasien - Hanya untuk admin
    public function delete($id) {
        if (Auth::guest() == TRUE || Auth::user()->status !== 'Admin') {
            return $this->auth();
        }

        $user_id = DB::table('patient')->where('id', $id)->value('user_id');
        $patient_name = DB::table('patient')->where('id', $id)->value('name');
        DB::table('users')->where('id', $user_id)->update(array('is_enabled' => '0'));

        return redirect('admin/patient/manage')->with('message', 'Data pasien ' . $patient_name . ' telah berhasil dihapus.');
    }

}
