<?php

namespace App\Http\Controllers;

use App;
use App\Http\Requests;
use Config;
use Crypt;
use Date;
use DB;
use File;
use Form;
use HTML;
use Imagick;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\MessageBag;
use PdfMerger;
use QrCode;
use Response;
use Schema;
use Session;
use Shihjay2\OpenIDConnectUMAClient;
use SoapBox\Formatter\Formatter;
use URL;

class ChartController extends Controller {

    public function __construct()
    {
        $this->middleware('checkinstall');
        // $this->middleware('csrf');
        // $this->middleware('postauth');
        // $this->middleware('patient');
    }

    public function alerts_list(Request $request, $type)
    {
        $data['sidebar'] = 'alerts';
        $data['message_action'] = Session::get('message_action');
        Session::forget('message_action');
        $query = DB::table('alerts')->where('pid', '=', Session::get('pid'))->where('practice_id', '=', Session::get('practice_id'));
        $type_arr = [
            'active' => [trans('nosh.alert_active'), 'fa-bell'],
            'pending' => [trans('nosh.alert_pending'), 'fa-clock-o'],
            'results' => [trans('nosh.alert_pending_results'), 'fa-flask'],
            'completed' => [trans('nosh.alert_completed'), 'fa-check'],
            'canceled' => [trans('nosh.alert_canceled'), 'fa-times']
        ];
        $dropdown_array = [
            'items_button_text' => $type_arr[$type][0]
        ];
        foreach ($type_arr as $key => $value) {
            if ($key !== $type) {
                $items[] = [
                    'type' => 'item',
                    'label' => $value[0],
                    'icon' => $value[1],
                    'url' => route('alerts_list', [$key])
                ];
            }
        }
        if ($type == 'active') {
            $query->where('alert_date_active', '<=', date('Y-m-d H:i:s', time() + 1209600))
                ->where('alert_date_complete', '=', '0000-00-00 00:00:00')
                ->where('alert_reason_not_complete', '=', '');
        }
        if ($type == 'completed') {
            $query->where('alert_date_complete', '!=', '0000-00-00 00:00:00')
                ->where('alert_reason_not_complete', '=', '');
        }
        if ($type == 'canceled') {
            $query->where('alert_date_complete', '=', '0000-00-00 00:00:00')
                ->where('alert_reason_not_complete', '!=', '');
        }
        if ($type == 'pending') {
            $query->where('alert_date_active', '>', date('Y-m-d H:i:s', time() + 1209600))
                ->where('alert_date_complete', '=', '0000-00-00 00:00:00')
                ->where('alert_reason_not_complete', '=', '');
        }
        if ($type == 'results') {
            $query->where('alert_date_complete', '=', '0000-00-00 00:00:00')
                ->where('alert_reason_not_complete', '=', '')
                ->where(function($query_array1) {
                    $query_array1->where('alert', '=', 'Laboratory results pending')
                    ->orWhere('alert', '=', 'Radiology results pending')
                    ->orWhere('alert', '=', 'Cardiopulmonary results pending')
                    ->orWhere('results', '=', 1);
                });
        }
        $dropdown_array['items'] = $items;
        $data['panel_dropdown'] = $this->dropdown_build($dropdown_array);
        $result = $query->get();
        $return = '';
        $columns = Schema::getColumnListing('alerts');
        $row_index = $columns[0];
        if ($result->count()) {
            $list_array = [];
            foreach ($result as $row) {
                $arr = [];
                $arr['label'] = $row->alert . ' (' . trans('nosh.due') . ' ' . date('m/d/Y', $this->human_to_unix($row->alert_date_active)) . ') - ' . $row->alert_description;
                $list_array[] = $arr;
            }
            $return .= $this->result_build($list_array, 'results_list');
        } else {
            $return .= ' ' . trans('nosh.none') . '.';
        }
        $data['content'] = $return;
        $data['alerts_active'] = true;
        $data['panel_header'] = trans('nosh.alerts');
        $data = array_merge($data, $this->sidebar_build('chart'));
        Session::put('last_page', $request->fullUrl());
        return view('home', $data);
    }

    public function allergies_list(Request $request, $type)
    {
        $data['sidebar'] = 'allergies';
        $data['message_action'] = Session::get('message_action');
        Session::forget('message_action');
        $query = DB::table('allergies')->where('pid', '=', Session::get('pid'))->orderBy('allergies_med', 'asc');
        if ($type == 'active') {
            $query->where('allergies_date_inactive', '=', '0000-00-00 00:00:00');
            $dropdown_array = [
                'items_button_text' => trans('nosh.allergies_active')
            ];
            $items[] = [
                'type' => 'item',
                'label' => trans('nosh.allergies_inactive'),
                'icon' => 'fa-times',
                'url' => route('allergies_list', ['inactive'])
            ];
        } else {
            $query->where('allergies_date_inactive', '!=', '0000-00-00 00:00:00');
            $dropdown_array = [
                'items_button_text' => trans('nosh.allergies_inactive')
            ];
            $items[] = [
                'type' => 'item',
                'label' => trans('nosh.allergies_active'),
                'icon' => 'fa-check',
                'url' => route('allergies_list', ['active'])
            ];
        }
        $dropdown_array['items'] = $items;
        $data['panel_dropdown'] = $this->dropdown_build($dropdown_array);
        $result = $query->get();
        $return = '';
        $columns = Schema::getColumnListing('allergies');
        $row_index = $columns[0];
        $list_array = [];
        if ($result->count()) {
            foreach ($result as $row) {
                $arr = [];
                $arr['label'] = $row->allergies_med . ' - ' . $row->allergies_reaction;
                if ($row->reconcile !== null && $row->reconcile !== 'y') {
                    $arr['danger'] = true;
                }
                $list_array[] = $arr;
            }
            $return .= $this->result_build($list_array, 'results_list');
        } else {
            $return .= ' ' . trans('nosh.nkda') . '.';
        }
        $data['content'] = $return;
        $data['panel_header'] = trans('nosh.allergies');
        $data = array_merge($data, $this->sidebar_build('chart'));
        Session::put('last_page', $request->fullUrl());
        return view('home', $data);
    }

    public function audit_logs(Request $request)
    {
        $data['sidebar'] = 'audit_logs';
        $data['message_action'] = Session::get('message_action');
        Session::forget('message_action');
        $query = DB::table('audit')->orderBy('timestamp', 'desc')->paginate(20);
        if ($query->count()) {
            $list_array = [];
            foreach ($query as $row) {
                $arr = [];
                $arr['label'] = '<b>' . date('Y-m-d', $this->human_to_unix($row->timestamp)) . '</b> - ' . $row->displayname . '<br>' . $row->query;
                $list_array[] = $arr;
            }
            $return = $this->result_build($list_array, 'results_list');
            $return .= $query->links();
        } else {
            $return = ' ' . trans('nosh.none') . '.';
        }
        $data['panel_header'] = trans('nosh.audit_logs');
        $data = array_merge($data, $this->sidebar_build('chart'));
        $data['content'] = $return;
        return view('home', $data);
    }

    public function conditions_list(Request $request, $type="")
    {
        $data['sidebar'] = 'conditions';
        $data['message_action'] = Session::get('message_action');
        Session::forget('message_action');
        $query = DB::table('issues')->where('pid', '=', Session::get('pid'))->orderBy('issue', 'asc');
        if ($type == 'active') {
            $query->where('issue_date_inactive', '=', '0000-00-00 00:00:00');
            $dropdown_array = [
                'items_button_text' => trans('nosh.active')
            ];
            $items[] = [
                'type' => 'item',
                'label' => trans('nosh.inactive'),
                'icon' => 'fa-times',
                'url' => route('conditions_list', ['inactive'])
            ];
        } else {
            $query->where('issue_date_inactive', '!=', '0000-00-00 00:00:00');
            $dropdown_array = [
                'items_button_text' => trans('nosh.inactive')
            ];
            $items[] = [
                'type' => 'item',
                'label' => trans('nosh.active'),
                'icon' => 'fa-check',
                'url' => route('conditions_list', ['active'])
            ];
        }
        $dropdown_array['items'] = $items;
        $data['panel_dropdown'] = $this->dropdown_build($dropdown_array);
        $result = $query->get();
        $return = '';
        $columns = Schema::getColumnListing('issues');
        $row_index = $columns[0];
        $pl_list_array = [];
        $mh_list_array = [];
        $sh_list_array = [];
        if ($result->count()) {
            $return .= '<ul class="nav nav-tabs"><li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#pl" style="color:green;">' . trans('nosh.problems') . '</a></li><li class="nav-item"><a class="nav-link" data-toggle="tab" href="#mh" title="' . trans('nosh.medical_history') . '">' . trans('nosh.past') . '</a></li><li class="nav-item"><a class="nav-link" data-toggle="tab" href="#sh" title="' . trans('nosh.surgical_history') . '" style="color:red;">' . trans('nosh.surgeries') . '</a></li></ul><div class="tab-content" style="margin-top:15px;">';
            foreach ($result as $row) {
                if ($row->type == 'Problem List') {
                    $pl_arr = [];
                    $pl_arr['label'] = $row->issue;
                    if ($row->reconcile !== null && $row->reconcile !== 'y') {
                        $pl_arr['danger'] = true;
                    }
                    $pl_list_array[] = $pl_arr;
                }
                if ($row->type == 'Medical History') {
                    $mh_arr = [];
                    $mh_arr['label'] = $row->issue;
                    if ($row->reconcile !== null && $row->reconcile !== 'y') {
                        $mh_arr['danger'] = true;
                    }
                    $mh_list_array[] = $mh_arr;
                }
                if ($row->type == 'Surgical History') {
                    $sh_arr = [];
                    $sh_arr['label'] = $row->issue;
                    if ($row->reconcile !== null && $row->reconcile !== 'y') {
                        $sh_arr['danger'] = true;
                    }
                    $sh_list_array[] = $sh_arr;
                }
            }
            $return .= '<div id="pl" class="tab-pane container active">' . $this->result_build($pl_list_array, 'conditions_list_pl') . '</div>';
            $return .= '<div id="mh" class="tab-pane container fade">' . $this->result_build($mh_list_array, 'conditions_list_mh') . '</div>';
            $return .= '<div id="sh" class="tab-pane container fade">' . $this->result_build($sh_list_array, 'conditions_list_sh') . '</div>';
            $return .= '</div>';
        } else {
            $return .= ' ' . trans('nosh.none') . '.';
        }
        $data['content'] = $return;
        $data['panel_header'] = trans('nosh.conditions');
        $data = array_merge($data, $this->sidebar_build('chart'));
        Session::put('last_page', $request->fullUrl());
        return view('home', $data);
    }

    public function demographics(Request $request)
    {
        $data['sidebar'] = 'demographics';
        $data['message_action'] = Session::get('message_action');
        Session::forget('message_action');
        $result = DB::table('demographics')->where('pid', '=', Session::get('pid'))->first();
        $return = '';
        $active_arr = [
            '0' => trans('nosh.inactive'),
            '1' => trans('nosh.active')
        ];
        if ($result) {
            $gender = $this->array_gender();
            $marital = $this->array_marital();
            $state = $this->array_states($result->country);
            $guardian_state = $this->array_states($result->guardian_country);
            $identity_arr = [
                trans('nosh.lastname') => $result->lastname,
                trans('nosh.firstname') => $result->firstname,
                trans('nosh.nickname') => $result->nickname,
                trans('nosh.middle') => $result->middle,
                trans('nosh.title') => $result->title,
                trans('nosh.DOB') => date('F jS, Y', strtotime($result->DOB)),
                trans('nosh.sex') => $gender[$result->sex],
                trans('nosh.patient_id') => $result->patient_id,
                trans('nosh.ss') => $result->ss,
                trans('nosh.race') => $result->race,
                trans('nosh.marital_status') => $result->marital_status,
                trans('nosh.partner_name') => $result->partner_name,
                trans('nosh.employer') => $result->employer,
                trans('nosh.ethnicity') => $result->ethnicity,
                trans('nosh.caregiver') => $result->caregiver,
                trans('nosh.status') => $active_arr[$result->active],
                trans('nosh.referred_by') => $result->referred_by,
                trans('nosh.language') => $result->language
            ];
            $contact_arr = [
                trans('nosh.street_address1') => $result->address,
                trans('nosh.country') => $result->country,
                trans('nosh.city') => $result->city,
                trans('nosh.state') => $state[$result->state],
                trans('nosh.zip') => $result->zip,
                trans('nosh.email') => $result->email,
                trans('nosh.phone_home') => $result->phone_home,
                trans('nosh.phone_work') => $result->phone_work,
                trans('nosh.phone_cell') => $result->phone_cell,
                trans('nosh.emergency_contact') => $result->emergency_contact,
                trans('nosh.reminder_method') => $result->reminder_method
            ];
            $guardian_arr = [
                trans('nosh.lastname') => $result->guardian_lastname,
                trans('nosh.firstname') => $result->guardian_firstname,
                trans('nosh.relationship') => $result->guardian_relationship,
                trans('nosh.street_address1') => $result->guardian_address,
                trans('nosh.country') => $result->guardian_country,
                trans('nosh.city') => $result->guardian_city,
                trans('nosh.state') => $guardian_state[$result->guardian_state],
                trans('nosh.zip') => $result->guardian_zip,
                trans('nosh.email') => $result->guardian_email,
                trans('nosh.phone_home') => $result->guardian_phone_home,
                trans('nosh.phone_work') => $result->guardian_phone_work,
                trans('nosh.phone_cell') => $result->guardian_phone_cell
            ];
            $other_arr = [
                trans('nosh.preferred_provider') => $result->preferred_provider,
                trans('nosh.preferred_pharmacy') => $result->preferred_pharmacy,
                trans('nosh.other1') => $result->other1,
                trans('nosh.other2') => $result->other2,
                trans('nosh.comments') => $result->comments
            ];
            if ($result->pharmacy_address_id !== '' && $result->pharmacy_address_id !== null) {
                $pharmacy_query = DB::table('addressbook')->where('address_id', '=', $result->pharmacy_address_id)->first();
                $other_arr['Preferred Pharmacy'] = (is_object($pharmacy_query) ? $pharmacy_query->displayname : '');
            }
            $return = $this->header_build(trans('nosh.name_identity'));
            foreach ($identity_arr as $key1 => $value1) {
                if ($value1 !== '' && $value1 !== null) {
                    $return .= '<div class="row"><div class="col-md-3"><b>' . $key1 . '</b></div><div class="col-md-8">' . $value1 . '</div></div>';
                }
            }
            $return .= '</div></div>';
            $return .= $this->header_build(trans('nosh.contacts'));
            foreach ($contact_arr as $key2 => $value2) {
                if ($value2 !== '' && $value2 !== null) {
                    $return .= '<div class="row"><div class="col-md-3"><b>' . $key2 . '</b></div><div class="col-md-8">' . $value2 . '</div></div>';
                }
            }
            $return .= '</div></div>';
            $return .= $this->header_build(trans('nosh.guardians'));
            foreach ($guardian_arr as $key3 => $value3) {
                if ($value3 !== '' && $value3 !== null) {
                    $return .= '<div class="row"><div class="col-md-3"><b>' . $key3 . '</b></div><div class="col-md-8">' . $value3 . '</div></div>';
                }
            }
            $return .= '</div></div>';
            $return .= $this->header_build(trans('nosh.other'));
            foreach ($other_arr as $key4 => $value4) {
                if ($value4 !== '' && $value4 !== null) {
                    $return .= '<div class="row"><div class="col-md-3"><b>' . $key4 . '</b></div><div class="col-md-8">' . $value4 . '</div></div>';
                }
            }
            $return .= '</div></div>';
        } else {
            $return .= ' ' . trans('nosh.none') . '.';
        }
        $data['content'] = $return;
        $data['panel_header'] = trans('nosh.demographics');
        $data = array_merge($data, $this->sidebar_build('chart'));
        Session::put('last_page', $request->fullUrl());
        return view('home', $data);
    }

    public function document_delete(Request $request)
    {
        unlink(Session::get('file_path_temp'));
        Session::forget('file_path_temp');
        return 'true';
    }

    public function document_view(Request $request, $id)
    {
        $data['sidebar'] = 'documents';
        $pid = Session::get('pid');
        $result = DB::table('documents')->where('documents_id', '=', $id)->first();
        if ($result->documents_type == 'ccda' || $result->documents_type == 'ccr') {
            return redirect()->route('upload_ccda_view', [$id, 'issues']);
        }
        $practiceInfo = DB::table('practiceinfo')->first();
        $file_path = str_replace($practiceInfo->documents_dir, env('DOCUMENTS_DIR') . "/", $result->documents_url);
        $name = time() . '_' . $pid . '.pdf';
        $data['filepath'] = public_path() . '/temp/' . $name;
        copy($file_path, $data['filepath']);
        Session::put('file_path_temp', $data['filepath']);
        while(!file_exists($data['filepath'])) {
            sleep(2);
        }
        $data['document_url'] = asset('temp/' . $name);
        $dropdown_array = [];
        $items = [];
        $items[] = [
            'type' => 'item',
            'label' => trans('nosh.back'),
            'icon' => 'fa-chevron-left',
            'url' => Session::get('last_page')
        ];
        $dropdown_array['items'] = $items;
        $data['panel_dropdown'] = $this->dropdown_build($dropdown_array);
        $data['panel_header'] = date('Y-m-d', $this->human_to_unix($result->documents_date)) . ' - ' . $result->documents_desc . ' ' . trans('nosh.from1') . ' ' . $result->documents_from;
        $data = array_merge($data, $this->sidebar_build('chart'));
        return view('documents', $data);
    }

    public function documents_list(Request $request, $type)
    {
        $data['sidebar'] = 'documents';
        $data['message_action'] = Session::get('message_action');
        Session::forget('message_action');
        if ($type == 'All') {
            $query = DB::table('documents')->where('pid', '=', Session::get('pid'))->orderBy('documents_date', 'desc');
        } else {
            $query = DB::table('documents')->where('pid', '=', Session::get('pid'))->where('documents_type', '=', $type)->orderBy('documents_date', 'desc');
        }
        $type_arr = [
            'All' => [trans('nosh.all'), 'fa-files-o'],
            'Laboratory' => [trans('nosh.laboratory'), 'fa-flask'],
            'Imaging' => [trans('nosh.imaging'), 'fa-film'],
            'Cardiopulmonary' => [trans('nosh.cardiopulmonary'), 'fa-heartbeat'],
            'Endoscopy' => [trans('nosh.endoscopy'), 'fa-video-camera'],
            'Referrals' => [trans('nosh.referrals'), 'fa-hand-o-right'],
            'Past_Records' => [trans('nosh.past_records'), 'fa-folder'],
            'Other_Forms' => [trans('nosh.other_forms'), 'fa-file-o'],
            'Letters' => [trans('nosh.letters'), 'fa-file-text-o'],
            'Education' => [trans('nosh.education'), 'fa-info-circle'],
            'ccda' => [trans('nosh.ccda'), 'fa-file-code-o'],
            'ccr' => [trans('nosh.ccr'), 'fa-file-code-o'],
        ];
        $dropdown_array = [
            'items_button_text' => $type_arr[$type][0]
        ];
        foreach ($type_arr as $key => $value) {
            if ($key !== $type) {
                $items[] = [
                    'type' => 'item',
                    'label' => $value[0],
                    'icon' => $value[1],
                    'url' => route('documents_list', [$key])
                ];
            }
        }
        $dropdown_array['items'] = $items;
        $data['panel_dropdown'] = $this->dropdown_build($dropdown_array);
        $result = $query->get();
        $return = '';
        $columns = Schema::getColumnListing('documents');
        $row_index = $columns[0];
        if ($result->count()) {
            $list_array = [];
            foreach ($result as $row) {
                $arr = [];
                if (!empty($row->documents_type)) {
                    $arr['label'] = '<i class="fa ' . $type_arr[$row->documents_type][1] . ' fa-fw" style="margin-right:10px;"></i><b>' . date('Y-m-d', $this->human_to_unix($row->documents_date)) . '</b> - ' . $row->documents_desc . ' from ' . $row->documents_from;
                    $arr['view'] = route('document_view', [$row->$row_index]);
                    if ($row->reconcile !== null && $row->reconcile !== 'y') {
                        $arr['danger'] = true;
                    }
                    $list_array[] = $arr;
                } else {
                    // Clean up orphaned entries
                    if (file_exists($row->documents_url)) {
                        unlink($row->documents_url);
                    }
                    DB::table('documents')->where($row_index, '=', $row->$row_index)->delete();
                    $this->audit('Delete');
                }
            }
            $return .= $this->result_build($list_array, 'results_list', false, true);
        } else {
            $return .= ' ' . trans('nosh.none') . '.';
        }
        $data['content'] = $return;
        $data['documents_active'] = true;
        $data['panel_header'] = trans('nosh.documents');
        $data = array_merge($data, $this->sidebar_build('chart'));
        Session::put('last_page', $request->fullUrl());
        return view('home', $data);
    }

    public function download_ccda(Request $request, $action, $hippa_id)
    {
        if (Session::has('download_ccda')) {
            Session::forget('download_ccda');
            $pid = Session::get('pid');
            $practice_id = Session::get('practice_id');
            $file_name = time() . '_ccda_' . $pid . '_' . Session::get('user_id') . ".xml";
            $file_path = public_path() . '/temp/' . $file_name;
            $ccda = $this->generate_ccda($hippa_id);
            File::put($file_path, $ccda);
            $headers = [
                'Set-Cookie' => 'fileDownload=true; path=/',
                'Content-Type' => 'text/xml',
                'Cache-Control' => 'max-age=60, must-revalidate',
                'Content-Disposition' => 'attachment; filename="' . $file_name . '"'
            ];
            return response()->download($file_path, $file_name, $headers);
        } else {
            Session::put('download_ccda', $request->fullUrl());
            return redirect()->route('records_list', ['release']);
        }
    }

    public function encounter_close(Request $request)
    {
        Session::forget('eid');
        return redirect()->route('patient');
    }

    public function encounter_view(Request $request, $eid, $previous=false)
    {
        $data['sidebar'] = 'encounters';
        $data['message_action'] = Session::get('message_action');
        Session::forget('message_action');
        $encounter = DB::table('encounters')->where('eid', '=', $eid)->first();
        // Tags
        $tags_relate = DB::table('tags_relate')->where('eid', '=', $eid)->get();
        $tags_val_arr = [];
        if ($tags_relate->count()) {
            foreach ($tags_relate as $tags_relate_row) {
                $tags = DB::table('tags')->where('tags_id', '=', $tags_relate_row->tags_id)->first();
                $tags_val_arr[] = $tags->tag;
            }
        }
        $return = '';
        if ($encounter->encounter_signed == 'Yes') {
            $return .= '<div style="margin-bottom:15px;">';
            foreach ($tags_val_arr as $tag) {
                $return .= '<span class="badge badge-primary">' . $tag . '</span>';
            }
            $return .= '</div>';
            $dropdown_array = [
                'default_button_text' => '<i class="fa fa-chevron-left fa-fw fa-btn"></i>' . trans('nosh.back'),
                'default_button_text_url' => Session::get('last_page')
            ];
            $data['panel_dropdown'] = $this->dropdown_build($dropdown_array);
        }if ($previous == true) {
            $return .= $this->encounters_view($eid, Session::get('pid'), $encounter->practice_id, true, false)->render();
        } else {
            $return .= $this->encounters_view($eid, Session::get('pid'), $encounter->practice_id, true, true)->render();
        }
        $data['content'] = $return;
        $data['panel_header'] = trans('nosh.encounter') . ' - ' .  date('Y-m-d', $this->human_to_unix($encounter->encounter_DOS));
        $data = array_merge($data, $this->sidebar_build('chart'));
        return view('home', $data);
    }

    public function encounter_vitals_view(Request $request, $eid='')
    {
        $data['sidebar'] = 'encounters';
        $vitals_arr = $this->array_vitals();
        $practice = DB::table('practiceinfo')->where('practice_id', '=', Session::get('practice_id'))->first();
        $return = '<div class="table-responsive"><table class="table table-striped"><thead><tr><th>' . trans('nosh.date') . '</th>';
        foreach ($vitals_arr as $k => $v) {
            $return .= '<th class="nosh-graph" data-nosh-vitals-type="' . $k . '">' . $v['name'] . '</th>';
        }
        $return .= '</tr></thead><tbody>';
        $query = DB::table('vitals')->where('pid', '=', Session::get('pid'))->orderBy('vitals_date', 'desc')->get();
        if ($query->count()) {
            foreach ($query as $row) {
                $return .= '<tr';
                $class_arr = [];
                if ($eid == $row->eid) {
                    $class_arr[] = 'nosh-table-active';
                }
                if (!empty($class_arr)) {
                    $return .= ' class="' . implode(' ', $class_arr) . '"';
                }
                $return .= '><td>' . date('Y-m-d', $this->human_to_unix($row->vitals_date)) . '</td>';
                foreach ($vitals_arr as $vitals_key => $vitals_val) {
                    if (isset($vitals_val['min'])) {
                        if ($vitals_key == 'temp') {
                            if (($vitals_val['min'][$practice->temp_unit] <= $row->{$vitals_key}) && ($row->{$vitals_key} <= $vitals_val['max'][$practice->temp_unit])) {
                                $return .= '<td class="nosh-graph" data-nosh-vitals-type="' . $vitals_key . '">' . $row->{$vitals_key} . '</td>';
                            } elseif ($row->{$vitals_key} !== null && $row->{$vitals_key} !== '') {
                                $return .= '<td class="nosh-graph danger" data-nosh-vitals-type="' . $vitals_key . '">' . $row->{$vitals_key} . '</td>';
                            } else {
                                $return .= '<td class="nosh-graph" data-nosh-vitals-type="' . $vitals_key . '"></td>';
                            }
                        } else {
                            if (($vitals_val['min'] <= $row->{$vitals_key}) && ($row->{$vitals_key} <= $vitals_val['max'])) {
                                $return .= '<td class="nosh-graph" data-nosh-vitals-type="' . $vitals_key . '">' . $row->{$vitals_key} . '</td>';
                            } elseif ($row->{$vitals_key} !== null && $row->{$vitals_key} !== '') {
                                $return .= '<td class="nosh-graph danger" data-nosh-vitals-type="' . $vitals_key . '">' . $row->{$vitals_key} . '</td>';
                            } else {
                                $return .= '<td class="nosh-graph" data-nosh-vitals-type="' . $vitals_key . '"></td>';
                            }
                        }
                    } else {
                        $return .= '<td class="nosh-graph" data-nosh-vitals-type="' . $vitals_key . '">' . $row->{$vitals_key} . '</td>';
                    }
                }
            }
        }
        $return .= '</tbody></table></div>';
        $dropdown_array = [];
        $dropdown_array['default_button_text'] = '<i class="fa fa-chevron-left fa-fw fa-btn"></i>' . trans('nosh.back');
        if ($eid !== '') {
            $dropdown_array['default_button_text_url'] = route('encounter', [$eid, 'o']);
        } else {
            $dropdown_array['default_button_text_url'] = Session::get('last_page');
        }
        $data['panel_dropdown'] = $this->dropdown_build($dropdown_array);
        $data['content'] = $return;
        $data['panel_header'] = trans('nosh.vital_signs');
        $data = array_merge($data, $this->sidebar_build('chart'));
        return view('home', $data);
    }

    public function encounter_vitals_chart(Request $request, $type)
    {
        $data['sidebar'] = 'results';
        $pid = Session::get('pid');
        $demographics = DB::table('demographics')->where('pid', '=', $pid)->first();
        $vitals_arr = $this->array_vitals();
        $data['graph_y_title'] = $vitals_arr[$type]['name'] . ',' . $vitals_arr[$type]['unit'];
        $data['graph_x_title'] = trans('nosh.date');
        $data['graph_series_name'] = $vitals_arr[$type]['name'];
        $data['graph_title'] = trans('nosh.chart_of') . ' ' . $vitals_arr[$type]['name'] . ' ' . trans('nosh.over_time_for') . ' ' . $demographics->firstname . ' ' . $demographics->lastname . ' ' . trans('nosh.as_of') . ' ' . date("Y-m-d, g:i a", time());
        $query1 = DB::table('vitals')
            ->select($type, 'vitals_date')
            ->where('pid', '=', $pid)
            ->orderBy('vitals_date', 'asc')
            ->distinct()
            ->get();
        $json = [];
        if ($query1->count()) {
            foreach ($query1 as $row1) {
                if ($row1->{$type} !== null && $row1->{$type} !== '') {
                    $json[] = [
                        $row1->vitals_date,
                        $row1->{$type}
                    ];
                }
            }
        }
        $dropdown_array = [];
        $dropdown_array['default_button_text'] = '<i class="fa fa-chevron-left fa-fw fa-btn"></i>' . trans('nosh.back');
        if (Session::has('eid')) {
            $dropdown_array['default_button_text_url'] = route('encounter_vitals_view');
        } else {
            $dropdown_array['default_button_text_url'] = route('encounter_vitals_view', [Session::get('eid')]);
        }
        $data['panel_dropdown'] = $this->dropdown_build($dropdown_array);
        $data['graph_data'] = json_encode($json);
        $data['graph_type'] = 'data-to-time';
        $data['title'] = $vitals_arr[$type]['name'];
        $data = array_merge($data, $this->sidebar_build('chart'));
        return view('graph', $data);
    }

    public function encounters_list(Request $request)
    {
        $data['sidebar'] = 'encounters';
        $data['message_action'] = Session::get('message_action');
        Session::forget('message_action');
        $query = DB::table('encounters')->where('pid', '=', Session::get('pid'))
            ->where('addendum', '=', 'n')->orderBy('encounter_DOS', 'desc');
        if (Session::get('patient_centric') == 'n') {
            $query->where('practice_id', '=', Session::get('practice_id'));
        }
        $query->where('encounter_signed', '=', 'Yes');
        $result = $query->get();
        $return = '';
        $encounter_type = $this->array_encounter_type();
        if ($result->count()) {
            $list_array = [];
            foreach ($result as $row) {
                $arr = [];
                $arr['label'] = '<b>' . date('Y-m-d', $this->human_to_unix($row->encounter_DOS)) . '</b> - ' . $encounter_type[$row->encounter_template] . ' - ' . $row->encounter_cc . '<br>' . trans('nosh.provider') . ': ' . $row->encounter_provider;
            $arr['view'] = route('encounter_view', [$row->eid]);
                $list_array[] = $arr;
            }
            $return .= $this->result_build($list_array, 'results_list');
        } else {
            $return .= ' ' . trans('nosh.none') . '.';
        }
        $data['content'] = $return;
        $data['panel_header'] = trans('nosh.encounters');
        $data = array_merge($data, $this->sidebar_build('chart'));
        Session::put('last_page', $request->fullUrl());
        return view('home', $data);
    }

    public function family_history(Request $request)
    {
        $data['sidebar'] = 'family_history';
        $data['message_action'] = Session::get('message_action');
        Session::forget('message_action');
        $data['content'] = '';
        $data['panel_header'] = trans('nosh.family_history');
        $data = array_merge($data, $this->sidebar_build('chart'));
        Session::put('last_page', $request->fullUrl());
        return view('sigma', $data);
    }

    public function growth_chart(Request $request, $type)
    {
        $pid = Session::get('pid');
        $displayname = Session::get('displayname');
        $demographics = DB::table('demographics')->where('pid', '=', $pid)->first();
        $gender = Session::get('gender');
        $sex = 'f';
        if ($gender == 'male') {
            $sex = 'm';
        }
        $time = time();
        $dob = $this->human_to_unix($demographics->DOB);
        $pedsage = ($time - $dob);
        $datenow = date(DATE_RFC822, $time);
        if ($type == 'bmi-age') {
            $data = $this->gc_bmi_age($sex, $pid);
            $data['panel_header'] = trans('nosh.bmi_percentiles') . ' ' . $demographics->firstname . ' ' . $demographics->lastname . ' ' . trans('nosh.as_of') . ' ' . $datenow;
            $data['graph_type'] = 'growth-chart';
        }
        if ($type == 'weight-age') {
            $data = $this->gc_weight_age($sex, $pid);
            $data['panel_header'] = trans('nosh.wt_percentiles') . ' ' . $demographics->firstname . ' ' . $demographics->lastname . ' ' . trans('nosh.as_of') . ' ' . $datenow;
            $data['graph_type'] = 'growth-chart';
        }
        if ($type == 'height-age') {
            $data = $this->gc_height_age($sex, $pid);
            $data['panel_header'] = trans('nosh.ht_percentiles') . ' ' . $demographics->firstname . ' ' . $demographics->lastname . ' ' . trans('nosh.as_of') . ' ' . $datenow;
            $data['graph_type'] = 'growth-chart';
        }
        if ($type == 'head-age') {
            $data = $this->gc_head_age($sex, $pid);
            $data['panel_header'] = trans('nosh.hc_percentiles') . ' ' . $demographics->firstname . ' ' . $demographics->lastname . ' ' . trans('nosh.as_of') . ' ' . $datenow;
            $data['graph_type'] = 'growth-chart';
        }
        if ($type == 'weight-height') {
            $data = $this->gc_weight_height($sex, $pid);
            $data['panel_header'] = trans('nosh.wt_ht_percentiles') . ' ' . $demographics->firstname . ' ' . $demographics->lastname . ' ' . trans('nosh.as_of') . ' ' . $datenow;
            $data['graph_type'] = 'growth-chart1';
        }
        $data['patientname'] = $demographics->firstname . ' ' . $demographics->lastname;
        $data['sidebar'] = 'growth_chart';
        $data = array_merge($data, $this->sidebar_build('chart'));
        return view('graph', $data);
    }

    public function immunizations_csv(Request $request)
    {
        $pid = Session::get('pid');
        $result = DB::table('immunizations')
            ->join('demographics', 'demographics.pid', '=', 'immunizations.pid')
            ->join('insurance', 'insurance.pid' , '=', 'immunizations.pid')
            ->where('immunizations.pid', '=', $pid)
            ->where('insurance.insurance_plan_active', '=', 'Yes')
            ->where('insurance.insurance_order', '=', 'Primary')
            ->select('immunizations.pid', 'demographics.lastname', 'demographics.firstname', 'demographics.DOB', 'demographics.sex', 'demographics.address', 'demographics.city', 'demographics.state', 'demographics.zip', 'demographics.phone_home', 'immunizations.imm_cvxcode', 'immunizations.imm_elsewhere', 'immunizations.imm_date', 'immunizations.imm_lot', 'immunizations.imm_manufacturer', 'insurance.insurance_plan_name')
            ->get();
        $csv = '';
        if ($result->count()) {
            $csv .= "PatientID,Last,First,BirthDate,Gender,PatientAddress,City,State,Zip,Phone,ImmunizationCVX,OtherClinic,DateGiven,LotNumber,Manufacturer,InsuredPlanName";
            foreach ($result as $row1) {
                $row = (array) $row1;
                $row['DOB'] = date('m/d/Y', $this->human_to_unix($row['DOB']));
                $row['imm_date'] = date('m/d/Y', $this->human_to_unix($row['imm_date']));
                $row['sex'] = strtoupper($row['sex']);
                if ($row['imm_elsewhere'] == 'Yes') {
                    $row['imm_elsewhere'] = $row['imm_date'];
                } else {
                    $row['imm_elsewhere'] = '';
                }
                $csv .= "\n";
                $csv .= implode(',', $row);
            }
        }
        $file_path = public_path() . '/temp/' . time() . '_'. Session::get('user_id') . '_immunization_csv.txt';
        File::put($file_path, $csv);
        while(!file_exists($file_path)) {
            sleep(2);
        }
        Session::put('download_now', $file_path);
        return redirect(Session::get('last_page'));
    }

    public function immunizations_list(Request $request)
    {
        $data['sidebar'] = 'immunizations';
        $data['message_action'] = Session::get('message_action');
        Session::forget('message_action');
        $query = DB::table('immunizations')
            ->where('pid', '=', Session::get('pid'))
            ->orderBy('imm_immunization', 'asc')
            ->orderBy('imm_sequence', 'asc');
        $result = $query->get();
        $return = '';
        $notes = DB::table('demographics_notes')->where('pid', '=', Session::get('pid'))->where('practice_id', '=', Session::get('practice_id'))->first();
        if (!empty($notes->imm_notes)) {
            $return .= '<div class="alert alert-success"><h5>' . trans('nosh.imm_notes') . '</h5>';
            $return .= nl2br($notes->imm_notes);
            $return .= '</div>';
        }
        $columns = Schema::getColumnListing('immunizations');
        $row_index = $columns[0];
        if ($result->count()) {
            $list_array = [];
            $seq_array = [
                '1' => ', ' . lcfirst(trans('nosh.first')),
                '2' => ', ' . lcfirst(trans('nosh.second')),
                '3' => ', ' . lcfirst(trans('nosh.third')),
                '4' => ', ' . lcfirst(trans('nosh.fourth')),
                '5' => ', ' . lcfirst(trans('nosh.fifth'))
            ];
            foreach ($result as $row) {
                $arr = [];
                $arr['label'] = '<b>' . $row->imm_immunization . '</b> - ' . date('Y-m-d', $this->human_to_unix($row->imm_date));
                if (isset($row->imm_sequence)) {
                    if (isset($seq_array[$row->imm_sequence])) {
                        $arr['label'] = '<b>' . $row->imm_immunization . $seq_array[$row->imm_sequence]  . '</b> - ' . date('Y-m-d', $this->human_to_unix($row->imm_date));
                    }
                }
                if ($row->reconcile !== null && $row->reconcile !== 'y') {
                    $arr['danger'] = true;
                }
                $list_array[] = $arr;
            }
            $return .= $this->result_build($list_array, 'results_list');
        } else {
            $return .= ' ' . trans('nosh.none') . '.';
        }
        $data['content'] = $return;
        $data['panel_header'] = trans('nosh.immunizations');
        $data = array_merge($data, $this->sidebar_build('chart'));
        if (Session::has('download_now')) {
            $data['download_now'] = route('download_now');
        }
        Session::put('last_page', $request->fullUrl());
        return view('home', $data);
    }

    public function immunizations_print()
    {
        ini_set('memory_limit','196M');
        $html = $this->page_immunization_list()->render();
        $user_id = Session::get('user_id');
        $file_path = public_path() . "/temp/" . time() . "_imm_list_" . $user_id . ".pdf";
        $this->generate_pdf($html, $file_path);
        while(!file_exists($file_path)) {
            sleep(2);
        }
        Session::put('download_now', $file_path);
        return redirect(Session::get('last_page'));
    }

    public function medications_list(Request $request, $type)
    {
        $data['sidebar'] = 'medications';
        $data['message_action'] = Session::get('message_action');
        Session::forget('message_action');
        $query = DB::table('rx_list')->where('pid', '=', Session::get('pid'))->orderBy('rxl_medication', 'asc');
        if ($type == 'active') {
            $query->where('rxl_date_inactive', '=', '0000-00-00 00:00:00')->where('rxl_date_old', '=', '0000-00-00 00:00:00');
            $dropdown_array = [
                'items_button_text' => trans('nosh.active')
            ];
            $items[] = [
                'type' => 'item',
                'label' => trans('nosh.inactive'),
                'icon' => 'fa-times',
                'url' => route('medications_list', ['inactive'])
            ];
        } else {
            $query->where('rxl_date_inactive', '!=', '0000-00-00 00:00:00');
            $dropdown_array = [
                'items_button_text' => trans('nosh.inactive')
            ];
            $items[] = [
                'type' => 'item',
                'label' => trans('nosh.active'),
                'icon' => 'fa-check',
                'url' => route('medications_list', ['active'])
            ];
        }
        $dropdown_array['items'] = $items;
        $data['panel_dropdown'] = $this->dropdown_build($dropdown_array);
        $result = $query->get();
        $return = '';
        $columns = Schema::getColumnListing('rx_list');
        $row_index = $columns[0];
        $list_array = [];
        if ($result->count()) {
            foreach ($result as $row) {
                $arr = [];
                if ($row->rxl_sig == '') {
                    $arr['label'] = '<strong>' . $row->rxl_medication . '</strong> ' . $row->rxl_dosage . ' ' . $row->rxl_dosage_unit . ', ' . $row->rxl_instructions . ' ' . trans('nosh.for') . ' ' . $row->rxl_reason;
                } else {
                    $arr['label'] = '<strong>' . $row->rxl_medication . '</strong> ' . $row->rxl_dosage . ' ' . $row->rxl_dosage_unit . ', ' . $row->rxl_sig . ', ' . $row->rxl_route . ', ' . $row->rxl_frequency;
                    $arr['label'] .= ' ' . trans('nosh.for') . ' ' . $row->rxl_reason;
                }
                $previous = DB::table('rx_list')
        			->where('pid', '=', Session::get('pid'))
        			->where('rxl_medication', '=', $row->rxl_medication)
        			->select('rxl_date_prescribed', 'prescription')
                    ->orderBy('rxl_date_prescribed', 'desc')
        			->first();
                if ($previous) {
                    if ($previous->rxl_date_prescribed !== null && $previous->rxl_date_prescribed !== '0000-00-00 00:00:00') {
                        $previous_date = new Date($this->human_to_unix($previous->rxl_date_prescribed));
                        $ago = $previous_date->diffInDays();
                        $arr['label'] .= '<br><strong>' . trans('nosh.last_prescribed') . ':</strong> ' . date('Y-m-d', $this->human_to_unix($previous->rxl_date_prescribed)) . ', ' . $ago . ' ' . trans('nosh.days_ago');
                        // $arr['label'] .= '<br><strong>Prescription Status:</strong> ' . ucfirst($previous->prescription);
                    }
                }
                if ($row->reconcile !== null && $row->reconcile !== 'y') {
                    $arr['danger'] = true;
                }
                $list_array[] = $arr;
            }
            $return .= $this->result_build($list_array, 'results_list');
        } else {
            $return .= ' ' . trans('nosh.none') . '.';
        }
        $data['content'] = $return;
        $data['panel_header'] = trans('nosh.medications');
        $data = array_merge($data, $this->sidebar_build('chart'));
        Session::put('last_page', $request->fullUrl());
        return view('home', $data);
    }

    public function patient(Request $request)
    {
        $this->setpatient(Session::get('pid'));
        $data['sidebar'] = 'demographics';
        $data['message_action'] = Session::get('message_action');
        Session::forget('message_action');
        $data['content'] = '';
        $demographics = DB::table('demographics')->where('pid', '=', Session::get('pid'))->first();
        $demographics_plus = DB::table('demographics_plus')->where('pid', '=', Session::get('pid'))->first();
        $practiceInfo = DB::table('practiceinfo')->first();
        if ($demographics->photo !== null) {
            $photo = str_replace($practiceInfo->documents_dir, env('DOCUMENTS_DIR') . "/", $demographics->photo);
            if (file_exists($photo)) {
                $directory = env('DOCUMENTS_DIR') . "/" . Session::get('pid') . "/";
                $new_directory = public_path() . '/temp/';
                $new_directory1 = '/temp/';
                $file_path = str_replace($directory, $new_directory, $photo);
                $file_path1 = str_replace($directory, $new_directory1, $photo);
                copy($photo, $file_path);
                $data['content'] .= HTML::image($file_path1, 'Image', array('border' => '0', 'style' => 'display:block;margin:auto;max-height: 200px;width: auto;'));
                $data['content'] .= '<br>';
            }
        }
        if (!empty($demographics_plus->date_added)) {
            $data['content'] .= '<div class="alert alert-success"><span style="margin-right:15px;"><i class="fa fa-user fa-lg" aria-hidden="true"></i></span><strong>' . trans('nosh.date_added') . '</strong>: ' . date('F jS, Y', strtotime($demographics_plus->date_added)) . '</div>';
        }
        $arr = $this->timeline();
        $data['content'] .= '<h4 style="text-align:center;">' . trans('nosh.timeline') . '</h4>';
        if (count($arr['json']) <1) {
            $data['content'] .= '<div class="alert alert-success"><p><span style="margin-right:15px;"><i class="fa fa-star-o fa-lg"></i></span><strong>' . trans('nosh.account_created') . '!</strong></p>';
            $data['content'] .='</div>';
        } else {
            $data['content'] .= '<section id="cd-timeline" class="cd-container">';
            foreach ($arr['json'] as $item) {
                $data['content'] .= $item['div'];
            }
            $data['content'] .= '</section>';
        }
        $data['panel_header'] = Session::get('ptname');
        if ($demographics->nickname !== '' && $demographics->nickname !== null) {
            $data['panel_header'] .= ' (' . $demographics->nickname . ')';
        }
        $data['panel_header'] .= ', ' . Session::get('age') . ', ' . ucfirst(Session::get('gender'));
        $data = array_merge($data, $this->sidebar_build('chart'));
        Session::put('last_page', $request->fullUrl());
        return view('home', $data);
    }

    public function print_chart_all()
    {
        $file_path = $this->print_chart(Session::get('pid'));
        return response()->download($file_path);
    }

    public function orders_list(Request $request, $type)
    {
        $data['sidebar'] = 'orders';
        $data['message_action'] = Session::get('message_action');
        Session::forget('message_action');
        $query = DB::table('orders')->where('pid', '=', Session::get('pid'))->where($type, '!=', '')->orderBy('orders_date', 'desc');
        $type_arr = [
            'orders_labs' => [trans('nosh.laboratory'), 'fa-flask'],
            'orders_radiology' => [trans('nosh.imaging'), 'fa-film'],
            'orders_cp' => [trans('nosh.cardiopulmonary'), 'fa-heartbeat'],
            'orders_referrals' => [trans('nosh.referrals'), 'fa-hand-o-right']
        ];
        $dropdown_array = [
            'items_button_text' => $type_arr[$type][0]
        ];
        foreach ($type_arr as $key => $value) {
            if ($key !== $type) {
                $items[] = [
                    'type' => 'item',
                    'label' => $value[0],
                    'icon' => $value[1],
                    'url' => route('orders_list', [$key])
                ];
            }
        }
        $dropdown_array['items'] = $items;
        $data['panel_dropdown'] = $this->dropdown_build($dropdown_array);
        $result = $query->get();
        $return = '';
        $columns = Schema::getColumnListing('orders');
        $row_index = $columns[0];
        if ($result->count()) {
            $list_array = [];
            foreach ($result as $row) {
                $arr = [];
                $arr['label'] = '<b>' . date('Y-m-d', $this->human_to_unix($row->orders_date)) . '</b> - ' . $row->{$type};
                if ($type == 'orders_referrals') {
                    $address = DB::table('addressbook')->where('address_id', '=', $row->address_id)->first();
                    $arr['label'] = '<b>' . date('Y-m-d', $this->human_to_unix($row->orders_date)) . '</b> - ' . $address->specialty . ': ' . $address->displayname;
                }
                $list_array[] = $arr;
            }
            $return .= $this->result_build($list_array, 'results_list');
        } else {
            $return .= ' ' . trans('nosh.none') . '.';
        }
        $data['content'] = $return;
        $data['panel_header'] = trans('nosh.pending_orders');
        $data = array_merge($data, $this->sidebar_build('chart'));
        Session::put('last_page', $request->fullUrl());
        return view('home', $data);
    }

    public function records_list(Request $request, $type)
    {
        $data['sidebar'] = 'records_list';
        $data['message_action'] = Session::get('message_action');
        Session::forget('message_action');
        if ($type == 'release') {
            $table = 'hippa';
            $query = DB::table($table)->where('pid', '=', Session::get('pid'))->where('practice_id', '=', Session::get('practice_id'))->where('other_hippa_id', '=', '0')->orderBy('hippa_date_release', 'desc');
            $dropdown_array = [
                'items_button_text' => trans('nosh.hippas')
            ];
            $items[] = [
                'type' => 'item',
                'label' => trans('nosh.hippa_requests'),
                'icon' => 'fa-arrow-down',
                'url' => route('records_list', ['request'])
            ];
        } else {
            $table ='hippa_request';
            $query = DB::table($table)->where('pid', '=', Session::get('pid'))->where('practice_id', '=', Session::get('practice_id'))->orderBy('hippa_date_request', 'desc');
            $dropdown_array = [
                'items_button_text' => trans('nosh.hippa_requests')
            ];
            $items[] = [
                'type' => 'item',
                'label' => trans('nosh.hippas'),
                'icon' => 'fa-arrow-up',
                'url' => route('records_list', ['release'])
            ];
        }
        $dropdown_array['items'] = $items;
        $data['panel_dropdown'] = $this->dropdown_build($dropdown_array);
        $result = $query->get();
        $return = '';
        $columns = Schema::getColumnListing($table);
        $row_index = $columns[0];
        if ($result->count()) {
            $list_array = [];
            foreach ($result as $row) {
                $arr = [];
                if ($type == 'release') {
                    $arr['label'] = '<b>' . date('Y-m-d', $this->human_to_unix($row->hippa_date_release)) . '</b> - ' . $row->hippa_provider . ' - ' . $row->hippa_reason;
                } else {
                    $arr['label'] = '<b>' . date('Y-m-d', $this->human_to_unix($row->hippa_date_request)) . '</b> - ' . $row->request_to . ' - ' . $row->request_reason;
                    if ($row->received == 'Yes') {
                        $arr['label_class'] = 'list-group-item-success nosh-result-list';
                    }
                }
                $list_array[] = $arr;
            }
            $return .= $this->result_build($list_array, 'results_list');
        } else {
            $return .= ' ' . trans('nosh.none') . '.';
        }
        $data['panel_header'] = trans('nosh.coordination_of_care');
        $data['content'] = $return;
        $data = array_merge($data, $this->sidebar_build('chart'));
        Session::put('last_page', $request->fullUrl());
        if (Session::has('download_ccda')) {
            $data['download_now'] = Session::get('download_ccda');
        }
        return view('home', $data);
    }

    public function results_chart(Request $request, $id)
    {
        $data['sidebar'] = 'results';
        $pid = Session::get('pid');
        $demographics = DB::table('demographics')->where('pid', '=', $pid)->first();
        $row0 = DB::table('tests')->where('tests_id', '=', $id)->first();
        $data['graph_y_title'] = $row0->test_units;
        $data['graph_x_title'] = 'Date';
        $data['graph_series_name'] = $row0->test_name;
        $data['graph_title'] = trans('nosh.results_chart1') . ' ' . $row0->test_name . ' ' . trans('nosh.results_chart2') . ' ' . $demographics->firstname . ' ' . $demographics->lastname . ' ' . trans('nosh.as_of') . ' ' . date("Y-m-d, g:i a", time());
        $query1 = DB::table('tests')
            ->where('test_name', '=', $row0->test_name)
            ->where('pid', '=', $pid)
            ->orderBy('test_datetime', 'asc')
            ->get();
        $json = [];
        if ($query1->count()) {
            foreach ($query1 as $row1) {
                $json[] = [
                    $row1->test_datetime,
                    $row1->test_result
                ];
            }
        }
        $dropdown_array = [];
        $dropdown_array['default_button_text'] = '<i class="fa fa-chevron-left fa-fw fa-btn"></i>' . trans('nosh.back');
        $dropdown_array['default_button_text_url'] = Session::get('last_page');
        $items = [];
        $items[] = [
            'type' => 'item',
            'label' => trans('nosh.print'),
            'icon' => 'fa-print',
            'url' => route('results_print', [$id])
        ];
        $dropdown_array['items'] = $items;
        $data['panel_dropdown'] = $this->dropdown_build($dropdown_array);
        $data['graph_data'] = json_encode($json);
        $data['graph_type'] = 'data-to-time';
        $data['results_active'] = true;
        $data['title'] = $row0->test_name;
        $data = array_merge($data, $this->sidebar_build('chart'));
        return view('graph', $data);
    }

    public function results_list(Request $request, $type)
    {
        $data['sidebar'] = 'results';
        $data['message_action'] = Session::get('message_action');
        Session::forget('message_action');
        $query = DB::table('tests')->where('pid', '=', Session::get('pid'))->where('test_type', '=', $type)->orderBy('test_datetime', 'desc');
        $type_arr = [
            'Laboratory' => [trans('nosh.laboratory'), 'fa-flask'],
            'Imaging' => [trans('nosh.imaging'), 'fa-film'],
        ];
        $dropdown_array = [
            'items_button_text' => $type_arr[$type][0]
        ];
        foreach ($type_arr as $key => $value) {
            if ($key !== $type) {
                $items[] = [
                    'type' => 'item',
                    'label' => $value[0],
                    'icon' => $value[1],
                    'url' => route('results_list', [$key])
                ];
            }
        }
        $items[] = [
            'type' => 'item',
            'label' => trans('nosh.vital_signs'),
            'icon' => 'fa-eye',
            'url' => route('encounter_vitals_view')
        ];
        $dropdown_array['items'] = $items;
        $data['panel_dropdown'] = $this->dropdown_build($dropdown_array);
        $result = $query->get();
        $return = '';
        $columns = Schema::getColumnListing('tests');
        $row_index = $columns[0];
        if ($result->count()) {
            $list_array = [];
            foreach ($result as $row) {
                $arr = [];
                $arr['label'] = '<b>' . date('Y-m-d', $this->human_to_unix($row->test_datetime)) . '</b> - ' . $row->test_name;
                $arr['view'] = route('results_view', [$row->$row_index]);
                $arr['chart'] = route('results_chart', [$row->$row_index]);
                $list_array[] = $arr;
            }
            $return .= $this->result_build($list_array, 'results_list', false, true);
        } else {
            $return .= ' ' . trans('nosh.none') . '.';
        }
        $data['content'] = $return;
        $data['panel_header'] = trans('nosh.results');
        $data = array_merge($data, $this->sidebar_build('chart'));
        Session::put('last_page', $request->fullUrl());
        return view('home', $data);
    }

    public function results_print(Request $request, $id)
    {
        ini_set('memory_limit','196M');
        $html = $this->page_results_list($id)->render();
        $user_id = Session::get('user_id');
        $file_path = public_path() . "/temp/" . time() . "_results_list_" . $user_id . ".pdf";
        $this->generate_pdf($html, $file_path);
        while(!file_exists($file_path)) {
            sleep(2);
        }
        return response()->download($file_path);
    }

    public function results_view(Request $request, $id)
    {
        $data['sidebar'] = 'results';
        $data['message_action'] = Session::get('message_action');
        Session::forget('message_action');
        $test_arr = $this->array_test_flag();
        $test = DB::table('tests')->where('tests_id', '=', $id)->first();
        $return = '<div class="table-responsive"><table class="table table-striped"><thead><tr><th>' . trans('nosh.date') . '</th><th>' . trans('nosh.test_result') . '</th><th>' . trans('nosh.unit') . '</th><th>' . trans('nosh.range') . '</th><th>' . trans('nosh.test_flags') . '</th></thead><tbody>';
        // Get old results for comparison table
        $query = DB::table('tests')
            ->where('test_name', '=', $test->test_name)
            ->where('pid', '=', Session::get('pid'))
            ->orderBy('test_datetime', 'desc')
            ->get();
        if ($query->count()) {
            foreach ($query as $row) {
                $return .= '<tr';
                $class_arr = [];
                if ($id == $row->tests_id) {
                    $class_arr[] = 'nosh-table-active';
                }
                if ($row->test_flags == "HH" || $row->test_flags == "LL" || $row->test_flags == "H" || $row->test_flags == "L") {
                    $class_arr[] = 'danger';
                }
                if (!empty($class_arr)) {
                    $return .= ' class="' . implode(' ', $class_arr) . '"';
                }
                $return .= '><td>' . date('Y-m-d', $this->human_to_unix($row->test_datetime)) . '</td>';
                $return .= '<td>' . $row->test_result . '</td>';
                $return .= '<td>' . $row->test_units . '</td>';
                $return .= '<td>' . $row->test_reference . '</td>';
                $return .= '<td>' . $test_arr[$row->test_flags] . '</td></tr>';
            }
        }
        $return .= '</tbody></table></div>';
        $dropdown_array = [];
        $dropdown_array['default_button_text'] = '<i class="fa fa-chevron-left fa-fw fa-btn"></i>' . trans('nosh.back');
        $dropdown_array['default_button_text_url'] = Session::get('last_page');
        $items = [];
        $items[] = [
            'type' => 'item',
            'label' => trans('nosh.chart'),
            'icon' => 'fa-line-chart',
            'url' => route('results_chart', [$id])
        ];
        $items[] = [
            'type' => 'item',
            'label' => trans('nosh.print'),
            'icon' => 'fa-print',
            'url' => route('results_print', [$id])
        ];
        $items[] = [
            'type' => 'separator'
        ];
        $dropdown_array['items'] = $items;
        $data['panel_dropdown'] = $this->dropdown_build($dropdown_array);
        $data['content'] = $return;
        $data['title'] = $test->test_name;
        $data = array_merge($data, $this->sidebar_build('chart'));
        return view('home', $data);
    }

    public function search_chart(Request $request)
    {
        $data['message_action'] = Session::get('message_action');
        Session::forget('message_action');
        $return = '';
        if ($request->isMethod('post')) {
            $q = $request->input('search_chart');
            Session::put('search_chart', $q);
        } else {
            $q = Session::get('search_chart');
            Session::forget('search_chart');
        }
        $allergies = DB::table('allergies')
            ->where('pid', '=', Session::get('pid'))
            ->where('allergies_date_inactive', '=', '0000-00-00 00:00:00')
            ->where(function($allergies1) use ($q) {
                $allergies1->where('allergies_med', 'LIKE', "%$q%")
                ->orWhere('allergies_reaction', 'LIKE', "%$q%");
            })
            ->get();
        $issues = DB::table('issues')
            ->where('pid', '=', Session::get('pid'))
            ->where('issue_date_inactive', '=', '0000-00-00 00:00:00')
            ->where(function($issues1) use ($q) {
                $issues1->where('issue', 'LIKE', "%$q%")
                ->orWhere('notes', 'LIKE', "%$q%");
            })
            ->get();
        $rx = DB::table('rx_list')
            ->where('pid', '=', Session::get('pid'))
            ->where('rxl_date_inactive', '=', '0000-00-00 00:00:00')
            ->where('rxl_date_old', '=', '0000-00-00 00:00:00')
            ->where(function($rx1) use ($q) {
                $rx1->where('rxl_medication', 'LIKE', "%$q%")
                ->orWhere('rxl_sig', 'LIKE', "%$q%")
                ->orWhere('rxl_reason', 'LIKE', "%$q%")
                ->orWhere('rxl_instructions', 'LIKE', "%$q%");
            })
            ->get();
        $sup = DB::table('sup_list')
            ->where('pid', '=', Session::get('pid'))
            ->where('sup_date_inactive', '=', '0000-00-00 00:00:00')
            ->where(function($sup1) use ($q) {
                $sup1->where('sup_supplement', 'LIKE', "%$q%")
                ->orWhere('sup_sig', 'LIKE', "%$q%")
                ->orWhere('sup_reason', 'LIKE', "%$q%")
                ->orWhere('sup_instructions', 'LIKE', "%$q%");
            })
            ->get();
        $imm = DB::table('immunizations')
            ->where('pid', '=', Session::get('pid'))
            ->where(function($imm1) use ($q) {
                $imm1->where('imm_immunization', 'LIKE', "%$q%")
                ->orWhere('imm_sequence', 'LIKE', "%$q%")
                ->orWhere('imm_manufacturer', 'LIKE', "%$q%");
            })
            ->get();
        $orders = DB::table('orders')
            ->where('pid', '=', Session::get('pid'))
            ->where(function($orders1) use ($q) {
                $orders1->where('orders_labs', 'LIKE', "%$q%")
                ->orWhere('orders_radiology', 'LIKE', "%$q%")
                ->orWhere('orders_cp', 'LIKE', "%$q%")
                ->orWhere('orders_referrals', 'LIKE', "%$q%")
                ->orWhere('orders_notes', 'LIKE', "%$q%");
            })
            ->get();
        $encounters = DB::table('encounters')
            ->join('assessment', 'assessment.eid', '=', 'encounters.eid')
            ->join('image', 'image.eid', '=', 'encounters.eid')
            ->join('pe', 'pe.eid', '=', 'encounters.eid')
            ->join('plan', 'plan.eid', '=', 'encounters.eid')
            ->join('procedure', 'procedure.eid', '=', 'encounters.eid')
            ->join('ros', 'ros.eid', '=', 'encounters.eid')
            ->join('rx', 'rx.eid', '=', 'encounters.eid')
            ->join('vitals', 'vitals.eid', '=', 'encounters.eid')
            ->select('encounters.eid', 'encounters.encounter_DOS', 'encounters.encounter_type', 'encounters.encounter_cc', 'encounters.encounter_provider', 'encounters.encounter_template', 'encounters.encounter_signed')
            ->where('encounters.pid', '=', Session::get('pid'))
            ->where('encounters.addendum', '=', 'n')
            ->where(function($encounters1) use ($q) {
                $encounters1->where('encounters.encounter_type', 'LIKE', "%$q%")
                ->orWhere('encounters.encounter_cc', 'LIKE', "%$q%")
                ->orWhere('assessment.assessment_1', 'LIKE', "%$q%")
                ->orWhere('assessment.assessment_2', 'LIKE', "%$q%")
                ->orWhere('assessment.assessment_3', 'LIKE', "%$q%")
                ->orWhere('assessment.assessment_4', 'LIKE', "%$q%")
                ->orWhere('assessment.assessment_5', 'LIKE', "%$q%")
                ->orWhere('assessment.assessment_6', 'LIKE', "%$q%")
                ->orWhere('assessment.assessment_7', 'LIKE', "%$q%")
                ->orWhere('assessment.assessment_8', 'LIKE', "%$q%")
                ->orWhere('assessment.assessment_9', 'LIKE', "%$q%")
                ->orWhere('assessment.assessment_10', 'LIKE', "%$q%")
                ->orWhere('assessment.assessment_11', 'LIKE', "%$q%")
                ->orWhere('assessment.assessment_12', 'LIKE', "%$q%")
                ->orWhere('assessment.assessment_other', 'LIKE', "%$q%")
                ->orWhere('assessment.assessment_ddx', 'LIKE', "%$q%")
                ->orWhere('assessment.assessment_notes', 'LIKE', "%$q%")
                ->orWhere('image.image_description', 'LIKE', "%$q%")
                ->orWhere('pe.pe', 'LIKE', "%$q%")
                ->orWhere('plan.plan', 'LIKE', "%$q%")
                ->orWhere('plan.goals', 'LIKE', "%$q%")
                ->orWhere('plan.tp', 'LIKE', "%$q%")
                ->orWhere('procedure.proc_description', 'LIKE', "%$q%")
                ->orWhere('ros.ros', 'LIKE', "%$q%")
                ->orWhere('rx.rx_rx', 'LIKE', "%$q%")
                ->orWhere('rx.rx_supplements', 'LIKE', "%$q%")
                ->orWhere('rx.rx_immunizations', 'LIKE', "%$q%")
                ->orWhere('rx.rx_orders_summary', 'LIKE', "%$q%")
                ->orWhere('rx.rx_supplements_orders_summary', 'LIKE', "%$q%")
                ->orWhere('vitals.vitals_other', 'LIKE', "%$q%");
            })
            ->get();
        $notes = DB::table('demographics_notes')->where('pid', '=', Session::get('pid'))->where('imm_notes', 'LIKE', "%$q%")->get();
        $notes1 = DB::table('demographics_notes')->where('pid', '=', Session::get('pid'))->where('billing_notes', 'LIKE', "%$q%")->get();
        $documents = DB::table('documents')
            ->where('pid', '=', Session::get('pid'))
            ->where(function($documents1) use ($q) {
                $documents1->where('documents_desc', 'LIKE', "%$q%")
                ->orWhere('documents_from', 'LIKE', "%$q%");
            })
            ->get();
        $tests = DB::table('tests')
            ->where('pid', '=', Session::get('pid'))
            ->where(function($tests1) use ($q) {
                $tests1->where('test_name', 'LIKE', "%$q%")
                ->orWhere('test_from', 'LIKE', "%$q%");
            })
            ->get();
        $alerts = DB::table('alerts')
            ->where('pid', '=', Session::get('pid'))
            ->where('practice_id', '=', Session::get('practice_id'))
            ->where('alert_date_complete', '=', '0000-00-00 00:00:00')
            ->where('alert_reason_not_complete', '=', '')
            ->where(function($alerts1) use ($q) {
                $alerts1->where('alert', 'LIKE', "%$q%")
                ->orWhere('alert_description', 'LIKE', "%$q%");
            })
            ->get();
        $t_messages_query = DB::table('t_messages')->where('pid', '=', Session::get('pid'));
        if (Session::get('patient_centric') == 'n') {
            $t_messages_query->where('practice_id', '=', Session::get('practice_id'));
        }
        if (Session::get('group_id') == '100') {
            $t_messages_query->where('t_messages_signed', '=', 'Yes');
        }
        $t_messages = $t_messages_query->where(function($t_messages_query1) use ($q) {
            $t_messages_query1->where('t_messages_subject', 'LIKE', "%$q%")
            ->orWhere('t_messages_message', 'LIKE', "%$q%");
            })->get();
        $demographics = DB::table('demographics')
            ->where('pid', '=', Session::get('pid'))
            ->where(function($demographics1) use ($q) {
                $demographics1->where('firstname', 'LIKE', "%$q%")
                ->orWhere('lastname', 'LIKE', "%$q%")
                ->orWhere('nickname', 'LIKE', "%$q%")
                ->orWhere('race', 'LIKE', "%$q%")
                ->orWhere('ethnicity', 'LIKE', "%$q%")
                ->orWhere('language', 'LIKE', "%$q%")
                ->orWhere('employer', 'LIKE', "%$q%");
            })
            ->get();
        $demographics_a = DB::table('demographics')
            ->where('pid', '=', Session::get('pid'))
            ->where(function($demographics_a1) use ($q) {
                $demographics_a1->where('address', 'LIKE', "%$q%")
                ->orWhere('city', 'LIKE', "%$q%")
                ->orWhere('email', 'LIKE', "%$q%")
                ->orWhere('emergency_contact', 'LIKE', "%$q%");
            })
            ->get();
        $demographics_b = DB::table('demographics')
            ->where('pid', '=', Session::get('pid'))
            ->where(function($demographics_b1) use ($q) {
                $demographics_b1->where('guardian_firstname', 'LIKE', "%$q%")
                ->orWhere('guardian_lastname', 'LIKE', "%$q%")
                ->orWhere('guardian_address', 'LIKE', "%$q%")
                ->orWhere('guardian_city', 'LIKE', "%$q%");
            })
            ->get();
        $demographics_c = DB::table('demographics')
            ->where('pid', '=', Session::get('pid'))
            ->where(function($demographics_c1) use ($q) {
                $demographics_c1->where('preferred_pharmacy', 'LIKE', "%$q%")
                ->orWhere('comments', 'LIKE', "%$q%")
                ->orWhere('other1', 'LIKE', "%$q%")
                ->orWhere('other2', 'LIKE', "%$q%");
            })
            ->get();
        $tags = DB::table('tags')->where('tag', 'LIKE', "%$q%")->get();
        $encounters_arr = [];
        $t_messages_arr = [];
        $documents_arr = [];
        $tests_arr = [];
        if ($tags->count()) {
            foreach ($tags as $tag) {
                $tags_query = DB::table('tags_relate')->where('tags_id', '=', $tag->tags_id)->where('pid', '=', Session::get('pid'))->get();
                if ($tags_query->count()) {
                    foreach ($tags_query as $tags_row) {
                        if ($tags_row->eid !== null && $tags_row->eid !== '') {
                            $encounters_arr[] = $tags_row->eid;
                        }
                        if ($tags_row->t_messages_id !== null && $tags_row->t_messages_id !== '') {
                            $t_messages_arr[] = $tags_row->t_messages_id;
                        }
                        if ($tags_row->documents_id !== null && $tags_row->documents_id !== '') {
                            $documents_arr[] = $tags_row->documents_id;
                        }
                        if ($tags_row->tests_id !== null && $tags_row->tests_id !== '') {
                            $tests_arr[] = $tags_row->tests_id;
                        }
                    }
                }
            }
        }
        if ($allergies->count() || $issues->count() || $rx->count() || $sup->count() || $imm->count() || $orders->count() || $encounters->count() || $notes->count() || $notes1->count() || $documents->count() || $tests->count() || $alerts->count() || $t_messages->count() || $demographics->count() || $demographics_a->count() || $demographics_b->count() || $demographics_c->count() || ! empty($encounters_arr) || ! empty($t_messages_arr) || ! empty($documents_arr) || ! empty($tests_arr)) {
            $list_array = [];
            $encounter_type = $this->array_encounter_type();
            if ($encounters->count()) {
                foreach ($encounters as $encounters_row) {
                    $arr = [];
                    $arr['label'] = '<b>' . trans('nosh.encounter') . ' - ' . date('Y-m-d', $this->human_to_unix($encounters_row->encounter_DOS)) . '</b> - ' . $encounter_type[$encounters_row->encounter_template] . ' - ' . $encounters_row->encounter_cc . '<br>' . trans('nosh.provider') . ': ' . $encounters_row->encounter_provider;
                    $arr['view'] = route('encounter_view', [$encounters_row->eid]);
                    $list_array[] = $arr;
                }
            }
            if (! empty($encounters_arr)) {
                foreach ($encounters_arr as $encounters_item) {
                    $encounters_row1 = DB::table('encounters')->where('eid', '=', $encounters_item)->first();
                    $arr = [];
                    $arr['label'] = '<b>' . trans('nosh.tagged_encounter') . ' - ' . date('Y-m-d', $this->human_to_unix($encounters_row1->encounter_DOS)) . '</b> - ' . $encounter_type[$encounters_row1->encounter_template] . ' - ' . $encounters_row1->encounter_cc . '<br>' . trans('nosh.provider') . ': ' . $encounters_row1->encounter_provider;
                    $arr['view'] = route('encounter_view', [$encounters_row1->eid]);
                    $list_array[] = $arr;
                }
            }
            if ($issues->count()) {
                $issue_arr = [
                    'Problem List' => 'pl',
                    'Medical History' => 'mh',
                    'Surgical History' => 'sh'
                ];
                foreach ($issues as $issues_row) {
                    $arr = [];
                    $arr['label'] = '<b>' . $issues_row->type . ' - </b>' . $issues_row->issue;
                    if ($issues_row->reconcile !== null && $issues_row->reconcile !== 'y') {
                        $arr['danger'] = true;
                    }
                    $list_array[] = $arr;
                }
            }
            if ($allergies->count()) {
                foreach ($allergies as $allergies_row) {
                    $arr = [];
                    $arr['label'] = '<b>' . trans('nosh.allergy') . ' - ' . $allergies_row->allergies_med . ' - ' . $allergies_row->allergies_reaction;
                    if ($allergies_row->reconcile !== null && $allergies_row->reconcile !== 'y') {
                        $arr['danger'] = true;
                    }
                    $list_array[] = $arr;
                }
            }
            if ($rx->count()) {
                foreach ($rx as $rx_row) {
                    $arr = [];
                    if ($rx_row->rxl_sig == '') {
                        $arr['label'] = '<b>' . trans('nosh.medication') . ' - </b><strong>' . $rx_row->rxl_medication . '</strong> ' . $rx_row->rxl_dosage . ' ' . $rx_row->rxl_dosage_unit . ', ' . $rx_row->rxl_instructions . ' ' . trans('nosh.for') . ' ' . $rx_row->rxl_reason;
                    } else {
                        $arr['label'] = '<b>' . trans('nosh.medication') . ' - </b><strong>' . $rx_row->rxl_medication . '</strong> ' . $rx_row->rxl_dosage . ' ' . $rx_row->rxl_dosage_unit . ', ' . $rx_row->rxl_sig . ', ' . $rx_row->rxl_route . ', ' . $rx_row->rxl_frequency;
                        $arr['label'] .= ' ' . trans('nosh.for')  . ' ' . $rx_row->rxl_reason;
                    }
                    $previous = DB::table('rx_list')
            			->where('pid', '=', Session::get('pid'))
            			->where('rxl_medication', '=', $rx_row->rxl_medication)
            			->select('rxl_date_prescribed', 'prescription')
                        ->orderBy('rxl_date_prescribed', 'desc')
            			->first();
                    if ($previous) {
                        if ($previous->rxl_date_prescribed !== null && $previous->rxl_date_prescribed !== '0000-00-00 00:00:00') {
                            $previous_date = new Date($this->human_to_unix($previous->rxl_date_prescribed));
                            $ago = $previous_date->diffInDays();
                            $arr['label'] .= '<br><strong>' . trans('nosh.last_prescribed') . ':</strong> ' . date('Y-m-d', $this->human_to_unix($previous->rxl_date_prescribed)) . ', ' . $ago . ' ' . trans('nosh.days_ago');
                            $arr['label'] .= '<br><strong>' . trans('nosh.prescription_status') . ':</strong> ' . ucfirst($previous->prescription);
                        }
                    }
                    if ($rx_row->reconcile !== null && $rx_row->reconcile !== 'y') {
                        $arr['danger'] = true;
                    }
                    $list_array[] = $arr;
                }
            }
            if ($sup->count()) {
                foreach ($sup as $sup_row) {
                    $arr = [];
                    if ($sup_row->sup_sig == '') {
                        $arr['label'] = '<b>' . trans('nosh.supplement') . ' - </b>' . $sup_row->sup_supplement . ' ' . $sup_row->sup_dosage . ' ' . $sup_row->sup_dosage_unit . ', ' . $sup_row->sup_instructions . ' ' . trans('nosh.for') . ' ' . $sup_row->sup_reason;
                    } else {
                        $arr['label'] = '<b>' . trans('nosh.supplement') . ' - </b>' . $sup_row->sup_supplement . ' ' . $sup_row->sup_dosage . ' ' . $sup_row->sup_dosage_unit . ', ' . $sup_row->sup_sig . ', ' . $sup_row->sup_route . ', ' . $sup_row->sup_frequency;
                        $arr['label'] .= ' ' . trans('nosh.for') . ' ' . $sup_row->sup_reason;
                    }
                    if ($sup_row->reconcile !== null && $sup_row->reconcile !== 'y') {
                        $arr['danger'] = true;
                    }
                    $list_array[] = $arr;
                }
            }
            if ($imm->count()) {
                $seq_array = [
                    '1' => ', first',
                    '2' => ', second',
                    '3' => ', third',
                    '4' => ', fourth',
                    '5' => ', fifth'
                ];
                foreach ($imm as $imm_row) {
                    $arr = [];
                    $arr['label'] = '<b>' . trans('nosh.immunization') . ' - ' . $imm_row->imm_immunization . '</b> - ' . date('Y-m-d', $this->human_to_unix($imm_row->imm_date));
                    if (isset($imm_row->imm_sequence)) {
                        if (isset($seq_array[$imm_row->imm_sequence])) {
                            $arr['label'] = '<b>' . trans('nosh.immunization') . ' - ' . $imm_row->imm_immunization . $seq_array[$imm_row->imm_sequence]  . '</b> - ' . date('Y-m-d', $this->human_to_unix($imm_row->imm_date));
                        }
                    }
                    if ($imm_row->reconcile !== null && $imm_row->reconcile !== 'y') {
                        $arr['danger'] = true;
                    }
                    $list_array[] = $arr;
                }
            }
            if ($tests->count()) {
                foreach ($tests as $tests_row) {
                    $arr = [];
                    $arr['label'] = '<b>' . trans('nosh.test_result1') . ' - ' . date('Y-m-d', $this->human_to_unix($tests_row->test_datetime)) . '</b> - ' . $tests_row->test_name;
                    $arr['view'] = route('results_view', [$tests_row->tests_id]);
                    $arr['chart'] = route('results_chart', [$tests_row->tests_id]);
                    $list_array[] = $arr;
                }
            }
            if (! empty($tests_arr)) {
                foreach ($tests_arr as $tests_item) {
                    $tests_row1 = DB::table('tests')->where('tests_id', '=', $tests_item)->first();
                    $arr = [];
                    $arr['label'] = '<b>' . trans('nosh.test_result2') . ' - ' . date('Y-m-d', $this->human_to_unix($tests_row1->test_datetime)) . '</b> - ' . $tests_row1->test_name;
                    $arr['view'] = route('results_view', [$tests_row1->tests_id]);
                    $arr['chart'] = route('results_chart', [$tests_row1->tests_id]);
                    $list_array[] = $arr;
                }
            }
            if ($documents->count()) {
                foreach ($documents as $documents_row) {
                    $arr = [];
                    $arr['label'] = '<b>' . trans('nosh.document') . ' - ' . date('Y-m-d', $this->human_to_unix($documents_row->documents_date)) . '</b> - ' . $documents_row->documents_desc . ' ' . trans('nosh.from') . ' ' . $documents_row->documents_from;
                    $arr['view'] = route('document_view', [$documents_row->documents_id]);
                    if ($documents_row->reconcile !== null && $documents_row->reconcile !== 'y') {
                        $arr['danger'] = true;
                    }
                    $list_array[] = $arr;
                }
            }
            if (! empty($documents_arr)) {
                foreach ($documents_arr as $documents_item) {
                    $documents_row1 = DB::table('documents')->where('documents_id', '=', $documents_item)->first();
                    $arr = [];
                    $arr['label'] = '<b>' . trans('nosh.tagged_document') . ' - ' . date('Y-m-d', $this->human_to_unix($documents_row1->documents_date)) . '</b> - ' . $documents_row1->documents_desc . ' ' . trans('nosh.from') . ' ' . $documents_row1->documents_from;
                    $arr['view'] = route('document_view', [$documents_row1->documents_id]);
                    if ($documents_row1->reconcile !== null && $documents_row1->reconcile !== 'y') {
                        $arr['danger'] = true;
                    }
                    $list_array[] = $arr;
                }
            }
            if ($t_messages->count()) {
                foreach ($t_messages as $t_messages_row) {
                    $arr = [];
                    $arr['label'] = '<b>' . trans('nosh.t_message') . ' - ' . date('Y-m-d', $this->human_to_unix($t_messages_row->t_messages_dos)) . '</b> - ' . $t_messages_row->t_messages_subject;
                    $arr['view'] = route('t_message_view', [$t_messages_row->t_messages_id]);
                    $list_array[] = $arr;
                }
            }
            if (! empty($t_messages_arr)) {
                foreach ($t_messages_arr as $t_messages_item) {
                    $t_messages_row1 = DB::table('t_messages')->where('t_messages_id', '=', $t_messages_item)->first();
                    $arr = [];
                    $arr['label'] = '<b>' . trans('nosh.tagged_t_message') . ' - ' . date('Y-m-d', $this->human_to_unix($t_messages_row1->t_messages_dos)) . '</b> - ' . $t_messages_row1->t_messages_subject;
                    $arr['view'] = route('t_message_view', [$t_messages_row1->t_messages_id]);
                    $list_array[] = $arr;
                }
            }
            if ($orders->count()) {
                foreach ($orders as $orders_row) {
                    $arr = [];
                    if ($orders_row->orders_labs !== '') {
                        $arr['label'] = '<b>' . trans('nosh.laboratory_orders') . ' - ' . date('Y-m-d', $this->human_to_unix($orders_row->orders_date)) . '</b> - ' . $orders_row->orders_labs;
                        $order_type = 'orders_labs';
                    }
                    if ($orders_row->orders_radiology !== '') {
                        $arr['label'] = '<b>' . trans('nosh.imaging_orders') . ' - ' . date('Y-m-d', $this->human_to_unix($orders_row->orders_date)) . '</b> - ' . $orders_row->orders_radiology;
                        $order_type = 'orders_radiology';
                    }
                    if ($orders_row->orders_cp !== '') {
                        $arr['label'] = '<b>' . trans('nosh.cardiopulmonary_orders') . ' - ' . date('Y-m-d', $this->human_to_unix($orders_row->orders_date)) . '</b> - ' . $orders_row->orders_cp;
                        $order_type = 'orders_cp';
                    }
                    if ($orders_row->orders_referrals !== '') {
                        $address = DB::table('addressbook')->where('address_id', '=', $orders_row->address_id)->first();
                        $arr['label'] = '<b>' . trans('nosh.referral') . ' - ' . date('Y-m-d', $this->human_to_unix($orders_row->orders_date)) . '</b> - ' . $address->specialty . ': ' . $address->displayname;
                        $order_type = 'orders_referrals';
                    }
                    $list_array[] = $arr;
                }
            }
            if ($alerts->count()) {
                foreach ($alerts as $alerts_row) {
                    $arr = [];
                    $arr['label'] = '<b>' . trans('nosh.alert') . ' - </b>' . $alerts_row->alert . ' (' . trans('nosh.due') . ' ' . date('m/d/Y', $this->human_to_unix($alerts_row->alert_date_active)) . ') - ' . $alerts_row->alert_description;
                    $list_array[] = $arr;
                }
            }
            if ($notes->count()) {
                foreach ($notes as $notes_row) {
                    $arr = [];
                    $arr['label'] = '<b>' . trans('nosh.imm_notes') . ' - </b>' . $notes_row->imm_notes;
                    $list_array[] = $arr;
                }
            }
            if ($notes1->count()) {
                foreach ($notes1 as $notes1_row) {
                    $arr = [];
                    $arr['label'] = '<b>' . trans('nosh.billing_notes') . ' - </b>' . $notes1_row->billing_notes;
                    $list_array[] = $arr;
                }
            }
            if ($demographics->count()) {
                $arr = [];
                $arr['label'] = '<b>'. trans('nosh.demographics') . ' - ' . trans('nosh.name_identity') . '</b>';
                $list_array[] = $arr;
            }
            if ($demographics_a->count()) {
                $arr = [];
                $arr['label'] = '<b>' . trans('nosh.demographics') . ' - ' . trans('nosh.contacts') . '</b>';
                $list_array[] = $arr;
            }
            if ($demographics_b->count()) {
                $arr = [];
                $arr['label'] = '<b>' . trans('nosh.demographics') . ' - ' . trans('nosh.guardians') . '</b>';
                $list_array[] = $arr;
            }
            if ($demographics_c->count()) {
                $arr = [];
                $arr['label'] = '<b>' . trans('nosh.demographics') . ' - ' . trans('nosh.other') . '</b>';
                $list_array[] = $arr;
            }
            $return .= $this->result_build($list_array, 'results_list', false, true);
        } else {
            $return .= ' ' . trans('nosh.none') . '.';
        }
        $data['content'] = $return;
        $data['sidebar'] = 'search';
        $data['panel_header'] = trans('nosh.search_results');
        $data = array_merge($data, $this->sidebar_build('chart'));
        Session::put('last_page', $request->fullUrl());
        return view('home', $data);
    }

    public function social_history(Request $request)
    {
        $data['sidebar'] = 'social_history';
        $data['message_action'] = Session::get('message_action');
        Session::forget('message_action');
        $recent_query = DB::table('other_history')
            ->where('pid', '=', Session::get('pid'))
            ->where('eid', '!=', '0')
            ->orderBy('eid', 'desc');
        $recent_oh_sh = $recent_query->whereNotNull('oh_sh')->first();
        $recent_oh_etoh = $recent_query->whereNotNull('oh_etoh')->first();
        $recent_oh_tobacco = $recent_query->whereNotNull('oh_tobacco')->first();
        $recent_oh_drugs = $recent_query->whereNotNull('oh_drugs')->first();
        $recent_oh_employment = $recent_query->whereNotNull('oh_employment')->first();
        $recent_oh_psychosocial = $recent_query->whereNotNull('oh_psychosocial')->first();
        $recent_oh_developmental = $recent_query->whereNotNull('oh_developmental')->first();
        $recent_oh_medtrials = $recent_query->whereNotNull('oh_medtrials')->first();
        $recent_oh_diet = $recent_query->whereNotNull('oh_diet')->first();
        $recent_oh_physical_activity = $recent_query->whereNotNull('oh_physical_activity')->first();
        $social_hx_arr = ['oh_sh', 'oh_etoh', 'oh_tobacco', 'oh_drugs', 'oh_employment', 'oh_psychosocial', 'oh_developmental', 'oh_medtrials', 'oh_diet', 'oh_physical_activity'];
        $return = '';
        $result = DB::table('other_history')->where('pid', '=', Session::get('pid'))->where('eid', '=', '0')->first();
        $patient = DB::table('demographics')->where('pid', '=', Session::get('pid'))->first();
        if ($result) {
            $lifestyle_arr = [
                trans('nosh.oh_sh') => nl2br($result->oh_sh),
                trans('nosh.sexuallyactive') => ucfirst($patient->sexuallyactive),
                trans('nosh.oh_diet') => nl2br($result->oh_diet),
                trans('nosh.oh_physical_activity') => nl2br($result->oh_physical_activity),
                trans('nosh.oh_employment') => nl2br($result->oh_employment)
            ];
            $habits_arr = [
                trans('nosh.oh_etoh') => nl2br($result->oh_etoh),
                trans('nosh.tobacco') => ucfirst($patient->tobacco),
                trans('nosh.oh_tobacco') => nl2br($result->oh_tobacco),
                trans('nosh.oh_drugs') => nl2br($result->oh_drugs)
            ];
            $mental_health_arr = [
                trans('nosh.oh_psychosocial') => nl2br($result->oh_psychosocial),
                trans('nosh.oh_developmental') => nl2br($result->oh_developmental),
                trans('nosh.oh_medtrials') => nl2br($result->oh_medtrials)
            ];
            $return = $this->header_build(trans('nosh.lifestyle'));
            foreach ($lifestyle_arr as $key1 => $value1) {
                if ($value1 !== '' && $value1 !== null) {
                    $return .= '<div class="col-md-3"><b>' . $key1 . '</b></div><div class="col-md-8">' . $value1 . '</div>';
                }
            }
            $return .= '</div></div>';
            $return .= $this->header_build(trans('nosh.habits'));
            foreach ($habits_arr as $key2 => $value2) {
                if ($value2 !== '' && $value2 !== null) {
                    $return .= '<div class="col-md-3"><b>' . $key2 . '</b></div><div class="col-md-8">' . $value2 . '</div>';
                }
            }
            $return .= '</div></div>';
            $return .= $this->header_build(trans('nosh.mental_health'));
            foreach ($mental_health_arr as $key3 => $value3) {
                if ($value3 !== '' && $value3 !== null) {
                    $return .= '<div class="col-md-3"><b>' . $key3 . '</b></div><div class="col-md-8">' . $value3 . '</div>';
                }
            }
            $return .= '</div></div>';
        } else {
            $return .= ' ' . trans('nosh.none') . '.';
        }
        $data['content'] = $return;
        $data['panel_header'] = trans('nosh.social_history');
        $data = array_merge($data, $this->sidebar_build('chart'));
        Session::put('last_page', $request->fullUrl());
        return view('home', $data);
    }

    public function supplements_list(Request $request, $type)
    {
        $data['sidebar'] = 'supplements';
        $data['message_action'] = Session::get('message_action');
        Session::forget('message_action');
        $query = DB::table('sup_list')->where('pid', '=', Session::get('pid'))->orderBy('sup_supplement', 'asc');
        if ($type == 'active') {
            $query->where('sup_date_inactive', '=', '0000-00-00 00:00:00');
            $dropdown_array = [
                'items_button_text' => trans('nosh.active')
            ];
            $items[] = [
                'type' => 'item',
                'label' => trans('nosh.inactive'),
                'icon' => 'fa-times',
                'url' => route('supplements_list', ['inactive'])
            ];
        } else {
            $query->where('sup_date_inactive', '!=', '0000-00-00 00:00:00');
            $dropdown_array = [
                'items_button_text' => trans('nosh.inactive')
            ];
            $items[] = [
                'type' => 'item',
                'label' => trans('nosh.active'),
                'icon' => 'fa-check',
                'url' => route('supplements_list', ['active'])
            ];
        }
        $dropdown_array['items'] = $items;
        $data['panel_dropdown'] = $this->dropdown_build($dropdown_array);
        $result = $query->get();
        $return = '';
        $columns = Schema::getColumnListing('sup_list');
        $row_index = $columns[0];
        $list_array = [];
        if ($result->count()) {
            foreach ($result as $row) {
                $arr = [];
                if ($row->sup_sig == '') {
                    $arr['label'] = $row->sup_supplement . ' ' . $row->sup_dosage . ' ' . $row->sup_dosage_unit . ', ' . $row->sup_instructions . ' ' . trans('nosh.for') . ' ' . $row->sup_reason;
                } else {
                    $arr['label'] =$row->sup_supplement . ' ' . $row->sup_dosage . ' ' . $row->sup_dosage_unit . ', ' . $row->sup_sig . ', ' . $row->sup_route . ', ' . $row->sup_frequency;
                    $arr['label'] .= ' ' . trans('nosh.for') . ' ' . $row->sup_reason;
                }
                if ($row->reconcile !== null && $row->reconcile !== 'y') {
                    $arr['danger'] = true;
                }
                $list_array[] = $arr;
            }
            $return .= $this->result_build($list_array, 'results_list');
        } else {
            $return .= ' ' . trans('nosh.none') . '.';
        }
        $data['content'] = $return;
        $data['panel_header'] = trans('nosh.supplements');
        $data = array_merge($data, $this->sidebar_build('chart'));
        Session::put('last_page', $request->fullUrl());
        return view('home', $data);
    }

    public function t_messages_list(Request $request)
    {
        $data['sidebar'] = 't_messages';
        $data['message_action'] = Session::get('message_action');
        Session::forget('message_action');
        $query = DB::table('t_messages')->where('pid', '=', Session::get('pid'))->orderBy('t_messages_dos', 'desc');
        $query->where('t_messages_signed', '=', 'Yes');
        if (Session::get('patient_centric') == 'n') {
            $query->where('practice_id', '=', Session::get('practice_id'));
        }
        $result = $query->get();
        $return = '';
        $columns = Schema::getColumnListing('t_messages');
        $row_index = $columns[0];
        if ($result->count()) {
            $list_array = [];
            foreach ($result as $row) {
                $arr = [];
                $arr['label'] = '<b>' . date('Y-m-d', $this->human_to_unix($row->t_messages_dos)) . '</b> - ' . $row->t_messages_subject;
                $arr['view'] = route('t_message_view', [$row->t_messages_id]);
                $list_array[] = $arr;
            }
            $return .= $this->result_build($list_array, 'results_list');
        } else {
            $return .= ' ' . trans('nosh.none') . '.';
        }
        $data['content'] = $return;
        $data['panel_header'] = trans('nosh.t_messages_list');
        $data = array_merge($data, $this->sidebar_build('chart'));
        Session::put('last_page', $request->fullUrl());
        return view('home', $data);
    }

    public function t_message_view(Request $request, $t_messages_id)
    {
        $data['sidebar'] = 't_messages';
        $data['message_action'] = Session::get('message_action');
        Session::forget('message_action');
        $message = DB::table('t_messages')->where('t_messages_id', '=', $t_messages_id)->first();
        // Tags
        $tags_relate = DB::table('tags_relate')->where('t_messages_id', '=', $t_messages_id)->get();
        $tags_val_arr = [];
        if ($tags_relate->count()) {
            foreach ($tags_relate as $tags_relate_row) {
                $tags = DB::table('tags')->where('tags_id', '=', $tags_relate_row->tags_id)->first();
                $tags_val_arr[] = $tags->tag;
            }
        }
        $return = '';
        $return .= '<div style="margin-bottom:15px;">';
        foreach ($tags_val_arr as $tag) {
            $return .= '<span class="badge badge-primary">' . $tag . '</span>';
        }
        $return .= '</div>';
        $return .= $this->t_messages_view($t_messages_id);
        $images = DB::table('image')->where('t_messages_id', '=', $t_messages_id)->get();
        if ($images->count()) {
            $return .= '<br><h5>' . trans('nosh.images') . ':</h5><div class="list-group gallery">';
            foreach ($images as $image) {
                $file_path1 = '/temp/' . time() . '_' . basename($image->image_location);
                $file_path = public_path() . $file_path1;
                copy($image->image_location, $file_path);
                $return .= '<div class="col-sm-4 col-xs-6 col-md-3 col-lg-3"><a class="thumbnail fancybox nosh-no-load" rel="ligthbox" href="' . url('/') . $file_path1 . '">';
                $return .= '<img class="img-responsive" alt="" src="' . url('/') . $file_path1 . '" />';
                $return .= '<div class="text-center"><small class="text-muted">' . $image->image_description . '</small></div></a>';
            }
            $return .= '</div>';
        }
        $dropdown_array = [];
        $dropdown_array['default_button_text'] = '<i class="fa fa-chevron-left fa-fw fa-btn"></i>' . trans('nosh.back');
        $dropdown_array['default_button_text_url'] = Session::get('last_page');
        $data['panel_dropdown'] = $this->dropdown_build($dropdown_array);
        $data['content'] = $return;
        $data['t_messages_active'] = true;
        $data['panel_header'] = trans('nosh.t_messages_message') . ' - ' .  date('Y-m-d', $this->human_to_unix($message->t_messages_dos));
        $data = array_merge($data, $this->sidebar_build('chart'));
        $data['assets_js'] = $this->assets_js('chart');
        $data['assets_css'] = $this->assets_css('chart');
        return view('home', $data);
    }

    public function treedata(Request $request)
    {
        $oh = DB::table('other_history')->where('pid', '=', Session::get('pid'))->where('eid', '=', '0')->first();
        $ret_arr = $this->treedata_build([], 'patient', [], [], 0);
        $nodes_arr = $ret_arr[0];
        $edges_arr = $ret_arr[1];
        $placeholder_count = $ret_arr[2];
        if ($oh->oh_fh !== null) {
            if ($this->yaml_check($oh->oh_fh)) {
                $nodes_arr = [];
                $edges_arr = [];
                $placeholder_count = 0;
                $formatter = Formatter::make($oh->oh_fh, Formatter::YAML);
                $fh_arr = $formatter->toArray();
                $ret_arr = $this->treedata_build($fh_arr, 'patient', [], [], 0);
                $nodes_arr = $ret_arr[0];
                $edges_arr = $ret_arr[1];
                $placeholder_count = $ret_arr[2];
                foreach ($fh_arr as $person_key => $person_val) {
                    $ret_arr = $this->treedata_build($fh_arr, $person_key, $nodes_arr, $edges_arr, $placeholder_count);
                    $nodes_arr = $ret_arr[0];
                    $edges_arr = $ret_arr[1];
                    $placeholder_count = $ret_arr[2];
                }
            }
        }
        $nodes_arr = $this->treedata_x_build($nodes_arr);
        $arr = [
            'nodes' => $nodes_arr,
            'edges' => $edges_arr
        ];
        return $arr;
    }

    public function upload_ccda_view(Request $request, $id, $type='issues')
    {
        $data['sidebar'] = 'records_list';
        $documents = DB::table('documents')->where('documents_id', '=', $id)->first();
        if ($documents->documents_type == 'ccda') {
            $data['ccda'] = str_replace("'", '"', preg_replace( "/\r|\n/", "", File::get($documents->documents_url)));
        }
        $data['message_action'] = Session::get('message_action');
        Session::forget('message_action');
        $return = '';
        $type_arr = [
            'issues' => [trans('nosh.conditions'), 'fa-bars', 'issue'],
            'rx_list' => [trans('nosh.medications'), 'fa-eyedropper', 'rxl_medication'],
            'immunizations' => [trans('nosh.immunizations'), 'fa-magic', 'imm_immunization'],
            'allergies' => [trans('nosh.allergies'), 'fa-exclamation-triangle', 'allergies_med', 'allergies_date_inactive']
        ];
        $dropdown_array = [
            'items_button_text' => $type_arr[$type][0]
        ];
        foreach ($type_arr as $key => $value) {
            if ($key !== $type) {
                $items[] = [
                    'type' => 'item',
                    'label' => $value[0],
                    'icon' => $value[1],
                    'url' => route('upload_ccda_view', [$id, $key])
                ];
            }
        }
        $dropdown_array['items'] = $items;
        $data['panel_dropdown'] = $this->dropdown_build($dropdown_array);
        $query = DB::table($type)->where('pid', '=', Session::get('pid'))->orderBy($type_arr[$type][2], 'asc');
        if ($type == 'issues') {
            $query->where('issue_date_inactive', '=', '0000-00-00 00:00:00');
        }
        if ($type == 'rx_list') {
            $query->where('rxl_date_inactive', '=', '0000-00-00 00:00:00')->where('rxl_date_old', '=', '0000-00-00 00:00:00');
        }
        if ($type == 'immunizations') {
            $query->orderBy('imm_sequence', 'asc');
        }
        if ($type == 'allergies') {
            $query->where('allergies_date_inactive', '=', '0000-00-00 00:00:00');
        }
        $result = $query->get();
        $list_array = [];
        if ($result->count()) {
            if ($type == 'issues') {
                foreach($result as $row) {
                    $arr = [];
                    $arr['label'] = $row->issue;
                    $list_array[] = $arr;
                }
            }
            if ($type == 'rx_list') {
                foreach($result as $row) {
                    $arr = [];
                    if ($row->rxl_sig == '') {
                        $arr['label'] = $row->rxl_medication . ' ' . $row->rxl_dosage . ' ' . $row->rxl_dosage_unit . ', ' . $row->rxl_instructions . ' ' . trans('nosh.for') . ' ' . $row->rxl_reason;
                    } else {
                        $arr['label'] = $row->rxl_medication . ' ' . $row->rxl_dosage . ' ' . $row->rxl_dosage_unit . ', ' . $row->rxl_sig . ', ' . $row->rxl_route . ', ' . $row->rxl_frequency;
                        $arr['label'] .= ' ' . trans('nosh.for') . ' ' . $row->rxl_reason;
                    }
                    $list_array[] = $arr;
                }
            }
            if ($type == 'immunizations') {
                $seq_array = [
                    '1' => ', ' . lcfirst(trans('nosh.first')),
                    '2' => ', ' . lcfirst(trans('nosh.second')),
                    '3' => ', ' . lcfirst(trans('nosh.third')),
                    '4' => ', ' . lcfirst(trans('nosh.fourth')),
                    '5' => ', ' . lcfirst(trans('nosh.fifth'))
                ];
                foreach ($result as $row) {
                    $arr = [];
                    $arr['label'] = $row->imm_immunization;
                    if (isset($row->imm_sequence)) {
                        if (isset($seq_array[$row->imm_sequence])) {
                            $arr['label'] = $row->imm_immunization . $seq_array[$row->imm_sequence];
                        }
                    }
                    $list_array[] = $arr;
                }
            }
            if ($type == 'allergies') {
                foreach ($result as $row) {
                    $arr = [];
                    $arr['label'] = $row->allergies_med . ' - ' . $row->allergies_reaction;
                    $list_array[] = $arr;
                }
            }
        }
        if ($documents->documents_type == 'ccr') {
            $xml = simplexml_load_file($documents->documents_url);
            // $phone_home = '';
            // $phone_work = '';
            // $phone_cell = '';
            // foreach ($xml->Actors->Actor[0]->Telephone as $phone) {
            //     if ((string) $phone->Type->Text == 'Home') {
            //         $phone_home = (string) $phone->Value;
            //     }
            //     if ((string) $phone->Type->Text == 'Mobile') {
            //         $phone_cell = (string) $phone->Value;
            //     }
            //     if ((string) $phone->Type->Text == 'Alternate') {
            //         $phone_work = (string) $phone->Value;
            //     }
            // }
            // $address = (string) $xml->Actors->Actor[0]->Address->Line1;
            // $address = ucwords(strtolower($address));
            // $city = (string) $xml->Actors->Actor[0]->Address->City;
            // $city = ucwords(strtolower($city));
            // $data1 = [
            //     'address' => $address,
            //     'city' => $city,
            //     'state' => (string) $xml->Actors->Actor[0]->Address->State,
            //     'zip' => (string) $xml->Actors->Actor[0]->Address->PostalCode,
            //     'phone_home' => $phone_home,
            //     'phone_work' => $phone_work,
            //     'phone_cell' => $phone_cell,
            // ];
            // DB::table('demographics')->where('pid', '=', $pid)->update($data1);
            // $this->audit('Update');
            if ($type == 'issues') {
                if (isset($xml->Body->Problems)) {
                    foreach ($xml->Body->Problems->Problem as $issue) {
                        if ((string) $issue->Status->Text == 'Active') {
                            $icd = (string) $issue->Description->Code->Value;
                            $icd_desc = $this->icd_search($icd);
                            if ($icd_desc == '') {
                                $icd_desc = (string) $issue->Description->Text;
                            }
                            $arr = [];
                            $arr['label'] = $icd_desc;
                            $arr['label_class'] = 'nosh-ccda-list';
                            $arr['danger'] = true;
                            $arr['label_data_arr'] = [
                                'data-nosh-type' => 'issues',
                                'data-nosh-name' => $icd_desc,
                                'data-nosh-code' => $icd,
                                'data-nosh-date' => (string) $issue->DateTime->ExactDateTime
                            ];
                            $list_array[] = $arr;
                        }
                    }
                }
            }
            if ($type == 'rx_list') {
                if (isset($xml->Body->Medications)) {
                    foreach ($xml->Body->Medications->Medication as $rx) {
                        if ((string) $rx->Status->Text == 'Active') {
                            $arr = [];
                            $arr['label'] = (string) $rx->Product->ProductName->Text . ', ' . $rx->Directions->Direction->Dose->Value;
                            $arr['label_class'] = 'nosh-ccda-list';
                            $arr['danger'] = true;
                            $arr['label_data_arr'] = [
                                'data-nosh-type' => 'rx_list',
                                'data-nosh-name' => (string) $rx->Product->ProductName->Text,
                                'data-nosh-code' => '',
                                'data-nosh-dosage' => '',
                                'data-nosh-dosage-unit' => '',
                                'data-nosh-route' => '',
                                'data-nosh-reason' => '',
                                'data-nosh-date' => (string) $rx->DateTime->ExactDateTime,
                                'data-nosh-administration' => $rx->Directions->Direction->Dose->Value
                            ];
                            $list_array[] = $arr;
                        }
                    }
                }
            }
            if ($type == 'immunizations') {
                if (isset($xml->Body->Immunizations)) {
                    foreach ($xml->Body->Immunizations->Immunization as $imm) {
                        if (strpos((string) $imm->Product->ProductName->Text, '#')) {
                            $items = explode('#',(string) $imm->Product->ProductName->Text);
                            $imm_immunization = rtrim($items[0]);
                            $imm_sequence = $items[1];
                        } else {
                            $imm_immunization = (string) $imm->Product->ProductName->Text;
                            $imm_sequence = '';
                        }
                        $arr = [];
                        $arr['label'] = $imm_immunization;
                        $arr['label_class'] = 'nosh-ccda-list';
                        $arr['danger'] = true;
                        $arr['label_data_arr'] = [
                            'data-nosh-type' => 'immunizations',
                            'data-nosh-name' =>  $imm_immunization,
                            'data-nosh-route' => '',
                            'data-nosh-date' => (string) $imm->DateTime->ApproximateDateTime,
                            'data-nosh-sequence' => $imm_sequence
                        ];
                        $list_array[] = $arr;
                    }
                }
            }
            if ($type == 'allergies') {
                if (isset($xml->Body->Alerts)) {
                    foreach ($xml->Body->Alerts->Alert as $alert) {
                        if ((string) $alert->Type->Text == 'Allergy') {
                            if ((string) $alert->Status->Text == 'Active') {
                                $arr = [];
                                $arr['label'] = (string) $alert->Description->Text;
                                $arr['label_class'] = 'nosh-ccda-list';
                                $arr['danger'] = true;
                                $arr['label_data_arr'] = [
                                    'data-nosh-type' => 'allergies',
                                    'data-nosh-name' => (string) $alert->Description->Text,
                                    'data-nosh-reaction' => (string) $alert->Reaction->Description->Text,
                                    'data-nosh-date' => (string) $alert->DateTime->ExactDateTime,
                                ];
                                $list_array[] = $arr;
                            }
                        }
                    }
                }
            }
        }
        $return = '<div class="alert alert-success">';
        $return .= '<h5>' . trans('nosh.upload_ccda') . '</h5>';
        $return .= '</div>';
        $return .= $this->result_build($list_array, $type . '_reconcile_list');
        $data['content'] = $return;
        $data['panel_header'] = trans('nosh.upload_ccda_view');
        $data = array_merge($data, $this->sidebar_build('chart'));
        Session::put('last_page', $request->fullUrl());
        return view('home', $data);
    }
}
