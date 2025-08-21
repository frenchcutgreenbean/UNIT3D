@extends('layout.with-main')

@section('title')
    <title>Bets - {{  config('other.title') }}</title>
@endsection

@section('breadcrumbs')
    <li class="breadcrumb--active">
        Bets
    </li>
@endsection

@section('page', 'page__bets--index')

@section('main')
    @livewire('bet-search')
@endsection
