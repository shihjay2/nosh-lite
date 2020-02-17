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
                    @if (isset($message_action))
						<div class="alert alert-success" role="alert">
							<strong>{!! $message_action !!}</strong>
						</div>
					@endif
                    {!! $content !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
