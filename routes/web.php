<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Core routes
Route::get('/', ['as' => 'patient', 'uses' => 'ChartController@patient']);
Route::any('load_data', ['as' => 'load_data', 'uses' => 'CoreController@load_data']);
Route::post('timer', ['as' => 'timer', 'uses' => 'CoreController@timer']);

// Chart routes
Route::get('alerts_list/{type}', ['as' => 'alerts_list', 'uses' => 'ChartController@alerts_list']);
Route::get('allergies_list/{type}', ['as' => 'allergies_list', 'uses' => 'ChartController@allergies_list']);
Route::get('audit_logs', ['as' => 'audit_logs', 'uses' => 'ChartController@audit_logs']);
Route::get('conditions_list/{type}', ['as' => 'conditions_list', 'uses' => 'ChartController@conditions_list']);
Route::get('demographics', ['as' => 'demographics', 'uses' => 'ChartController@demographics']);
Route::post('document_delete', ['as' => 'document_delete', 'uses' => 'ChartController@document_delete']);
Route::get('document_view/{id}', ['as' => 'document_view', 'uses' => 'ChartController@document_view']);
Route::get('documents_list/{type}', ['as' => 'documents_list', 'uses' => 'ChartController@documents_list']);
Route::get('download_ccda/{action}/{hippa_id}', ['as' => 'download_ccda', 'uses' => 'ChartController@download_ccda']);
Route::get('encounter_close', ['as' => 'encounter_close', 'uses' => 'ChartController@encounter_close']);
Route::any('encounter_view/{eid}/{previous?}', ['as' => 'encounter_view', 'uses' => 'ChartController@encounter_view']);
Route::get('encounter_vitals_view/{eid?}', ['as' => 'encounter_vitals_view', 'uses' => 'ChartController@encounter_vitals_view']);
Route::get('encounter_vitals_chart/{type}', ['as' => 'encounter_vitals_chart', 'uses' => 'ChartController@encounter_vitals_chart']);
Route::get('encounters_list', ['as' => 'encounters_list', 'uses' => 'ChartController@encounters_list']);
Route::get('family_history', ['as' => 'family_history', 'uses' => 'ChartController@family_history']);
Route::get('growth_chart/{type}', ['as' => 'growth_chart', 'uses' => 'ChartController@growth_chart']);
Route::get('immunizations_csv', ['as' => 'immunizations_csv', 'uses' => 'ChartController@immunizations_csv']);
Route::get('immunizations_list', ['as' => 'immunizations_list', 'uses' => 'ChartController@immunizations_list']);
Route::get('immunizations_print', ['as' => 'immunizations_print', 'uses' => 'ChartController@immunizations_print']);
Route::get('medications_list/{type}', ['as' => 'medications_list', 'uses' => 'ChartController@medications_list']);
Route::get('print_chart_all', ['as' => 'print_chart_all', 'uses' => 'ChartController@print_chart_all']);
Route::get('orders_list/{type}', ['as' => 'orders_list', 'uses' => 'ChartController@orders_list']);
Route::get('records_list/{type}', ['as' => 'records_list', 'uses' => 'ChartController@records_list']);
Route::get('results_chart/{id}', ['as' => 'results_chart', 'uses' => 'ChartController@results_chart']);
Route::get('results_list/{type}', ['as' => 'results_list', 'uses' => 'ChartController@results_list']);
Route::get('results_print/{id}', ['as' => 'results_print', 'uses' => 'ChartController@results_print']);
Route::get('results_view/{id}', ['as' => 'results_view', 'uses' => 'ChartController@results_view']);
Route::any('search_chart', ['as' => 'search_chart', 'uses' => 'ChartController@search_chart']);
Route::get('social_history', ['as' => 'social_history', 'uses' => 'ChartController@social_history']);
Route::get('supplements_list/{type}', ['as' => 'supplements_list', 'uses' => 'ChartController@supplements_list']);
Route::get('t_messages_list', ['as' => 't_messages_list', 'uses' => 'ChartController@t_messages_list']);
Route::any('t_message_view/{t_messages_id}', ['as' => 't_message_view', 'uses' => 'ChartController@t_message_view']);
Route::get('treedata', ['as' => 'treedata', 'uses' => 'ChartController@treedata']);
Route::get('upload_ccda_view/{id}/{type}', ['as' => 'upload_ccda_view', 'uses' => 'ChartController@upload_ccda_view']);
