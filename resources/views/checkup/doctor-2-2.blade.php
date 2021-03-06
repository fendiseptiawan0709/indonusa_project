@extends('layouts.layout-doctor')

@section('css')

<title>EMR Dokter | Isi Pemeriksaan</title>

@stop

@section('content')

@section('content')
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Pemeriksaan
            <small>Isi Pemeriksaan Pasien</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Beranda</a></li>
            <li class="">Pemeriksaan</li>
            <li class="active">Isi Pemeriksaan</li>
        </ol>
    </section>
    <section class="content">

        <div class="row">
            <div class="col-md-12">
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs">
                        <li class="active"><a href="#profile" data-toggle="tab">Pemeriksaan Pasien</a></li>
                    </ul>
                    <div class="tab-content">
                        <div class="active tab-pane" id="profile">
                            <h4>Data pemeriksaan pasien hari ini</h4>
                            <div class="form-horizontal">
                                @foreach($checkup as $r_checkup)
                                @foreach($patient as $r_patient)
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <p style="">Tanggal Pemeriksaan</p>
                                            </div>
                                            <div class="col-md-8">
                                                <p>{{ date('d-M-Y', strtotime($r_checkup->date)) }}</p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                @if(Auth::user()->status == 'Doctor')
                                                <p style="">Nama Dokter</p>
                                                @else
                                                <p style="">Nama Farmasi</p>
                                                @endif
                                            </div>
                                            <div class="col-md-8">
                                                <p>{{ Auth::user()->name }}</p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <p style="">Nama Pasien</p>
                                            </div>
                                            <div class="col-md-8">
                                                <p>{{ $r_patient->name }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <p style="">Gejala</p>
                                            </div>
                                            <div class="col-md-8">
                                                <p>{{ $r_checkup->symtomp }}</p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <p style="">Hasil Pemeriksaan</p>
                                            </div>
                                            <div class="col-md-8">
                                                <p>{{ $r_checkup->result }}</p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <p style="">Catatan</p>
                                            </div>
                                            <div class="col-md-8">
                                                <p>{{ $r_checkup->note }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                                @endforeach
                                <!-- Horizontal Form -->
                                <div class="box box-info" style="margin-top: 20px">
                                    <div class="box-header with-border">
                                        <h3 class="box-title">Resep Obat Pasien</h3>
                                    </div>
                                    <!-- /.box-header -->
                                    <!-- form start -->
                                    <div class="form-horizontal">
                                        <form action="{{ url('doctor/checkup/step_2_pharmacy_submit') }}" method="POST">
                                            @if(count($prescription_detail) !== 0)
                                            <div class="row">
                                                <div class="col-xs-12">
                                                    <div class="box">
                                                        <div class="box-header">
                                                            <div class="box-tools hidden">
                                                                <div class="input-group input-group-sm" style="width: 150px;">
                                                                    <input type="text" name="table_search" class="form-control pull-right" placeholder="Search">

                                                                    <div class="input-group-btn">
                                                                        <button type="submit" class="btn btn-default"><i class="fa fa-search"></i></button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!-- /.box-header -->
                                                        <div class="box-body table-responsive no-padding">
                                                            <table class="table table-hover">
                                                                <tr>
                                                                    <th>No.</th>
                                                                    <th>Nama Obat</th>
                                                                    <th>Dosis</th>
                                                                    <th>Aturan Penggunaan</th>
                                                                    <th>Aksi</th>
                                                                </tr>
                                                                <?php $i = 1; ?>
                                                                @foreach($prescription_detail as $r_detail)
                                                                <tr>
                                                                    <td>{{ $i++ }}</td>
                                                                    <td>{{ $r_detail->medicine }}</td>
                                                                    <td>{{ $r_detail->dosage }}</td>
                                                                    <td>{{ $r_detail->rule_of_use }}</td>
                                                                    <td>
                                                                        <button class="btn btn-danger" title="Hapus obat" type="submit" name="action" value="delete_{{ $r_detail->id }}" onclick="return confirm('Apa Anda yakin menghapus obat ini dari resep ?')"><i class="fa fa-close"></i></button>
                                                                    </td>
                                                                </tr>
                                                                @endforeach
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @endif
                                            <input name="_token" value="{{ csrf_token() }}" type="hidden"/>
                                            <input name="prescription_id" value="{{ $prescription_id }}" type="hidden"/>
                                            <div class="box-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="inputEmail3" class="col-md-4 control-label">Nama Obat</label>

                                                            <div class="col-sm-8">
                                                                <input name="medicine" type="text" class="form-control" id="inputEmail3" placeholder="Nama obat">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="inputPassword3" class="col-sm-2 control-label">Dosis</label>

                                                            <div class="col-sm-4">
                                                                <!--<input type="text" class="form-control" id="inputPassword3" placeholder="Dosis obat">-->
                                                                <select class="form-control" name="dosage">
                                                                    <option value="3 x 1">3 kali sehari</option>
                                                                    <option value="2 x 1">2 kali sehari</option>
                                                                    <option value="1 x 1">1 kali sehari</option>
                                                                    <option value="1 x 2">1 kali dua hari</option>
                                                                    <option value="1 x 3">1 kali tiga hari</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <label for="inputEmail3" class="col-sm-2 control-label">Aturan Pakai</label>

                                                            <div class="col-sm-7">
                                                                <input type="text" name="rule_of_use" class="form-control" id="inputEmail3" placeholder="Aturan pakai obat">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- /.box-body -->
                                            <div class="box-footer">
                                                <div class="row">
                                                    <div class="col-md-9 col-md-push-2">
                                                        <div class="col-md-3">
                                                            <button type="submit" name="action" value="add" class="btn btn-info btn-block">Tambah Obat</button>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <button type="submit" name="action" value="submit" class="btn btn-warning btn-block" onclick="return confirm('Apakah Anda yakin untuk me-submit data pemeriksaan ini?')">Submit Pemeriksaan</button>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <button type="submit" name="action" value="cancel" class="btn btn-danger pull-right">Batal</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- /.box-footer -->
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /.tab-pane -->
                    </div>
                    <!-- /.tab-content -->
                </div>
                <!-- /.nav-tabs-custom -->
            </div>
            <!-- /.col -->
        </div>
        <!-- /.row -->

    </section>
</div>

@stop

@section('js')

<script>
    $('#category').on('change', function (e) {
        var cat_id = e.target.value;
        //ajax
        $.get('/doctor/checkup/category-dropdown?category=' + cat_id, function (data) {
            //success data
            $('#subcategory').empty();
            $('#subcategory').append(' Please choose one');
            $.each(data, function (index, subcatObj) {
                $('#subcategory').append(''
                        + subcatObj.subcategory_name + '</option>');
            });
        });
    });
</script>

@stop