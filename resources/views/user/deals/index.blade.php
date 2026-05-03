@extends('user.layouts.app')

@section('content')
<div style="max-width: 1400px; margin: 0 auto; padding: 2rem;">
    <h1>Top Deals</h1>
    @foreach($deals as $deal)
        <p>{{ $deal->title }}</p>
    @endforeach
</div>
@endsection
