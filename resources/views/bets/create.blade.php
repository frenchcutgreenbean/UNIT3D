@extends('layout.with-main')
@section('title')
    <title>Create Bet - {{ config('other.title') }}</title>
@endsection

@section('breadcrumbs')
    <li class="breadcrumb--active">
        Create Bet
    </li>
@endsection
@section('page', 'page__bets--create')

@section('main')

    @include('bets.partials.form', [
        'action' => route('bets.store'),
        'method' => 'POST',
        'buttonText' => 'Create Bet',
        'bet' => null,
        'titleText' => 'Create'
    ])
@endsection
