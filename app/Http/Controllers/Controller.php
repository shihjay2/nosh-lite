<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

use App;
use Illuminate\Support\Arr;
use Carbon\Carbon;
use Config;
use PragmaRX\Countries\Package\Countries;
use Date;
use DB;
use Form;
use HTML;
use Mail;
use shihjay2\tcpdi_merger\Merger;
use \NumberFormatter;
use PDF;
use Session;
use Illuminate\Support\Str;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function array_assessment()
    {
        $return = [
            'assessment_other' => [
                'standardmtm' => 'SOAP Note',
                'standard' => 'Additional Diagnoses'
            ],
            'assessment_ddx' => [
                'standardmtm' => 'MAP2',
                'standard' => 'Differential Diagnoses Considered'
            ],
            'assessment_notes' => [
                'standardmtm' => 'Pharmacist Note',
                'standard' => 'Assessment Discussion'
            ]
        ];
        return $return;
    }

    protected function array_encounter_type()
    {
        $return = [
            'medical' => trans('nosh.medical_encounter'),
            'phone' => trans('nosh.phone_encounter'),
            'virtual' => trans('nosh.virtual_encounter'),
            'standardmedical1' => 'Standard Medical Visit V2', // Depreciated
            'standardmedical' => 'Standard Medical Visit V1', // Depreciated
            'standardpsych' => trans('nosh.standardpsych'),
            'standardpsych1' => trans('nosh.standardpsych1'),
            'clinicalsupport' => trans('nosh.clinicalsupport'),
            'standardmtm' => trans('nosh.standardmtm')
        ];
        return $return;
    }

    protected function array_family()
    {
        $family = [
            '' => '',
            'Father' => trans('nosh.father'),
            'Mother' => trans('nosh.mother'),
            'Brother' => trans('nosh.brother'),
            'Sister' => trans('nosh.sister'),
            'Son' => trans('nosh.son'),
            'Daughter' => trans('nosh.daughter'),
            'Spouse' => trans('nosh.spouse'),
            'Partner' => trans('nosh.partner'),
            'Paternal Uncle' => trans('nosh.paternal_uncle'),
            'Paternal Aunt' => trans('nosh.paternal_aunt'),
            'Maternal Uncle' => trans('nosh.maternal_uncle'),
            'Maternal Aunt' => trans('nosh.maternal_aunt'),
            'Maternal Grandfather' => trans('nosh.maternal_grandfather'),
            'Maternal Grandmother' => trans('nosh.maternal_grandmother'),
            'Paternal Grandfather' => trans('nosh.paternal_grandfather'),
            'Paternal Grandmother' => trans('nosh.paternal_grandmother')
        ];
        return $family;
    }

    protected function array_gc()
    {
        $return = [
            'weight-age' => [
                'f' => 'wfa_girls_p_exp.txt',
                'm' => 'wfa_boys_p_exp.txt'
            ],
            'height-age' => [
                'f' => 'lhfa_girls_p_exp.txt',
                'm' => 'lhfa_boys_p_exp.txt'
            ],
            'head-age' => [
                'f' => 'hcfa_girls_p_exp.txt',
                'm' => 'hcfa_boys_p_exp.txt'
            ],
            'bmi-age' => [
                'f' => 'bfa_girls_p_exp.txt',
                'm' => 'bfa_boys_p_exp.txt'
            ],
            'weight-length' => [
                'f' => 'wfl_girls_p_exp.txt',
                'm' => 'wfl_boys_p_exp.txt'
            ],
            'weight-height' => [
                'f' => 'wfh_girls_p_exp.txt',
                'm' => 'wfh_boys_p_exp.txt'
            ]
        ];
        return $return;
    }

    protected function array_gender()
    {
        $gender = [
            'm' => trans('nosh.male'),
            'f' => trans('nosh.female'),
            'u' => trans('nosh.undifferentiated')
        ];
        return $gender;
    }

    protected function array_labs()
    {
        $ua = [
            'labs_ua_urobili' => trans('nosh.labs_ua_urobili'),
            'labs_ua_bilirubin' => trans('nosh.labs_ua_bilirubin'),
            'labs_ua_ketones' => trans('nosh.labs_ua_ketones'),
            'labs_ua_glucose' => trans('nosh.labs_ua_glucose'),
            'labs_ua_protein' => trans('nosh.labs_ua_protein'),
            'labs_ua_nitrites' => trans('nosh.labs_ua_nitrites'),
            'labs_ua_leukocytes' => trans('nosh.labs_ua_leukocytes'),
            'labs_ua_blood' => trans('nosh.labs_ua_blood'),
            'labs_ua_ph' => trans('nosh.labs_ua_ph'),
            'labs_ua_spgr' => trans('nosh.labs_ua_spgr'),
            'labs_ua_color' => trans('nosh.labs_ua_color'),
            'labs_ua_clarity'=> trans('nosh.labs_ua_clarity')
        ];
        $single = [
            'labs_upt' => trans('nosh.labs_upt'),
            'labs_strep' => trans('nosh.labs_strep'),
            'labs_mono' => trans('nosh.labs_mono'),
            'labs_flu' => trans('nosh.labs_flu'),
            'labs_microscope' => trans('nosh.labs_microscope'),
            'labs_glucose' => trans('nosh.labs_glucose'),
            'labs_other' => trans('nosh.labs_other')
        ];
        $return = [
            'ua' => $ua,
            'single' => $single
        ];
        return $return;
    }

    protected function array_marital()
    {
        $marital = [
            'Single' => trans('nosh.single'),
            'Married' => trans('nosh.married'),
            'Common law' => trans('nosh.commonlaw'),
            'Domestic partner' => trans('nosh.domesticpartner'),
            'Registered domestic partner' => trans('nosh.registereddomesticpartner'),
            'Interlocutory' => trans('nosh.interlocutory'),
            'Living together' => trans('nosh.livingtogether'),
            'Legally Separated' => trans('nosh.legallyseparated'),
            'Divorced' => trans('nosh.divorced'),
            'Separated' => trans('nosh.separated'),
            'Annulled' => trans('nosh.annulled'),
            'Widowed' => trans('nosh.widowed'),
            'Other' => trans('nosh.other'),
            'Unknown' => trans('nosh.unknown'),
            'Unmarried' => trans('nosh.unmarried'),
            'Unreported' => trans('nosh.unreported')
        ];
        return $marital;
    }

    protected function array_oh()
    {
        $return = [
            'oh_pmh' => trans('nosh.oh_pmh'),
            'oh_psh' => trans('nosh.oh_psh'),
            'oh_fh' => trans('nosh.oh_fh'),
            'oh_sh' => trans('nosh.oh_sh'),
            'oh_diet' => trans('nosh.oh_diet'),
            'oh_physical_activity' => trans('nosh.oh_physical_activity'),
            'oh_etoh' => trans('nosh.oh_etoh'),
            'oh_tobacco' => trans('nosh.oh_tobacco'),
            'oh_drugs' => trans('nosh.oh_drugs'),
            'oh_employment' => trans('nosh.oh_employment'),
            'oh_psychosocial' => trans('nosh.oh_psychosocial'),
            'oh_developmental' => trans('nosh.oh_developmental'),
            'oh_medtrials' => trans('nosh.oh_medtrials'),
            'oh_meds' => trans('nosh.oh_meds'),
            'oh_supplements' => trans('nosh.oh_supplements'),
            'oh_allergies' => trans('nosh.oh_allergies'),
            'oh_results' => trans('nosh.oh_results')
        ];
        return $return;
    }

    protected function array_pe()
    {
        $return = [
            "pe" => "",
            "pe_gen1" => "General",
            "pe_eye1" => "Eye - Conjunctiva and Lids",
            "pe_eye2" => "Eye - Pupil and Iris",
            "pe_eye3" => "Eye - Fundoscopic",
            "pe_ent1" => "ENT - External Ear and Nose",
            "pe_ent2" => "ENT - Canals and Tympanic Membranes",
            "pe_ent3" => "ENT - Hearing Assessment",
            "pe_ent4" => "ENT - Sinuses, Mucosa, Septum, and Turbinates",
            "pe_ent5" => "ENT - Lips, Teeth, and Gums",
            "pe_ent6" => "ENT - Oropharynx",
            "pe_neck1" => "Neck - General",
            "pe_neck2" => "Neck - Thryoid",
            "pe_resp1" => "Respiratory - Effort",
            "pe_resp2" => "Respiratory - Percussion",
            "pe_resp3" => "Respiratory - Palpation",
            "pe_resp4" => "Respiratory - Auscultation",
            "pe_cv1" => "Cardiovascular - Palpation",
            "pe_cv2" => "Cardiovascular - Auscultation",
            "pe_cv3" => "Cardiovascular - Carotid Arteries",
            "pe_cv4" => "Cardiovascular - Abdominal Aorta",
            "pe_cv5" => "Cardiovascular - Femoral Arteries",
            "pe_cv6" => "Cardiovascular - Extremities",
            "pe_ch1" => "Chest - Inspection",
            "pe_ch2" => "Chest - Palpation",
            "pe_gi1" => "Gastrointestinal - Masses and Tenderness",
            "pe_gi2" => "Gastrointestinal - Liver and Spleen",
            "pe_gi3" => "Gastrointestinal - Hernia",
            "pe_gi4" => "Gastrointestinal - Anus, Perineum, and Rectum",
            "pe_gu1" => "Genitourinary - Genitalia",
            "pe_gu2" => "Genitourinary - Urethra",
            "pe_gu3" => "Genitourinary - Bladder",
            "pe_gu4" => "Genitourinary - Cervix",
            "pe_gu5" => "Genitourinary - Uterus",
            "pe_gu6" => "Genitourinary - Adnexa",
            "pe_gu7" => "Genitourinary - Scrotum",
            "pe_gu8" => "Genitourinary - Penis",
            "pe_gu9" => "Genitourinary - Prostate",
            "pe_lymph1" => "Lymphatic - Neck",
            "pe_lymph2" => "Lymphatic - Axillae",
            "pe_lymph3" => "Lymphatic - Groin",
            "pe_ms1" => "Musculoskeletal - Gait and Station",
            "pe_ms2" => "Musculoskeletal - Digit and Nails",
            "pe_ms3" => "Musculoskeletal - Shoulder",
            "pe_ms4" => "Musculoskeletal - Elbow",
            "pe_ms5" => "Musculoskeletal - Wrist",
            "pe_ms6" => "Musculoskeletal - Hand",
            "pe_ms7" => "Musculoskeletal - Hip",
            "pe_ms8" => "Musculoskeletal - Knee",
            "pe_ms9" => "Musculoskeletal - Ankle",
            "pe_ms10" => "Musculoskeletal - Foot",
            "pe_ms11" => "Musculoskeletal - Cervical Spine",
            "pe_ms12" => "Musculoskeletal - Thoracic and Lumbar Spine",
            "pe_neuro1" => "Neurological - Cranial Nerves",
            "pe_neuro2" => "Neurological - Deep Tendon Reflexes",
            "pe_neuro3" => "Neurological - Sensation and Motor",
            "pe_psych1" => "Psychiatric - Judgement",
            "pe_psych2" => "Psychiatric - Orientation",
            "pe_psych3" => "Psychiatric - Memory",
            "pe_psych4" => "Psychiatric - Mood and Affect",
            'pe_constitutional1' => 'Psychiatric - Constitutional',
            'pe_mental1' => 'Psychiatric - Mental Status Examination',
            "pe_skin1" => "Skin - Inspection",
            "pe_skin2" => "Skin - Palpation"
        ];
        return $return;
    }

    protected function array_plan()
    {
        $return = [
            'plan' => trans('nosh.recommendations'),
            'followup' => trans('nosh.followup'),
            'goals' => trans('nosh.goals'),
            'tp' => trans('nosh.tp'),
            'duration' => trans('nosh.duration1')
        ];
        return $return;
    }

    protected function array_ros()
    {
        $return = [
            'ros' => '',
            'ros_gen' => 'General',
            'ros_eye' => 'Eye',
            'ros_ent' => 'Ears, Nose, Throat',
            'ros_resp' => 'Respiratory',
            'ros_cv' => 'Cardiovascular',
            'ros_gi' => 'Gastrointestinal',
            'ros_gu' => 'Genitourinary',
            'ros_mus' => 'Musculoskeletal',
            'ros_neuro' => 'Neurological',
            'ros_psych' => 'Psychological',
            'ros_heme' => 'Hematological, Lymphatic',
            'ros_endocrine' => 'Endocrine',
            'ros_skin' => 'Skin',
            'ros_wcc' => 'Well Child Check',
            'ros_psych1' => 'Depression',
            'ros_psych2' => 'Anxiety',
            'ros_psych3' => 'Bipolar',
            'ros_psych4' => 'Mood Disorders',
            'ros_psych5' => 'ADHD',
            'ros_psych6' => 'PTSD',
            'ros_psych7' => 'Substance Related Disorder',
            'ros_psych8' => 'Obsessive Compulsive Disorder',
            'ros_psych9' => 'Social Anxiety Disorder',
            'ros_psych10' => 'Autistic Disorder',
            'ros_psych11' => "Asperger's Disorder"
        ];
        return $return;
    }

    protected function array_states($country='United States')
    {
        $states = [
            '' => '',
        ];
        $states1 = Countries::where('name.common', $country)
            ->first()
            ->hydrateStates()
            ->states
            ->sortBy('name')
            ->pluck('name', 'postal')
            ->toArray();
        if ($country == 'Philippines') {
            $states1['MNL'] = 'Metro Manila';
            asort($states1);
        }
        $states = array_merge($states, $states1);
        return $states;
    }

    protected function array_test_flag()
    {
        $test_arr = [
            '' => '',
            'L' => trans('nosh.test_flag_L'),
            'H' => trans('nosh.test_flag_H'),
            'LL' => trans('nosh.test_flag_LL'),
            'HH' => trans('nosh.test_flag_HH'),
            '<' => trans('nosh.test_flag_below'),
            '>' => trans('nosh.test_flag_above'),
            'N' => trans('nosh.test_flag_N'),
            'A' => trans('nosh.test_flag_A'),
            'AA' => trans('nosh.test_flag_AA'),
            'U' => trans('nosh.test_flag_U'),
            'D' => trans("nosh.test_flag_D"),
            'B' => trans('nosh.test_flag_B'),
            'W' => trans('nosh.test_flag_W'),
            'S' => trans('nosh.test_flag_S'),
            'R' => trans('nosh.test_flag_R'),
            'I' => trans('nosh.test_flag_I'),
            'MS' => trans('nosh.test_flag_MS'),
            'VS' => trans('nosh.test_flag_VS')
        ];
        return $test_arr;
    }

    protected function array_vitals($practice_id)
    {
        $practice = DB::table('practiceinfo')->where('practice_id', '=', $practice_id)->first();
        $return = [
            'weight' => [
                'name' => trans('nosh.weight'),
                'unit' => $practice->weight_unit
            ],
            'height' => [
                'name' => trans('nosh.height'),
                'unit' => $practice->height_unit
            ],
            'headcircumference' => [
                'name' => trans('nosh.headcircumference1'),
                'unit' => $practice->hc_unit
            ],
            'BMI' => [
                'min' => '19',
                'max' => '30',
                'name' => trans('nosh.BMI'),
                'unit' => 'kg/m2'
            ],
            'temp' => [
                'min' => [
                    'F' => '93',
                    'C' => '34'
                ],
                'max' => [
                    'F' => '100.4',
                    'C' => '38'
                ],
                'name' => trans('nosh.temp1'),
                'unit' => $practice->temp_unit
            ],
            'bp_systolic' => [
                'min' => '80',
                'max' => '140',
                'name' => trans('nosh.bp_systolic1'),
                'unit' => 'mmHg'
            ],
            'bp_diastolic' => [
                'min' => '50',
                'max' => '90',
                'name' => trans('nosh.bp_diastolic1'),
                'unit' => 'mmHg'
            ],
            'pulse' => [
                'min' => '50',
                'max' => '140',
                'name' => trans('nosh.pulse'),
                'unit' => 'bpm'
            ],
            'respirations' => [
                'min' => '10',
                'max' => '35',
                'name' => trans('nosh.respirations1'),
                'unit' => 'bpm'
            ],
            'o2_sat' => [
                'min' => '90',
                'max' => '100',
                'name' => trans('nosh.o2_sat'),
                'unit' => 'percent'
            ]
        ];
        return $return;
    }

    protected function array_vitals1()
    {
        $return = [
            'wt_percentile' => trans('nosh.wt_percentile'),
            'ht_percentile' => trans('nosh.ht_percentile'),
            'wt_ht_percentile' => trans('nosh.wt_ht_percentile'),
            'hc_percentile' => trans('nosh.hc_percentile'),
            'bmi_percentile' => trans('nosh.bmi_percentile')
        ];
        return $return;
    }

    protected function csv_to_array($filename = '', $delimiter = ',', $asHash = true) {
        if (!(is_readable($filename) || (($status = get_headers($filename)) && strpos($status[0], '200')))) {
            return FALSE;
        }
        $header = NULL;
        $data = [];
        if (($handle = fopen($filename, 'r')) !== FALSE) {
            if ($asHash) {
                while ($row = fgetcsv($handle, 0, $delimiter)) {
                    if (!$header) {
                        $header = $row;
                    } else {
                        $data[] = array_combine($header, mb_convert_encoding($row, 'UTF-8', 'UTF-8'));
                    }
                }
            } else {
                while ($row = fgetcsv($handle, 0, $delimiter)) {
                    $data[] = mb_convert_encoding($row, 'UTF-8', 'UTF-8');
                }
            }
            fclose($handle);
        }
        return $data;
    }

    /**
    * Dropdown build
    * @param array  $dropdown_array -
    * $dropdown_array = [
    *    'default_button_text' => 'split button with dropdown',
    *    'default_button_text_url' => URL::to('button_action'), requires default_button_text
    *    'default_button_id' => 'id of element',
    *    'items_button_text' => 'dropdown button text',
    *    'items_button_icon' => 'fa fa-icon',
    *    'items' => [
    *        [
    *            'type' => 'item', or separator or header or item
    *            'label' => 'Practice NPI', needed for item or header
    *            'icon' => 'fa-stethoscope',
    *            'id' => 'id of element',
    *            'url' => 'URL'
    *        ],
    *       [
    *            'type' => 'separator',
    *        ]
    *    ],
    *    'origin' => 'previous URL',
    *    'class' => 'btn-success'
    *    'new_window' => boolean
    * ];
    * @param int $id - Item key in database
    * @return Response
    */
    protected function dropdown_build($dropdown_array)
    {
        $class = 'btn-primary';
        if (isset($dropdown_array['class'])) {
            $class = $dropdown_array['class'];
        }
        $new_window = '';
        if (isset($dropdown_array['new_window'])) {
            $new_window = ' target="_blank"';
            $class .= ' nosh-no-load';
        }
        if (isset($dropdown_array['items'])) {
            $return = '<div class="btn-group">';
            if (count($dropdown_array['items']) == 1 && isset($dropdown_array['items_button_text']) == false) {
                if (isset($dropdown_array['default_button_text'])) {
                    $return .= '<a href="' . $dropdown_array['default_button_text_url'] . '" class="btn ' . $class . ' btn-sm">' . $dropdown_array['default_button_text'] . '</a><a href="' . $dropdown_array['items'][0]['url'] . '" class="btn ' . $class . ' btn-sm"><i class="fa ' . $dropdown_array['items'][0]['icon'] . ' fa-fw fa-btn"></i>' . $dropdown_array['items'][0]['label'] . '</a></div>';
                } else {
                    $return .= '<a href="' . $dropdown_array['items'][0]['url'] . '"';
                    if (isset($dropdown_array['items'][0]['id'])) {
                        $return .= ' id="' . $dropdown_array['items'][0]['id'] . '"';
                    }
                    $return .= ' class="btn ' . $class . ' btn-sm"><i class="fa ' . $dropdown_array['items'][0]['icon'] . ' fa-fw fa-btn"></i>' . $dropdown_array['items'][0]['label'] . '</a></div>';
                }
            } else {
                if (isset($dropdown_array['default_button_text'])) {
                    $return .= '<a href="' . $dropdown_array['default_button_text_url'] . '" class="btn ' . $class . ' btn-sm">' . $dropdown_array['default_button_text'] . '</a><button type="button" class="btn ' . $class . ' btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="caret"></span><span class="sr-only">Toggle Dropdown</span></button>';
                }
                if (isset($dropdown_array['items_button_text'])) {
                    $return .= '<button type="button" class="btn ' . $class . ' btn-sm dropdown-toggle" data-toggle="dropdown"><span class="fa-btn">' . $dropdown_array['items_button_text'] . '</span><span class="caret"></span></button>';
                }
                if (isset($dropdown_array['items_button_icon'])) {
                    $return .= '<button type="button" class="btn ' . $class . ' btn-sm dropdown-toggle" data-toggle="dropdown"><i class="fa ' . $dropdown_array['items_button_icon'] . ' fa-fw fa-btn"></i><span class="caret"></span></button>';
                }
                $return .= '<div class="dropdown-menu dropdown-menu-right">';
                foreach ($dropdown_array['items'] as $row) {
                    if ($row['type'] == 'separator') {
                        $return .= '<div class="dropdown-divider"></div>';
                    }
                    if ($row['type'] == 'header') {
                        $return .= '<h6 class="dropdown-header">' . $row['label'] . '</h6>';
                    }
                    if ($row['type'] == 'item') {
                        $return .= '<a class="dropdown-item" href="' . $row['url'] . '"';
                        if (isset($row['id'])) {
                            $return .= ' id="' . $row['id'] . '"';
                        }
                        $return .= '><i class="fa ' . $row['icon'] . ' fa-fw fa-btn mr-2"></i>' . $row['label'] . '</a>';
                    }
                }
                $return .= '</div></div>';
            }
        } else {
            $return = '<div class="btn-group"><a href="' . $dropdown_array['default_button_text_url'] . '"class="btn ' . $class . ' btn-sm"';
            if (isset($dropdown_array['default_button_id'])) {
                $return .= ' id="' . $dropdown_array['default_button_id'] . '"';
            }
            $return .= $new_window . '>' . $dropdown_array['default_button_text'] . '</a></div>';
        }
        return $return;
    }

    protected function encounters_view($eid, $pid, $practice_id, $modal=false, $addendum=false)
    {
        if ($modal == false) {
            App::setLocale(Session::get('practice_locale'));
        } else {
            App::setLocale(Session::get('user_locale'));
        }
        $encounterInfo = DB::table('encounters')->where('eid', '=', $eid)->first();
        $data['patientInfo'] = DB::table('demographics')->where('pid', '=', $pid)->first();
        $data['eid'] = $eid;
        $data['encounter_DOS'] = date('F jS, Y; h:i A', $this->human_to_unix($encounterInfo->encounter_DOS));
        $data['encounter_provider'] = $encounterInfo->encounter_provider;
        $data['date_signed'] = date('F jS, Y; h:i A', $this->human_to_unix($encounterInfo->date_signed));
        $data['age1'] = $encounterInfo->encounter_age;
        $data['dob'] = date('F jS, Y', $this->human_to_unix($data['patientInfo']->DOB));
        $date = Date::parse($data['patientInfo']->DOB);
        $age_arr = explode(',', $date->timespan());
        $data['age'] = ucwords($age_arr[0] . ' Old');
        $gender_arr = $this->array_gender();
        $data['gender'] = $gender_arr[$data['patientInfo']->sex];
        $data['encounter_cc'] = nl2br($encounterInfo->encounter_cc);
        $practiceInfo = DB::table('practiceinfo')->where('practice_id', '=', $practice_id)->first();
        $data['hpi'] = '';
        $data['ros'] = '';
        $data['oh'] = '';
        $data['mtm'] = '';
        $data['vitals'] = '';
        $data['pe'] = '';
        $data['images'] = '';
        $data['labs'] = '';
        $data['procedure'] = '';
        $data['assessment'] = '';
        $data['orders'] = '';
        $data['rx'] = '';
        $data['plan'] = '';
        $data['billing'] = '';
        $hpiInfo = DB::table('hpi')->where('eid', '=', $eid)->first();
        if ($hpiInfo) {
            if (!is_null($hpiInfo->hpi) && $hpiInfo->hpi !== '') {
                $data['hpi'] = '<br><h4>' . trans('nosh.hpi') . ':</h4><p class="view">';
                $data['hpi'] .= nl2br($hpiInfo->hpi);
                $data['hpi'] .= '</p>';
            }
            if (!is_null($hpiInfo->situation) && $hpiInfo->situation !== '') {
                $data['hpi'] = '<br><h4>' . trans('nosh.situation') . ':</h4><p class="view">';
                $data['hpi'] .= nl2br($hpiInfo->situation);
                $data['hpi'] .= '</p>';
            }
            if (!is_null($hpiInfo->forms) && $hpiInfo->forms !== '') {
                $data['hpi'] .= '<br><h4>' . trans('nosh.form_responses') . ':</h4><p class="view">';
                $data['hpi'] .= nl2br($hpiInfo->forms);
                $data['hpi'] .= '</p>';
            }
        }
        $rosInfo = DB::table('ros')->where('eid', '=', $eid)->first();
        if ($rosInfo) {
            $data['ros'] = '<br><h4>' . trans('nosh.ros') . ':</h4><p class="view">';
            $ros_arr = $this->array_ros();
            foreach ($ros_arr as $ros_k => $ros_v) {
                if ($rosInfo->{$ros_k} !== '' && $rosInfo->{$ros_k} !== null) {
                    if ($ros_k !== 'ros') {
                        $data['ros'] .= '<strong>' . $ros_v . ': </strong>';
                    }
                    $data['ros'] .= nl2br($rosInfo->{$ros_k});
                    $data['ros'] .= '<br /><br />';
                }
            }
            $data['ros'] .= '</p>';
        }
        $ohInfo = DB::table('other_history')->where('eid', '=', $eid)->first();
        if ($ohInfo) {
            $oh_arr = $this->array_oh();
            $data['oh'] = '<br><h4>' . trans('nosh.other_history') . ':</h4><p class="view">';
            foreach ($oh_arr as $oh_k => $oh_v) {
                if ($ohInfo->{$oh_k} !== '' && $ohInfo->{$oh_k} !== null) {
                    $data['oh'] .= '<strong>' . $oh_v . ': </strong>';
                    if ($oh_k == 'oh_fh') {
                        $ohInfo->{$oh_k} = str_replace('---', '', $ohInfo->{$oh_k});
                    }
                    $data['oh'] .= nl2br($ohInfo->{$oh_k});
                    $data['oh'] .= '<br /><br />';
                }
            }
            $data['oh'] .= '</p>';
        }
        if ($encounterInfo->encounter_template == 'standardmtm') {
            $data['mtm'] = '<br><h4>' . trans('nosh.mtm1') . ':</h4><p class="view">';
            $data['mtm'] .= $this->page_mtm_map($pid, true);
            $data['mtm'] .= $this->page_mtm_pml($pid, true);
        }
        $vitalsInfo = DB::table('vitals')->where('eid', '=', $eid)->first();
        if ($vitalsInfo) {
            $vitals_arr = $this->array_vitals($practice_id);
            $vitals_arr1 = $this->array_vitals1();
            $data['vitals'] = '<br><h4>' . trans('nosh.vital_signs') . ':</h4><p class="view">';
            $data['vitals'] .= '<strong>' . trans('nosh.date_time') . ':</strong>';
            $data['vitals'] .= $vitalsInfo->vitals_date . '<br>';
            foreach ($vitals_arr as $vitals_k => $vitals_v) {
                if (!empty($vitalsInfo->{$vitals_k})) {
                    if ($vitals_k !== 'bp_systolic' && $vitals_k !== 'bp_diastolic') {
                        $data['vitals'] .= '<strong>' . $vitals_v['name'] . ': </strong>';
                        if ($vitals_k == 'temp') {
                            $data['vitals'] .= $vitalsInfo->{$vitals_k} . ' ' . $vitals_v['unit'] . ', ' . $vitalsInfo->temp_method . '<br>';
                        } else {
                            $data['vitals'] .= $vitalsInfo->{$vitals_k} . ' ' . $vitals_v['unit'] . '<br>';
                        }
                    } elseif ($vitals_k == 'bp_systolic') {
                        $data['vitals'] .= '<strong>' . trans('nosh.blood_pressure') . ': </strong>';
                        $data['vitals'] .= $vitalsInfo->bp_systolic . '/' . $vitalsInfo->bp_diastolic . ' mmHg, ' . $vitalsInfo->bp_position . '<br>';
                    }
                }
            }
            foreach ($vitals_arr1 as $vitals_k1 => $vitals_v1) {
                if (!empty($vitalsInfo->{$vitals_k1})) {
                    $data['vitals'] .= '<strong>' . $vitals_v1 . ': </strong>';
                    $data['vitals'] .= $vitalsInfo->{$vitals_k1} . '<br>';
                }
            }
            if (!empty($vitalsInfo->vitals_other)) {
                $data['vitals'] .= '<strong>' . trans('nosh.vitals_other') . ': </strong>';
                $data['vitals'] .= nl2br($vitalsInfo->vitals_other) . '<br>';
            }
            $data['vitals'] .= '</p>';
        }
        $peInfo = DB::table('pe')->where('eid', '=', $eid)->first();
        if ($peInfo) {
            $pe_arr = $this->array_pe();
            $data['pe'] = '<br><h4>' . trans('nosh.pe') . ':</h4><p class="view">';
            foreach ($pe_arr as $pe_k => $pe_v) {
                if ($peInfo->{$pe_k} !== '' && $peInfo->{$pe_k} !== null) {
                    if ($pe_k !== 'pe') {
                        $data['pe'] .= '<strong>' . $pe_v . ': </strong>';
                    }
                    $data['pe'] .= nl2br($peInfo->{$pe_k});
                    $data['pe'] .= '<br /><br />';
                }
            }
            $data['pe'] .= '</p>';
        }
        $imagesInfo = DB::table('image')->where('eid', '=', $eid)->get();
        $html = '';
        if ($imagesInfo->count()) {
            $data['images'] = '<br><h4>' . trans('nosh.images') . ':</h4><p class="view">';
            $k = 0;
            foreach ($imagesInfo as $imagesInfo_row) {
                $image_location = str_replace($practiceInfo->documents_dir, env('DOCUMENTS_DIR') . "/", $imagesInfo_row->image_location);
                $directory = env('DOCUMENTS_DIR') . "/" . $pid . "/";
                $new_directory = public_path() . '/temp/';
                $new_directory1 = '/temp/';
                $file_path = str_replace($directory, $new_directory, $image_location);
                $file_path1 = str_replace($directory, $new_directory1, $image_location);
                copy($image_location, $file_path);
                if ($k != 0) {
                    $data['images'] .= '<br><br>';
                }
                $data['images'] .= HTML::image($file_path1, 'Image', array('border' => '0'));
                if ($imagesInfo_row->image_description != '') {
                    $data['images'] .= '<br>' . $imagesInfo_row->image_description . '<br>';
                }
                $k++;
            }
        }
        $labsInfo = DB::table('labs')->where('eid', '=', $eid)->first();
        if ($labsInfo) {
            $labs_arr = $this->array_labs();
            $data['labs'] = '<br><h4>' . trans('nosh.laboratory_testing') . ':</h4><p class="view">';
            if ($labsInfo->labs_ua_urobili != '' || $labsInfo->labs_ua_bilirubin != '' || $labsInfo->labs_ua_ketones != '' || $labsInfo->labs_ua_glucose != '' || $labsInfo->labs_ua_protein != '' || $labsInfo->labs_ua_nitrites != '' || $labsInfo->labs_ua_leukocytes != '' || $labsInfo->labs_ua_blood != '' || $labsInfo->labs_ua_ph != '' || $labsInfo->labs_ua_spgr != '' || $labsInfo->labs_ua_color != '' || $labsInfo->labs_ua_clarity != ''){
                $data['labs'] .= '<strong>' . trans('nosh.dipstick_ua') . ':</strong><br /><table>';
                foreach ($labs_arr['ua'] as $labs_ua_k => $labs_ua_v) {
                    if ($labsInfo->{$labs_ua_k} !== '' && $labsInfo->{$labs_ua_k} !== null) {
                        $data['labs'] .= '<tr><th align=\"left\">' . $labs_ua_v . ':</th><td align=\"left\">' . $labsInfo->{$labs_ua_k} . '</td></tr>';
                    }
                }
                $data['labs'] .= '</table>';
            }
            foreach ($labs_arr['single'] as $labs_single_k => $labs_single_v) {
                if ($labsInfo->{$labs_single_k} !== '' && $labsInfo->{$labs_single_k} !== null) {
                    $data['labs'] .= '<strong>' . $labs_single_v . ': </strong>';
                    $data['labs'] .= $labsInfo->{$labs_single_k};
                    $data['labs'] .= '<br /><br />';
                }
            }
            $data['labs'] .= '</p>';
        }
        $assessmentInfo = DB::table('assessment')->where('eid', '=', $eid)->first();
        if ($assessmentInfo) {
            $assessment_arr = $this->array_assessment();
            $data['assessment'] = '<br><h4>' . trans('nosh.assessment') . ':</h4><p class="view">';
            for ($l = 1; $l <= 12; $l++) {
                $col0 = 'assessment_' . $l;
				// GYN 20181006: Add ICD code to assessment display
				$col1 = 'assessment_icd' . $l;
                if (!empty($assessmentInfo->{$col0})) {
                    if ($l > 1) {
                        $data['assessment'] .= '<br />';
                    }
                    $data['assessment'] .= '<strong>' . $assessmentInfo->{$col0};
					if (!empty($assessmentInfo->{$col1})) {
						$data['assessment'] .= ' [' . $assessmentInfo->{$col1} . ']';
					}
					$data['assessment'] .= '</strong><br />';
                }
            }
            foreach ($assessment_arr as $assessment_k => $assessment_v) {
                if ($assessmentInfo->{$assessment_k} !== '' && $assessmentInfo->{$assessment_k} !== null) {
                    if ($encounterInfo->encounter_template == 'standardmtm') {
                        $data['assessment'] .= '<strong>' . $assessment_v['standardmtm'] . ': </strong>';
                    } else {
                        $data['assessment'] .= '<strong>' . $assessment_v['standard'] . ': </strong>';
                    }
                    $data['assessment'] .= nl2br($assessmentInfo->{$assessment_k});
                    $data['assessment'] .= '<br /><br />';
                }
            }
            $data['assessment'] .= '</p>';
        }
        $procedureInfo = DB::table('procedure')->where('eid', '=', $eid)->first();
        if ($procedureInfo) {
            $procedure_arr = $this->array_procedure();
            $data['procedure'] = '<br><h4>' . trans('nosh.procedures') . ':</h4><p class="view">';
            foreach ($procedure_arr as $procedure_k => $procedure_v) {
                if ($procedureInfo->{$procedure_k} !== '' && $procedureInfo->{$procedure_k} !== null) {
                    if ($procedure_k == 'proc_description') {
                        if ($this->yaml_check($procedureInfo->{$procedure_k})) {
                            $proc_search_arr = ['code:', 'timestamp:', 'procedure:', 'type:', '---' . "\n", '|'];
                            $proc_replace_arr = ['<b>' . trans('nosh.procedure_code') . ':</b>', '<b>When:</b>', '<b>' . trans('nosh.procedure_description') . ':</b>', '<b>Type:</b>', '', ''];
                            $data['procedure'] .= nl2br(str_replace($proc_search_arr, $proc_replace_arr, $procedureInfo->{$procedure_k}));
                        } else {
                            $data['procedure'] .= '<strong>' . $procedure_v . ': </strong>';
                            $data['procedure'] .= nl2br($procedureInfo->{$procedure_k});
                            $data['procedure'] .= '<br /><br />';
                        }
                    } else {
                        $data['procedure'] .= '<strong>' . $procedure_v . ': </strong>';
                        $data['procedure'] .= nl2br($procedureInfo->{$procedure_k});
                        $data['procedure'] .= '<br /><br />';
                    }
                }
            }
            $data['procedure'] .= '</p>';
        }
        $ordersInfo1 = DB::table('orders')->where('eid', '=', $eid)->get();
        if ($ordersInfo1->count()) {
            $data['orders'] = '<br><h4>' . trans('nosh.orders') . ':</h4><p class="view">';
            $orders_lab_array = [];
            $orders_radiology_array = [];
            $orders_cp_array = [];
            $orders_referrals_array = [];
            foreach ($ordersInfo1 as $ordersInfo) {
                $address_row1 = DB::table('addressbook')->where('address_id', '=', $ordersInfo->address_id)->first();
                if ($address_row1) {
                    $orders_displayname = $address_row1->displayname;
                    if ($ordersInfo->orders_referrals != '') {
                        $orders_displayname = $address_row1->specialty . ': ' . $address_row1->displayname;
                    }
                } else {
                    $orders_displayname = 'Unknown';
                }
                if ($ordersInfo->orders_labs != '') {
                    $orders_lab_array[] = trans('nosh.orders_sent_to') . ' ' . $orders_displayname . ': '. nl2br($ordersInfo->orders_labs) . '<br />';
                }
                if ($ordersInfo->orders_radiology != '') {
                    $orders_radiology_array[] = trans('nosh.orders_sent_to') . ' ' . $orders_displayname . ': '. nl2br($ordersInfo->orders_radiology) . '<br />';
                }
                if ($ordersInfo->orders_cp != '') {
                    $orders_cp_array[] = trans('nosh.orders_sent_to') . ' ' . $orders_displayname . ': '. nl2br($ordersInfo->orders_cp) . '<br />';
                }
                if ($ordersInfo->orders_referrals != '') {
                    $orders_referrals_array[] = trans('nosh.referral_sent_to') . ' ' . $orders_displayname . ': '. nl2br($ordersInfo->orders_referrals) . '<br />';
                }
            }
            if (! empty($orders_lab_array)) {
                $data['orders'] .= '<strong>' . trans('nosh.laboratory') . ': </strong><br>';
                foreach ($orders_lab_array as $lab_item) {
                    $data['orders'] .= $lab_item;
                }
            }
            if (! empty($orders_radiology_array)) {
                $data['orders'] .= '<strong>' . trans('nosh.imaging') . ': </strong><br>';
                foreach ($orders_radiology_array as $radiology_item) {
                    $data['orders'] .= $radiology_item;
                }
            }
            if (! empty($orders_cp_array)) {
                $data['orders'] .= '<strong>' . trans('nosh.cardiopulmonary') . ': </strong><br>';
                foreach ($orders_cp_array as $cp_item) {
                    $data['orders'] .= $cp_item;
                }
            }
            if (! empty($orders_referrals_array)) {
                $data['orders'] .= '<strong>' . trans('nosh.referrals') . ': </strong><br>';
                foreach ($orders_referrals_array as $referrals_item) {
                    $data['orders'] .= $referrals_item;
                }
            }
            $data['orders'] .= '</p>';
        }
        $rxInfo = DB::table('rx')->where('eid', '=', $eid)->first();
        if ($rxInfo) {
            $rx_arr = $this->array_rx();
            $data['rx'] = '<br><h4>' . trans('nosh.prescriptions_imm') . ':</h4><p class="view">';
            foreach ($rx_arr as $rx_k => $rx_v) {
                if ($rxInfo->{$rx_k} !== '' && $rxInfo->{$rx_k} !== null) {
                    $data['rx'] .= '<strong>' . $rx_v . ': </strong><br>';
                    $data['rx'] .= nl2br($rxInfo->{$rx_k});
                    if ($rx_k == 'rx_immunizations') {
                        $data['rx'] .= trans('nosh.imm_disclaimer') . '.<br />';
                    }
                    $data['rx'] .= '<br /><br />';
                }
            }
            $data['rx'] .= '</p>';
        }
        $planInfo = DB::table('plan')->where('eid', '=', $eid)->first();
        if ($planInfo) {
            $plan_arr = $this->array_plan();
            $data['plan'] = '<br><h4>' . trans('nosh.plan') . ':</h4><p class="view">';
            foreach ($plan_arr as $plan_k => $plan_v) {
                if ($planInfo->{$plan_k} !== '' && $planInfo->{$plan_k} !== null) {
                    $data['plan'] .= '<strong>' . $plan_v . ': </strong>';
                    $data['plan'] .= nl2br($planInfo->{$plan_k});
                    if ($plan_k == 'duration') {
                        $data['plan'] .= ' ' . lcfirst(trans('nosh.minutes'));
                    }
                    $data['plan'] .= '<br /><br />';
                }
            }
            $data['plan'] .= '</p>';
        }
        $billing_query = DB::table('billing_core')->where('eid', '=', $eid)->get();
        if ($billing_query->count()) {
            $data['billing'] = '<p class="view">';
            $billing_count = 0;
            foreach ($billing_query as $billing_row) {
                if ($billing_count > 0) {
                    $data['billing'] .= ',' . $billing_row->cpt;
                } else {
                    $data['billing'] .= '<strong>' . trans('nosh.cpt_codes') . ': </strong>';
                    $data['billing'] .= $billing_row->cpt;
                }
                $billing_count++;
            }
            if ($encounterInfo->bill_complex != '') {
                $data['billing'] .= '<br><strong>' . trans('nosh.bill_complex') . ': </strong>';
                $data['billing'] .= nl2br($encounterInfo->bill_complex);
                $data['billing'] .= '<br /><br />';
            }
            $data['billing'] .= '</p>';
        }
        if ($encounterInfo->encounter_signed == 'No') {
            $data['status']    = trans('nosh.draft');
        } else {
            $data['status'] = trans('nosh.signed_on') . ' ' . date('F jS, Y', $this->human_to_unix($encounterInfo->date_signed)) . '.';
        }
        App::setLocale(Session::get('user_locale'));
        if ($modal == true) {
            if ($addendum == true) {
                $data['addendum'] = true;
            } else {
                $data['addendum'] = false;
            }
            return view('encounter', $data);
        } else {
            return view('encounter', $data);
        }
    }

    protected function gc_bmi_age($sex, $pid)
    {
        $type = 'bmi-age';
        $data['patient'] = $this->gc_bmi_chart($pid);
        $data['graph_y_title'] = 'kg/m2';
        $array = $this->gc_spline($type, $sex);
        $myComparator = function($a, $b) use ($array) {
            return $a["Age"] - $b["Age"];
        };
        usort($array, $myComparator);
        foreach ($array as $row) {
            $data['categories'][] = (float) $row['Age'];
            $data['P5'][] = (float) $row['P5'];
            $data['P10'][] = (float) $row['P10'];
            $data['P25'][] = (float) $row['P25'];
            $data['P50'][] = (float) $row['P50'];
            $data['P75'][] = (float) $row['P75'];
            $data['P90'][] = (float) $row['P90'];
            $data['P95'][] = (float) $row['P95'];
        }
        $data['graph_x_title'] = trans('nosh.age_days');
        $val = end($data['patient']);
        $age = round($val[0]);
        $x = $val[1];
        $lms = $this->gc_lms($type, $sex, $age);
        $l = $lms['L'];
        $m = $lms['M'];
        $s = $lms['S'];
        $val1 = $x / $m;
        if ($lms['L'] != '0') {
            $val2 = pow($val1, $l);
            $val2 = $val2 - 1;
            $val3 = $l * $s;
            $zscore = $val2 / $val3;
        } else {
            $val4 = log($val1);
            $zscore = $val4 / $s;
        }
        $percentile = $this->gc_cdf($zscore) * 100;
        $percentile = round($percentile);
        $data['percentile'] = strval($percentile);
        $data['categories'] = json_encode($data['categories']);
        $data['P5'] = json_encode($data['P5']);
        $data['P10'] = json_encode($data['P10']);
        $data['P25'] = json_encode($data['P25']);
        $data['P50'] = json_encode($data['P50']);
        $data['P75'] = json_encode($data['P75']);
        $data['P90'] = json_encode($data['P90']);
        $data['P95'] = json_encode($data['P95']);
        $data['patient'] = json_encode($data['patient']);
        return $data;
    }

    protected function gc_bmi_chart($pid)
    {
        $query = DB::table('vitals')
            ->select('BMI', 'pedsage')
            ->where('pid', '=', $pid)
            ->where('BMI', '!=', '')
            ->orderBy('pedsage', 'asc')
            ->get();
        if ($query) {
            $vals = [];
            $i = 0;
            foreach ($query as $row) {
                $x = $row->pedsage * 2629743 / 86400;
                if ($x <= 1856) {
                    $vals[$i][] = $x;
                    $vals[$i][] = (float) $row->BMI;
                    $i++;
                }
            }
            return $vals;
        } else {
            return FALSE;
        }
    }

    protected function gc_cdf($n)
    {
        if($n < 0) {
            return (1 - $this->gc_erf($n / sqrt(2)))/2;
        } else {
            return (1 + $this->gc_erf($n / sqrt(2)))/2;
        }
    }

    protected function gc_erf($x)
    {
        $pi = 3.1415927;
        $a = (8*($pi - 3))/(3*$pi*(4 - $pi));
        $x2 = $x * $x;
        $ax2 = $a * $x2;
        $num = (4/$pi) + $ax2;
        $denom = 1 + $ax2;
        $inner = (-$x2)*$num/$denom;
        $erf2 = 1 - exp($inner);
        return sqrt($erf2);
    }

    protected function gc_head_age($sex, $pid)
    {
        $type = 'head-age';
        $data['patient'] = $this->gc_hc_chart($pid);
        $data['graph_y_title'] = 'cm';
        $array = $this->gc_spline($type, $sex);
        $myComparator = function($a, $b) use ($array) {
            return $a["Age"] - $b["Age"];
        };
        usort($array, $myComparator);
        foreach ($array as $row) {
            $data['categories'][] = (float) $row['Age'];
            $data['P5'][] = (float) $row['P5'];
            $data['P10'][] = (float) $row['P10'];
            $data['P25'][] = (float) $row['P25'];
            $data['P50'][] = (float) $row['P50'];
            $data['P75'][] = (float) $row['P75'];
            $data['P90'][] = (float) $row['P90'];
            $data['P95'][] = (float) $row['P95'];
        }
        $data['graph_x_title'] = trans('nosh.age_days');
        $val = end($data['patient']);
        $age = round($val[0]);
        $x = $val[1];
        $lms = $this->gc_lms($type, $sex, $age);
        $l = $lms['l'];
        $m = $lms['m'];
        $s = $lms['s'];
        $val1 = $x / $m;
        if ($lms['l'] != '0') {
            $val2 = pow($val1, $l);
            $val2 = $val2 - 1;
            $val3 = $l * $s;
            $zscore = $val2 / $val3;
        } else {
            $val4 = log($val1);
            $zscore = $val4 / $s;
        }
        $percentile = $this->gc_cdf($zscore) * 100;
        $percentile = round($percentile);
        $data['percentile'] = strval($percentile);
        $data['categories'] = json_encode($data['categories']);
        $data['P5'] = json_encode($data['P5']);
        $data['P10'] = json_encode($data['P10']);
        $data['P25'] = json_encode($data['P25']);
        $data['P50'] = json_encode($data['P50']);
        $data['P75'] = json_encode($data['P75']);
        $data['P90'] = json_encode($data['P90']);
        $data['P95'] = json_encode($data['P95']);
        $data['patient'] = json_encode($data['patient']);
        return $data;
    }

    protected function gc_hc_chart($pid)
    {
        $query = DB::table('vitals')
            ->select('headcircumference', 'pedsage')
            ->where('pid', '=', $pid)
            ->where('headcircumference', '!=', '')
            ->orderBy('pedsage', 'asc')
            ->get();
        if ($query) {
            $vals = [];
            $i = 0;
            foreach ($query as $row) {
                $row1 = DB::table('practiceinfo')->first();
                if ($row1->hc_unit == 'in') {
                    $y = $row->headcircumference * 2.54;
                } else {
                    $y = $row->headcircumference * 1;
                }
                $x = $row->pedsage * 2629743 / 86400;
                if ($x <= 1856) {
                    $vals[$i][] = $x;
                    $vals[$i][] = $y;
                    $i++;
                }
            }
            return $vals;
        } else {
            return FALSE;
        }
    }

    protected function gc_height_age($sex, $pid)
    {
        $type = 'height-age';
        $data['patient'] = $this->gc_height_chart($pid);
        $data['graph_y_title'] = 'cm';
        $array = $this->gc_spline($type, $sex);
        $myComparator = function($a, $b) use ($array) {
            return $a["Day"] - $b["Day"];
        };
        usort($array, $myComparator);
        foreach ($array as $row) {
            $data['categories'][] = (float) $row['Day'];
            $data['P5'][] = (float) $row['P5'];
            $data['P10'][] = (float) $row['P10'];
            $data['P25'][] = (float) $row['P25'];
            $data['P50'][] = (float) $row['P50'];
            $data['P75'][] = (float) $row['P75'];
            $data['P90'][] = (float) $row['P90'];
            $data['P95'][] = (float) $row['P95'];
        }
        $data['graph_x_title'] = trans('nosh.age_days');
        $val = end($data['patient']);
        $age = round($val[0]);
        $x = $val[1];
        $lms = $this->gc_lms($type, $sex, $age);
        $l = $lms['L'];
        $m = $lms['M'];
        $s = $lms['S'];
        $val1 = $x / $m;
        if ($lms['L'] != '0') {
            $val2 = pow($val1, $l);
            $val2 = $val2 - 1;
            $val3 = $l * $s;
            $zscore = $val2 / $val3;
        } else {
            $val4 = log($val1);
            $zscore = $val4 / $s;
        }
        $percentile = $this->gc_cdf($zscore) * 100;
        $percentile = round($percentile);
        $data['percentile'] = strval($percentile);
        $data['categories'] = json_encode($data['categories']);
        $data['P5'] = json_encode($data['P5']);
        $data['P10'] = json_encode($data['P10']);
        $data['P25'] = json_encode($data['P25']);
        $data['P50'] = json_encode($data['P50']);
        $data['P75'] = json_encode($data['P75']);
        $data['P90'] = json_encode($data['P90']);
        $data['P95'] = json_encode($data['P95']);
        $data['patient'] = json_encode($data['patient']);
        return $data;
    }

    protected function gc_height_chart($pid)
    {
        $query = DB::table('vitals')
            ->select('height', 'pedsage')
            ->where('pid', '=', $pid)
            ->where('height', '!=', '')
            ->orderBy('pedsage', 'asc')
            ->get();
        if ($query) {
            $vals = [];
            $i = 0;
            foreach ($query as $row) {
                $row1 = DB::table('practiceinfo')->first();
                if ($row1->height_unit == 'in') {
                    $y = $row->height * 2.54;
                } else {
                    $y = $row->height * 1;
                }
                $x = $row->pedsage * 2629743 / 86400;
                if ($x <= 1856) {
                    $vals[$i][] = $x;
                    $vals[$i][] = $y;
                    $i++;
                }
            }
            return $vals;
        } else {
            return FALSE;
        }
    }

    protected function gc_lms($style, $sex, $age)
    {
        $gc = $this->array_gc();
        $result = $this->csv_to_array(resource_path() . '/' . $gc[$style][$sex], "\t", true);
        $result1a = Arr::where($result, function($value, $key) use ($age, $style) {
            if ($style == 'height-age') {
                if ($value['Day'] == $age) {
                    return true;
                }
            } else {
                if ($value['Age'] == $age) {
                    return true;
                }
            }
        });
        $result = head($result1a);
        return $result;
    }

    protected function gc_lms1($style, $sex, $length)
    {
        $gc = $this->array_gc();
        $result = $this->csv_to_array(resource_path() . '/' . $gc[$style][$sex], "\t", true);
        $result1a = Arr::where($result, function($value, $key) use ($length) {
            if ($value['Length'] == $length) {
                return true;
            }
        });
        $result = head($result1a);
        return $result;
    }

    protected function gc_lms2($style, $sex, $height)
    {
        $gc = $this->array_gc();
        $result = $this->csv_to_array(resource_path() . '/' . $gc[$style][$sex], "\t", true);
        $result1a = Arr::where($result, function($value, $key) use ($height) {
            if ($value['Height'] == $height) {
                return true;
            }
        });
        $result = head($result1a);
        return $result;
    }

    protected function gc_spline($style, $sex)
    {
        $gc = $this->array_gc();
        $result = $this->csv_to_array(resource_path() . '/' . $gc[$style][$sex], "\t", true);
        return $result;
    }

    protected function gc_weight_age($sex, $pid)
    {
        $type = 'weight-age';
        $data['patient'] = $this->gc_weight_chart($pid);
        $data['graph_y_title'] = 'kg';
        $array = $this->gc_spline($type, $sex);
        $myComparator = function($a, $b) use ($array) {
            return $a["Age"] - $b["Age"];
        };
        usort($array, $myComparator);
        foreach ($array as $row) {
            $data['categories'][] = (float) $row['Age'];
            $data['P5'][] = (float) $row['P5'];
            $data['P10'][] = (float) $row['P10'];
            $data['P25'][] = (float) $row['P25'];
            $data['P50'][] = (float) $row['P50'];
            $data['P75'][] = (float) $row['P75'];
            $data['P90'][] = (float) $row['P90'];
            $data['P95'][] = (float) $row['P95'];
        }
        $data['graph_x_title'] = trans('nosh.age_days');
        $val = end($data['patient']);
        $age = round($val[0]);
        $x = $val[1];
        $lms = $this->gc_lms($type, $sex, $age);
        $l = $lms['L'];
        $m = $lms['M'];
        $s = $lms['S'];
        $val1 = $x / $m;
        $data['val1'] = $val1;
        if ($lms['L'] != '0') {
            $val2 = pow($val1, $l);
            $val2 = $val2 - 1;
            $val3 = $l * $s;
            $zscore = $val2 / $val3;
        } else {
            $val4 = log($val1);
            $zscore = $val4 / $s;
        }
        $percentile = $this->gc_cdf($zscore) * 100;
        $percentile = round($percentile);
        $data['percentile'] = strval($percentile);
        $data['categories'] = json_encode($data['categories']);
        $data['P5'] = json_encode($data['P5']);
        $data['P10'] = json_encode($data['P10']);
        $data['P25'] = json_encode($data['P25']);
        $data['P50'] = json_encode($data['P50']);
        $data['P75'] = json_encode($data['P75']);
        $data['P90'] = json_encode($data['P90']);
        $data['P95'] = json_encode($data['P95']);
        $data['patient'] = json_encode($data['patient']);
        return $data;
    }

    protected function gc_weight_chart($pid)
    {
        $query = DB::table('vitals')
            ->select('weight', 'pedsage')
            ->where('pid', '=', $pid)
            ->where('weight', '!=', '')
            ->orderBy('pedsage', 'asc')
            ->get();
        if ($query) {
            $vals = [];
            $i = 0;
            foreach ($query as $row) {
                $row1 = DB::table('practiceinfo')->first();
                if ($row1->weight_unit == 'lbs') {
                    $y = $row->weight / 2.20462262185;
                } else {
                    $y = $row->weight * 1;
                }
                $x = $row->pedsage * 2629743 / 86400;
                if ($x <= 1856) {
                    $vals[$i][] = $x;
                    $vals[$i][] = $y;
                    $i++;
                }
            }
            return $vals;
        } else {
            return FALSE;
        }
    }

    protected function gc_weight_height($sex, $pid)
    {
        $data['patient'] = $this->gc_weight_height_chart($pid);
        $data['graph_y_title'] = 'kg';
        $data['graph_x_title'] = 'cm';
        $query = DB::table('vitals')
            ->select('weight', 'height', 'pedsage')
            ->where('pid', '=', $pid)
            ->where('weight', '!=', '')
            ->where('height', '!=', '')
            ->orderBy('pedsage', 'desc')
            ->first();
        $pedsage = $query->pedsage * 2629743 / 86400;
        if ($pedsage <= 730) {
            $type = 'weight-length';
            $array1 = $this->gc_spline($type, $sex);
            $myComparator = function($a, $b) use ($array1) {
                if ($a["Length"] == $b["Length"]) {
                    return 0;
                }
                return ($a["Length"] < $b["Length"]) ? -1 : 1;
            };
            usort($array1, $myComparator);
            $i = 0;
            foreach ($array1 as $row1) {
                $data['P5'][$i][] = (float) $row1['Length'];
                $data['P5'][$i][] = (float) $row1['P5'];
                $data['P10'][$i][] = (float) $row1['Length'];
                $data['P10'][$i][] = (float) $row1['P10'];
                $data['P25'][$i][] = (float) $row1['Length'];
                $data['P25'][$i][] = (float) $row1['P25'];
                $data['P50'][$i][] = (float) $row1['Length'];
                $data['P50'][$i][] = (float) $row1['P50'];
                $data['P75'][$i][] = (float) $row1['Length'];
                $data['P75'][$i][] = (float) $row1['P75'];
                $data['P90'][$i][] = (float) $row1['Length'];
                $data['P90'][$i][] = (float) $row1['P90'];
                $data['P95'][$i][] = (float) $row1['Length'];
                $data['P95'][$i][] = (float) $row1['P95'];
                $i++;
            }
        } else {
            $type = 'weight-height';
            $array2 = $this->gc_spline($type, $sex);
            $myComparator = function($a, $b) use ($array2) {
                if ($a["Height"] == $b["Height"]) {
                    return 0;
                }
                return ($a["Height"] < $b["Height"]) ? -1 : 1;
            };
            usort($array2, $myComparator);
            $j = 0;
            foreach ($array2 as $row1) {
                $data['P5'][$j][] = (float) $row1['Height'];
                $data['P5'][$j][] = (float) $row1['P5'];
                $data['P10'][$j][] = (float) $row1['Height'];
                $data['P10'][$j][] = (float) $row1['P10'];
                $data['P25'][$j][] = (float) $row1['Height'];
                $data['P25'][$j][] = (float) $row1['P25'];
                $data['P50'][$j][] = (float) $row1['Height'];
                $data['P50'][$j][] = (float) $row1['P50'];
                $data['P75'][$j][] = (float) $row1['Height'];
                $data['P75'][$j][] = (float) $row1['P75'];
                $data['P90'][$j][] = (float) $row1['Height'];
                $data['P90'][$j][] = (float) $row1['P90'];
                $data['P95'][$j][] = (float) $row1['Height'];
                $data['P95'][$j][] = (float) $row1['P95'];
                $j++;
            }
        }
        $val = end($data['patient']);
        $length = round($val[0]);
        $data['length'] = $length;
        $x = $val[1];
        if ($pedsage <= 730) {
            $lms = $this->gc_lms1($type, $sex, $length);
        } else {
            $lms = $this->gc_lms2($type, $sex, $length);
        }
        $percentile = 0;
        if (! empty($lms)) {
            $l = $lms['L'];
            $m = $lms['M'];
            $s = $lms['S'];
            $val1 = $x / $m;
            if ($lms['L'] != '0') {
                $val2 = pow($val1, $l);
                $val2 = $val2 - 1;
                $val3 = $l * $s;
                $zscore = $val2 / $val3;
            } else {
                $val4 = log($val1);
                $zscore = $val4 / $s;
            }
            $percentile = $this->gc_cdf($zscore) * 100;
            $percentile = round($percentile);
        }
        $data['percentile'] = strval($percentile);
        // $data['categories'] = json_encode($data['categories']);
        $data['P5'] = json_encode($data['P5']);
        $data['P10'] = json_encode($data['P10']);
        $data['P25'] = json_encode($data['P25']);
        $data['P50'] = json_encode($data['P50']);
        $data['P75'] = json_encode($data['P75']);
        $data['P90'] = json_encode($data['P90']);
        $data['P95'] = json_encode($data['P95']);
        $data['patient'] = json_encode($data['patient']);
        return $data;
    }

    protected function gc_weight_height_chart($pid)
    {
        $query = DB::table('vitals')
            ->select('weight', 'height', 'pedsage')
            ->where('pid', '=', $pid)
            ->where('weight', '!=', '')
            ->where('height', '!=', '')
            ->orderBy('pedsage', 'asc')
            ->get();
        if ($query) {
            $vals = [];
            $i = 0;
            foreach ($query as $row) {
                $row1 = DB::table('practiceinfo')->first();
                if ($row1->weight_unit == 'lbs') {
                    $y = $row->weight / 2.20462262185;
                } else {
                    $y = $row->weight * 1;
                }
                if ($row1->height_unit == 'in') {
                    $x = $row->height * 2.54;
                } else {
                    $x = $row->height * 1;
                }
                $pedsage = $row->pedsage * 2629743 / 86400;
                if ($pedsage <= 730) {
                    if ($x >= 45 && $x <= 110) {
                        $vals[$i][] = $x;
                        $vals[$i][] = $y;
                        $i++;
                    }
                } else {
                    if ($x >= 65 && $x <= 120) {
                        $vals[$i][] = $x;
                        $vals[$i][] = $y;
                        $i++;
                    }
                }
            }
            return $vals;
        } else {
            return FALSE;
        }
    }

    protected function gen_uuid()
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    protected function generate_pdf($html, $filepath, $footer='footerpdf', $header='', $type='1', $headerparam='', $watermark='')
    {
        // if ($header != '') {
        //     if ($headerparam == '') {
        //         $pdf_options['header-center'] = $header;
        //         $pdf_options['header-font-size'] = 8;
        //     } else {
        //         $header = route($header, array($headerparam));
        //         $header = str_replace("https", "http", $header);
        //         $pdf_options['header-html'] = $header;
        //         $pdf_options['header-spacing'] = 5;
        //     }
        // }
        if ($header !== '') {
            PDF::setHeaderCallback(function($pdf) use ($header, $headerparam) {
                // Set font
                $pdf->SetFont('helvetica', 'B', 10);
                // Title
                if ($header == 'mtmheaderpdf') {
                    $row = DB::table('demographics')->where('pid', '=', $headerparam)->first();
                    $date = explode(" ", $row->DOB);
                    $date1 = explode("-", $date[0]);
                    $patientDOB = $date1[1] . "/" . $date1[2] . "/" . $date1[0];
                    $patientInfo1 = $row->firstname . ' ' . $row->lastname;
                    $header_html = '<body style="font-size:0.8em;margin:0; padding:0;">';
                    $page = $pdf->PageNo();
                    if ($page > 1) {
                        $header_html .= '<div style="width:6.62in;text-align:center;border: 1px solid black;"><b style="font-variant: small-caps;">Personal Medication List For';
                        $header_html .= $row->firstname . ' ' . $row->lastname . ', ' . $date1[1] . "/" . $date1[2] . "/" . $date1[0] . '</b></div><br>(Continued)';
                    }
                    $header_html .= '</body>';
                } else {
                    $header_html = '<body style="font-size:0.8em;margin:0; padding:0;">';
                    $header_html .= $header;
                    $header_html .= '</body>';
                }
                $pdf->writeHTML($header_html, true, false, false, false, '');
            });
        }
        PDF::setFooterCallback(function($pdf) use ($footer) {
            // Position at 35 mm from bottom
            $pdf->SetY(-40);
            // Set font
            $pdf->SetFont('helvetica', 'I', 8);
            if ($footer == 'footerpdf') {
                $footer_html = '<div style="border-top: 1px solid #000000; text-align: center; padding-top: 3mm; font-size: 8px;">Page ' . $pdf->getAliasNumPage();
                $footer_html .= ' of ' . $pdf->getAliasNbPages() . '</div><p style="text-align:center; font-size: 8px;">';
                $footer_html .= trans('nosh.footer') . "</p>";
                $footer_html .= '<p style="text-align:center; font-size: 8px;">' . trans('nosh.footer1') . '</p>';
            }
            if ($footer == 'mtmfooterpdf') {
                $footer_html = '<div style="border-top: 1px solid #000000; font-family: Arial, Helvetica, sans-serif; font-size: 7;">';
                $footer_html .= '<table><tr><td>Form CMS-10396 (01/12)</td><td style="text-align:right;">Form Approved OMB No. 0938-1154</td></tr></table>';
                $footer_html .= '<div style="text-align:center; font-family: ' . "'Times New Roman'" . ', Times, serif; font-size: 12;">Page ' . $pdf->getAliasNumPage() . ' of ' . $pdf->getAliasNbPages() . '</div></div>';
            }
            // Page number
            $pdf->writeHTML($footer_html, true, false, false, false, '');
            // $pdf->Cell(0, 10, 'Page '.$pdf->getAliasNumPage().'/'.$pdf->getAliasNbPages(), 1, false, 'C', 0, '', 0, false, 'T', 'M');

        });
        PDF::SetAuthor('NOSH ChartingSystem');
        PDF::SetTitle('NOSH PDF Document');
        if ($type == '1') {
            PDF::SetMargins('26', '26' ,'26', true);
        }
        if ($type == '2') {
            PDF::SetMargins('16', '26' ,'16', true);
        }
        if ($type == '3') {
            PDF::SetMargins('16', '16' ,'16', true);
        }
        PDF::SetFooterMargin('40');
        PDF::SetFont('freeserif', '', 10);
        PDF::AddPage();
        PDF::SetAutoPageBreak(true, '40');
        PDF::writeHTML($html, true, false, false, false, '');
        if ($watermark !== '') {
            if ($watermark == 'void') {
                $watermark_file = 'voidstamp.png';
            }
            PDF::SetAlpha(0.5);
            PDF::Image(resource_path() . '/' . $watermark_file, '0', '0', '', '', 'PNG', false, 'C', false, 300, 'C', false, false, 0 ,false, false, false);
        }
        PDF::Output($filepath, 'F');
        PDF::reset();
        return true;
    }

    protected function header_build($arr, $type='')
    {
        $return = '<div class="card"><div class="card-header text-white bg-success"><div class="container-fluid panel-container"><div class="col-xs-8 text-left"><h6 class="card-title" style="height:25px;display:table-cell !important;vertical-align:middle;">';
        if (is_array($arr)) {
            $return .= $type . '</h6></div><div class="col-xs-4 text-right"><a href="' . $arr[$type] . '" class="btn btn-default btn-sm">Edit</a></div></div></div><div class="card-body"><div class="row">';
        } else {
            $return .= $arr . '</h6></div></div></div><div class="card-body">';
        }
        return $return;
    }

    protected function human_to_unix($datestr)
    {
        $datestr_arr = explode(' (', $datestr);
        $date = Date::parse($datestr_arr[0]);
        return $date->timestamp;
    }

    protected function page_ccr($pid)
    {
        $data['patientInfo'] = DB::table('demographics')->where('pid', '=', $pid)->first();
        $data['dob'] = date('m/d/Y', $this->human_to_unix($data['patientInfo']->DOB));
        $data['insuranceInfo'] = '';
        $query_in = DB::table('insurance')->where('pid', '=', $pid)->where('insurance_plan_active', '=', 'Yes')->get();
        if ($query_in) {
            foreach ($query_in as $row_in) {
                $data['insuranceInfo'] .= $row_in->insurance_plan_name . '; ID: ' . $row_in->insurance_id_num . '; Group: ' . $row_in->insurance_group . '; ' . $row_in->insurance_insu_lastname . ', ' . $row_in->insurance_insu_firstname . '<br><br>';
            }
        }
        $body = trans('nosh.active_issues') . ':<br />';
        $query = DB::table('issues')->where('pid', '=', $pid)->where('issue_date_inactive', '=', '0000-00-00 00:00:00')->get();
        if ($query) {
            $body .= '<ul>';
            foreach ($query as $row) {
                $body .= '<li>' . $row->issue . '</li>';
            }
            $body .= '</ul>';
        } else {
            $body .= trans('nosh.none') . '.';
        }
        $body .= '<hr />' . trans('nosh.active_medications') . ':<br />';
        $query1 = DB::table('rx_list')->where('pid', '=', $pid)->where('rxl_date_inactive', '=', '0000-00-00 00:00:00')->where('rxl_date_old', '=', '0000-00-00 00:00:00')->get();
        if ($query1) {
            $body .= '<ul>';
            foreach ($query1 as $row1) {
                if ($row1->rxl_sig == '') {
                    $body .= '<li>' . $row1->rxl_medication . ' ' . $row1->rxl_dosage . ' ' . $row1->rxl_dosage_unit . ', ' . $row1->rxl_instructions . ' for ' . $row1->rxl_reason . '</li>';
                } else {
                    $body .= '<li>' . $row1->rxl_medication . ' ' . $row1->rxl_dosage . ' ' . $row1->rxl_dosage_unit . ', ' . $row1->rxl_sig . ', ' . $row1->rxl_route . ', ' . $row1->rxl_frequency . ' for ' . $row1->rxl_reason . '</li>';
                }
            }
            $body .= '</ul>';
        } else {
            $body .= trans('nosh.none') . '.';
        }
        $body .= '<hr />' . trans('nosh.immunizations') . ':<br />';
        $query2 = DB::table('immunizations')->where('pid', '=', $pid)->orderBy('imm_immunization', 'asc')->orderBy('imm_sequence', 'asc')->get();
        if ($query2) {
            $body .= '<ul>';
            foreach ($query2 as $row2) {
                $sequence = '';
                if ($row2->imm_sequence == '1') {
                    $sequence = ', ' . lcfirst(trans('nosh.first')) . ',';;
                }
                if ($row2->imm_sequence == '2') {
                    $sequence = ', ' . lcfirst(trans('nosh.second')) . ',';;
                }
                if ($row2->imm_sequence == '3') {
                    $sequence = ', ' . lcfirst(trans('nosh.third')) . ',';;
                }
                if ($row2->imm_sequence == '4') {
                    $sequence = ', ' . lcfirst(trans('nosh.fourth')) . ',';;
                }
                if ($row2->imm_sequence == '5') {
                    $sequence = ', ' . lcfirst(trans('nosh.fifth')) . ',';;
                }
                $body .= '<li>' . $row2->imm_immunization . $sequence . ' ' . trans('nosh.given_on') . ' ' . date('F jS, Y', $this->human_to_unix($row2->imm_date)) . '</li>';
            }
            $body .= '</ul>';
        } else {
            $body .= trans('nosh.none') . '.';
        }
        $body .= '<hr />' . trans('nosh.allergies') . ':<br />';
        $query3 = DB::table('allergies')->where('pid', '=', $pid)->where('allergies_date_inactive', '=', '0000-00-00 00:00:00')->get();
        if ($query3) {
            $body .= '<ul>';
            foreach ($query3 as $row3) {
                $body .= '<li>' . $row3->allergies_med . ' - ' . $row3->allergies_reaction . '</li>';
            }
            $body .= '</ul>';
        } else {
            $body .= trans('nosh.nkda') . '.';
        }
        $body .= '<br />' . trans('nosh.printed_by') . ' ' . Session::get('displayname') . '.';
        $data['letter'] = $body;
        return view('pdf.ccr_page',$data);
    }

    protected function page_default()
    {
        $pid = Session::get('pid');
        $practice = DB::table('practiceinfo')->where('practice_id', '=', Session::get('practice_id'))->first();
        $data['practiceName'] = $practice->practice_name;
        $data['website'] = $practice->website;
        $data['practiceInfo1'] = $practice->street_address1;
        if ($practice->street_address2 != '') {
            $data['practiceInfo1'] .= ', ' . $practice->street_address2;
        }
        $data['practiceInfo2'] = $practice->city . ', ' . $practice->state . ' ' . $practice->zip;
        $data['practiceInfo3'] = trans('nosh.phone') . ': ' . $practice->phone . ', ' . trans('nosh.fax') . ': ' . $practice->fax;
        $patient = DB::table('demographics')->where('pid', '=', $pid)->first();
        $data['patientInfo1'] = $patient->firstname . ' ' . $patient->lastname;
        $data['patientInfo2'] = $patient->address;
        $data['patientInfo3'] = $patient->city . ', ' . $patient->state . ' ' . $patient->zip;
        $data['firstname'] = $patient->firstname;
        $data['lastname'] = $patient->lastname;
        $data['date'] = date('F jS, Y');
        $data['signature'] = $this->signature(Session::get('user_id'));
        return $data;
    }

    protected function page_intro($title, $practice_id)
    {
        if (Session::has('patient_locale')) {
            App::setLocale(Session::get('patient_locale'));
        }
        $practice = DB::table('practiceinfo')->where('practice_id', '=', $practice_id)->first();
        $data['practiceName'] = $practice->practice_name;
        $data['website'] = $practice->website;
        $data['practiceInfo'] = $practice->street_address1;
        if ($practice->street_address2 !== '') {
            $data['practiceInfo'] .= ', ' . $practice->street_address2;
        }
        $data['practiceInfo'] .= '<br />';
        $data['practiceInfo'] .= $practice->city . ', ' . $practice->state . ' ' . $practice->zip . '<br />';
        $data['practiceInfo'] .= trans('nosh.phone') . ': ' . $practice->phone . ', ' . trans('nosh.fax') . ': ' . $practice->fax . '<br />';
        $data['practiceLogo'] = $this->practice_logo($practice_id);
        $data['title'] = $title;
        App::setLocale(Session::get('user_locale'));
        return view('pdf.intro', $data);
    }

    protected function page_results_list($id)
    {
        if (Session::has('patient_locale')) {
            App::setLocale(Session::get('patient_locale'));
        }
        $data = $this->page_default();
        $test_arr = $this->array_test_flag();
        $row0 = DB::table('tests')->where('tests_id', '=', $id)->first();
        $query1 = DB::table('tests')
            ->where('test_name', '=', $row0->test_name)
            ->where('pid', '=', Session::get('pid'))
            ->orderBy('test_datetime', 'asc')
            ->get();
        $data['body'] = $row0->test_name . ' ' . trans('nosh.page_results2') . ' ' . $data['firstname'] . ' ' . $data['lastname'] . ':<br />';
        if ($query1->count()) {
            $data['body'] .= '<table border="1" cellpadding="5"><thead><tr><th>' . trans('nosh.date') . '</th><th>' . trans('nosh.test_result') . '</th><th>' . trans('nosh.unit') . '</th><th>' . trans('nosh.range') . '</th><th>' . trans('nosh.flags') . '</th></thead><tbody>';
            foreach ($query1 as $row1) {
                $data['body'] .= '<tr><td>' . date('Y-m-d', $this->human_to_unix($row1->test_datetime)) . '</td>';
                $data['body'] .= '<td>' . $row1->test_result . '</td>';
                $data['body'] .= '<td>' . $row1->test_units . '</td>';
                $data['body'] .= '<td>' . $row1->test_reference . '</td>';
                $data['body'] .= '<td>' . $test_arr[$row1->test_flags] . '</td></tr>';
            }
            $data['body'] .= '</tbody></table>';
        }
        $data['body'] .= '<br />' . trans('nosh.printed_by') . ' ' . Session::get('displayname') . '.';
        $data['date'] = date('F jS, Y');
        $data['signature'] = $this->signature(Session::get('user_id'));
        App::setLocale(Session::get('user_locale'));
        return view('pdf.letter_page', $data);
    }

    /**
     *    Print chart
     *
     *    @param    $hippa_id = Hippa ID
     * @param    $pid = Patient ID
     *  @param    $type = Options: all, queue, 1year
     */
    protected function print_chart($pid)
    {
        ini_set('memory_limit','196M');
        App::setLocale(Session::get('practice_locale'));
        $result = DB::table('practiceinfo')->first();
        $patient = DB::table('demographics')->where('pid', '=', $pid)->first();
        $lastname = str_replace(' ', '_', $patient->lastname);
        $firstname = str_replace(' ', '_', $patient->firstname);
        $dob = date('Ymd', $this->human_to_unix($patient->DOB));
        $filename_string = Str::random(30);
        $pdf_arr = [];
        // Generate encounters and messages
        $header = strtoupper($patient->lastname . ', ' . $patient->firstname . '(DOB: ' . date('m/d/Y', $this->human_to_unix($patient->DOB)) . ', Gender: ' . ucfirst(Session::get('gender')) . ', ID: ' . $pid . ')');
        $file_path_enc = public_path() . '/temp/' . time() . '_' . $filename_string . '_printchart.pdf';
        $html = $this->page_intro('Medical Records', $result->practice_id);
        $query1 = DB::table('encounters')
            ->where('pid', '=', $pid)
            ->where('encounter_signed', '=', 'Yes')
            ->where('addendum', '=', 'n')
            // ->where('practice_id', '=', Session::get('practice_id'))
            ->orderBy('encounter_DOS', 'desc')
            ->get();
        $query2 = DB::table('t_messages')
            ->where('pid', '=', $pid)
            ->where('t_messages_signed', '=', 'Yes')
            // ->where('practice_id', '=', Session::get('practice_id'))
            ->orderBy('t_messages_dos', 'desc')
            ->get();
        $query3 = DB::table('documents')
            ->where('pid', '=', $pid)
            ->orderBy('documents_date', 'desc')->get();
        if ($query1->count()) {
            $html .= '<table width="100%" style="font-size:1em"><tr><th style="background-color: gray;color: #FFFFFF;">' . strtoupper(trans('nosh.encounters')) . '</th></tr></table>';
            foreach ($query1 as $row1) {
                $html .= $this->encounters_view($row1->eid, $pid, $row1->practice_id);
            }
        }
        if ($query2->count()) {
            $html .= '<pagebreak /><table width="100%" style="font-size:1em"><tr><th style="background-color: gray;color: #FFFFFF;">' . strtoupper(trans('nosh.t_messages')) . '</th></tr></table>';
            foreach ($query2 as $row2) {
                $html .= $this->t_messages_view($row2->t_messages_id, true);
            }
        }
        $html .= '</body></html>';
        $this->generate_pdf($html, $file_path_enc, 'footerpdf', $header, '2');
        $pdf_arr[] = $file_path_enc;
        // Generate CCR
        $file_path_ccr = public_path() . '/temp/' . time() . '_' . $filename_string . '_ccr.pdf';
        $html_ccr = $this->page_intro(trans('nosh.ccr_description'), $result->practice_id);
        $html_ccr .= $this->page_ccr($pid);
        $this->generate_pdf($html_ccr, $file_path_ccr, 'footerpdf', $header, '2');
        $pdf_arr[] = $file_path_ccr;
        // Gather documents
        if ($query3->count()) {
            $practiceInfo = DB::table('practiceinfo')->first();
            foreach ($query3 as $row3) {
                $pdf_arr[] = str_replace($practiceInfo->documents_dir, env('DOCUMENTS_DIR') . "/", $row3->documents_url);
            }
        }
        // Compile and save file
        $pdf = new Merger();
        foreach ($pdf_arr as $pdf_item) {
            if (file_exists($pdf_item)) {
                $file_parts = pathinfo($pdf_item);
                if ($file_parts['extension'] == 'pdf') {
                    $pdf->addFromFile($pdf_item );
                }
            }
        }
        $file_path = public_path() . '/temp/' . time() . '_' . $filename_string . '_' . $pid . '_printchart_final.pdf';
        $pdf->merge();
        $pdf->save($file_path);
        App::setLocale(Session::get('user_locale'));
        return $file_path;
    }

    protected function practice_logo($practice_id, $size='80px')
    {
        $logo = '<br><br><br><br><br>';
        $practice = DB::table('practiceinfo')->where('practice_id', '=', $practice_id)->first();
        if ($practice->practice_logo !== '' && $practice->practice_logo !== null) {
            if (file_exists(public_path() . '/' . $practice->practice_logo)) {
                $link = HTML::image($practice->practice_logo, 'Practice Logo', array('border' => '0', 'height' => $size));
                $logo = str_replace('https', 'http', $link);
            }
        }
        return $logo;
    }

    /**
    * Result build
    * @param array  $list_array - ['label' => '', 'pid' => 'Patient ID', 'edit' => 'URL', 'delete' => 'URL', 'reactivate' => 'URL, 'inactivate' => 'URL', 'origin' => 'previous URL', 'active' => boolean, 'danger' => boolean, 'label_class' => 'class', 'label_data' => 'label information']
    * @param int $id - Item key in database
    * @return Response
    */
    protected function result_build($list_array, $id, $nosearch=false, $viewfirst=false, $table='')
    {
        $return = '';
        if ($nosearch == false) {
            $return .= '<div id="' . $id . '_div"><form><div class="form-row" style="margin-bottom:20px;"><div class="col-sm-6"><input class="form-control fuzzy-search mb-2 mr-sm-2" placeholder="Filter Results..." /></div>';
            $return .= '</div></form>';
        } else {
            if ($table !== '') {
                $return .= '<form><div class="form-row" style="margin-bottom:20px;"><div class="col"><a href="' . route('core_form', [$table, 'id', '0']) . '" class="btn btn-primary btn-sm"><i class="fa fa-fw fa-plus"></i></a></div></div></form>';
            }
        }
        $return .= '<ul class="list-group list" id="' . $id . '">';
        if (is_array($list_array)) {
            foreach ($list_array as $item) {
                $return .= '<li class="list-group-item container-fluid';
                if (isset($item['active'])) {
                    $return .= ' list-group-item-success';
                }
                if (isset($item['danger'])) {
                    $return .= ' list-group-item-danger';
                }
                if (isset($item['label_class'])) {
                    $return .= ' ' . $item['label_class'];
                } else {
                    $return .= ' nosh-result-list';
                }
                $return .= '"';
                if (isset($item['label_data'])) {
                    $return .= ' nosh-data="' . $item['label_data'] . '"';
                }
                if (isset($item['label_data_arr'])) {
                    foreach ($item['label_data_arr'] as $data_item_k => $data_item_v) {
                        $return .= ' ' . $data_item_k . '="' . $data_item_v . '"';
                    }
                }
                $return .= '><span class="nosh_list_item">' . $item['label'] . '</span><span class="pull-right">';
                if ($viewfirst == true) {
                    if (isset($item['view'])) {
                        $return .= '<a href="' . $item['view'] . '" class="btn fa-btn" data-toggle="tooltip" title="' . trans('nosh.view') . '"><i class="fa fa-eye fa-lg"></i></a>';
                    }
                    if (isset($item['edit'])) {
                        $return .= '<a href="' . $item['edit'] . '" class="btn fa-btn" data-toggle="tooltip" title="' . trans('nosh.edit') . '"><i class="fa fa-pencil fa-lg"></i></a>';
                    }
                } else {
                    if (isset($item['edit'])) {
                        $return .= '<a href="' . $item['edit'] . '" class="btn fa-btn" data-toggle="tooltip" title="' . trans('nosh.edit') . '"><i class="fa fa-pencil fa-lg"></i></a>';
                    }
                    if (isset($item['view'])) {
                        $return .= '<a href="' . $item['view'] . '" class="btn fa-btn" data-toggle="tooltip" title="' . trans('nosh.view') . '"><i class="fa fa-eye fa-lg"></i></a>';
                    }
                }
                if (isset($item['view_file'])) {
                    $return .= '<a href="' . $item['view_file'] . '" class="btn fa-btn" data-toggle="tooltip" title="' . trans('nosh.view_file') . '" target="_blank"><i class="fa fa-eye fa-lg"></i></a>';
                }
                if (isset($item['delete'])) {
                    $return .= '<a href="' . $item['delete'] . '" class="btn fa-btn nosh-delete" data-toggle="tooltip" title="' . trans('nosh.delete') . '"><i class="fa fa-trash fa-lg"></i></a>';
                }
                if (isset($item['tag_remove'])) {
                    $return .= '<button class="btn fa-btn nosh-tag-remove" data-toggle="tooltip" title="' . trans('nosh.delete') . '" nosh-tags-reference-id="' . $item['tag_remove'] . '"><i class="fa fa-trash fa-lg"></i></button>';
                }
                if (isset($item['file_remove'])) {
                    $return .= '<button class="btn fa-btn nosh-file-remove" data-toggle="tooltip" title="' . trans('nosh.delete') . '" nosh-files-reference-id="' . $item['file_remove'] . '"><i class="fa fa-trash fa-lg"></i></button>';
                }
                if (isset($item['reconcile'])) {
                    $return .= '<button class="btn fa-btn nosh-reconcile" data-toggle="tooltip" title="' . trans('nosh.reconcile') . '" nosh-reconcile-id="' . $item['reconcile'] . '" nosh-reconcile-amount="' . $item['reconcile_amount'] . '"><i class="fa fa-check fa-lg"></i></button>';
                }
                $return .= '</span>';
                $return .= '</li>';
            }
        }
        $return .= '</ul>';
        if ($nosearch == false) {
            $return .= '</div>';
        }
        return $return;
    }

    /**
    * Set patient into session
    * @param string  $pid - patient id
    * @return Response
    */
    protected function setpatient($pid)
    {
        $row = DB::table('demographics')->where('pid', '=', $pid)->first();
        $date = Date::parse($row->DOB);
        $dob1 = $date->timestamp;
        $age_arr = explode(',', $date->timespan());
        $gender_arr = [
            'm' => 'male',
            'f' => 'female',
            'u' => 'individual'
        ];
        Session::put('pid', $pid);
        Session::put('gender', $gender_arr[$row->sex]);
        Session::put('age', ucwords($age_arr[0] . ' Old'));
        Session::put('agealldays', $date->diffInDays(Date::now()));
        Session::put('ptname', $row->firstname . ' ' . $row->lastname);
        return true;
    }

    protected function sidebar_build($type)
    {
        $return = [];
        if ($type == 'chart') {
            $return['name'] = Session::get('ptname');
            $return['title'] = Session::get('ptname');
            // Demographics
            $demographics = DB::table('demographics')->where('pid', '=', Session::get('pid'))->first();
            if ($demographics->nickname !== '' && $demographics->nickname !== null) {
                $return['name'] .= ' (' . $demographics->nickname . ')';
            }
            $return['demographics_quick'] = '<h6 class="px-3"><strong>DOB: </strong>' . date('F jS, Y', strtotime($demographics->DOB)) . '</h6>';
            $return['demographics_quick'] .= '<h6 class="px-3"><strong>' . trans('nosh.sidebar1') . ': </strong>' . Session::get('age') . '</h6>';
            $return['demographics_quick'] .= '<h6 class="px-3"><strong>' . trans('nosh.sex') . ': </strong>' . ucfirst(Session::get('gender')) . '</h6>';
            // Vitals
            $return['growth_chart_show'] = 'no';
            if (Session::get('agealldays') < 6574.5) {
                $vitals = DB::table('vitals')->where('pid', '=', Session::get('pid'))->first();
                if ($vitals) {
                    $return['growth_chart_show'] = 'yes';
                }
            }
        }
        return $return;
    }

    protected function send_mail($template, $data_message, $subject, $to, $practice_id)
    {
        $practice = DB::table('practiceinfo')->where('practice_id', '=', $practice_id)->first();
        if (env('MAIL_HOST') == 'smtp.gmail.com') {
            $google = new Google_Client();
            $google->setClientID(env('GOOGLE_KEY'));
            $google->setClientSecret(env('GOOGLE_SECRET'));
            $google->refreshToken($practice->google_refresh_token);
            $credentials = $google->getAccessToken();
            $data1['smtp_pass'] = $credentials['access_token'];
            DB::table('practiceinfo')->where('practice_id', '=', $practice_id)->update($data1);
            $config['mail.password'] =  $credentials['access_token'];
            config($config);
            extract(Config::get('mail'));
        }
        if (env('MAIL_DRIVER') !== 'none') {
            Mail::send($template, $data_message, function ($message) use ($to, $subject, $practice) {
    			$message->to($to)
    				->from($practice->email, $practice->practice_name)
    				->subject($subject);
    		});
            App::setLocale(Session::get('user_locale'));
		    return trans('nosh.email_sent') . ".";
        }
        return true;
    }

    protected function timeline()
    {
        $pid = Session::get('pid');
        $json = [];
        $date_arr = [];
        $query0 = DB::table('encounters')->where('pid', '=', $pid)->where('addendum', '=', 'n')->get();
        if ($query0->count()) {
            foreach ($query0 as $row0) {
                $description = '';
                $procedureInfo = DB::table('procedure')->where('eid', '=', $row0->eid)->first();
                if ($procedureInfo) {
                    $description .= '<span class="nosh_bold">' . trans('nosh.procedures') . ':</span>';
                    if ($procedureInfo->proc_type != '') {
                        $description .= '<strong>Procedure: </strong>';
                        $description .= nl2br($procedureInfo->proc_type);
                    }
                }
                $assessmentInfo = DB::table('assessment')->where('eid', '=', $row0->eid)->first();
                if ($assessmentInfo) {
                    $assessment_arr = $this->array_assessment();
                    $description .= '<span class="nosh_bold">' . trans('nosh.assessment') . ':</span>';
                    for ($l = 1; $l <= 12; $l++) {
                        $col0 = 'assessment_' . $l;
                        if ($assessmentInfo->{$col0} !== '' && $assessmentInfo->{$col0} !== null) {
                            $description .= '<br><strong>' . $assessmentInfo->{$col0} . '</strong><br />';
                        }
                    }
                    foreach ($assessment_arr as $assessment_k => $assessment_v) {
                        if ($assessmentInfo->{$assessment_k} !== '' && $assessmentInfo->{$assessment_k} !== null) {
                            if ($row0->encounter_template == 'standardmtm') {
                                $description .= '<br /><strong>' . $assessment_v['standardmtm'] . ': </strong>';
                            } else {
                                $description .= '<br /><strong>' . $assessment_v['standard'] . ': </strong>';
                            }
                            $description .= nl2br($assessmentInfo->{$assessment_k});
                            $description .= '<br /><br />';
                        }
                    }
                }
                $encounter_status = 'y';
                if (Session::get('group_id') == '100' && $row0->encounter_signed == 'No') {
                    $encounter_status = 'n';
                    $description = trans('nosh.unsigned_encounter');
                }
                $div0 = $this->timeline_item($row0->eid, 'eid', 'Encounter', $this->human_to_unix($row0->encounter_DOS), trans('nosh.encounter') . ': ' . $row0->encounter_cc, $description, $encounter_status);
                $json[] = [
                    'div' => $div0,
                    'startDate' => $this->human_to_unix($row0->encounter_DOS)
                ];
                $date_arr[] = $this->human_to_unix($row0->encounter_DOS);
            }
        }
        $query1 = DB::table('t_messages')->where('pid', '=', $pid)->get();
        if ($query1->count()) {
            foreach ($query1 as $row1) {
                $div1 = $this->timeline_item($row1->t_messages_id, 't_messages_id', 'Telephone Message', $this->human_to_unix($row1->t_messages_dos), trans('nosh.t_message'), substr($row1->t_messages_message, 0, 500) . '...', $row1->t_messages_signed);
                $json[] = [
                    'div' => $div1,
                    'startDate' => $this->human_to_unix($row1->t_messages_dos)
                ];
                $date_arr[] = $this->human_to_unix($row1->t_messages_dos);
            }
        }
        $query2 = DB::table('rx_list')->where('pid', '=', $pid)->orderBy('rxl_date_active','asc')->groupBy('rxl_medication')->get();
        if ($query2->count()) {
            foreach ($query2 as $row2) {
                $row2a = DB::table('rx_list')->where('rxl_id', '=', $row2->rxl_id)->first();
                if ($row2->rxl_sig == '') {
                    $instructions = $row2->rxl_instructions;
                } else {
                    $instructions = $row2->rxl_sig . ', ' . $row2->rxl_route . ', ' . $row2->rxl_frequency;
                    if ($row2->rxl_instructions !== null && $row2->rxl_instructions !== '') {
                        $instructions .= ', ' . $row2->rxl_instructions;
                    }
                }
                $description2 = $row2->rxl_medication . ' ' . $row2->rxl_dosage . ' ' . $row2->rxl_dosage_unit . ', ' . $instructions . ' ' . trans('nosh.for') . ' ' . $row2->rxl_reason;
                if ($row2->rxl_date_prescribed == null || $row2->rxl_date_prescribed == '0000-00-00 00:00:00') {
                    $div2 = $this->timeline_item($row2->rxl_id, 'rxl_id', 'New Medication', $this->human_to_unix($row2->rxl_date_active), trans('nosh.new_medication'), $description2);
                } else {
                    $description2 .= '<br>Status: ' . ucfirst($row2->prescription);
                    $div2 = $this->timeline_item($row2->rxl_id, 'rxl_id', 'Prescribed Medication', $this->human_to_unix($row2->rxl_date_active), trans('nosh.prescribed_medication'), $description2);
                }
                $json[] = [
                    'div' => $div2,
                    'startDate' => $this->human_to_unix($row2->rxl_date_active)
                ];
                $date_arr[] = $this->human_to_unix($row2->rxl_date_active);
            }
        }
        $query3 = DB::table('issues')->where('pid', '=', $pid)->get();
        if ($query3->count()) {
            foreach ($query3 as $row3) {
                if ($row3->type == 'Problem List') {
                    $title = 'New Problem';
                    $title1 = trans('nosh.new_problem');
                }
                if ($row3->type == 'Medical History') {
                    $title = 'New Medical Event';
                    $title1 = trans('nosh.new_medical_event');
                }
                if ($row3->type == 'Surgical History') {
                    $title = 'New Surgical Event';
                    $title1 = trans('nosh.new_surgical_event');
                }
                $description3 = $row3->issue;
                if ($row3->notes !== null && $row3->notes !== '') {
                    $description3 .= ', ' . $row3->notes;
                }
                $div3 = $this->timeline_item($row3->issue_id, 'issue_id', $title, $this->human_to_unix($row3->issue_date_active), $title1, $description3);
                $json[] = [
                    'div' => $div3,
                    'startDate' => $this->human_to_unix($row3->issue_date_active)
                ];
                $date_arr[] = $this->human_to_unix($row3->issue_date_active);
            }
        }
        $query4 = DB::table('immunizations')->where('pid', '=', $pid)->get();
        if ($query4->count()) {
            foreach ($query4 as $row4) {
                $div4 = $this->timeline_item($row4->imm_id, 'imm_id', 'Immunization Given', $this->human_to_unix($row4->imm_date), trans('nosh.immunization_given'), $row4->imm_immunization);
                $json[] = [
                    'div' => $div4,
                    'startDate' => $this->human_to_unix($row4->imm_date)
                ];
                $date_arr[] = $this->human_to_unix($row4->imm_date);
            }
        }
        $query5 = DB::table('rx_list')->where('pid', '=', $pid)->where('rxl_date_inactive', '!=', '0000-00-00 00:00:00')->get();
        if ($query5->count()) {
            foreach ($query5 as $row5) {
                $row5a = DB::table('rx_list')->where('rxl_id', '=', $row5->rxl_id)->first();
                if ($row5->rxl_sig == '') {
                    $instructions5 = $row5->rxl_instructions;
                } else {
                    $instructions5 = $row5->rxl_sig . ', ' . $row5->rxl_route . ', ' . $row5->rxl_frequency;
                    if ($row5->rxl_instructions !== null && $row5->rxl_instructions !== '') {
                        $instructions5 .= ', ' . $row5->rxl_instructions;
                    }
                }
                $description5 = $row5->rxl_medication . ' ' . $row5->rxl_dosage . ' ' . $row5->rxl_dosage_unit . ', ' . $instructions5 . ' ' . trans('nosh.for') . ' ' . $row5->rxl_reason;
                $div5 = $this->timeline_item($row5->rxl_id, 'rxl_id', 'Medication Stopped', $this->human_to_unix($row5->rxl_date_inactive), trans('nosh.medication_stopped'), $description5);
                $json[] = [
                    'div' => $div5,
                    'startDate' => $this->human_to_unix($row5->rxl_date_inactive)
                ];
                $date_arr[] = $this->human_to_unix($row5->rxl_date_inactive);
            }
        }
        $query6 = DB::table('allergies')->where('pid', '=', $pid)->where('allergies_date_inactive', '=', '0000-00-00 00:00:00')->get();
        if ($query6->count()) {
            foreach ($query6 as $row6) {
                $description6 = $row6->allergies_med;
                if ($row6->notes !== null && $row6->notes !== '') {
                    $description6 .= ', ' . $row6->notes;
                }
                $div6 = $this->timeline_item($row6->allergies_id, 'allergies_id', 'New Allergy', $this->human_to_unix($row6->allergies_date_active), trans('nosh.new_allergy'), $description6);
                $json[] = [
                    'div' => $div6,
                    'startDate' => $this->human_to_unix($row6->allergies_date_active)
                ];
                $date_arr[] = $this->human_to_unix($row6->allergies_date_active);
            }
        }
        $query7 = DB::table('data_sync')->where('pid', '=', $pid)->get();
        if ($query7->count()) {
            foreach ($query7 as $row7) {
                $description7 = $row7->action . ', ' . $row7->from;
                $div7 = $this->timeline_item($row7->source_id, $row7->source_index, 'Data Sync via FHIR', $this->human_to_unix($row7->created_at), trans('nosh.data_sync'), $description7);
                $json[] = [
                    'div' => $div7,
                    'startDate' => $this->human_to_unix($row7->created_at)
                ];
                $date_arr[] = $this->human_to_unix($row7->created_at);
            }
        }
        if (! empty($json)) {
            foreach ($json as $key => $value) {
                $item[$key]  = $value['startDate'];
            }
            array_multisort($item, SORT_DESC, $json);
        }
        asort($date_arr);
        $arr['start'] = reset($date_arr);
        $arr['end'] = end($date_arr);
        if ($arr['end'] - $arr['start'] >= 315569260) {
            $arr['granular'] = 'decade';
        }
        if ($arr['end'] - $arr['start'] > 31556926 && $arr['end'] - $arr['start'] < 315569260) {
            $arr['granular'] = 'year';
        }
        if ($arr['end'] - $arr['start'] <= 31556926) {
            $arr['granular'] = 'month';
        }
        $arr['json'] = $json;
        return $arr;
    }

    protected function timeline_item($value, $type, $category, $date, $title, $p, $status='')
    {
        $div = '<div class="cd-timeline-block" data-nosh-category="' . $category . '">';
        if ($category == 'Encounter') {
            $div .= '<div class="cd-timeline-img cd-encounter"><i class="fa fa-stethoscope fa-fw fa-lg"></i>';
        }
        if ($category == 'Telephone Message') {
            $div .= '<div class="cd-timeline-img cd-encounter"><i class="fa fa-phone fa-fw fa-lg"></i>';
        }
        if ($category == 'New Medication' || $category == 'Prescribed Medication') {
            $div .= '<div class="cd-timeline-img cd-medication"><i class="fa fa-eyedropper fa-fw fa-lg"></i>';
        }
        if ($category == 'New Problem' || $category == 'New Medical Event' || $category == 'New Surgical Event') {
            $div .= '<div class="cd-timeline-img cd-issue"><i class="fa fa-bars fa-fw fa-lg"></i>';
        }
        if ($category == 'Immunization Given') {
            $div .= '<div class="cd-timeline-img cd-imm"><i class="fa fa-magic fa-fw fa-lg"></i>';
        }
        if ($category == 'Medication Stopped') {
            $div .= '<div class="cd-timeline-img cd-medication"><i class="fa fa-ban fa-fw fa-lg"></i>';
        }
        if ($category == 'New Allergy') {
            $div .= '<div class="cd-timeline-img cd-allergy"><i class="fa fa-exclamation-triangle fa-fw fa-lg"></i>';
        }
        if ($category == 'Data Sync via FHIR') {
            $div .= '<div class="cd-timeline-img cd-datasync"><i class="fa fa-fire fa-fw fa-lg"></i>';
        }
        $div .= '</div><div class="cd-timeline-content">';
        $div .= '<h3>' . $title . '</h3>';
        $div .= '<p>' . $p . '</p>';
        // Set up URL to each type
        $type_arr = [
            'eid' => route('encounter_view', [$value]),
            't_messages_id' => route('t_message_view', [$value]),
            'rxl_id' => route('medications_list', ['active']),
            'issue_id' => route('conditions_list', ['active']),
            'imm_id' => route('immunizations_list'),
            'allergies_id' => route('allergies_list', ['active'])
        ];
        $url = $type_arr[$type];
        if ($status !== 'n') {
            $div .= '<a href="' . $url . '" class="btn btn-primary cd-read-more" data-nosh-value="' . $value . '" data-nosh-type="' . $type . '" data-nosh-status="' . $status . '">' . trans('nosh.read_more') . '</a>';
        }
        $div .= '<span class="cd-date">' . date('Y-m-d', $date) . '</span>';
        $div .= '</div></div>';
        return $div;
    }

    protected function t_messages_view($t_messages_id, $print=false)
    {
        $row = DB::table('t_messages')->where('t_messages_id', '=', $t_messages_id)->first();
        $text = '<table cellspacing="2" style="font-size:0.9em; width:100%;">';
        if ($print == true) {
            $text .= '<tr><th style="background-color: gray;color: #FFFFFF; text-align: left;">' . strtoupper(trans('nosh.t_messages_view1')) . '</th></tr>';
        }
        $text .= '<tr><td><h4>' . trans('nosh.t_messages_dos') . ': </h4>' . date('m/d/Y', $this->human_to_unix($row->t_messages_dos));
        $text .= '<br><h4>' . trans('nosh.t_messages_subject') . ': </h4>' . $row->t_messages_subject;
        $text .= '<br><h4>' . trans('nosh.t_messages_message') . ': </h4>' . $row->t_messages_message;
        if ($row->actions !== '' && $row->actions !== null) {
            $search_arr = ['action:', 'timestamp:', '---' . "\n"];
            $replace_arr = ['<b>' . trans('nosh.t_messages_view2'). ':</b>', '<b>' . trans('nosh.t_messages_view3') . ':</b>', ''];
            $text .= '<br><h4>' . trans('nosh.t_messages_view4') . ': </h4>' . nl2br(str_replace($search_arr, $replace_arr, $row->actions));
        }
        $text .= '<br><hr />' . trans('nosh.electronic_sign') . ' ' . $row->t_messages_provider . '.';
        $text .= '</td></tr></table>';
        return $text;
    }

    protected function treedata_x_build($nodes_arr)
    {
        $return = [];
        $nodes_partners = [];
        $nodes_sibling = [];
        $nodes_paternal = [];
        $nodes_maternal = [];
        $nodes_children = [];
        $nodes_placeholder = [];
        foreach ($nodes_arr as $node)
        {
            if ($node['id'] !== 'patient') {
                if (isset($node['sibling_group'])) {
                    if ($node['sibling_group'] == 'Partners') {
                        $nodes_partners[] = $node;
                    }
                    if ($node['sibling_group'] == 'Sibling') {
                        $nodes_sibling[] = $node;
                    }
                    if ($node['sibling_group'] == 'Paternal') {
                        $nodes_paternal[] = $node;
                    }
                    if ($node['sibling_group'] == 'Maternal') {
                        $nodes_maternal[] = $node;
                    }
                    if ($node['sibling_group'] == 'Children') {
                        $nodes_children[] = $node;
                    }
                }
                if (isset($node['orig_x'])) {
                    $nodes_placeholder[] = $node;
                }
            } else {
                $return[] = $node;
            }
        }
        if (! empty($nodes_partners)) {
            $a = 1;
            foreach ($nodes_partners as $node1) {
                $node1['x'] = 8 * $a;
                $return[] = $node1;
                $a++;
            }
        }
        if (! empty($nodes_sibling)) {
            $a1 = -1;
            foreach ($nodes_sibling as $node1a) {
                $node1a['x'] = 8 * $a1;
                $return[] = $node1a;
                $a1--;
            }
        }
        if (! empty($nodes_children)) {
            $bf = 1;
            $bm = -1;
            foreach ($nodes_children as $node2) {
                if ($node2['color'] == 'rgb(125,125,255)')  {
                    $node2['x'] = 8 * $bm;
                    $return[] = $node2;
                    $bm--;
                } else {
                    $node2['x'] = 8 * $bf;
                    $return[] = $node2;
                    $bf++;
                }
            }
        }
        if (! empty($nodes_paternal)) {
            $c = -1;
            foreach ($nodes_paternal as $node3) {
                $node3['x'] = 8 * $c;
                $return[] = $node3;
                $c--;
            }
        }
        if (! empty($nodes_maternal)) {
            $d = 1;
            foreach ($nodes_maternal as $node4) {
                $node4['x'] = 8 * $d;
                $return[] = $node4;
                $d++;
            }
        }
        if (! empty($nodes_placeholder)) {
            foreach ($nodes_placeholder as $node5) {
                $orig_x = 0;
                foreach ($return as $k => $v) {
                    if ($v['id'] === $node5['orig_x']) {
                        $orig_x = $v['x'];
                        break;
                    }
                }
                if ($node5['color'] == 'rgb(125,125,255)')  {
                    if ($node5['y'] == -10) {
                        $node5['x'] = $orig_x - 8;
                    }
                    if ($node5['y'] == -20) {
                        $node5['x'] = $orig_x - 4;
                    }

                } else {
                    if ($node5['y'] == -10) {
                        $node5['x'] = $orig_x + 8;
                    }
                    if ($node5['y'] == -20) {
                        $node5['x'] = $orig_x + 4;
                    }
                }
                $return[] = $node5;
            }
        }
        return $return;
    }

    protected function treedata_build($arr, $key, $nodes_arr, $edges_arr, $placeholder_count)
    {
        $rel_arr = [
            'Father' => ['Paternal Grandfather', 'Paternal Grandmother', -20, -10, 'Paternal'],
            'Mother' => ['Maternal Grandfather', 'Maternal Grandmother', -20, -10, 'Maternal'],
            'Sister' => ['Father', 'Mother', -10, 0, 'Sibling'],
            'Brother' => ['Father', 'Mother', -10, 0, 'Sibling'],
            'Paternal Uncle' => ['Paternal Grandfather', 'Paternal Grandmother', -20, -10, 'Paternal'],
            'Paternal Aunt' => ['Paternal Grandfather', 'Paternal Grandmother', -20, -10, 'Paternal'],
            'Maternal Uncle' => ['Maternal Grandfather', 'Maternal Grandmother', -20, -10, 'Maternal'],
            'Maternal Aunt' => ['Maternal Grandfather', 'Maternal Grandmother', -20, -10, 'Maternal'],
        ];
        $rel_arr1 = [
            'Spouse',
            'Partner',
        ];
        $rel_arr2 = $this->array_family();
        $status_arr = [
            'Alive' => trans('nosh.alive'),
            'Deceased' => trans('nosh.deceased')
        ];
        $patient = DB::table('demographics')->where('pid', '=', Session::get('pid'))->first();
        $parents_arr = [];
        $parents1_arr = [];
        if (! empty($arr) && $key !== 'patient') {
            $color = 'rgb(125,125,255)';
            if ($arr[$key]['Gender'] == 'Female') {
                $color = 'rgb(255,125,125)';
            }
            $nosh_data = '<strong>' . trans('nosh.name') . ':</strong> ' . $arr[$key]['Name'];
            $nosh_data = '<br><strong>' . trans('nosh.relationship_to_patient') . ':</strong> ' . $rel_arr2[$arr[$key]['Relationship']];
            $nosh_data .= '<br><strong>' . trans('nosh.status') . ':</strong> ' . $status_arr[$arr[$key]['Status']];
            $nosh_data .= '<br><strong>' . trans('nosh.DOB') . ':</strong> ' . $arr[$key]['Date of Birth'];
            $nosh_data .= '<br><strong>' . trans('nosh.sex') . ':</strong> ' . $arr[$key]['Gender'];
            $nosh_data .= '<br><strong>' . trans('nosh.medical_history') . ':</strong><ul>';
            $medical_arr = explode("\n", $arr[$key]['Medical']);
            foreach ($medical_arr as $medical_item) {
                $nosh_data .= '<li>' . $medical_item . '</li>';
            }
            $nosh_data .= '</ul>';
            // $nosh_data .= '<div class="form-group"><div class="col-md-6 col-md-offset-3">';
            // $nosh_data .= '<a href="' . route('family_history_update', [$key]) . '" class="btn btn-success btn-block"><i class="fa fa-btn fa-pencil"></i> ' . trans('nosh.edit') . '</a>';
            // $nosh_data .= '<a href="' . route('family_history_update', ['add']) . '" class="btn btn-info btn-block"><i class="fa fa-btn fa-plus"></i> ' . trans('nosh.add_new_entry') . '</a></div></div>';
            $node = [
                'id' => $key,
                'label' => $arr[$key]['Name'],
                'size' => 5,
                'color' => $color,
                'nosh_url' => '',
                'nosh_data' => $nosh_data
            ];
            if (array_key_exists($arr[$key]['Relationship'], $rel_arr)) {
                $parents_arr = $rel_arr[$arr[$key]['Relationship']];
                if (isset($rel_arr[$arr[$key]['Relationship']][4])) {
                    $node['sibling_group'] = $rel_arr[$arr[$key]['Relationship']][4];
                }
                $node['y'] = $rel_arr[$arr[$key]['Relationship']][3];
            } else {
                if ($arr[$key]['Relationship'] == 'Son' || $arr[$key]['Relationship'] == 'Daughter') {
                    $parents1_arr = ['Patient', $arr[$key]['Mother']];
                    if ($patient->sex == 'f') {
                        $parents1_arr = [$arr[$key]['Father'],'Patient'];
                    }
                    $node['y'] = 10;
                    $node['sibling_group'] = 'Children';
                }
                if (in_array($arr[$key]['Relationship'], $rel_arr1)) {
                    $node['y'] = 0;
                    $node['sibling_group'] = 'Partners';
                }
            }
            $nodes_arr[] = $node;
            $orig_x = $key;
        } else {
            // Add root patient since $arr is empty
            $color = 'rgb(125,125,255)';
            if ($patient->sex == 'f') {
                $color = 'rgb(255,125,125)';
            }
            $oh = DB::table('other_history')->where('pid', '=', Session::get('pid'))->where('eid', '=', '0')->first();
            // if ($this->yaml_check($oh->oh_fh)) {
            //     $nosh_data = '<div class="form-group"><div class="col-md-6 col-md-offset-3">';
            //     $nosh_data .= '<a href="' . route('family_history_update', ['add']) . '" class="btn btn-info btn-block"><i class="fa fa-btn fa-plus"></i> ' . trans('nosh.add_new_entry') . '</a></div></div>';
            // } else {
            //     $nosh_data = $oh->oh_fh;
            //     $nosh_data .= '<div class="form-group"><div class="col-md-6 col-md-offset-3">';
            //     $nosh_data .= '<a href="' . route('family_history_update', ['add']) . '" class="btn btn-info btn-block"><i class="fa fa-btn fa-plus"></i> ' . trans('nosh.add_new_entry') . '</a></div></div>';
            // }
            $nodes_arr[] = [
                'id' => 'patient',
                'label' => $patient->firstname . ' ' . $patient->lastname,
                'size' => 10,
                'color' => $color,
                'nosh_url' => '',
                'nosh_data' => '',
                'x' => 0,
                'y' => 0
            ];
            $parents_arr = ['Father', 'Mother', -10];
            $orig_x = 'patient';
        }
        // Build mother and father (all people do) - find them if they exist in YAML
        if (! empty($parents_arr)) {
            $father = array_search($parents_arr[0], array_column($arr, 'Relationship'));
            $mother = array_search($parents_arr[1], array_column($arr, 'Relationship'));
            if ($father !== FALSE) {
                $edges_arr[] = [
                    'id' => $this->gen_uuid(),
                    'source' => $father,
                    'target' => $key,
                    'label' => trans('nosh.biological_father'),
                    'type' => 'arrow',
                    'size' => 2,
                    'color' => '#bbb'
                ];
            } else {
                // Check if placeholder node already exists
                $placeholder_node = array_search($parents_arr[0], array_column($nodes_arr, 'label'));
                if ($placeholder_node !== FALSE) {
                    $placeholder_id = $nodes_arr[$placeholder_node]['id'];
                } else {
                    // Make node
                    $placeholder_id = 'placeholder_' . $placeholder_count;
                    // $nosh_data = '<div class="form-group"><div class="col-md-6 col-md-offset-3">';
                    // $nosh_data .= '<a href="' . route('family_history_update', ['add']) . '" class="btn btn-info btn-block"><i class="fa fa-btn fa-plus"></i> ' . trans('nosh.add_new_entry') . '</a></div></div>';
                    $nodes_arr[] = [
                        'id' => $placeholder_id,
                        'label' => $parents_arr[0],
                        'size' => 5,
                        'color' => 'rgb(125,125,255)',
                        'nosh_url' => '',
                        'nosh_data' => '',
                        'orig_x' => $key,
                        'y' => $parents_arr[2]
                    ];
                    $placeholder_count++;
                }
                $edges_arr[] = [
                    'id' => $this->gen_uuid(),
                    'source' => $placeholder_id,
                    'target' => $key,
                    'label' => trans('nosh.biological_father'),
                    'type' => 'arrow',
                    'size' => 2,
                    'color' => '#bbb'
                ];
            }
            if ($mother !== FALSE) {
                $edges_arr[] = [
                    'id' => $this->gen_uuid(),
                    'source' => $mother,
                    'target' => $key,
                    'label' => trans('nosh.biological_mother'),
                    'type' => 'arrow',
                    'size' => 2,
                    'color' => '#bbb'
                ];
            } else {
                $placeholder_node = array_search($parents_arr[1], array_column($nodes_arr, 'label'));
                if ($placeholder_node !== FALSE) {
                    $placeholder_id = $nodes_arr[$placeholder_node]['id'];
                } else {
                    // Make node
                    $placeholder_id = 'placeholder_' . $placeholder_count;
                    // $nosh_data = '<div class="form-group"><div class="col-md-6 col-md-offset-3">';
                    // $nosh_data .= '<a href="' . route('family_history_update', ['add']) . '" class="btn btn-info btn-block"><i class="fa fa-btn fa-plus"></i> ' . trans('nosh.add_new_entry') . '</a></div></div>';
                    $nodes_arr[] = [
                        'id' => $placeholder_id,
                        'label' => $parents_arr[1],
                        'size' => 5,
                        'color' => 'rgb(255,125,255)',
                        'nosh_url' => '',
                        'nosh_data' => '',
                        'orig_x' => $key,
                        'y' => $parents_arr[2]
                    ];
                    $placeholder_count++;
                }
                $edges_arr[] = [
                    'id' => $this->gen_uuid(),
                    'source' => $placeholder_id,
                    'target' => $key,
                    'label' => trans('nosh.biological_mother'),
                    'type' => 'arrow',
                    'size' => 2,
                    'color' => '#bbb'
                ];
            }
        }
        if (! empty($parents1_arr)) {
            if ($parents1_arr[0] == 'Patient') {
                $edges_arr[] = [
                    'id' => $this->gen_uuid(),
                    'source' => 'patient',
                    'target' => $key,
                    'label' => trans('nosh.biological_father'),
                    'type' => 'arrow',
                    'size' => 2,
                    'color' => '#bbb'
                ];
                $mother = array_search($parents1_arr[1], array_column($arr, 'Name'));
                if ($mother !== FALSE) {
                    $edges_arr[] = [
                        'id' => $this->gen_uuid(),
                        'source' => $mother,
                        'target' => $key,
                        'label' => trans('nosh.biological_mother'),
                        'type' => 'arrow',
                        'size' => 2,
                        'color' => '#bbb'
                    ];
                }
            }
            if ($parents1_arr[1] == 'Patient') {
                $edges_arr[] = [
                    'id' => $this->gen_uuid(),
                    'source' => 'patient',
                    'target' => $key,
                    'label' => trans('nosh.biological_mother'),
                    'type' => 'arrow',
                    'size' => 2,
                    'color' => '#bbb'
                ];
                $father = array_search($parents1_arr[0], array_column($arr, 'Name'));
                if ($father !== FALSE) {
                    $edges_arr[] = [
                        'id' => $this->gen_uuid(),
                        'source' => $father,
                        'target' => $key,
                        'label' => trans('nosh.biological_father'),
                        'type' => 'arrow',
                        'size' => 2,
                        'color' => '#bbb'
                    ];
                }
            }
        }
        return [$nodes_arr, $edges_arr, $placeholder_count];
    }

    protected function yaml_check($yaml)
    {
        $check = substr($yaml, 0, 3);
        if ($check == '---') {
            return true;
        } else {
            return false;
        }
    }
}
