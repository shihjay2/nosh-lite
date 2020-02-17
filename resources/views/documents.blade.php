@extends('layouts.app')

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
                    <embed src="{{ $document_url }}" type="application/pdf" width="100%" height="600px">
                    <a href="{{ $document_url }}" target="_blank" class="nosh-no-load">{{ trans('nosh.no_pdf') }}</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('view.scripts')
<script type="text/javascript">
    $(document).ready(function() {
        $('[data-toggle=offcanvas]').css('cursor', 'pointer').click(function() {
            $('.row-offcanvas').toggleClass('active');
        });
        if (noshdata.message_action !== '') {
            toastr.success(noshdata.message_action);
        }
    });
    $(window).bind('beforeunload', function(){
        $.ajax({
            type: 'POST',
            url: noshdata.document_delete,
            async: false,
            success: function(data){
            }
        });
        return void(0);
    });
</script>
@endsection
