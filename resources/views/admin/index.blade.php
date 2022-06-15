@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>{{ config('adminlte.title') }}</h1>
@stop

@section('content')
    <p>メニューから選択してください。</p>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset( cacheBusting('css/common.css') ) }}">
@stop

@section('js')
@stop
