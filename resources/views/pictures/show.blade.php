@extends('layouts.app')

@section('content')
<h1>{{ $picture->title }}</h1>
<p>Provided by {{ $gallery->author }}</p>
    <img src="{{ route('galleries.pictures.show', compact('gallery', 'picture')) }}">
@endsection
