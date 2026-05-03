@extends('user.layouts.app')

@section('content')
<div style="max-width: 1400px; margin: 0 auto; padding: 2rem;">
    <h1>Categories</h1>
    @foreach($categories as $category)
        <p>{{ $category->name }}</p>
    @endforeach
</div>
@endsection
