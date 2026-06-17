@extends('emails.layout')

@section('content')
    <div class="title">{{ $title }}</div>

    @foreach($lines as $line)
        <p class="line">{{ $line }}</p>
    @endforeach

    <div class="action-wrap">
        <a href="{{ $url }}" class="btn btn-{{ $type }}">
            Reset Password
        </a>
    </div>

    <hr class="divider">

    <p class="line">
        If you did not request a password reset, no further action is required.
    </p>
@endsection