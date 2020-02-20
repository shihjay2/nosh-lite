<?php

namespace App\Http\Controllers;

use App;
use App\Events\ProcessEvent;
use App\Http\Requests;
use Date;
use DB;
use Event;
use File;
use Form;
use Hash;
use HTML;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\MessageBag;
use Imagick;
use Shihjay2\OpenIDConnectUMAClient;
use shihjay2\tcpdi_merger\MyTCPDI;
use shihjay2\tcpdi_merger\Merger;
use QrCode;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Schema;
use Session;
use Storage;
use SoapBox\Formatter\Formatter;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use URL;
use ZipArchive;

class CoreController extends Controller
{
    public function __construct()
    {
    }

    public function load_data(Request $request)
    {
        if ($request->isMethod('post')) {
            $files = glob(env('DOCUMENTS_DIR').'/{,.}*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            $zipfile = $request->file('file_input')->store('public/zips');
            $zip = new ZipArchive;
            $open = $zip->open(storage_path() . '/app/' . $zipfile);
            if ($open === TRUE) {
                $zip->extractTo(env('DOCUMENTS_DIR'));
                $sqlsearch = glob(env('DOCUMENTS_DIR') . '/*_noshexport.sql');
                $dirs = array_filter(glob(env('DOCUMENTS_DIR') . '/*'), 'is_dir');
                $pid = str_replace(env('DOCUMENTS_DIR') . '/', '', $dirs[0]);
                Session::put('pid', $pid);
                if (! empty($sqlsearch)) {
                    foreach ($sqlsearch as $sqlfile) {
                        $command = "mysql -u " . env('DB_USERNAME') . " -p". env('DB_PASSWORD') . " " . env('DB_DATABASE') . " < " . $sqlfile;
                        system($command);
                        unlink($sqlfile);
                    }
                    $message = trans('nosh.load_data');
                    Session::put('message_action', $message);
                    return redirect()->route('patient');
                } else {
                    unlink($directory . '/' . $file->getClientOriginalName());
                    $message = trans('nosh.error') . " - " . trans('nosh.database_import_cloud2');
                }
                $zip->close();
            } else {
                $message = trans('nosh.error') . ' - ' . $open . ',' . $zipfile;
            }
            return $message;
        } else {
            $data['panel_header'] = trans('nosh.load_data');
            $data['document_upload'] = route('load_data');
            $type_arr = ['zip'];
            $data['document_type'] = json_encode($type_arr);
            return view('document_upload', $data);
        }
    }

    public function timer(Request $request)
    {
        $return = '';
        if (file_exists(base_path() . '/timer')) {
            $min = File::get(base_path() . '/timer');
            $type = 'warning';
            if ($min > 30) {
                $type = 'primary';
            }
            if ($min < 10) {
                $type = 'danger';
            }
            $return = '<span class="badge badge-' . $type . '">' . $min  . 'minutes remaining</span>';
        }
        return $return;
    }
}
