<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'NOSH Lite') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset(mix('/css/builds/base.css')) }}" rel="stylesheet">
    @yield('view.stylesheet')
    <style>
        @import url(https://fonts.googleapis.com/css?family=Nunito);
        @import url(https://fonts.googleapis.com/css?family=Pacifico);
        body {
            font-family: 'Nunito';
        }
        h3 {
            font-family: 'Nunito';
        }
        h4 {
            font-family: 'Nunito';
        }
        h5 {
            font-family: 'Nunito';
        }
        pre.bash {
            background-color: black;
            color: white;
            font-size: small;
            font-family: Consolas,Monaco,Lucida Console,Liberation Mono,DejaVu Sans Mono,Bitstream Vera Sans Mono,Courier New, monospace;
            width: 100%;
            display: inline-block;
        }
        .card {
            border-radius: 5px;
        }
        .card--blue {
            border-top: 2px solid blue;
        }
        .card--green {
            border-top: 2px solid green;
        }
        .card--red {
            border-top: 2px solid red;
        }

        body {
            overflow-x: hidden;
            padding-top: 55px;
        }

        .w-sidebar {
            width: 200px;
            max-width: 200px;
            top: 0;
            z-index: 1060;
        }

        .row.collapse {
            margin-left: -200px;
            left: 0;
        	transition: margin-left .15s linear;
        }

        .row.collapse.show {
            margin-left: 0 !important;
        }

        .row.collapsing {
            margin-left: -200px;
            left: -0.05%;
        	transition: all .15s linear;
        }

        @media (max-width:768px) {
            .row.collapse,
            .row.collapsing {
                margin-left: 0 !important;
                left: 0 !important;
                overflow: visible;
            }

            .row > .sidebar.collapse {
                display: flex !important;
                margin-left: -100% !important;
                transition: all .3s linear;
                position: fixed;
                z-index: 1050;
                max-width: 0;
                min-width: 0;
                flex-basis: auto;
            }

            .row > .sidebar.collapse.show {
                margin-left: 0 !important;
                width: 100%;
                max-width: 100%;
                min-width: initial;
            }

            .row > .sidebar.collapsing {
                display: flex !important;
                margin-left: -10% !important;
                transition: all .3s linear !important;
                position: fixed;
                z-index: 1050;
                min-width: initial;
            }
        }
        .sidebar-nav-link[data-toggle].collapsed:after {
            content: "     ▾";
        }
        .sidebar-nav-link[data-toggle]:not(.collapsed):after {
            content: "     ▴";
        }
        .cd-timeline-content h3 {
            font-family: 'Nunito';
        }
        .logo{
            font-family: 'Pacifico', arial, serif;
            font-size: 30px;
            text-shadow: 4px 4px 4px #aaa;
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <div class="container-fluid fixed-top py-0">
        <div class="row collapse show no-gutters d-flex h-100 position-relative">
            @if (isset($sidebar))
            <div class="col-3 px-0 w-sidebar navbar-collapse collapse d-none d-md-flex">
                <!-- spacer col -->
            </div>
            @endif
            <div class="col px-3 px-md-0">
                <!-- toggler -->
                <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
                    <div class="container">
                        @if (isset($sidebar))
                        <a data-toggle="collapse" href="#" data-target=".collapse" role="button" class="p-1" style="margin-right:10px;">
                            <i class="fa fa-chevron-right fa-lg"></i>
                        </a>
                        @endif
                        <div style="width:122px">
                            <a class="navbar-brand logo" href="{{ route('patient') }}">
                                {{ trans('nosh.nosh_lite')}}
                            </a>
                            <div id="timer" style="font-size:1.2em"></div>
                        </div>
                        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                            <span class="navbar-toggler-icon"></span>
                        </button>

                        <div class="collapse navbar-collapse" id="navbarSupportedContent">
                            <!-- Left Side Of Navbar -->
                            <ul class="navbar-nav mr-auto">

                            </ul>

                            <!-- Right Side Of Navbar -->
                            <ul class="navbar-nav ml-auto">
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('load_data') }}"><i class="fa fa-fw fa-upload"></i> {{ trans('nosh.load_data') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('print_chart_all') }}"><i class="fa fa-fw fa-print"></i> {{ trans('nosh.print') }}</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
    </div>
    <div class="container-fluid px-0">
        <div class="row collapse show no-gutters d-flex h-100 position-relative">
            @if (isset($sidebar))
                <div class="col-3 p-0 h-100 w-sidebar navbar-collapse collapse d-none d-md-flex sidebar">
                    <!-- fixed sidebar -->
                    <div class="navbar-dark overflow-auto bg-dark text-white position-fixed h-100 align-self-start w-sidebar">
                        <!-- <h5 class="px-2 pt-2 logo">{{ trans('nosh.nosh_lite')}} <a data-toggle="collapse" class="px-1 d-inline d-md-none text-white" href="#" data-target=".collapse"><i class="fa fa-chevron-left"></i></a></h5> -->
                        @if (isset($name))
                            <div style="color:white">
                                <a data-toggle="collapse" class="d-flex justify-content-center pt-2 d-inline d-md-none text-white" href="#" data-target=".collapse"><i class="fa fa-chevron-left"></i></a>
                                <a href="{{ route('patient') }}" class="text-white">
                                    <div style="font-size:1.2em" class="px-2 pt-2 pb-2">{{ $name }}</div>
                                    {!! $demographics_quick !!}
                                </a>
                            </div>
                        @endif
                        <ul class="nav flex-column flex-nowrap text-truncate nav-pills">
                            <li class="nav-item sidebar-search">
                                <form class="nosh-form" role="form" method="POST" action="{{ route('search_chart') }}">
                                    {{ csrf_field() }}
                                    <div class="input-group custom-search-form px-2 pb-2">
                                        <input type="text" name="search_chart" class="form-control" placeholder="{{ trans('nosh.search_chart') }}">
                                        <span class="input-group-btn">
                                        <button class="btn btn-primary" type="submit">
                                            <i class="fa fa-search"></i>
                                        </button>
                                    </span>
                                    </div>
                                </form>
                            </li>
                            <li class="nav-item">
                                @if($sidebar == 'demographics')
                                    <a class="nav-link active" href="{{ route('demographics') }}"><i class="fa fa-fw fa-user"></i> {{ trans('nosh.demographics') }}</a>
                                @else
                                    <a class="nav-link text-white" href="{{ route('demographics') }}"><i class="fa fa-fw fa-user"></i> {{ trans('nosh.demographics') }}</a>
                                @endif
                            </li>
                            <li class="nav-item">
                                @if($sidebar == 'conditions')
                                    <a class="nav-link active" href="{{ route('conditions_list', ['type' => 'active']) }}"><i class="fa fa-fw fa-bars"></i> {{ trans('nosh.conditions_list') }}</a>
                                @else
                                    <a class="nav-link text-white" href="{{ route('conditions_list', ['type' => 'active']) }}"><i class="fa fa-fw fa-bars"></i> {{ trans('nosh.conditions_list') }}</a>
                                @endif
                            </li>
                            <li class="nav-item">
                                @if($sidebar == 'medications')
                                    <a class="nav-link active" href="{{ route('medications_list', ['type' => 'active']) }}"><i class="fa fa-fw fa-eyedropper"></i> {{ trans('nosh.medications_list') }}</a>
                                @else
                                    <a class="nav-link text-white" href="{{ route('medications_list', ['type' => 'active']) }}"><i class="fa fa-fw fa-eyedropper"></i> {{ trans('nosh.medications_list') }}</a>
                                @endif
                            </li>
                            <li class="nav-item">
                                @if($sidebar == 'supplements')
                                    <a class="nav-link active" href="{{ route('supplements_list', ['type' => 'active']) }}"><i class="fa fa-fw fa-tree"></i> {{ trans('nosh.supplements_list') }}</a>
                                @else
                                    <a class="nav-link text-white" href="{{ route('supplements_list', ['type' => 'active']) }}"><i class="fa fa-fw fa-tree"></i> {{ trans('nosh.supplements_list') }}</a>
                                @endif
                            </li>
                            <li class="nav-item">
                                @if($sidebar == 'immunizations')
                                    <a class="nav-link active" href="{{ route('immunizations_list') }}"><i class="fa fa-fw fa-magic"></i> {{ trans('nosh.immunizations_list') }}</a>
                                @else
                                    <a class="nav-link text-white" href="{{ route('immunizations_list') }}"><i class="fa fa-fw fa-magic"></i> {{ trans('nosh.immunizations_list') }}</a>
                                @endif
                            </li>
                            <li class="nav-item">
                                @if($sidebar == 'allergies')
                                    <a class="nav-link active" href="{{ route('allergies_list', ['type' => 'active']) }}"><i class="fa fa-fw fa-exclamation-triangle"></i> {{ trans('nosh.allergies_list') }}</a>
                                @else
                                    <a class="nav-link text-white" href="{{ route('allergies_list', ['type' => 'active']) }}"><i class="fa fa-fw fa-exclamation-triangle"></i> {{ trans('nosh.allergies_list') }}</a>
                                @endif
                            </li>
                            <li class="nav-item">
                                @if($sidebar == 'alerts')
                                    <a class="nav-link active" href="{{ route('alerts_list', ['type' => 'active']) }}"><i class="fa fa-fw fa-tree"></i> {{ trans('nosh.alerts_list') }}</a>
                                @else
                                    <a class="nav-link text-white" href="{{ route('alerts_list', ['type' => 'active']) }}"><i class="fa fa-fw fa-tree"></i> {{ trans('nosh.alerts_list') }}</a>
                                @endif
                            </li>
                            <li class="nav-item">
                                @if($sidebar == 'orders')
                                    <a class="nav-link active" href="{{ route('orders_list', ['type' => 'orders_labs']) }}"><i class="fa fa-fw fa-thumbs-o-up"></i> {{ trans('nosh.orders_list') }}</a>
                                @else
                                    <a class="nav-link text-white" href="{{ route('orders_list', ['type' => 'orders_labs']) }}"><i class="fa fa-fw fa-thumbs-o-up"></i> {{ trans('nosh.orders_list') }}</a>
                                @endif
                            </li>
                            <li class="nav-item">
                                @if($sidebar == 'encounters')
                                    <a class="nav-link active" href="{{ route('encounters_list') }}"><i class="fa fa-fw fa-stethoscope"></i> {{ trans('nosh.encounters_list') }}</a>
                                @else
                                    <a class="nav-link text-white" href="{{ route('encounters_list') }}"><i class="fa fa-fw fa-stethoscope"></i> {{ trans('nosh.encounters_list') }}</a>
                                @endif
                            </li>
                            <li class="nav-item">
                                @if($sidebar == 'documents')
                                    <a class="nav-link active" href="{{ route('documents_list', ['type' => 'All']) }}"><i class="fa fa-fw fa-file-text-o"></i> {{ trans('nosh.documents_list') }}</a>
                                @else
                                    <a class="nav-link text-white" href="{{ route('documents_list', ['type' => 'All']) }}"><i class="fa fa-fw fa-file-text-o"></i> {{ trans('nosh.documents_list') }}</a>
                                @endif
                            </li>
                            <li class="nav-item">
                                @if($sidebar == 'results')
                                    <a class="nav-link active" href="{{ route('results_list', ['type' => 'Laboratory']) }}"><i class="fa fa-fw fa-flask"></i> {{ trans('nosh.results_list') }}</a>
                                @else
                                    <a class="nav-link text-white" href="{{ route('results_list', ['type' => 'Laboratory']) }}"><i class="fa fa-fw fa-flask"></i> {{ trans('nosh.results_list') }}</a>
                                @endif
                            </li>
                            <li class="nav-item">
                                @if($sidebar == 't_messages')
                                    <a class="nav-link active" href="{{ route('t_messages_list') }}"><i class="fa fa-fw fa-phone"></i> {{ trans('nosh.t_messages_list') }}</a>
                                @else
                                    <a class="nav-link text-white" href="{{ route('t_messages_list') }}"><i class="fa fa-fw fa-phone"></i> {{ trans('nosh.t_messages_list') }}</a>
                                @endif
                            </li>
                            @if($growth_chart_show == 'yes')
                            <li class="nav-item">
                                <a class="nav-link collapsed text-white sidebar-nav-link" href="#submenu1" data-toggle="collapse" data-target="#submenu1"><i class="fa fa-fw fa-line-chart"></i> {{ trans('nosh.growth_charts') }}</a>
                                <div class="collapse" id="submenu1" aria-expanded="false">
                                    <ul class="flex-column pl-2 nav">
                                        <li class="nav-item"><a class="nav-link text-white" href="{{ route('growth_chart', ['weight-age']) }}">{{ trans('nosh.weight') }}</a></li>
                                        <li class="nav-item"><a class="nav-link text-white" href="{{ route('growth_chart', ['height-age']) }}">{{ trans('nosh.height') }}</a></li>
                                        @if(Session::get('agealldays') < 1856)
                                            <li class="nav-item"><a class="nav-link text-white" href="{{ route('growth_chart', ['head-age']) }}">{{ trans('nosh.hc') }}</a></li>
                                        @endif
                                        <l class="nav-item"i><a class="nav-link text-white" href="{{ route('growth_chart', ['weight-height']) }}">{{ trans('nosh.weight_height') }}</a></li>
                                        @if(Session::get('agealldays') > 730.5)
                                            <li class="nav-item"><a class="nav-link text-white" href="{{ route('growth_chart', ['bmi-age']) }}">{{ trans('nosh.BMI') }}</a></li>
                                        @endif
                                    </ul>
                                </div>
                            </li>
                            @endif
                            <li class="nav-item">
                                @if($sidebar == 'social_history')
                                    <a class="nav-link active" href="{{ route('social_history') }}"><i class="fa fa-fw fa-check"></i> {{ trans('nosh.social_history') }}</a>
                                @else
                                    <a class="nav-link text-white" href="{{ route('social_history') }}"><i class="fa fa-fw fa-users"></i> {{ trans('nosh.social_history') }}</a>
                                @endif
                            </li>
                            <li class="nav-item">
                                @if($sidebar == 'family_history')
                                    <a class="nav-link active" href="{{ route('family_history') }}"><i class="fa fa-fw fa-sitemap"></i> {{ trans('nosh.family_history') }}</a>
                                @else
                                    <a class="nav-link text-white" href="{{ route('family_history') }}"><i class="fa fa-fw fa-sitemap"></i> {{ trans('nosh.family_history') }}</a>
                                @endif
                            </li>
                            <li class="nav-item">
                                @if($sidebar == 'records_list')
                                    <a class="nav-link active" href="{{ route('records_list', ['release']) }}"><i class="fa fa-fw fa-handshake-o"></i> {{ trans('nosh.records_list') }}</a>
                                @else
                                    <a class="nav-link text-white" href="{{ route('records_list', ['release']) }}"><i class="fa fa-fw fa-handshake-o"></i> {{ trans('nosh.records_list') }}</a>
                                @endif
                            </li>
                            <li class="nav-item">
                                @if($sidebar == 'audit_logs')
                                    <a class="nav-link active" href="{{ route('audit_logs') }}"><i class="fa fa-fw fa-stack-overflow"></i> {{ trans('nosh.audit_logs') }}</a>
                                @else
                                    <a class="nav-link text-white" href="{{ route('audit_logs') }}"><i class="fa fa-fw fa-stack-overflow"></i> {{ trans('nosh.audit_logs') }}</a>
                                @endif
                            </li>
                        </ul>
                    </div>
                </div>
            @endif
            <div id="app" class="col p-3">
                <main class="py-4">
                    @yield('content')
                </main>
            </div>
        </div>
    </div>
    <div class="modal" id="loadingModal" role="dialog">
        <div class="modal-dialog">
          <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-body">
                    <i class="fa fa-spinner fa-spin fa-pulse fa-2x fa-fw"></i><span id="modaltext" style="margin:10px"></span>
                </div>
            </div>
        </div>
    </div>
    <div class="modal" id="warningModal" role="dialog">
        <div class="modal-dialog">
          <!-- Modal content-->
            <div class="modal-content">
                <div id="warningModal_header" class="modal-header"></div>
                <div id="warningModal_body" class="modal-body" style="height:80vh;overflow-y:auto;"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal"><i class="fa fa-btn fa-times"></i> {{ trans('nosh.button_close') }}</button>
                  </div>
            </div>
        </div>
    </div>
    <script src="{{ asset(mix('/js/builds/base.js')) }}"></script>
    <script type="text/javascript">
    // Global variables
    var noshdata = {
        'document_delete': '<?php echo url("document_delete"); ?>',
        'document_type': '<?php if (isset($document_type)) { echo $document_type; }?>',
        'document_url': '<?php if (isset($document_url)) { echo $document_url; }?>',
        'graph_series_name': '<?php if (isset($graph_series_name)) { echo $graph_series_name; }?>',
        'graph_title': '<?php if (isset($graph_title)) { echo $graph_title; }?>',
        'graph_type': '<?php if (isset($graph_type)) { echo $graph_type; }?>',
        'graph_x_title': '<?php if (isset($graph_x_title)) { echo $graph_x_title; }?>',
        'graph_y_title': '<?php if (isset($graph_y_title)) { echo $graph_y_title; }?>',
        'height_unit': '<?php if (isset($height_unit)) { echo $height_unit; }?>',
        'last_page': '<?php echo url("last_page"); ?>',
        'treedata': '<?php echo url("treedata"); ?>',
        'vitals_graph': '<?php echo url("encounter_vitals_chart"); ?>'
    };
    toastr.options = {
        'closeButton': true,
        'debug': false,
        'newestOnTop': true,
        'progressBar': true,
        'positionClass': 'toast-bottom-full-width',
        'preventDuplicates': false,
        'showDuration': '300',
        'hideDuration': '1000',
        'timeOut': '10000',
        'extendedTimeOut': '5000',
        'showEasing': 'swing',
        'hideEasing': 'linear',
        'showMethod': 'fadeIn',
        'hideMethod': 'fadeOut'
    };
    toastr.options.onHidden = function() { noshdata.toastr_collide = ''; };
    $.ajaxSetup({
        headers: {"cache-control":"no-cache"},
        beforeSend: function(request) {
            return request.setRequestHeader("X-CSRF-Token", $("meta[name='csrf-token']").attr('content'));
        }
    });
    function checkEmpty(o,n) {
        var text = '';
        if (o.val() === '' || o.val() === null) {
            if (n !== undefined) {
                text = n.replace(":","");
                toastr.error(text + ' Required');
            }
            o.closest('.form_group').addClass('has-error');
            o.parent().append('<span class="help-block">' + text + ' required</span>');
            return false;
        } else {
            if (o.closest('.form_group').hasClass('has-error')) {
                o.closest('.form_group').removeClass('has-error');
                o.next().remove();
            }
            return true;
        }
    }
    function timer_notification() {
        $.ajax({
            type: "POST",
            url: "{{ route('timer') }}",
            success: function(data){
                if (data !== '') {
                    $('#timer').html(data);
                }
            }
        });
    }
    $(document).ready(function() {
        var options = {
            valueNames: [
                'nosh_list_item'
            ]
        };
        var list = new List('results_list_div', options);
        var list1 = new List('conditions_list_pl_div', options);
        var list2 = new List('conditions_list_mh_div', options);
        var list3 = new List('conditions_list_sh_div', options);
        $('a').css('cursor', 'pointer').on('click', function(event) {
            if ($(this).attr('href') !== undefined) {
                if ($(this).attr('href').search('#') == -1 && $(this).hasClass('nosh-no-load') === false) {
                    $('#modaltext').text("{{ trans('nosh.loading') }}...");
                    $('#loadingModal').modal('show');
                }
                if ($(this).hasClass('kakeibo-no-load') === true && $(this).hasClass('nosh-delete')) {
                    $(this).removeClass('kakeibo-no-load');
                }
            }
        });
        setInterval(timer_notification, 10000);
    });
    </script>
    @yield('view.scripts')
</body>
</html>
