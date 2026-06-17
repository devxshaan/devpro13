@extends('emails.layout')

@section('content')

    <p class="title">{{ $title }}</p>

    @foreach($lines as $line)
        <p class="line">{{ $line }}</p>
    @endforeach

    @if($action)
        <div class="action-wrap">
            <a href="{{ $action['url'] }}" class="btn btn-{{ $type }}">
                {{ $action['text'] }}
            </a>
        </div>
    @endif

    @if($footer)
        <hr class="divider">
        <p class="footer-text">{{ $footer }}</p>
    @endif

@endsection