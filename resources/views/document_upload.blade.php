@extends('layouts.app')

@section('view.stylesheet')
	<link rel="stylesheet" href="{{ asset('css/fileinput.min.css') }}">
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <span class="pull-left" style="font-size:1.2em">{!! $panel_header !!}</span>
                    @if (isset($panel_dropdown))
                        <div class="pull-right">
                            {!! $panel_dropdown !!}
                        </div>
                    @endif
                </div>
                <div class="card-body">
                    @if (isset($content))
                        {!! $content !!}
                    @endif
                    <form id="document_upload_form" class="form-horizontal" role="form" method="POST" enctype="multipart/form-data" action="{{ $document_upload }}">
                        {{ csrf_field() }}
                        <label class="control-label"></label>
                        <input id="file_input" name="file_input" type="file" multiple class="file-loading">
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('view.scripts')
<script src="{{ asset('js/sortable.min.js') }}"></script>
<script src="{{ asset('js/purify.min.js') }}"></script>
<script src="{{ asset('js/fileinput.min.js') }}"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $('[data-toggle=offcanvas]').css('cursor', 'pointer').click(function() {
            $('.row-offcanvas').toggleClass('active');
        });
        $("#file_input").fileinput({
            allowedFileExtensions: JSON.parse(noshdata.document_type),
            maxFileCount: 1,
			dropZoneEnabled: false
        });
    });
</script>
@endsection
